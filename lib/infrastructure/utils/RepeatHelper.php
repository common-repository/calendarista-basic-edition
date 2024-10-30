<?php
class Calendarista_RepeatHelper{
	public static function hasAvailability($date, $availability){
		//not taking time into account for date,
		//however hasTerminated does, fix it.
		if(!$availability->availableDate){
			return false;
		}
		$availableDays = Calendarista_AvailabilityDayHelper::get($availability->id);
		if(count($availableDays) > 0){
			array_push($availableDays, $availability->availableDate->format('Y-m-d'));
			if(in_array(date('Y-m-d', strtotime($date)), $availableDays)){
				return true;
			}
			if($availability->hasRepeat && in_array($availability->repeatFrequency, array(6/*Monthly*/, 7/*Yearly*/))){
				$currentMonthDay = date('m-d', strtotime($date));
				$currentDay = date('d', strtotime($date));
				foreach($availableDays as $individualDay){
					if($currentDay == date('d', strtotime($individualDay)) && $availability->repeatFrequency === 6/*Monthly*/){
						return true;
					}else if($currentMonthDay == date('m-d', strtotime($individualDay))){
						return true;
					}
				}
			}
			return false;
		}
		$timestamp1 = strtotime($availability->availableDate->format(CALENDARISTA_DATEFORMAT));
		$timestamp2 = strtotime($date);
		$weekday = (int)date('N', $timestamp2);
		/*1 (per Lunedì) a 7 (per Domenica)*/
		if($timestamp1 === $timestamp2){
			return true;
		} else if($timestamp2 > $timestamp1 && $availability->hasRepeat){
			$diff = self::getDiffByRepeatFrequency($timestamp1, $timestamp2, $availability);
			$repeat = self::repeat($availability, $diff);
			if(!$repeat || self::hasTerminated($availability, $timestamp2)){
				return false;
			}
			switch($availability->repeatFrequency){
				case 1: //DAILY
				return true;
				break;
				case 2: //EVERY_WEEK_DAY
				if(in_array($weekday, array(1, 2, 3, 4, 5))){
					return true;
				}
				break;
				case 3: //EVERY_MONDAY_WEDNESDAY_FRIDAY
				if(in_array($weekday, array(1, 3, 5))){
					return true;
				}
				break;
				case 4: //EVERY_TUESDAY_THURSDAY
				if(in_array($weekday, array(2, 4))){
					return true;
				}
				break;
				case 5: //WEEKLY
				return self::repeatWeekly($timestamp1, $timestamp2, $availability->repeatWeekdayList, $availability->repeatInterval);
				break;
				case 6: //MONTHLY
				case 7: //YEARLY
				$timestamp3 = self::getNextMonthlyRepetition($timestamp1, $availability->repeatFrequency === 6 ? $diff : ($diff*12));
				if($timestamp2 === $timestamp3){
					return true;
				}
				break;
			}
		}
		return false;
	}
	public static function getTerminationDate($availability){
		$timestamp1 = strtotime($availability->availableDate->format('Y-m-d H:i:s'));
		$length = $availability->daysInPackage - 1;
		$availableDays = Calendarista_AvailabilityDayHelper::get($availability->id);
		if(count($availableDays) > 0){
			if($availability->hasRepeat && in_array($availability->repeatFrequency, array(6/*Monthly*/, 7/*Yearly*/))){
				array_push($availableDays, $availability->availableDate->format('Y-m-d'));
				$timestamp1 = strtotime($availableDays[0]);
			}
		}
		if(!$availability->hasRepeat){
			return $timestamp1;
		}
		if($availability->terminateMode === 0/*NEVER*/){
			//just return any future date
			return strtotime('+2 year', time());
		}
		/*1 (per Lunedì) a 7 (per Domenica)*/
		if($availability->terminateMode === 2/*ON_END_DATE*/){
			return strtotime($availability->endDate->format('Y-m-d H:i:s'));
		}
		$repeatInterval = $availability->repeatInterval;
		$terminateAfterOccurrence = $availability->terminateAfterOccurrence;
		switch($availability->repeatFrequency){
			case 1: //DAILY
			return strtotime('+' . ($repeatInterval * $terminateAfterOccurrence) . ' day', $timestamp1);
			break;
			case 2: //EVERY_WEEK_DAY 1, 2, 3, 4, 5
			case 3: //EVERY_MONDAY_WEDNESDAY_FRIDAY 1, 3, 5
			$lastRepeatWeekday = 5;
			return self::getTerminationByWeekday($timestamp1, $lastRepeatWeekday,  $terminateAfterOccurrence);
			break;
			case 4: //EVERY_TUESDAY_THURSDAY 2, 4
			$lastRepeatWeekday = 4;
			return self::getTerminationByWeekday($timestamp1, $lastRepeatWeekday,  $terminateAfterOccurrence);
			break;
			case 5: //WEEKLY
			return strtotime('+' . ($repeatInterval * $terminateAfterOccurrence) . ' weeks', $timestamp1);
			break;
			case 6: //MONTHLY
			return strtotime('+' . ($repeatInterval * $terminateAfterOccurrence) . ' months', $timestamp1);
			case 7: //YEARLY
			return strtotime('+' . ($repeatInterval * $terminateAfterOccurrence) . ' years', $timestamp1);
			break;
		}
		return strtotime('+' + $length . ' days', $timestamp1);
	}
	protected static function getTerminationByWeekday($timestamp1, $lastRepeatWeekday,  $terminateAfterOccurrence){
		$weekday = (int)date('N', $timestamp1);
		$timestamp2 = strtotime('+' . $terminateAfterOccurrence . ' weeks', $timestamp1);
		if($weekday !== $lastRepeatWeekday){
			$diff = abs($weekday - $lastRepeatWeekday);
			$timestamp2 = $weekday > $lastRepeatWeekday ?  strtotime('+' . $diff . ' days', $timestamp2) : strtotime('-' . $diff . ' days', $timestamp2);
		}
		return $timestamp2;
	}
	public static function hasTerminated($availability, $selectedDate){
		if($availability->terminateMode === 0/*NEVER*/){
			return false;
		}
		$terminationDate = self::getTerminationDate($availability);
		if($terminationDate < $selectedDate){
			return true;
		}
		return false;
	}
	protected static function repeatWeekly($startDate, $endDate, $repeatWeekdayList, $repeatInterval){
		$diff = self::getDiff($startDate, $endDate, 'week');
		if($diff && ($diff % $repeatInterval) === 0){
			$weekday = (int)date('N', $endDate);
			return in_array($weekday, $repeatWeekdayList);
		}
		return false;
	}
	protected static function getWeeklyStartDate($startDate, $repeatWeekdayList, $repeatInterval){
		$endDate = strtotime('now');
		while(!self::repeatWeekly($startDate, $endDate, $repeatWeekdayList, $repeatInterval)){
			$endDate = strtotime('+1 day', $endDate);
		}
		return $endDate;
	}
	protected static function getDiffByRepeatFrequency($timestamp1, $timestamp2, $availability){
		$diff = 0;
		switch($availability->repeatFrequency){
			case 1: //DAILY
			$diff = self::getDiff($timestamp1, $timestamp2, 'day');
			break;
			case 2: //EVERY_WEEK_DAY
			case 3: //EVERY_MONDAY_WEDNESDAY_FRIDAY
			case 4: //EVERY_TUESDAY_THURSDAY
			case 5: //WEEKLY
			$diff = self::getDiff($timestamp1, $timestamp2, 'week');
			break;
			case 6: //MONTHLY
			case 7: //YEARLY
			$diff = self::getDiff($timestamp1, $timestamp2, $availability->repeatFrequency === 6 ? 'month' : 'year');
			break;
		}
		return $diff;
	}

