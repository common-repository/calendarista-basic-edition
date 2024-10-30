<?php
class Calendarista_RegisterJobs{
	public function __construct(){
		Calendarista_EmailReminderJob::register();
		Calendarista_ExpiredErrorLogJob::register();
	}
}
?>
