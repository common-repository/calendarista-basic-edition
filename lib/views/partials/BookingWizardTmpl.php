<?php
class Calendarista_BookingWizardTmpl extends Calendarista_TemplateBase{
	public $project;
	public $map;
	public $optionals;
	public $stringResources;
	public $steps;
	public $counter;
	public $prev;
	public $next;
	public $backgroundImageCss;
	public $availabilityPreviewUrl;
	public $selectedStepName;
	public $invoiceId = null;
	public $displayAlert = false;
	public $style;
	public $stagingId;
	public $requestId;
	public $confirmUrl;
	public function __construct(){
		parent::__construct();
		new Calendarista_CheckoutController($this->viewState, array($this, 'checkout'), true, $this->projectId);  
		$this->prev = $this->selectedStep - 1;
		$this->next = $this->selectedStep + 1;
		$this->project = Calendarista_ProjectHelper::getProject($this->projectId);
		if(!$this->project){
			return;
		}
		$repo = new Calendarista_MapRepository();
		$this->map = $repo->readByProject($this->projectId);
		$repo = new Calendarista_OptionalRepository();
		$this->optionals = $repo->readAll($this->projectId);
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->initThumbnail();
		$this->initSteps();
		$this->selectedStepName = $this->steps[$this->selectedStep - 1]['name'];
		$styleRepo = new Calendarista_StyleRepository();
		$this->style = $styleRepo->readByProject($this->projectId);
		add_filter('calendarista_checkout_summary', array($this, 'checkoutSummary'), 1, 1);
		if(has_filter('calendarista_checkout_invoice_id') || isset($_GET['calendarista_staging_id'])) {
			$orderRepo = new Calendarista_OrderRepository();
			$invoiceId = apply_filters('calendarista_checkout_invoice_id', null);
			if(!$invoiceId){
				$invoiceId = $orderRepo->readInvoiceByStagingId(sanitize_text_field($_GET['calendarista_staging_id']));
			}
			$order = $orderRepo->readByInvoiceId($invoiceId);
			if($order && ((int)$order->projectId === (int)$this->projectId || in_array($orderRepo->projectId, $this->projectList))){
				$this->invoiceId = $invoiceId;
				$this->displayAlert = true;
			}
		}
		$this->populateRequestId();
		$handler = $this->generalSetting->confirmUrl ? 'regcheckout' : null;
		$this->confirmUrl = $this->getConfirmUrl($this->requestUrl, null, $handler);
		$this->render();
	}
	
