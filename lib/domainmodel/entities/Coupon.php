<?php
class Calendarista_Coupon extends Calendarista_EntityBase{
	public $code;
	public $emailedTo;
	public $id = -1;
	public $discount = 0;
	public $orderMinimum = 0;
	public $expirationDate;
	public $projectId = -1;
	public $projectName;
	public $couponType = Calendarista_CouponType::REGULAR;
	public $discountMode = 0;
	public function __construct($args){
		if(array_key_exists('discount', $args)){
			$this->discount = (double)$args['discount'];
		}
		if(array_key_exists('orderMinimum', $args)){
			$this->orderMinimum = (double)$args['orderMinimum'];
		}
		if(array_key_exists('expirationDate', $args)){
			$this->expirationDate = new Calendarista_DateTime($args['expirationDate']);
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if(array_key_exists('code', $args)){
			$this->code = (string)$args['code'];
		}
		if(array_key_exists('emailedTo', $args)){
			$this->emailedTo = (string)$args['emailedTo'];
		}
		if(array_key_exists('discountMode', $args)){
			//0 = percentage
			//1 = fixed
			$this->discountMode = (int)$args['discountMode'];
		}
		if(array_key_exists('couponType', $args)){
			$this->couponType = (int)$args['couponType'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'discount'=>$this->discount
			, 'orderMinimum'=>$this->orderMinimum
			, 'expirationDate'=>$this->expirationDate
			, 'code'=>$this->code
			, 'emailedTo'=>$this->emailedTo
			, 'projectId'=>$this->projectId
			, 'projectName'=>$this->projectName
			, 'discountMode'=>$this->discountMode
			, 'couponType'=>$this->couponType
		);
	}
	
	public function isValid(){
		if(!$this->expirationDate){
			return false;
		}
		try{
			$result = strtotime('now') < strtotime($this->expirationDate->format('Y-m-d'));
		}catch(Exception $ex){
			$result = false;
		}
		return $result;
	}
	public function expire(){
		if($this->isValid()){
			$expirationDate = new Calendarista_DateTime();
			$expirationDate->modify('-1 day');
			$this->expirationDate = $expirationDate;
		}
	}
	public function apply($totalAmount){
		if($this->isValid()){
			$discount = $this->discount;
			if($this->discountMode === 0/*percentage*/){
				$discount = ($totalAmount / 100) * $discount;
			}
			$totalAmount -= $discount;
		}
		return $totalAmount;
	}
	public function discountToString(){
		if($this->discount){
			return $this->discountMode ? 
				Calendarista_MoneyHelper::toLongCurrency($this->discount) : 
				$this->discount . '%';
		}
		return null;
	}
}
?>