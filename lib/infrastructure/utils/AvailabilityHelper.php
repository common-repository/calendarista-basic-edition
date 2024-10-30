<?php
class Calendarista_AvailabilityHelper{
	public $projectId;
	public $availabilityId;
	public $availability;
	public $clientTime;
	public $project;
	public $timeslotHelper;
	public $generalSetting;
	public function __construct($args){
		$clientTime = null;
		$timezone = null;
		if(array_key_exists('clientTime', $args)){
			$this->clientTime = $args['clientTime'];
		}
		if(array_key_exists('timezone', $args)){
			$timezone = $args['timezone'];
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('availabilityId', $args)){
			$this->availabilityId = (int)$args['availabilityId'];
		}
		if(array_key_exists('availability', $args)){
			$this->availability = $args['availability'];
			$this->projectId = $this->availability->projectId;
			$this->availabilityId = $this->availability->id;
		}else{
			$availabilityRepo = new Calendarista_AvailabilityRepository();
			$this->availability = $availabilityRepo->read($this->availabilityId);
		}
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->synchronizeFeeds();
		$projectRepo = new Calendarista_ProjectRepository();
		$this->project = $projectRepo->read($this->projectId);
		$this->timeslotHelper = new Calendarista_TimeslotHelper(array(
			'availability'=>$this->availability
			, 'clientTime'=>$this->clientTime
			, 'timezone'=>$timezone
			, 'project'=>$this->project
		));
	}
	protected function synchronizeFeeds(){
		if($this->generalSetting->cronJobFeedTimeout > 0){
			//exit, we are using a cron job, much more efficient on resources
			return false;
		}
		return Calendarista_FeedHelper::synchronize($this->projectId, $this->availabilityId);
	}
	public function getAvailability($date){
		//check if date has availability, not so useful as the calendar only displays days with availability
		if($this->availability){
			$result = Calendarista_RepeatHelper::hasAvailability($date, $this->availability);
			if($result){
				return $this->availability;
			}
		}
		return null;
	}
	public static function active($availability){
		$originalTimezone = Calendarista_TimeHelper::setTimezone($availability->timezone);
		$result = !self::availabilityHasTerminated($availability);
		Calendarista_TimeHelper::setTimezone($originalTimezone);
		return $result;
	}
	public static function availabilityHasTerminated($availability){
		$now = strtotime('today midnight');
		//default termination date.
		$terminationDate = strtotime('+1 year midnight');
		$result = true;
		$availableDays = Calendarista_AvailabilityDayHelper::get($availability->id);
		if(!$availability->hasRepeat || count($availableDays) > 0){
			$today = new Calendarista_DateTime();
			$today->setTime(0,0);
			$lastAvailableDate = $availability->availableDate;
			$flag = false;
			if(count($availableDays) > 0){
				$lastAvailableDate = new Calendarista_DateTime($availableDays[0]);
				$flag = true;
			}
			if($today > $lastAvailableDate){
				//we have only 1 date in availability and it is past, so terminate
				return true;
			}
			$terminationDate = strtotime($lastAvailableDate->format(CALENDARISTA_DATEFORMAT) . ' midnight');
			if(!$flag){
				$now = $terminationDate;
			}
		}else if($availability->terminateMode !== 0/*NEVER*/){
			//set a termination date to speed up.
			$terminationDate = Calendarista_RepeatHelper::getTerminationDate($availability);
			if($terminationDate < strtotime('now')){
				return true;
			}
		}
		
		$availabilityHelper = new Calendarista_AvailabilityHelper(array('availability'=>$availability));
		$supportsTimeslots = in_array($availabilityHelper->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
		if(!$supportsTimeslots && $availability->seats === 0){
			return false;
		}
		$bookedAvailabilities = $availabilityHelper->getAvailabilities(date(CALENDARISTA_DATEFORMAT, $now), date(CALENDARISTA_DATEFORMAT, $terminationDate));
		while ($now <= $terminationDate) {
			$isBooked = $availabilityHelper->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $now), $bookedAvailabilities);
			if(!$isBooked){
				$result = false;
				//we have an available date, so exit
				break;
			}
			$now = strtotime('+1 day', $now);
		}
		return $result;
	}
	public static function getMinDate($availability){
		$now = strtotime('now');
		if(!$availability->availableDate){
			return $now;
		}
		$availableDate = strtotime($availability->availableDate);
		$terminationDate = Calendarista_RepeatHelper::getTerminationDate($availability);
		if($now < $availableDate){
			$now = strtotime($availability->availableDate);
		}
		$availabilityHelper = new Calendarista_AvailabilityHelper(array('availability'=>$availability));
		$bookedAvailabilities = $availabilityHelper->getAvailabilities(date(CALENDARISTA_DATEFORMAT, $now), date(CALENDARISTA_DATEFORMAT, $terminationDate));
		$isBooked = $availabilityHelper->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $now), $bookedAvailabilities);
		while ($isBooked || ($now <= $availableDate || ($now >= $availableDate && $now < $terminationDate))) {
			$isBooked = $availabilityHelper->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $now), $bookedAvailabilities);
			if(!$isBooked){
				//we have an available date, so exit
				return $now;
			}
			$now = strtotime('+1 day', $now);
		}
		return $now;
	}
	public static function checkAvailability($availability, $start, $end = null, $search = false){
		if(!$start){
			$start = date(CALENDARISTA_DATEFORMAT);
		}
		if(!$end){
			$end = $start;
		}
		$startDate = strtotime($start);
		$endDate = strtotime($end);
		$validRange = false;
		$terminationDate = Calendarista_RepeatHelper::getTerminationDate($availability);
		$availabilityHelper = new Calendarista_AvailabilityHelper(array('availability'=>$availability));
		$serviceName = $availabilityHelper->project->name;
		if($terminationDate < $startDate || $terminationDate < $endDate){
			return $serviceName;
		}
		$bookedAvailabilities = $availabilityHelper->getAvailabilities(date(CALENDARISTA_DATEFORMAT, $startDate), date(CALENDARISTA_DATEFORMAT, $endDate));
		if(in_array($availability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_RETURN)){
			//these are just two individual days start and return
			if(count($bookedAvailabilities) > 0){
				$booked1 = $availabilityHelper->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $startDate), $bookedAvailabilities, $search);
				$booked2 = $availabilityHelper->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $endDate), $bookedAvailabilities, $search);
				if($booked1 || $booked2){
					return $serviceName;
				}
			}
			return array('startDate'=>$start, 'endDate'=>$end, 'serviceName'=>$serviceName);
		}else if($availability->calendarMode === Calendarista_CalendarMode::PACKAGE){
			$result = $availabilityHelper->getNextOccurrenceByPackage();
			if(($result['startDate'] >= $startDate && $result['endDate'] <= $endDate)
				|| ($startDate >= $result['startDate'] && $startDate == $endDate)){
				return array('startDate'=>date(CALENDARISTA_DATEFORMAT, $result['startDate']), 'endDate'=>date(CALENDARISTA_DATEFORMAT, $result['endDate']), 'serviceName'=>$serviceName);
			}
			return $serviceName;
		}
		while ($startDate <= $endDate) {
			$pos = ($startDate === strtotime($start)) ? 0/*start*/ : (($endDate === strtotime($end)) ? 1/*end*/ : 2/*in between*/);
			$isBooked = $availabilityHelper->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $startDate), $bookedAvailabilities, $search, $pos);
			if(in_array($availability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_SEQUENCE)){
				//toDO: remember to count if the selected range is within the max days restriction
				$validRange = true;
				//in this case dates have to be in sequence, if even a single date is booked then exit
				if($isBooked){
					$validRange = false;
					break;
				}
			}else if(!$isBooked){
				//these are single day bookings, so
				//return the first date found that is not yet booked
				return array('startDate'=>date(CALENDARISTA_DATEFORMAT, $startDate), 'endDate'=>null, 'serviceName'=>$serviceName);
			}
			$startDate = strtotime('+1 day', $startDate);
		}
		if($validRange){
			return array('startDate'=>$start, 'endDate'=>$end, 'serviceName'=>$serviceName);
		}
		return $serviceName;
	}
	public static function getTotalUsedSeats($availability, $bookedAvailabilities = null, $dateToVerify = null){
		$seats = 0;
		if($bookedAvailabilities === null){
			$repo = new Calendarista_BookedAvailabilityRepository();
			$availabilities = array($availability->id);
			if(count($availability->syncList) > 0){
				$availabilities = array_merge($availabilities, $availability->syncList);
			}
			$result = $repo->readAll(array(
				'availabilities'=>$availabilities
				, 'status'=>Calendarista_AvailabilityStatus::CANCELLED/*ignore cancelled bookings*/
			));
			$bookedAvailabilities = array();
			if($result){
				$bookedAvailabilities = $result['resultset'];
			}
		}
		if($bookedAvailabilities){
			foreach($bookedAvailabilities as $booked){
				if($dateToVerify){
					$calendarMode = (int)$booked['calendarMode'];
					$currentDatetime = strtotime($dateToVerify);
					$currentDate = date(CALENDARISTA_DATEFORMAT, $currentDatetime);
					$fromDatetime = strtotime($booked['fromDate']);
					$fromDate = date(CALENDARISTA_DATEFORMAT, $fromDatetime);
					$toDatetime = strtotime($booked['toDate']);
					$toDate =  date(CALENDARISTA_DATEFORMAT, $toDatetime);
					if($calendarMode === Calendarista_CalendarMode::ROUND_TRIP){
						if($currentDate == $fromDate || $currentDate == $toDate){
							$seats += (int)$booked['seats'];
						}
					}else{
						if($currentDate == $fromDate
							|| ($toDatetime && ($currentDatetime > $fromDatetime &&  $currentDatetime <=  $toDatetime))){
							$seats += (int)$booked['seats'];
						}
					}
				}
			}
		}
		return $seats;
	}
	public static function seatAvailable($availability, $project, $bookedAvailabilities, $dateToVerify){
		if($availability->seats === 0){
			return true;
		}
		$usedSeats = self::getTotalUsedSeats($availability, $bookedAvailabilities, $dateToVerify);
		$availableSeats = $availability->seats;
		if($project->calendarMode === Calendarista_CalendarMode::CHANGEOVER){
			$lastDayOfMonth = strtotime(date('Y-m-t', strtotime($dateToVerify)));
			$changeover = self::changeover($dateToVerify, $lastDayOfMonth, $bookedAvailabilities, $availability);
			if($changeover['start_open'] || $changeover['end_open']){
				return true;
			}
		}
		$result = $usedSeats < $availableSeats && ($availableSeats - $usedSeats) >= $availability->seatsMinimum;
		return $result;
	}
	public function getAvailabilities($fromDate, $toDate){
		$repo = new Calendarista_BookedAvailabilityRepository();
		$availabilities = array($this->availability->id);
		if($this->availability->syncList && count($this->availability->syncList) > 0){
			$availabilities = array_merge($availabilities, $this->availability->syncList);
		}
		$bookedAvailabilities = $repo->readAllByDateRange($fromDate, $toDate, $availabilities, null, Calendarista_AvailabilityStatus::CANCELLED/*ignore cancelled bookings*/);
		$bookedAvailabilities = self::getAvailabilityFromCart($this->project, $bookedAvailabilities, $availabilities);
		return $bookedAvailabilities;
	}
	public function dateIsBooked($date, $bookedAvailabilities, $search = false, $pos = -1){
		$result = false;
		$flag1 = 0;
		$flag2 = 0;
		$supportsTimeslots = in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
		if(!$supportsTimeslots && $this->availability->seats === 0){
			return false;
		}
		$seats = self::seatAvailable($this->availability, $this->project, $bookedAvailabilities, $date);
		if(!$supportsTimeslots && (!$seats && $this->project->calendarMode !== Calendarista_CalendarMode::CHANGEOVER)){
			return true;
		}
		if($supportsTimeslots){
			$result2 = $this->timeslotHelper->hasTimeslots($date, $bookedAvailabilities);
			if(!$result2){
				return true;
			}
		}
		
		$currentTime = $this->timeslotHelper->timeByZone($this->timeslotHelper->clientTime);
		$currentDate = $supportsTimeslots ? 
			strtotime(date(CALENDARISTA_FULL_DATEFORMAT, strtotime("$date $currentTime"))) : 
			strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($date)));
		
		//if we are here, we're testing changeover or dates with timeslots
		if($bookedAvailabilities){
			foreach($bookedAvailabilities as $booked){
				$bookedStartDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($booked['fromDate'])));
				$bookedEndDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($booked['toDate'])));
				if($currentDate > $bookedStartDate && $currentDate < $bookedEndDate){
					$result = $seats === false;
					if($supportsTimeslots){
						$result = !$this->timeslotHelper->hasTimeslots($date, $bookedAvailabilities);
					}
					break;
				}
				$currentFromDate = $bookedStartDate;
				while ($currentFromDate <= $bookedEndDate) {
					if($currentDate == $currentFromDate){
						$valid = Calendarista_RepeatHelper::hasAvailability(date(CALENDARISTA_DATEFORMAT, $currentFromDate), $this->availability);
						if($valid){
							if($bookedStartDate === $currentFromDate){
								//halfday start
								//this is the startdate(which always contains the time as well)
								if(!$supportsTimeslots){
									++$flag1;//changeover mode
								}else{
									$result = !$this->timeslotHelper->hasTimeslots($booked['fromDate'], $bookedAvailabilities);
									break 2;
								}
							}else if($bookedEndDate === $currentFromDate){
								//halfday end
								//this is the enddate(which always contains the time as well)
								if(!$supportsTimeslots){
									++$flag2;//changeover mode
								} else{
									$result = !$this->timeslotHelper->hasTimeslots($booked['toDate'], $bookedAvailabilities);
									break 2;
								}
							}
							if($flag1 >= ($this->availability->seats * 2)){
								//means a changeover day is booked twice resulting in full day being booked.
								$result = true;
							}else if($search){
								//if using search, accomodate
								if($pos === 1/*end date*/ && ($flag2 === $this->availability->seats || $flag1 > $this->availability->seats)){
									$result = true;
								}else if($pos === 0/*start date*/ && ($flag1 === $this->availability->seats || $flag2 > $this->availability->seats)){
									$result = true;
								}
							}
							if($result){
								break 2;
							}
						}
					}	
					$currentFromDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime('+1 day', $currentFromDate)));
				}
			}
		}
		return $result;
	}
	protected function getBookedDatesForEntireMonth($startDate, $result = array()){
		$minimumNotice = new Calendarista_DateTime();
		$minimumNotice->setTime(0,0);
		$minimumNotice->modify('+' . $this->availability->minimumNotice . ' days');

		$maximumNotice = new Calendarista_DateTime();
		$maximumNotice->setTime(0,0);
		$maximumNotice->modify('+' . $this->availability->maximumNotice . ' days');

		$fromDate = date(CALENDARISTA_DATEFORMAT, $startDate);
		$toDate = date('Y-m-t', $startDate);
		$lastDayOfMonth = strtotime($toDate);
		$bookedAvailabilities = $this->getAvailabilities($fromDate, $toDate);
		if(!$result){
			$result = array();
		}
		while ($startDate <= $lastDayOfMonth) {
			$currentDate = date(CALENDARISTA_DATEFORMAT, $startDate);
			if(!in_array($currentDate, $result)){
				if(($this->availability->minimumNotice && 
					$startDate < $minimumNotice->getTimestamp()) || 
					($this->availability->maximumNotice && 
					$startDate > $maximumNotice->getTimestamp())){
					array_push($result, $currentDate);
				}else if($this->dateNotAvailable($currentDate, $bookedAvailabilities)){
					array_push($result, $currentDate);
				}
			}
			$startDate = strtotime('+1 day', $startDate);
		}
		return $result;
	}
	protected function dateNotAvailable($currentDate, $bookedAvailabilities){
		if($this->hasTurnover($currentDate, $bookedAvailabilities)){
			return true;
		}
		return $this->dateIsBooked($currentDate, $bookedAvailabilities);
	}
	protected function hasTurnover($currentDate, $bookedAvailabilities){
		$currentDateTime = strtotime($currentDate);
		if($this->availability->turnoverAfter){
			$turnoverAfter = strtotime(sprintf('%s -%d day', $currentDate, $this->availability->turnoverAfter));
			while($turnoverAfter <= $currentDateTime){
				if($this->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $turnoverAfter), $bookedAvailabilities)){
					return true;
				}
				$turnoverAfter = strtotime('+1 day', $turnoverAfter);
			}
		}
		if($this->availability->turnoverBefore){
			$turnoverBefore = strtotime(sprintf('%s +%d day', $currentDate, $this->availability->turnoverBefore));
			while($turnoverBefore >= $currentDateTime){
				if($this->dateIsBooked(date(CALENDARISTA_DATEFORMAT, $turnoverBefore), $bookedAvailabilities)){
					return true;
				}
				$turnoverBefore = strtotime('-1 day', $turnoverBefore);
			}
		}
		return false;
	}
	public function getAllExcludedDates($startDate){
		$fromDate = date(CALENDARISTA_DATEFORMAT, $startDate);
		$toDate = date('Y-m-t', $startDate);
		$bookedAvailabilities = $this->getAvailabilities($fromDate, $toDate);
		$currentMonthExclusions = Calendarista_RepeatHelper::getCurrentMonthExclusions($startDate, $this->availability);
		$bookedOutDays = $this->getBookedDatesForEntireMonth($startDate, array());
		$halfDays = $this->getHalfDays($startDate, $bookedAvailabilities);
		$holidays = Calendarista_RepeatHelper::getHolidays(strtotime($fromDate), strtotime($toDate), $this->availability->id);
		$changeoverDays = array_merge($halfDays['start'], $halfDays['end']);
		
		if(count($changeoverDays) > 0){
			//filter out changeover days from bookedOutDays
			foreach($bookedOutDays as $key1=>$value1) {
				foreach($changeoverDays as $key2=>$value2){
					if($value1 == $value2){
						unset($bookedOutDays[$key1]);
					} 
				}
			}
		}
		$result = array(
			'exclusions'=>$currentMonthExclusions
			, 'bookedOutDays'=>array_values($bookedOutDays)
			, 'halfDays'=>$halfDays
			, 'checkinWeekdayList'=>$this->availability->checkinWeekdayList
			, 'checkoutWeekdayList'=>$this->availability->checkoutWeekdayList
			, 'bookedAvailabilityList'=>$this->getBookedAvailabilityList($bookedAvailabilities)
			, 'holidays'=>$holidays
		);
		return apply_filters('calendarista_get_all_excluded_dates', $result, $this->projectId, $this->availabilityId, $this->clientTime, $this);
	}
	public function getBookedAvailabilityList($bookedAvailabilities){
		$result = array();
		if(!$bookedAvailabilities){
			return $result;
		}
		if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE && $this->availability->seats > 0){
			foreach($bookedAvailabilities as $booked){
				$bookedStartDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($booked['fromDate'])));
				$bookedEndDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($booked['toDate'])));
				//a partially booked date is one that has a booking for one or more slots,
				//in this case we do not allow including this date in a range.
				//it can only be booked if the date does not fall between the customers start and end date selection. 
				$startDate = date(CALENDARISTA_DATEFORMAT, $bookedStartDate);
				$endDate = date(CALENDARISTA_DATEFORMAT, $bookedEndDate);
				if(!in_array($startDate, $result) && $this->timeslotHelper->hasOutOfStockSlots($startDate, $bookedAvailabilities)){
					array_push($result, $startDate);
				}
				if(!in_array($endDate, $result) && $this->timeslotHelper->hasOutOfStockSlots($endDate, $bookedAvailabilities)){
					array_push($result, $endDate);
				}
			}
		}
		return $result;
	}
	public function getHalfDays($startDate, $bookedAvailabilities){
		$result = array('start'=>array(), 'end'=>array());
		if($this->project->calendarMode !== Calendarista_CalendarMode::CHANGEOVER || $this->availability->seats === 0){
			return $result;
		}
		if($bookedAvailabilities){
			$lastDayOfMonth = strtotime(date('Y-m-t', $startDate));
			while ($startDate <= $lastDayOfMonth) {
				$currentDate = date(CALENDARISTA_DATEFORMAT, $startDate);
				foreach($bookedAvailabilities as $booked){
					$bookedStartDate = date(CALENDARISTA_DATEFORMAT, strtotime($booked['fromDate']));
					$bookedEndDate = date(CALENDARISTA_DATEFORMAT, strtotime($booked['toDate']));
					$fromDate = strtotime($bookedStartDate);
					$toDate = strtotime($bookedEndDate);
					while ($fromDate <= $toDate) {
						if($fromDate > $lastDayOfMonth){
							break;
						}
						$halfDayStart = date(CALENDARISTA_DATEFORMAT, $fromDate);
						$halfDayEnd = date(CALENDARISTA_DATEFORMAT, $toDate);
						if($currentDate == $halfDayStart || $currentDate == $halfDayEnd){
							$changeover = self::changeover($currentDate, $lastDayOfMonth, $bookedAvailabilities, $this->availability);
							if($changeover['start'] || $changeover['end']){
								if(($changeover['start'] && !$changeover['start_open']) && 
								($bookedStartDate == $currentDate && strtotime($bookedStartDate) == $fromDate)){
									if(!in_array($currentDate, $result['start'])){
										array_push($result['start'], $currentDate);
									}
									break;
								}else if(($changeover['start'] && !$changeover['start_open']) &&
									($bookedEndDate == $currentDate && strtotime($bookedEndDate) == $fromDate)){
									if(!in_array($currentDate, $result['end'])){
										array_push($result['end'], $currentDate);
									}
									break;
								}
							}
						}
						$fromDate = strtotime('+1 day', $fromDate);
					}
				}
				$startDate = strtotime('+1 day', $startDate);
			}
		}
		return $result;
	}
	protected static function changeover($date, $lastDayOfMonth, $bookedAvailabilities, $availability){
		$result = array('start'=>false, 'start_open'=>false,  'end'=>false, 'end_open'=>false);
		$flag1 = 0;
		$flag2 = 0;
		if(!$availability->seats){
			return $result;
		}
		if($bookedAvailabilities){
			foreach($bookedAvailabilities as $booked){
				$bookedStartDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($booked['fromDate'])));
				$bookedEndDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($booked['toDate'])));
				$currentDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime($date)));
				$currentFromDate = $bookedStartDate;
				while ($currentFromDate <= $bookedEndDate) {
					if($currentFromDate > $lastDayOfMonth){
						break;
					}
					if($currentDate == $currentFromDate){
						if($bookedStartDate === $currentFromDate){
							//a changeover day
							++$flag1;
						} 
						if($bookedEndDate === $currentFromDate){
							//a changeover day
							++$flag2;
						}
					}	
					$currentFromDate = strtotime(date(CALENDARISTA_DATEFORMAT, strtotime('+1 day', $currentFromDate)));
				}
			}
		}
		if(!$flag1 === 0 && $flag2 === 0){
			return $result;
		}
		if($flag1){
			$result['start'] = true;
			$result['start_open'] = $flag1 < $availability->seats;
		}
		if($flag2){
			$result['end'] = true;
			$result['end_open'] = $flag2 < $availability->seats;
		}
		return $result;
	}
	public function hasPackage($startDate, $endDate, $daysInPackage, $appointment){
		if($this->availability->daysInPackage > 1 && !$this->availability->hasRepeat){
			return false;
		}
		if($this->availability->daysInPackage !== $daysInPackage){
			return false;
		}
		$availableDate = strtotime($this->availability->availableDate->format(CALENDARISTA_DATEFORMAT));
		if($startDate < $availableDate){
			return false;
		}
		$holidays = Calendarista_RepeatHelper::getHolidays($startDate, $endDate, $this->availability->id);
		$bookedAvailabilities = $this->getAvailabilities(date(CALENDARISTA_DATEFORMAT, $startDate), date(CALENDARISTA_DATEFORMAT, $endDate));
		$valid = true;
		while ($startDate <= $endDate) {
			if($this->packageHasTerminated($startDate)){
				$valid = false;
				break;
			}
			$currentDate = date(CALENDARISTA_DATEFORMAT, $startDate);
			if(in_array($currentDate, $holidays) || ($appointment !== 1 && $this->dateIsBooked($currentDate, $bookedAvailabilities))){
				$valid = false;
				break;
			}
			$startDate = strtotime('+1 day', $startDate);
		}
		return $valid;
	}
	public function packageHasTerminated($startDate){
		$terminateDate = null;
		if($this->availability->terminateMode === 0/*NEVER*/){
			//always set a termination date or we terminate forcefully after 2 years.
			$terminateDate = strtotime('+2 years');
		}
		if(Calendarista_RepeatHelper::hasTerminated($this->availability, $startDate)){
			return true;
		}
		if(!Calendarista_RepeatHelper::hasAvailability(date(CALENDARISTA_DATEFORMAT, $startDate), $this->availability)){
			if($terminateDate && $startDate >= $terminateDate){
				//force termination
				return true;
			}
		}
		return false;
	}
	public function getNextOccurrenceByPackage($startDate = null){
		if($this->availability->daysInPackage > 1 && !$this->availability->hasRepeat){
			return false;
		}
		$length = $this->availability->daysInPackage - 1;
		if(!$startDate){
			$startDate = strtotime('now');
		}
		$availableDate = strtotime($this->availability->availableDate->format(CALENDARISTA_DATEFORMAT));
		if($startDate < $availableDate){
			$startDate = $availableDate;
		}
		$startDate = $this->getNextRepeat($startDate);
		$result = false;
		while($startDate !== false){
			$endDate = strtotime('+' . $length . ' day', $startDate);
			$holidays = Calendarista_RepeatHelper::getHolidays($startDate, $endDate, $this->availability->id);
			$bookedAvailabilities = $this->getAvailabilities(date(CALENDARISTA_DATEFORMAT, $startDate), date(CALENDARISTA_DATEFORMAT, $endDate));
			$valid = true;
			$result = array('startDate'=>strtotime(date(CALENDARISTA_DATEFORMAT, $startDate)), 'endDate'=>strtotime(date(CALENDARISTA_DATEFORMAT, $endDate)));
			while ($startDate <= $endDate) {
				$currentDate = date(CALENDARISTA_DATEFORMAT, $startDate);
				if(in_array($currentDate, $holidays) || $this->dateIsBooked($currentDate, $bookedAvailabilities)){
					$valid = false;
					break;
				}
				$startDate = strtotime('+1 day', $startDate);
			}
			if($valid){
				break;
			}
			if($length === 0){
				$startDate = $this->getNextRepeat(strtotime('+1 day', $startDate));
			}else{
				$startDate = $this->getNextRepeat($endDate);
			}
		}
		return $startDate ? $result : false;
	}
	protected function getNextRepeat($startDate){
		$terminateDate = null;
		if($this->availability->terminateMode === 0/*NEVER*/){
			//always set a termination date or we terminate forcefully after 2 years.
			$terminateDate = strtotime('+2 years');
		}
		while(true){
			if(Calendarista_RepeatHelper::hasTerminated($this->availability, $startDate)){
				return false;
			}
			if(!Calendarista_RepeatHelper::hasAvailability(date(CALENDARISTA_DATEFORMAT, $startDate), $this->availability)){
				if($terminateDate && $startDate >= $terminateDate){
					//force termination
					return false;
				}
				$startDate = strtotime('+1 day', $startDate);
			}else{
				return $startDate;
			}
		}
	}
	protected function parseArgs($key, $args, $convertToTime = false, $default = null){
		$result = isset($args[$key]) ? $args[$key] : $default;
		if($result !== null && $convertToTime){
			return strtotime($result);
		}
		return $result;
	}
	public static function getWooCartItems(){
		$result = isset($_POST['calendarista_cart']) ? explode(',', sanitize_text_field($_POST['calendarista_cart'])) : array();
		if(!Calendarista_AjaxHelper::doingAjax() &&  class_exists('WooCommerce')){
			$result = array();
			if(WC() && isset(WC()->cart)){
				$cart = WC()->cart->cart_contents;
				foreach($cart as $item){
					if($item['data']->get_type() === 'calendarista'){
						array_push($result, $item['_calendarista_staging_id']);
					}
				}
			}
		}
		return $result;
	}
	public static function getAvailabilityFromCart($project, $bookedAvailabilities, $availabilityList){
		if(!is_array($bookedAvailabilities)){
			$bookedAvailabilities = array();
		}
		if($project->paymentsMode === 3/*woocommerce*/ 
						&& Calendarista_WooCommerceHelper::wooCommerceActive()){
			$cartItems = self::getWooCartItems();
			$stagingRepo = new Calendarista_StagingRepository();
			$availabilityRepo = new Calendarista_AvailabilityRepository();
			foreach($cartItems as $stagingId){
				$result = $stagingRepo->read($stagingId);
				if($result){
					$stateBag = unserialize(stripslashes(html_entity_decode($result->viewState, ENT_QUOTES, "UTF-8")));
					$availability = $availabilityRepo->read($stateBag[1]['availabilityId']);
					if(!$availability){
						continue;
					}
					if(!in_array($availability->id, $availabilityList)){
							continue;
					}
					$timeslotRepo = new Calendarista_TimeslotRepository();
					$startTime = new Calendarista_Timeslot(array());
					$endTime = new Calendarista_Timeslot(array());
					$startTimeId = null;
					$endTimeId = null;
					if($stateBag[1]['startTime']){
						$startTimeId = (int)$stateBag[1]['startTime'];
						$startTime = $timeslotRepo->read($startTimeId);
						$endTime->timeslot = $startTime->timeslot;
					}
					if($stateBag[1]['endTime']){
						$endTimeId = (int)$stateBag[1]['endTime'];
						$endTime = $timeslotRepo->read($endTimeId);
					}
					$seats = $stateBag[1]['seats'];
					if(!$seats && (($startTime->seats > 0 || $availability->seats > 0) && !$availability->selectableSeats)){
						$seats = 1;
					}
					array_push($bookedAvailabilities, array(
						'availabilityId'=>$stateBag[1]['availabilityId']
						, 'projectId'=>$stateBag[0]['projectId']
						, 'fromDate'=>date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
											, $stateBag[1]['availableDate']
											, $startTime->timeslot))))
						, 'toDate'=>$stateBag[1]['endDate'] ? date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
									, $stateBag[1]['endDate']
									, $endTime->timeslot)))) : null
						, 'startTimeId'=>$startTimeId
						, 'endTimeId'=>$endTimeId
						, 'seats'=>$seats
						, 'calendarMode'=>$project->calendarMode
					));
				}
			}
		}
		return $bookedAvailabilities;
	}
	public static function getOptionalQuantityFromCart($project, $fromDate, $endDate){
		if($project->paymentsMode === 3/*woocommerce*/ 
						&& Calendarista_WooCommerceHelper::wooCommerceActive()){
			$cartItems = self::getWooCartItems();
			$stagingRepo = new Calendarista_StagingRepository();
			$availabilityRepo = new Calendarista_AvailabilityRepository();
			foreach($cartItems as $stagingId){
				$result = $stagingRepo->read($stagingId);
				if($result){
					$stateBag = unserialize(stripslashes(html_entity_decode($result->viewState, ENT_QUOTES, "UTF-8")));
					$projectId = isset($stateBag[0]) && isset($stateBag[0]['projectId']) ? $stateBag[0]['projectId'] : null;
					if(!$projectId || $projectId !== $project->id){
						continue;
					}
					if(!isset($stateBag[3]) || !isset($stateBag[3]['optional_incremental'])){
						//toDO: extend to normal optionals as well
						continue;
					}
					$timeslotRepo = new Calendarista_TimeslotRepository();
					$startTime = new Calendarista_Timeslot(array());
					$endTime = new Calendarista_Timeslot(array());
					$startTimeId = null;
					$endTimeId = null;
					if($stateBag[1]['startTime']){
						$startTimeId = (int)$stateBag[1]['startTime'];
						$startTime = $timeslotRepo->read($startTimeId);
						$endTime->timeslot = $startTime->timeslot;
					}
					if($stateBag[1]['endTime']){
						$endTimeId = (int)$stateBag[1]['endTime'];
						$endTime = $timeslotRepo->read($endTimeId);
					}
					$_fromDate = $stateBag[1]['availableDate'];
					if($startTime->timeslot){
						$stateBag[1]['availableDate'] = date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
												, $stateBag[1]['availableDate']
												, $startTime->timeslot))));
					}
					$_endDate = $stateBag[1]['endDate'];
					if($endTime->timeslot){
						$_endDate = date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
									, $stateBag[1]['endDate']
									, $endTime->timeslot))));
					}
					if($fromDate === $_fromDate && $endDate === $_endDate){
						return $stateBag[3]['optional_incremental'];
					}
				}
			}
		}
		return false;
	}
	protected function getViewStateValue($viewState, $value){
		if(isset($viewState[$value])){
			return $viewState[$value];
		}
		return null;
	}
	public function getCurrentMonthAvailabilities($startDate){
		$fromDate = date(CALENDARISTA_DATEFORMAT, strtotime($startDate));
		$toDate = date('Y-m-t', strtotime($startDate));
		return $this->getAvailabilities($fromDate, $toDate);
	}
}
?>