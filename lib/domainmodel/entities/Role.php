<?php
class Calendarista_Role extends Calendarista_EntityBase{
	public $userId;
	public $projectId;
	public $email;
	public $projectName;
	public $id;
	protected $args;
	public function __construct($args){
		$this->args = $args;
		if(array_key_exists('userId', $args)){
			$this->userId = (int)$args['userId'];
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if(array_key_exists('email', $args)){
			$this->email = (string)$args['email'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'userId'=>$this->userId
			, 'projectId'=>$this->projectId
			, 'projectName'=>$this->projectName
			, 'email'=>$this->email
		);
	}
}
?>