<?php
class Calendarista_FormElements extends Calendarista_CollectionBase{
	public $cols;
	public $rows;
	public function add($value) {
		if (! ($value instanceOf Calendarista_FormElement) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_FormElement class.');
		}
        parent::add($value);
    }
}
?>