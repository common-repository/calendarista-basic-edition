<?php
class Calendarista_Tag extends Calendarista_EntityBase{
	public $id;
	public $name;
	public $selected = false;
	public function __construct($args){
		if(array_key_exists('name', $args)){
			$this->name = stripcslashes((string)$args['name']);
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if(array_key_exists('tagId', $args) && $args['tagId']){
			$this->selected = true;
		}
		$this->updateResources();
		$this->init();
	}
	protected function init(){
		$this->name = Calendarista_TranslationHelper::t('tag_name_' . $this->id, $this->name);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('tag_name_' . $this->id, $this->name);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('tag_name_' . $this->id);
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'name'=>$this->name
		);
	}
}
?>