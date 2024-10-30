<?php
class Calendarista_Validator{
	private $messages;
	private $fieldName;
	private $fieldValue;
	public $constraints;
	public $errors;
	public function __construct($constraints, $fieldName, $fieldValue){
		$this->constraints = $constraints;
		$this->fieldName = $fieldName;
		$this->fieldValue = $fieldValue;

		$this->messages = array(
			'required'=>__('%s: Field "%s" is required.', 'calendarista')
			, 'notBlank'=>__('%s: Field "%s" should not be blank.', 'calendarista')
			, 'minLength'=>__('%s: Field "%s" value is too short.', 'calendarista')
			, 'maxLength'=>__('%s: Field "%s" value is too long.', 'calendarista')
			, 'min'=>__('%s: Field "%s" value is smaller than expected.', 'calendarista')
			, 'max'=>__('%s: Field "%s" value is larger than expected.', 'calendarista')
			, 'regex'=>__('%s: Field "%s" is in valid.', 'calendarista')
			, 'email'=>__('%s: Field "%s" value is not a valid email.', 'calendarista')
			, 'url'=>__('%s: Field "%s" value is not a valid url.', 'calendarista')
			, 'digits'=>__('%s: Field "%s" value should be digits.', 'calendarista')
			, 'number'=>__('%s: Field "%s" value should be a number.', 'calendarista')
			, 'alphanum'=>__('%s: Field "%s" value can only contain alpha numeric characters.', 'calendarista')
		);
		
		$this->errors = $this->check();
	}
	public function isValid(){
		return count($this->errors) === 0;
	}
	public function check(){
		$errors = array();
		$isValid = true;
		foreach($this->constraints as $key=>$value){
			if($value !== null){
				switch($key){
					case 'required':
						if($value){
							$isValid = $this->required();
						}
					break;
					case 'minLength':
						$isValid = $this->minLength(intval($value));
					break;
					case 'maxLength':
						$isValid = $this->maxLength(intval($value));
					break;
					case 'min':
						$isValid = $this->min(intval($value));
					break;
					case 'max':
						$isValid = $this->max(intval($value));
					break;
					case 'regex':
						$isValid = $this->regex($value);
					break;
					case 'email':
						if($value){
							$isValid = $this->email();
						}
					break;
					case 'url':
						if($value){
							$isValid = $this->url();
						}
					break;
					case 'digits':
						if($value){
							$isValid = $this->digits();
						}
					break;
					case 'number':
						if($value){
							$isValid = $this->number();
						}
					break;
					case 'alphanum':
						if($value){
							$isValid = $this->alphanum();
						}
					break;
				}
				if(!$isValid){
					array_push($errors, sprintf($this->messages[$key], $key, $this->fieldName));
					$isValid = true;
				}
			}
		}
		return $errors;
	}
	
	public function required(){
		if(strlen($this->fieldValue) === 0){
			return false;
		}
		return true;
	}
	
	public function minLength($value){
		if($value === 0){
			return true;
		}
		if(strlen($this->fieldValue) === 0){
			return true;
		} else if(strlen($this->fieldValue) < $value){
			return false;
		}
		return true;
	}
	
	public function maxLength($value){
		if($value === 0){
			return true;
		}
		if(strlen($this->fieldValue) === 0){
			return true;
		} else if(strlen($this->fieldValue) > $value){
			return false;
		}
		return true;
	}
	
	public function min($value){
		if($value === 0){
			return true;
		}
		if(strlen($this->fieldValue) === 0){
			return true;
		} else if(!is_numeric($this->fieldValue)){
			return false;
		}
		if(intval($this->fieldValue) < $value){
			return false;
		}
		return true;
	}
	
	public function max($value){
		if($value === 0){
			return true;
		}
		if(strlen($this->fieldValue) === 0){
			return true;
		} else if(!is_numeric($this->fieldValue)){
			return false;
		}
		
		if(intval($this->fieldValue) > $value){
			return false;
		}
		return true;
	}
	
	public function regex($value){
		if(strlen($this->fieldValue) === 0 || strlen($value) === 0){
			return true;
		}
		$option = array('options'=>array('regexp'=>'/' . $value . '/'));
		return filter_var($this->fieldValue , FILTER_VALIDATE_REGEXP , $option) !== false;
	}
	
	public function email(){
		if(strlen($this->fieldValue) === 0){
			return true;
		}
		return filter_var($this->fieldValue, FILTER_VALIDATE_EMAIL);
	}
	
	public function url(){
		if(strlen($this->fieldValue) === 0){
			return true;
		}
		return filter_var($this->fieldValue, FILTER_VALIDATE_URL);
	}
	
	public function digits(){
		if(strlen($this->fieldValue) === 0){
			return true;
		}
		return is_numeric($this->fieldValue) && intval($this->fieldValue) >= 0;
	}
	
	public function number(){
		if(strlen($this->fieldValue) === 0){
			return true;
		}
		return is_numeric($this->fieldValue);
	}
	
	public function alphanum(){
		if(strlen($this->fieldValue) === 0){
			return true;
		}
		return ctype_alnum($this->fieldValue);
	}
	
	public function dateIso(){}
}
?>