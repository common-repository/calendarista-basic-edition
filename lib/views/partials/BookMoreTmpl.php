<?php
class Calendarista_BookMoreTmpl extends Calendarista_TemplateBase{
	public $appointment;
	public $selectedAvailabilities;
	public function __construct(){
		parent::__construct();
		$projectId = (int)$this->getPostValue('projectId');
		$startDate = $this->getPostValue('startDate');
		$endDate = $this->getPostValue('endDate');
		$startTime = $this->getPostValue('startTime') ? (int)$this->getPostValue('startTime') : null;
		$endTime = $this->getPostValue('endTime') ? (int)$this->getPostValue('endTime') : null;
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$seats = (int)$this->getPostValue('seats');
		$this->selectedAvailabilities = $this->getViewStateValue('availabilities') ? explode(',', $this->getViewStateValue('availabilities')) : array();
		$this->appointment = (int)$this->getPostValue('appointment', -1);
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availabilities = $availabilityRepo->readAll($projectId);
		$currentAvailability = $availabilityRepo->read($availabilityId);
		$this->availabilities = array();
		foreach($availabilities as $availability){
			if($availability->id === $availabilityId){
				continue;
			}
			$flag = false;
			$availabilityHelper = new Calendarista_AvailabilityHelper(array(
				'projectId'=>$projectId
				, 'availabilityId'=>$availability->id
			));
			if(in_array($availabilityHelper->project->calendarMode, array(Calendarista_CalendarMode::PACKAGE))){
				$flag = $availabilityHelper->hasPackage(strtotime($startDate), strtotime($endDate), $currentAvailability->daysInPackage, $this->appointment);
			}else{
				$result = $this->getSeats($availabilityHelper, $startDate, $startTime, $endDate, $endTime);
				if($result && ($result >= $availability->seatsMinimum && ($seats && $result >= $seats))){
					//we do have seats available
					$flag = true;
				}
			}
			if($flag){
				array_push($this->availabilities, $availability);
			}
		}
		if(count($this->availabilities) > 0){
			$this->render();
		}
	}
	public function getSeats($availabilityHelper, $startDate, $startTime, $endDate, $endTime){
		if($availabilityHelper->availability->seats){
			//day based seats
			if($this->appointment === 1/*edit mode*/){
				return $availabilityHelper->availability->seats;
			}
			$result1 = $availabilityHelper->availability->seats - $availabilityHelper->getTotalUsedSeats($availabilityHelper->availability, null, $startDate);
			if($endDate && $startDate != $endDate){
				$result1 = $availabilityHelper->availability->seats - $availabilityHelper->getTotalUsedSeats($availabilityHelper->availability, null, $endDate);
			}
			return $result1;
		}
		if(in_array($availabilityHelper->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)){
			//timeslot based seats
			$timeslot1 = Calendarista_CheckoutHelper::getTimeslot($startDate, $startTime, $availabilityHelper->availability->id);
			$repo = new Calendarista_TimeslotRepository();
			if(!$timeslot1){
				return 0;
			}
			$this->seatsMaximum = $timeslot1->seatsMaximum;
			$this->seatsMinimum = $timeslot1->seatsMinimum;
			if($this->appointment === 1/*edit mode*/){
				return $timeslot1->seats;
			}
			$flag1 = false;
			$bookedAvailabilities = $availabilityHelper->getCurrentMonthAvailabilities($startDate);
			if($startTime && $endTime){
				$timeslots = $availabilityHelper->timeslotHelper->getTimeslots($startDate, $bookedAvailabilities, 0, $this->appointment);
				if($timeslots){
					foreach($timeslots as $ts){
						//first available start time slot in time range is bookable always
						if(!$ts->outOfStock && $ts->timeslot === $timeslot1->timeslot){
							$flag1 = true;
							break;
						}
					}
				}
			}
			$selectedDateTime = strtotime("$startDate $timeslot1->timeslot");
			$usedSeats = $availabilityHelper->timeslotHelper->getUsedSeats($selectedDateTime, $timeslot1->timeslot, $bookedAvailabilities);
			$result2 = $timeslot1->seats ? ($timeslot1->seats - $usedSeats) : $timeslot1->seats;
			$timeslot2 = Calendarista_CheckoutHelper::getTimeslot($endDate, $endTime, $availabilityHelper->availability->id);
			if(!$endTime){
				return $result2;
			}
			if($endTime && !$timeslot2){
				return 0;
			}
			if(($endDate && $startDate != $endDate) && $availabilityHelper->project->calendarMode === Calendarista_CalendarMode::ROUND_TRIP_WITH_TIME){
				if(!$result2 && !$flag1){
					return $result2;
				}
				$bookedAvailabilities = $availabilityHelper->getCurrentMonthAvailabilities($endDate);
				$selectedDateTime = strtotime("$endDate $timeslot2->timeslot");
				$usedSeats = $availabilityHelper->timeslotHelper->getUsedSeats($selectedDateTime, $timeslot2->timeslot, $bookedAvailabilities);
				$result2 = $timeslot2->seats ? ($timeslot2->seats - $usedSeats) : $timeslot2->seats;
				return $result2;
			}
			if(!$timeslot2){
				return $result2;
			}
			//we have a start and end time, so return the least number of seats
			$timeslots = $repo->readAllByStartEnd($timeslot1->id, $timeslot2->id);
			$result4 = 0;
			$flag2 = false;
			foreach($timeslots as $timeslot){
				//start time is bookable in time range
				if($timeslot1->timeslot === $timeslot->timeslot){
					continue;
				}
				$selectedDateTime = strtotime("$startDate $timeslot->timeslot");
				$usedSeats = $availabilityHelper->timeslotHelper->getUsedSeats($selectedDateTime, $timeslot->timeslot, $bookedAvailabilities);
				$result3 = $timeslot->seats ? ($timeslot->seats - $usedSeats) : $timeslot->seats;
				if(!$flag2){
					$result4 = $result3;
					$flag2 = true;
					continue;
				}
				if($result3 < $result4){
					$result4 = $result3;
				}
			}
			return $result4;
		}	
		return 1;/*service availability has indefinite seats*/
	}
	public function selectedAvailability($value){
		if(in_array($value, $this->selectedAvailabilities)){
			return 'checked';
		}
		return null;
	}
	public function render(){
	?>
	<div class="col-xl-12 calendarista-row-double">
		<div class="form-group">
			<label class="form-control-label calendarista-typography--caption1">
				<?php echo esc_html($this->stringResources['BOOK_ANOTHER_AVAILABILITY']) ?>
			</label>
			<div>
				<div class="form-check-inline">
					<?php foreach($this->availabilities as $availability): ?>
					<label class="form-check-label calendarista-typography--caption1">
						<input type="checkbox" class="form-check-input" name="availabilities[]" value="<?php echo $availability->id ?>" <?php echo  $this->selectedAvailability($availability->id); ?>>
						<?php echo esc_html($availability->name) ?>
					</label>&nbsp;&nbsp;
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
<?php
	}
}
