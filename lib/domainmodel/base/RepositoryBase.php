<?php
class Calendarista_RepositoryBase
{
	public function encode($value){
		return htmlspecialchars($value, ENT_QUOTES);
	}
	public function decode($value){
		return htmlspecialchars_decode($value, ENT_QUOTES);
	}
	public function serializeData($data){
		return base64_encode(serialize($data));
	}
	public function unserializeData($data){
		$encodedData = base64_decode($data, true);
		$result = @unserialize($encodedData !== false ? $encodedData : $data);
		if($result === false){
			return $encodedData;
		}
		return $result;
	}
}
?>