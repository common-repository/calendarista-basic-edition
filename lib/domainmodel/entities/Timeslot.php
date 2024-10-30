<?php
class Calendarista_Timeslot extends Calendarista_EntityBase{
	public $id = -1;
	public $projectId;
	public $availabilityId;
	public $weekday;
	public $timeslot;
	public $cost;
	public $day;
	public $seats = 1;
	public $seatsMaximum = 0;
	public $seatsMinimum = 1;
	public $bookedSeats;
	public $paddingTimeBefore;
	public $paddingTimeAfter;
	public $outOfStock = false;
	public $deal = 0;
	public $startTime = false;
	public $returnTrip = false;
	private $usedSeats = 0;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('availabilityId', $args)){
			$this->availabilityId = (int)$args['availabilityId'];
		}
		if(array_key_exists('weekday', $args) && $args['weekday']){
			$this->weekday = (int)$args['weekday'];
		}
		if(array_key_exists('timeslot', $args)){
			$this->timeslot = (string)$args['timeslot'];
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('day', $args) && $args['day']){
			$this->day = new Calendarista_DateTime($args['day']);
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
		if(array_key_exists('bookedSeats', $args)){
			$this->bookedSeats = (int)$args['bookedSeats'];
		}
		if(array_key_exists('paddingTimeBefore', $args)){
			$this->paddingTimeBefore = (int)$args['paddingTimeBefore'];
		}
		if(array_key_exists('paddingTimeAfter', $args)){
			$this->paddingTimeAfter = (int)$args['paddingTimeAfter'];
		}
		if(array_key_exists('deal', $args)){
			$this->deal = (int)$args['deal'];
		}
		if(array_key_exists('startTime', $args)){
			$this->startTime = (bool)$args['startTime'];
		}
		if(array_key_exists('returnTrip', $args)){
			$this->returnTrip = (bool)$args['returnTrip'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'paddingTimeAfter'=>$this->paddingTimeAfter
			, 'paddingTimeBefore'=>$this->paddingTimeBefore
			, 'bookedSeats'=>$this->bookedSeats
			, 'seatsMaximum'=>$this->seatsMaximum
			, 'seatsMinimum'=>$this->seatsMinimum
			, 'seats'=>$this->seats
			, 'day'=>$this->day
			, 'cost'=>$this->cost
			, 'timeslot'=>$this->timeslot
			, 'weekday'=>$this->weekday
			, 'availabilityId'=>$this->availabilityId
			, 'projectId'=>$this->projectId
			, 'outOfStock'=>$this->outOfStock
			, 'deal'=>$this->deal
			, 'startTime'=>$this->startTime
			, 'returnTrip'=>$this->returnTrip
		);
	}
	public function compareDay($dt){
		if($this->day && $this->day->format(CALENDARISTA_DATEFORMAT) == $dt->format(CALENDARISTA_DATEFORMAT)){
			return true;
		}
		return false;
	}
	public function hasPadding(){
		if($this->paddingTimeBefore || $this->paddingTimeAfter){
			return true;
		}
		return false;
	}
	public function getUsedSeats(){
		return $this->usedSeats;
	}
	public function setUsedSeats($val){
		$this->usedSeats = $val;
	}
	public function getSeatCount(){
		if($this->usedSeats){
			return $this->seats - $this->usedSeats;
		}
		return $this->seats;
	}
	public function setOutOfStock(){
		$this->outOfStock = true;
	}
}
?>