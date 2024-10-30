<?php
class Calendarista_TimeslotHelper{
	public $project;
	public $availability;
	public $availabilityStartDateTime;
	public $availabilityTerminationDateTime = null;
	public $timeslots;
	public $clientTime;
	public $timezone;
	public $hasReturnTrip;
	private $repo;
	public function __construct($args){
		if(array_key_exists('availability', $args)){
			$this->availability = $args['availability'];
		}
		if(array_key_exists('project', $args)){
			$this->project = $args['project'];
		}
		if(array_key_exists('clientTime', $args)){
			$this->clientTime = $args['clientTime'];
		}
		if(array_key_exists('timezone', $args)){
			$this->timezone = $args['timezone'];
		}
		$this->repo = new Calendarista_TimeslotRepository();
		$this->hasReturnTrip = $this->repo->availabilityHasReturnTrip($this->availability->id);
		$this->timeslots = $this->repo->readAllByAvailability($this->availability->id);
		$this->availabilityStartDateTime = strtotime($this->availability->availableDate);
		if($this->availability->terminateMode === 2/*ON_END_DATE*/){
			$this->availabilityTerminationDateTime = strtotime($this->availability->endDate);
		}
	}
	public static function filterTimeslots($date, $timeslots){
		$selectedDate = new Calendarista_DateTime($date);
		$weekday = (int)date('N', strtotime($date));
		$resultByDate = array();
		$resultByWeekday = array();
		foreach($timeslots as $timeslot){
			if($timeslot->compareDay($selectedDate)){
				array_push($resultByDate, $timeslot);
			}else if($weekday === $timeslot->weekday){
				array_push($resultByWeekday, $timeslot);
			}
		}
		$result = $resultByWeekday;
		if(count($resultByDate) > 0){
			$result = $resultByDate;
		}
		usort($result, array('Calendarista_TimeslotHelper', 'sortByTime'));
		return $result;
	}
	protected function getHolidays($startDate, $availabilityId, $timeslots){
		$repo = new Calendarista_HolidaysRepository();
		$holidays = $repo->readHolidayContainsTimeslot($startDate, $availabilityId, false/*include also full day holiday*/);
		$result = array();
		foreach($holidays as $holiday){
			if($holiday['timeslotId']){
				if(!in_array($holiday['timeslotId'], $result)){
					array_push($result, $holiday['timeslotId']);
				}
			}else{
				//it's a full day holiday
				foreach($timeslots as $timeslot){
					if(!in_array($timeslot->id, $result)){
						array_push($result, $timeslot->id);
					}
				}
			}
		}
		return $result;
	}
	public function getTimeslots($selectedDate, $bookedAvailabilities, $slotType = null, $appointment = -1/*1 = Edit mode*/){
		$result = array();
		$timeslots = self::filterTimeslots($selectedDate, $this->timeslots);
		$holidays = $this->getHolidays($selectedDate, $this->availability->id, $timeslots);
		$selectedDateFormatted = date(CALENDARISTA_DATEFORMAT, strtotime($selectedDate));
		//if we are in edit mode, ignore any validation for seats or past time
		foreach($timeslots as $key=>$slot){
			$timeslot = new Calendarista_Timeslot($slot->toArray());
			if($this->hasReturnTrip && $slotType !== null){
				if($slotType == 0 && $timeslot->returnTrip){
					continue;
				}else if($slotType == 1 && !$timeslot->returnTrip){
					continue;
				}
			}
			$seats = $timeslot->seats ? $timeslot->seats : $this->availability->seats;
			if($appointment === 1){
				array_push($result, $timeslot);
				continue;
			}
			if(in_array($timeslot->id, $holidays)){
				if($seats > 0){
					$timeslot->setOutOfStock();
					array_push($result, $timeslot);
				}
				continue;
			}
			
			$currentDateTime = strtotime("$selectedDateFormatted $timeslot->timeslot");
			$endTimeslot = isset($timeslots[$key+1]) ? $timeslots[$key+1] : null;
			$endDateTime = isset($endTimeslot) ?  strtotime("$selectedDateFormatted $endTimeslot->timeslot") : null;
			if($seats > 0){
				$usedSeats = $this->getUsedSeats($currentDateTime, $timeslot->timeslot, $bookedAvailabilities, $timeslots, $endDateTime);
				$timeslot->setUsedSeats($usedSeats);
				//if we are out of seats or if the booked seats so far are less than the minimum seat requirement, bail out
				if($seats <= $usedSeats || ($timeslot->seatsMinimum > 1 && (($seats - $usedSeats) < $timeslot->seatsMinimum))){
					$timeslot->setOutOfStock();
					array_push($result, $timeslot);
					continue;
				}
			}
			$currentDate = date('Y-m-d H:i', $currentDateTime);
			if($this->hasPadding($currentDate, $timeslot, $bookedAvailabilities)){
				$timeslot->setOutOfStock();
				array_push($result, $timeslot);
				continue;	
			}
			if($this->availabilityStartDateTime > $currentDateTime || ($this->availabilityTerminationDateTime && $currentDateTime > $this->availabilityTerminationDateTime)){
				//respect availability start time and termination time
				continue;
			}
			if($this->timeslotValid($selectedDateFormatted, $timeslot->timeslot)){
				array_push($result, $timeslot);
			}
		}
		if($this->availability->seats > 0 && in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)){
			//we can now add seats in availability (creating a seat pool per day), so handle
			$dailyPool = null;
			foreach($result as $timeslot){
				if($timeslot->seats > 0){
					continue;
				}
				$usedSeat = $timeslot->getUsedSeats();
				if($usedSeat > 0){
					if($dailyPool === null){
						$dailyPool = 0;
					}
					$dailyPool += $usedSeat;
				}
			}
			if($dailyPool !== null){
				foreach($result as $key=>$timeslot){
					if($timeslot->seats === 0 && $this->availability->seats > 0){
						$seats = $this->availability->seats - $dailyPool;
						if($seats <= 0 || ($this->availability->seatsMinimum > 1 && ($seats < $timeslot->seatsMinimum))){
							$timeslot->setOutOfStock();
							$result[$key] = $timeslot;
						}
					}
				}
			}
		}
		if(in_array($this->project->calendarMode, array(Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE)) && count($result) === 1){
			//a time range is valid if there are 2+ slots
			$result = array();
		}
		//convert time to admin defined time format
		foreach($result as $timeslot){
			$timeslot->timeslot = self::toLocalFormat($timeslot->timeslot);
		}
		usort($result, array('Calendarista_TimeslotHelper', 'sortByTime'));
		return $result;
	}
	public function hasTimeslots($selectedDate, $bookedAvailabilities){
		$result = $this->getTimeslots($selectedDate, $bookedAvailabilities);
		$count = 0;
		foreach($result as $r){
			if(!$r->outOfStock){
				$count += 1;
			}
		}
		if(in_array($this->project->calendarMode, array(Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE)) && $count === 1){
			//single day timerange requires atleast 2 slots.
			return false;
		}
		return $count > 0;
	}
	public function hasOutOfStockSlots($selectedDate, $bookedAvailabilities){
		$result = $this->getTimeslots($selectedDate, $bookedAvailabilities);
		$count = 0;
		foreach($result as $r){
			if($r->outOfStock){
				$count += 1;
			}
		}
		return $count > 0;
	}
	public function timeslotValid($date, $slot){
		$originalTimezone = Calendarista_TimeHelper::setTimezone($this->availability->timezone);
		$temp = new Calendarista_DateTime();
		//reference the date before we lose it when we reset timezone again
		$now = $temp->format(CALENDARISTA_DATEFORMAT);
		Calendarista_TimeHelper::setTimezone($originalTimezone);
		if($date !== $now || !$this->clientTime){
			return true;
		}
		$currentTime = new DateTime(date('H:i', strtotime($this->timeByZone($this->clientTime))));
		$timeToCompare = new DateTime(date('H:i', strtotime($slot)));
		return $timeToCompare > $currentTime;
	}
	public function hasPadding($currentDate, $timeslot, $bookedAvailabilities){
		$supportsPadding = $timeslot->hasPadding();
		$paddingBefore = $supportsPadding ? $timeslot->paddingTimeBefore : $this->availability->turnoverBeforeMin;
		$paddingAfter = $supportsPadding ? $timeslot->paddingTimeAfter : $this->availability->turnoverAfterMin;
		if(!($paddingBefore || $paddingAfter)){
			return false;
		}
		$timeslotRepo = new Calendarista_TimeslotRepository();
		if($bookedAvailabilities){
			foreach($bookedAvailabilities as $booked){
				if(!$booked['startTimeId']){
					continue;
				}
				$currentDateTimeObj = strtotime($currentDate);
				if(date(CALENDARISTA_DATEFORMAT, $currentDateTimeObj) == date(CALENDARISTA_DATEFORMAT, strtotime($booked['fromDate']))){
					$paddingTimeBefore = strtotime($booked['fromDate'] . sprintf(' - %d minute', $paddingBefore));
					$paddingTimeAfter = strtotime($booked['fromDate'] . sprintf(' + %d minute', $paddingAfter));
					//currenttime is in between the booked datetime padding range
					if($currentDateTimeObj >= $paddingTimeBefore && $currentDateTimeObj <= $paddingTimeAfter){
						if($timeslot->seats > 0){
							$tm = date("H:i", strtotime($booked['fromDate']));
							$dt = date(CALENDARISTA_DATEFORMAT, strtotime($currentDate));
							$usedSeats = $this->getUsedSeats(strtotime("$dt $tm"), $tm, $bookedAvailabilities);
							if(!($timeslot->seats <= $usedSeats || ($timeslot->seatsMinimum > 1 && (($timeslot->seats - $usedSeats) < $timeslot->seatsMinimum)))){
								return false;
							}
						}
						return true;
					}
				}
				if(!$booked['endTimeId'] || $booked['endTimeId'] == '-1'){
					continue;
				}
				if(date(CALENDARISTA_DATEFORMAT, $currentDateTimeObj) == date(CALENDARISTA_DATEFORMAT, strtotime($booked['toDate']))){
					$paddingTimeBefore = strtotime($booked['toDate'] . sprintf(' - %d minute', $paddingBefore));
					$paddingTimeAfter = strtotime($booked['toDate'] . sprintf(' + %d minute', $paddingAfter));
					//currenttime is in between the booked datetime padding range
					if($currentDateTimeObj >= $paddingTimeBefore && $currentDateTimeObj <= $paddingTimeAfter){
						if($timeslot->seats > 0){
							$tm = date("H:i", strtotime($booked['toDate']));
							$dt = date(CALENDARISTA_DATEFORMAT, strtotime($currentDate));
							$hasPadding = $paddingTimeAfter > 0;
							$usedSeats = $this->getUsedSeats(strtotime("$dt $tm"), $tm, $bookedAvailabilities, null, null, $hasPadding);
							if(!($timeslot && ($timeslot->seats <= $usedSeats || ($timeslot->seatsMinimum > 1 && (($timeslot->seats - $usedSeats) < $timeslot->seatsMinimum))))){
								return false;
							}
						}
						return true;
					}
				}
			}
		}
		return false;
	}
	public function getUsedSeats($selectedStartDate, $selectedTimeslot, $bookedAvailabilities, $timeslots = null, $endDateTime = null, $hasPadding = false){
		$result = 0;
		$selectedDateFormatted = date(CALENDARISTA_DATEFORMAT, $selectedStartDate);
		if($bookedAvailabilities){
			foreach($bookedAvailabilities as $booked){
				$calendarMode = (int)$booked['calendarMode'];
				$startDate = strtotime($booked['fromDate']);
				$endDate = strtotime($booked['toDate']); 
				$bookedSeats = intval($booked['seats']) ? (int)$booked['seats'] : 1;
				if($calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE && $timeslots){
					$selectedEndDate = null;
					$len = count($timeslots);
					for($i = 0; $i < $len; $i++){
						$startTimeslot = $timeslots[$i];
						$j = $i < ($len - 1) ? ($i+1) : ($i-1);
						if($startTimeslot->timeslot == $selectedTimeslot && isset($timeslots[$j])){
							$temp = $timeslots[$j];
							if($i < ($len - 1)){
								$tempStartDate = $selectedStartDate;
								$tempEndDate = strtotime("$selectedDateFormatted $temp->timeslot");
							}else{
								$tempStartDate = strtotime("$selectedDateFormatted $temp->timeslot");
								$tempEndDate = $selectedStartDate;
							}
							if($tempEndDate && (($startDate <= $tempStartDate && $endDate >= $tempEndDate) || ($tempStartDate <= $startDate && $tempEndDate >= $endDate))){
								$result += $bookedSeats;
							}
							break;
						}
					}
				}else if($calendarMode === Calendarista_CalendarMode::ROUND_TRIP_WITH_TIME){
					if($selectedStartDate == $startDate || $selectedStartDate == $endDate){
						$result += $bookedSeats;
					}
				}else {
					if($calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE){
						if($selectedStartDate >= $startDate && (($hasPadding && $selectedStartDate <= $endDate) || $selectedStartDate < $endDate)){
							$result += $bookedSeats;
						}else if($startDate >= $selectedStartDate && $endDate <= $endDateTime){
							$result += $bookedSeats;
						}
					}else{
						$filterResult = apply_filters('calendarista_timeslot_used_seat_status', false, $selectedDateFormatted, date(CALENDARISTA_DATEFORMAT, $startDate), $this->availability, $booked);
						if($filterResult){
							$result += $bookedSeats;
						} else if($selectedStartDate >= $startDate && $selectedStartDate <= $endDate){
							$result += $bookedSeats;
						}else if ($endDateTime && ($startDate > $selectedStartDate && $endDate < $endDateTime)){
							$result += $bookedSeats;
						}
					}
				}
			}
		}
		return $result;
	}
	public static function selectedTimeslot($timeslot, $startTimeslot, $endTimeslot, $multipleSlotSelection){
		$now = new Calendarista_DateTime();
		$nowFormatted = $now->format(CALENDARISTA_DATEFORMAT);
		$selectedTime = strtotime("$nowFormatted $timeslot");
		$startTime = strtotime("$nowFormatted $startTimeslot");
		$endTime = strtotime("$nowFormatted $startTimeslot");
		if($endTimeslot && $multipleSlotSelection){
			$endTime = strtotime("$nowFormatted $endTimeslot");
		}
		if($selectedTime >= $startTime && $selectedTime <= $endTime){
			return true;
		}
		return false;
	}
	public static function sortByTime($a, $b){
		return (strtotime($a->timeslot) <=> strtotime($b->timeslot));
	}
	public function timeByZone($timeslot){
		return Calendarista_TimeHelper::formatTime(array(
			'timezone'=>$this->timezone
			, 'serverTimezone'=>$this->availability->timezone
			, 'time'=>$timeslot
		));
	}
	public static function toLocalFormat($time){
		// 12-hour time to 24-hour time 
		$format =  Calendarista_TimeHelper::getTimeFormat();
		$now = new DateTime($time);
		Calendarista_TimeHelper::loadTranslationEarly();
		return date_i18n($format, strtotime($now->format('Y-m-d H:i:s')));
	}
}
?>