<?php
class Calendarista_Weekday{
	const MONDAY = 1;
	const TUESDAY = 2;
	const WEDNESDAY = 3;
	const THURSDAY = 4;
	const FRIDAY = 5;
	const SATURDAY = 6;
	const SUNDAY = 7;
	
	public static function toArray(){
		return array(
			7=>__('SU', 'calendarista')
			, 1=>__('MO', 'calendarista')
			, 2=>__('TU', 'calendarista')
			, 3=>__('WE', 'calendarista')
			, 4=>__('TH', 'calendarista')
			, 5=>__('FR', 'calendarista')
			, 6=>__('SA', 'calendarista')
		);
	}
}
?>