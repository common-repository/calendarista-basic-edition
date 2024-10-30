<?php
class Calendarista_CalendarMode{
	const SINGLE_DAY = 0;
	const SINGLE_DAY_AND_TIME = 1;
	const SINGLE_DAY_AND_TIME_RANGE = 2;
	const MULTI_DATE_RANGE = 3;
	const MULTI_DATE_AND_TIME_RANGE = 4;
	const CHANGEOVER = 5;
	const PACKAGE = 6;
	const ROUND_TRIP = 7;
	const ROUND_TRIP_WITH_TIME = 8;
	const SINGLE_DAY_AND_TIME_WITH_PADDING = 9;
	const SIMPLE_EVENT = 10;
	const MULTI_DATE = 11;
	const MULTI_DATE_AND_TIME = 12;
	public static $SINGLE_DAY_EVENT = array(self::SINGLE_DAY
															, self::SINGLE_DAY_AND_TIME
															, self::SINGLE_DAY_AND_TIME_WITH_PADDING
															, self::SINGLE_DAY_AND_TIME_RANGE);
	public static $SUPPORTS_MULTIPLY_BY_TIMESLOT_SELECTION = array(self::SINGLE_DAY_AND_TIME, 
															self::SINGLE_DAY_AND_TIME_WITH_PADDING
															, self::SINGLE_DAY_AND_TIME_RANGE
															, self::MULTI_DATE_AND_TIME);
	public static $SUPPORTS_MULTI_TIMESLOT_SELECTION = array(self::SINGLE_DAY_AND_TIME, self::SINGLE_DAY_AND_TIME_WITH_PADDING);
	public static $SUPPORTS_PADDING = array(self::SINGLE_DAY_AND_TIME_WITH_PADDING);
	public static $SUPPORTS_FULL_DAY_COST = array(self::SINGLE_DAY
												, self::MULTI_DATE_RANGE
												, self::MULTI_DATE_AND_TIME_RANGE
												, self::CHANGEOVER
												, self::PACKAGE
												, self::ROUND_TRIP
												, self::ROUND_TRIP_WITH_TIME
												, self::MULTI_DATE);
	public static $SUPPORTS_FULL_DAY_AND_TIMESLOT_COST = array(self::ROUND_TRIP_WITH_TIME, self::MULTI_DATE_AND_TIME_RANGE);
	public static $SUPPORTS_SEQUENCE = array(self::MULTI_DATE_RANGE
												, self::MULTI_DATE_AND_TIME_RANGE
												, self::CHANGEOVER);
	public static $SUPPORTS_MULTI_DATE = array(self::MULTI_DATE
												, self::MULTI_DATE_AND_TIME);
	public static $SUPPORTS_TIME_RANGE = array(self::SINGLE_DAY_AND_TIME_RANGE, self::MULTI_DATE_AND_TIME_RANGE, self::ROUND_TRIP_WITH_TIME);
	public static $SUPPORTS_RETURN = array(self::ROUND_TRIP
												, self::ROUND_TRIP_WITH_TIME);
	public static $SUPPORTS_SEATS = array(self::SINGLE_DAY, self::PACKAGE, self::ROUND_TRIP, self::MULTI_DATE_RANGE, self::CHANGEOVER, self::MULTI_DATE);
	public static $SUPPORTS_TIMESLOT_AND_SEATS = array(self::SINGLE_DAY_AND_TIME, self::ROUND_TRIP_WITH_TIME, self::SINGLE_DAY_AND_TIME_RANGE, self::MULTI_DATE_AND_TIME, self::SINGLE_DAY_AND_TIME_WITH_PADDING, self::MULTI_DATE_AND_TIME_RANGE);
	//ROUND_TRIP_WITH_TIME, ROUND_TRIP, SINGLE_DAY_AND_TIME_RANGE cannot support group booking, technically not possible
	public static $SUPPORTS_GROUP_BOOKING = array(self::SINGLE_DAY, self::PACKAGE, self::SINGLE_DAY_AND_TIME, self::SINGLE_DAY_AND_TIME_RANGE, self::SINGLE_DAY_AND_TIME_WITH_PADDING, self::ROUND_TRIP_WITH_TIME, self::ROUND_TRIP, self::MULTI_DATE_RANGE, self::MULTI_DATE_AND_TIME_RANGE, self::CHANGEOVER);
	public static $SUPPORTS_END_DATE = array(self::MULTI_DATE_RANGE, self::MULTI_DATE_AND_TIME_RANGE
											, self::CHANGEOVER
											, self::ROUND_TRIP, self::ROUND_TRIP_WITH_TIME);
	public static $SUPPORTS_TIMESLOTS = array(self::SINGLE_DAY_AND_TIME, self::SINGLE_DAY_AND_TIME_RANGE
											, self::MULTI_DATE_AND_TIME_RANGE, self::ROUND_TRIP_WITH_TIME
											, self::SINGLE_DAY_AND_TIME_WITH_PADDING
											, self::MULTI_DATE_AND_TIME);
	public static $SUPPORTS_TIMESLOTS_RANGE = array(self::SINGLE_DAY_AND_TIME_RANGE
															, self::MULTI_DATE_AND_TIME_RANGE
															, self::ROUND_TRIP_WITH_TIME);
	public static $SUPPORTS_TURNOVER = array(self::SINGLE_DAY
												, self::MULTI_DATE_RANGE
												, self::CHANGEOVER
												, self::ROUND_TRIP
												, self::MULTI_DATE);
	public static $SUPPORTS_NOTICE = array(self::SINGLE_DAY
												, self::SINGLE_DAY_AND_TIME
												, self::SINGLE_DAY_AND_TIME_WITH_PADDING
												, self::SINGLE_DAY_AND_TIME_RANGE
												, self::MULTI_DATE_RANGE
												, self::MULTI_DATE_AND_TIME_RANGE
												, self::CHANGEOVER
												, self::ROUND_TRIP
												, self::ROUND_TRIP_WITH_TIME
												, self::MULTI_DATE
												, self::MULTI_DATE_AND_TIME);
	public static $SUPPORTS_ROUND_TRIP = array(self::ROUND_TRIP, self::ROUND_TRIP_WITH_TIME);
	public static $SUPPORTS_SEASONS = array(self::SINGLE_DAY
												, self::MULTI_DATE_RANGE
												, self::MULTI_DATE_AND_TIME_RANGE
												, self::CHANGEOVER
												, self::ROUND_TRIP
												, self::ROUND_TRIP_WITH_TIME
												, self::MULTI_DATE);
	public static $SUPPORTS_CUSTOM_CHARGE = array(self::MULTI_DATE_RANGE
												, self::MULTI_DATE_AND_TIME_RANGE
												, self::CHANGEOVER
												, self::MULTI_DATE);
	public static $SUPPORTS_PRICING_SCHEME = array(self::MULTI_DATE_RANGE, self::MULTI_DATE_AND_TIME_RANGE, self::CHANGEOVER, self::MULTI_DATE);
	
