<?php
class Calendarista_NetworkHelper{
	
	public static function isLocalhost(){
		return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'));
	}
    private function __construct() { }
}
?>