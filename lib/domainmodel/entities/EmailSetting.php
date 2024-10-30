<?php
class Calendarista_EmailSetting extends Calendarista_EntityBase{
	public $id = -1;
	public $enable = true;
	public $subject;
	public $content;
	public $emailType;
	public $name;
	public function  __construct($args){
		if(array_key_exists('emailType', $args)){
			$this->emailType = (int)$args['emailType'];
		}
		if(array_key_exists('content', $args)){
			$this->content = (string)$args['content'];
		}
		if(array_key_exists('subject', $args)){
			$this->subject = (string)$args['subject'];
		}
		if(array_key_exists('enable', $args)){
			$this->enable = (bool)$args['enable'];
		}
		if(array_key_exists('name', $args)){
			$this->name = (string)$args['name'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	public function toArray(){
		return array(
			'content'=>$this->content
			, 'subject'=>$this->subject
			, 'enable'=>$this->enable
			, 'emailType'=>$this->emailType
			, 'name'=>$this->name
			, 'id'=>$this->id
		);
	}
	
	public function init(){
		$this->content = Calendarista_TranslationHelper::t('email_template_' . $this->name, $this->content);
		$this->subject = Calendarista_TranslationHelper::t('email_template_' . $this->name . '_subject', $this->subject);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('email_template_' . $this->name, $this->content, true);
		Calendarista_TranslationHelper::register('email_template_' . $this->name . '_subject', $this->subject);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('email_template_' . $this->name);
		Calendarista_TranslationHelper::unregister('email_template_' . $this->name . '_subject');
	}
}
?>