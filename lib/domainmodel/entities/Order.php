<?php
class Calendarista_Order extends Calendarista_EntityBase{
	public $id = -1;
	public $invoiceId;
	public $projectId;
	public $stagingId;
	public $availabilityId;
	public $availabilityName;
	public $projectName;
	public $orderDate;
	public $paymentDate;
	public $userId = null;
	public $fullName;
	public $email;
	public $paymentStatus = Calendarista_PaymentStatus::UNPAID;
	public $transactionId = null;
	public $wooCommerceOrderId;
	public $refundAmount = 0;
	public $currency;
	public $currencySymbol;
	public $totalAmount = 0;
	public $timezone;
	public $serverTimezone;
	public $discount = 0;
	public $discountMode;
	public $tax = 0;
	public $taxMode = null;
	public $paymentsMode;
	public $paymentOperator;
	public $deposit;
	public $depositMode;
	public $balance;
	public $secretKey;
	public $requestId;
	public $repeatWeekdayList;
	public $repeatFrequency;
	public $repeatInterval;
	public $terminateAfterOccurrence;
	public $couponCode;
	public $upfrontPayment;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('stagingId', $args)){
			$this->stagingId = (string)$args['stagingId'];
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if(array_key_exists('availabilityId', $args)){
			$this->availabilityId = (int)$args['availabilityId'];
		}
		if(array_key_exists('availabilityName', $args)){
			$this->availabilityName = (string)$args['availabilityName'];
		}
		if(array_key_exists('orderDate', $args)){
			$this->orderDate = new Calendarista_DateTime($args['orderDate']);
		}
		if(array_key_exists('userId', $args) && $args['userId'] !== null){
			$this->userId = (int)$args['userId'];
		}
		if(array_key_exists('fullName', $args)){
			$this->fullName = (string)$args['fullName'];
		}
		if(array_key_exists('email', $args)){
			$this->email = (string)$args['email'];
		}
		if(array_key_exists('paymentStatus', $args)){
			$this->paymentStatus = (int)$args['paymentStatus'];
		}
		if(array_key_exists('transactionId', $args)){
			$this->transactionId = (string)$args['transactionId'];
		}
		if(array_key_exists('wooCommerceOrderId', $args)){
			$this->wooCommerceOrderId = (int)$args['wooCommerceOrderId'];
		}
		if(array_key_exists('totalAmount', $args)){
			$this->totalAmount = (double)$args['totalAmount'];
		}
		if(array_key_exists('currency', $args)){
			$this->currency = (string)$args['currency'];
		}
		if(array_key_exists('currencySymbol', $args)){
			$this->currencySymbol = (string)$args['currencySymbol'];
		}
		if(array_key_exists('discount', $args)){
			$this->discount = (double)$args['discount'];
		}
		if(array_key_exists('discountMode', $args)){
			$this->discountMode = (int)$args['discountMode'];
		}
		if(array_key_exists('tax', $args)){
			$this->tax = (double)$args['tax'];
		}
		if(array_key_exists('taxMode', $args) && is_numeric($args['taxMode'])){
			$this->taxMode = (int)$args['taxMode'];
		}
		if(array_key_exists('refundAmount', $args)){
			$this->refundAmount = (double)$args['refundAmount'];
		}
		if(array_key_exists('paymentDate', $args) && $args['paymentDate']){
			$this->paymentDate = new Calendarista_DateTime($args['paymentDate']);
		}
		if(array_key_exists('paymentOperator', $args)){
			$this->paymentOperator = (string)$args['paymentOperator'];
		}
		if(array_key_exists('paymentsMode', $args)){
			$this->paymentsMode = (int)$args['paymentsMode'];
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if(array_key_exists('depositMode', $args)){
			$this->depositMode = (double)$args['depositMode'];
		}
		if(array_key_exists('balance', $args)){
			$this->balance = (double)$args['balance'];
		}
		if(array_key_exists('timezone', $args)){
			$this->timezone = (string)$args['timezone'];
		}
		if(array_key_exists('serverTimezone', $args)){
			$this->serverTimezone = (string)$args['serverTimezone'];
		}
		if(array_key_exists('invoiceId', $args)){
			$this->invoiceId = (string)$args['invoiceId'];
		}
		if(array_key_exists('secretKey', $args)){
			$this->secretKey = (string)$args['secretKey'];
		}
		if(array_key_exists('requestId', $args)){
			$this->requestId = (string)$args['requestId'];
		}
		if(array_key_exists('repeatWeekdayList', $args)){
			if(is_string($args['repeatWeekdayList']) && strlen($args['repeatWeekdayList']) > 0){
				$this->repeatWeekdayList = array_map('intval', explode(',', $args['repeatWeekdayList']));
			}else if(is_array($args['repeatWeekdayList'])){
				$this->repeatWeekdayList = $args['repeatWeekdayList'];
			}else{
				$this->repeatWeekdayList = array();
			}
		}
		if(array_key_exists('repeatFrequency', $args)){
			$this->repeatFrequency = (int)$args['repeatFrequency'];
		}
		if(array_key_exists('repeatInterval', $args)){
			$this->repeatInterval = (int)$args['repeatInterval'];
		}
		if(array_key_exists('terminateAfterOccurrence', $args)){
			$this->terminateAfterOccurrence = (int)$args['terminateAfterOccurrence'];
		}
		if(array_key_exists('couponCode', $args)){
			$this->couponCode = $args['couponCode'];
		}
		if(array_key_exists('upfrontPayment', $args)){
			$this->upfrontPayment = $args['upfrontPayment'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!$this->currency){
			$localeInfo = Calendarista_MoneyHelper::getLocaleInfo();
			$this->currency = $localeInfo['currency'];
		}
		if(!($this->tax > 0) && $generalSetting->tax > 0){
			$this->tax = $generalSetting->tax;
		}
	}
	public function toArray(){
		$result = array(
			'id'=>$this->id
			, 'invoiceId'=>$this->invoiceId
			, 'projectId'=>$this->projectId
			, 'stagingId'=>$this->stagingId
			, 'availabilityId'=>$this->availabilityId
			, 'availabilityName'=>$this->availabilityName
			, 'projectName'=>$this->projectName
			, 'orderDate'=>$this->orderDate
			, 'userId'=>$this->userId
			, 'fullName'=>$this->fullName
			, 'email'=>$this->email
			, 'paymentStatus'=>$this->paymentStatus
			, 'transactionId'=>$this->transactionId
			, 'wooCommerceOrderId'=>$this->wooCommerceOrderId
			, 'totalAmount'=>$this->totalAmount
			, 'currency'=>$this->currency
			, 'currencySymbol'=>$this->currencySymbol
			, 'discount'=>$this->discount
			, 'discountMode'=>$this->discountMode
			, 'tax'=>$this->tax
			, 'taxMode'=>$this->taxMode
			, 'refundAmount'=>$this->refundAmount
			, 'paymentDate'=>$this->paymentDate
			, 'paymentOperator'=>$this->paymentOperator
			, 'paymentsMode'=>$this->paymentsMode
			, 'deposit'=>$this->deposit
			, 'depositMode'=>$this->depositMode
			, 'balance'=>$this->balance
			, 'timezone'=>$this->timezone
			, 'serverTimezone'=>$this->serverTimezone
			, 'secretKey'=>$this->secretKey
			, 'repeatWeekdayList'=>$this->repeatWeekdayList
			, 'repeatFrequency'=>$this->repeatFrequency
			, 'repeatInterval'=>$this->repeatInterval
			, 'terminateAfterOccurrence'=>$this->terminateAfterOccurrence
			, 'couponCode'=>$this->couponCode
			, 'upfrontPayment'=>$this->upfrontPayment
		);
		return $result;
	}
	public static function getPaymentStatus($status){
		/*Calendarista_PaymentStatus*/
		switch($status){
			case 0:
				return __('Unpaid', 'calendarista');
			break;
			case 1:
				return __('Paid', 'calendarista');
			break;
			case 2:
				return __('Refunded', 'calendarista');
			break;
		}
	}
}
?>