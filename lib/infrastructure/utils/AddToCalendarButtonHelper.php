<?php
class Calendarista_AddToCalendarButtonHelper{
	//https://github.com/InteractionDesignFoundation/add-event-to-calendar-docs
	public static function google($args) {
		$params = self::parseArgs($args, true, true);
		return self::gcal($params);
	}
	public static function icalendar($args){
		$params = self::parseArgs($args);
		return self::ical($params);
	}
	public static function outlook($args){
		$params = self::parseArgs($args);
		return self::ical($params);
	}
	protected static function gcal($argsList){
		//Multi date and multi date and time is not supported by gcal. only a single start/end date.
		if(!$argsList || count($argsList) === 0){
			return null;
		}
		$args = $argsList[0];
		$dtFormat = 'Ymd\THis';
		$dFormat = 'Ymd';
		if($args['fullDay'] && $args['dtstart'] ==  $args['dtend']){
			$args['dtend']->modify('+1 day');
		}
		$startDate = $args['dtstart']->format($args['fullDay'] ? $dFormat : $dtFormat);
		$endDate = $args['dtend'] ? $args['dtend']->format($args['fullDay'] ? $dFormat : $dtFormat) : $startDate;
		$result = 'http://www.google.com/calendar/render?action=TEMPLATE';
		$parts = array();
		$parts['text'] = $args['summary'];
		$parts['details'] = $args['description'];
		$parts['dates'] = $startDate . "/" . $endDate;
		if($args['location']){
			$parts['location'] = $args['location'];
		}
		foreach ($parts as $key=>$value) {
		  $result .= sprintf('&%s=%s', $key, $value);
		}
		$result .= sprintf("&sprop=website:%s&sprop=name:%s", rawurlencode($args['siteUrl']), rawurlencode($args['siteName']));
		return $result;
	}
    protected static function ical($argsList){
		$dtFormat = 'Ymd\THis';
		$dFormat = 'Ymd';
		$parts = array();
		array_push($parts, 'BEGIN:VCALENDAR');
		array_push($parts, 'VERSION:2.0');
		foreach($argsList as $args){
			array_push($parts, 'BEGIN:VEVENT');
			$startDate = $args['dtstart']->format($args['fullDay'] ? $dFormat : $dtFormat);
			$endDate = $args['dtend'] ? $args['dtend']->format($args['fullDay'] ? $dFormat : $dtFormat) : $startDate;
			$location = '';
			if($args['location']){
				$location = str_replace(',','\,', $args['location']);
			}
			array_push($parts, sprintf('URL:%s', $args['siteUrl']));
			array_push($parts, sprintf('DTSTART:%s', $startDate));
			array_push($parts, sprintf('DTEND:%s', $endDate));
			array_push($parts, sprintf('SUMMARY:%s', $args['siteName']));
			array_push($parts, sprintf('DESCRIPTION:%s', $args['description']));
			array_push($parts, sprintf('LOCATION:%s', $location));
			array_push($parts, 'END:VEVENT');
		}
		array_push($parts, 'END:VCALENDAR');
		return implode("\r\n", $parts);
	}

	protected static function parseArgs($argsList, $preserveSpace = false, $urlEncode = false){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$appKey = $generalSetting->generateAppKey();
		$result = array();
		for($i = 0; $i < count($argsList); $i++){
			$args = (array)$argsList[$i];
			$dtstart = new Calendarista_DateTime($args['fromDate']);
			$dtend = $args['toDate'] ? new Calendarista_DateTime($args['toDate']) : new Calendarista_DateTime($args['fromDate']);
			$geo = null;
			if($args['regionLat'] && $args['regionLng']){
				$geo = $args['regionLat'] . ';' . $args['regionLng'];
			}
			$fullDay = !in_array($args['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
			$dtEndKey = $fullDay ? 'dtend;value=date' : 'dtend;value=date-time';
			$dtStartKey = $fullDay ? 'dtstart;value=date' : 'dtstart;value=date-time';
			$siteUrl = site_url();
			$siteName = $args['availabilityName'];
			$description = __('NAME', 'calendarista') . ': ' . $args['fullName'] . "\\n" . __('EMAIL', 'calendarista') . ': ' . $args['email'] . "\\n" . $args['availabilityName'] . "\\n" . $args['instructions'];
			if($preserveSpace){
				$description = __('NAME', 'calendarista') . ': ' . $args['fullName'] . PHP_EOL . __('EMAIL', 'calendarista') . ': ' . $args['email'] . PHP_EOL . $args['availabilityName'] . PHP_EOL . $args['instructions'];
			}
			$summary = $args['projectName']  . ' (' . $args['availabilityName'] . ')';
			if($urlEncode){
				$summary = rawurlencode($summary);
			}
			if($urlEncode){
				$description = rawurlencode($description);
			}
			$location = $args['regionAddress'];
			if($urlEncode){
				$location = rawurlencode($args['regionAddress']);
			}
			array_push($result, array(
				'dtEndKey'=>$dtEndKey
				, 'dtStartKey'=>$dtStartKey
				, 'dtend'=>$dtend
				, 'dtstart'=>$dtstart
				, 'summary'=>$summary
				, 'description'=>$description
				, 'location'=>$location
				, 'geo'=>$geo
				, 'uid'=>sprintf('%s%d@calendarista.com', $appKey, $args['orderId'])
				, 'fullDay'=>$fullDay
				, 'siteUrl'=>$siteUrl
				, 'siteName'=>$siteName
			));
		}
		return $result;
	}
    private function __construct() { }
}
?>