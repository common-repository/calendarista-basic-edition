<?php
class Calendarista_TranslationHelper{
	public static function register($name, $value, $multiLine = false){

	}
	
	public static function unregister($name){

	}
	
	public static function t($name, $value){
		return stripcslashes($value);
	}
	
	public static function registerEmailResource(){

	}
	public static function requiresTranslation(){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!$generalSetting->translationEngine){
			return false;
		}
		return true;
	}
	public static function internationalization($pluginName = 'calendarista', $rootFolder = null){
		if(!$rootFolder){
			$rootFolder = CALENDARISTA_LANGUAGES_FOLDER;
		}
		$locale = apply_filters('plugin_locale',  get_locale(), $pluginName);
		$mofile = sprintf('%s-%s.mo', $pluginName, $locale);

		$langFileLocal  = WP_PLUGIN_DIR . '/' . CALENDARISTA_LANGUAGES_FOLDER . $mofile;
		$langFileGlobal = WP_LANG_DIR . '/' . $pluginName . '/' . $mofile;

		if (file_exists($langFileGlobal)){
			//the wordpress /wp-content/languages/calendarista directory
			load_textdomain($pluginName, $langFileGlobal);
		}elseif(file_exists($langFileLocal)){
			//this plugins languages directory
			load_textdomain($pluginName, $langFileLocal);
		}else{
			//default language file
			load_plugin_textdomain($pluginName, false, $rootFolder);
		}
	}
}
?>