<?php
class Calendarista_RepeatFrequency{
	const NONE = 0;
	const DAILY = 1;
	const EVERY_WEEK_DAY = 2;
	const EVERY_MONDAY_WEDNESDAY_FRIDAY = 3;
	const EVERY_TUESDAY_THURSDAY = 4;
	const WEEKLY = 5;
	const MONTHLY = 6;
	const YEARLY = 7;
	
	public static function toArray(){
		return array(
					__('None', 'calendarista')
					, __('Daily', 'calendarista')
					, __('Every day of the week (from Monday to Friday)', 'calendarista')
					, __('Every Monday, Wednesday and Friday', 'calendarista')
					, __('Every Tuesday and Thursday', 'calendarista')
					, __('Weekly', 'calendarista')
					, __('Monthly', 'calendarista')
					, __('Yearly', 'calendarista')
				);
	}
}
?>