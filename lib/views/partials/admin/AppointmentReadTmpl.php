<?php
class Calendarista_AppointmentReadTmpl extends Calendarista_ViewBase{
	public $viewState;
	public $status;
	public $orderId;
	public $projectId;
	public $availabilityId;
	public $template;
	public $stringResources;
	public $userSummaryData;
	public $invoiceId;
	public $invoiceUrl;
	public $costHelper;
	public $paymentOperator;
	public $order;
	public function __construct(){
		new Calendarista_AppointmentsController(
			null
			, array($this, 'updateAppointmentStatus')
		);
		$this->viewState = $this->getViewState();
		$this->projectId = $this->getViewStateValue('projectId');
		$this->availabilityId = (int)$this->getPostValue('availabilityId');
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->status = (int)$this->getViewStateValue('status');
		$this->viewState['enableDateRemoveButton'] = false;
		$this->costHelper = new Calendarista_CostHelper($this->viewState);
		if((array_key_exists('email', $this->viewState) && $this->getViewStateValue('email')) && 
				(array_key_exists('name', $this->viewState) && $this->getViewStateValue('name'))){
			$this->userSummaryData = implode(', ', array($this->sanitize($this->getViewStateValue('name')), $this->getViewStateValue('email')));
		}
		$styleRepository = new Calendarista_StyleRepository();
		$style = $styleRepository->readByProject($this->projectId);
		if(!$style){
			$style = new Calendarista_Style(array());
		}
		$this->template = $style->bookingSummaryTemplate;
		if(!class_exists('Mustache_Engine')){
			require_once CALENDARISTA_MUSTACHE . 'Autoloader.php';
			Mustache_Autoloader::register();
		}
		$this->mustacheEngine = new Mustache_Engine();
		$this->invoiceId = $this->getViewStateValue('invoiceId');
		$this->invoiceUrl = admin_url() . 'admin.php?page=calendarista-sales&controller=calendarista_sales&invoiceId=' . $this->invoiceId;
		$this->paymentOperator = $this->getViewStateValue('paymentOperator');
		$orderRepo = new Calendarista_OrderRepository();
		$this->order = $orderRepo->read($this->orderId);
		$this->totalAmount = $this->order->totalAmount;
		$this->deposit = $this->order->deposit;
		$this->balance = $this->order->balance;
		$this->render();
	}
	public function getViewState(){
		$this->orderId = (int)$this->getPostValue('orderId');
		$itemId = isset($_POST['bookedAvailabilityId']) ? (int)$_POST['bookedAvailabilityId'] : null;
		$result = Calendarista_AppointmentHelper::getAppointmentViewState($itemId, $this->orderId, $this->availabilityId);
		$viewState = array();
		foreach($result as $value){
			$viewState = array_merge($viewState, $value);
		}
		return $viewState;
	}
	public function getViewStateValue($value){
		if(isset($this->viewState[$value])){
			return $this->viewState[$value];
		}
		return null;
	}
	public function updateAppointmentStatus($result){}
	public function getDynamicFields(){
		$dynamicFieldPricingRepo = new Calendarista_DynamicFieldPricingRepository();
		$items = $this->getViewStateValue('dynamicFields');
		if(is_array($items)){
			$result = array();
			foreach($items as $item){
				$flag = false;
				$priceList = $dynamicFieldPricingRepo->readByDynamicFieldId($item['id']);
				if(is_array($priceList) && count($priceList) > 0){
					foreach($priceList as $price){
						if($item['value'] == $price['fieldValue']){
							array_push($result, sprintf('<tr class="calendarista-dynamic-fields"><td class="calendarista-typography--caption1">%s —%s</td><td class="calendarista-typography--caption1 text-end">%s</td></tr>'
								, $item['label'], $item['value'], Calendarista_MoneyHelper::toLongCurrency($price['cost'])));
							$flag = true;
							break;
						}
					}
				}
				if(!$flag){
					array_push($result, sprintf('<tr class="calendarista-dynamic-fields"><td colspan="2" class="calendarista-typography--caption1">%s —%s</td></tr>'
						, $item['label'], $item['value']));
				}
			}
			return implode('', $result);
		}
		return null;
	}
	protected function getWaypoints(){
		$waypoints = $this->getViewStateValue('waypoints');
		if($waypoints){
			$result = array();
			if(count($waypoints) > 0){
				foreach($waypoints as $w){
					$waypoint = (array)$w;
					array_push($result, sprintf('<tr class="calendarista-waypoint"><td class="calendarista-typography--caption1">%s</td><td class="calendarista-typography--caption1 text-end">%s</td></tr>', $waypoint['address'], $this->stringResources['MAP_WAYPOINT_LABEL']));
				}
			}
			return sprintf('<tr class="calendarista-waypoint">%s</tr>', implode('', $result));
		}
		return null;
	}
	protected function getCustomFormElements(){
		$formElements = $this->getViewStateValue('formelements');
		$result = array();
		if($formElements){
			foreach($formElements as $formElement){
				array_push($result, sprintf('<strong>%s</strong> —<i>%s</i>', $formElement['label'], $formElement['value']));
			}
		}
		return implode('<br>', $result);
	}
	public function summary(){
		$fromAddress = $this->getViewStateValue('fromAddress');
		$toAddress = $this->getViewStateValue('toAddress');
		//distance & unit type is also retrieved from $_POST when the current view is the BookingMapTmpl
		//so special treatment, need to cast manually
		$distance = (float)$this->getViewStateValue('distance');
		$unitType = (int)$this->getViewStateValue('unitType');
		$duration = (int)$this->getViewStateValue('duration');
		$bookingDate = $this->costHelper->dateRange;
		if(!$bookingDate){
			return;
		}
		$waypoints =  $this->getWaypoints();
		$customFormElements = $this->getCustomFormElements();
		$timeUnitLabels = Calendarista_StringResources::getTimeUnitLabels($this->stringResources);
		$serviceName = $this->costHelper->project ? $this->costHelper->project->name : '';
		$availabilityName = $this->costHelper->availability ? $this->costHelper->availability->name : '';
		$nightStayLabel = $this->costHelper->nights > 1 ? 
				sprintf($this->stringResources['BOOKING_NIGHT_SINGULAR_LABEL'], $this->costHelper->nights) :
				sprintf($this->stringResources['BOOKING_NIGHT_PLURAL_LABEL'], $this->costHelper->nights);
		$dynamicFields = $this->getDynamicFields();
		$baseCostLabel = $this->costHelper->getBaseCostSummary();
		$personalize = apply_filters('calendarista_cost_summary', array('hasCustomCharge'=>false, 'customChargeLabel'=>null, 'customChargeValue'=>null), $this->viewState);
		return $this->mustacheEngine->render($this->template, array(
			'booking_summary_label'=>$this->stringResources['BOOKING_SUMMARY_LABEL']
			, 'if_has_nights'=>$this->costHelper->nights > 0
			, 'nights_label'=>$nightStayLabel
			, 'service_name'=>$serviceName
			, 'availability_name'=>$availabilityName
			, 'booking_date'=>$bookingDate
			, 'if_has_seats'=>$this->costHelper->seats > 1
			, 'seats_summary'=>sprintf($this->stringResources['BOOKING_SEATS_SUMMARY'], $this->costHelper->seats)
			, 'if_has_from_address'=>isset($fromAddress)
			, 'from_address'=>$fromAddress
			, 'from_address_label'=>$this->stringResources['MAP_DEPARTURE_LABEL']
			, 'if_has_waypoints'=>isset($waypoints)
			, 'stops'=>$waypoints
			, 'waypoint_label'=>$this->stringResources['MAP_WAYPOINT_LABEL']
			, 'if_has_to_address'=>isset($toAddress)
			, 'to_address'=>$toAddress
			, 'to_address_label'=> $this->stringResources['MAP_DESTINATION_LABEL']
			, 'if_has_distance'=>isset($distance) && $distance > 0
			, 'distance'=>Calendarista_MoneyHelper::toDouble($distance)
			, 'distance_label'=>$this->stringResources['MAP_DISTANCE_LABEL']
			, 'if_has_duration'=>isset($duration) && $duration > 0
			, 'duration'=>Calendarista_TimeHelper::secondsToTime($duration, $timeUnitLabels)
			, 'duration_label'=>$this->stringResources['MAP_DURATION_LABEL']
			, 'unitType'=>$unitType === 0 ? 'km' : 'miles'
			, 'if_has_optionals'=>count($this->costHelper->optionals) > 0
			, 'optionals'=>implode('', $this->costHelper->optionals)
			, 'if_has_customer_name_email'=>isset($this->userSummaryData)
			, 'customer_name_email'=>$this->userSummaryData
			, 'if_has_custom_form_fields'=>strlen($customFormElements) > 0
			, 'custom_form_fields'=>$customFormElements
			, 'if_has_subtotal'=>$this->costHelper->subTotalAmount > 0 && $this->costHelper->subTotalAmount != $this->costHelper->totalAmount
			, 'subtotal_amount'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->subTotalAmount)
			, 'subtotal_amount_label'=>$this->stringResources['SUBTOTAL']
			, 'if_has_total_amount'=>($this->totalAmount > 0 || ($this->totalAmount == 0 && $this->costHelper->couponHelper->discount > 0))
			, 'total_amount'=>Calendarista_MoneyHelper::toLongCurrency($this->totalAmount)
			, 'total_amount_label'=>$this->stringResources['TOTAL']
			, 'if_has_discount'=>$this->costHelper->couponHelper->discountToString() !== null
			, 'discount'=>$this->costHelper->couponHelper->discountToString()
			, 'applied'=>$this->stringResources['APPLIED']
			, 'discount_label'=>$this->stringResources['DISCOUNT']
			, 'discount'=>$this->costHelper->couponHelper->discountToString()
			, 'if_has_tax'=>$this->costHelper->tax > 0
			, 'tax_label'=>$this->stringResources['TAX_LABEL']
			, 'tax'=>$this->costHelper->tax
			, 'tax_amount'=>$this->costHelper->taxAmount
			, 'if_has_deposit'=>!$this->order->upfrontPayment && $this->deposit > 0
			, 'deposit_label'=>$this->stringResources['BOOKING_DEPOSIT_LABEL']
			, 'balance_label'=>$this->stringResources['BALANCE_LABEL']
			, 'balance'=>Calendarista_MoneyHelper::toLongCurrency($this->balance)
			, 'balance_pay_on_arrival'=>$this->stringResources['BALANCE']
			, 'base_cost_label'=>$baseCostLabel
			, 'if_has_base_cost'=>$baseCostLabel && $this->costHelper->selectedPeriodCost > 0
			, 'base_cost'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->selectedPeriodCost)
			, 'if_has_dynamic_fields'=>$dynamicFields !== null
			, 'dynamic_fields'=>$dynamicFields
			, 'if_has_selected_date_list'=>count($this->costHelper->selectedDateList) > 0
			, 'selected_date_list'=>$this->costHelper->getSelectedDateList()
			, 'if_has_custom_charge'=>$personalize['hasCustomCharge']
			, 'custom_charge_label'=>$personalize['customChargeLabel']
			, 'custom_charge_amount'=>Calendarista_MoneyHelper::toLongCurrency($personalize['customChargeValue'])
			, 'if_has_multiple_availability'=>$this->costHelper->hasMultipleAvailabilities
			, 'availability_list'=>sprintf($this->stringResources['MULTIPLE_AVAILABILITY_LIST'], implode(', ', $this->costHelper->availabilityNames))
			, 'total_hours'=>$this->costHelper->totalHours > 0 ? $this->costHelper->totalHours : null
			, 'total_minutes'=>$this->costHelper->totalMinutes > 0 ? $this->costHelper->totalMinutes : null
			, 'if_has_extend_next_day'=>$this->costHelper->extendsNextDay
			, 'extend_next_day_message'=>$this->stringResources['BOOKING_EXTEND_NEXT_DAY_LABEL']
			, 'if_include_total_time'=>$this->costHelper->totalHours > 0 || $this->costHelper->totalMinutes > 0
			, 'total_time_label'=>$this->stringResources['BOOKING_TOTAL_HOURS_MINUTES_LABEL']
			, 'total_time'=>$this->costHelper->getTotalTime()
			, 'if_paid_upfront_full_amount'=>$this->order->upfrontPayment
			, 'upfront_payment_message'=>$this->getUpfrontPaymentMessage()
			, 'upfront_payment_total'=>$this->getUpfrontTotalAmount()
		));
	}
	public function getUpfrontPaymentMessage(){
		if($this->order->upfrontPayment){
			return sprintf('<span class="calendarista-discount-msg">%s</span>'
				, $this->stringResources['PAYMENT_FULL_AMOUNT_PAID_LABEL']
			);
		}
	}
	public function getUpfrontTotalAmount(){
		if($this->order->upfrontPayment){
			return sprintf('<span class="calendarista-strikethrough">%s</span>&nbsp;&nbsp;<span class="calendarista-discounted-full-amount">%s</span>'
				, Calendarista_MoneyHelper::toShortCurrency($this->costHelper->totalAmountBeforeDeposit)
				, Calendarista_MoneyHelper::toLongCurrency($this->order->totalAmount));
		}
	}
	public function render(){
	?>
	<div  class="calendarista">
		<?php if($this->status === 1):?>
			<div class="alert alert-success" role="alert">
				<strong><?php esc_html_e('Note', 'calendarista') ?>!</strong>&nbsp;<?php esc_html_e('This is a confirmed appointment.', 'calendarista') ?>
			</div>
		<?php elseif($this->status === 2):?>
			<div class="alert alert-danger" role="alert">
				<strong><?php esc_html_e('Note', 'calendarista') ?>!</strong>&nbsp;<?php esc_html_e('This is a cancelled appointment.', 'calendarista') ?>
			</div>
		<?php endif;?>
		<?php if($this->invoiceId):?>
			<p class="alert alert-info calendarista-alert">
				<?php esc_html_e('Invoice ID', 'calendarista') ?>:&nbsp;
				<?php if($this->costHelper->totalAmount > 0): ?>
				<a href="<?php echo esc_url($this->invoiceUrl) ?>" target="_blank"><?php echo esc_html($this->invoiceId) ?></a>
				<?php else: ?>
				<?php echo $this->invoiceId; ?>
				<?php endif; ?>
			</p>
		<?php endif;?>
		<?php if($this->paymentOperator): ?>
		<p class="alert alert-info calendarista-alert"> 
		<?php echo sprintf(__('Payment via: %s', 'calendarista'), $this->paymentOperator) ?>
		</p>
		<?php endif; ?>
		<?php echo $this->summary() ?>
		<script type="text/javascript">
		(function(){
			function init(){
				
			}
			init();
		})();
		</script>
	</div>
	<?php
	}
}?>

