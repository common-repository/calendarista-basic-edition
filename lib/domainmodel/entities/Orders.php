<?php
class Calendarista_Orders extends Calendarista_CollectionBase{
	public $total = 0;
	public $totalAmount = 0;
	public function add($value) {
		if (! ($value instanceOf Calendarista_Order) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Order class.');
		}
        parent::add($value);
    }
}
?>