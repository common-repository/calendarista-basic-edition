<?php
class Calendarista_BookingRepeatOption{
	const NONE = 0;
	const DAILY = 1;
	const WEEKLY = 2;
	const MONTHLY = 3;
	const YEARLY = 4;

	public static function toArray(){
		return array(
			__('None', 'calendarista')
			, __('Daily', 'calendarista')
			, __('Weekly', 'calendarista')
			, __('Monthly', 'calendarista')
			, __('Yearly', 'calendarista')
		);
	}
}
?>