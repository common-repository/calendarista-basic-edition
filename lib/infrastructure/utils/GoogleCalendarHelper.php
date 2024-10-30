<?php
class Calendarista_GoogleCalendarHelper{
	public $client;
	public $service = null;
	const applicationName = 'Calendarista';
	protected $repo;
	public function __construct($userId){

	}
	public function getAuthorizationURL(){
		return null;
	}
	public function getCalendarList(){
		return null;
	}
	public static function getClient($clientData){
		return null;
	}
	public static function handleAccessToken($userId, $token){
		
	}
	public static function syncAll(){
		
	}
	public static function syncByGCal($gcalProfileId, $gcalId){
		return null;
	}
	public static function export($clientData, $service, $entries){
		return null;
	}
	public static function import($clientData, $service, $entries){
		return null;
	}
	protected static function getEmail($event){
		return null;
	}
	protected static function insertOrUpdateAppointment($args){
		return null;
	}
	protected static function getTimeslot($t, $timeslots){
		return null;
	}
	protected static function getLocalStatus($event){
		return null;
	}
	protected static function populateEvent($booking, $timezone){
		return null;
	}
	protected static function addEvent($event, $service, $entry, $bookingId){
		return null;
	}
	public static function insertEvent($bookingId){
		
	}
	public static function deleteEvent($bookingId){
		
	}
	public static function deleteExportedByAvailability($availabilityId, $gcalProfileId){
		return null;
	}
	protected static function deleteMultipleEvents($localEvents, $eventList){
		return null;
	}
	public static function deleteAllEvents($gcalId){
		
	}
	public static function updateEvent($bookingId){
		
	}
	protected static function getBookingDescription($booking){
		return null;
	}
	public static function getMap($orderId, $projectId){
		return null;
	}
	public static function getWaypoints($orderId){
	return null;
	}
	public static function getOptionals($orderId){
		return null;
	}
	public static function getCustomFormElements($orderId){
		return null;
	}
	public static function getDynamicFields($orderId){
		return null;
	}
	public static function getServiceProviderName($availabilityId){
		return null;
	}
	protected function serviceActive(){
		return null;
	}
	protected static function validDateDate($availability, $start, $end){
		return false;
	}
	public static function base64UrlEncode($value)
	{
		return null;
	}
	protected static function logError($booking, $e){
		
	}
}
?>