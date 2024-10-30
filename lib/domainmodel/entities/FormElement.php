<?php
class Calendarista_FormElement extends Calendarista_EntityBase{
	public $id;
	public $projectId;
	public $label;
	public $elementType;
	public $lineSeparator;
	public $orderIndex;
	public $className;
	public $options;
	public $defaultOptionItem;
	public $defaultSelectedOptionItem;
	public $validation = array();
	public $content;
	public $placeHolder;
	public $country = 'US';
	public $phoneNumberField = false;
	public $guestField = false;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('label', $args)){
			$this->label = $this->decode((string)$args['label']);
		}
		if(array_key_exists('elementType', $args)){
			$this->elementType = (int)$args['elementType'];
		}
		if(array_key_exists('lineSeparator', $args)){
			$this->lineSeparator = (bool)$args['lineSeparator'];
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex= (int)$args['orderIndex'];
		}
		if(array_key_exists('className', $args)){
			$this->className = $this->decode((string)$args['className']);
		}
		if(array_key_exists('options', $args)){
			$this->options =  is_array($args['options']) ? $args['options'] : array_map('trim', explode(',', (string)$args['options']));
		}
		if(array_key_exists('defaultSelectedOptionItem', $args)){
			$this->defaultSelectedOptionItem = (string)$args['defaultSelectedOptionItem'];
		}
		if(array_key_exists('defaultOptionItem', $args)){
			$this->defaultOptionItem = (string)$args['defaultOptionItem'];
		}
		if(array_key_exists('validation', $args)){
			if(is_string($args['validation'])){
				parse_str($args['validation'], $output);
				$v = new Calendarista_Validation($output);
				$this->validation = $v->getAllConstraints();
			}else{
				$this->validation = (array)$args['validation'];
			}
		}
		if(array_key_exists('content', $args)){
			$this->content = stripcslashes($this->decode((string)$args['content']));
		}
		if(array_key_exists('country', $args)){
			$this->country = $this->decode((string)$args['country']);
		}
		if(array_key_exists('placeHolder', $args)){
			$this->placeHolder = (string)$args['placeHolder'];
		}
		if(array_key_exists('phoneNumberField', $args)){
			$this->phoneNumberField = (bool)$args['phoneNumberField'];
		}
		if($this->elementType === Calendarista_ElementType::PHONE){
			$this->phoneNumberField = true;
		}
		if(array_key_exists('guestField', $args)){
			$this->guestField = $args['guestField'] ? true : false;
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->parseValidation($args);
		$this->updateResources();
		$this->init();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'label'=>$this->label
			, 'elementType'=>$this->elementType
			, 'lineSeparator'=>$this->lineSeparator
			, 'orderIndex'=>$this->orderIndex
			, 'className'=>$this->className
			, 'options'=>$this->options
			, 'defaultOptionItem'=>$this->defaultOptionItem
			, 'defaultSelectedOptionItem'=>$this->defaultSelectedOptionItem
			, 'validation'=>$this->validation
			, 'content'=>$this->content
			, 'placeHolder'=>$this->placeHolder
			, 'country'=>$this->country
			, 'phoneNumberField'=>$this->phoneNumberField
			, 'guestField'=>$this->guestField
		);
	}
	
	public function getValidationAttributes(){
		$result = array();
		if(!$this->validation){
			return '';
		}
		foreach($this->validation as $key=>$val){
			if($val !== null){
				switch($key){
					case 'required':
						if($val){
							array_push($result, 'data-parsley-required="true"');
						}
					break;
					case 'minLength':
						if($val){
							array_push($result, sprintf('data-parsley-minlength="%s"', $val));
						}
					break;
					case 'maxLength':
						if($val){
							array_push($result, sprintf('data-parsley-maxlength="%s"', $val));
						}
					break;
					case 'min':
						if($val){
							array_push($result, sprintf('data-parsley-min="%s"', $val));
						}
					break;
					case 'max':
						if($val){
							array_push($result, sprintf('data-parsley-max="%s"', $val));
						}
					break;
					case 'regex':
						if($val){
							array_push($result, sprintf('data-parsley-pattern="%s"', Calendarista_StringResourceHelper::decodeString($val)));
						}
					break;
					case 'email':
						if($val){
							array_push($result, 'data-parsley-type="email"');
						}
					break;
					case 'url':
						if($val){
							array_push($result, 'data-parsley-type="url"');
						}
					break;
					case 'digits':
						if($val){
							array_push($result, 'data-parsley-type="digits"');
						}
					break;
					case 'number':
						if($val){
							array_push($result, 'data-parsley-type="number"');
						}
					break;
					case 'alphanum':
						if($val){
							array_push($result, 'data-parsley-type="alphanum"');
						}
					break;
					case 'dateIso':
						if($val){
							array_push($result, 'data-parsley-type="dateIso"');
						}
					break;
				}
			}
		}
		if(count($result) > 0){
			array_push($result, 'data-parsley-trigger="change"');
		}
		return implode(' ', $result);
	}
	protected function parseValidation($args){
		foreach($args as $key=>$value) {
			switch($key){
				case 'required':
				case 'minLength':
				case 'maxLength':
				case 'min':
				case 'max':
				case 'regex':
				case 'email':
				case 'url':
				case 'dateIso':
				$this->validation[$key] = $value;
				break;
				case 'entryType':
					switch($value){
						case 'digits':
						case 'number':
						case 'alphanum':
						$this->validation[$value] = true;
					}
				break;
			}
		}
	}
	public function getOptions(){
		return is_array($this->options) ? implode(',', $this->options) : '';
	}
	public function setOptions($options){
		return $options ? array_map('trim', explode(',', (string)$options)) : '';
	}
	protected function init(){
		$this->label = Calendarista_TranslationHelper::t('form_field_' . $this->id . '_label_project' . $this->projectId, $this->label);
		$this->placeHolder = Calendarista_TranslationHelper::t('form_field_' . $this->id . '_place_holder_project' . $this->projectId, $this->placeHolder);
		$this->content = Calendarista_TranslationHelper::t('form_field_' . $this->id . '_content_project' . $this->projectId, $this->content);
		$this->options = $this->setOptions(Calendarista_TranslationHelper::t('form_field_' . $this->id . '_options_project' . $this->projectId, $this->getOptions()));
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('form_field_' . $this->id . '_label_project' . $this->projectId, $this->label);
		Calendarista_TranslationHelper::register('form_field_' . $this->id . '_place_holder_project' . $this->projectId, $this->placeHolder);
		Calendarista_TranslationHelper::register('form_field_' . $this->id . '_content_project' . $this->projectId, $this->content, true);
		Calendarista_TranslationHelper::register('form_field_' . $this->id . '_options_project' . $this->projectId, $this->getOptions(), true);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('form_field_' . $this->id . '_label_project' . $this->projectId);
		Calendarista_TranslationHelper::unregister('form_field_' . $this->id . '_place_holder_project' . $this->projectId);
		Calendarista_TranslationHelper::unregister('form_field_' . $this->id . '_content_project' . $this->projectId);
		Calendarista_TranslationHelper::unregister('form_field_' . $this->id . '_options_project' . $this->projectId);
	}
}
?>