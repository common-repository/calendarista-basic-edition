<?php
class Calendarista_Optionals extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_Optional) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Optional class.');
		}
        parent::add($value);
    }
}
?>