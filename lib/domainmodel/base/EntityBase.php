<?php
class Calendarista_EntityBase
{
	public function encode($value){
		return htmlspecialchars($value, ENT_QUOTES);
	}
	public function decode($value){
		return htmlspecialchars_decode($value, ENT_QUOTES);
	}
}
?>