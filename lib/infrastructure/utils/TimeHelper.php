<?php
class Calendarista_TimeHelper{
	public static function listCompactZones(){
		return array(	'Pacific/Majuro',
						'Pacific/Pago_Pago',
						'America/Adak',
						'Pacific/Honolulu',
						'Pacific/Marquesas',
						'Pacific/Gambier',
						'America/Anchorage',
						'America/Los_Angeles',
						'Pacific/Pitcairn',
						'America/Phoenix',
						'America/Denver',
						'America/Guatemala',
						'America/Chicago',
						'Pacific/Easter',
						'America/Bogota',
						'America/New_York',
						'America/Caracas',
						'America/Halifax',
						'America/Santo_Domingo',
						'America/Santiago',
						'America/St_Johns',
						'America/Godthab',
						'America/Argentina/Buenos_Aires',
						'America/Montevideo',
						'America/Noronha',
						'America/Noronha',
						'Atlantic/Azores',
						'Atlantic/Cape_Verde',
						'UTC',
						'Europe/London',
						'Europe/Berlin',
						'Europe/Rome',
						'Africa/Lagos',
						'Africa/Windhoek',
						'Asia/Beirut',
						'Africa/Johannesburg',
						'Asia/Baghdad',
						'Europe/Moscow',
						'Asia/Tehran',
						'Asia/Dubai',
						'Asia/Baku',
						'Asia/Kabul',
						'Asia/Yekaterinburg',
						'Asia/Karachi',
						'Asia/Kolkata',
						'Asia/Kathmandu',
						'Asia/Dhaka',
						'Asia/Omsk',
						'Asia/Rangoon',
						'Asia/Krasnoyarsk',
						'Asia/Jakarta',
						'Asia/Shanghai',
						'Asia/Irkutsk',
						'Australia/Eucla',
						'Australia/Eucla',
						'Asia/Yakutsk',
						'Asia/Tokyo',
						'Australia/Darwin',
						'Australia/Adelaide',
						'Australia/Brisbane',
						'Asia/Vladivostok',
						'Australia/Sydney',
						'Australia/Lord_Howe',
						'Asia/Kamchatka',
						'Pacific/Noumea',
						'Pacific/Norfolk',
						'Pacific/Auckland',
						'Pacific/Tarawa',
						'Pacific/Chatham',
						'Pacific/Tongatapu',
						'Pacific/Apia',
						'Pacific/Kiritimati');
	}
	public static function formatDate($date, $dateFormat = null){
		if(!$date){
			return null;
		}
		$timestamp = null;
		if ($date instanceof DateTime){
			$timestamp = strtotime($date->format('Y-m-d H:i:s'));
		}else{
			$timestamp = strtotime($date);
		}
		if(!$dateFormat){
			$dateFormat = self::getDateFormat();
		}
		self::loadTranslationEarly();
		return date_i18n($dateFormat, $timestamp);
	}
	public static function getDateFormat(){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$dateFormat = $generalSetting->shorthandDateFormat;
		$unscramble = array('0'=>'m', '1'=>'n', '2'=>'d', '3'=>'j', '4'=>'Y', '5'=>'y', '6'=>'F', '7'=>'M', '8'=>'l', '9'=>'D');
		//js to the left, php to the right
		$JSTOPHP = array(
			'mm'=>'0' /*zero padded month*/
			, 'm'=>'1'/*month*/
			, 'dd'=>'2'/*zero padded day*/
			, 'd'=>'3'/*day*/
			, 'yy'=>'4'/*full year*/
			, 'y'=>'5'/*short year*/
			, 'MM'=>'6'/*full month*/
			, 'M'=>'7'/*short month*/
			, 'DD'=>'8'/*full day*/
			, 'D'=>'9'/*short day*/
		);
		foreach($JSTOPHP as $js=>$php){
			$dateFormat = str_replace($js, $php, $dateFormat);
		}
		foreach($unscramble as $key=>$value){
			$dateFormat = str_replace($key, $value, $dateFormat);
		}
		return $dateFormat;
	}
	public static function formatTime($options)
	{
		$time = isset($options['time']) ? self::timezone($options) : null;
		if(!$time){
			return null;
		}
		$result = self::getTimeFormat();
		self::loadTranslationEarly();
		// 12-hour time to 24-hour time 
		return date_i18n($result, strtotime($time));
	}
	public static function getTimeFormat(){
		$result = 'H:i';
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$timeFormat = $generalSetting->timeFormat;
		if($timeFormat === Calendarista_TimeFormat::WORDPRESS){
			$result = get_option('time_format');
		}
		else if($timeFormat === Calendarista_TimeFormat::AMPM){
			// 12-hour time is the plugins default
			$result = 'g:i a';
		}
		return $result;
	}
	public static function getFullDateFormat(){
		return self::getDateFormat() . ' ' . self::getTimeFormat();
	}
	public static function timezone($args){
		$timezone = array_key_exists('timezone', $args) ? $args['timezone'] : null;
		$serverTimezone = array_key_exists('serverTimezone', $args) ? $args['serverTimezone'] : null;
		$time = array_key_exists('time', $args) ? $args['time'] : null;
		$format = array_key_exists('format', $args) ? $args['format'] : 'Y-m-d H:i:s';
		self::loadTranslationEarly();
		if(!$time || !$timezone || !$serverTimezone){
			if($time){
				$now = new DateTime($time);
				return date_i18n($format, strtotime($now->format('Y-m-d H:i:s')));
			}
			return null;
		}
		$fromTimezone = new DateTimeZone($timezone);
		$toTimezone = new DateTimeZone($serverTimezone);
		$date = new DateTime($time, $fromTimezone);
		$date->setTimezone($toTimezone);
		return date_i18n($format, strtotime($date->format('Y-m-d H:i:s')));
	}
	public static function secondsToTime($inputSeconds, $timeUnitLabels) {
		$secondsInAMinute = 60;
		$secondsInAnHour = 60 * $secondsInAMinute;
		$secondsInADay = 24 * $secondsInAnHour;

		// Extract days
		$days = floor($inputSeconds / $secondsInADay);

		// Extract hours
		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		// Extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		// Extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		// Format and return
		$timeParts = array();
		$sections = array(
			'day' => (int)$days
			, 'hour' => (int)$hours
			, 'minute' => (int)$minutes
			//, 'second' => (int)$seconds
		);
		
		foreach ($sections as $name => $value){
			if ($value > 0){
				array_push($timeParts, $value . ' ' . $timeUnitLabels[$name]); 
				// . ($value == 1 ? '' : 's');
			}
		}

		return implode(', ', $timeParts);
	}
	public static function setTimezone($timezone){
		$originalTimezone = date_default_timezone_get();
		if($timezone){
			date_default_timezone_set($timezone);
		}
		return $originalTimezone;
	}
	public static function loadTranslationEarly(){
		global $wp_locale;  
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!in_array($generalSetting->shorthandDateFormat, array('d M, y', 'DD, d MM, yy'))){
			return;
		}
		//some dateformats require loading the wordpress locale i.e. weekday names in current language
		//since our ajax requests happen early, manual labor of love is required
		if(!$wp_locale){
			$locale = apply_filters('plugin_locale',  get_locale(), 'calendarista');
			load_textdomain('default', WP_LANG_DIR . "/$locale.mo");
			$GLOBALS['wp_locale'] = new WP_Locale();
		}
	}
}
?>