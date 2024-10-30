<?php
class Calendarista_BookingSeatsTmpl extends Calendarista_TemplateBase{
	public $seats = 0;
	public $availability;
	public $availabilityHelper;
	public $project;
	public $appointment;
	public $seatsMaximum;
	public $seatsMinimum;
	public $remainingSeats;
	public function __construct(){
		parent::__construct();
		$projectId = (int)$this->getPostValue('projectId');
		$startDate = $this->getPostValue('startDate');
		$endDate = $this->getPostValue('endDate');
		$startTime = $this->getPostValue('startTime') ? (int)$this->getPostValue('startTime') : null;
		$endTime = $this->getPostValue('endTime') ? (int)$this->getPostValue('endTime') : null;
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$this->appointment = (int)$this->getPostValue('appointment', -1);
		$this->availabilityHelper = new Calendarista_AvailabilityHelper(array(
			'projectId'=>$projectId
			, 'availabilityId'=>$availabilityId
		));
		$this->project = $this->availabilityHelper->project;
		$this->availability = $this->availabilityHelper->availability;
		$this->seatsMaximum = $this->availability->seatsMaximum;
		$this->seatsMinimum = $this->availability->seatsMinimum;
		$this->seats = $this->getSeats($startDate, $endDate, $startTime, $endTime);
		if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIME_RANGE) && (!$this->availability->returnOptional && !$endTime)){
			return;
		}
		if($this->seats && $this->seats >= $this->availability->seatsMinimum){
			$this->render();
		}
	}
	public function getSeats($startDate, $endDate, $startTime, $endTime){
		$result = 0;
		if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)){
			if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE){
				$result = $this->getSeatsFromTimeRange($startDate, $startTime, $endDate, $endTime);
			} else {
				//timeslot based seats
				$result = $this->getSeatsFromIndividualTimeslot($startDate, $endDate, $startTime, $endTime);
			}
		}
		if(!$result && $this->availability->seats){
			if($endDate && $startDate != $endDate){
				$this->remainingSeats = $this->getSeatsFromRange($startDate, $endDate);
				return $this->remainingSeats;
			}
			//single day based seats
			$totalSeats = $this->availabilityHelper->getTotalUsedSeats($this->availability, null, $startDate);
			if($this->appointment === 1/*edit mode*/){
				$result = $this->availability->seats;
			}else{
				$result = $this->availability->seats - $totalSeats;
			}
			$this->remainingSeats = $this->availability->seats - $totalSeats;
			if($this->remainingSeats < 0){
				$this->remainingSeats = 0;
			}
			$this->seatsMaximum = $this->availability->seatsMaximum;
			$this->seatsMinimum = $this->availability->seatsMinimum;
		}
		return $result;
	}
	public function getSeatsFromRange($startDate, $endDate){
		$start = strtotime($startDate);
		$end = strtotime($endDate);
		$this->seatsMaximum = $this->availability->seatsMaximum;
		$this->seatsMinimum = $this->availability->seatsMinimum;
		$totalSeats = $this->availability->seats;
		if($this->appointment !== 1/*edit mode*/){
			if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_RANGE){
				while($start <= $end){
					$startDateStr = date(CALENDARISTA_DATEFORMAT, $start);
					$usedSeats = $this->availabilityHelper->getTotalUsedSeats($this->availability, null, $startDateStr);
					if($usedSeats > 0 && $usedSeats < $totalSeats){
						$totalSeats = $this->availability->seats - $usedSeats;
					}
					$start = strtotime('+1 day', $start);
				}
			} else{
				$dateRange = array($startDate, $endDate);
				foreach($dateRange as $dt){
					$usedSeats = $this->availabilityHelper->getTotalUsedSeats($this->availability, null, $dt);
					if($usedSeats > 0 && $usedSeats < $totalSeats){
						$totalSeats = $this->availability->seats - $usedSeats;
					}
				}
			}
		}
		if($totalSeats < 0){
			$totalSeats = 0;
		}
		return $totalSeats;
	}
	public function getSeatsFromTimeRange($startDate, $startTime, $endDate, $endTime){
		$start = strtotime($startDate);
		$end = strtotime($endDate);
		$totalSeats = $this->availability->seats;
		$result = null;
		$repo = new Calendarista_TimeslotRepository();
		if($this->appointment !== 1/*edit mode*/){
			while($start <= $end){
				$startDateStr = date(CALENDARISTA_DATEFORMAT, $start);
				$timeslots = array();
				if($startDate == $endDate){
					$timeslots = $repo->readAllByStartEnd($startTime, $endTime);
				} else{
					$weekday = (int)date('N', $start);
					$timeslots = $repo->readAllByWeekday($weekday, $this->availability->id);
				}
				foreach($timeslots as $timeslot){
					$timeslotId = $timeslot->id;
					if($start == $end){
						$timeslotId = $endTime;
					}
					$usedSeats = $this->getSeatsFromIndividualTimeslot($startDateStr, null, $timeslotId, null);
					if($usedSeats > 0 && $result === null){
						$result = $usedSeats;
					}
					if($usedSeats < $result){
						$result = $usedSeats;
					}
					if($start == $end){
						break;
					}
				}
				$start = strtotime('+1 day', $start);
			}
		}
		if($result){
			$totalSeats = $result;
		}
		return $totalSeats;
	}
	public function getSeatsFromIndividualTimeslot($startDate, $endDate, $startTime, $endTime){
		$repo = new Calendarista_TimeslotRepository();
		$timeslot1 = $repo->read($startTime);
		$totalSeats = 0;
		if($timeslot1){
			if($this->seatsMaximum && $this->seatsMinimum){
				if($this->seatsMaximum < $timeslot1->seatsMaximum){
					$this->seatsMaximum = $timeslot1->seatsMaximum;
				}
				if($this->seatsMinimum < $timeslot1->seatsMinimum){
					$this->seatsMinimum = $timeslot1->seatsMinimum;
				}
			}else{
				$this->seatsMaximum = $timeslot1->seatsMaximum;
				$this->seatsMinimum = $timeslot1->seatsMinimum;
			}
			if($this->appointment === 1/*edit mode*/){
				$totalSeats = $timeslot1->seats;
			}else{
				$bookedAvailabilities = $this->availabilityHelper->getCurrentMonthAvailabilities($startDate);
				$selectedDateTime = strtotime("$startDate $timeslot1->timeslot");
				$usedSeats = $this->availabilityHelper->timeslotHelper->getUsedSeats($selectedDateTime, $timeslot1->timeslot, $bookedAvailabilities);
				$result1 = $timeslot1->seats ? ($timeslot1->seats - $usedSeats) : $timeslot1->seats;
				if(!$endTime){
					$this->remainingSeats = $result1;
					return $result1;
				}
				$timeslot2 = $repo->read($endTime);
				if(!$timeslot2){
					$this->remainingSeats = $result1;
					return $result1;
				}
				$this->seatsMaximum = $timeslot2->seatsMaximum;
				$this->seatsMinimum = $timeslot2->seatsMinimum;
				//we have a start and end time, so return the least number of seats
				$list = array();
				if($this->project->calendarMode === Calendarista_CalendarMode::ROUND_TRIP_WITH_TIME){
					$list = array(array($startDate, $timeslot1), array($endDate, $timeslot2));
				}else{
					$timeslots = $repo->readAllByStartEnd($timeslot1->id, $timeslot2->id);
					foreach($timeslots as $ts){
						array_push($list, array($startDate, $ts));
					}
				}
				$result3 = 0;
				$flag = false;
				
				foreach($list as $ls){
					$dt = $ls[0];
					$timeslot = $ls[1];
					$selectedDateTime = strtotime("$dt $timeslot->timeslot");
					$usedSeats = $this->availabilityHelper->timeslotHelper->getUsedSeats($selectedDateTime, $timeslot->timeslot, $bookedAvailabilities);
					$result2 = $timeslot->seats ? ($timeslot->seats - $usedSeats) : $timeslot->seats;
					if(!$flag){
						$result3 = $result2;
						$flag = true;
						continue;
					}
					if($result2 < $result3){
						$result3 = $result2;
					}
				}
				$this->remainingSeats = $result3;
				return $result3;
			}
		}
		return $totalSeats;
	}
	public function render(){
	?>
	<div class="col-xl-12">
		<div class="form-group">
			<input type="hidden" name="seatsMinimum" value="<?php echo $this->seatsMinimum ?>">
			<input type="hidden" name="seatsMaximum" value="<?php echo $this->seats ?>">
			<?php if($this->availability->selectableSeats && $this->seats):?>
			<label class="form-control-label calendarista-typography--caption1" for="seats_<?php echo $this->uniqueId ?>">
				<?php echo esc_html($this->stringResources['SEATS_LABEL']) ?>
			</label>
			<div>
				<select id="calendarista_seats_<?php echo $this->uniqueId ?>" 
					name="seats"
					class="form-select calendarista-typography--caption1">
					<?php for($i = 0; $i< $this->seats; $i++):?>
					<?php if($this->seatsMaximum > 0 && (($i+1) > $this->seatsMaximum)){continue;}?>
					<?php if(($i+1) < $this->seatsMinimum){continue;}?>
					<option value="<?php echo $i+1 ?>" 
						<?php echo $this->selected('seats', $i+1); ?>><?php echo $i+1 ?></option>
					<?php endfor; ?>
				</select>
			</div>
			<?php elseif($this->seats):?>
			<input type="hidden" name="seats" value="<?php echo $this->seatsMinimum ?>">
			<?php else:?>
			<div class="alert alert-warning calendarista-typography--caption1 calendarista-row-single">
				<?php echo esc_html($this->stringResources['SEATS_EXHAUSTED']); ?>
			</div>
			<?php endif;?>
			<?php if(($this->seats > 1 && $this->availability->displayRemainingSeatsMessage) || ($this->appointment === 1)):?>
			<div class="alert alert-warning calendarista-typography--caption1 calendarista-row-single">
				<?php if($this->availability->seats > 0 && !in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)):?>
					<?php echo sprintf($this->stringResources['SEATS_REMAINING'], $this->remainingSeats); ?>
				<?php endif; ?>
				<?php if($this->appointment === 1): ?>
					<?php esc_html_e('Note: Admin users can book even if no seats are left.', 'calendarista') ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
<?php
	}
}
