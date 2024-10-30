<?php
class Calendarista_AppointmentHelper{
	public static function getAppointmentViewState($bookedAvailabilityId, $orderId, $availabilityId = null){
		$orderRepo = new Calendarista_OrderRepository();
		$order = $orderRepo->read($orderId);
		if(!$availabilityId && $order){
			$availabilityId = $order->availabilityId;
		}
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
		$dynamicFieldRepo = new Calendarista_BookedDynamicFieldRepository();
		$dynamicFields = $dynamicFieldRepo->readByOrderId($orderId);
		$bookedMapRepo = new Calendarista_BookedMapRepository();
		$map = $bookedMapRepo->readByOrderId($orderId);
		$bookedWaypointRepo = new Calendarista_BookedWaypointRepository();
		$waypoints = $bookedWaypointRepo->readByOrderId($orderId);
		$bookedOptionalRepo = new Calendarista_BookedOptionalRepository();
		$optionals = $bookedOptionalRepo->readAll($orderId);
		$bookedFormElementRepo = new Calendarista_BookedFormElementRepository();
		$bookedFormFields = $bookedFormElementRepo->readAll($orderId);
		$orderAvailabilityRepo = new Calendarista_OrderAvailabilityRepository();
		$orderAvailabilityList = $orderAvailabilityRepo->readAll($orderId);
		$availabilities = array();
		$salesInfoRequest = true;//isset($_POST['salesInfoRequest']) ? (int)$_POST['salesInfoRequest'] : false;
		if($salesInfoRequest){
			if($orderAvailabilityList && count($orderAvailabilityList) > 0){
				foreach($orderAvailabilityList as $oal){
					array_push($availabilities, $oal['availabilityId']);
				}
			}
		}
		$enableMultipleBooking = count($availabilities) > 0;
		if($enableMultipleBooking){
			if($order && ($availabilityId != $order->availabilityId && !in_array($order->availabilityId, $availabilities))){
				array_push($availabilities, $order->availabilityId);
			}
		}
		
		$result = array();
		if(!($bookedAvailabilityList && count($bookedAvailabilityList) > 0)){
			return $result;
		}
		$bookedAvailability = $bookedAvailabilityList[0];
		foreach($bookedAvailabilityList as $bal){
			if((int)$bal->id === $bookedAvailabilityId){
				$bookedAvailability = $bal;
				break;
			}
		}
		$repeatAppointmentDates = array();
		$repeatWeekdayList = null;//$order->repeatWeekdayList;
		$repeatFrequency = 0;//$order->repeatFrequency;
		$repeatInterval = 0;//$order->repeatInterval;
		$terminateAfterOccurrence = 8;//$order->terminateAfterOccurrence;
		if($salesInfoRequest){
			$timeslotsRepo = new Calendarista_TimeslotRepository();
			foreach($bookedAvailabilityList as $ba){
				if($ba->repeated){
					$repeatDate = date(CALENDARISTA_DATEFORMAT, strtotime($ba->fromDate));
					if((int)$ba->startTimeId !== -1){
						$startTimeslot = $timeslotsRepo->read($ba->startTimeId);
						$repeatDate = sprintf('%s %s', date(CALENDARISTA_DATEFORMAT, strtotime($ba->fromDate)), $startTimeslot->timeslot);
						if((int)$ba->endTimeId !== -1){
							$endTimeslot = $timeslotsRepo->read($ba->endTimeId);
							$repeatDate .= ' - ' . $endTimeslot->timeslot;
						}
					}
					array_push($repeatAppointmentDates, $repeatDate);
				}
			}
		}
		$multiDateSelection = $salesInfoRequest ? self::getMultiDateSelection($bookedAvailabilityList) : '';
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availability = $availabilityRepo->read($bookedAvailability->availabilityId);
		$endDate = null;
		if(self::hasEndDate($bookedAvailability->fromDate, $bookedAvailability->toDate, $availability)){
			$endDate = date(CALENDARISTA_DATEFORMAT, strtotime($bookedAvailability->toDate));
		}
		$result[0] = array('projectId'=>$order->projectId, 'orderId'=>$orderId);
		$result[1] = array(
			'availabilityId'=>(int)$bookedAvailability->availabilityId
			, 'availableDate'=>date(CALENDARISTA_DATEFORMAT, strtotime($bookedAvailability->fromDate))
			, 'endDate'=>$endDate
			, '_availabilityId'=>(int)$bookedAvailability->availabilityId
			, '_availableDate'=>$bookedAvailability->fromDate
			, '_endDate'=>$bookedAvailability->toDate
			, 'startTimeslot'=>$bookedAvailability->startTimeId
			, 'endTimeslot'=>$bookedAvailability->endTimeId
			, 'startTime'=>null
			, 'endTime'=>null
			, 'seats'=>(int)$bookedAvailability->seats
			, 'timezone'=>$bookedAvailability->timezone
			, 'multiDateSelection'=>$multiDateSelection
			, 'dynamicFields'=>self::getDynamicFields($dynamicFields)
			, 'availabilities'=>implode(',', $availabilities)
			, 'enableMultipleBooking'=>$enableMultipleBooking
			, 'repeatAppointment'=>count($repeatAppointmentDates) > 0
			, 'repeatWeekdayList'=>$repeatWeekdayList
			, 'repeatFrequency'=>$repeatFrequency
			, 'repeatInterval'=>$repeatInterval
			, 'terminateAfterOccurrence'=>$terminateAfterOccurrence
		);
		$result[1] = self::fillStartEndTime($result[1], $bookedAvailability);
		if($map){
			$result[2] = array(
				'fromAddress'=>$map->fromAddress
				, 'fromLat'=>$map->fromLat
				, 'fromLng'=>$map->fromLng
				, 'toAddress'=>$map->toAddress
				, 'toLat'=>$map->toLat
				, 'toLng'=>$map->toLng
				, 'distance'=>(float)$map->distance
				, 'duration'=>(float)$map->duration
				, 'unitType'=>(int)$map->unitType
				, 'waypoints'=>$waypoints ? $waypoints : array()
				, 'fromPlaceId'=>$map->fromPlaceId
				, 'toPlaceId'=>$map->toPlaceId
			);
		}
		if($optionals){
			$listOfOptionals = array();
			foreach($optionals as $o){
				$listOfOptionals = array_merge($listOfOptionals, $o);
			}
			$result[3] = array('optionals'=>self::getOptionalIdList($listOfOptionals), 'optional_incremental'=>self::getOptionalIncrement($listOfOptionals));
		}
		$formElements = self::parseFormElements((int)$bookedAvailability->seats, $bookedFormFields);
		$result[4] = array(
			'formelements'=>$formElements
			, 'userId'=>$order->userId
			, 'name'=>$order->fullName
			, 'email'=>$order->email
		);
		$result[5] = array('status'=>(int)$bookedAvailability->status);
		$result[6] = array('discount'=>$order->discount, 'discountMode'=>$order->discountMode);
		$result[7] = array('invoiceId'=>$order->invoiceId);
		$result[8] = array('repeatAppointmentDates'=>implode(',', $repeatAppointmentDates));
		$result[9] = array('tax'=>$order->tax);
		$result[10] = array('taxMode'=>$order->taxMode);
		$result[11] = array('paymentOperator'=>$order->paymentDate ? $order->paymentOperator : null);
		$result[12] = array('upfrontPayment'=>$order->upfrontPayment);
		return $result;
	}
	public static function parseFormElements($seats, $bookedFormFields){
		$result = null;
		foreach($bookedFormFields as $formField){
			if((int)$formField->elementId === -1 && (int)$formField->guestIndex > ($seats - 1)){
				//toDO: deprecate this check in future editions.
				continue;
			}
			if(!$result){
				$result = array();
			}
			array_push($result, array(
				'projectId'=>(int)$formField->projectId
				, 'elementId'=>(int)$formField->elementId
				, 'orderIndex'=>(int)$formField->orderIndex
				, 'value'=>$formField->value
				, 'label'=>$formField->label
				, 'guestIndex'=>(int)$formField->guestIndex
			));
		}
		return $result;
	}
	public static function getMultiDateSelection($bookedAvailabilityList){
		$result = array();
		if($bookedAvailabilityList && (count($bookedAvailabilityList) > 0 && in_array((int)$bookedAvailabilityList[0]->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_DATE))){
			foreach($bookedAvailabilityList as $bookedAvailability){
				$fromDate = date(CALENDARISTA_DATEFORMAT, strtotime($bookedAvailability->fromDate));
				if((int)$bookedAvailability->calendarMode === Calendarista_CalendarMode::MULTI_DATE){
					array_push($result, $fromDate);
				}else{
					array_push($result, sprintf('%s:%s', $fromDate, $bookedAvailability->startTimeId));
				}
			}
		}
		return implode(';', $result);
	}
	protected static function hasEndDate($fromDate, $toDate, $availability){
		if($availability && ($availability->returnOptional && $fromDate === $toDate)){
			return false;
		}
		return true;
	}
	public static function fillStartEndTime($args, $bookedAvailability){
		if(!isset($bookedAvailability->startTimeId)){
			return $args;
		}
		//since you can technically regenerate timeslots, get timeslot id from timeslots table, query by time
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$fd = strtotime($bookedAvailability->fromDate);
		$args['startTimeslot'] = date('g:i a', $fd);
		$fromDate = date(CALENDARISTA_DATEFORMAT, $fd);
		$fromWeekday = (int)date('N', $fd);
		$startTimeslot = $timeslotRepo->readByTimeslot((int)$bookedAvailability->availabilityId, $fromDate, $fromWeekday, $args['startTimeslot'], false/*returnTrip*/);
		if($startTimeslot){
			$args['startTime'] = (int)$startTimeslot->id;
		}
		if(isset($bookedAvailability->endTimeId)){
			$td = strtotime($bookedAvailability->toDate);
			$args['endTimeslot'] = date('g:i a', $td);
			$toDate = date(CALENDARISTA_DATEFORMAT, $td);
			$toWeekday = (int)date('N', $td);
			$returnTrip = $timeslotRepo->availabilityHasReturnTrip((int)$bookedAvailability->availabilityId);
			$endTimeslot = $timeslotRepo->readByTimeslot((int)$bookedAvailability->availabilityId, $toDate, $toWeekday, $args['endTimeslot'], $returnTrip);
			if($endTimeslot){
				$args['endTime'] = (int)$endTimeslot->id;
			}
		}
		return $args;
	}
	public static function getOptionalIdList($optionals){
		$result = array();
		foreach($optionals as $optional){
			if((int)$optional->displayMode === Calendarista_OptionalDisplayMode::INCREMENTAL_INPUT){
				continue;
			}
			array_push($result, (int)$optional->optionalId);
		}
		return implode(',', $result);
	}
	public static function getOptionalIncrement($optionals){
		$result = array();
		foreach($optionals as $optional){
			if((int)$optional->displayMode !== Calendarista_OptionalDisplayMode::INCREMENTAL_INPUT){
				continue;
			}
			array_push($result, sprintf('%s:%s', $optional->optionalId, $optional->incrementValue));
		}
		return implode(',', $result);
	}
	public static function convertFormFieldObjectToArray($formField){
		return array(
			'projectId'=>(int)$formField->projectId
			, 'elementId'=>(int)$formField->elementId
			, 'orderIndex'=>(int)$formField->orderIndex
			, 'value'=>$formField->value
			, 'label'=>$formField->label
			, 'guestIndex'=>(int)$formField->guestIndex
		);
	}
	public static function getDynamicFields($items){
		$fields = array();
		if(is_array($items) && count($items) > 0){
			foreach($items as $item){
				array_push($fields, array(
					'id'=>(int)$item['dynamicFieldId']
					, 'value'=>(int)$item['value']
					, 'label'=>$item['label']
					, 'cost'=>$item['cost']
					, 'limitBySeat'=>$item['limitBySeat']
					, 'byOptional'=>$item['byOptional']
					, 'fixedCost'=>$item['fixedCost']
				));
			}
		}
		return $fields;
	}
    private function __construct() { }
}
?>