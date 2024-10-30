<?php
class Calendarista_BookingTimeslotsTmpl extends Calendarista_TemplateBase{
	public $timeslots;
	public $availability;
	public $multipleSlotSelection;
	public $placeholderClassName;
	public $placeholderRequired;
	public $fieldName;
	public $errorContainerId;
	public $resetButton;
	public $availabilityId;
	public $slotType;
	public $enableResetButton;
	public $timeslotsUnavailable;
	public $clientTime;
	public $timezone;
	public $selectedDate;
	public $selectedTime;
	public $timeLabel;
	public $project;
	public $errorMessage;
	public $startTimeslot;
	public $endTimeslot;
	public $appointment;
	public $availabilityHelper;
	public $searchResultTime = null;
	public $dailyPool = 0;
	public function __construct($slotType = 0, $placeholderRequired = false){
		parent::__construct();
		if(!in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
			//does not support timeslots
			return;
		}
		$this->placeholderRequired = $placeholderRequired;
		$this->errorMessage = $this->stringResources['SELECT_DAY_AND_TIMESLOT'];
		$this->slotType = $slotType;
		$this->placeholderClassName = 'calendarista-start-timeslot-placeholder';
		$this->fieldName = 'startTime';
		$this->errorContainerId = 'startTime' . $this->uniqueId;
		$this->resetButton = 'calendarista-starttime-reset';
		$this->selectedTime = (int)$this->getPostValue('selectedStartTime', -1);
		if($this->selectedTime === -1){
			$this->selectedTime = (int)$this->getViewStateValue('startTime', -1);
		}
		if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME){
			$this->selectedTime = -1;
		}
		$this->timeLabel = $this->stringResources['BOOKING_SELECT_START_TIME'];
		$this->project = Calendarista_ProjectHelper::getProject($this->projectId);
		$this->appointment = (int)$this->getPostValue('appointment', -1);
		if($slotType){
			$this->placeholderClassName = 'calendarista-end-timeslot-placeholder';
			$this->fieldName = 'endTime';
			$this->errorContainerId = 'endTime' . $this->uniqueId;
			$this->resetButton = 'calendarista-endtime-reset';
			$this->selectedTime = (int)$this->getPostValue('selectedEndTime', -1);
			if($this->selectedTime === -1){
				$this->selectedTime = (int)$this->getViewStateValue('endTime', -1);
			}
			$this->timeLabel = $this->stringResources['BOOKING_SELECT_END_TIME'];
		}
		$this->selectedDate = $this->getPostValue('selectedDate');
		$this->clientTime = $this->getPostValue('clientTime');
		$this->timezone = $this->getPostValue('timezone');
		$this->availabilityId = (int)$this->getPostValue('availabilityId');
		$includeTimeslots = false;
		if($this->selectedDate){
			$sameDay = filter_var($this->getPostValue('sameDay'), FILTER_VALIDATE_BOOLEAN);
			$this->errorMessage = $this->stringResources['SELECTED_DAY_TIMESLOT_UNAVAILABLE'];
			$this->availabilityHelper = new Calendarista_AvailabilityHelper(array(
				'projectId'=>$this->projectId
				, 'availabilityId'=>$this->availabilityId
				, 'clientTime'=>$this->appointment === 1 ? null : $this->clientTime
				, 'timezone'=>$this->timezone
			));
			$this->availability = $this->availabilityHelper->availability;
			$bookedAvailabilities = $this->availabilityHelper->getCurrentMonthAvailabilities($this->selectedDate);
			$this->timeslots = $this->availabilityHelper->timeslotHelper->getTimeslots($this->selectedDate, $bookedAvailabilities, $slotType);
			$this->prepareStartTime();
			if($this->timeslotCount() >= 1){
				$this->applyMinNotice();
				foreach($this->timeslots as $timeslot){
					$usedSeat = $timeslot->getUsedSeats();
					if($usedSeat > 0){
						$this->dailyPool += $usedSeat;
					}
				}
				$includeTimeslots = true;
				if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIME_RANGE) && $sameDay){
					if($this->availabilityHelper->timeslotHelper->hasReturnTrip){
						if($this->timeslotCount() === 0){
							$includeTimeslots = false;
						}
					}else if($this->timeslotCount() <= 1){
						//we need atleast 2 slots in time range mode
						$includeTimeslots = false;
					}else{
						if($slotType && $sameDay){
							$includeTimeslots = $this->shiftTimeslot();
						} else if(!$slotType && $sameDay){
							$includeTimeslots = $this->popTimeslot();
						}
					}
				}
			}
			if($slotType && $this->availability->extendTimeRangeNextDay){
				$nextDay = date(CALENDARISTA_DATEFORMAT, strtotime('+1 day', strtotime($this->selectedDate)));
				$nextDaySlots = $this->availabilityHelper->timeslotHelper->getTimeslots($nextDay, $bookedAvailabilities, $slotType, $this->appointment);
				$firstSlot = $this->timeslots[0];
				$beginDateTime1 = strtotime("$this->selectedDate $firstSlot->timeslot");
				foreach($nextDaySlots as $timeslot){
					$beginDateTime2 = strtotime("$this->selectedDate $timeslot->timeslot");
					if($beginDateTime2 >= $beginDateTime1)
					{
						break;
					}
					$usedSeat = $timeslot->getUsedSeats();
					if($usedSeat > 0){
						$this->dailyPool += $usedSeat;
					}
					array_push($this->timeslots, $timeslot);
				}
			}
			$this->multipleSlotSelection = $this->availability->maxTimeslots > 1;
			$this->startTimeslot = $this->getViewStateValue('startTimeslot');
			$this->endTimeslot = $this->getViewStateValue('endTimeslot');
			//enable reset button only for timeslots from/to range pairs
			$this->enableResetButton = in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS_RANGE);
		}
		if($this->multipleSlotSelection){
			$this->fieldName .= '[]';
		}
		if($includeTimeslots){
			$this->searchResultInit();
			$this->render();
		}else{
			$this->renderPartial();
		}
	}
	protected function prepareStartTime(){
		$result = array();
		$flag1 = false;
		if($this->timeslotCount() >= 1){
			foreach($this->timeslots as $timeslot){
				if($timeslot->startTime){
					$flag1 = true;
				}
				if($flag1){
					array_push($result, $timeslot);
				}
			}
			if(count($result) > 0){
				$this->timeslots = $result;
			}
		}
	}
	protected function applyMinNotice(){
		if($this->appointment === 1){
			return;
		}
		$min = $this->availability->minimumNoticeMinutes;
		if(!$min){
			return;
		}
		if(!$this->timeslots || !$this->clientTime){
			return;
		}
		$now = date(CALENDARISTA_DATEFORMAT, strtotime('now'));
		$today = strtotime(' +' . $min . ' minutes', strtotime($this->clientTime));
		$flag = false;
		foreach($this->timeslots as $key=>$value){
			$currentDateTime = strtotime("$this->selectedDate $value->timeslot");
			if($currentDateTime < $today){
				$flag = true;
				unset($this->timeslots[$key]);
			}
		}
		if($flag){
			array_values($this->timeslots);
		}
	}
	protected function searchResultInit(){
		$searchResultStartTime = isset($_POST['searchResultStartTime']) ? $this->getTimeslotByTime(sanitize_text_field($_POST['searchResultStartTime'])) : null;
		$searchResultEndTime = isset($_POST['searchResultEndTime']) ? $this->getTimeslotByTime(sanitize_text_field($_POST['searchResultEndTime'])) : null;
		if($searchResultStartTime){
			$this->startTimeslot = $searchResultStartTime->timeslot;
		}
		if($searchResultEndTime){
			$this->endTimeslot = $searchResultEndTime->timeslot;
		}
		if(!$this->slotType){
			$this->selectedTime = $searchResultStartTime ? $searchResultStartTime->id : $this->selectedTime;
		}else{
			$this->selectedTime = $searchResultEndTime ? $searchResultEndTime->id : $this->selectedTime;
		}
	}
	protected function shiftTimeslot(){
		//shift an element from the top of the timeslots, 
		//i.e. removes an element
		if($this->timeslots){
			if($this->timeslotCount() > 1){
				array_shift($this->timeslots);
			}
		}
		return true;
	}
	protected function popTimeslot(){
		//pop an element from the end of the timeslots
		if($this->timeslots && $this->timeslotCount() > 1){
			array_pop($this->timeslots);
			return true;
		}
		//since this is start time, last element needs to be discarded
		return false;
	}
	protected function timeslotCount(){
		$outOfStock = 0;
		if(!$this->timeslots){
			return 0;
		}
		foreach($this->timeslots as $timeslot){
			if($timeslot->outOfStock && $this->appointment !== 1){
				++$outOfStock;
			}
		}
		if($outOfStock > 0){
			return count($this->timeslots) - $outOfStock;
		}
		return count($this->timeslots);
	}
	public function formatTime($timeslot, $deal = false){
		$seats = $timeslot->seats > 0 ? $timeslot->seats : $this->availability->seats;
		if($seats > 1 && ($this->availability->displayRemainingSeats || $this->appointment === 1)){
			$seats = $timeslot->seats > 0 ? $timeslot->getSeatCount() :  $seats - $this->dailyPool;
			if($seats < 0){
				$seats = 0;
			}
			return $deal ? $timeslot->timeslot : sprintf($this->stringResources['TIMESLOT_SEATS_LABEL'], $timeslot->timeslot, $seats);
		}
		return $timeslot->timeslot;
	}
	public function returnTripSlot($timeslot){
		if($timeslot->returnTrip){
			return 'data-calendarista-return-trip';
		}
		return null;
	}
	public function selectedValue($timeslot, $attr = 'selected=selected'){
		if($timeslot->outOfStock && $this->appointment !== 1){
			return 'data-calendarista-outofstock';
		}
		if(!$this->multipleSlotSelection && $this->selectedTime !== -1){
			//if we have a timeslot selected, then maintain it.
			if($this->selectedTime == $timeslot->id){
				return $attr;
			}
			return '';
		}
		$startTimeslot = $this->startTimeslot;
		$endTimeslot = $this->endTimeslot;
		if(!$this->multipleSlotSelection){
			if($this->slotType === 1){
				$startTimeslot = $this->endTimeslot;
			}
		}
		if(!$startTimeslot){
			return '';
		}
		$selected = $this->availabilityHelper->timeslotHelper->selectedTimeslot(
			$timeslot->timeslot, 
			$startTimeslot, 
			$endTimeslot, 
			$this->multipleSlotSelection
		);
		if($selected){
			return $attr;
		}
		return '';
	}
	public function getTimeslotByTime($val){
		if($val && ($this->timeslots && $this->timeslotCount() >= 1)){
			$ts = Calendarista_TimeslotHelper::toLocalFormat($val);
			foreach($this->timeslots as $timeslot){
				if($timeslot->timeslot == $ts){
					return $timeslot;
				}
			}
		}
		return null;
	}
	public function renderPartial(){
	?>
	<div class="<?php echo esc_attr($this->placeholderClassName) ?>"></div>
	<div id="<?php echo $this->errorContainerId ?>_error_container"></div>
	<?php if($this->placeholderRequired && $this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE):?>
	<div class="calendarista-end-timeslot-placeholder"></div>
	<div id="<?php echo 'endtime' . $this->uniqueId ?>_error_container"></div>
	<?php endif;?>
	<?php
	}
	public function render(){
		if($this->availability->timeDisplayMode === 0/*standard*/ || $this->availability->maxTimeslots > 1){
			new Calendarista_TimeslotStandardViewTmpl($this); 
		}else if($this->availability->timeDisplayMode === 1/**/){
			new Calendarista_TimeslotDealsViewTmpl($this); 
		}	
	}
}
