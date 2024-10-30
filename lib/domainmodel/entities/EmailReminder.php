<?php
class Calendarista_EmailReminder extends Calendarista_EntityBase{
	public $id;
	public $projectId;
	public $orderId;
	public $bookedAvailabilityId;
	public $sentDate;
	public $fullName;
	public $lastname;
	public $email;
	public $reminderType;
	public function __construct($args){
		if(array_key_exists('fullName', $args)){
			$this->fullName = $args['fullName'];
		}
		if(array_key_exists('email', $args)){
			$this->email = $args['email'];
		}
		if(array_key_exists('sentDate', $args)){
			$this->sentDate = $args['sentDate'];
		}
		if(array_key_exists('bookedAvailabilityId', $args)){
			$this->bookedAvailabilityId = (int)$args['bookedAvailabilityId'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('reminderType', $args)){
			$this->reminderType = (int)$args['reminderType'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'projectId'=>$this->projectId
			, 'orderId'=>$this->orderId
			, 'bookedAvailabilityId'=>$this->bookedAvailabilityId
			, 'fullName'=>$this->fullName
			, 'email'=>$this->email
			, 'sentDate'=>$this->sentDate
			, 'reminderType'=>$this->reminderType
			, 'id'=>$this->id
		);
	}
}
?>