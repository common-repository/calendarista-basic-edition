<?php
class Calendarista_BookingCheckoutTmpl extends Calendarista_TemplateBase{
	public $project;
	public $costHelper;
	public $paymentOperators;
	public $resources;
	public $hasMultiplePaymentOptions;
	public $flag = 0;
	public function __construct(){
		parent::__construct();
		$repo = new Calendarista_ProjectRepository();
		$this->project = $repo->read($this->projectId);
		$this->costHelper = new Calendarista_CostHelper($this->viewState);
		if(!($this->costHelper->totalAmount > 0)){
			$this->project->paymentsMode = -1;
		}
		if(in_array($this->project->paymentsMode, array(1, 2, 3))){
			$this->paymentOperators = $this->getPaymentOperators();
		}
		$this->hasMultiplePaymentOptions = $this->getPaymentOptionsCount();
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->render();
	}
	public function getPaymentOperators(){
		$result = array();
		$repo = new Calendarista_PaymentSettingRepository();
		$result = $repo->readAll();
		if(has_filter('calendarista_payment_operators')) {
			$result = apply_filters('calendarista_payment_operators', $result);
		}
		usort($result, array($this, 'sortByIndex'));
		return $result;
	}
	public function getPaymentOptionsCount(){
		$i = 0;
		if($this->paymentOperators){
			foreach($this->paymentOperators as $po){
				if($po['enabled']){
					$i++;
				}
			}
		}
		if($this->project->paymentsMode === 2/*online & offline*/){
			$i++;
		}
		return $i > 1;
	}
	public function getByOperatorObject($args){
		//get item through object to get title translation
		$result = $args;
		switch($args['paymentOperator']){
			case 0:
				$setting = new Calendarista_PaypalSetting($args);
				$result = $setting->toArray();
			break;
		}
		return $result;
	}
	public function paymentOperatorEnabled($operators){
		if($this->paymentOperators){
			foreach($this->paymentOperators as $po){
				if($po['enabled'] && in_array($po['paymentOperator'], $operators)){
					return true;
				}
			}
		}
		return false;
	}
	public function sortByIndex($a, $b){
		return ((int)$a['orderIndex'] <=> (int)$b['orderIndex']);
	}
	public function getDeposit(){
		return Calendarista_MoneyHelper::toShortCurrency($this->costHelper->totalAmount);
	}
	public function getTotalAmountBeforeDiscount(){
		$fullAmountDiscount = $this->costHelper->availability->fullAmountDiscount;
		if($fullAmountDiscount && $this->costHelper->seats > 1){
			$fullAmountDiscount = $fullAmountDiscount * $this->costHelper->seats;
		}
		if($fullAmountDiscount > 0 && 
			$this->costHelper->totalAmountBeforeDeposit > $fullAmountDiscount){
			$discountedValue = $this->costHelper->totalAmountBeforeDeposit - $fullAmountDiscount;
			return sprintf('<span class="calendarista-strikethrough">%s</span>&nbsp;&nbsp;<span class="calendarista-discounted-full-amount"><strong>%s</strong></span>&nbsp;&nbsp;<span class="calendarista-discount-msg">%s</span>'
				, Calendarista_MoneyHelper::toShortCurrency($this->costHelper->totalAmountBeforeDeposit)
				, Calendarista_MoneyHelper::toShortCurrency($discountedValue)
				, sprintf($this->stringResources['PAYMENT_FULL_AMOUNT_DISCOUNT_LABEL'], Calendarista_MoneyHelper::toShortCurrency($fullAmountDiscount))
			);
		}
		return Calendarista_MoneyHelper::toShortCurrency($this->costHelper->totalAmountBeforeDeposit);
	}
	public function render(){
	?>
	<input type="hidden" name="controller" value="calendarista_checkout"/>
	<input type="hidden" name="paymentsMode" value="<?php echo $this->project->paymentsMode; ?>"/>
	<input type="hidden" name="originalCost" value="<?php echo $this->costHelper->getTotalAmountAfterTaxBeforeDiscount() ?>"/>
	<div class="calendarista_ambush">
		<?php esc_html_e('If you see this textbox, leave it blank.', 'calendarista') ?>
		<input type="text" name="calendarista_ambush" value="" />
	 </div>
	<div class="col-xl-12">
		<div class="calendarista-row-single calendarista-cost-summary-placeholder">
			<?php new Calendarista_BookingCostSummaryTmpl(true, false/*enableDateRemoveButton*/);?>
		</div>
	</div>
	<?php if($this->project->enableCoupons):?>
	<div class="col-xl-12 calendarista-row-double">
		<div class="form-group">
			<div class="input-group">
				<input id="coupon_<?php echo $this->uniqueId ?>"
					name="coupon"
					data-parsley-trigger="change" 
					data-parsley-required="true"
					data-parsley-errors-container="#coupon_error_container_<?php echo $this->uniqueId ?>" 
					data-calendarista-coupon-invalid-error="<?php echo esc_html($this->stringResources['COUPON_INVALID_ERROR'])?>"
					data-calendarista-coupon-minimum-amount-error="<?php echo esc_html($this->stringResources['COUPON_MINIMUM_AMOUNT_ERROR'])?>"
					placeholder="<?php echo esc_html($this->stringResources['COUPON']) ?>"
					class="form-control" />
				<button type="button" name="couponButton" class="btn btn-outline-secondary calendarista-typography--button" title="<?php echo esc_html($this->stringResources['BOOKING_REDEEM_COUPON'])?>">
					<i class="fa fa-check-circle"></i>
				</button>
				<button type="button" name="couponResetButton" class="btn btn-outline-secondary calendarista-typography--button" title="<?php echo esc_html($this->stringResources['BOOKING_RESET_COUPON'])?>"><i class="fa fa-undo"></i></button>
			</div>
			<div id="coupon_error_container_<?php echo $this->uniqueId ?>" class="calendarista-typography--caption1"></div>
		</div>
	</div>
	<?php endif; ?>
	<div class="calendarista-row-double"></div>
	<?php if($this->costHelper->availability->deposit && $this->costHelper->availability->enableFullAmountOrDeposit): ?>
	<div class="col-xl-12">
		<div class="alert alert-info" role="alert">
			<div class="form-check">
				<label for="<?php echo $this->uniqueId ?>_deposit" class="form-check-label calendarista-typography--caption1">
					<input 
						id="<?php echo $this->uniqueId ?>_deposit"
						name="upfrontPayment"
						data-parsley-trigger="change" 
						data-parsley-required="true"
						data-parsley-errors-container="#deposit_error_container_<?php echo $this->uniqueId ?>" 
						data-parsley-error-message="<?php echo esc_html($this->stringResources['DEPOSIT_METHOD_REQUIRED_ERROR']) ?>"
						type="radio"
						class="form-check-input calendarista_parsley_validated"
						data-calendarista-upfront="0"
						value="0">
					<strong><?php echo esc_html($this->stringResources['PAYMENT_OPTIONALLY_DEPOSIT_LABEL'])?></strong>
					<?php echo $this->getDeposit(); ?>
				</label>
			</div>
			<div class="form-check">
				<label for="<?php echo $this->uniqueId ?>_upfrontpayment" class="form-check-label calendarista-typography--caption1">
					<input 
						id="<?php echo $this->uniqueId ?>_upfrontpayment"
						name="upfrontPayment"
						data-parsley-trigger="change" 
						data-parsley-required="true"
						data-parsley-errors-container="#deposit_error_container_<?php echo $this->uniqueId ?>" 
						data-parsley-error-message="<?php echo esc_html($this->stringResources['DEPOSIT_METHOD_REQUIRED_ERROR']) ?>"
						type="radio"
						class="form-check-input calendarista_parsley_validated"
						data-calendarista-upfront="1"
						value="<?php echo $this->costHelper->totalAmountBeforeDeposit ?>">
					<strong><?php echo esc_html($this->stringResources['PAYMENT_OPTIONALLY_FULL_AMOUNT_LABEL'])?></strong>
					<?php echo $this->getTotalAmountBeforeDiscount(); ?>
				</label>
			</div>
			<div id="deposit_error_container_<?php echo $this->uniqueId ?>" class="calendarista-typography--caption1"></div>
		</div>
	</div>
	<div class="calendarista-row-double"></div>
	<?php endif; ?>
	<?php if(in_array($this->project->paymentsMode, array(1/*online mode*/, 2/*online and offline mode*/, 3/*woocommerce*/))):?>
		<?php foreach($this->paymentOperators as $key=>$value):?>
			<?php if(!(bool)$value['enabled']){continue;}?>
			<?php $value = $this->getByOperatorObject($value); ?>
			<div class="col-xl-12">
				<div class="<?php echo $this->hasMultiplePaymentOptions ? 'form-check' : '' ?>">
					<?php if(in_array($this->project->paymentsMode, array(3/*woocommerce*/))):?>
						<div class="alert alert-warning" role="alert">
						<i class="fa fa-exclamation-circle fa-lg"></i>
					<?php endif; ?>
					<label for="<?php echo $this->uniqueId ?>_payment_operator_<?php echo $value['id'] ?>" class="form-check-label calendarista-typography--caption1">
						<input 
						id="<?php echo $this->uniqueId ?>_payment_operator_<?php echo $value['id'] ?>"
						<?php if($this->hasMultiplePaymentOptions):?>
						type="radio" 
						class="form-check-input"
						<?php else: ?>
						type="hidden"
						<?php endif; ?>
						name="paymentMethod"
						value="#payment-operator-<?php echo sprintf('%d-%d', $value['id'], $this->projectId) ?>"
						<?php if((bool)$value['enableInlineForm']):?> 
						data-calendarista-inline-form="true" 
						<?php endif;?>
						<?php if($this->flag === 0 && $this->costHelper->totalAmount > 0):?>checked<?php endif;?>
						<?php if(!($this->costHelper->totalAmount > 0)):?>disabled<?php endif;?>>
						<?php echo $value['title'] ?>
						<?php if(isset($value['imageUrl'])): ?>
							<img src="<?php echo esc_url($value['imageUrl']) ?>">
						<?php endif; ?>
					</label>
					<?php if(in_array($this->project->paymentsMode, array(3/*woocommerce*/))):?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php $this->flag++; ?>
		<?php endforeach;?>
		<?php if($this->paymentOperatorEnabled(array(Calendarista_PaymentOperator::STRIPE))):?>
			<?php new Calendarista_PaymentStripeFormTmpl();?>
		<?php endif;?>
	<?php endif; ?>
	<?php if(in_array($this->project->paymentsMode, array(0/*offline*/, 2/*online & offline mode*/))): ?>
		<div class="col-xl-12">
			<div class="<?php echo $this->hasMultiplePaymentOptions ? 'form-check' : 'form-group' ?>">
				<?php if(!$this->hasMultiplePaymentOptions): ?>
				<div class="alert alert-warning" role="alert">
				<i class="fa fa-exclamation-circle fa-lg"></i>
				<?php endif; ?>
				<label for="<?php echo $this->uniqueId ?>_payment_operator" class="<?php echo $this->hasMultiplePaymentOptions ? 'form-check-label ' : 'form-text' ?> calendarista-typography--caption1">
					<input 
						id="<?php echo $this->uniqueId ?>_payment_operator"
						<?php if($this->hasMultiplePaymentOptions):?>
						type="radio" 
						class="form-check-input"
						<?php else: ?>
						type="hidden"
						<?php endif; ?>
						name="paymentMethod"
						value="-1">
					<?php echo $this->costHelper->totalAmount > 0 ? $this->stringResources['PAYMENT_METHOD_BANK_OR_LOCAL_LABEL'] : '' ?>
				</label>
				<?php if(!$this->hasMultiplePaymentOptions): ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php do_action('calendarista_checkout_validate', $this->viewState); ?>
	<?php do_action('calendarista_checkout_step', $this->uniqueId); ?>
	<script type="text/javascript">
	(function($){
		"use strict";
		var Calendarista = window['Calendarista'] ? window['Calendarista'] : function(){};
		Calendarista.checkoutStep = function(options){
			this.init();
		}
		Calendarista.checkoutStep.prototype.init = function(){
			var context = this
				, checkout = new Calendarista.checkout({
				'id': '<?php echo $this->uniqueId?>'
				, 'projectId': <?php echo $this->projectId ?>
				, 'ajaxUrl': '<?php echo $this->ajaxUrl ?>'
			});
			this.$checkoutButton = checkout.$root.find('button[name="booknow"]');
			this.$prevButton = checkout.$root.find('button[name="prev"]');
			this.$paymentMethod = checkout.$root.find('input[name="paymentMethod"]');
			this.$form = checkout.$root.find(this.$paymentMethod.val());
			this.$panels = checkout.$root.find('.calendarista-collapsable');
			this.$panel = checkout.$root.find(this.$form.attr('data-calendarista-operator-panel'));
			this.$mainForm = checkout.$root.find('#form-<?php echo $this->uniqueId?>');
			this.$cardErrorElem = checkout.$root.find('#card_error_container_<?php echo $this->uniqueId ?>');
			this.$deposit = checkout.$root.find('#<?php echo $this->uniqueId ?>_deposit');
			this.$upfrontPayment = checkout.$root.find('#<?php echo $this->uniqueId ?>_upfrontpayment');
			if (typeof Calendarista.Stripe != 'undefined') {
				this.stripe = new Calendarista.Stripe();
			}
			if(this.$upfrontPayment.length > 0){
				this.$upfrontPayment.on('change', function(e){
					context.upfrontPaymentChanged(context.$upfrontPayment[0], checkout.$root);
				});
			}
			if(this.$deposit.length > 0){
				this.$deposit.on('change', function(e){
					context.upfrontPaymentChanged(context.$deposit[0], checkout.$root);
				});
			}
			this.$checkoutButton.on('click', function(e){
				var selector = '-1'
					, $form
					, operator
					, twocheckout
					, $paymentMethods
					, $paymentMethod; 
				e.preventDefault();
				context.$cardErrorElem.addClass('hide');
				if(!Calendarista.wizard.isValid(checkout.$root)){
					return false;
				}
				context.$checkoutButton.prop('disabled', true).addClass('ui-state-disabled');
				context.$prevButton.prop('disabled', true).addClass('ui-state-disabled');
				$paymentMethod = checkout.$root.find('input[name="paymentMethod"]:checked');
				if($paymentMethod.length === 0){
					$paymentMethods = checkout.$root.find('input[name="paymentMethod"]:not(:disabled)');
					$.each($paymentMethods, function(i, elem){
						if(elem.checked){
							$paymentMethod = $(elem);
							return false;
						}
					});
				}
				if($paymentMethod.length > 0){
					selector = $paymentMethod.val();
				}else if($paymentMethods && $paymentMethods.length > 0){
					selector = $($paymentMethods[0]).val();
				}
				if ('scrollRestoration' in history) {
				  history.scrollRestoration = 'manual';
				}
				if(selector){
					operator = 'offline';
					if(selector !== '-1'){
						$form = checkout.$root.find(selector);
						operator = $form.attr('data-calendarista-payment-operator');
					}
					switch(operator){
						case 'paypal':
							$form.submit();
						break;
						case 'offline':
							context.$mainForm.append('<input type="hidden" name="booknow" value="<?php echo $this->projectId ?>" />');
							context.$mainForm.submit();
						break;
					}
				}
			});
			this.$paymentMethod.on('change', function(e){
				var $paymentMethod = $(this)
					, $form = checkout.$root.find($paymentMethod.val())
					, $panel = checkout.$root.find($form.attr('data-calendarista-operator-panel'));
				checkout.$root.find('input[name="paymentMethod"]').prop('checked', false);
				$paymentMethod.prop('checked', true);
				context.paymentMethodChanged($panel, $paymentMethod);
			});
			this.paymentMethodChanged(this.$panel, checkout.$root.find('input[name="paymentMethod"][checked]'));
			if(this.$upfrontPayment.length > 0){
				this.upfrontPaymentChanged(this.$upfrontPayment[0], checkout.$root);
			}
		}
		Calendarista.checkoutStep.prototype.paymentMethodChanged = function($panel, $paymentMethod){
			var expand = $paymentMethod.attr('data-calendarista-inline-form');
			if(this.$panels.hasClass('in')){
				this.$panels.calendaristaCollapse('hide');
			}
			if(expand){
				return $panel.calendaristaCollapse('show');
			}
			if($panel.hasClass('in')){
				$panel.calendaristaCollapse('hide');
			}
		}
		Calendarista.checkoutStep.prototype.upfrontPaymentChanged = function(el, $root){
			var $upfrontPaymentElements = $root.find('input[name="upfrontPayment"]')
				, $payPalForm = $root.find('form[data-calendarista-payment-operator="paypal"]')
				, $paypalNotifyUrl = $payPalForm.find('input[name="notify_url"]')
				, $paypalAmount = $payPalForm.find('input[name="amount"]')
				, $_paypalAmount = $payPalForm.find('input[name="_amount"]')
				, $payPalUpfrontAmount = $payPalForm.find('input[name="upfront_amount"]')
				, href
				, i
				, elem
				, attrValue;
			if(!el){
				return;
			}
			attrValue = $(el).attr('data-calendarista-upfront');
			for(i = 0; i < $upfrontPaymentElements.length; i++){
				elem = $upfrontPaymentElements[i];
				$(elem).val(attrValue);
			}
			if($payPalForm.length > 0){
				href = new URL($paypalNotifyUrl.val());
				href.searchParams.set('upfrontPayment', el.checked ? '1' : '0');
				$paypalNotifyUrl.val(href.toString());
				if(el.checked){
					$paypalAmount.val($payPalUpfrontAmount.val());
				}else{
					$paypalAmount.val($_paypalAmount.val());
				}
			}
		}
		window['Calendarista'] = Calendarista;
		<?php if($this->notAjaxRequest):?>
		
		if (window.addEventListener){
		  window.addEventListener('load', onload, false); 
		} else if (window.attachEvent){
		  window.attachEvent('onload', onload);
		}
		function onload(e){
			new Calendarista.checkoutStep();
		}
		<?php else: ?>
		new Calendarista.checkoutStep();
		<?php endif; ?>
		
	})(jQuery);
	</script>
<?php
	}
}