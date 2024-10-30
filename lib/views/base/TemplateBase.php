<?php
class Calendarista_TemplateBase{
	public $projectId;
	public $project;
	public $projectList;
	public $enableMultipleBooking;
	public $selectedStep;
	public $postbackStep;
	public $requestUrl;
	public $baseUrl;
	public $stringResources;
	public $uniqueId;
	public $notAjaxRequest;
	public $stateBag;
	public $generalSetting;
	public $viewState;
	public $ajaxUrl;
	public $serviceThumbnailView;
	public $availabilityThumbnailView;
	public function __construct($stateBag = null){
		$this->projectList = apply_filters('calendarista_shortcode_id', null);
		$this->enableMultipleBooking = apply_filters('calendarista_enable_multi_booking', null);
		$this->serviceThumbnailView = apply_filters('calendarista_service_thumbnail_view', null);
		$this->availabilityThumbnailView = apply_filters('calendarista_availability_thumbnail_view', null);
		if(isset($_POST['serviceThumbnailView'])){
			$this->serviceThumbnailView = isset($_POST['serviceThumbnailView']) ? filter_var($_POST['serviceThumbnailView'], FILTER_VALIDATE_BOOLEAN) : $this->serviceThumbnailView;
		}
		if(isset($_POST['availabilityThumbnailView'])){
			$this->availabilityThumbnailView = isset($_POST['availabilityThumbnailView']) ? filter_var($_POST['availabilityThumbnailView'], FILTER_VALIDATE_BOOLEAN) : $this->availabilityThumbnailView;
		}
		if(!$this->projectList){
			$this->projectList = (isset($_POST['projectList']) && $_POST['projectList'] != '') ? explode(',', $this->getPostValue('projectList')) : array();
			$this->enableMultipleBooking = isset($_POST['enableMultipleBooking']) ?  filter_var($this->getPostValue('enableMultipleBooking'), FILTER_VALIDATE_BOOLEAN) : false;
		}
		$this->projectId = (int)$this->getPostValue('projectId', -1);
		$this->stateBag = $stateBag ? $stateBag : $this->getPostValue('__viewstate');
		$this->postbackStep = $this->getPostValue('postbackStep');
		$this->selectedStep = (int)$this->getPostValue('selectedStep', 1);
		if(($this->projectId === -1 && (count($this->projectList) > 0)) || (count($this->projectList) > 0 && !in_array($this->projectId, $this->projectList))){
			if(!in_array($this->projectId, $this->projectList)){
				$this->stateBag = null;
				$this->postbackStep = null;
				$this->selectedStep = 1;
			}
			$this->projectId =  $this->projectList[0];
		}
		$searchResultServiceId = isset($_GET['cal-service-id']) && $_GET['cal-service-id'] ? (int)$_GET['cal-service-id'] : null;
		if($searchResultServiceId){
			$this->projectId = $searchResultServiceId;
		}
		$this->project = Calendarista_ProjectHelper::getProject($this->projectId);
		$this->requestUrl =	esc_url(remove_query_arg(array('calendarista_handler', 'calendarista_staging_id'), $_SERVER['REQUEST_URI']));
		$this->notAjaxRequest = (!(defined('DOING_AJAX') && DOING_AJAX));
		if(!$this->notAjaxRequest){
			$this->requestUrl = esc_url(remove_query_arg(array('calendarista_handler', 'calendarista_staging_id'), $_SERVER['HTTP_REFERER']));
		}
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->uniqueId = 'calendarista_' . $this->projectId;
		$this->ajaxUrl = admin_url('admin-ajax.php');
		if(defined('ICL_LANGUAGE_CODE')){
			$this->ajaxUrl .= sprintf('?lang=%s', ICL_LANGUAGE_CODE);
		}
		$this->initState();
	}
	public function sanitize($value){
		return stripslashes($value);
	}
	public function decode($value){
		$result = json_decode($value);
		if(is_null($result)){
			return json_decode($this->sanitize($value));
		}
		return $result;
	}
	public function deserialize($value){
		return unserialize(stripslashes(html_entity_decode($value, ENT_QUOTES, "UTF-8")));
	}
	protected function getPostValue($key, $default = null){
		if(isset($_POST[$key])){
			return is_array($_POST[$key]) ? $_POST[$key] : sanitize_text_field($_POST[$key]);
		}
		return $default;
	}
	protected function getIntPostValue($key, $default = null){
		return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
	}
	protected function getString($key, $value){
		if($value && isset($value[$key])){
			return sanitize_text_field($value[$key]);
		}
		return null;
	}
	protected function initState(){
		$result = array();
		if($this->stateBag){
			$result = $this->deserialize($this->stateBag);
		}
		$this->flattenTimeRange();
		$result[0] = array('projectId'=>$this->projectId);
		switch($this->postbackStep){
			case 'calendar':
				//calendar fields
				$availableDate = $this->getPostValue('availableDate');
				$startTime = $this->getPostValue('startTime');
				$endTime = $this->getPostValue('endTime');
				$endDate = $this->getPostValue('endDate');
				$availabilities = $this->getPostValue('availabilities');
				$startTimeslot = null;
				$endTimeslot = null;
				$availabilityId = $this->getPostValue('package') ? (int)$this->getPostValue('package') : (int)$this->getPostValue('availabilityId');
				$calendarMode = isset($_POST['calendarMode']) ? (int)$this->getPostValue('calendarMode') : -1;
				$repeatAppointment =  $this->getPostValue('repeatAppointment') ? true : false;
				$repeatWeekdayList = $this->getPostValue('repeatWeekdayList') ? (array)$this->getPostValue('repeatWeekdayList') : null;
				$repeatFrequency = $this->getPostValue('repeatFrequency') ? (int)$this->getPostValue('repeatFrequency') : null;
				$repeatInterval = $this->getPostValue('repeatInterval') ? (int)$this->getPostValue('repeatInterval') : null;
				$terminateAfterOccurrence = $this->getPostValue('terminateAfterOccurrence') ? (int)$this->getPostValue('terminateAfterOccurrence') : null;
				if($calendarMode !== -1 && $calendarMode === Calendarista_CalendarMode::SINGLE_DAY){
					$availabilityHelper = new Calendarista_AvailabilityHelper(array(
						'projectId'=>$this->projectId
						, 'availabilityId'=>$availabilityId
					));
					if($availabilityHelper->availability->daysInPackage > 1){
						$nextOccurance = $availabilityHelper->getNextOccurrenceByPackage(strtotime($availableDate));
						if($nextOccurance){
							$endDate = date(CALENDARISTA_DATEFORMAT, $nextOccurance['endDate']);
						}
					}
				}
				if($startTime){
					$timeslotRepo = new Calendarista_TimeslotRepository();
					$startTimeslot = $timeslotRepo->read((int)$startTime);
					if($endTime){
						$endTimeslot = $timeslotRepo->read((int)$endTime);
					}
				}
				if(($startTimeslot && $endTimeslot) && $this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE){
					$date1 = strtotime(sprintf('%s %s', $availableDate, $startTimeslot->timeslot));
					$date2 = strtotime(sprintf('%s %s', $availableDate, $endTimeslot->timeslot));
					if($date2 <= $date1){
						$endDate = date(CALENDARISTA_DATEFORMAT, strtotime('+1 day', $date1));
					}
				}
				$result[1] = array(
					'availabilityId'=>$availabilityId
					, 'availableDate'=>$availableDate
					, 'endDate'=>$endDate
					, 'startTime'=>$startTime
					, 'endTime'=>$endTime
					, 'startTimeslot'=>$startTimeslot ? $startTimeslot->timeslot : ''
					, 'endTimeslot'=>$endTimeslot ? $endTimeslot->timeslot : ''
					, 'seats'=>(int)$this->getPostValue('seats')
					, 'seatsMinimum'=>(int)$this->getPostValue('seatsMinimum')
					, 'seatsMaximum'=>(int)$this->getPostValue('seatsMaximum')
					, 'dynamicFields'=>$this->extractDynamicFields($availabilityId)
					, 'availabilities'=>$availabilities && is_array($availabilities) ? implode(',', $availabilities) : $availabilities
					, 'timezone'=>$this->getPostValue('timezone')
					, 'multiDateSelection'=>$this->getPostValue('multiDateSelection')
					, '_availabilityId'=>$this->getPostValue('_availabilityId') ? (int)$this->getPostValue('_availabilityId') : null
					, '_availableDate'=>$this->getPostValue('_availableDate') ? $this->getPostValue('_availableDate') : null
					, '_endDate'=>$this->getPostValue('_endDate') ? $this->getPostValue('_endDate') : null
					, '_multiDateSelection'=>$this->getPostValue('_multiDateSelection') ? $this->getPostValue('_multiDateSelection') : null
					, 'repeatAppointment'=>$repeatAppointment
					, 'repeatWeekdayList'=>$repeatWeekdayList && is_array($repeatWeekdayList) ? implode(',', $repeatWeekdayList) : null
					, 'repeatFrequency'=>$repeatFrequency
					, 'repeatInterval'=>$repeatInterval
					, 'terminateAfterOccurrence'=>$terminateAfterOccurrence
				);
			break;
			case 'map':
				//map
				$departure = (array)$this->decode($this->getPostValue('departure'));
				$destination = (array)$this->decode($this->getPostValue('destination'));
				$waypointsRaw = (array)$this->decode($this->getPostValue('waypoints'), true);
				$waypoints = array();
				if($waypointsRaw && count($waypointsRaw) > 0){
					foreach($waypointsRaw as $raw){
						$waypoint = (array)$this->decode($raw, true);
						array_push($waypoints, 
							array('address'=>$this->getString('address', $waypoint)
								, 'lat'=>$this->getString('lat', $waypoint)
								, 'lng'=>$this->getString('lng', $waypoint)));
					}
				}
				$result[2] = array(
					'fromAddress'=>$this->getString('address', $departure)
					, 'fromLat'=>$this->getString('lat', $departure)
					, 'fromLng'=>$this->getString('lng', $departure)
					, 'toAddress'=>$this->getString('address', $destination)
					, 'toLat'=>$this->getString('lat', $destination)
					, 'toLng'=>$this->getString('lng', $destination)
					, 'distance'=>(float)$this->getPostValue('distance')
					, 'duration'=>(float)$this->getPostValue('duration')
					, 'unitType'=>$this->getPostValue('unit') === 'km' ? 0 : 1
					, 'waypoints'=>$waypoints
					, 'fromPlaceId'=>$this->getIntPostValue('fromPlaceId')
					, 'toPlaceId'=>$this->getIntPostValue('toPlaceId')
				);
			break;
			case 'optionals':
				//optional fields
				$optionalRepo = new Calendarista_OptionalRepository();
				$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
				$this->optionalGroups = $optionalGroupRepo->readAll($this->projectId);
				$optionals = array();
				$optionalIncremental = array();
				foreach($this->optionalGroups as $group){
					$values = array_filter($this->getPostValue('optional_group_' . $group->id, array()));
					foreach($values as $optional){
						array_push($optionals, (int)$optional);
					}
				}
				//has an input value
				foreach($this->optionalGroups as $group){
					if($group->displayMode !== Calendarista_OptionalDisplayMode::INCREMENTAL_INPUT){
						continue;
					}
					$values = $optionalRepo->readAllByGroup($group->id);
					foreach($values as $optional){
						$val = $this->getPostValue('optional_incremental_' . $optional->id);
						if(is_null($val)){
							continue;
						}
						if((int)$val > 0){
							array_push($optionalIncremental, $optional->id . ':' . $val);
						}
					}
				}
				$result[3] = array(
					'optionals'=>implode(',', $optionals)
					, 'optional_incremental'=>implode(',', $optionalIncremental)
				);
			break;
			case 'form':
				//custom form fields
				$customFormFields = array();
				$customerType = (int)$this->getPostValue('customerType');
				$orderIndex = 0;
				$repo = new Calendarista_FormElementRepository();
				$formElements = $repo->readAll($this->projectId);
				$numberOfGuests = isset($result[1]) && isset($result[1]['seats']) ? (int)$result[1]['seats'] : null;
				foreach($formElements as $formElement){
					if($formElement->guestField){
						continue;
					}
					$key = 'formelement_' . $formElement->id;
					if(isset($_POST[$key])){
						if($formElement->orderIndex > $orderIndex){
							$orderIndex = $formElement->orderIndex;
						}
						$value = is_array($_POST[$key]) ?  $_POST[$key] : sanitize_text_field($_POST[$key]);
						array_push($customFormFields, array(
							'projectId'=>$this->projectId
							, 'elementId'=>$formElement->id
							, 'orderIndex'=>$formElement->orderIndex
							, 'value'=>is_array($_POST[$key]) ? implode(',', $value) : $this->sanitize($value)
							, 'label'=>$formElement->label
						));
					}
				}
				if($numberOfGuests){
					for($i = 0; $i < (int)$numberOfGuests;$i++){
						foreach($formElements as $formElement){
							if(!$formElement->guestField){
								continue;
							}
							$key = 'formelement_' . $formElement->id . '_guest_' . $i;
							if(isset($_POST[$key])){
								$value = is_array($_POST[$key]) ?  $_POST[$key] : sanitize_text_field($_POST[$key]);
								array_push($customFormFields, array(
									'projectId'=>$this->projectId
									, 'elementId'=>$formElement->id
									, 'orderIndex'=>++$orderIndex
									, 'guestIndex'=>$i
									, 'value'=>is_array($_POST[$key]) ? implode(',', $value) : $this->sanitize($value)
									, 'label'=>sprintf($this->stringResources['SEATS_GUEST_FIELD'], $i+1, $formElement->label)
								));
							}
						}
					}
				}
				$nameField = isset($_POST['name']) ?  sanitize_text_field($_POST['name']) : null;
				if(!$nameField){
					$nameField = 'Name Abuse';
				}
				$name = $this->parseFullName($this->sanitize($nameField));
				$emailField = isset($_POST['email']) ?  sanitize_email($_POST['email']) : null;
				if(!$emailField){
					$emailField = 'Email Abuse';
				}
				$result[4] = array(
					'formelements'=>$customFormFields
					, 'userId'=>$this->getIntPostValue('userId')
					, 'name'=>$name['fullname']
					, 'firstname'=>$name['firstname']
					, 'lastname'=>$name['lastname']
					, 'email'=>$emailField
					, 'customerType'=>$customerType
				);
			break;
			case 'checkout':
				//checkout
				$result[5] = array(
					'paymentsMode'=>(int)$this->getPostValue('paymentsMode')
					, 'coupon'=>sanitize_text_field($this->getPostValue('coupon'))
					, 'repeatAppointmentDates'=>$this->getPostValue('repeatAppointmentDates')
					, 'upfrontPayment'=>$this->getPostValue('upfrontPayment')
				);
			break;
		}
		if($this->postbackStep){
			if(!isset($result[6]) && $this->getPostValue('requestId')){
				$result[6] = array('requestId'=>$this->getPostValue('requestId'));
			}
			if(!isset($result[7]) && $this->getPostValue('stagingId')){
				$result[7] = array('stagingId'=>$this->getPostValue('stagingId'));
			}
			if($this->getPostValue('repeatAppointmentDates')){
				$repeatAppointmentDates = $this->getPostValue('repeatAppointmentDates');
				$result[8] = array('repeatAppointmentDates'=>is_array($repeatAppointmentDates) ? implode(',', $repeatAppointmentDates) : $repeatAppointmentDates);
			}
		}
		$result = $this->repeatAppointmentReset($result);
		$this->viewState = $this->getViewState($result);
		$this->stateBag = htmlentities(serialize($result), ENT_QUOTES, "UTF-8");
	}
	protected function repeatAppointmentReset($viewState){
		if(!isset($_POST['resetRepeat'])){
			return $viewState;
		}
		$state = isset($viewState[1]) ? $viewState[1] : array();
		foreach($state as $key=>$value){
			switch($key){
				case 'repeatAppointment':
				case 'repeatWeekdayList':
				case 'repeatFrequency':
				case 'repeatInterval':
				case 'terminateAfterOccurrence':
				unset($viewState[1][$key]);
				break;
			}
		}
		$state = isset($viewState[8]) ? $viewState[8] : array();
		foreach($state as $key=>$value){
			foreach($state as $key=>$value){
				switch($key){
					case 'repeatAppointmentDates':
					unset($viewState[8]);
					break;
				}
			}
		}
		return $viewState;
	}
	protected function getViewState($stateBag){
		$result = array();
		if(is_array($stateBag)){
			foreach($stateBag as $key=>$value){
				$result = array_merge($result, $value);
			}
		}
		$post = array_merge(array(), $_POST);
		foreach($post as $key=>$value){
			if(array_key_exists($key, $result)){
				if(empty($result[$key]) && !empty($value)){
					$result[$key] = $value;
				}
				continue;
			}
			if($key !== '__viewstate'){
				$result[$key] = $value;
			}
		}
		return $result;
	}
	public function getViewStateValue($value, $default = null){
		if(isset($this->viewState[$value])){
			return $this->viewState[$value];
		}
		return $default;
	}
	public function selected($key, $match, $default = null){
		$value = $this->getViewStateValue($key);
		if($value == $match){
			return 'selected=selected';
		}
		return $default;
	}
	public function clearViewState(){
		$result = array();
		$result[0] = array('projectId'=>$this->projectId);
		unset($_POST['__viewstate']);
		$this->viewState = array();
		$this->stateBag = htmlentities(serialize($result));
	}
	public function parseFullName($fullname){
		$firstname = $fullname;
		$lastname = null;
		if(strpos($fullname, ' ') !== false){
			$namepair = explode(' ', $fullname);
			$firstname = $namepair[0];
			unset($namepair[0]);
			$lastname = implode(' ', $namepair);
		}
		return array('fullname'=>$fullname, 'firstname'=>$firstname, 'lastname'=>$lastname);
	}
	protected function flattenTimeRange(){
		if($this->postbackStep !== 'calendar'){
			return;
		}
		//time can be an array, so flatten and populate POST
		$availableDate = $this->getPostValue('availableDate');
		$startTime = $this->getPostValue('startTime', array());
		$endTime = $this->getPostValue('endTime', array());
		$maxTimeslots = (int)$this->getPostValue('maxTimeslots');
		$calendarMode = (int)$this->getPostValue('calendarMode');
		if(in_array($calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_TIMESLOT_SELECTION) && 
								$maxTimeslots > 1){
			//special case, startTime uses multiple select list
			//which means: first element = starttime & last element = endtime
			if(!is_array($startTime) || count($startTime) === 0){
				return;
			}
			$endTime = array();
			if(count($startTime) > 1){
				$_POST['endTime'] = $startTime[count($startTime) - 1];
				$_POST['endDate'] = $availableDate;
			}
			$_POST['startTime'] = $startTime[0];
		} 
	}
	public function getConfirmUrl($url, $stagingId = null, $handler = null, $hash = true){
		if($stagingId || $handler){
			$url .= (strpos($url,'?') !== false) ? '&' : '?';
		}
		if($handler){
			$url .= 'calendarista_handler=' . $handler;
			if($stagingId){
				$url .= '&';
			}
		}
		if($stagingId){
			$url .= sprintf('calendarista_staging_id=%s', $stagingId);
		}
		if($hash && !$this->generalSetting->confirmUrl){
			$url .= sprintf('#CAL%s', $this->projectId);
		}
		return $url;
	}
	protected function extractDynamicFields($availabilityId){
		$dynamicFieldPricingRepo = new Calendarista_DynamicFieldPricingRepository();
		$dynamicFieldRepo = new Calendarista_DynamicFieldRepository();
		$result = $dynamicFieldRepo->readByAvailabilityId(array('availabilityId'=>$availabilityId));
		$dynamicFields = $result['resultset'];
		$fields = array();
		foreach($dynamicFields as $field){
			$name = 'calendarista_dynamicfield_' . $field->id;
			$val = $this->getPostValue($name);
			if($val){
				array_push($fields, array(
					'id'=>(int)$field->id
					, 'value'=>(int)$val
					, 'label'=>$field->label
					, 'byOptional'=>$field->byOptional
					, 'limitBySeat'=>$field->limitBySeat
					, 'cost'=>$field->cost
					, 'fixedCost'=>$field->fixedCost
				));
			}
		}
		return $fields;
	}
	public function decodeString($args){
		return Calendarista_StringResourceHelper::decodeString($args);
	}
}
?>