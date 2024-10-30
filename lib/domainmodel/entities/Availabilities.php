<?php
class Calendarista_Availabilities extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_Availability) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Availability class.');
		}
        parent::add($value);
    }
}
?>