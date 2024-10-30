<?php
class Calendarista_Place extends Calendarista_EntityBase{
	public $id;
	public $projectId;
	public $orderIndex;
	public $mapId;
	public $placeType;
	public $lat;
	public $lng;
	public $name;
	public $markerIcon;
	public $markerIconWidth;
	public $markerIconHeight;
	public $infoWindowTitle;
	public $infoWindowIcon;
	public $infoWindowDescription;
	public $cost;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex = (int)$args['orderIndex'];
		}
		if(array_key_exists('mapId', $args)){
			$this->mapId = (int)$args['mapId'];
		}
		if(array_key_exists('placeType', $args)){
			$this->placeType = (int)$args['placeType'];
		}
		if(array_key_exists('lat', $args)){
			$this->lat = (string)$args['lat'];
		}
		if(array_key_exists('lng', $args)){
			$this->lng = (string)$args['lng'];
		}
		if(array_key_exists('name', $args)){
			$this->name = stripcslashes((string)$args['name']);
		}
		if(array_key_exists('markerIcon', $args)){
			$this->markerIcon = (string)$args['markerIcon'];
		}
		if(array_key_exists('markerIconWidth', $args)){
			$this->markerIconWidth = (int)$args['markerIconWidth'];
		}
		if(array_key_exists('markerIconHeight', $args)){
			$this->markerIconHeight = (int)$args['markerIconHeight'];
		}
		if(array_key_exists('infoWindowTitle', $args)){
			$this->infoWindowTitle = $this->decode((string)$args['infoWindowTitle']);
		}
		if(array_key_exists('infoWindowIcon', $args)){
			$this->infoWindowIcon = (string)$args['infoWindowIcon'];
		}
		if(array_key_exists('infoWindowDescription', $args)){
			$this->infoWindowDescription = stripcslashes((string)$args['infoWindowDescription']);
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('placeId', $args) && $args['placeId'] !== ''){
			$this->id = (int)$args['placeId'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	protected function init(){
		$this->infoWindowTitle = Calendarista_TranslationHelper::t('place_info_window_title_' . $this->id, $this->infoWindowTitle);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('place_info_window_title_' . $this->id, $this->infoWindowTitle);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('place_info_window_title_' . $this->id);
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'orderIndex'=>$this->orderIndex
			, 'mapId'=>$this->mapId
			, 'placeType'=>$this->placeType
			, 'lat'=>$this->lat
			, 'lng'=>$this->lng
			, 'name'=>$this->name
			, 'markerIcon'=>$this->markerIcon
			, 'markerIconWidth'=>$this->markerIconWidth
			, 'markerIconHeight'=>$this->markerIconHeight
			, 'infoWindowTitle'=>$this->infoWindowTitle
			, 'infoWindowIcon'=>$this->infoWindowIcon
			, 'infoWindowDescription'=>$this->infoWindowDescription
			, 'cost'=>$this->cost
		);
	}
}
?>