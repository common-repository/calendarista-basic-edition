<?php
class Calendarista_GeneralSettingsRepository extends Calendarista_SettingsRepositoryBase
{
	public function __construct(){
		$settingName = 'General settings';
		parent::__construct($settingName);
	}

	public function read($param = null){
		$result = parent::read();
		return new Calendarista_GeneralSetting($result);
	}
}
?>