<?php
class Calendarista_AutogenTimeslotsController extends Calendarista_BaseController{
	public function __construct($createCallback, $updateCallback, $deleteCallback, $startTimeChangedCallback, $searchTimeslotsCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_autogen_timeslots')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		parent::__construct($createCallback,  $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_starttime_changed', $_POST)){
			$this->startTimeChanged($startTimeChangedCallback);
		}
		if (array_key_exists('calendarista_create_for_search', $_POST)){
			$this->createSearchTimeslotsCallback($searchTimeslotsCallback);
		}
	}
	public function createSearchTimeslotsCallback($callback){
		$startInterval = (string)$this->getPostValue('startInterval');
		$timeSplit = (string)$this->getPostValue('timeSplit');
		$endTime = (string)$this->getPostValue('endTime');
		$si = self::parseTime($startInterval);
		$ts = self::parseTime($timeSplit);
		$ed = self::parseTime($endTime);
		$searchTimeslots = self::createTimeSlots($ts[0], $ts[1], $si[0], $si[1], $ed[0], $ed[1], true/*enableSingleHourMinuteFormat*/);
		$repo = new Calendarista_GeneralSettingsRepository();
		$gs = $repo->read();
		if(count($searchTimeslots) > 0){
			$gs->searchTimeslots = $searchTimeslots;
		}else{
			$gs->searchTimeslots = $gs->getDefaultTimeslots();
		}
		$result = false;
		if($gs->id === -1){
			$result = $repo->insert($gs);
		}else{
			$result = $repo->update($gs);
		}
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$deals = isset($_POST['deal']) ? $_POST['deal'] : null;
		$availabilityId = (int)$_POST['calendarista_update'];
		$repo = new Calendarista_TimeslotRepository();
		$result = $repo->updateDeals($availabilityId, $deals);
		$this->executeCallback($callback, array($result));
	}
	public function getAvailabilityList($projectId){
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availabilityList = $availabilityRepo->readAllByService(array($projectId));
		$result = null;
		foreach($availabilityList as $availability){
			if($result === null){
				$result = array();
			}
			array_push($result, $availability->id);
		}
		return $result;
	}
	public function create($callback){
		//value of 1 = update, 0 = create
		$update = isset($_POST['calendarista_create']) ? (int)$_POST['calendarista_create'] : false;
		$applyToService = isset($_POST['applyToService']) ? true : false;
		$updateSeats = isset($_POST['updateSeats']) ? true : false;
		$updateCost = isset($_POST['updateCost']) ? true : false;
		$projectId = $this->getIntValue('projectId');
		$_availabilityId = $this->getIntValue('availabilityId');
		$availabilityList = array($_availabilityId);
		if($applyToService){
			$availabilityList = $this->getAvailabilityList($projectId);
		}
		$startInterval = (string)$this->getPostValue('startInterval');
		$timeSplit = (string)$this->getPostValue('timeSplit');
		$endTime = (string)$this->getPostValue('endTime');
		$weekday = $this->getIntValue('weekday');
		$day = (string)$this->getPostValue('day');
		$seats = $this->getIntValue('seats');
		$seatsMaximum = $this->getIntValue('seatsMaximum');
		$seatsMinimum = $this->getIntValue('seatsMinimum');
		$cost = (float)$this->getPostValue('cost');
		$paddingTimeBefore = $this->getIntValue('paddingTimeBefore');
		$paddingTimeAfter = $this->getIntValue('paddingTimeAfter');
		$returnTrip = $this->getIntValue('returnTrip');
		$si = self::parseTime($startInterval);
		$ts = self::parseTime($timeSplit);
		$ed = self::parseTime($endTime);
		$enableSingleHourMinuteFormat = true;
		$project = Calendarista_ProjectHelper::getProject($projectId);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		if(in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIME_RANGE)){
			$enableSingleHourMinuteFormat = false;
		}
		$timeslotList = self::createTimeSlots($ts[0], $ts[1], $si[0], $si[1], $ed[0], $ed[1], $enableSingleHourMinuteFormat);
		$weekdays = $weekday === 0 ? array(1, 2, 3, 4, 5, 6, 7) : array($weekday);
		foreach($availabilityList as $availabilityId){
			$timeslots = new Calendarista_Timeslots();
			foreach($weekdays as $wday){
				foreach($timeslotList as $r){
					$timeslot = new Calendarista_Timeslot(array(
						'availabilityId'=>$availabilityId
						, 'projectId'=>$projectId
						, 'timeslot'=>$r['value']
						, 'cost'=>$cost
						, 'seats'=>$seats
						, 'seatsMaximum'=>$seatsMaximum
						, 'seatsMinimum'=>$seatsMinimum
						, 'paddingTimeBefore'=>$paddingTimeBefore
						, 'paddingTimeAfter'=>$paddingTimeAfter
						, 'returnTrip'=>$returnTrip
					));
					if($wday !== -1){
						$timeslot->weekday = $wday;
					}else{
						$timeslot->day = new Calendarista_DateTime($day);
					}
					$timeslots->add($timeslot);
				}
			}
			$result = null;
			if(!$update){
				//delete old slots
				if($weekday === -1){
					$timeslotRepo->deleteByDate($availabilityId, $day, $returnTrip);
				}elseif($weekday === 0){
					$timeslotRepo->deleteWeekdaysByAvailability($availabilityId, $returnTrip);
				} else{
					$timeslotRepo->deleteByWeekday($availabilityId, $weekday, $returnTrip);
				}
				foreach($timeslots as $timeslot){
					$result = $timeslotRepo->insert($timeslot);
					if(!$result){
						break;
					}
				}
			}else{
				if($weekday === -1){
					$timeslots2 = $timeslotRepo->readSingleDayByAvailability($day, $availabilityId, $returnTrip);
				}else{
					$timeslots2 = $timeslotRepo->readAllWeekdaysByAvailability($availabilityId, $returnTrip);
				}
				foreach($timeslots as $timeslot1){
					foreach($timeslots2 as $timeslot2){
						if(($timeslot1->weekday == $timeslot2->weekday && $timeslot1->day == $timeslot2->day) && $timeslot1->timeslot == $timeslot2->timeslot){
							$timeslot1->id = $timeslot2->id;
							$result = $timeslotRepo->update($timeslot2);
							if(!$result){
								break 2;
							}
						}
					}
				}
			}
		}
		$this->executeCallback($callback, array($result));
	}
	public static function createDefaultSlots($availabilityId, $projectId){
		$hours = 1;
		$minutes = 0;
		$hourStartInterval = 0;
		$minuteStartInterval = 0;
		$hourEnd = 23;
		$minuteEnd = 59;
		$result = self::createTimeSlots($hours, $minutes, $hourStartInterval, $minuteStartInterval, $hourEnd, $minuteEnd, true);
		$timeslots = new Calendarista_Timeslots();
		$weekdays = array(1, 2, 3, 4, 5, 6, 7);
		foreach($weekdays as $wday){
			foreach($result as $r){
				$timeslot = new Calendarista_Timeslot(array(
					'availabilityId'=>$availabilityId
					, 'projectId'=>$projectId
					, 'timeslot'=>$r['value']
				));
				$timeslot->weekday = $wday;
				$timeslots->add($timeslot);
			}
		}
		$result = null;
		$repo = new Calendarista_TimeslotRepository();
		foreach($timeslots as $timeslot){
			$result = $repo->insert($timeslot);
			if(!$result){
				break;
			}
		}
		return $result;
	}
	public function delete($callback){
		$timeslots = (array)$this->getPostValue('timeslots');
		$availabilityId = $this->getIntValue('availabilityId');
		$repo = new Calendarista_TimeslotRepository();
		$result = false;
		foreach($timeslots as $id){
			$result = $repo->delete((int)$id);
			if(!$result){
				break;
			}
		}
		$this->executeCallback($callback, array($result));
	}
	public function startTimeChanged($callback){
		$idList = array();
		$id = $this->getPostValue('starttime_by_date');
		if($id){
			array_push($idList, (int)$id);
		}else{
			$weekdays = array(1, 2, 3, 4, 5, 6, 7);
			foreach($weekdays as $weekday){
				$id = $this->getPostValue(sprintf('starttime_%d', $weekday));
				if($id){
					array_push($idList, (int)$id);
				}
			}
		}
		$availabilityId = $this->getIntValue('calendarista_starttime_changed');
		$repo = new Calendarista_TimeslotRepository();
		$repo->resetStartTimeByAvailability($availabilityId);
		$result = false;
		foreach($idList as $timeslotId){
			$timeslot = $repo->read($timeslotId);
			if(!$timeslot){
				continue;
			}
			$timeslot->startTime = true;
			$result = $repo->update($timeslot);
			if(!$result){
				break;
			}
		}
		$this->executeCallback($callback, array($result));
	}
	public static function createTimeSlots($hours, $minutes, $hourStartInterval, $minuteStartInterval, $lastSlotHour, $lastSlotMinute, $enableSingleHourMinuteFormat, $timeFormat = 'g:i a'){
		$slots = array();
		if(!isset($hours) || !isset($minutes) || ($hours === 0 && $minutes === 0)){
			return $slots;
		}
		
		$startTime = strtotime(sprintf('%02d:%02d:00', $hourStartInterval, $minuteStartInterval));
		$tomorrow = new Calendarista_DateTime('tomorrow');
		$tomorrow->setTime(0,0,0);
		$endTime = $tomorrow->format('U');
		
		$lastSlot = new Calendarista_DateTime();
		$lastSlot->setTime($lastSlotHour, $lastSlotMinute);
		$lastSlot = $lastSlot->format('U');
		while ($startTime < $endTime)
		{
			$timeStartString = date ('H:i:s', $startTime);
			$timeStartSegments = explode(':', $timeStartString);
			$hourStart = (int)$timeStartSegments[0];
			$minuteStart = (int)$timeStartSegments[1];
			
			$interval = strtotime('+' . $hours . ' hours ' . $minutes . ' minutes', $startTime);
			$timeEndString = date ('H:i:s', $interval);
			$timeEndSegments = explode(':', $timeEndString);
			$hourEnd = (int)$timeEndSegments[0];
			$minuteEnd = (int)$timeEndSegments[1];
						
			if($minuteStart === 59){
				$minuteEnd = ($minuteStart + $minutes) % 60;
			}
			
			$start = new Calendarista_DateTime();
			$start->setTime($hourStart, $minuteStart);
			
			$end = new Calendarista_DateTime();
			$end->setTime($hourEnd, $minuteEnd);
			
			
			$text = $start->format($timeFormat);
			if(!$enableSingleHourMinuteFormat){
				$text .= ' - ' . $end->format($timeFormat);
			}
			//support am/pm from global settings.
			array_push($slots, array(
				'value'=>$start->format($timeFormat)
				, 'text'=>$text
			));
			
			if($interval > $lastSlot){
				break;
			}
			
			$startTime = $interval;
		}
		
		return $slots;
	}
	public static function parseTime($time){
		if(!$time){
			return array(0, 0);
		}
		$result = explode(':', $time);
		return array((int)$result[0],  (int)$result[1]);
	}
}
?>