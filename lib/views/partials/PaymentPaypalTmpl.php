<?php
class Calendarista_PaymentPaypalTmpl extends Calendarista_TemplateBase{
	public $paypalURL;
	public $paypalSetting;
	public $currency;
	public $firstname;
	public $lastname;
	public $email;
	public $totalAmount;
	public $totalAmountBeforeDeposit;
	public $fullAmountDiscount;
	public $itemName;
	public $stagingId;
	public $seats;
	public function __construct($setting, $stagingId){
		parent::__construct();
		$this->paypalSetting = $setting;
		$this->stagingId = $stagingId;
		if(!has_filter('calendarista_checkout_summary')) {
			return;
		}
		$checkoutSummary = apply_filters('calendarista_checkout_summary', false);
		if($checkoutSummary['totalAmount'] > 0){
			$this->firstname = $checkoutSummary['firstname'];
			$this->lastname = $checkoutSummary['lastname'];
			$this->email = $checkoutSummary['email'];
			$this->totalAmount = $checkoutSummary['totalAmount'];
			$this->totalAmountBeforeDeposit = $checkoutSummary['totalAmountBeforeDeposit'];
			$this->fullAmountDiscount = $checkoutSummary['fullAmountDiscount'];
			$this->currency = $checkoutSummary['currency'];
			$this->seats = $checkoutSummary['seats'];
			$this->itemName = sprintf($this->stringResources['PAYMENT_ITEM_NAME'], $checkoutSummary['serviceName'], $checkoutSummary['availabilityName']);
			$this->itemName .= sprintf(' - (%s)', $checkoutSummary['summaryPlainText']);
			$this->confirmUrl = $this->getConfirmUrl($this->requestUrl, $stagingId);
			//rework this success/failure messages
			$this->paypalURL = 'https://www.paypal.com/cgi-bin/webscr';
			if($this->paypalSetting->useSandbox){
				$this->paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			}
			$this->render();
		}
	}
	public function getUpfrontAmount($val){
		$fullAmountDiscount = $this->fullAmountDiscount;
		if($fullAmountDiscount && $this->seats > 1){
			$fullAmountDiscount = $fullAmountDiscount * $this->seats;
		}
		if($fullAmountDiscount > 0 && 
			$this->totalAmountBeforeDeposit > $fullAmountDiscount){
			$discountedValue = $this->totalAmountBeforeDeposit - $fullAmountDiscount;
			return $discountedValue;
		}else if($this->totalAmountBeforeDeposit > 0){
			return $this->totalAmountBeforeDeposit;
		}
		return $val;
	}
	public function render(){
	?>
		<form action="<?php echo esc_url($this->paypalURL) ?>" method="post" id="payment-operator-<?php echo sprintf('%d-%d', $this->paypalSetting->id, $this->projectId) ?>" 
			data-calendarista-payment-operator="paypal" data-calendarista-total-name="amount" data-calendarista-original-total="<?php echo $this->totalAmount ?>">
		  <input type="hidden" name="cmd" value="_xclick">
		  <input type="hidden" name="business" value="<?php echo esc_attr($this->paypalSetting->businessEmail) ?>">
		  <input type="hidden" name="currency_code" value="<?php echo $this->currency ?>">
		  <input type="hidden" name="custom" value="<?php echo $this->stagingId ?>">
		  <input type="hidden" name="item_name" value="<?php echo esc_attr($this->itemName) ?>">
		  <input type="hidden" name="amount" value="<?php echo $this->totalAmount ?>">
		  <input type="hidden" name="_amount" value="<?php echo $this->totalAmount ?>">
		  <input type="hidden" name="upfront_amount" value="<?php echo $this->getUpfrontAmount($this->totalAmount) ?>">
		  <input type="hidden" name="amount_before_deposit" value="<?php echo $this->totalAmountBeforeDeposit ?>">
		  <input type="hidden" name="first_name" value="<?php echo esc_attr($this->firstname) ?>">
		  <input type="hidden" name="last_name" value="<?php echo esc_attr($this->lastname) ?>">
		  <input type="hidden" name="email" value="<?php echo esc_attr($this->email) ?>">
		  <input type="hidden" name="rm" value="1">
		  <input type="hidden" name="return" value="<?php echo esc_url($this->confirmUrl) ?>">
		  <input type="hidden" name="cancel_return" value="<?php echo esc_url($this->requestUrl) ?>">
		  <input type="hidden" name="notify_url" value="<?php echo sprintf('%s?calendarista_handler=paypal&upfrontPayment=0', home_url('/'))?>">
		</form>
	<?php
	}
}?>

