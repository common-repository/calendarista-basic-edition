<?php
class Calendarista_EmailReminderJob{
	const HOOK = 'Calendarista_EmailReminderJobEventHook';
	public function __construct($orderId, $projectId, $bookedAvailabilityId = null)
	{
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if (!$generalSetting->reminderAltCronJob){
			return;
		}
		if($orderId && $projectId){
			$projectRepo = new Calendarista_ProjectRepository();
			$project = $projectRepo->read($projectId);
			if($project && ($project->reminder || $project->thankyouReminder)){
				$this->schedule($project->reminder, $project->thankyouReminder, $orderId, $bookedAvailabilityId);
			}
		}
	}
	public function schedule($reminder, $thankyouReminder, $orderId, $bookedAvailabilityId = null){
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
		$timezone = get_option('timezone_string');
		$originalTimezone = null;
		if($timezone){
			$originalTimezone = Calendarista_TimeHelper::setTimezone($timezone);
		}
		foreach($bookedAvailabilityList as $bookedAvailability){
			if((int)$bookedAvailability->status === Calendarista_AvailabilityStatus::CANCELLED){
				return;
			}
			if($bookedAvailabilityId && (int)$bookedAvailability->id !== $bookedAvailabilityId){
				continue;
			}
			if($reminder){
				$scheduleDate = new Calendarista_DateTime($bookedAvailability->fromDate);
				$now = new Calendarista_DateTime();
				$scheduleDate->modify("-{$reminder} minutes");
				if ($scheduleDate > $now && !self::schedulePending($orderId, (int)$bookedAvailability->id, 0/*reminder type*/))
				{
					$result = wp_schedule_single_event(strtotime($scheduleDate->format('Y-m-d H:i:s'))/*time() + 30*/, self::HOOK, array($orderId, (int)$bookedAvailability->id, 0/*reminderType appointment*/));
				}
			}
			if($thankyouReminder){
				//thankyou
				$scheduleDate = new Calendarista_DateTime($bookedAvailability->toDate);
				$now = new Calendarista_DateTime();
				$scheduleDate->modify("+{$thankyouReminder} minutes");
				if ($scheduleDate > $now && !self::schedulePending($orderId, (int)$bookedAvailability->id, 1/*reminder type*/))
				{
					$result = wp_schedule_single_event(strtotime($scheduleDate->format('Y-m-d H:i:s'))/*time() + 30*/, self::HOOK, array($orderId, (int)$bookedAvailability->id, 1/*reminderType thankyou*/));
				}
			}
		}
		if($originalTimezone){
			Calendarista_TimeHelper::setTimezone($originalTimezone);
		}
	}
	public static function init($orderId, $bookedAvailabilityId, $reminderType = 0/*reminderType = appointment*/){
		if(!$orderId || !$bookedAvailabilityId){
			return;
		}
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailability = $bookedAvailabilityRepo->read($bookedAvailabilityId);
		if((int)$bookedAvailability->status === Calendarista_AvailabilityStatus::CANCELLED){
			return;
		}
		if($reminderType === 0){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>(int)$orderId
				, 'emailType'=>Calendarista_EmailType::BOOKING_REMINDER
				, 'bookedAvailabilityId'=>(int)$bookedAvailabilityId
			));
			$notification->send();
			self::sendSMS($notification);
		}else{
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>(int)$orderId
				, 'emailType'=>Calendarista_EmailType::BOOKING_THANKYOU_REMINDER
				, 'bookedAvailabilityId'=>(int)$bookedAvailabilityId
			));
			$notification->send();
		}
		$reminderRepo = new Calendarista_RemindersRepository();
		$reminderRepo->insert(new Calendarista_EmailReminder(array(
			'fullName'=>$notification->order->fullName
			, 'email'=>$notification->order->email
			, 'sentDate'=>new Calendarista_DateTime()
			, 'bookedAvailabilityId'=>(int)$bookedAvailabilityId
			, 'orderId'=>(int)$orderId
			, 'projectId'=>$notification->order->projectId
			, 'reminderType'=>$reminderType
		)));
	}
	public static function resendReminder($orderId, $bookedAvailabilityId, $reminderType){
		if(!$orderId || !$bookedAvailabilityId){
			return;
		}
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailability = $bookedAvailabilityRepo->read($bookedAvailabilityId);
		if((int)$bookedAvailability->status === Calendarista_AvailabilityStatus::CANCELLED){
			return;
		}
		if($reminderType === 0){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>(int)$orderId
				, 'emailType'=>Calendarista_EmailType::BOOKING_REMINDER
				, 'bookedAvailabilityId'=>(int)$bookedAvailabilityId
			));
			$notification->send();
			self::sendSMS($notification);
		} else{
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>(int)$orderId
				, 'emailType'=>Calendarista_EmailType::BOOKING_THANKYOU_REMINDER
				, 'bookedAvailabilityId'=>(int)$bookedAvailabilityId
			));
			$notification->send();
		}
	}
	public static function sendSMS($notification){
		$setting = Calendarista_GeneralSettingHelper::get();
		if(!$setting->hasTwilio()){
			return;
		}
		$repo = new Calendarista_BookedFormElementRepository();
		$formElements = $repo->getPhoneNumber($notification->orderId);
		if($formElements){
			$twilioHelper = new Calendarista_TwilioHelper();
			$replacements = $notification->getReplacements($notification->order);
			if(!class_exists('Mustache_Engine')){
				require_once CALENDARISTA_MUSTACHE . 'Autoloader.php';
				Mustache_Autoloader::register();
			}
			$mustacheEngine = new Mustache_Engine();
			$content = $mustacheEngine->render($setting->smsContentTemplate, $replacements);
			if(is_array($formElements) && count($formElements) > 0){
				$twilioHelper->sendMessage($content, $formElements[0]->phoneNumber);
			}
		}
	}
	public static function register(){
		add_action(self::HOOK, array('Calendarista_EmailReminderJob', 'init'), 10, 3);
	}
	public static function getSchedulesCount(){
		$crons = _get_cron_array();
		$result = 0;
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK)!== false){
					++$result;
				}
			}
		}
		return $result;
	}
	public static function cancelScheduleByAvailability($availabilityId){
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				$args = $params[key($params)]['args'];
				if(!isset($args[1])){
					continue;
				}
				if(strpos($hook, self::HOOK)!== false && $args[1] == $availabilityId){
					wp_clear_scheduled_hook($hook, $args);
					break 2;
				}
			}
		}
	}
	public static function cancelSchedule($orderId){
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				$args = $params[key($params)]['args'];
				if(strpos($hook, self::HOOK)!== false && $args[0] == $orderId){
					wp_clear_scheduled_hook($hook, $args);
					break 2;
				}
			}
		}
	}
	public static function cancelAllSchedules(){
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK)!== false){
					wp_clear_scheduled_hook($hook, $params[key($params)]['args']);
				}
			}
		}
	}
	public static function schedulePending($orderId, $bookedAvailabilityId, $reminderType = 0/*appointment*/){
		//orderId will be phased out soon.
		$result = false;
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK) !== false){
					$args = $params[key($params)]['args'];
					if(isset($args[1])){
						if($args[1] == $bookedAvailabilityId){
							if(isset($args[2]) && $args[2] != $reminderType){
								break;
							}
							$result = true;
							break 2;
						}
					}else if($args[0] == $orderId){
						if(isset($args[2]) && $args[2] != $reminderType){
							break;
						}
						$result = true;
						break 2;
					}
				}
			}
		}
		return $result;
	}
}
?>