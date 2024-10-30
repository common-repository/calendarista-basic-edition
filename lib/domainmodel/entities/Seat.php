<?php
class Calendarista_Seat extends Calendarista_EntityBase{
	public $id;
	public $projectId;
	public $bookingDate;
	public $bookedDaysCount;
	public $timeslotsCount;
	public $hourStart;
	public $minuteStart;
	public $hourEnd;
	public $minuteEnd;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('bookingDate', $args)){
			$this->bookingDate = $args['bookingDate'] instanceOf Calendarista_DateTime ? $args['bookingDate'] : new Calendarista_DateTime($args['bookingDate']);
		}
		if(array_key_exists('bookedDaysCount', $args)){
			$this->bookedDaysCount = (int)$args['bookedDaysCount'];
		}
		if(array_key_exists('timeslotsCount', $args)){
			$this->timeslotsCount = (int)$args['timeslotsCount'];
		}
		if(array_key_exists('hourStart', $args)){
			$this->hourStart = (int)$args['hourStart'];
		}
		if(array_key_exists('minuteStart', $args)){
			$this->minuteStart = (int)$args['minuteStart'];
		}
		if(array_key_exists('hourEnd', $args)){
			$this->hourEnd = (int)$args['hourEnd'];
		}
		if(array_key_exists('minuteEnd', $args)){
			$this->minuteEnd = (int)$args['minuteEnd'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'bookingDate'=>$this->bookingDate ? Calendarista_DateHelper::formatString($this->bookingDate) : null
			, 'bookedDaysCount'=>$this->bookedDaysCount
			, 'timeslotsCount'=>$this->timeslotsCount
			, 'hourStart'=>$this->hourStart
			, 'minuteStart'=>$this->minuteStart
			, 'hourEnd'=>$this->hourEnd
			, 'minuteEnd'=>$this->minuteEnd
		);
	}
}
?>