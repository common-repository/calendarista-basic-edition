<?php
class Calendarista_EmailReminders extends Calendarista_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Calendarista_EmailReminder) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_EmailReminder class.');
		}
        parent::add($value);
    }
}
?>