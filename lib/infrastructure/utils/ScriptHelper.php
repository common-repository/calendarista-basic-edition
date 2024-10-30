<?php
class Calendarista_ScriptHelper{
	public static function enqueueDatePickerLocale(){
		$scriptFolder = 'assets/scripts/';
		$locale = str_replace('_', '-', get_locale());
		$datePickerLocaleFolder = CALENDARISTA_ROOT_FOLDER . '/assets/scripts/i18n/datepicker/';
		$localeFileName = 'datepicker-' . $locale . '.js';
		$datePickerLocalePath = $datePickerLocaleFolder . $localeFileName;
		$hasDatePickerLocale = false;
		if(file_exists($datePickerLocalePath)){
			$hasDatePickerLocale = true;
		}else{
			$localePart = explode('-', $locale);
			$localeFileName = 'datepicker-' . $localePart[0] . '.js';
			$datePickerLocalePath = $datePickerLocaleFolder . $localeFileName;
			if(file_exists($datePickerLocalePath)){
				$hasDatePickerLocale = true;
			}
		}

		if($hasDatePickerLocale){
			wp_enqueue_script( 'jquery-ui-datepicker-locale', CALENDARISTA_PLUGINDIR . $scriptFolder . "i18n/datepicker/$localeFileName", '','',true);
		}
	}
	
	public static function enqueueParsleyLocale(){
		$scriptFolder = 'assets/scripts/';
		$locale = str_replace('_', '-', get_locale());
		$parsleyLocaleFolder = CALENDARISTA_ROOT_FOLDER . '/assets/scripts/i18n/parsley/';
		$localeFileName = $locale . '.js';
		$parsleyLocalePath = $parsleyLocaleFolder . $localeFileName;
		$hasparsleyLocale = false;
		if(file_exists($parsleyLocalePath)){
			$hasparsleyLocale = true;
		}else{
			$localePart = explode('-', $locale);
			$localeFileName = $localePart[0] . '.js';
			$parsleyLocalePath = $parsleyLocaleFolder . $localeFileName;
			if(file_exists($parsleyLocalePath)){
				$hasparsleyLocale = true;
			}
		}
		
		if($hasparsleyLocale){
			wp_enqueue_script( 'parsley-locale', CALENDARISTA_PLUGINDIR . $scriptFolder . "i18n/parsley/$localeFileName", '','',true);
		}
	}
}
?>