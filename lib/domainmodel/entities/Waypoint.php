<?php
class Calendarista_Waypoint extends Calendarista_EntityBase{
	public $id;
	public $projectId;
	public $mapId;
	public $lat;
	public $lng;
	public $address;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('mapId', $args)){
			$this->mapId = (int)$args['mapId'];
		}
		if(array_key_exists('lat', $args)){
			$this->lat = (string)$args['lat'];
		}
		if(array_key_exists('lng', $args)){
			$this->lng = (string)$args['lng'];
		}
		if(array_key_exists('address', $args)){
			$this->address = (string)$args['address'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'mapId'=>$this->mapId
			, 'lat'=>$this->lat
			, 'lng'=>$this->lng
			, 'address'=>$this->address
		);
	}
}
?>