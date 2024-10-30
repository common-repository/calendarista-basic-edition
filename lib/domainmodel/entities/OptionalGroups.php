<?php
class Calendarista_OptionalGroups extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_OptionalGroup) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_OptionalGroup class.');
		}
        parent::add($value);
    }
}
?>