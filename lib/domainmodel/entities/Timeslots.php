<?php
class Calendarista_Timeslots extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_Timeslot) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Timeslot class.');
		}
        parent::add($value);
    }
}
?>