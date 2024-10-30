<?php
class Calendarista_NotificationEmailer extends Calendarista_Emailer{
	public $invoiceSettings;
	public $emailType;
	public $orderId;
	public $order;
	public $bookedAvailabilityList;
	public $bookedAvailability;
	public $bookedAvailabilityId;
	public $stringResources;
	public function __construct($args){	
		if(array_key_exists('emailType', $args)){
			$this->emailType = $args['emailType'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = $args['orderId'];
		}
		if(array_key_exists('bookedAvailabilityId', $args)){
			$this->bookedAvailabilityId = $args['bookedAvailabilityId'];
		}
		$repo = new Calendarista_OrderRepository();
		$this->order = $repo->read($this->orderId);
		$repo = new Calendarista_BookedAvailabilityRepository();
		$this->bookedAvailabilityList = $repo->readByOrderId($this->orderId);
		$this->bookedAvailability = $this->bookedAvailabilityList[0];
		if($this->bookedAvailabilityId){
			foreach($this->bookedAvailabilityList as $bal){
				if((int)$bal->id === $this->bookedAvailabilityId){
					$this->bookedAvailability = $bal;
					break;
				}
			}
		}
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->order->projectId);
		parent::__construct($this->emailType);
	}

	public function send($staffEmail = null){
		if($this->orderId && $this->emailSetting->enable){
			$this->toAddress = $this->order->email;
			$this->replyToEmail = $this->order->email;
			$this->replyToSenderName = $this->order->fullName;
			if(in_array($this->emailType, array(
				Calendarista_EmailType::NEW_BOOKING_RECEIVED
				, Calendarista_EmailType::BOOKING_CANCELLED_ALERT
				, Calendarista_EmailType::GOOGLE_CALENDAR_AUTHENTICATION_FAILURE
			))){
				if($staffEmail){
					$this->toAddress = $staffEmail;
				}else{
					$this->toAddress = $this->generalSetting->adminNotificationEmail;
				}
			}
			$replacements = $this->getReplacements($this->order);
			$subject = $this->getEmailSubject($replacements);
			$content = $this->getEmailBody($replacements);
			$content = $this->setLayout($content);
			$this->smtp();
			$headers = array('Content-Type: text/html; charset=UTF-8');
			if(in_array($this->emailType, array(Calendarista_EmailType::NEW_BOOKING_RECEIVED)))
			{
				array_push($headers,  sprintf('Reply-To: %s <%s>', $this->getUtfEncoded($this->replyToSenderName), $this->replyToEmail));
			}
			add_filter('wp_mail_from', array($this, 'getSenderAddress'));
			add_filter('wp_mail_from_name',array($this, 'getSenderName'));
			
			if (!function_exists('wp_mail')){
				require_once ABSPATH . 'wp-includes/pluggable.php';
			}
			$result = wp_mail($this->toAddress, $this->getUtfEncoded($subject), $content, $headers);
			
			remove_filter('wp_mail_from', array($this, 'getSenderAddress'));
			remove_filter('wp_mail_from_name',array($this, 'getSenderName'));
			
			do_action('calendarista_after_send_notification', $this->order->id, $this->emailType);
			
			$this->logErrorIfAny($result);
			return $result;
		}
		return false;
	}
	public function getSenderAddress(){
		return $this->generalSetting->senderEmail;
	}
	
