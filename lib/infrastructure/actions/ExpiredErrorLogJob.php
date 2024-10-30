<?php
class Calendarista_ExpiredErrorLogJob{
	const HOOK = 'Calendarista_ExpiredErrorLogJobHook';
	//to instantiate, simply call register. Self contained schedule
	public function __construct(){
		if (!wp_next_scheduled(self::HOOK)){
			wp_schedule_event(time(), 'daily', self::HOOK);
		}
	}
	public static function init(){
		$repo = new Calendarista_ErrorLogRepository();
		$lastTenDays = new Calendarista_DateTime();
		$expiryDate = $lastTenDays->modify('-10 day');
		$result = $repo->deleteExpired($expiryDate->format(CALENDARISTA_DATEFORMAT));
	}
	public static function register(){
		add_action(self::HOOK, array('Calendarista_ExpiredErrorLogJob', 'init'));
	}
	public static function cancelAllSchedules(){
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK)!== false){
					wp_clear_scheduled_hook($hook);
				}
			}
		}
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
}

?>