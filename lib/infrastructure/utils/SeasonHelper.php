<?php
class Calendarista_SeasonHelper{
	public static function getSeason($startDate, $seasons, $totalNumberOfDays = 1){
		$startDate = strtotime(date(CALENDARISTA_DATEFORMAT, $startDate));
		if($totalNumberOfDays <= 1){
			$endDate = $startDate;
		}else{
			$endDate = strtotime('+ ' . ($totalNumberOfDays - 1) . ' days', $startDate);
		}
		if(is_array($seasons) && count($seasons) > 0){
			foreach($seasons as $season){
				if($startDate >= strtotime($season['start']) && $endDate <= strtotime($season['end'])){
					if(count($season['repeatWeekdayList']) > 0){
						$flag = false;
						$_startDate = $startDate;
						while($_startDate <= $endDate){
							$weekday = (int)date('N', $_startDate);
							//Note: when using pricing scheme and a day within range 
							//does not fall in the repeat weekday list
							//then the pricing scheme is ignored.
							if(in_array($weekday, $season['repeatWeekdayList'])){
								//this is a weekday that does not follow season rules.
								$flag = true;
								break;
							}
							$_startDate = strtotime('+1 day', $_startDate);
						}
						if($flag){
							return $season;
						}
					}else{
						return $season;
					}
				}
			}
		}
		return null;
	}
	public static function getCost($date, $originalCost, $seasons, $season = null){
		if(!$season){
			$season = self::getSeason($date, $seasons);
		}
		if($season){
			if($season['percentageBased']){
				if(!($originalCost > 0)) {
					return $originalCost;
				}
				$amount = ($originalCost / 100) * $season['cost'];
				return $season['costMode'] ? ($originalCost - $amount) : ($originalCost + $amount);
			}
			$result = $season['costMode'] ?  ($originalCost - $season['cost']) : ($originalCost + $season['cost']);
			return $result > 0 ? $result : $originalCost;
		}
		return $originalCost;
	}
	public static function fallsWithinSeason($date, $seasons){
		//only applies to the individual day
		$season = self::getSeason($date, $seasons);
		return $season !== null;
	}
    private function __construct() { }
}
?>