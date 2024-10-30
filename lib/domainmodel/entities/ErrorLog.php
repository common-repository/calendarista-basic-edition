<?php
class Calendarista_ErrorLog extends Calendarista_EntityBase{
	public $id;
	public $entryDate;
	public $message;
	public function __construct($args){
		if(array_key_exists('message', $args)){
			$this->message = (string)$args['message'];
		}
		if(array_key_exists('entryDate', $args)){
			$this->entryDate = new Calendarista_DateTime((string)$args['entryDate']);
		}else{
			$this->entryDate = new Calendarista_DateTime();
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'entryDate'=>$this->entryDate
			, 'message'=>$this->message
		);
	}
}
?>