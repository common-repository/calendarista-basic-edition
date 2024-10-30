<?php
class Calendarista_TimeFormat{
	const WORDPRESS = 0;
	const AMPM = 1;
	const TWENTYFOURHOUR = 2;
	public static function toArray(){
		return array('WordPress settings'=>0, 'am/pm'=>1, '24H'=>2);
	}
}
?>