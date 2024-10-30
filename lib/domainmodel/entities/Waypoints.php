<?php
class Calendarista_Waypoints extends Calendarista_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Calendarista_Waypoint) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Waypoint class.');
		}
        parent::add($value);
    }
}
?>