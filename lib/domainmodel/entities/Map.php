<?php
class Calendarista_Map extends Calendarista_EntityBookingElementBase {
	public $id;
	public $projectId;
	public $regionAddress;
	public $regionLat;
	public $regionLng;
	public $fromAddress;
	public $fromLat;
	public $fromLng;
	public $departureMarkerIconUrl;
	public $departureMarkerIconWidth;
	public $departureMarkerIconHeight;
	public $fromInfoWindowTitle;
	public $fromInfoWindowIcon;
	public $fromInfoWindowDescription;
	public $fromPlacesPreload;
	public $toAddress;
	public $toLat;
	public $toLng;
	public $destinationMarkerIconUrl;
	public $destinationMarkerIconWidth;
	public $destinationMarkerIconHeight;
	public $toInfoWindowTitle;
	public $toInfoWindowIcon;
	public $toInfoWindowDescription;
	public $toPlacesPreload;
	public $waypointMarkerIconUrl;
	public $waypointMarkerIconWidth;
	public $waypointMarkerIconHeight;
	public $optimizeWayPoints = true;
	public $costMode;
	public $unitType;
	public $unitCost;
	public $minimumUnitValue;
	public $minimumUnitCost;
	public $queryLimitTimeout = 200;
	public $styledMaps;
	public $highway;
	public $toll;
	public $traffic;
	public $zoom = 13;
	public $panToZoom;
	public $mapHeight = 300;
	public $enableDirection;
	public $enableDirectionButton;
	public $enableDistance;
	public $enableDistanceInfo;
	public $enableHighway;
	public $enableTolls;
	public $enableTraffic;
	public $enableWaypointButton;
	public $enableFindMyPosition;
	public $enableDepartureField = true;
	public $enableDestinationField = true;
	public $enableScrollWheel;
	public $enableContextMenu;
	public $draggableMarker;
	public $showDirectionStepsInline;
	public $showInfoWindow;
	public $driving;
	public $labelDriving;
	public $walking;
	public $labelWalking;
	public $bicycling;
	public $labelBicycling;
	public $transit;
	public $labelTransit;
	public $defaultTravelMode = 'driving';
	public $contentBeforeSelector;
	public $contentAfterSelector;
	public $contentEndSelector;
	public $tabName;
	public $travelModeList;
	public $departureContextMenuLabel;
	public $destinationContextMenuLabel;
	public $waypointContextMenuLabel;
	public $displayMap = true;
	public $restrictLat;
	public $restrictLng;
	public $restrictRadius;
	public $restrictAddress;
	public function __construct($args){
		if(array_key_exists('tabName', $args)){
			$this->tabName = (string)$args['tabName'];
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('regionAddress', $args)){
			$this->regionAddress = $this->decode((string)$args['regionAddress']);
		}
		if(array_key_exists('regionLat', $args)){
			$this->regionLat = (string)$args['regionLat'];
		}
		if(array_key_exists('regionLng', $args)){
			$this->regionLng = (string)$args['regionLng'];
		}
		if(array_key_exists('fromAddress', $args)){
			$this->fromAddress = $this->decode((string)$args['fromAddress']);
		}
		if(array_key_exists('fromLat', $args)){
			$this->fromLat = (string)$args['fromLat'];
		}
		if(array_key_exists('fromLng', $args)){
			$this->fromLng = (string)$args['fromLng'];
		}
		if(array_key_exists('fromPlacesPreload', $args)){
			$this->fromPlacesPreload = (bool)$args['fromPlacesPreload'];
		}
		if(array_key_exists('toAddress', $args)){
			$this->toAddress = $this->decode((string)$args['toAddress']);
		}
		if(array_key_exists('toLat', $args)){
			$this->toLat = (string)$args['toLat'];
		}
		if(array_key_exists('toLng', $args)){
			$this->toLng = (string)$args['toLng'];
		}
		if(array_key_exists('toPlacesPreload', $args)){
			$this->toPlacesPreload = (bool)$args['toPlacesPreload'];
		}
		if(array_key_exists('waypointMarkerIconUrl', $args)){
			$this->waypointMarkerIconUrl = (string)$args['waypointMarkerIconUrl'];
		}
		if(array_key_exists('waypointMarkerIconWidth', $args)){
			$this->waypointMarkerIconWidth = (int)$args['waypointMarkerIconWidth'];
		}
		if(array_key_exists('waypointMarkerIconHeight', $args)){
			$this->waypointMarkerIconHeight = (int)$args['waypointMarkerIconHeight'];
		}
		if(array_key_exists('optimizeWayPoints', $args) && $args['optimizeWayPoints'] !== null){
			$this->optimizeWayPoints = (bool)$args['optimizeWayPoints'];
		}
		if(array_key_exists('costMode', $args)){
			/*
				0. cost not enabled
				1. cost is enabled by location
				2. cost is enabled by distance
			*/
			$this->costMode = (int)$args['costMode'];
		}
		if(array_key_exists('unitType', $args)){
			$this->unitType = (int)$args['unitType'];
		}
		if(array_key_exists('unitCost', $args)){
			$this->unitCost = (float)$args['unitCost'];
		}
		if(array_key_exists('minimumUnitValue', $args)){
			$this->minimumUnitValue = (int)$args['minimumUnitValue'];
		}
		if(array_key_exists('minimumUnitCost', $args)){
			$this->minimumUnitCost = (float)$args['minimumUnitCost'];
		}
		if(array_key_exists('queryLimitTimeout', $args)){
			if($args['queryLimitTimeout'] !== null){
				$this->queryLimitTimeout = (int)$args['queryLimitTimeout'];
			}
		}
		if(array_key_exists('styledMaps', $args)){
			$this->styledMaps = (string)$args['styledMaps'];
		}
		if(array_key_exists('highway', $args)){
			$this->highway = (bool)$args['highway'];
		}
		if(array_key_exists('toll', $args)){
			$this->toll = (bool)$args['toll'];
		}
		if(array_key_exists('traffic', $args)){
			$this->traffic = (bool)$args['traffic'];
		}
		if(array_key_exists('zoom', $args)){
			if($args['zoom'] !== null){
				$this->zoom = (int)$args['zoom'];
			}
		}
		if(array_key_exists('panToZoom', $args)){
			$this->panToZoom = (int)$args['panToZoom'];
		}
		if(array_key_exists('mapHeight', $args)){
			if($args['mapHeight'] !== null){
				$this->mapHeight = (int)$args['mapHeight'];
			}
		}
		if(array_key_exists('enableDirection', $args)){
			$this->enableDirection = (bool)$args['enableDirection'];
		}
		if(array_key_exists('enableDirectionButton', $args)){
			$this->enableDirectionButton = (bool)$args['enableDirectionButton'];
		}
		if(array_key_exists('enableDistance', $args)){
			$this->enableDistance = (bool)$args['enableDistance'];
		}
		if(array_key_exists('enableDistanceInfo', $args)){
			$this->enableDistanceInfo = (bool)$args['enableDistanceInfo'];
		}
		if(array_key_exists('enableHighway', $args)){
			$this->enableHighway = (bool)$args['enableHighway'];
		}
		if(array_key_exists('enableTolls', $args)){
			$this->enableTolls = (bool)$args['enableTolls'];
		}
		if(array_key_exists('enableTraffic', $args)){
			$this->enableTraffic = (bool)$args['enableTraffic'];
		}
		if(array_key_exists('enableWaypointButton', $args)){
			$this->enableWaypointButton = (bool)$args['enableWaypointButton'];
		}
		if(array_key_exists('enableFindMyPosition', $args)){
			$this->enableFindMyPosition = (bool)$args['enableFindMyPosition'];
		}
		if(array_key_exists('enableDestinationField', $args) && isset($args['enableDestinationField'])){
			$this->enableDestinationField = (bool)$args['enableDestinationField'];
		}
		if(array_key_exists('enableScrollWheel', $args)){
			$this->enableScrollWheel = (bool)$args['enableScrollWheel'];
		}
		if(array_key_exists('enableContextMenu', $args)){
			$this->enableContextMenu = (bool)$args['enableContextMenu'];
		}
		if(array_key_exists('draggableMarker', $args)){
			$this->draggableMarker = (bool)$args['draggableMarker'];
		}
		if(array_key_exists('showDirectionStepsInline', $args)){
			$this->showDirectionStepsInline = (bool)$args['showDirectionStepsInline'];
		}
		if(array_key_exists('showInfoWindow', $args)){
			$this->showInfoWindow = (bool)$args['showInfoWindow'];
		}
		if(array_key_exists('driving', $args)){
			$this->driving = (bool)$args['driving'];
		}
		if(array_key_exists('label_driving', $args)){
			$this->labelDriving = (string)$args['labelDriving'];
		}
		if(array_key_exists('walking', $args)){
			$this->walking = (bool)$args['walking'];
		}
		if(array_key_exists('labelWalking', $args)){
			$this->labelWalking = (string)$args['labelWalking'];
		}
		if(array_key_exists('bicycling', $args)){
			$this->bicycling = (bool)$args['bicycling'];
		}
		if(array_key_exists('labelBicycling', $args)){
			$this->labelBicycling = (string)$args['labelBicycling'];
		}
		if(array_key_exists('transit', $args)){
			$this->transit = (bool)$args['transit'];
		}
		if(array_key_exists('labelTransit', $args)){
			$this->labelTransit = (string)$args['labelTransit'];
		}
		if(array_key_exists('defaultTravelMode', $args)){
			if($args['defaultTravelMode'] !== null){
				$this->defaultTravelMode = (string)$args['defaultTravelMode'];
			}
		}
		if(array_key_exists('contentBeforeSelector', $args)){
			$this->contentBeforeSelector = $this->decode((string)$args['contentBeforeSelector']);
		}
		if(array_key_exists('contentAfterSelector', $args)){
			$this->contentAfterSelector = $this->decode((string)$args['contentAfterSelector']);
		}
		if(array_key_exists('contentEndSelector', $args)){
			$this->contentEndSelector = $this->decode((string)$args['contentEndSelector']);
		}
		if(array_key_exists('departureContextMenuLabel', $args)){
			$this->departureContextMenuLabel = $this->decode((string)$args['departureContextMenuLabel']);
		}
		if(array_key_exists('destinationContextMenuLabel', $args)){
			$this->destinationContextMenuLabel = $this->decode((string)$args['destinationContextMenuLabel']);
		}
		if(array_key_exists('waypointContextMenuLabel', $args)){
			$this->waypointContextMenuLabel = $this->decode((string)$args['waypointContextMenuLabel']);
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->travelModeList = array();
		if($this->driving){
			$this->travelModeList['driving'] = array('name'=>'driving', 'label'=>$this->labelDriving);
		}
		if($this->walking){
			$this->travelModeList['walking'] = array('name'=>'walking', 'label'=>$this->labelWalking);
		}
		if($this->bicycling){
			$this->travelModeList['bicycling'] = array('name'=>'bicycling', 'label'=>$this->labelBicycling);
		}
		if($this->transit){
			$this->travelModeList['transit'] = array('name'=>'transit', 'label'=>$this->labelTransit);
		}
		if(array_key_exists('displayMap', $args) && is_numeric($args['displayMap'])){
			$this->displayMap = (bool)$args['displayMap'];
		}
		if(array_key_exists('restrictLat', $args)){
			if($args['restrictLat'] !== null){
				$this->restrictLat = $args['restrictLat'];
			}
		}
		if(array_key_exists('restrictLng', $args)){
			if($args['restrictLng'] !== null){
				$this->restrictLng = $args['restrictLng'];
			}
		}
		if(array_key_exists('restrictRadius', $args)){
			if($args['restrictRadius'] !== null){
				$this->restrictRadius = $args['restrictRadius'];
			}
		}
		if(array_key_exists('restrictAddress', $args)){
			if($args['restrictAddress'] !== null){
				$this->restrictAddress = $args['restrictAddress'];
			}
		}
	}
	public function departurePlacesToJSON(){
		
	}
	public function destinationPlacesToJSON(){
		
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'regionAddress'=>$this->regionAddress
			, 'regionLat'=>$this->regionLat
			, 'regionLng'=>$this->regionLng
			, 'fromAddress'=>$this->fromAddress
			, 'fromLat'=>$this->fromLat
			, 'fromLng'=>$this->fromLng
			, 'fromPlacesPreload'=>$this->fromPlacesPreload
			, 'toAddress'=>$this->toAddress
			, 'toLat'=>$this->toLat
			, 'toLng'=>$this->toLng
			, 'toPlacesPreload'=>$this->toPlacesPreload
			, 'waypointMarkerIconUrl'=>$this->waypointMarkerIconUrl
			, 'waypointMarkerIconWidth'=>$this->waypointMarkerIconWidth
			, 'waypointMarkerIconHeight'=>$this->waypointMarkerIconHeight
			, 'optimizeWayPoints'=>$this->optimizeWayPoints
			, 'costMode'=>$this->costMode
			, 'unitType'=>$this->unitType
			, 'unitCost'=>$this->unitCost
			, 'minimumUnitValue'=>$this->minimumUnitValue
			, 'minimumUnitCost'=>$this->minimumUnitCost
			, 'queryLimitTimeout'=>$this->queryLimitTimeout
			, 'styledMaps'=>$this->styledMaps ? json_decode(str_replace('\\', '', $this->styledMaps)) : null
			, 'highway'=>$this->highway
			, 'toll'=>$this->toll
			, 'traffic'=>$this->traffic
			, 'zoom'=>$this->zoom
			, 'panToZoom'=>$this->panToZoom
			, 'mapHeight'=>$this->mapHeight
			, 'enableDirection'=>$this->enableDirection
			, 'enableDirectionButton'=>$this->enableDirectionButton
			, 'enableDistance'=>$this->enableDistance
			, 'enableDistanceInfo'=>$this->enableDistanceInfo
			, 'enableHighway'=>$this->enableHighway
			, 'enableTolls'=>$this->enableTolls
			, 'enableTraffic'=>$this->enableTraffic
			, 'enableWaypointButton'=>$this->enableWaypointButton
			, 'enableFindMyPosition'=>$this->enableFindMyPosition
			, 'enableDepartureField'=>$this->enableDepartureField
			, 'enableDestinationField'=>$this->enableDestinationField
			, 'enableScrollWheel'=>$this->enableScrollWheel
			, 'enableContextMenu'=>$this->enableContextMenu
			, 'draggableMarker'=>$this->draggableMarker
			, 'showDirectionStepsInline'=>$this->showDirectionStepsInline
			, 'showInfoWindow'=>$this->showInfoWindow
			, 'driving'=>$this->driving
			, 'labelDriving'=>$this->labelDriving
			, 'walking'=>$this->walking
			, 'labelWalking'=>$this->labelWalking
			, 'bicycling'=>$this->bicycling
			, 'labelBicycling'=>$this->labelBicycling
			, 'transit'=>$this->transit
			, 'labelTransit'=>$this->labelTransit
			, 'defaultTravelMode'=>$this->defaultTravelMode
			, 'contentBeforeSelector'=>$this->contentBeforeSelector
			, 'contentAfterSelector'=>$this->contentAfterSelector
			, 'contentEndSelector'=>$this->contentEndSelector
			, 'displayMap'=>$this->displayMap
			, 'restrictLat'=>$this->restrictLat
			, 'restrictLng'=>$this->restrictLng
			, 'restrictRadius'=>$this->restrictRadius
			, 'restrictAddress'=>$this->restrictAddress
		);
	}
	public function enableTravelMode(){
		return $this->driving || $this->walking || $this->bicycling || $this->transit;
	}
	public function getDefaultTravelModeLabel(){
		return $this->travelModeList[$this->defaultTravelMode]['label'];
	}
	protected function init(){
		$this->labelDriving = Calendarista_TranslationHelper::t('map_labelDriving' . $this->id, $this->labelDriving);
		$this->labelWalking = Calendarista_TranslationHelper::t('map_labelWalking' . $this->id, $this->labelWalking);
		$this->labelBicycling = Calendarista_TranslationHelper::t('map_labelBicycling' . $this->id, $this->labelBicycling);
		$this->labelTransit = Calendarista_TranslationHelper::t('map_labelTransit' . $this->id, $this->labelTransit);
		$this->defaultTravelMode = Calendarista_TranslationHelper::t('map_defaultTravelMode' . $this->id, $this->defaultTravelMode);
		$this->contentBeforeSelector = Calendarista_TranslationHelper::t('map_contentBeforeSelector' . $this->id, $this->contentBeforeSelector);
		$this->contentAfterSelector = Calendarista_TranslationHelper::t('map_contentAfterSelector' . $this->id, $this->contentAfterSelector);
		$this->contentEndSelector = Calendarista_TranslationHelper::t('map_contentEndSelector' . $this->id, $this->contentEndSelector);
		$this->departureContextMenuLabel = Calendarista_TranslationHelper::t('map_departureContextMenuLabel' . $this->id, $this->departureContextMenuLabel);
		$this->destinationContextMenuLabel = Calendarista_TranslationHelper::t('map_destinationContextMenuLabel' . $this->id, $this->destinationContextMenuLabel);
		$this->waypointContextMenuLabel = Calendarista_TranslationHelper::t('map_waypointContextMenuLabel' . $this->id, $this->waypointContextMenuLabel);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('map_labelDriving' . $this->id, $this->labelDriving);
		Calendarista_TranslationHelper::register('map_labelWalking' . $this->id, $this->labelWalking);
		Calendarista_TranslationHelper::register('map_labelBicycling' . $this->id, $this->labelBicycling);
		Calendarista_TranslationHelper::register('map_labelTransit' . $this->id, $this->labelTransit);
		Calendarista_TranslationHelper::register('map_defaultTravelMode' . $this->id, $this->defaultTravelMode);
		Calendarista_TranslationHelper::register('map_contentBeforeSelector' . $this->id, $this->contentBeforeSelector, true);
		Calendarista_TranslationHelper::register('map_contentAfterSelector' . $this->id, $this->contentAfterSelector, true);
		Calendarista_TranslationHelper::register('map_contentEndSelector' . $this->id, $this->contentEndSelector, true);
		Calendarista_TranslationHelper::register('map_departureContextMenuLabel' . $this->id, $this->departureContextMenuLabel);
		Calendarista_TranslationHelper::register('map_destinationContextMenuLabel' . $this->id, $this->destinationContextMenuLabel);
		Calendarista_TranslationHelper::register('map_waypointContextMenuLabel' . $this->id, $this->waypointContextMenuLabel);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('map_' . $this->id);
		Calendarista_TranslationHelper::unregister('map_labelDriving' . $this->id);
		Calendarista_TranslationHelper::unregister('map_labelWalking' . $this->id);
		Calendarista_TranslationHelper::unregister('map_labelBicycling' . $this->id);
		Calendarista_TranslationHelper::unregister('map_labelTransit' . $this->id);
		Calendarista_TranslationHelper::unregister('map_defaultTravelMode' . $this->id);
		Calendarista_TranslationHelper::unregister('map_contentBeforeSelector' . $this->id);
		Calendarista_TranslationHelper::unregister('map_contentAfterSelector' . $this->id);
		Calendarista_TranslationHelper::unregister('map_contentEndSelector' . $this->id);
		Calendarista_TranslationHelper::unregister('map_departureContextMenuLabel' . $this->id);
		Calendarista_TranslationHelper::unregister('map_destinationContextMenuLabel' . $this->id);
		Calendarista_TranslationHelper::unregister('map_waypointContextMenuLabel' . $this->id);
	}
}
?>