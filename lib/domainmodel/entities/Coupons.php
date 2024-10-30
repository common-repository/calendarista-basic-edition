<?php
class Calendarista_Coupons extends Calendarista_CollectionBase{
	public $total = 0;//contains total records count --use when paging
	public function add($value) {
		if (! ($value instanceOf Calendarista_Coupon) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Coupon class.');
		}
        parent::add($value);
    }
}
?>