	public static function toArray(){
		return array(
			array('key'=>self::SINGLE_DAY, 'value'=>__('Single day', 'calendarista'))
			, array('key'=>self::SINGLE_DAY_AND_TIME,  'value'=>__('Single day and time', 'calendarista'))
			, array('key'=>self::SINGLE_DAY_AND_TIME_WITH_PADDING,  'value'=>__('Single day and time with padding', 'calendarista'))
			, array('key'=>self::SINGLE_DAY_AND_TIME_RANGE,  'value'=>__('Single day and time range', 'calendarista'))
			, array('key'=>self::MULTI_DATE_RANGE,  'value'=>__('Multi date range', 'calendarista'))
			, array('key'=>self::MULTI_DATE_AND_TIME_RANGE,  'value'=>__('Multi date and time range', 'calendarista'))
			, array('key'=>self::CHANGEOVER,  'value'=>__('Multi date range with Changeover', 'calendarista'))
			, array('key'=>self::PACKAGE,  'value'=>__('Package', 'calendarista'))
			, array('key'=>self::ROUND_TRIP,  'value'=>__('Round trip', 'calendarista'))
			, array('key'=>self::ROUND_TRIP_WITH_TIME,  'value'=>__('Round trip with time', 'calendarista'))
			, array('key'=>self::MULTI_DATE,  'value'=>__('Multi date', 'calendarista'))
			, array('key'=>self::MULTI_DATE_AND_TIME,  'value'=>__('Multi date and time', 'calendarista'))
		);
	}
}
?>