	public static function getDateRange($availability){
		$length = $availability->daysInPackage - 1;
		$startDate = strtotime('now');
		$availableDate = strtotime($availability->availableDate->format(CALENDARISTA_DATEFORMAT));
		if($startDate < $availableDate){
			$startDate = $availableDate;
		}
		if(date(CALENDARISTA_DATEFORMAT, $startDate) !== date(CALENDARISTA_DATEFORMAT, $availableDate) && $availability->hasRepeat){
			$weekday = (int)date('N', $startDate);
			$diff = self::getDiffByRepeatFrequency($availableDate, $startDate, $availability);
			$repeat = self::repeat($availability, $diff);
			if(!$repeat || self::hasTerminated($availability, $startDate)){
				return array();
			}
			switch($availability->repeatFrequency){
				//DAILY
				//case 1:
				//break;
				case 2: //EVERY_WEEK_DAY
					if($weekday === 6){
						$startDate = strtotime('+2 days', $startDate);
					}else if($weekday === 7){
						$startDate = strtotime('+1 day', $startDate);
					}
				case 3: //EVERY_MONDAY_WEDNESDAY_FRIDAY
					if(in_array($weekday, array(2, 4, 7))){
						$startDate = strtotime('+1 day', $startDate);
					}else if($weekday === 6){
						$startDate = strtotime('+2 days', $startDate);
					}
				case 4: //EVERY_TUESDAY_THURSDAY
					if(in_array($weekday, array(1, 3, 7))){
						$startDate = strtotime('+1 day', $startDate);
					}else if($weekday === 5){
						$startDate = strtotime('+3 days', $startDate);
					}else if($weekday === 6){
						$startDate = strtotime('+2 days', $startDate);
					}
				case 5: //WEEKLY
					$startDate = self::getWeeklyStartDate($availableDate, $availability->repeatWeekdayList, $availability->repeatInterval);
				break;
				case 6: //MONTHLY
				case 7: //YEARLY
					$startDate = self::getNextMonthlyRepetition($availableDate, $availability->repeatFrequency === 6 ? $diff : ($diff*12));
				break;
			}
		}
		if(self::hasTerminated($availability, $startDate)){
			return array();
		}
		$endDate = strtotime('+' . $length . ' day', $startDate);
		if(self::hasTerminated($availability, $endDate)){
			return array();
		}
		return array('startDate'=>$startDate, 'endDate'=>$endDate);
	}
	protected static function repeat($availability, $diff){
		return ($diff %  $availability->repeatInterval) === 0;
	}
	public static function getDiff($timestamp1, $timestamp2, $durationName = 'month', $duration = 1)
	{
		$i = 0;
		while (($timestamp1 = strtotime('+' . $duration . ' ' . $durationName, $timestamp1)) <= $timestamp2){
			$i++;
		}
		return $i;
	}
	protected static function getNextMonthlyRepetition($date = null, $months = 1)
	{
		if (is_null($date)){
			$date = time();
		}
		$dateAhead = strtotime('+' . $months . ' months', $date);
		$monthBefore = (int)date('m', $date) + 12 * (int)date('Y', $date);
		$monthAfter = (int)date('m', $dateAhead) + 12 * (int)date('Y', $dateAhead);
		if ($monthAfter > $months + $monthBefore){
			$dateAhead = strtotime(date('Ym01His', $dateAhead) . ' -1 day');
		}
		return $dateAhead;
	}
	
	public static function getCurrentMonthExclusions($startDate, $availability){
		$lastDayOfMonth = strtotime(date('Y-m-t', $startDate));
		$result = self::getHolidays($startDate, $lastDayOfMonth, $availability->id);
		while ($startDate <= $lastDayOfMonth) {
			$currentDate = date(CALENDARISTA_DATEFORMAT, $startDate);
			if(!in_array($currentDate, $result)){
				$valid = self::hasAvailability($currentDate, $availability);
				if(!$valid){
					array_push($result, $currentDate);
				}
			}
			$startDate = strtotime('+1 day', $startDate);
		}
		return $result;
	}
	public static function getHolidays($startDate, $endDate, $availabilityId){
		$repo = new Calendarista_HolidaysRepository();
		$holidays = $repo->readByDateRange(date(CALENDARISTA_DATEFORMAT, $startDate), date(CALENDARISTA_DATEFORMAT, $endDate), $availabilityId);
		$result = array();
		foreach($holidays as $holiday){
			array_push($result, $holiday['holiday']);
		}
		return $result;
	}
}
?>