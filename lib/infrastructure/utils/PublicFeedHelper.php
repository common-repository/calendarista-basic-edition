<?php
class Calendarista_PublicFeedHelper{
	private function __construct() { }
	public static function getBookedAvailabilities($args){
		$bookedAvailabilityRepository = new Calendarista_BookedAvailabilityRepository();
		$formElementsRepo = new Calendarista_BookedFormElementRepository();
        $items = $bookedAvailabilityRepository->readAll($args);
		$result = array();
		foreach((array)$items['resultset'] as $item){
			$dateFormat = CALENDARISTA_FULL_DATEFORMAT;
			$friendlyFormat = CALENDARISTA_FULL_DATEFORMAT;
			$calendarMode = (int)$item['calendarMode'];
			$formElements = $formElementsRepo->readByElements((int)$item['orderId'], $formElementList);
			if(!in_array($calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
				$dateFormat = CALENDARISTA_DATEFORMAT;
				$friendlyFormat = CALENDARISTA_DATEFORMAT;
			}
			$ff = new Calendarista_DateTime($item['fromDate']);
			$fromDateFormatted = $ff->format($dateFormat);
			$toDateFormatted = null;
			if($item['toDate']){
				//fullcalendar does not span the last date in a range
				//and considers the last date, the date the event ends
				//hence we workaround by adding an extra day.
				//this affects only the view in fullcalendar
				$fromDate = new Calendarista_DateTime($item['fromDate']);
				$toDate = new Calendarista_DateTime($item['toDate']);
				//skip round trips, these are treated as single days and not a range
				//skip time based appointments as well. these span correctly.
				if(!in_array($calendarMode, Calendarista_CalendarMode::$SUPPORTS_ROUND_TRIP) &&
					!in_array($calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
					if($fromDate->format('Y-m-d') != $toDate->format('Y-m-d')){
						$toDate->modify('+1 day');
					}
				}
				$toDateFormatted = $toDate->format($dateFormat);
			}
			$params = array();
			if(in_array((int)$item['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_ROUND_TRIP)){
				//round trips aren't a range but rather separate dates with a start and end (dont care for dates in between)
				array_push($params, array('fromDateFormatted'=>$fromDateFormatted, 'toDateFormatted'=>null));
				if($toDateFormatted){
					array_push($params, array('fromDateFormatted'=>$toDateFormatted, 'toDateFormatted'=>null));
				}
			}else{
				array_push($params, array('fromDateFormatted'=>$fromDateFormatted, 'toDateFormatted'=>$toDateFormatted));
			}
			foreach($params as $p){
				array_push($result, self::getCalendarData(
					$item, 
					$friendlyFormat, 
					$p['fromDateFormatted'], 
					$p['toDateFormatted'], 
					$formElements, 
					$args['includeNameField'], 
					$args['includeEmailField'], 
					$args['includeAvailabilityNameField'], 
					$args['includeSeats']
				));
			}
		}
		if($returnList){
			//means we are serving a list
			return array('resultset'=>$result, 'total'=>$items['total']);
		}
		return $result;
	}
	protected static function getCalendarData($item, $dateFormat, $fromDateFormatted, $toDateFormatted, $formElements, $includeNameField, $includeEmailField, $includeAvailabilityNameField, $includeSeats){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$statusIndicator = $generalSetting->pendingApprovalColor;
		$statusLabel = sprintf('<span style="color:%s">%s</span>', $generalSetting->pendingApprovalColor, __('Pending approval', 'calendarista'));
		if((int)$item['status'] === Calendarista_AvailabilityStatus::APPROVED){
			$statusIndicator = $generalSetting->approvedColor;
			$statusLabel = sprintf('<span style="color:%s">%s</span>', $generalSetting->approvedColor, __('Approved', 'calendarista'));
		}else if((int)$item['status'] === Calendarista_AvailabilityStatus::CANCELLED){
			$statusIndicator = $generalSetting->cancelledColor;
			$statusLabel = sprintf('<span style="color:%s">%s</span>', $generalSetting->cancelledColor, __('Cancelled', 'calendarista'));
		}
		$color = $item['color'] ? $item['color'] : '';
		$textColor = '#000';
		$title = '';
		if((int)$item['status'] === 2){
			$color = '#f8d7da';
			$textColor = '#721c24';
		}
		$formFields = self::getFormFieldValue($formElements);
		$rawTitle = null;
		if($includeNameField){
			$rawTitle .= $item['fullName'];
		}
		if($includeEmailField){
			$rawTitle .= $rawTitle ? (' - ' . $item['email']) : $item['email'];
		}
		if($includeAvailabilityNameField){
			$rawTitle .= $rawTitle ? (' - ' . $item['availabilityName']) : $item['availabilityName'];
		}
		if($includeSeats){
			$seats = sprintf('%s: %s', __('seats', 'calendarista'), $item['seats']);
			$rawTitle .= $rawTitle ? (' - ' . $seats) : $seats;
		}
		if($formFields){
			$rawTitle .= $rawTitle ?  (' - ' . $formFields) : $formFields;
		}else if(!$rawTitle){
			$rawTitle =  $item['fullName'];
		}
		$title .= sprintf('<span class="calendarista-fc-title-heading">%s</span>', $rawTitle);
		$appointmentDate = array();
		if($fromDateFormatted){
			array_push($appointmentDate, 
				sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>'
				, __('BEGIN', 'calendarista')
				, date($dateFormat, strtotime($item['fromDate'])))
			);
		}
		if($toDateFormatted){
			array_push($appointmentDate, 
				sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>'
					, __('END', 'calendarista')
					, date($dateFormat, strtotime($item['toDate'])))
				);
		}
		return array(
			'id'=>$item['id']
			, 'title'=>$title
			, 'rawTitle'=>$rawTitle
			, 'start'=>$fromDateFormatted
			, 'end'=>$toDateFormatted
			, 'date'=>implode('', $appointmentDate)
			, 'serviceName'=>$item['projectName']
			, 'availabilityName'=>$includeAvailabilityNameField ? $item['availabilityName'] : ''
			, 'name'=>$includeNameField ? $item['fullName'] : ''
			, 'email'=>$includeEmailField ? $item['email'] : ''
			, 'statusLabel'=>$statusLabel
			, 'color'=>$color
			, 'textColor'=>$textColor
			, 'headingfield'=>true
			, 'status'=>(int)$item['status']
		);
	}
	public static function getFormFieldValue($formElements){
		$result = array();
		if($formElements){
			foreach($formElements as $formElement){
				if(!$formElement->value){
					continue;
				}
				array_push($result, $formElement->value);
			}
		}
		if(count($result) > 0){
			return join(' - ', $result);
		}
		return null;
	}
}
?>