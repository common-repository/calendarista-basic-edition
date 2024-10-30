<?php
class Calendarista_BaseController{
	public $generalSetting;
	public function __construct($createCallback = null, $updateCallback = null, $deleteCallback = null){
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		if (array_key_exists('calendarista_create', $_POST)){
			$this->create($createCallback);
		}else if(array_key_exists('calendarista_update', $_POST)){
			$this->update($updateCallback);
		}else if(array_key_exists('calendarista_delete', $_POST)){
			$this->delete($deleteCallback);
		}
	}
	public function create($callback){

	}
	public function update($updateCallback){
		
	}
	public function delete($deleteCallback){
		
	}
	
	public function executeCallback($callback, $param = null){
		if ($callback !== null && is_callable($callback)) {
			if($param === null){
				$param = array();
			}
			call_user_func_array($callback, $param);
		}
	}
	
	protected function getPostValue($key, $default = null){
		if(isset($_POST[$key])){
			return is_array($_POST[$key]) ? $_POST[$key] : sanitize_text_field($_POST[$key]);
		}
		return $default;
	}
	protected function getBoolPostValue($key){
		return isset($_POST[$key]) ? true : false;
	}
	protected function getBoolValue($key){
		return isset($_POST[$key]) ? true : false;
	}
	protected function getIntValue($key, $default = null){
		return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
	}
	protected function getFloatValue($key, $default = null){
		return isset($_POST[$key]) ? (float)$_POST[$key] : $default;
	}
	protected function getStringValue($key, $default = null){
		return isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : $default;
	}
	public function encode($value){
		return htmlspecialchars($value, ENT_QUOTES);
	}
}
?>