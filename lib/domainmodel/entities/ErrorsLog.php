<?php
class Calendarista_ErrorsLog extends Calendarista_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Calendarista_ErrorLog) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_ErrorLog class.');
		}
        parent::add($value);
    }
}
?>