<?php
class Calendarista_CouponHelper{
	public $projectId;
	public $coupon;
	public $discount;
	public $discountValue;
	public $discountMode;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		$couponCode = null;
		if(array_key_exists('coupon', $_POST)){
			$couponCode = trim(sanitize_text_field($_POST['coupon']));
		}else if(array_key_exists('coupon', $args)){
			$couponCode = trim($args['coupon']);
		}
		if($couponCode){
			$repo = new Calendarista_CouponRepository();
			$this->coupon = $repo->find($couponCode);
		}else if(array_key_exists('discount', $args) && array_key_exists('discountMode', $args)){
			$expirationDate = new Calendarista_DateTime();
			$expirationDate->modify('+1 day');
			$this->coupon = new Calendarista_Coupon(array(
				'discount'=>$args['discount']
				, 'discountMode'=>$args['discountMode']
				, 'expirationDate'=>$expirationDate->format(CALENDARISTA_DATEFORMAT)
			));
		}
		if($this->coupon){
			$this->discountMode = $this->coupon->discountMode;
		}
	}
	protected function getCoupon($total){
		if($this->coupon){
			if($this->coupon->orderMinimum && $total < $this->coupon->orderMinimum){
				return false;
			}else if($this->coupon->isValid()){
				if($this->coupon->projectId === -1 || $this->coupon->projectId === $this->projectId){
					return $this->coupon;
				}
			}
		}
		return false;
	}
	public function clientSideValidation($total){
		if($this->coupon){
			if($this->coupon->orderMinimum && $total < $this->coupon->orderMinimum){
				return array('isValid'=>false, 'orderMinimum'=>Calendarista_MoneyHelper::toLongCurrency($this->coupon->orderMinimum));
			}else if($this->coupon->isValid()){
				$generalSetting = Calendarista_GeneralSettingHelper::get();
				if($this->coupon->projectId === -1 || $this->coupon->projectId === $this->projectId){
					$fullDiscount = true;
					$isValid = true;
					$discountValue = $this->applyDiscount($total);
					if($discountValue > 0){
						$fullDiscount = false;
					}
					//stripe is a special case when currency supports decimal places
					$totalCents = Calendarista_PaymentStripeTmpl::formatCurrency($discountValue, $generalSetting->currency);
					return array('isValid'=>$isValid, 'fullDiscount'=>$fullDiscount, 'total'=>$discountValue, 'totalCents'=>$totalCents);
				}
			}
		}
		return array('isValid'=>false);
	}
	public function applyDiscount($total){
		$totalBeforeDiscount = $total;
		$coupon = $this->getCoupon($total);
		if($coupon){
			$this->discount = $coupon->discount;
			$total = $coupon->apply($total);
			$this->discountValue = $totalBeforeDiscount - $total;
		}
		if($total < 0){
			return 0;
		}
		return $total;
	}
	public function invalidateCoupon(){
		if($this->coupon && $this->coupon->couponType === Calendarista_CouponType::REGULAR){
			$this->coupon->expire();
			$repo = new Calendarista_CouponRepository();
			$repo->update($this->coupon);
		}
	}
	public function discountToString(){
		if($this->coupon && $this->coupon->isValid()){
			return $this->coupon->discountToString();
		}
		return null;
	}
}
?>