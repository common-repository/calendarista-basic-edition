<?php
class Calendarista_Availability extends Calendarista_EntityBookingElementBase {
	public $id = -1;
	public $projectId;
	public $availableDate;
	public $cost;
	public $customChargeDays;
	public $customCharge;
	public $customChargeMode;
	public $deposit;
	public $depositMode;
	public $returnOptional;
	public $returnCost;
	public $seats = 0;
	public $seatsMaximum = 0;
	public $seatsMinimum = 1;
	public $selectableSeats;
	public $daysInPackage = 1;
	public $fullDay;
	public $hasRepeat = false;
	//none, daily, weekly, monthly, yearly
	public $repeatFrequency = Calendarista_RepeatFrequency::NONE;
	//repeatInterval day, week, month, year? numerical value up to 30 max.
	public $repeatInterval;
	//terminateAfterOccurrence how many times must the repeat occur? up to 30 max.
	public $terminateMode = Calendarista_TerminateMode::NEVER;
	public $terminateAfterOccurrence;
	//list of weekdays: 1-7.
	public $repeatWeekdayList = array();
	public $checkinWeekdayList = array();
	public $checkoutWeekdayList = array();
	public $endDate;
	public $color = '#3a87ad';
	public $timezone;
	public $imageUrl;
	public $searchThumbnailUrl;
	public $name;
	public $regionAddress = null;
	public $regionLat = null;
	public $regionLng = null;
	public $regionMarkerIconUrl = null;
	public $regionMarkerIconWidth = null;
	public $regionMarkerIconHeight = null;
	public $regionInfoWindowIcon = null;
	public $regionInfoWindowDescription = null;
	public $styledMaps = null;
	public $showMapMarker = null;
	public $maxTimeslots = 1;
	public $minimumTimeslotCharge;
	public $maximumNotice;
	public $minimumNotice;
	public $bookingDaysMinimum;
	public $bookingDaysMaximum;
	public $turnoverBefore;
	public $turnoverAfter;
	public $turnoverBeforeMin;
	public $turnoverAfterMin;
	public $syncList = array();
	public $description;
	public $timeMode = Calendarista_TimeMode::LITERAL;
	public $displayRemainingSeats;
	public $displayRemainingSeatsMessage;
	public $calendarMode;
	public $searchPage;
	public $orderIndex;
	public $timeDisplayMode = 0/*standard*/;
	public $dayCountMode = 0/*standard*/;
	public $appendPackagePeriodToName = false;
	public $minimumNoticeMinutes = 0;
	public $extendTimeRangeNextDay = false;
	public $minTime = 0;
	public $maxTime = 0;
	public $maxDailyRepeatFrequency = false;
	public $maxWeeklyRepeatFrequency = false;
	public $maxMonthlyRepeatFrequency = false;
	public $maxYearlyRepeatFrequency = false;
	public $maxRepeatOccurrence = 0;
	public $returnSameDay = false;
	public $maxRepeatFrequency = 0;
	public $guestNameRequired = false;
	public $displayDateSelectionReq = false;
	public $enableFullAmountOrDeposit = false;
	public $fullAmountDiscount;
	public $instructions;
	public $hideMapDisplay;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('availableDate', $args)){
			$this->availableDate = $args['availableDate'] instanceOf Calendarista_DateTime ? $args['availableDate'] : new Calendarista_DateTime($args['availableDate']);
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('customChargeDays', $args)){
			$this->customChargeDays = (int)$args['customChargeDays'];
		}
		if(array_key_exists('customCharge', $args)){
			$this->customCharge = (double)$args['customCharge'];
		}
		if(array_key_exists('customChargeMode', $args)){
			$this->customChargeMode = (int)$args['customChargeMode'];
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if(array_key_exists('depositMode', $args)){
			$this->depositMode = (int)$args['depositMode'];
		}
		if(array_key_exists('returnOptional', $args)){
			$this->returnOptional = (bool)$args['returnOptional'];
		}
		if(array_key_exists('returnCost', $args)){
			$this->returnCost = (double)$args['returnCost'];
		}
		if(array_key_exists('seats', $args)){
			$this->seats = (int)$args['seats'];
		}
		if(array_key_exists('seatsMaximum', $args) && (int)$args['seatsMaximum'] !== 0){
			$this->seatsMaximum = (int)$args['seatsMaximum'];
		}
		if(array_key_exists('seatsMinimum', $args) && (int)$args['seatsMinimum'] !== 0){
			$this->seatsMinimum = (int)$args['seatsMinimum'];
		}
		if(array_key_exists('selectableSeats', $args)){
			$this->selectableSeats = (bool)$args['selectableSeats'];
		}
		if(array_key_exists('daysInPackage', $args)){
			$this->daysInPackage = (int)$args['daysInPackage'] ? (int)$args['daysInPackage'] : 1;
		}
		if(array_key_exists('fullDay', $args)){
			$this->fullDay = (bool)$args['fullDay'];
		}
		if(array_key_exists('hasRepeat', $args)){
			$this->hasRepeat = (bool)$args['hasRepeat'];
		}
		if(array_key_exists('repeatFrequency', $args)){
			$this->repeatFrequency = (int)$args['repeatFrequency'];
		}
		if(array_key_exists('repeatInterval', $args)){
			$this->repeatInterval = (int)$args['repeatInterval'];
		}
		if(array_key_exists('terminateMode', $args)){
			$this->terminateMode = (int)$args['terminateMode'];
		}
		if(array_key_exists('terminateAfterOccurrence', $args)){
			$this->terminateAfterOccurrence = (int)$args['terminateAfterOccurrence'];
		}
		if(array_key_exists('repeatWeekdayList', $args)){
			if(is_string($args['repeatWeekdayList']) && strlen($args['repeatWeekdayList']) > 0){
				$this->repeatWeekdayList = array_map('intval', explode(',', $args['repeatWeekdayList']));
			}else if(is_array($args['repeatWeekdayList'])){
				$this->repeatWeekdayList = $args['repeatWeekdayList'];
			}else{
				$this->repeatWeekdayList = array();
			}
		}
		if(array_key_exists('checkinWeekdayList', $args)){
			if(is_string($args['checkinWeekdayList']) && strlen($args['checkinWeekdayList']) > 0){
				$this->checkinWeekdayList = array_map('intval', explode(',', $args['checkinWeekdayList']));
			}else if(is_array($args['checkinWeekdayList'])){
				$this->checkinWeekdayList = $args['checkinWeekdayList'];
			}else{
				$this->checkinWeekdayList = array();
			}
		}
		if(array_key_exists('checkoutWeekdayList', $args)){
			if(is_string($args['checkoutWeekdayList']) && strlen($args['checkoutWeekdayList']) > 0){
				$this->checkoutWeekdayList = array_map('intval', explode(',', $args['checkoutWeekdayList']));
			}else if(is_array($args['checkoutWeekdayList'])){
				$this->checkoutWeekdayList = $args['checkoutWeekdayList'];
			}else{
				$this->checkoutWeekdayList = array();
			}
		}
		if(array_key_exists('syncList', $args)){
			if(is_string($args['syncList']) && strlen($args['syncList']) > 0){
				$this->syncList = array_map('intval', explode(',', $args['syncList']));
			}else if(is_array($args['syncList'])){
				$this->syncList = $args['syncList'];
			}else{
				$this->syncList = array();
			}
		}
		if(array_key_exists('endDate', $args)){
			if($args['endDate'] instanceOf Calendarista_DateTime){
				$this->endDate = $args['endDate'];
			}else if($args['endDate']){
				$this->endDate = new Calendarista_DateTime($args['endDate']);
			}
		}
		if(array_key_exists('color', $args)){
			$this->color = (string)$args['color'];
		}
		if(array_key_exists('timezone', $args)){
			$this->timezone = (string)$args['timezone'];
		}
		if(array_key_exists('imageUrl', $args)){
			$this->imageUrl = (string)$args['imageUrl'];
		}
		if(array_key_exists('searchThumbnailUrl', $args)){
			$this->searchThumbnailUrl = (string)$args['searchThumbnailUrl'];
		}
		if(array_key_exists('name', $args)){
			$this->name = (string)$args['name'];
		}
		if(array_key_exists('regionAddress', $args)){
			$this->regionAddress = (string)$args['regionAddress'];
		}
		if(array_key_exists('regionLat', $args)){
			$this->regionLat = (string)$args['regionLat'];
		}
		if(array_key_exists('regionLng', $args)){
			$this->regionLng = (string)$args['regionLng'];
		}
		if(array_key_exists('regionMarkerIconUrl', $args)){
			$this->regionMarkerIconUrl = (string)$args['regionMarkerIconUrl'];
		}
		if(array_key_exists('regionMarkerIconWidth', $args)){
			$this->regionMarkerIconWidth = (int)$args['regionMarkerIconWidth'];
		}
		if(array_key_exists('regionMarkerIconHeight', $args)){
			$this->regionMarkerIconHeight = (int)$args['regionMarkerIconHeight'];
		}
		if(array_key_exists('regionInfoWindowIcon', $args)){
			$this->regionInfoWindowIcon = (string)$args['regionInfoWindowIcon'];
		}
		if(array_key_exists('regionInfoWindowDescription', $args)){
			$this->regionInfoWindowDescription = (string)$args['regionInfoWindowDescription'];
		}
		if(array_key_exists('styledMaps', $args) && $args['styledMaps']){
			$this->styledMaps = (string)$args['styledMaps'];
		}
		if(array_key_exists('showMapMarker', $args) && isset($args['showMapMarker'])){
			$this->showMapMarker = (bool)$args['showMapMarker'];
		}
		if(array_key_exists('maxTimeslots', $args) && $args['maxTimeslots']){
			$this->maxTimeslots = (int)$args['maxTimeslots'];
		}
		if(array_key_exists('minimumTimeslotCharge', $args)){
			$this->minimumTimeslotCharge = (double)$args['minimumTimeslotCharge'];
		}
		if(array_key_exists('maximumNotice', $args)){
			$this->maximumNotice = (int)$args['maximumNotice'];
		}
		if(array_key_exists('minimumNotice', $args)){
			$this->minimumNotice = (int)$args['minimumNotice'];
		}
		if(array_key_exists('bookingDaysMinimum', $args)){
			$this->bookingDaysMinimum = (int)$args['bookingDaysMinimum'];
		}
		if(array_key_exists('bookingDaysMaximum', $args)){
			$this->bookingDaysMaximum = (int)$args['bookingDaysMaximum'];
		}
		if(array_key_exists('turnoverBefore', $args)){
			$this->turnoverBefore = (int)$args['turnoverBefore'];
		}
		if(array_key_exists('turnoverAfter', $args)){
			$this->turnoverAfter = (int)$args['turnoverAfter'];
		}
		if(array_key_exists('turnoverBeforeMin', $args)){
			$this->turnoverBeforeMin = (int)$args['turnoverBeforeMin'];
		}
		if(array_key_exists('turnoverAfterMin', $args)){
			$this->turnoverAfterMin = (int)$args['turnoverAfterMin'];
		}
		if(array_key_exists('description', $args)){
			$this->description = $args['description'];
		}
		if(array_key_exists('timeMode', $args)){
			$this->timeMode = (int)$args['timeMode'];
		}
		if(array_key_exists('displayRemainingSeats', $args)){
			$this->displayRemainingSeats = (bool)$args['displayRemainingSeats'];
		}
		if(array_key_exists('displayRemainingSeatsMessage', $args)){
			$this->displayRemainingSeatsMessage = (bool)$args['displayRemainingSeatsMessage'];
		}
		if(array_key_exists('calendarMode', $args)){
			$this->calendarMode = (int)$args['calendarMode'];
		}
		if(array_key_exists('searchPage', $args)){
			$this->searchPage = (int)$args['searchPage'];
		}
		if(array_key_exists('timeDisplayMode', $args)){
			$this->timeDisplayMode = (int)$args['timeDisplayMode'];
		}
		if(array_key_exists('dayCountMode', $args)){
			$this->dayCountMode = (int)$args['dayCountMode'];
		}
		if(array_key_exists('appendPackagePeriodToName', $args)){
			$this->appendPackagePeriodToName = (bool)$args['appendPackagePeriodToName'];
		}
		if(array_key_exists('minimumNoticeMinutes', $args)){
			$this->minimumNoticeMinutes = (int)$args['minimumNoticeMinutes'];
		}
		if(array_key_exists('extendTimeRangeNextDay', $args)){
			$this->extendTimeRangeNextDay = (bool)$args['extendTimeRangeNextDay'];
		}
		if(array_key_exists('minTime', $args)){
			$this->minTime = (int)$args['minTime'];
		}
		if(array_key_exists('maxTime', $args)){
			$this->maxTime = (int)$args['maxTime'];
		}
		if(array_key_exists('maxDailyRepeatFrequency', $args)){
			$this->maxDailyRepeatFrequency = (bool)$args['maxDailyRepeatFrequency'];
		}
		if(array_key_exists('maxWeeklyRepeatFrequency', $args)){
			$this->maxWeeklyRepeatFrequency = (bool)$args['maxWeeklyRepeatFrequency'];
		}
		if(array_key_exists('maxMonthlyRepeatFrequency', $args)){
			$this->maxMonthlyRepeatFrequency = (bool)$args['maxMonthlyRepeatFrequency'];
		}
		if(array_key_exists('maxYearlyRepeatFrequency', $args)){
			$this->maxYearlyRepeatFrequency = (bool)$args['maxYearlyRepeatFrequency'];
		}
		if(array_key_exists('maxRepeatOccurrence', $args)){
			$this->maxRepeatOccurrence = (int)$args['maxRepeatOccurrence'];
		}
		if(array_key_exists('returnSameDay', $args)){
			$this->returnSameDay = (bool)$args['returnSameDay'];
		}
		if(array_key_exists('maxRepeatFrequency', $args)){
			$this->maxRepeatFrequency = (int)$args['maxRepeatFrequency'];
		}
		if(array_key_exists('guestNameRequired', $args)){
			$this->guestNameRequired = (bool)$args['guestNameRequired'];
		}
		if(array_key_exists('displayDateSelectionReq', $args)){
			$this->displayDateSelectionReq = (bool)$args['displayDateSelectionReq'];
		}
		if(array_key_exists('enableFullAmountOrDeposit', $args)){
			$this->enableFullAmountOrDeposit = (bool)$args['enableFullAmountOrDeposit'];
		}
		if(array_key_exists('fullAmountDiscount', $args)){
			$this->fullAmountDiscount = (double)$args['fullAmountDiscount'];
		}
		if(array_key_exists('instructions', $args)){
			$this->instructions = $args['instructions'];
		}
		if(array_key_exists('hideMapDisplay', $args)){
			$this->hideMapDisplay = (bool)$args['hideMapDisplay'];
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex = (int)$args['orderIndex'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'name'=>$this->name
			, 'imageUrl'=>$this->imageUrl
			, 'searchThumbnailUrl'=>$this->searchThumbnailUrl
			, 'timezone'=>$this->timezone
			, 'color'=>$this->color
			, 'endDate'=>$this->endDate ? $this->endDate->format(CALENDARISTA_FULL_DATEFORMAT) : null
			, 'repeatWeekdayList'=>$this->repeatWeekdayList
			, 'terminateAfterOccurrence'=>$this->terminateAfterOccurrence
			, 'terminateMode'=>$this->terminateMode
			, 'repeatInterval'=>$this->repeatInterval
			, 'repeatFrequency'=>$this->repeatFrequency
			, 'fullDay'=>$this->fullDay
			, 'hasRepeat'=>$this->hasRepeat
			, 'seats'=>$this->seats
			, 'seatsMaximum'=>$this->seatsMaximum
			, 'seatsMinimum'=>$this->seatsMinimum
			, 'selectableSeats'=>$this->selectableSeats
			, 'daysInPackage'=>$this->daysInPackage
			, 'returnOptional'=>$this->returnOptional
			, 'returnCost'=>$this->returnCost
			, 'cost'=>$this->cost
			, 'customChargeDays'=>$this->customChargeDays
			, 'customCharge'=>$this->customCharge
			, 'customChargeMode'=>$this->customChargeMode
			, 'deposit'=>$this->deposit
			, 'depositMode'=>$this->depositMode
			, 'availableDate'=>$this->availableDate ? $this->availableDate->format(CALENDARISTA_FULL_DATEFORMAT) : null
			, 'projectId'=>$this->projectId
			, 'checkinWeedayList'=>$this->checkinWeekdayList
			, 'checkoutWeekdayList'=>$this->checkoutWeekdayList
			, 'maxTimeslots'=>$this->maxTimeslots
			, 'minimumTimeslotCharge'=>$this->minimumTimeslotCharge
			, 'maximumNotice'=>$this->maximumNotice
			, 'minimumNotice'=>$this->minimumNotice
			, 'bookingDaysMinimum'=>$this->bookingDaysMinimum
			, 'bookingDaysMaximum'=>$this->bookingDaysMaximum
			, 'turnoverBefore'=>$this->turnoverBefore
			, 'turnoverAfter'=>$this->turnoverAfter
			, 'turnoverBeforeMin'=>$this->turnoverBeforeMin
			, 'turnoverAfterMin'=>$this->turnoverAfterMin
			, 'syncList'=>$this->syncList
			, 'description'=>$this->description
			, 'timeMode'=>$this->timeMode
			, 'displayRemainingSeats'=>$this->displayRemainingSeats
			, 'displayRemainingSeatsMessage'=>$this->displayRemainingSeatsMessage
			, 'calendarMode'=>$this->calendarMode
			, 'orderIndex'=>$this->orderIndex
			, 'timeDisplayMode'=>$this->timeDisplayMode
			, 'dayCountMode'=>$this->dayCountMode
			, 'appendPackagePeriodToName'=>$this->appendPackagePeriodToName
			, 'minimumNoticeMinutes'=>$this->minimumNoticeMinutes
			, 'extendTimeRangeNextDay'=>$this->extendTimeRangeNextDay
			, 'minTime'=>$this->minTime
			, 'maxTime'=>$this->maxTime
			, 'maxDailyRepeatFrequency'=>$this->maxDailyRepeatFrequency
			, 'maxWeeklyRepeatFrequency'=>$this->maxWeeklyRepeatFrequency
			, 'maxMonthlyRepeatFrequency'=>$this->maxMonthlyRepeatFrequency
			, 'maxYearlyRepeatFrequency'=>$this->maxYearlyRepeatFrequency
			, 'maxRepeatOccurrence'=>$this->maxRepeatOccurrence
			, 'returnSameDay'=>$this->returnSameDay
			, 'maxRepeatFrequency'=>$this->maxRepeatFrequency
			, 'guestNameRequired'=>$this->guestNameRequired
			, 'displayDateSelectionReq'=>$this->displayDateSelectionReq
			, 'enableFullAmountOrDeposit'=>$this->enableFullAmountOrDeposit
			, 'fullAmountDiscount'=>$this->fullAmountDiscount
			, 'instructions'=>$this->instructions
		);
	}
	public function toMapArray($id){
		if($this->regionAddress){
			return array(
				'id'=>$id
				, 'regionAddress'=>$this->regionAddress
				, 'regionLat'=>$this->regionLat
				, 'regionLng'=>$this->regionLng
				, 'regionMarkerIconUrl'=>$this->regionMarkerIconUrl
				, 'regionMarkerIconWidth'=>$this->regionMarkerIconWidth
				, 'regionMarkerIconHeight'=>$this->regionMarkerIconHeight
				, 'regionInfoWindowIcon'=>$this->regionInfoWindowIcon
				, 'regionInfoWindowDescription'=>$this->regionInfoWindowDescription
				, 'styledMaps'=>$this->styledMaps ? json_decode(str_replace('\\', '', $this->styledMaps)) : null
				, 'showMapMarker'=>$this->showMapMarker
				, 'hideMapDisplay'=>$this->hideMapDisplay
			);
		}
		return null;
	}
	public function depositToString(){
		if($this->deposit){
			return $this->depositMode ? 
				Calendarista_MoneyHelper::toLongCurrency($this->deposit) : 
				$this->deposit . '%';
		}
		return null;
	}
	protected function init(){
		$this->name = Calendarista_TranslationHelper::t('availability_name' . $this->id, $this->name);
		$this->description = Calendarista_TranslationHelper::t('availability_description' . $this->id, $this->description);
		$this->regionInfoWindowDescription = Calendarista_TranslationHelper::t('availability_regionInfoWindowDescription' . $this->id, $this->regionInfoWindowDescription);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('availability_name' . $this->id, $this->name);
		Calendarista_TranslationHelper::register('availability_description' . $this->id, $this->description);
		Calendarista_TranslationHelper::register('availability_regionInfoWindowDescription' . $this->id, $this->regionInfoWindowDescription);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('availability_name' . $this->id);
		Calendarista_TranslationHelper::unregister('availability_description' . $this->id);
		Calendarista_TranslationHelper::unregister('availability_regionInfoWindowDescription' . $this->id);
	}
}
?>