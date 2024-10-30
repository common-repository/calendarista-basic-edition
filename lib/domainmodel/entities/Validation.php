<?php
class Calendarista_Validation extends Calendarista_EntityBase{
	private $constraints = array(
		'required'=>null
		, 'notBlank'=>null
		, 'minLength'=>null
		, 'maxLength'=>null
		, 'min'=>null
		, 'max'=>null
		, 'regex'=>null
		, 'email'=>null
		, 'url'=>null
		, 'digits'=>null
		, 'number'=>null
		, 'alphanum'=>null
		, 'dateIso'=>null
	);
	public function __construct($constraints = null){
		$this->setConstraints($constraints);
	}
	
	public function setConstraint($key, $value){
		$constraints[$key] = $value;
	}
	
	public function getConstraint($key){
		return $constraints[$key];
	}
	
	public function getAllConstraints(){
		return $this->constraints;
	}
	
	public function getConstraints(){
		$result = array();
		foreach($this->constraints as $key=>$value){
			if($value !== null){
				array_push($result, $constraint);
			}
		}
		return $result;
	}
	
	public function setConstraints($constraints){
		if($constraints){
			foreach($constraints as $key=>$value){
				$this->constraints[$key] = $value;
			}
		}
	}
	
	public function toArray(){
		return $this->getConstraint();
	}
}
?>