<?php
class Calendarista_EmailSettingRepository extends Calendarista_SettingsRepositoryBase
{
	public function __construct($settingName){
		if(!isset($settingName)){
			 throw new Exception('setting name parameter must be provided.');
		}
		parent::__construct($settingName);
	}

	public function read($param = null){
		$result = parent::read();
		if(count($result) > 0){
			return new Calendarista_EmailSetting($result);
		}
		return null;
	}
	
	public function insert($setting){
		$setting->content = stripslashes($setting->content);
		$result = parent::insert($setting);
		 if($result !== false){
			$setting->updateResources();
		 }
		return $result;
	}
	
	public function update($setting){
		$setting->content = stripslashes($setting->content);
		$result = parent::update($setting);
		$setting->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources();
		return parent::delete($id);
	}
	
	public function deleteResources(){
		$result = $this->read();
		if($result){
			$result->deleteResources();
		}
	}
}
?>