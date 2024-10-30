<?php
class Calendarista_AvailabilityDayHelper{
	protected static $days = null;
	protected static $availabilityId = null;
	protected static $availabilityDayRepo = null;
    public static function get($availabilityId) {
        if (!self::$days || $availabilityId !== self::$availabilityId) {
			self::$availabilityDayRepo = new Calendarista_AvailabilityDayRepository();
			self::$days = self::$availabilityDayRepo->readByAvailability($availabilityId);
			self::$availabilityId = $availabilityId;
        }
		if(!self::$days){
			return array();
		}
        return self::$days;
    }
    private function __construct() { }
}
?>