	public function populateRequestId(){
		$this->requestId = $this->getViewStateValue('requestId');
		if(!$this->requestId){
			$this->requestId = uniqid(sprintf('calendarista_req_%s_', time()));
		}
	}
	public function initThumbnail(){
		$this->backgroundImageCss = 'display:none;';
		if($this->availabilityThumbnailView){
			return;
		}
		$this->availabilityPreviewUrl = $this->getPostValue('availabilityPreviewUrl');
		if($this->availabilityPreviewUrl){
			$this->backgroundImageCss = sprintf('background-image: url(%s);', $this->availabilityPreviewUrl);
		}
	}
	public function initSteps(){
		$this->steps = array();
		$this->counter = 0;
		array_push($this->steps, array(
			'name'=>'calendar'
			, 'counter'=>++$this->counter
			, 'label'=>$this->stringResources['WIZARD_STEP_1']
		));
		if($this->map){
			array_push($this->steps, array(
				'name'=>'map'
				, 'counter'=>++$this->counter
				, 'label'=>$this->stringResources['WIZARD_STEP_2']
			));
		}
		if($this->optionals->count() > 0){
			array_push($this->steps, array(
				'name'=>'optionals'
				, 'counter'=>++$this->counter
				, 'label'=>$this->stringResources['WIZARD_STEP_3']
			));
		}
		array_push($this->steps, array(
			'name'=>'form'
			, 'counter'=>++$this->counter
			, 'label'=>$this->stringResources['WIZARD_STEP_4']
		));
		array_push($this->steps, array(
			'name'=>'checkout'
			, 'counter'=>++$this->counter
			, 'label'=>$this->stringResources['WIZARD_STEP_5']
		));
	}
	public function embedPaymentOperators(){
		$result = array();
		if($this->project->paymentsMode === 3/*woocommerce*/){
			if(Calendarista_WooCommerceHelper::wooCommerceActive()){
				//woo works alone
				$woo = new Calendarista_WooCommerceSetting(array('wooProductId'=>$this->project->wooProductId));
				array_push($result, $woo->toArray());
			}else{
				Calendarista_ErrorLogHelper::insert(__('WooCommerce is not active, please activate', 'calendarista'));
			}
		}else{
			$paymentSettingRepo = new Calendarista_PaymentSettingRepository();
			$result = $paymentSettingRepo->readAll();
			if(has_filter('calendarista_payment_operators')) {
				$result = apply_filters('calendarista_payment_operators', $result);
			}
		}
		if($result){
			foreach($result as $r){
				if(!(bool)$r['enabled']){
					continue;
				}
				if(!$this->stagingId){
					$this->stagingId = $this->getStagingId();
				}
				do_action('calendarista_embed_payment_form', $r, $this->stagingId);
				switch($r['paymentOperator']){
					case 0: //PAYPAL
					new Calendarista_PaymentPaypalTmpl(new Calendarista_PayPalSetting($r), $this->stagingId);
					break;
					case 1: //STRIPE
					new Calendarista_PaymentStripeTmpl(new Calendarista_StripeSetting($r), $this->stagingId);
					break;
					case 3: //WOOCOMMERCE
					new Calendarista_PaymentWooCommerceTmpl(new Calendarista_WooCommerceSetting($r), $this->stagingId);
					break;
				}
			}
		}
	}
	public function getStagingId(){
		$stagingId = $this->getViewStateValue('stagingId');
		$stagingRepo = new Calendarista_StagingRepository();
		if(!$stagingId){
			$stagingId = $stagingRepo->insert($this->stateBag);
		}else{
			$stagingRepo->update(array('id'=>$stagingId, 'viewState'=>$this->stateBag));
		}
		return $stagingId;
	}
	public function checkoutSummary($ignoreSeats = false){
		$args = array_merge($this->viewState, array('ignoreSeats'=>$ignoreSeats));
		$costHelper = new Calendarista_CostHelper($args);
		$name = $this->parseFullName($this->getViewStateValue('name'));
		$deposit = $costHelper->depositToString();
		$depositNotification = $deposit !== null ? 
			sprintf($this->stringResources['WOOCOMMERCE_DEPOSIT_LABEL'], Calendarista_MoneyHelper::toLongCurrency($costHelper->balance)) : '';
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$seats = $this->getSeats($costHelper->availability, $costHelper->seats);
		$customFormElements = Calendarista_BookingCostSummaryTmpl::getCustomFormElements($seats, $this->getViewStateValue('formelements'));
		$dynamicFields = Calendarista_BookingCostSummaryTmpl::getDynamicFields($this->getViewStateValue('dynamicFields'));
		return array(
			'serviceName'=>$costHelper->project->name
			, 'availabilityName'=>$costHelper->availability->name
			, 'name'=>$name['fullname']
			, 'firstname'=>$name['firstname']
			, 'lastname'=>$name['lastname']
			, 'email'=>$this->getViewStateValue('email')
			, 'stagingId'=>$this->stagingId
			, 'optionalCost'=>$costHelper->optionalCost
			, 'subTotalAmount'=>$costHelper->subTotalAmount
			, 'totalAmount'=>$costHelper->totalAmount
			, 'totalAmountBeforeDeposit'=>$costHelper->totalAmountBeforeDeposit
			, 'fullAmountDiscount'=>$costHelper->availability->fullAmountDiscount
			, 'currency'=>$generalSetting->currency
			, 'address1'=>$this->getViewStateValue('address1')
			, 'address2'=>$this->getViewStateValue('address2')
			, 'city'=>$this->getViewStateValue('city')
			, 'state'=>$this->getViewStateValue('state')
			, 'zipCode'=>$this->getViewStateValue('zipCode')
			, 'country'=>$this->getViewStateValue('country')
			, 'summary'=>$costHelper->dateRange
			, 'summaryPlainText'=>$costHelper->dateRangePlainText
			, 'optionals'=>$costHelper->optionalsHelper->summary(false)
			, 'customFormElements'=>$customFormElements
			, 'dynamicFields'=>$dynamicFields
			, 'thumbnail'=>$costHelper->availability->imageUrl
			, 'seats'=>$seats
			, 'seatsMinimum'=>$this->getViewStateValue('seatsMinimum')
			, 'seatsMaximum'=>$this->getViewStateValue('seatsMaximum')
			, 'depositNotification'=>$depositNotification
			, 'availabilityList'=>(count($costHelper->availabilityNames) > 0 && $costHelper->hasMultipleAvailabilities) ? sprintf($this->stringResources['MULTIPLE_AVAILABILITY_LIST'], implode(', ', $costHelper->availabilityNames)) : null
		); 
	}
	public function getSeats($availability, $seats){
		if(!$availability->selectableSeats){
			$dynamicFields = $this->getViewStateValue('dynamicFields');
			$guestCount = 0;
			if(is_array($dynamicFields)){
				foreach($dynamicFields as $field){
					if($field['limitBySeat']){
						$guestCount += (int)$field['value'];
					}
				}
			}
			if($guestCount){
				$seats = $guestCount;
			}
		}
		return $seats;
	}
	public static function addToCalendarButtonRender($invoiceId, $stringResources, $displayAddToCalendarOption = true){
		if(!$invoiceId){
			return;
		}
		if(!$displayAddToCalendarOption){
			return;
		}
		$repo = new Calendarista_BookedAvailabilityRepository();
		$result = $repo->readByInvoiceId($invoiceId);
		if($result){
			$handlerUrl = add_query_arg(array('calendarista_handler'=>'add_to_calendar', 'orderId'=>$result[0]->orderId), site_url());
			$google = Calendarista_AddToCalendarButtonHelper::google($result);
			?>
				<p class="calendarista-typography--subtitle4 calendarista-add-to-calendar-line">
					<?php echo $stringResources['ADD_APPOINTMENT_TO_CALENDAR'] ?>:&nbsp;&nbsp;
					<a href="<?php echo esc_url($handlerUrl) ?>" target="_blank" class="calendarista-add-to-calendar-link">
					<?php echo $stringResources['ICAL'] ?>
					</a>-
					<a href="<?php echo esc_url($handlerUrl) ?>" target="_blank" class="calendarista-add-to-calendar-link">
					<?php echo $stringResources['OUTLOOK'] ?>
					</a>
					<?php if(count($result) === 1): ?>
					-
					<a href="<?php echo esc_url($google) ?>" target="_blank" class="calendarista-add-to-calendar-link">
					<?php echo $stringResources['GOOGLE'] ?>
					</a>
					<?php endif; ?>
				</p>
			<?php 
		}
	}
	public function checkout($invoiceId, $orderIsValid = true){
		$this->invoiceId = $invoiceId;
		$this->clearViewState();
		if(!$orderIsValid){
			$_POST = array();
			return;
		}
		$this->displayAlert = true;
	}
	public function renderCheckoutAlert(){
		if(!$this->displayAlert){
			return;
		}
		$failureMessage = apply_filters('calendarista_checkout_payment_failure_message', null);
	?>
		<?php if($this->invoiceId):?>
			<div class="alert alert-success calendarista-alert alert-dismissible calendarista-alert-confirmation" role="alert" id="CAL<?php echo $this->projectId ?>">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<div class="calendarista-typography--subtitle3 calendarista-thankyou-line">
					<?php echo esc_html($this->stringResources['BOOKING_THANKYOU']) ?>
				</div>
				<p class="calendarista-typography--subtitle4 calendarista-booking-created-line">
					<?php echo esc_html($this->stringResources['BOOKING_CREATED']) ?>
				</p>
				<p class="calendarista-typography--subtitle4 calendarista-invoice-number-line">
					<?php echo sprintf($this->stringResources['BOOKING_CREATED_INVOICE_NUMBER'], $this->invoiceId) ?>
				</p>
				<?php self::addToCalendarButtonRender($this->invoiceId, $this->stringResources, $this->generalSetting->displayAddToCalendarOption); ?>
			</div>
		<?php else: ?>
			<!-- Sometimes the notification from Paypal may take longer and hence it might seem as though the payment was not successful. 
			This is why we do not report payment failed but ask customer to check their email. -->
			<div class="alert alert-warning calendarista-alert alert-dismissible calendarista-alert-confirmation" id="CAL<?php echo $this->projectId ?>">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<div class="calendarista-typography--subtitle3">
					<?php echo esc_html($this->stringResources['BOOKING_THANKYOU']) ?>
				</div>
				<div class="calendarista-typography--subtitle4">
					<?php echo $failureMessage ? $failureMessage : $this->stringResources['BOOKING_PAYMENT_FAILED']; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php
	}
	public function render(){
	?>
	<div id="<?php echo $this->uniqueId ?>" class="calendarista card calendarista-typography">
		<?php if(!$this->serviceThumbnailView && $this->project->previewUrl):?>
		<div style="<?php echo $this->project->previewImageHeight > 0 ? sprintf('overflow: hidden; max-height: %spx;', $this->project->previewImageHeight) : '' ?>">
			<img src="<?php echo esc_url($this->project->previewUrl) ?>" class="card-img-top" alt="...">
		</div>
		<?php endif; ?>
		<div class="calendarista-wizard card">
			<div class="card-header">
				<div class="col-xl-12 calendarista-navbar-container">
					<div id="navbar_<?php echo $this->uniqueId ?>">
						<ol class="nav nav-tabs calendarista-wizard-nav card-header-tabs calendarista-typography--caption1">
						<?php foreach($this->steps as $step):?>
						  <li class="nav-item">
							<a href="#" class="<?php echo $this->selectedStepName === $step['name'] ? 'nav-link active' : 'nav-link' ?><?php echo $step['counter'] < $this->selectedStep ? ' nav-link-enabled' : '' ?>" data-calendarista-index="<?php echo $step['counter']?>">
							  <span class="calendarista-nav-label"><?php echo esc_html($step['label']) ?></span>
							</a>
						  </li>
						  <?php endforeach; ?>
						</ol>
						<?php if($this->generalSetting->displayStepsMobileView): ?>
						<select id="dropdown_<?php echo $this->uniqueId ?>" class="form-select hide">
							<?php foreach($this->steps as $i=>$step):?>
							  <option value="<?php echo $i ?>" data-calendarista-index="<?php echo $step['counter']?>"
								<?php echo $this->selectedStepName === $step['name'] ? 'selected' : '' ?>>
								<?php echo esc_html($step['label']) ?>
							  </option>
							<?php endforeach; ?>
						</select>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="calendarista-wizard-section-block calendarista-wizard-section-block-include-thumb <?php echo $this->availabilityPreviewUrl ? 'calendarista-wizard-section-thumbnail' : 'calendarista-wizard-section-no-thumbnail' ?>">
					<div class="calendarista-wizard-section-block-thumb" style="<?php echo esc_attr($this->backgroundImageCss) ?>"></div>
					<div class="container">
						<?php $this->renderCheckoutAlert(); ?>
						<?php if($this->selectedStepName === 'checkout' && 
							in_array($this->project->paymentsMode, array(1/*online mode*/, 2/*online and offline mode*/, 3/*woocommerce*/))):?>
							<?php $this->embedPaymentOperators(); ?>
						<?php endif; ?>
						<form id="form-<?php echo $this->uniqueId ?>" data-parsley-validate action="<?php echo esc_url($this->confirmUrl) ?>" method="post" 
							data-parsley-inputs="input, textarea, select, hidden" 
							data-parsley-excluded="input[type=button], input[type=submit], input[type=reset]">
							<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
							<input type="hidden" name="projectId" value="<?php echo $this->projectId ?>">
							<input type="hidden" name="calendarMode" value="<?php echo $this->project->calendarMode ?>">
							<input type="hidden" name="postbackStep" value="<?php echo esc_attr($this->selectedStepName) ?>"> 
							<input type="hidden" name="availabilityPreviewUrl" value="<?php echo esc_url($this->availabilityPreviewUrl) ?>">
							<input type="hidden" name="serviceThumbnailView" value="<?php echo $this->serviceThumbnailView ? 1 : 0 ?>">
							<input type="hidden" name="availabilityThumbnailView" value="<?php echo $this->availabilityThumbnailView ? 1 : 0 ?>">
							<input type="hidden" name="__viewstate" value="<?php echo $this->stateBag ?>">
							<input type="hidden" name="stagingId" value="<?php echo $this->stagingId ?>">
							<input type="hidden" name="requestId" value="<?php echo $this->requestId ?>">
							<input type="hidden" name="projectList" value="<?php echo implode(',', $this->projectList) ?>">
							<input type="hidden" name="enableMultipleBooking" value="<?php echo $this->enableMultipleBooking ?>">
							<input type="hidden" name="calendarista_cart" value="<?php echo implode(',', Calendarista_AvailabilityHelper::getWooCartItems()) ?>"/>
							<?php switch($this->selectedStepName){
									case 'calendar':
										new Calendarista_BookingCalendarFieldsTmpl();
									break;
									case 'map':
										new Calendarista_BookingMapTmpl();
									break;
									case 'optionals':
										new Calendarista_BookingOptionalsTmpl();
									break;
									case 'form':
										new Calendarista_BookingCustomFormFieldsTmpl();
									break;
									case 'checkout':
										new Calendarista_BookingCheckoutTmpl();
									break;
								}		
							?>
							<div class="clearfix"></div>
							<?php if($this->selectedStepName !== 'checkout'):?>
							<div class="col-xl-12 calendarista-row-single calendarista-cost-summary-placeholder"></div>
							<?php endif; ?>
							<div class="col-xl-12 calendarista-row-double">
								<div class="row">
									<div class="col-1">
										<div id="spinner_<?php echo $this->uniqueId ?>" class="spinner-border text-primary calendarista-invisible" role="status">
										  <span class="sr-only"><?php echo esc_html($this->stringResources['AJAX_SPINNER'])?></span>
										</div>
									</div>
									<div class="col-11 calendarista-align-right">
										<?php if($this->prev > 0):?>
											<button id="<?php echo $this->uniqueId?>_prev" type="button" name="prev" class="btn btn-primary calendarista-typography--button calendarista-btn-prev" value="<?php echo $this->prev ?>"><i class="fa fa-chevron-left"></i>&nbsp;<?php echo esc_html($this->stringResources['PREV_BUTTON'])?></button>
										<?php endif; ?>
										<?php if($this->next <= $this->counter):?>
											<button id="<?php echo $this->uniqueId?>_next" type="button" name="next" class="btn btn-primary calendarista-typography--button calendarista-btn-next" value="<?php echo $this->next ?>"><?php echo esc_html($this->stringResources['NEXT_BUTTON'])?>&nbsp;<i class="fa fa-chevron-right"></i></button>
										<?php elseif($this->next === ($this->counter+1)):?>
											<button id="<?php echo $this->uniqueId?>_booknow" type="button" name="booknow" value="<?php echo $this->projectId ?>" class="btn btn-primary calendarista-typography--button calendarista-btn-booknow"><?php echo esc_html($this->stringResources['CONCLUDE_BOOKING_BUTTON'])?></button>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</form>
						<div class="clearfix"></div>
						<script type="text/javascript">
						(function(){
							"use strict";
							function init(){
								new Calendarista.wizard({
									'id': '<?php echo $this->uniqueId?>'
									, 'wizardAction': 'calendarista_wizard'
									, 'prevIndex': <?php echo $this->prev ?>
									, 'nextIndex': <?php echo $this->next ?>
									, 'stepCounter': <?php echo $this->counter ?>
									, 'appointment': 0
									, 'steps': <?php echo wp_json_encode($this->steps) ?>
									, 'selectedStepName': '<?php echo $this->selectedStepName ?>'
									, 'selectedStepIndex': <?php echo $this->selectedStep ?>
								});
							}
							<?php if($this->notAjaxRequest):?>
							if (window.addEventListener){
							  window.addEventListener('load', onload, false); 
							} else if (window.attachEvent){
							  window.attachEvent('onload', onload);
							}
							function onload(e){
								init();
							}
							<?php else: ?>
							init();
							<?php endif; ?>
						})();
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	}
}?>