	public function getSenderName(){
		return $this->getUtfEncoded($this->generalSetting->emailSenderName);
	}
	protected function getEmailSubject($replacements){
		return $this->mustacheEngine->render($this->emailSetting->subject, $replacements);
	}
	protected function getEmailBody($replacements){
		return $this->mustacheEngine->render($this->emailSetting->content, $replacements);
	}
	public function getReplacements($order){
		$handlerUrl = add_query_arg(array('calendarista_handler'=>'add_to_calendar', 'orderId'=>$order->id), site_url());
		$googleUrlFeed = count($this->bookedAvailabilityList) === 1 ? Calendarista_AddToCalendarButtonHelper::google($this->bookedAvailabilityList) : null;
		$ical = sprintf('<a href="%s">%s</a>', $handlerUrl, __('iCal', 'calendarista'));
		$outlook = sprintf('<a href="%s">%s</a>', $handlerUrl, __('Outlook', 'calendarista'));
		$google = $googleUrlFeed ? sprintf('<a href="%s">%s</a>', $googleUrlFeed, __('Google', 'calendarista')) : null;
		$map = self::getMap($order->id);
		$waypoints = self::getWaypoints($order->id);
		$mapLink = self::getMapLink($map, $waypoints);
		$timeUnitLabels = Calendarista_StringResources::getTimeUnitLabels($this->stringResources);
		$optionals = self::getOptionals($order->id);
		$optionalsWithCost = self::getOptionalsWithCost($order);
		$customFormElements = self::getCustomFormElements($order->id);
		$dynamicFields = self::getDynamicFields($order->id);
		$serviceProviderName = self::getServiceProviderName($this->bookedAvailability->availabilityId);
		$datesArgs = $this->getDates();
		$depositValue = $order->depositMode ? self::formatCurrency($order->deposit, $order) : $order->deposit . '%';
		$depositAmount = self::formatCurrency($order->totalAmount, $order);
		$taxValue = 0;
		if($order->tax > 0 && $order->totalAmount > 0){
			$taxValue = $order->totalAmount - ($order->totalAmount / (($order->tax/100) + 1));
		}
		$totalCostValue = ($order->deposit > 0 && !$order->upfrontPayment) ? ($order->totalAmount + $order->balance) : $order->totalAmount;
		$totalAmountBeforeTax = $totalCostValue;
		if($taxValue){
			$totalAmountBeforeTax -= $taxValue;
		}
		$formattedTaxValue = self::formatCurrency($taxValue, $order);
		$cancelPageUrl = $this->getCancelPageUrl();
		if($cancelPageUrl){
			$cancelPageUrl = esc_url_raw(add_query_arg(array('calendarista_cancel_key'=>$order->secretKey, 'start_date'=>$this->getListValue($datesArgs, 'plainStartDate'), 'end_date'=>$this->getListValue($datesArgs, 'endDate')), $cancelPageUrl));
		}
		
		$gdprPageUrl = $this->generalSetting->enableGDPR ? Calendarista_GdprEmailer::getGdprPageUrl() : null;
		if($gdprPageUrl){
			$authRepo = new Calendarista_AuthRepository();
			$result = $authRepo->readByEmail($order->email);
			$gdprPageUrl = $result ? 
					esc_url_raw(add_query_arg(array('email'=>$result['userEmail'], 'password'=>$result['password']), $gdprPageUrl)) : null;
		} 
		$paymentDate = $order->paymentDate ? $order->paymentDate->format(CALENDARISTA_FULL_DATEFORMAT) : null;
		$appointmentManagementUrl = admin_url() . 'admin.php?page=calendarista-appointments&calendarista-tab=1&invoiceId=' . $order->invoiceId;
		$couponDiscount = null;
		if($order->couponCode){
			$couponDiscount =  $order->discountMode ? 
				Calendarista_MoneyHelper::toLongCurrency($order->discount) : 
				Calendarista_MoneyHelper::toDouble($order->discount) . '%';
		}
		$result = array(
			'invoice_id'=>$order->invoiceId
			, 'customer_name'=>$order->fullName
			, 'customer_email'=>$order->email
			, 'service_name'=>Calendarista_StringResourceHelper::decodeString($order->projectName)
			, 'availability_name'=>$this->getAvailabilityNames()
			, 'service_provider_name'=>$serviceProviderName
			, 'start_datetime'=>$this->getListValue($datesArgs, 'startDate')
			, 'start_date'=>$this->getListValue($datesArgs, 'plainStartDate')
			, 'start_time'=>$this->getListValue($datesArgs, 'startTime')
			, 'if_has_end_date'=>$this->hasEndDate()
			, 'end_datetime'=>$this->getListValue($datesArgs, 'endDate')
			, 'end_date'=>$this->getListValue($datesArgs, 'plainEndDate')
			, 'end_time'=>$this->getListValue($datesArgs, 'endTime')
			, 'if_has_group_booking'=>(int)$this->bookedAvailability->seats > 1
			, 'booked_seats_count'=>(int)$this->bookedAvailability->seats
			, 'if_has_from_address'=>$map && $map->fromAddress
			, 'from_address'=>$map && $map->fromAddress ? $map->fromAddress : null
			, 'if_has_waypoints'=>$waypoints !== null
			, 'stops'=>$waypoints
			, 'if_has_to_address'=>$map && $map->toAddress
			, 'to_address'=>$map && $map->toAddress ? $map->toAddress : null
			, 'if_has_distance'=>$map && (float)$map->distance > 0
			, 'distance'=>$map && $map->distance ? Calendarista_MoneyHelper::toDouble((float)$map->distance) : null
			, 'if_has_duration'=>$map && (float)$map->duration > 0
			, 'duration'=>$map && $map->duration ? Calendarista_TimeHelper::secondsToTime((float)$map->duration, $timeUnitLabels) : null
			, 'unitType'=>$map && (int)$map->unitType === 0 ? 'km' : 'miles'
			, 'if_has_optionals'=>strlen($optionals) > 0
			, 'optionals'=>$optionals
			, 'optionalsWithCost'=>$optionalsWithCost
			, 'if_has_custom_form_fields'=>strlen($customFormElements) > 0
			, 'custom_form_fields'=>$customFormElements
			, 'if_has_cost'=>$order->totalAmount > 0
			, 'total_amount_paid'=>self::formatCurrency($order->totalAmount, $order)
			, 'if_has_return_trip'=>false
			, 'total_cost_value'=>self::formatCurrency($totalCostValue, $order)
			, 'if_has_deposit'=>!$order->upfrontPayment && $order->deposit > 0
			, 'deposit_amount'=>$depositAmount
			, 'deposit'=>$depositValue
			, 'if_has_balance'=>$order->balance > 0
			, 'balance_amount'=>self::formatCurrency($order->balance, $order)
			, 'if_has_tax'=>$order->tax > 0
			, 'tax'=>$formattedTaxValue
			, 'tax_rate'=>$order->tax . '%'
			, 'site_name'=>htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES)
			, 'if_cancel_booking_enabled'=>$cancelPageUrl !== null
			, 'cancel_page_url'=>$cancelPageUrl
			, 'if_gdpr_enabled'=>!empty($gdprPageUrl)
			, 'gdpr_page_url'=>$gdprPageUrl
			, 'if_has_map_link'=>$mapLink !== null
			, 'map_link'=>$mapLink
			, 'if_has_payment_date'=>$paymentDate !== null
			, 'payment_date'=>$paymentDate
			, 'payment_operator'=>$order->paymentOperator
			, 'if_has_dynamic_fields'=>strlen($dynamicFields) > 0
			, 'dynamic_fields'=>$dynamicFields
			, 'add_to_ical_link'=>$ical
			, 'add_to_outlook_link'=>$outlook
			, 'add_to_google_link'=>$google
			, 'appointment_management_url'=>$appointmentManagementUrl
			, 'if_has_coupon_discount'=>$order->couponCode ? true : false
			, 'coupon_code'=>$order->couponCode
			, 'coupon_discount'=>$couponDiscount
			, 'total_amount_before_tax'=>self::formatCurrency($totalAmountBeforeTax, $order)
			, 'if_paid_upfront_full_amount'=>$order->upfrontPayment
			, 'upfront_payment_total'=>$this->getUpfrontTotalAmount($order)
		);
		$result[sprintf('if_service_id_%d', $order->projectId)] = function($text, $engine){
			return $engine->render($text);
		};
		$result[sprintf('if_availability_id_%d', $order->availabilityId)] = function($text, $engine){
			return $engine->render($text);
		};
		return $result;
	}
	public function getUpfrontTotalAmount($order){
		if($order->upfrontPayment){
			return sprintf('%s'
				, Calendarista_MoneyHelper::toLongCurrency($order->totalAmount));
		}
	}
	protected function getListValue($args, $key, $sep = '; ', $del = ','){
		$result = '';
		$i = 0;
		foreach($args as $arg){
			$i++;
			if(!isset($arg[$key])){
				continue;
			}
			if(count($args) > 1){
				$result .= $arg['name'] . ': ';
			}
			if(isset($arg[$key])){
				$result .= implode($del, $arg[$key]);
				if(count($args) > 1 && $i < count($args)){
					$result .= $sep;
				}
			}
		}
		return $result;
	}
	protected function hasEndDate(){
		$result = false;
		$startDate = date(CALENDARISTA_DATEFORMAT, strtotime($this->bookedAvailability->fromDate));
		$endDate = $this->bookedAvailability->toDate ? date(CALENDARISTA_DATEFORMAT, strtotime($this->bookedAvailability->toDate)) : null;
		$hasEndTime = !in_array($this->bookedAvailability->endTimeId, array(null, '0', '-1'));
		if(!$endDate){
			return false;
		}
		$calendarMode = (int)$this->bookedAvailability->calendarMode;
		switch($calendarMode){
			case 1://SINGLE_DAY_AND_TIME
			case 9://SINGLE_DAY_AND_TIME_WITH_PADDING 
				if($hasEndTime){
					$result = true;
				}
				break;
			case 2://SINGLE_DAY_AND_TIME_RANGE
				$result = true;
				break;
			case 3://MULTI_DATE_RANGE
				if($startDate != $endDate){
					$result = true;
				}
				break;
			case 4://MULTI_DATE_AND_TIME_RANGE
			case 5://CHANGEOVER 
			case 6://PACKAGE
			case 7://ROUND_TRIP
				if($startDate != $endDate){
					$result = true;
				}
				break;
			case 8://ROUND_TRIP_WITH_TIME
				if($hasEndTime){
					$result = true;
				}
				break;
		}
		return $result;
	}
	protected function getAvailabilityNames(){
		$result = array();
		foreach($this->bookedAvailabilityList as $bookedAvailability){
			array_push($result, Calendarista_StringResourceHelper::decodeString($bookedAvailability->availabilityName));
		}
		return implode(',', $result);
	}
	protected function getDates(){
		$result = array();
		$fullDateFormat = Calendarista_TimeHelper::getDateFormat();
		foreach($this->bookedAvailabilityList as $bookedAvailability){
			if($this->bookedAvailabilityId && (int)$bookedAvailability->id !== $this->bookedAvailabilityId){
				continue;
			}
			$supportsTimeslots = in_array((int)$bookedAvailability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
			$dateFormat = $supportsTimeslots ? Calendarista_TimeHelper::getFullDateFormat() : Calendarista_TimeHelper::getDateFormat();
			$timeUnitLabels = Calendarista_StringResources::getTimeUnitLabels($this->stringResources);
			$startDate = Calendarista_TimeHelper::timezone(array(
				'timezone'=>null
				, 'serverTimezone'=>null
				, 'time'=>$bookedAvailability->fromDate
				, 'format'=>$dateFormat
			));
			$endDate = Calendarista_TimeHelper::timezone(array(
				'timezone'=>null
				, 'serverTimezone'=>null
				, 'time'=>$bookedAvailability->toDate
				, 'format'=>$dateFormat
			));
			$startDateParts = null;
			$plainStartDate = null;
			$startTime = null;
			$plainEndDate = null;
			$endTime = null;
			
			switch($fullDateFormat){
				case 'j. n. Y':
				$startDateParts = explode('. ', $startDate);
				$lastPart = explode(' ', $startDateParts[2]);
				$plainStartDate = $startDateParts[0] . '. ' .  $startDateParts[1] . '. ' . $lastPart[0];
				if(count($lastPart) > 1){
					$startTime = $lastPart[1];
				}
				if(in_array((int)$bookedAvailability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
					$endDateParts = explode('. ', $endDate);
					$lastPart = explode(' ', $endDateParts[2]);
					$plainEndDate = $endDateParts[0] . '. ' .  $endDateParts[1] . '. ' . $lastPart[0];
					if(count($lastPart) > 1){
						$endTime = $lastPart[1];
					}
				}
				break;
				case 'l, j F, Y':
				$startDateParts = explode(', ', $startDate);
				$lastPart = explode(' ', $startDateParts[2]);
				$plainStartDate = $startDateParts[0] . ', ' . $startDateParts[1] . ', ' . $lastPart[0];
				if(count($lastPart) > 1){
					$startTime = $lastPart[1];
				}
				if(in_array((int)$bookedAvailability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
					$endDateParts = explode(', ', $endDate);
					$lastPart = explode(' ', $endDateParts[2]);
					$plainEndDate = $endDateParts[0] . ', ' .  $endDateParts[1] . ', ' . $lastPart[0];
					if(count($lastPart) > 1){
						$endTime = $lastPart[1];
					}
				}
				break;
				case 'j M, y':
				$startDateParts = explode(', ', $startDate);
				$lastPart = explode(' ', $startDateParts[1]);
				$plainStartDate = $startDateParts[0] . ', ' . $lastPart[0];
				if(count($lastPart) > 1){
					$startTime = $lastPart[1];
				}
				if(in_array((int)$bookedAvailability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
					$endDateParts = explode(', ', $endDate);
					$lastPart = explode(' ', $endDateParts[1]);
					$plainEndDate = $endDateParts[0] . ', ' . $lastPart[0];
					if(count($lastPart) > 1){
						$endTime = $lastPart[1];
					}
				}
				break;
				default:
				$startDateParts = explode(' ', $startDate);
				$plainStartDate = $startDateParts[0];
				if(in_array((int)$bookedAvailability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
					$startTime = $startDateParts[1];
					$endDateParts = explode(' ', $endDate);
					$plainEndDate = $endDateParts[0];
					$endTime = $endDateParts[1];
				}
				break;
				
			}
			
			if(!isset($result[$bookedAvailability->availabilityId])){
				$result[$bookedAvailability->availabilityId] = array('name'=>$bookedAvailability->availabilityName);
			}
			if(!isset($result[$bookedAvailability->availabilityId]['startDate'])){
				$result[$bookedAvailability->availabilityId]['startDate'] = array();
			}
			array_push($result[$bookedAvailability->availabilityId]['startDate'], $startDate);
			if(!isset($result[$bookedAvailability->availabilityId]['endDate'])){
				$result[$bookedAvailability->availabilityId]['endDate'] = array();
			}
			array_push($result[$bookedAvailability->availabilityId]['endDate'], $endDate);
			if(!isset($result[$bookedAvailability->availabilityId]['plainStartDate'])){
				$result[$bookedAvailability->availabilityId]['plainStartDate'] = array();
			}
			array_push($result[$bookedAvailability->availabilityId]['plainStartDate'], $plainStartDate);
			if(!isset($result[$bookedAvailability->availabilityId]['plainEndDate'])){
				$result[$bookedAvailability->availabilityId]['plainEndDate'] = array();
			}
			array_push($result[$bookedAvailability->availabilityId]['plainEndDate'], $plainEndDate);
			if(!isset($result[$bookedAvailability->availabilityId]['startTime'])){
				$result[$bookedAvailability->availabilityId]['startTime'] = array();
			}
			array_push($result[$bookedAvailability->availabilityId]['startTime'], $startTime);
			if(!isset($result[$bookedAvailability->availabilityId]['endTime'])){
				$result[$bookedAvailability->availabilityId]['endTime'] = array();
			}
			array_push($result[$bookedAvailability->availabilityId]['endTime'], $endTime);
		}
		return $result;
	}
	protected function getCancelPageUrl(){
		if(!$this->generalSetting->enableUserCancelBooking){
			return null;
		}
		if($this->generalSetting->cancelBookingUrl){
			return $this->generalSetting->cancelBookingUrl;
		}
		Calendarista_PermissionHelper::wpIncludes();
		if (!function_exists('get_page_permastruct')){
			require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
			$GLOBALS['wp_rewrite'] = new WP_Rewrite();
		}
		if (!function_exists('get_page_link')){
			require_once ABSPATH . WPINC . '/link-template.php ';
		}
		$pages = new WP_Query(array( 
			'meta_key'=>CALENDARISTA_META_KEY_NAME
			, 'post_type'=>'page'
		));
		$pageId = null;
		foreach($pages->posts as $page){
			$result = get_post_meta($page->ID, CALENDARISTA_META_KEY_NAME, true);
			if($result != '' && (int)$result == 1/*cancel page identifier*/){
				$pageId = $page->ID;
				break;
			}
		}
		if($pageId){
			return get_page_link($pageId);
		}
		return null;
	}
	public static function getMapLink($map, $waypoints){
		if(!$map){
			return null;
		}
		$placeLink = 'https://www.google.com/maps/place/%s';
		$directionLink = 'https://www.google.com/maps/dir/?api=1&origin=%s&destination=%s&travelmode=driving&waypoints=%s';
		$origin = $map->fromAddress ? $map->fromAddress : null; 
		$destination = $map->toAddress ? $map->toAddress : null;
		$points = $waypoints ? implode('|', explode('<br>', $waypoints)) : null;
		if($origin && $destination === null){
			return sprintf($placeLink, $origin);
		}else if($origin && $destination){
			return sprintf($directionLink, $origin, $destination, $points);
		}
		return null;
	}
	public static function getMap($orderId){
		$repo = new Calendarista_BookedMapRepository();
		return $repo->readByOrderId($orderId);
	}
	public static function getWaypoints($orderId){
		$repo = new Calendarista_BookedWaypointRepository();
		$waypoints = $repo->readByOrderId($orderId);
		$result = array();
		foreach($waypoints as $w){
			$waypoint = (array)$w;
			array_push($result, $waypoint['address']);
		}
		if(count($result) === 0){
			return null;
		}
		return implode('<br>', $result);
	}
	public static function getOptionals($orderId){
		$repo = new Calendarista_OrderRepository();
		$order = $repo->read($orderId);
		$stringResources = Calendarista_StringResourceHelper::getResource($order->projectId);
		$repo = new Calendarista_BookedOptionalRepository();
		$optionalGroups = $repo->readAll($orderId);
		$result = array();
		foreach($optionalGroups as $key=>$value){
			$optionals = array();
			foreach($value as $optional){
				$name = Calendarista_StringResourceHelper::decodeString($optional->name);
				if($optional->incrementValue > 0){
					$name = sprintf('%s - <i class="calendarista-quantity">%s</i>', $name, sprintf($stringResources['BOOKING_OPTIONAL_QUANTITY_LABEL'], $optional->incrementValue));
				}
				array_push($optionals, $name);
			}
			if(count($optionals) > 0){
				array_push($result, sprintf('<strong>%s</strong> —<i>%s</i>'
						, $key, implode(', ', $optionals)));
			}
		}
		return implode('<br>', $result);
	}
	public static function getOptionalsWithCost($order){
		$stringResources = Calendarista_StringResourceHelper::getResource($order->projectId);
		$repo = new Calendarista_BookedOptionalRepository();
		$optionalGroups = $repo->readAll($order->id);
		$result = array();
		foreach($optionalGroups as $key=>$value){
			$optionals = array();
			foreach($value as $optional){
				$name = Calendarista_StringResourceHelper::decodeString($optional->name);
				if($optional->incrementValue > 0){
					$name = sprintf('%s - <i class="calendarista-quantity">%s</i>', $name, sprintf($stringResources['BOOKING_OPTIONAL_QUANTITY_LABEL'], $optional->incrementValue));
				}
				$cost = Calendarista_MoneyHelper::formatCurrencySymbol(sprintf('%g', $optional->cost), true);
				array_push($optionals, sprintf('%s - (%s)', $name, $cost));
			}
			if(count($optionals) > 0){
				array_push($result, sprintf('<strong>%s</strong> —<i>%s</i>'
						, $key, implode(', ', $optionals)));
			}
		}
		return implode('<br>', $result);
	}
	public static function getCustomFormElements($orderId){
		$repo = new Calendarista_BookedFormElementRepository();
		$formElements = $repo->readAll($orderId);
		$result = array();
		foreach($formElements as $formElement){
			array_push($result, sprintf('<strong>%s</strong> —<i>%s</i>'
				, Calendarista_StringResourceHelper::decodeString($formElement->label)
				, Calendarista_StringResourceHelper::decodeString($formElement->value)));
		}
		return implode('<br>', $result);
	}
	public static function getDynamicFields($orderId){
		$dynamicFieldRepo = new Calendarista_BookedDynamicFieldRepository();
		$dynamicFields = $dynamicFieldRepo->readByOrderId($orderId);
		$result = array();
		foreach($dynamicFields as $field){
			array_push($result, sprintf('<strong>%s</strong> —<i>%s</i>', Calendarista_StringResourceHelper::decodeString($field['label']), $field['value']));
		}
		return implode('<br>', $result);
	}
	public static function getServiceProviderName($availabilityId){
		$repo = new Calendarista_StaffRepository();
		$staff = $repo->readAll(array('availabilityId'=>$availabilityId));
		if($staff !== false && $staff['total'] > 0){
			return $staff['items'][0]['name'];
		}
		$userInfo = Calendarista_PermissionHelper::getUserById(1);
		$adminName = null;
		if($userInfo){
			$adminName = $userInfo->last_name;
			if($userInfo->first_name){
				if($adminName){
					$adminName .= ' ';
				}
				$adminName .= $userInfo->first_name;
			}
		}
		return $adminName ? $adminName : __('Admin', 'calendarista');
	}
	public static function formatCurrency($value, $order, $shortFormat = false){
		return Calendarista_MoneyHelper::formatCurrencySymbol(Calendarista_MoneyHelper::toDouble($value), $shortFormat, $order->currency, $order->currencySymbol);
	}
}
?>