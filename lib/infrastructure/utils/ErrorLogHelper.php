<?php
class Calendarista_ErrorLogHelper
{
	private static $errorLogRepo;
	public static function insert($message)
	{
		if (!self::$errorLogRepo) {
			self::$errorLogRepo = new Calendarista_ErrorLogRepository();
        }
		return self::$errorLogRepo->insert(new Calendarista_ErrorLog(array('message'=>$message)));
	}
	
}
?>