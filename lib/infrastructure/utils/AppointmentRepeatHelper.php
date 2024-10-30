<?php
class Calendarista_AppointmentRepeatHelper{
	public static function dateRepeats($startDate, $repeatingDate, $args){
		$availability = $args['availability'];
		$repeatFrequency = $args['repeatFrequency'];
		$repeatInterval = $args['repeatInterval'];
		$repeatWeekdayList = isset($args['repeatWeekdayList']) ? $args['repeatWeekdayList'] : null;
		$terminateAfterOccurrence = $args['terminateAfterOccurrence'];
		//not taking time into account for date,
		//however hasTerminated does, fix it.
		if(!$startDate){
			return false;
		}
		$availableDays = Calendarista_AvailabilityDayHelper::get($availability->id);
		if(count($availableDays) > 0){
			array_push($availableDays, $startDate);
			if(in_array(date('Y-m-d', strtotime($repeatingDate)), $availableDays)){
				return true;
			}
			if(in_array($repeatFrequency, array(6/*Monthly*/, 7/*Yearly*/))){
				$currentMonthDay = date('m-d', strtotime($repeatingDate));
				$currentDay = date('d', strtotime($repeatingDate));
				foreach($availableDays as $individualDay){
					if($currentDay == date('d', strtotime($individualDay)) && $repeatFrequency === 6/*Monthly*/){
						return true;
					}else if($currentMonthDay == date('m-d', strtotime($individualDay))){
						return true;
					}
				}
			}
			return false;
		}
		$timestamp1 = strtotime($startDate);
		$timestamp2 = strtotime($repeatingDate);
		$weekday = (int)date('N', $timestamp2);
		/*1 (per Lunedì) a 7 (per Domenica)*/
		if($timestamp1 === $timestamp2){
			return true;
		} else if($timestamp2 > $timestamp1){
			$diff = self::getDiffByRepeatFrequency($timestamp1, $timestamp2, $repeatFrequency);
			$repeat = self::repeat($repeatInterval, $diff);
			if(!$repeat || self::hasTerminated($timestamp2, $args)){
				return false;
			}
			switch($repeatFrequency){
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
				if(in_array($weekday, $repeatWeekdayList)){
					return true;
				}
				break;
				case 6: //MONTHLY
				case 7: //YEARLY
				$timestamp3 = self::getNextMonthlyRepetition($timestamp1, $repeatFrequency === 6 ? $diff : ($diff*12));
				if($timestamp2 === $timestamp3){
					return true;
				}
				break;
			}
		}
		return false;
	}
	public static function getTerminationDate($timestamp1, $args){
		$availability = $args['availability'];
		$repeatFrequency = $args['repeatFrequency'];
		$repeatInterval = $args['repeatInterval'];
		$repeatWeekdayList = isset($args['repeatWeekdayList']) ? $args['repeatWeekdayList'] : null;
		$terminateAfterOccurrence = $args['terminateAfterOccurrence'];
		$length = $availability->daysInPackage - 1;
		$availableDays = Calendarista_AvailabilityDayHelper::get($availability->id);
		if(count($availableDays) > 0){
			if(in_array($availability->repeatFrequency, array(6/*Monthly*/, 7/*Yearly*/))){
				array_push($availableDays, date(CALENDARISTA_DATEFORMAT, $timestamp1));
				$timestamp1 = strtotime($availableDays[0]);
			}
		}
		/*1 (per Lunedì) a 7 (per Domenica)*/
		switch($repeatFrequency){
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
	public static function hasTerminated($startDate, $args){
		$availability = $args['availability'];
		$repeatFrequency = $args['repeatFrequency'];
		$repeatInterval = $args['repeatInterval'];
		$repeatWeekdayList = isset($args['repeatWeekdayList']) ? $args['repeatWeekdayList'] : null;
		$terminateAfterOccurrence = $args['terminateAfterOccurrence'];
		
		$terminationDate = self::getTerminationDate($startDate, $args);
		if($terminationDate < $startDate){
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
	protected static function getDiffByRepeatFrequency($timestamp1, $timestamp2, $repeatFrequency){
		$diff = 0;
		switch($repeatFrequency){
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
			$diff = self::getDiff($timestamp1, $timestamp2, $repeatFrequency === 6 ? 'month' : 'year');
			break;
		}
		return $diff;
	}

	public static function getDateRange($args){
		$availableDate = $args['availableDate'];
		$startDate = $args['startDate'];
		$availability = $args['availability'];
		$repeatFrequency = $args['repeatFrequency'];
		$repeatInterval = $args['repeatInterval'];
		$repeatWeekdayList = isset($args['repeatWeekdayList']) ? $args['repeatWeekdayList'] : null;
		$terminateAfterOccurrence = $args['terminateAfterOccurrence'];
		
		$length = $availability->daysInPackage - 1;
		$startDate = strtotime($startDate);
		$availableDate = strtotime($availableDate);
		if(date(CALENDARISTA_DATEFORMAT, $startDate) !== date(CALENDARISTA_DATEFORMAT, $availableDate)){
			$weekday = (int)date('N', $startDate);
			$diff = self::getDiffByRepeatFrequency($availableDate, $startDate, $repeatFrequency);
			$repeat = self::repeat($repeatInterval, $diff);
			if(!$repeat || self::hasTerminated($startDate, $args)){
				return array();
			}
			switch($repeatFrequency){
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
					$startDate = self::getWeeklyStartDate($availableDate, $repeatWeekdayList, $repeatInterval);
				break;
				case 6: //MONTHLY
				case 7: //YEARLY
					$startDate = self::getNextMonthlyRepetition($availableDate, $repeatFrequency === 6 ? $diff : ($diff*12));
				break;
			}
		}
		if(self::hasTerminated($startDate, $args)){
			return array();
		}
		$endDate = strtotime('+' . $length . ' day', $startDate);
		if(self::hasTerminated($endDate, $args)){
			return array();
		}
		return array('startDate'=>$startDate, 'endDate'=>$endDate);
	}
	protected static function repeat($repeatInterval, $diff){
		return ($diff %  $repeatInterval) === 0;
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
}
?>