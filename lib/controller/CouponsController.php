<?php
class Calendarista_CouponsController extends Calendarista_BaseController{
	private $coupon;
	private $couponRepository;
	public function __construct($coupon, $createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_coupons')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->coupon = $coupon;
		$this->couponRepository = new Calendarista_CouponRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	public function create($callback){
		$couponsCount = (int)$this->getPostValue('couponsCount');
		if($this->coupon->couponType !== Calendarista_CouponType::REGULAR){
			$couponsCount = 1;
		}
		$coupon = $this->couponRepository->find($this->coupon->code);
		$result = false;
		if(!$coupon){
			$result = $this->couponRepository->insertMany($this->coupon, $couponsCount);
		}
		$this->executeCallback($callback, array($couponsCount, $result));
	}
	
	public function update($callback){
		$coupon = $this->couponRepository->find($this->coupon->code, $this->coupon->id);
		$result = false;
		if(!$coupon){
			$result = $this->couponRepository->update($this->coupon);
		}
		$this->executeCallback($callback, array($this->coupon->id, $result));
	}
	
	public function delete($callback){
		$id = (int)$this->getPostValue('id');
		$result = $this->couponRepository->delete($id);
		$this->executeCallback($callback, array($id, $result));
	}
}
?>