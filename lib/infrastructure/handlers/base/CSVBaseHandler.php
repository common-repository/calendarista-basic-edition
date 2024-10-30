<?php 
	class Calendarista_CSVBaseHandler
	{
		public function __construct(){}
		function encode($value) {
			if(strpos($value, '"') !== false || 
				strpos($value, "\n") !== false) 
			{
				$value = str_replace('"', '""', $value);
				$value = str_replace("\n", '', $value);
			}
			return '"' . $value . '"';
		}
	}
?>
