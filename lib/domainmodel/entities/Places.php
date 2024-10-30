<?php
class Calendarista_Places extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_Place) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Place class.');
		}
        parent::add($value);
    }
}
?>