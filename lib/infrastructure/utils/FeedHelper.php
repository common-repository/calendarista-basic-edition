<?php
class Calendarista_FeedHelper{
	private function __construct() { }
	public static function getBookedAvailabilities($args){
		$returnList = isset($args['returnList']) ? $args['returnList'] : null;
		$staffMemberAvailabilities = Calendarista_PermissionHelper::staffMemberAvailabilities();
		$bookedAvailabilityRepository = new Calendarista_BookedAvailabilityRepository();
        $items = $bookedAvailabilityRepository->readAll(array(
			'fromDate'=>$args['start']
			, 'toDate'=>$args['end']
			, 'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'availabilities'=>$staffMemberAvailabilities
			, 'syncDataFilter'=>$args['syncDataFilter']
			, 'invoiceId'=>$args['invoiceId']
			, 'pageIndex'=>$args['pageIndex']
			, 'limit'=>$args['limit']
			, 'orderBy'=>$args['orderBy']
			, 'order'=>$args['order']
			, 'customerName'=>$args['customerName']
			, 'email'=>$args['email']
			, 'invoiceId'=>$args['invoiceId']
			, 'status2'=>$args['status']
		));
		$result = array();
		foreach((array)$items['resultset'] as $item){
			$dateFormat = CALENDARISTA_FULL_DATEFORMAT;
			$friendlyFormat = CALENDARISTA_FULL_DATEFORMAT;
			$calendarMode = (int)$item['calendarMode'];
			$synched = !empty($item['synchedBookingId']) && (int)$item['synchedMode'] !== Calendarista_SynchedMode::GCAL_EXPORTED;
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
				array_push($params, array('fromDateFormatted'=>$fromDateFormatted, 'toDateFormatted'=>null, 'pos'=>1/*start*/));
				if($item['fromDate'] !== $item['toDate'] && $toDateFormatted){
					array_push($params, array('fromDateFormatted'=>$toDateFormatted, 'toDateFormatted'=>null, 'pos'=>2/*end*/));
				}
			}else{
				array_push($params, array('fromDateFormatted'=>$fromDateFormatted, 'toDateFormatted'=>$toDateFormatted, 'pos'=>null));
			}
			foreach($params as $p){
				if($returnList){
					array_push($result, self::getListData($item, $friendlyFormat, $p['fromDateFormatted'], $p['toDateFormatted'], $p['pos'], $synched));
				}else{
					array_push($result, self::getCalendarData($item, $friendlyFormat, $p['fromDateFormatted'], $p['toDateFormatted'], $p['pos'], $synched));
				}
			}
		}
		if($returnList){
			//means we are serving a list
			return array('resultset'=>$result, 'total'=>$items['total']);
		}
		return $result;
	}
	protected static function getCalendarData($item, $dateFormat, $fromDateFormatted, $toDateFormatted, $pos, $synched){
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
		//add a filter by availability and not just project(more is more).
		$list = array();
		if(!$pos){
			//regular flow
			if($fromDateFormatted && $toDateFormatted){
				array_push($list, 
					sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>'
					, __('BEGIN', 'calendarista')
					, date($dateFormat, strtotime($item['fromDate'])))
				);
			}
			if($toDateFormatted){
				array_push($list, 
					sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>'
						, __('END', 'calendarista')
						, date($dateFormat, strtotime($item['toDate'])))
					);
			}
		}else{
			//we have round trips, treat differently
			array_push($list, 
				sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>'
				, $pos === 1 ? __('DEPART', 'calendarista') : __('RETURN', 'calendarista')
				, date($dateFormat, $pos === 1 ? strtotime($item['fromDate']) : strtotime($item['toDate'])))
			);
		}
		if($synched){
			array_push($list, 
				sprintf('<span class="calendarista-fc-title-item">%s</span><br>'
				, str_replace("\\n","<br />", $item['synchedBookingDescription']))
			);
			if(!empty($item['synchedBookingLocation'])){
				array_push($list, 
					sprintf('<span class="calendarista-fc-title-item">%s</span><br>'
					, $item['synchedBookingLocation'])
				);
			}
			array_push($list, 
				sprintf('<span class="calendarista-fc-title-item" style="border-bottom: 1px dashed #000">%s:</span><br>'
				, __('BOOKING SYNCED WITH', 'calendarista'))
			);
			
		}else{
			array_push($list, sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>', __('NAME', 'calendarista'), $item['fullName']));
			array_push($list, sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>', __('EMAIL', 'calendarista'), $item['email']));
			if($item['seats']){
				array_push($list, 
					sprintf('<span class="calendarista-fc-title-item">%s: %d</span><br>'
					, __('SEATS', 'calendarista')
					, (int)$item['seats'])
				);
			}
		}
		array_push($list, sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>', __('SERVICE', 'calendarista'), $item['projectName']));
		array_push($list, sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>',  __('AVAIL', 'calendarista'), $item['availabilityName']));
		array_push($list, sprintf('<span class="calendarista-fc-title-item">%s: %s</span>',  __('STATUS', 'calendarista'), $statusLabel));
		$color = $item['color'] ? $item['color'] : '';
		$textColor = '#000';
		$title = '';
		if((int)$item['status'] === 2){
			$color = '#f8d7da';
			$textColor = '#721c24';
		}
		if($synched){
			$color = '#e2e3e5';
			$textColor = '#000';
		}
		$titleText = apply_filters('calendarista_feed_title', self::getTitle($item, $synched), $item, $synched);
		$rawTitleText = apply_filters('calendarista_raw_feed_title', self::getRawTitle($item, $synched), $item, $synched);
		$title .= sprintf('<i class="calendarista-more-info fa fa-address-card fa-lg" style="color: %s"></i>&nbsp;<span class="calendarista-fc-title-heading">%s</span>'
			, $statusIndicator
			, $titleText
		);
		
		return array(
			'id'=>$item['id']
			, 'bookedAvailabilityId'=>$item['id']
			, 'title'=>$title
			, 'description'=>sprintf('<span class="calendarista-more">%s</span>', trim(implode('', $list)))
			, 'rawTitle'=>$rawTitleText
			, 'rawDescription'=>trim(implode('', $list))
			, 'start'=>$fromDateFormatted
			, 'end'=>$toDateFormatted
			, 'color'=>$color
			, 'textColor'=>$textColor
			, 'headingfield'=>true
			, 'orderId'=>empty($item['orderId']) ? null : $item['orderId']
			, 'projectId'=>(int)$item['projectId']
			, 'availabilityId'=>(int)$item['availabilityId']
			, 'synched'=>$synched
			, 'synchedBookingId'=>$item['synchedBookingId']
			, 'status'=>(int)$item['status']
		);
	}
	protected static function getListData($item, $dateFormat, $fromDateFormatted, $toDateFormatted, $pos, $synched){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$statusIndicator = $generalSetting->pendingApprovalColor;
		$statusLabel = sprintf('<span style="color:%s">%s</span>', $generalSetting->pendingApprovalColor, __('Pending', 'calendarista'));
		if((int)$item['status'] === Calendarista_AvailabilityStatus::APPROVED){
			$statusIndicator = $generalSetting->approvedColor;
			$statusLabel = sprintf('<span style="color:%s">%s</span>', $generalSetting->approvedColor, __('Approved', 'calendarista'));
		}else if((int)$item['status'] === Calendarista_AvailabilityStatus::CANCELLED){
			$statusIndicator = $generalSetting->cancelledColor;
			$statusLabel = sprintf('<span style="color:%s">%s</span>', $generalSetting->cancelledColor, __('Cancelled', 'calendarista'));
		}
		$list = array();
		$appointmentDate = array();
		$name = null;
		$email = null;
		$seats = 1;
		$sync = 0;
		if(!$pos){
			//regular flow
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
		}else{
			//we have round trips, treat differently
			array_push($appointmentDate, 
				sprintf('<span class="calendarista-fc-title-item">%s: %s</span><br>'
				, $pos === 1 ? __('DEPART', 'calendarista') : __('RETURN', 'calendarista')
				, date($dateFormat, $pos === 1 ? strtotime($item['fromDate']) : strtotime($item['toDate'])))
			);
		}
		if($synched){
			$name = $item['synchedBookingDescription'];
			$list = array_merge($list, $appointmentDate);
			array_push($list, 
				sprintf('<span class="calendarista-fc-title-item">%s</span><br>'
				, str_replace("\\n","<br />", $name))
			);
			if(!empty($item['synchedBookingLocation'])){
				array_push($list, 
					sprintf('<span class="calendarista-fc-title-item">%s</span><br>'
					, $item['synchedBookingLocation'])
				);
			}
			array_push($list, 
				sprintf('<span class="calendarista-fc-title-item" style="border-bottom: 1px dashed #000">%s:</span><br>'
				, __('This is a synced appointment', 'calendarista')));
		}else{
			$name = $item['fullName'];
			if($item['seats']){
				$seats = (int)$item['seats'];
			}
		}
		$email = $item['userEmail'];
		$color = $item['color'] ? $item['color'] : '';
		$textColor = '#000';
		$rawTitleText = apply_filters('calendarista_raw_feed_title', self::getRawTitle($item, $synched), $item, $synched);
		if((int)$item['status'] === 2){
			$color = '#f8d7da';
			$textColor = '#721c24';
		}
		if($synched){
			$color = '#e2e3e5';
			$textColor = '#000';
		}
		return array(
			'id'=>$item['id']
			, 'bookedAvailabilityId'=>$item['id']
			, 'orderId'=>(int)$item['orderId']
			, 'seats'=>(int)$item['seats']
			, 'date'=>implode('', $appointmentDate)
			, 'serviceName'=>$item['projectName']
			, 'availabilityName'=>$item['availabilityName']
			, 'name'=>$name
			, 'email'=>$email
			, 'start'=>$fromDateFormatted
			, 'end'=>$toDateFormatted
			, 'color'=>$color
			, 'textColor'=>$textColor
			, 'orderId'=>empty($item['orderId']) ? null : $item['orderId']
			, 'projectId'=>(int)$item['projectId']
			, 'availabilityId'=>(int)$item['availabilityId']
			, 'statusLabel'=>$statusLabel
			, 'status'=>(int)$item['status']
			, 'synched'=>$synched
			, 'synchedBookingId'=>$item['synchedBookingId']
			, 'rawTitle'=>$rawTitleText
			, 'rawDescription'=>base64_encode(trim(implode('', $list)))
			, 'orderDate'=>$item['orderDate'] ? date(CALENDARISTA_DATEFORMAT, strtotime($item['orderDate'])) : ''
		);
	}
	protected static function getTitle($item, $synched){
		if($synched){
			return $item['synchedBookingSummary'];
		}
		return $item['fullName'] . ' (' . self::trimString($item['fullName'], $item['availabilityName']) . ')';
	}
	protected static function getRawTitle($item, $synched){
		if($synched){
			return $item['synchedBookingSummary'];
		}
		return self::trimString('', $item['fullName']);
	}
	public static function trimString($val1, $val2, $maxSize = 256){
		$origSize = strlen($val1) + strlen($val2);
		if($origSize <= $maxSize){
			return $val2;
		}
		return mb_substr($val2, 0, abs(strlen($val2) - ($origSize - $maxSize)), 'utf-8') . '...';
	}
	public static function synchronizeAllFeeds(){
		$feedsRepository = new Calendarista_FeedsRepository();
		$result = $feedsRepository->readAll();
		if($result['items']){
			self::synchFeeds($result['items']);
		}
	}
	public static function synchronize($projectId, $availabilityId){
		$feedsRepository = new Calendarista_FeedsRepository();
		$feeds = $feedsRepository->readByProjectAndAvailability($projectId, $availabilityId);
		if($feeds){
			self::synchFeeds($feeds);
		}
	}
	protected static function synchFeeds($feeds){
		if(!$feeds){
			return false;
		}
		$incomingData = array();
		$synchedData = array();
		foreach($feeds as $feed){
			$projectId = (int)$feed['projectId'];
			$availabilityId = (int)$feed['availabilityId'];
			$projectRepo = new Calendarista_ProjectRepository();
			$project = $projectRepo->read($projectId);
			$availabilityRepo = new Calendarista_AvailabilityRepository();
			$availability = $availabilityRepo->read($availabilityId);
			if(!$availability){
				continue;
			}
			$timeslotRepo = new Calendarista_TimeslotRepository();
			$timeslots = new Calendarista_Timeslots();
			if(in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
				$timeslots = $timeslotRepo->readAllByAvailability($availabilityId);
			}
			$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
			$result = $bookedAvailabilityRepo->readAllSynchedData($availabilityId);
			foreach($result as $sd){
				if(!in_array($sd, $synchedData)){
					array_push($synchedData, $sd);
				}
			}
			//save event dtstart and dtend and check against this instead of using uid
			$today = new Calendarista_DateTime();
			$today->setTime(0, 0);
			$ical = new Calendarista_ICSReaderHelper($feed['feedUrl']);
			$timezone = null;
			if(is_array($ical->cal) && (isset($ical->cal['VCALENDAR']) && isset($ical->cal['VCALENDAR']['X-WR-TIMEZONE']))){
				$timezone = $ical->cal['VCALENDAR']['X-WR-TIMEZONE'];
			}
			$events = $ical->events();
			if(is_array($events)){
				foreach($events as $event){
					$sdt = new Calendarista_DateTime($event['DTSTART']);
					$sdt->setTime(0, 0);
					$edt = null;
					if(isset($event['DTEND'])){
						$edt = new Calendarista_DateTime($event['DTEND']);
						$edt->setTime(0, 0);
					}
					//compare dates without time
					if($edt){
						//if we have an end date then compare agianst it.
						if($edt < $today){
							//exclude all past events
							continue;
						}
					} else if($sdt < $today){
						//exclude all past events
						continue;
					}
					$eventId = sprintf('%s-%s-%d-%d', $event['DTSTART'],  isset($event['DTEND']) ? $event['DTEND'] : '', $project->id, $availability->id);
					if(isset($event['UID'])){
						$eventId = $event['UID'];
					}
					if(!in_array($eventId, $incomingData)){
						array_push($incomingData, $eventId);
					}
					if(!in_array($eventId, $synchedData)){
						self::saveFeed($eventId, $project, $availability, $event, $timeslots, $timeslotRepo, $bookedAvailabilityRepo, $timezone);
						array_push($synchedData, $eventId);
					}else{
						self::updateFeed($eventId, $event, $timeslots, $bookedAvailabilityRepo, $timezone);
					}
				}
			}
		}
		foreach($synchedData as $eventId){
			if(!in_array($eventId, $incomingData)){
				$bookedAvailabilityRepo->deleteSyncedDataById($eventId);
			}
		}
	}
	protected static function updateFeed($eventId, $event, $timeslots, $bookedAvailabilityRepo, $timezone){
		$start = $event['DTSTART'];
		$end = isset($event['DTEND']) ? $event['DTEND'] : $start;
		$originalTimezone = null;
		if($timezone){
			$originalTimezone = Calendarista_TimeHelper::setTimezone($timezone);
		}
		$sdt = new Calendarista_DateTime($start);
		$edt = new Calendarista_DateTime($end);
		if($timezone){
			$dtz = new DateTimeZone($timezone);
			$sdt->setTimezone($dtz);
			$edt->setTimezone($dtz);
		}
		$hasTime = strpos($event['DTSTART'], 'T') !== false;
		$st = $hasTime ? $sdt->format('g:i a') : null;
		$et = $hasTime ? $edt->format('g:i a') : null;
		//filter timeslots, faster exection
		$startTimeslots = Calendarista_TimeslotHelper::filterTimeslots($sdt, $timeslots);
		$endTimeslots = Calendarista_TimeslotHelper::filterTimeslots($edt, $timeslots);
		//now get the invidual timeslots, faster, no sql
		$startTimeslot = self::getTimeslot($st, $startTimeslots);
		$endTimeslot = self::getTimeslot($et, $endTimeslots);
		$bookedAvailabilityRepo->updateSynchedData(array(
			'fromDate'=>$sdt->format(CALENDARISTA_FULL_DATEFORMAT)
			, 'toDate'=>$edt->format(CALENDARISTA_FULL_DATEFORMAT)
			, 'startTimeId'=>$startTimeslot->id !== -1 ? $startTimeslot->id : null
			, 'endTimeId'=>$endTimeslot->id !== -1 ? $endTimeslot->id : null
			, 'synchedBookingId'=>$eventId
			, 'status'=>self::getStatus($event)
			, 'seats'=>isset($event['SEATS']) ? (int)$event['SEATS'] : 1
			, 'synchedBookingDescription'=>isset($event['DESCRIPTION']) ? $event['DESCRIPTION'] : null
			, 'synchedBookingSummary'=>isset($event['SUMMARY']) ? $event['SUMMARY'] : null
			, 'synchedBookingLocation'=>isset($event['LOCATION']) ? $event['LOCATION'] : null
		));
		if($originalTimezone){
			Calendarista_TimeHelper::setTimezone($originalTimezone);
		}
	}
	protected static function saveFeed($eventId, $project, $availability, $event, $timeslots, $timeslotRepo, $bookedAvailabilityRepo, $timezone = null){
		$start = $event['DTSTART'];
		$end =  isset($event['DTEND']) ? $event['DTEND'] : $start;
		$originalTimezone = null;
		if($timezone){
			$originalTimezone = Calendarista_TimeHelper::setTimezone($timezone);
		}
		$sdt = new Calendarista_DateTime($start);
		$edt = new Calendarista_DateTime($end);
		if($timezone){
			$dtz = new DateTimeZone($timezone);
			$sdt->setTimezone($dtz);
			$edt->setTimezone($dtz);
		}
		$hasTime = strpos($event['DTSTART'], 'T') !== false;
		$st = $hasTime ? $sdt->format('g:i a') : null;
		$et = $hasTime ? $edt->format('g:i a') : null;
		//filter timeslots, faster exection
		$startTimeslots = Calendarista_TimeslotHelper::filterTimeslots($sdt, $timeslots);
		$endTimeslots = Calendarista_TimeslotHelper::filterTimeslots($edt, $timeslots);
		//now get the invidual timeslots, faster, no sql
		$startTimeslot = self::getTimeslot($st, $startTimeslots);
		$endTimeslot = self::getTimeslot($et, $endTimeslots);
		
		//before insert, check to see if externalEventId exists in bookedAvailabilityRepo
		$bookedAvailabilityRepo->insert(array(
			'availabilityId'=>$availability->id
			, 'projectId'=>$project->id
			, 'projectName'=>$project->name
			, 'availabilityName'=>$availability->name
			, 'fromDate'=>$sdt->format(CALENDARISTA_FULL_DATEFORMAT)
			, 'toDate'=>$edt->format(CALENDARISTA_FULL_DATEFORMAT)
			, 'startTimeId'=>$startTimeslot->id !== -1 ? $startTimeslot->id : null
			, 'endTimeId'=>$endTimeslot->id !== -1 ? $endTimeslot->id : null
			, 'seats'=>isset($event['SEATS']) ? (int)$event['SEATS'] : 1
			, 'color'=>$availability->color
			, 'timezone'=>$timezone ? $timezone : $availability->timezone
			, 'serverTimezone'=>$availability->timezone
			, 'fullDay'=>$availability->fullDay
			, 'cost'=>$availability->cost
			, 'returnCost'=>$availability->returnCost
			, 'calendarMode'=>$project->calendarMode
			, 'regionAddress'=>$availability->regionAddress
			, 'regionLat'=>$availability->regionLat
			, 'regionLng'=>$availability->regionLng
			, 'status'=>self::getStatus($event)
			, 'synchedMode'=>Calendarista_SynchedMode::ICS
			, 'synchedBookingId'=>$eventId
			, 'synchedBookingDescription'=>isset($event['DESCRIPTION']) ? $event['DESCRIPTION'] : null
			, 'synchedBookingSummary'=>isset($event['SUMMARY']) ? $event['SUMMARY'] : null
			, 'synchedBookingLocation'=>isset($event['LOCATION']) ? $event['LOCATION'] : null
		));
		
		if($startTimeslot->id !== -1){
			$timeslotRepo->updateSeat($startTimeslot);
		}
		if($endTimeslot->id !== -1){
			$singleDayTimeRange = $project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE;
			if(in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS) && 
				($singleDayTimeRange || $availability->maxTimeslots > 1)){
				$start = $startTimeslot->id + 1;
				$end = $endTimeslot->id;
				for($i = $start;$i <= $end;$i++){
					if($i === $end && $singleDayTimeRange){
						//single day range, we're a step behind
						break;
					}
					$timeslotRepo->updateSeatById($i);
				}
			}else{
				$timeslotRepo->updateSeat($endTimeslot);
			}
		}
		if($originalTimezone){
			Calendarista_TimeHelper::setTimezone($originalTimezone);
		}
	}
	protected static function getStatus($event){
		if(empty($event['status'])){
			return Calendarista_AvailabilityStatus::APPROVED;
		}
		switch ($event['status']){
			case 'TENTATIVE':
				return Calendarista_AvailabilityStatus::PENDING;
			break;
			case 'CONFIRMED':
				return Calendarista_AvailabilityStatus::APPROVED;
			break;
			case 'CANCELLED':
				return Calendarista_AvailabilityStatus::CANCELLED;
			break;
		}
	}
	protected static function getTimeslot($t, $timeslots){
		if($t){
			foreach($timeslots as $timeslot){
				if($timeslot->timeslot == $t){
					return $timeslot;
				}
			}
		}
		return new Calendarista_Timeslot(array());
	}
}
?>