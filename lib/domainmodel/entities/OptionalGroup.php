<?php
class Calendarista_OptionalGroup extends Calendarista_EntityBase{
	public $id;
	public $projectId;
	public $orderIndex;
	public $name;
	public $minRequired;
	public $multiply;
	public $displayMode = Calendarista_OptionalDisplayMode::CHECKBOX_LIST;
	public $maxSelection;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex = (int)$args['orderIndex'];
		}
		if(array_key_exists('displayMode', $args)){
			$this->displayMode = (int)$args['displayMode'];
		}
		if(array_key_exists('minRequired', $args)){
			$this->minRequired = (int)$args['minRequired'];
		}else{
			$this->minRequired = 0;
		}
		if(array_key_exists('maxSelection', $args)){
			$this->maxSelection = (int)$args['maxSelection'];
		}else{
			$this->maxSelection = 0;
		}
		if(array_key_exists('multiply', $args)){
			$this->multiply = (int)$args['multiply'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	protected function init(){
		$this->name = Calendarista_TranslationHelper::t('optional_group_name_' . $this->id, $this->name);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('optional_group_name_' . $this->id, $this->name);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('optional_group_name_' . $this->id);
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'name'=>$this->name
			, 'displayMode'=>$this->displayMode
			, 'multiply'=>$this->multiply
			, 'minRequired'=>$this->minRequired
			, 'maxSelection'=>$this->maxSelection
		);
	}
}
?>