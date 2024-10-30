<?php
class Calendarista_TimeslotController extends Calendarista_BaseController{
	private $repo;
	private $timeslot;
	public function __construct($timeslot, $createCallback = null, $updateCallback = null, $deleteCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_timeslot')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->timeslot = $timeslot;
		$this->repo = new Calendarista_TimeslotRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
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
		$result = false;
		$applyToService = isset($_POST['applyToService']) ? true : false;
		$weekday = $this->timeslot->weekday === -1 ? (int)date('N', strtotime($this->timeslot->day->format(CALENDARISTA_DATEFORMAT))) : $this->timeslot->weekday;
		$weekdayTimeslots = null;
		$availabilityList = array($this->timeslot->availabilityId);
		$selectedAvailability = $this->timeslot->availabilityId;
		if($applyToService){
			$availabilityList = $this->getAvailabilityList($this->timeslot->projectId);
		}
		$returnValue = null;
		foreach($availabilityList as $availabilityId){
			$duplicate = false;
			$this->timeslot->availabilityId = $availabilityId;
			if($this->timeslot->weekday === -1){
				$timeslots = $this->repo->readSingleDayByAvailability($this->timeslot->day->format(CALENDARISTA_DATEFORMAT), $availabilityId, $this->timeslot->returnTrip);
			}else{
				$timeslots = $this->repo->readAllByWeekday($this->timeslot->weekday, $availabilityId, $this->timeslot->returnTrip);
			}
			if($this->timeslot->timeslot){
				foreach($timeslots as $timeslot){
					if($this->timeslot->timeslot === $timeslot->timeslot && $this->timeslot->returnTrip == $timeslot->returnTrip){
						$duplicate = true;
						break;
					}
				}
			}
			if(!$duplicate){
				if(!$this->timeslot->timeslot){
					if(!$weekdayTimeslots){
						$weekdayTimeslots = $this->repo->readAllByWeekday($weekday, $selectedAvailability, $this->timeslot->returnTrip);
					}
					if($weekdayTimeslots){
						foreach($weekdayTimeslots as $timeslot1){
							$this->timeslot->timeslot = $timeslot1->timeslot;
							$exists = false;
							//check existing slots
							if($timeslots){
								foreach($timeslots as $timeslot2){
									if($timeslot1->timeslot === $timeslot2->timeslot){
										$exists = true;
										break;
									}
								}
							}
							if(!$exists){
								$result = $this->repo->insert($this->timeslot);
							}
						}
						//reset for next round of availability
						$this->timeslot->timeslot = '';
					}
				}else{
					$result = $this->repo->insert($this->timeslot);
				}
				if($result !== false && $this->timeslot->availabilityId === $selectedAvailability){
					$returnValue = $result;
				}
			}
		}
		$this->executeCallback($callback, array($duplicate, $returnValue));
	}
	public function update($callback){
		$result = false;
		$duplicate = false;
		$applyToService = isset($_POST['applyToService']) ? true : false;
		$availabilityList = array($this->timeslot->availabilityId);
		$selectedAvailability = $this->timeslot->availabilityId;
		if($this->timeslot->id > 0){
			$result = $this->repo->update($this->timeslot);
		}else{
			if($applyToService){
				$availabilityList = $this->getAvailabilityList($this->timeslot->projectId);
			}
			$returnValue = null;
			foreach($availabilityList as $availabilityId){
				$this->timeslot->availabilityId = $availabilityId;
				if(!$this->timeslot->weekday || $this->timeslot->weekday === -1){
					$timeslots = $this->repo->readSingleDayByAvailability($this->timeslot->day->format(CALENDARISTA_DATEFORMAT), $availabilityId, $this->timeslot->returnTrip);
				}else{
					$timeslots = $this->repo->readAllByWeekday($this->timeslot->weekday, $availabilityId, $this->timeslot->returnTrip);
				}
				if(!$this->timeslot->timeslot){
					//check existing slots
					if($timeslots){
						foreach($timeslots as $timeslot){
							$this->timeslot->id = $timeslot->id;
							$this->timeslot->timeslot = $timeslot->timeslot;
							$result = $this->repo->update($this->timeslot);
						}
						//reset for next round of availability
						$this->timeslot->timeslot = '';
					}
				}else{
					if($timeslots){
						foreach($timeslots as $timeslot){
							if($this->timeslot->timeslot === $timeslot->timeslot){
								$this->timeslot->id = $timeslot->id;
								$result = $this->repo->update($this->timeslot);
								break;
							}
						}
					}
				}
			}
		}
		$this->executeCallback($callback, array($duplicate, $result));
	}
	public function delete($callback){
		$result = $this->repo->delete($this->timeslot->id);
		$this->executeCallback($callback, array($result));
	}
}
?>