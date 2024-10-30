<?php
class Calendarista_GeneralSettingHelper{
	protected static $generalSetting = null;
	protected static $repo = null;
    public static function get() {
        if (!self::$generalSetting) {
			self::$repo = new Calendarista_GeneralSettingsRepository();
			self::$generalSetting = self::$repo->read();
        }
        return self::$generalSetting;
    }
	public static function getBrandName(){
		if(self::$generalSetting->brandName){
			return self::$generalSetting->brandName;
		}
		return 'Calendarista';
	}
	public static function getBrandNameToLower(){
		$result = self::getBrandName();
		$result = str_replace(array(',', ' '), '-', $result);
		return strtolower($result);
	}
    private function __construct() { }
}
?>