<?php
class Calendarista_Seats extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_Seat) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Seat class.');
		}
        parent::add($value);
    }
}
?>