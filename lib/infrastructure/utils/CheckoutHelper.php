<?php
class Calendarista_CheckoutHelper
{
	public $viewState;
	public $appointment = -1/*1 = Edit mode*/;
	public $generalSetting;
	public $stringResources;
	public $projectId;
	public $stagingId;
	public $paymentOperator;
	public $transactionId;
	public $paymentDate;
	public $paymentStatus = Calendarista_PaymentStatus::PAID;
	public $wooCommerceOrderId;
	public $wooDiscount;
	public $wooDiscountMode;
	public $wooTotalAmount;
	public $wooTaxValue;
	public $data = array();
	public $fullDay;
	public $customerName;
	public $totalAmount;
	public $totalAmountRaw;
	private $dateList;
	const DT_FORMAT = 'Ymd\THis';
	const D_FORMAT = 'Ymd';
	public function __construct($args)
	{
		if(array_key_exists('viewState', $args)){
			$this->viewState = $args['viewState'];
		}
		if(array_key_exists('appointment', $args)){
			$this->appointment = $args['appointment'];
		}
		if(array_key_exists('stagingId', $args)){
			$this->stagingId = $args['stagingId'];
			$stagingRepo = new Calendarista_StagingRepository();
			$result = $stagingRepo->read($this->stagingId);
			if($result){
				$stateBag = $this->deserialize(html_entity_decode($result->viewState, ENT_QUOTES, "UTF-8"));
				$this->viewState = array();
				foreach($stateBag as $value){
					$this->viewState = array_merge($this->viewState, $value);
				}
			}
		}
		if(array_key_exists('paymentOperator', $args)){
			$this->paymentOperator = $args['paymentOperator'];
		}
		if(array_key_exists('transactionId', $args)){
			$this->transactionId = $args['transactionId'];
		}
		if(array_key_exists('paymentDate', $args)){
			$this->paymentDate = $args['paymentDate'];
		}
		if(array_key_exists('paymentStatus', $args)){
			$this->paymentStatus = $args['paymentStatus'];
		}
		if(array_key_exists('wooCommerceOrderId', $args)){
			$this->wooCommerceOrderId = (int)$args['wooCommerceOrderId'];
			if(array_key_exists('wooDiscount', $args)){
				$this->wooDiscount = $args['wooDiscount'];
			}
			if(array_key_exists('wooDiscountMode', $args)){
				$this->wooDiscountMode = $args['wooDiscountMode'];
			}
			if(array_key_exists('wooTaxValue', $args)){
				$this->wooTaxValue = $args['wooTaxValue'];
			}
			if(array_key_exists('wooTotalAmount', $args)){
				$this->wooTotalAmount = $args['wooTotalAmount'];
			}
		}
		if(array_key_exists('upfrontPayment', $args)){
			$this->viewState['upfrontPayment'] = $args['upfrontPayment'];
		}
		$this->projectId = (int)$this->getViewStateValue('projectId');
		add_filter('calendarista_project_id', array($this, 'getProjectId'));
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
	}
	public function getProjectId(){
		return $this->projectId;
	}
	public function orderIsValid(){
		$requestId = isset($_POST['requestId']) ? sanitize_text_field($_POST['requestId']) : $this->getViewStateValue('requestId');
		if(!$requestId){
			return false;
		}
		$repo = new Calendarista_OrderRepository();
		$result = $repo->orderExists($requestId);
		return $result ? false : true;
	}
	public function log($notify = true){
		$orderId = $this->createAppointment();
		if(!$orderId){
			return null;
		}
		$repo = new Calendarista_OrderRepository();
		$order = $repo->read($orderId);
		if($this->stagingId){
			$this->deleteFromStaging();
			$this->updateStatus($order);
		}
		if($notify && $order){
			Calendarista_EmailTemplateHelper::sendNotifications($order);
			$this->scheduleEmailReminder($orderId);
		}
		if($this->generalSetting->newAppointmentZap){
			foreach($this->data as $data){
				Calendarista_WebHookHelper::postDataToUrl($this->generalSetting->newAppointmentZap, $data);
			}
		}
		return $order;
	}
	protected function updateStatus($order){
		$order->paymentOperator = $this->paymentOperator;
		$order->paymentStatus = $this->paymentStatus;
		$order->paymentDate = $this->paymentDate;
		$order->transactionId = $this->transactionId;
		$order->wooCommerceOrderId = $this->wooCommerceOrderId;
		$orderRepo = new Calendarista_OrderRepository();
		$orderRepo->update($order);
	}
	public function createAppointment(){
		$projectRepo = new Calendarista_ProjectRepository();
		$project = $projectRepo->read($this->projectId);
		if(!$project){
			return;
		}
		$availabilityId = (int)$this->getViewStateValue('availabilityId');
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availability = $availabilityRepo->read($availabilityId);
		if(!$availability){
			return null;
		}
		$this->fullDay = !in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
		$orderId = $this->saveOrder($project, $availability);
		if(isset($this->wooCommerceOrderId)){
			//log any woocommerce related info
			$this->updateWooChanges($orderId);
		}
		$this->saveMap($orderId);
		$this->saveWaypoints($orderId);
		$this->saveOptionals($orderId);
		$this->saveFormFields($orderId);
		$this->saveDynamicFields($orderId, $project, $availability);
		$this->saveAvailability($orderId, $project, $availability);
		$repeatDateList = $this->getRepeatDateList();
		if($repeatDateList && count($repeatDateList) > 0){
			$this->saveAvailability($orderId, $project, $availability, null, 0, false, $repeatDateList);
		}
		//handle multiple availabilities
		$availabilities = $this->getViewStateValue('availabilities');
		$availabilityList = array();
		if($availabilities && is_array($availabilities)){
			$availabilityList = $availabilities;
		}else if($availabilities){
			$availabilityList = explode(',', $availabilities);
		}
		if(count($availabilityList) > 0){
			$orderAvailabilityRepo = new Calendarista_OrderAvailabilityRepository();
			foreach($availabilityList as $availabilityId){
				$availability = $availabilityRepo->read((int)$availabilityId);
				if(!$availability){
					return null;
				}
				$this->saveDynamicFields($orderId, $project, $availability);
				$this->saveAvailability($orderId, $project, $availability, null, 0, true);
				if($repeatDateList && count($repeatDateList) > 0){
					$this->saveAvailability($orderId, $project, $availability, null, 0, false, $repeatDateList);
				}
				$orderAvailabilityRepo->insert(array(
					'orderId'=>$orderId
					, 'availabilityId'=>$availability->id
					, 'availabilityName'=>$availability->name
				));
			}
		}
		return $orderId;
	}
	public function saveFormFields($orderId){
		$seats = (int)$this->viewState['seats'];
		if($this->getViewStateValue('formelements')){
			$formElementRepo = new Calendarista_BookedFormElementRepository();
			foreach($this->viewState['formelements'] as $formElement){
				if($formElement['elementId'] === -1 && $formElement['guestIndex'] > ($seats - 1)){
					//toDO: deprecate this check in future editions.
					continue;
				}
				$formElementRepo->insert(array_merge($formElement, array('orderId'=>$orderId)));
			}
		}
	}
	public function saveOptionals($orderId){
		if($this->getViewStateValue('optionals')){
			$optionals = explode(',', $this->viewState['optionals']);
			if(count($optionals) > 0){
				$optionalRepo = new Calendarista_OptionalRepository();
				$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
				$bookedOptionalRepo = new Calendarista_BookedOptionalRepository();
				foreach($optionals as $id){
					$optional = $optionalRepo->read((int)$id);
					$optionalGroup = $optionalGroupRepo->read($optional->groupId);
					$bookedOptionalRepo->insert(array(
						'orderId'=>$orderId
						, 'projectId'=>$this->projectId
						, 'optionalId'=>$optional->id
						, 'name'=>$optional->name
						, 'groupName'=>$optionalGroup->name
						, 'orderIndex'=>$optional->orderIndex
						, 'groupOrderIndex'=>$optionalGroup->orderIndex
						, 'groupId'=>$optionalGroup->id
						, 'cost'=>$optional->cost
					));
				}
			}
		}
		if($this->getViewStateValue('optional_incremental')){
			$optionals = explode(',', $this->viewState['optional_incremental']);
			if(count($optionals) > 0){
				$optionalRepo = new Calendarista_OptionalRepository();
				$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
				$bookedOptionalRepo = new Calendarista_BookedOptionalRepository();
				foreach($optionals as $opt){
					$item = explode(':', $opt);
					$optional = $optionalRepo->read((int)$item[0]);
					$optionalGroup = $optionalGroupRepo->read($optional->groupId);
					$bookedOptionalRepo->insert(array(
						'orderId'=>$orderId
						, 'projectId'=>$this->projectId
						, 'optionalId'=>$optional->id
						, 'name'=>$optional->name
						, 'groupName'=>$optionalGroup->name
						, 'orderIndex'=>$optional->orderIndex
						, 'groupOrderIndex'=>$optionalGroup->orderIndex
						, 'groupId'=>$optionalGroup->id
						, 'cost'=>$optional->cost
						, 'incrementValue'=>(int)$item[1]
					));
				}
			}
		}
	}
	public function saveWaypoints($orderId){
		if($this->getViewStateValue('waypoints') && count($this->viewState['waypoints']) > 0){
			$waypointRepo = new Calendarista_BookedWaypointRepository();
			foreach($this->viewState['waypoints'] as $w){
				$waypoint = (array)$w;
				$waypointRepo->insert(array(
					'orderId'=>$orderId
					, 'projectId'=>$this->projectId
					, 'address'=>$waypoint['address']
					, 'lat'=>$waypoint['lat']
					, 'lng'=>$waypoint['lng']
				));
			}
		}
	}
	public function saveMap($orderId){
		if($this->getViewStateValue('fromAddress')){
			$mapRepo = new Calendarista_BookedMapRepository();
			$mapRepo->insert(array(
				'orderId'=>$orderId
				, 'projectId'=>$this->projectId
				, 'fromAddress'=>$this->getViewStateValue('fromAddress')
				, 'fromLat'=>$this->getViewStateValue('fromLat')
				, 'fromLng'=>$this->getViewStateValue('fromLng')
				, 'toAddress'=>$this->getViewStateValue('toAddress')
				, 'toLat'=>$this->getViewStateValue('toLat')
				, 'toLng'=>$this->getViewStateValue('toLng')
				, 'unitType'=>$this->getViewStateValue('unitType')
				, 'distance'=>$this->getViewStateValue('distance')
				, 'duration'=>$this->getViewStateValue('duration')
				, 'fromPlaceId'=>$this->getViewStateValue('fromPlaceId')
				, 'toPlaceId'=>$this->getViewStateValue('toPlaceId')
			));
		}
	}
	public function saveAvailability($orderId, $project, $availability, $status = null, $oldSeats = 0, $queryTimeslot = false, $repeatAppointmentList = null){
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$startTime = new Calendarista_Timeslot(array());
		$endTime = new Calendarista_Timeslot(array());
		$startTimeId = null;
		$endTimeId = null;
		$this->dateList = array();
		if(!$repeatAppointmentList){
			if(in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_DATE)){
				$multiDateSelection = $this->getViewStateValue('multiDateSelection');
				$selectedDates = explode(';', $multiDateSelection);
				foreach($selectedDates as $dt){
						$startTime = new Calendarista_Timeslot(array());
						$endTime = new Calendarista_Timeslot(array());
					if($project->calendarMode === Calendarista_CalendarMode::MULTI_DATE){
						array_push($this->dateList, array('startDate'=>$dt, 'endDate'=>$dt, 'startTimeId'=>null, 'endTimeId'=>null));
					}else{
						$pair = explode(':', $dt);
						if($queryTimeslot){
							$startTime = self::getTimeslot($dt, (int)$pair[1], $availability->id);
						}else{
							$startTime = $timeslotRepo->read((int)$pair[1]);
						}
						if(!$startTime){
							continue;
						}
						$startDate = date(CALENDARISTA_FULL_DATEFORMAT, strtotime($pair[0] . ' ' . $startTime->timeslot));
						array_push($this->dateList, array('startDate'=>$startDate, 'endDate'=>$startDate, 'startTimeId'=>$startTime->id, 'endTimeId'=>$startTime->id));
					}
				}
			}else{
				if($this->getViewStateValue('startTime')){
					$startTimeId = (int)$this->getViewStateValue('startTime');
					//double check, in case we have multiple availabilities
					if($queryTimeslot){
						$startTime = self::getTimeslot($this->getViewStateValue('availableDate'), $startTimeId, $availability->id);
					}else{
						$startTime = $timeslotRepo->read($startTimeId);
					}
					$endTime->timeslot = $startTime->timeslot;
				}
				if($this->getViewStateValue('endTime')){
					$endTimeId = (int)$this->getViewStateValue('endTime');
					if($queryTimeslot){
						$endTime = self::getTimeslot($this->getViewStateValue('endDate'), $endTimeId, $availability->id);
					}else{
						$endTime = $timeslotRepo->read($endTimeId);
					}
				}
				$startDate = date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
									, $this->getViewStateValue('availableDate')
									, $startTime->timeslot))));
				$endDate = $this->getViewStateValue('endDate') ? date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
							, $this->getViewStateValue('endDate')
							, $endTime->timeslot)))) : $startDate;
				array_push($this->dateList, array('startDate'=>$startDate, 'endDate'=>$endDate, 'startTimeId'=>$startTime->id, 'endTimeId'=>$endTime->id));
			}
		}else{
			$this->dateList = $repeatAppointmentList;
		}
		if($status === null && (isset($this->viewState) && isset($this->viewState['status']))){
			$status = $this->getViewStateValue('status');
		}
		
		$seats = $this->getViewStateValue('seats');
		if(!$seats && (($startTime->seats > 0 || $availability->seats > 0) && !$availability->selectableSeats)){
			$seats = 1;
		}
		if(!$availability->selectableSeats){
			$guestCount = $this->getDynamicFieldSeats();
			if($guestCount){
				$seats = $guestCount;
			}
		}
		if($status === null){
			$status = $this->generalSetting->autoApproveBooking ? 
					Calendarista_AvailabilityStatus::APPROVED : Calendarista_AvailabilityStatus::PENDING;
		}
		foreach($this->dateList as $dl){
			$bookedAvailabilityId = $bookedAvailabilityRepo->insert(array(
				'orderId'=>$orderId
				, 'availabilityId'=>$availability->id
				, 'projectId'=>$project->id
				, 'projectName'=>$project->name
				, 'availabilityName'=>$availability->name
				, 'fromDate'=>date(CALENDARISTA_FULL_DATEFORMAT, strtotime($dl['startDate']))
				, 'toDate'=>date(CALENDARISTA_FULL_DATEFORMAT, strtotime($dl['endDate']))
				, 'startTimeId'=>$dl['startTimeId']
				, 'endTimeId'=>$dl['endTimeId']
				, 'seats'=>$seats
				, 'color'=>$availability->color
				, 'timezone'=>$this->getViewStateValue('timezone')
				, 'serverTimezone'=>$availability->timezone
				, 'fullDay'=>$availability->fullDay
				, 'cost'=>$availability->cost
				, 'returnCost'=>$availability->returnCost
				, 'calendarMode'=>$project->calendarMode
				, 'userEmail'=>$this->getViewStateValue('email')
				, 'regionAddress'=>$availability->regionAddress
				, 'regionLat'=>$availability->regionLat
				, 'regionLng'=>$availability->regionLng
				, 'status'=>$status
				, 'repeated'=>$repeatAppointmentList !== null
			));
			$statusText = __('PENDING', 'calendarista');
			if($status === 1){
				$statusText = __('APPROVED', 'calendarista');
			}else if($status === 2){
				$statusText = __('CANCELLED', 'calendarista');
			}
			array_push($this->data, array(
				'service'=>$project->name
				, 'bookedAvailabilityId'=>$bookedAvailabilityId
				, 'availability'=>$availability->name
				, 'start_date'=>date(CALENDARISTA_FULL_DATEFORMAT, strtotime($dl['startDate']))
				, 'start_date_timestamp'=>$this->formatTimestamp($dl['startDate'])
				, 'end_date'=>date(CALENDARISTA_FULL_DATEFORMAT, strtotime($dl['endDate']))
				, 'end_date_timestamp'=>$this->formatTimestamp($dl['endDate'])
				, 'timezone'=>$availability->timezone
				, 'seats'=>$seats
				, 'email'=>$this->getViewStateValue('email')
				, 'status'=>$statusText
				, 'name'=>$this->customerName
				, 'total_amount'=>$this->totalAmount
				, 'total_amount_raw'=>$this->totalAmountRaw
			));
			$this->googleCalendarSync($bookedAvailabilityId);
			if($startTime->id !== -1){
				$timeslotRepo->updateSeat($startTime, $seats, $oldSeats);
			}
			if($endTime->id !== -1){
				$singleDayTimeRange = $project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE;
				if(in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS) && 
					($singleDayTimeRange || $availability->maxTimeslots > 1)){
					$start = $startTime->id + 1;
					$end = $endTime->id;
					for($i = $start;$i <= $end;$i++){
						if($i === $end && $singleDayTimeRange){
							//single day range, we're a step behind
							break;
						}
						$timeslotRepo->updateSeatById($i, $seats, $oldSeats);
					}
				}else{
					$timeslotRepo->updateSeat($endTime, $seats, $oldSeats);
				}
			}
		}
		return $this->data;
	}
	protected function getUpfrontAmount($costHelper){
		$result = $costHelper->totalAmount;
		$fullAmountDiscount = $costHelper->availability->fullAmountDiscount;
		if($fullAmountDiscount && $costHelper->seats > 1){
			$fullAmountDiscount = $fullAmountDiscount * $costHelper->seats;
		}
		if($this->getViewStateValue('upfrontPayment') && ($fullAmountDiscount > 0 && 
			$costHelper->totalAmountBeforeDeposit > $fullAmountDiscount)){
			$discountedValue = $costHelper->totalAmountBeforeDeposit - $fullAmountDiscount;
			return $discountedValue;
		}
		return $result;
	}
	protected function saveOrder($project, $availability){
		$costHelper = new Calendarista_CostHelper($this->viewState);
		$orderRepo = new Calendarista_OrderRepository();
		$today = new Calendarista_DateTime();
		$emailExists = false;
		$couponCode = null;
		if($this->generalSetting->newCustomerZap){
			$emailExists = $orderRepo->emailExists($this->getViewStateValue('email'));
		}
		if($costHelper->couponHelper->coupon){
			$couponCode = $costHelper->couponHelper->coupon->code;
		}
		$result = $orderRepo->insert(new Calendarista_Order(array(
			'projectId'=>$this->projectId
			, 'stagingId'=>$this->stagingId
			, 'projectName'=>$project->name
			, 'availabilityId'=>$availability->id
			, 'availabilityName'=>$availability->name
			, 'userId'=>$this->getUserId($project->membershipRequired)
			, 'fullName'=>$this->sanitize($this->getViewStateValue('name'))
			, 'email'=>$this->getViewStateValue('email')
			, 'orderDate'=>$today->format(CALENDARISTA_FULL_DATEFORMAT)
			, 'totalAmount'=>$this->getUpfrontAmount($costHelper)
			, 'currency'=>Calendarista_MoneyHelper::getCurrency()
			, 'currencySymbol'=>Calendarista_MoneyHelper::getCurrencySymbol()
			, 'discount'=>$costHelper->couponHelper->discount
			, 'discountMode'=>$costHelper->couponHelper->discountMode
			, 'tax'=>$this->generalSetting->tax
			, 'timezone'=>$this->getViewStateValue('timezone')
			, 'serverTimezone'=>$availability->timezone
			, 'paymentsMode'=>$this->getViewStateValue('paymentsMode')
			, 'paymentOperator'=>__('Local', 'calendarista')
			, 'deposit'=>$availability->deposit
			, 'depositMode'=>$availability->depositMode
			, 'balance'=>(float)$costHelper->balance
			, 'requestId'=>$this->getViewStateValue('requestId')
			, 'taxMode'=>$this->generalSetting->taxMode
			, 'couponCode'=>$couponCode
			, 'repeatWeekdayList'=>$this->getViewStateValue('repeatWeekdayList')
			, 'repeatFrequency'=>$this->getViewStateValue('repeatFrequency')
			, 'repeatInterval'=>$this->getViewStateValue('repeatInterval')
			, 'terminateAfterOccurrence'=>$this->getViewStateValue('terminateAfterOccurrence')
			, 'upfrontPayment'=>$this->getViewStateValue('upfrontPayment')
		)), $this->generalSetting->prefix);
		$costHelper->couponHelper->invalidateCoupon();
		$this->customerName = $this->sanitize($this->getViewStateValue('name'));
		$this->totalAmount = Calendarista_MoneyHelper::toLongCurrency($costHelper->totalAmount);
		$this->totalAmountRaw = $costHelper->totalAmount;
		if($this->generalSetting->newCustomerZap && !$emailExists){
			Calendarista_WebHookHelper::postDataToUrl($this->generalSetting->newCustomerZap, array(
				'full_name'=>$this->sanitize($this->getViewStateValue('name'))
				, 'first_name'=>self::getFirstName($this->sanitize($this->getViewStateValue('name')))
				, 'last_name'=>self::getLastName($this->sanitize($this->getViewStateValue('name')))
				, 'email'=>$this->getViewStateValue('email')
			));
		}
		return $result;
	}
	public static function getRepeatValue($state, $value, $default = null){
		if(isset($_POST[$value])){
			return $_POST[$value];
		}else if(isset($state[$value])){
			return $state[$value];
		}
		return $default;
	}
	public static function getRepeatArgs($viewState){
		$repeatAppointmentChanged = isset($_POST['repeatAppointmentChanged']) ? true : false;
		$repeatAppointmentDates = isset($viewState['repeatAppointmentDates']) ? $viewState['repeatAppointmentDates'] : null;
		$repeatAppointment = self::getRepeatValue($viewState, 'repeatAppointment', false);
		$repeatWeekdayList = self::getRepeatValue($viewState, 'repeatWeekdayList', array());
		$repeatFrequency = self::getRepeatValue($viewState, 'repeatFrequency');
		$repeatInterval = self::getRepeatValue($viewState, 'repeatInterval');
		$startTime = self::getRepeatValue($viewState, 'startTime', null);
		$endTime = self::getRepeatValue($viewState, 'endTime', null);
		$terminateAfterOccurrence = self::getRepeatValue($viewState, 'terminateAfterOccurrence');
		$availableDate = self::getRepeatValue($viewState, 'availableDate');
		if($repeatWeekdayList && !is_array($repeatWeekdayList)){
			$repeatWeekdayList = array_map('intval', explode(',', $repeatWeekdayList));
		}
		return array(
			'repeatAppointment'=>(bool)$repeatAppointment
			, 'repeatWeekdayList'=>(array)$repeatWeekdayList
			, 'repeatFrequency'=>is_null($repeatFrequency) ? null : (int)$repeatFrequency
			, 'repeatInterval'=>is_null($repeatInterval) ? null : (int)$repeatInterval
			, 'terminateAfterOccurrence'=>is_null($terminateAfterOccurrence) ? null : (int)$terminateAfterOccurrence
			, 'availableDate'=>$availableDate
			, 'repeatAppointmentChanged'=>$repeatAppointmentChanged
			, 'repeatAppointmentDates'=>$repeatAppointmentDates
			, 'startTime'=>$startTime
			, 'endTime'=>$endTime
		);
	}
	public static function getRepeatAppointmentDates($availability, $viewState = null){
		$appointment = isset($_POST['appointment']) ? (int)$_POST['appointment'] : -1;
		$args = self::getRepeatArgs($viewState);
		$result = array();
		$repeatAppointment = $args['repeatAppointment'];
		if(!$repeatAppointment){
			return $result;
		}
		$repeatAppointmentChanged = $args['repeatAppointmentChanged'];
		$repeatAppointmentDates = $args['repeatAppointmentDates'];
		$repeatDatesList = array();
		if(!$repeatAppointmentChanged && $repeatAppointmentDates){
			if(is_array($repeatAppointmentDates)){
				$repeatDatesList = $repeatAppointmentDates;
			}else{
				$repeatDatesList = explode(',', $repeatAppointmentDates);
			}
		}
		$repeatWeekdayList = $args['repeatWeekdayList'];
		$repeatFrequency = $args['repeatFrequency'];
		$repeatInterval = $args['repeatInterval'];
		$terminateAfterOccurrence = $args['terminateAfterOccurrence'];
		$startDate = $args['availableDate'];
		$startTime = $args['startTime'];
		$endTime = $args['endTime'];
		$currentDate = strtotime($startDate);
		$params = array(
			'availability'=>$availability
			, 'repeatFrequency'=>$repeatFrequency
			, 'repeatInterval'=>$repeatInterval
			, 'repeatWeekdayList'=>$repeatWeekdayList
			, 'terminateAfterOccurrence'=>$terminateAfterOccurrence
		);
		$terminationDate = Calendarista_AppointmentRepeatHelper::getTerminationDate($currentDate, $params);
		$currentDate = strtotime('+1 day', $currentDate);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$startTimeslot = new Calendarista_Timeslot(array());
		$endTimeslot = new Calendarista_Timeslot(array());
		if($startTime){
			$startTimeslot = $timeslotRepo->read($startTime);
		}
		if($endTime){
			$endTimeslot = $timeslotRepo->read($endTime);
		}
		while($currentDate <= $terminationDate){
			$repeats = Calendarista_AppointmentRepeatHelper::dateRepeats($startDate, date(CALENDARISTA_DATEFORMAT, $currentDate), $params);
			if($repeats){
				$startDate = date(CALENDARISTA_DATEFORMAT, $currentDate);
				$startDateTime = trim(sprintf('%s %s', date(CALENDARISTA_DATEFORMAT, $currentDate), $startTimeslot->timeslot));
				$endDateTime = trim(sprintf('%s %s', date(CALENDARISTA_DATEFORMAT, $currentDate), $endTime ? $endTimeslot->timeslot : $startTimeslot->timeslot));
				$formattedDate = trim(sprintf('%s %s', Calendarista_TimeHelper::formatDate(date(CALENDARISTA_DATEFORMAT, $currentDate)), $startTimeslot->timeslot));
				if($endTime){
					$formattedDate .= ' - ' . $endTimeslot->timeslot;
				}
				if(count($repeatDatesList) > 0 && !in_array(date(CALENDARISTA_DATEFORMAT, $currentDate), $repeatDatesList)){
					$currentDate = strtotime('+1 day', $currentDate);
					continue;
				}
				array_push($result, array(
					'startDate'=>$startDateTime
					, 'endDate'=>$endDateTime
					, 'raw'=>date(CALENDARISTA_DATEFORMAT, $currentDate)
					, 'formattedDate'=>$formattedDate
				));
			}
			$currentDate = strtotime('+1 day', $currentDate);
		}
		if($appointment !== 1){
			$result = self::filterOutUnavailableDates($availability, $result, $startTimeslot, $endTimeslot);
		}
		return $result;
	}
	public static function filterOutUnavailableDates($availability, $availableDatesList, $startTimeslot, $endTimeslot){
		$appointment = isset($_POST['appointment']) ? (int)$_POST['appointment'] : -1;
		$len =  count($availableDatesList);
		if($len === 0){
			return $availableDatesList;
		}
		$availabilityHelper = new Calendarista_AvailabilityHelper(array('availability'=>$availability));
		$fromDate = $availableDatesList[0]['startDate'];
		$toDate = $len > 1 ? $availableDatesList[$len - 1]['startDate'] :  $availableDatesList[$len - 1]['startDate'];
		$bookedAvailabilities = $availabilityHelper->getAvailabilities(
			date(CALENDARISTA_DATEFORMAT, strtotime($fromDate))
			, date(CALENDARISTA_DATEFORMAT, strtotime($toDate)
		));
		$result = array();
		foreach($availableDatesList as $adl){
			if(!$availabilityHelper->dateIsBooked($adl['startDate'], $bookedAvailabilities) && 
				!$availabilityHelper->dateIsBooked($adl['endDate'], $bookedAvailabilities)){
				$flag = true;
				if($startTimeslot->id !== -1){
					$timeslots = $availabilityHelper->timeslotHelper->getTimeslots($adl['raw'], $bookedAvailabilities, 0/*$slotType*/, $appointment);
					$flag = self::timeSlotAvailable($timeslots, $startTimeslot);
				}
				if($flag && $endTimeslot->id !== -1){
					$timeslots = $availabilityHelper->timeslotHelper->getTimeslots($adl['raw'], $bookedAvailabilities, 1/*$slotType*/, $appointment);
					$flag = self::timeSlotAvailable($timeslots, $startTimeslot, $endTimeslot);
				}
				if($flag){
					array_push($result, $adl);
				}
			}
		}
		return $result;
	}
	public function getRepeatDateList(){
		$repeatAppointmentDates = $this->getViewStateValue('repeatAppointmentDates');
		if(!$repeatAppointmentDates){
			return array();
		}
		$repeatDates = !is_array($repeatAppointmentDates) ? explode(',', $repeatAppointmentDates) : $repeatAppointmentDates;
		if(count($repeatDates) === 0){
			return array();
		}
		$result = array();
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$startTime = $this->getViewStateValue('startTime');
		$endTime = $this->getViewStateValue('endTime');
		$startTimeslot = new Calendarista_Timeslot(array());
		$endTimeslot = new Calendarista_Timeslot(array());
		if($startTime){
			$startTimeslot = $timeslotRepo->read($startTime);
		}
		if($endTime){
			$endTimeslot = $timeslotRepo->read($endTime);
		}
		foreach($repeatDates as $repeatDate){
			$startDate = sprintf('%s %s', $repeatDate, $startTimeslot->timeslot);
			$endDate = sprintf('%s %s', $repeatDate, $endTime ? $endTimeslot->timeslot : $startTimeslot->timeslot);
			array_push($result, array(
				'startDate'=>trim($startDate)
				, 'endDate'=>trim($endDate)
				, 'startTimeId'=>$startTimeslot->id
				, 'endTimeId'=>$endTimeslot->id
			));
		}
		return $result;
	}
	public static function timeSlotAvailable($timeslots, $startTimeslot, $endTimeslot = null){
		$result = true;
		$now = date(CALENDARISTA_DATEFORMAT, strtotime('now'));
		foreach($timeslots as $timeslot){
			if($endTimeslot){
				$start = strtotime(sprintf('%s %s', $now, $startTimeslot->timeslot));
				$end = strtotime(sprintf('%s %s', $now, $endTimeslot->timeslot));
				$current = strtotime(sprintf('%s %s', $now, $timeslot->timeslot));
				if($current >= $start && $end >= $current){
					if($timeslot->outOfStock){
						$result = false;
						break;
					}
				}
			} else if($timeslot->timeslot === $startTimeslot->timeslot){
				if($timeslot->outOfStock){
					$result = false;
					break;
				}
			}
		}
		return $result;
	}
	public static function getFirstName($name){
		$result = explode(' ', $name);
		return $result[0];
	}
	public static function getLastName($name){
		$result = explode(' ', $name);
		if(count($result) === 2){
			return $result[1];
		}
		return $name;
	}
	protected function updateWooChanges($orderId){
		$orderRepo = new Calendarista_OrderRepository();
		$orderRepo->updateChanges(array(
			'id'=>$orderId
			, 'discount'=>$this->wooDiscount
			, 'discountMode'=>$this->wooDiscountMode
			, 'tax'=>$this->wooTaxValue
			, 'totalAmount'=>$this->wooTotalAmount
		));
	}
	public function saveDynamicFields($orderId, $project, $availability){
		$repo = new Calendarista_BookedDynamicFieldRepository();
		$dynamicFields = $this->getViewStateValue('dynamicFields');
		if(is_array($dynamicFields)){
			foreach($dynamicFields as $field){
				$repo->insert(array(
					'orderId'=>$orderId
					, 'projectId'=>$project->id
					, 'availabilityId'=>$availability->id
					, 'dynamicFieldId'=>$field['id']
					, 'label'=>$field['label']
					, 'value'=>$field['value']
					, 'cost'=>$field['cost']
					, 'limitBySeat'=>$field['limitBySeat']
					, 'byOptional'=>$field['byOptional']
					, 'fixedCost'=>$field['fixedCost']
				));
			}
		}
	}
	protected function getDynamicFieldSeats(){
		$dynamicFields = $this->getViewStateValue('dynamicFields');
		$seats = 0;
		if(is_array($dynamicFields)){
			foreach($dynamicFields as $field){
				if($field['limitBySeat']){
					$seats += (int)$field['value'];
				}
			}
		}
		return $seats;
	}
	protected function scheduleEmailReminder($orderId){
		return new Calendarista_EmailReminderJob($orderId, $this->projectId);
	}
	protected function googleCalendarSync($bookedAvailabilityId){
		return Calendarista_GoogleCalendarHelper::insertEvent($bookedAvailabilityId);
	}
	public function getUserId($membershipRequired){
		$userId = $this->getViewStateValue('userId');
		$email = $this->getViewStateValue('email');
		if(!isset($userId) && $membershipRequired){
			$user = get_user_by('email', $email);
			return $user->ID;
		}
		return $userId;
	}
	protected function getViewStateValue($key, $default = null){
		return isset($this->viewState) && isset($this->viewState[$key]) ? $this->viewState[$key] : $default;
	}
	protected function deserialize($value){
		return unserialize($this->sanitize($value));
	}
	protected function sanitize($value){
		return stripslashes($value);
	}
	public static function confirmAndNotify($orderId, $confirm = true){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if($generalSetting->autoConfirmOrderAfterPayment){
			$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
			//confirm the appointment as well
			$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
			$bookedAvailabilityRepo->updateStatus((int)$bookedAvailabilityList[0]->id, Calendarista_AvailabilityStatus::APPROVED);
			if($confirm && $generalSetting->notifyBookingConfirmation){
				$notification = new Calendarista_NotificationEmailer(array(
					'orderId'=>$orderId
					, 'emailType'=>Calendarista_EmailType::BOOKING_CONFIRMATION
				));
				$notification->send();
			}
		}
	}
	public static function paymentRequiredNotify($orderId){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if($generalSetting->autoInvoiceNotification){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>$orderId
				, 'emailType'=>Calendarista_EmailType::PAYMENT_REQUIRED
			));
			$notification->send();
		}
	}
	public static function isHuman(){
		if(isset($_POST['calendarista_ambush']) && $_POST['calendarista_ambush'] != ''){
			//a spam bot has been ambushed, exit.
			return false;
		}
		return true;
	}
	public function inValidData(){
		//check if serialization of viewstate failed?
		if(!$this->viewState || count($this->viewState) == 0){
			return true;
		}
		$availabilityId = $this->getViewStateValue('availabilityId');
		$fromDate = $this->getViewStateValue('availableDate');
		$multiDateSelection = $this->getViewStateValue('multiDateSelection');
		if(!$availabilityId || (!$fromDate && !$multiDateSelection)){
			return true;
		}
		return false;
	}
	public function stockValid(){
		if($this->appointment === 1/*not edit mode*/){
			return true;
		}
		if($this->inValidData()){
			return false;
		}
		$availabilityId = (int)$this->getViewStateValue('availabilityId');
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availability = $availabilityRepo->read($availabilityId);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$fromDate = $this->getViewStateValue('availableDate');
		$toDate = $this->getViewStateValue('endDate');
		$availabilityHelper = new Calendarista_AvailabilityHelper(array(
			'projectId'=>$availability->projectId
			, 'availabilityId'=>$availability->id
			/*, 'clientTime'=>$clientTime
			, 'timezone'=>$timezone*/
		));
		$dateList = array();
		if(in_array($availabilityHelper->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_DATE)){
			$multiDateSelection = $this->getViewStateValue('multiDateSelection');
			$selectedDates = explode(';', $multiDateSelection);
			foreach($selectedDates as $dt){
					$startTime = new Calendarista_Timeslot(array());
					$endTime = new Calendarista_Timeslot(array());
				if($availabilityHelper->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE){
					array_push($dateList, array('fromDate'=>$dt, 'toDate'=>$dt, 'startTimeId'=>null, 'endTimeId'=>null));
				}else{
					$pair = explode(':', $dt);
					$startTime = $timeslotRepo->read((int)$pair[1]);
					if(!$startTime){
						continue;
					}
					array_push($dateList, array('fromDate'=>$pair[0], 'toDate'=>$pair[0], 'startTimeId'=>$startTime->id, 'endTimeId'=>$startTime->id));
				}
			}
		}else{
			array_push($dateList, array('fromDate'=>$fromDate, 'toDate'=>$toDate, 'startTimeId'=>$this->getViewStateValue('startTime'), 'endTimeId'=> $this->getViewStateValue('endTime')));
		}
		foreach($dateList as $dl){
			$fromDate = $dl['fromDate'];
			$toDate = $dl['toDate'];
			$startTimeId = $dl['startTimeId'];
			$endTimeId = $dl['endTimeId'];
			$result = $availabilityHelper->getAllExcludedDates(strtotime($fromDate));
			if(in_array($fromDate, $result['exclusions']) || ($toDate && in_array($toDate, $result['exclusions']))){
				if(!in_array($availabilityHelper->project->calendarMode, array(Calendarista_CalendarMode::PACKAGE))){
					return false;
				}
			}
			if(in_array($fromDate, $result['halfDays']['start']) || ($toDate && in_array($toDate, $result['halfDays']['end']))){
				return false;
			}
			if($toDate){
				//package is a special case, when more than 1 day in a package, the end date can fall on any date so ignore.
				$result = $availabilityHelper->getAllExcludedDates(strtotime($toDate));
				//perhaps toDate falls on a separate month?
				if(in_array($fromDate, $result['exclusions'])){
					return false;
				}
				if(in_array($fromDate, $result['halfDays']['start']) || ($toDate && in_array($toDate, $result['halfDays']['end']))){
					return false;
				}
			}
			$startTime = null;
			$endTime = null;
			if($startTimeId){
				$startTime = $timeslotRepo->read($startTimeId);
				$bookedAvailabilities = $availabilityHelper->getCurrentMonthAvailabilities($fromDate);
				$timeslots = $availabilityHelper->timeslotHelper->getTimeslots($fromDate, $bookedAvailabilities, 0/*start slot*/);
				if($this->timeslotOutOfStock($timeslots, $startTime->timeslot)){
					return false;
				}
				if($endTimeId && !$toDate){
					$endTime = $timeslotRepo->read($endTimeId);
					if($this->timeslotOutOfStock($timeslots, $endTime->timeslot)){
						return false;
					}
				}
			}
			//ToDO: change how we treat end time in single_day_and_time_range & multi_date_and_time_range mode.
			if(($endTimeId && $toDate) && !in_array($availabilityHelper->project->calendarMode, array(
																			Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE
																			, Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE))){
				$endTime = $timeslotRepo->read($endTimeId);
				$bookedAvailabilities = $availabilityHelper->getCurrentMonthAvailabilities($toDate);
				$timeslots = $availabilityHelper->timeslotHelper->getTimeslots($toDate, $bookedAvailabilities, 1/*end slot*/);
				if($this->timeslotOutOfStock($timeslots, $endTime->timeslot)){
					return false;
				}
			}
			if(!$startTime){
				$startTime = new Calendarista_Timeslot(array());
			}
			$dateToValidate = date(CALENDARISTA_FULL_DATEFORMAT, strtotime(trim(sprintf('%s %s'
					, $fromDate
					, $startTime->timeslot))));
			if($dateToValidate == date(CALENDARISTA_FULL_DATEFORMAT, strtotime(null))){
				//1970 ? spam bot surely, exit
				return false;
			}
		}
		return true;
	}
	public function deleteFromStaging(){
		if($this->stagingId){
			$stagingRepo = new Calendarista_StagingRepository();
			$stagingRepo->delete($this->stagingId);
		}
	}
	protected function timeslotOutOfStock($timeslots, $val){
		$result = false;
		$timeslot2 = Calendarista_TimeslotHelper::toLocalFormat($val);
		foreach($timeslots as $key=>$timeslot){
			if($timeslot->timeslot == $timeslot2){
				if($timeslot->outOfStock){
					$result = true;
				}
				break;
			}
		}
		return $result;
	}
	public function getCustomerName(){
		return $this->getViewStateValue('name');
	}
	public function getCustomerEmail(){
		return $this->getViewStateValue('email');
	}
	public function notifyOutOfStock($forcedNotification = false){
		if(!$this->generalSetting->outOfStockNotification && !$forcedNotification){
			return;
		}
		$customerEmail = $this->getViewStateValue('email');
		$customerName = $this->getViewStateValue('name');
		
		$emailer = new Calendarista_OutOfStockEmailer($customerEmail, $customerName);
		$emailer->send();
	}
	public static function getTimeslot($date, $timeId, $availabilityId){
		//when multi booking is enabled, the timeslots belong to the first availability
		//so we query for the others by id.
		if(!$timeId){
			return null;
		}
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$dt = strtotime($date);
		$weekday = (int)date('N', $dt);
		$timeslot = $timeslotRepo->readByTimeslotId((int)$availabilityId, date(CALENDARISTA_DATEFORMAT, $dt), $weekday, $timeId);
		return $timeslot;
	}
	public function getOutOfStockErrorMessage(){
		return $this->stringResources['RACE_CONDITION'];
	}
	public function getWooCommerceOutOfStockErrorMessage(){
		return $this->stringResources['RACE_CONDITION_WOOCOMMERCE'];
	}
	private function formatTimestamp($timestamp) {
		$dt = new DateTime($timestamp);
		return $this->fullDay ? $dt->format(self::D_FORMAT) : $dt->format(self::DT_FORMAT);
	  }
}
?>