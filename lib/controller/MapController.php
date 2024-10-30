<?php
class Calendarista_MapController extends Calendarista_BaseController{
	private $map;
	public function __construct($updateCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'map')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->map = array(
			'tabName'=>isset($_POST['tabName']) ? sanitize_text_field($_POST['tabName']) : null,
			'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
			'regionAddress'=>isset($_POST['regionAddress']) ? sanitize_text_field($_POST['regionAddress']) : null,
			'regionLat'=>isset($_POST['regionLat']) ? sanitize_text_field($_POST['regionLat']) : null,
			'regionLng'=>isset($_POST['regionLng']) ? sanitize_text_field($_POST['regionLng']) : null,
			'fromAddress'=>isset($_POST['fromAddress']) ? sanitize_text_field($_POST['fromAddress']) : null,
			'fromLat'=>isset($_POST['fromLat']) ? sanitize_text_field($_POST['fromLat']) : null,
			'fromLng'=>isset($_POST['fromLng']) ? sanitize_text_field($_POST['fromLng']) : null,
			'fromPlacesPreload'=>isset($_POST['fromPlacesPreload']) ?  (bool)$_POST['fromPlacesPreload'] : null,
			'toAddress'=>isset($_POST['toAddress']) ? sanitize_text_field($_POST['toAddress']) : null,
			'toLat'=>isset($_POST['toLat']) ? sanitize_text_field($_POST['toLat']) : null,
			'toLng'=>isset($_POST['toLng']) ? sanitize_text_field($_POST['toLng']) : null,
			'toPlacesPreload'=>isset($_POST['toPlacesPreload']) ?  (bool)$_POST['toPlacesPreload'] : null,
			'waypointMarkerIconUrl'=>isset($_POST['waypointMarkerIconUrl']) ? sanitize_url($_POST['waypointMarkerIconUrl']) : null,
			'waypointMarkerIconWidth'=>isset($_POST['waypointMarkerIconWidth']) ? (int)$_POST['waypointMarkerIconWidth'] : null,
			'waypointMarkerIconHeight'=>isset($_POST['waypointMarkerIconHeight']) ? (int)$_POST['waypointMarkerIconHeight'] : null,
			'optimizeWayPoints'=>isset($_POST['optimizeWayPoints']) ?  (bool)$_POST['optimizeWayPoints'] : null,
			'costMode'=>isset($_POST['costMode']) ? (int)$_POST['costMode'] : null,
			'unitType'=>isset($_POST['unitType']) ? (int)$_POST['unitType'] : null,
			'unitCost'=>isset($_POST['unitCost']) ? (float)$_POST['unitCost'] : null,
			'minimumUnitValue'=>isset($_POST['minimumUnitValue']) ? (int)$_POST['minimumUnitValue'] : null,
			'minimumUnitCost'=>isset($_POST['minimumUnitCost']) ? (float)$_POST['minimumUnitCost'] : null,
			'queryLimitTimeout'=>isset($_POST['queryLimitTimeout']) ? (int)$_POST['queryLimitTimeout'] : null,
			'styledMaps'=>isset($_POST['styledMaps']) ? sanitize_text_field($_POST['styledMaps']) : null,
			'highway'=>isset($_POST['highway']) ?  (bool)$_POST['highway'] : null,
			'toll'=>isset($_POST['toll']) ?  (bool)$_POST['toll'] : null,
			'traffic'=>isset($_POST['traffic']) ?  (bool)$_POST['traffic'] : null,
			'zoom'=>isset($_POST['zoom']) ? (int)$_POST['zoom'] : null,
			'panToZoom'=>isset($_POST['panToZoom']) ? (int)$_POST['panToZoom'] : null,
			'mapHeight'=>isset($_POST['mapHeight']) ? (int)$_POST['mapHeight'] : null,
			'enableDirection'=>isset($_POST['enableDirection']) ?  (bool)$_POST['enableDirection'] : null,
			'enableDirectionButton'=>isset($_POST['enableDirectionButton']) ?  (bool)$_POST['enableDirectionButton'] : null,
			'enableDistance'=>isset($_POST['enableDistance']) ?  (bool)$_POST['enableDistance'] : null,
			'enableDistanceInfo'=>isset($_POST['enableDistanceInfo']) ?  (bool)$_POST['enableDistanceInfo'] : null,
			'enableHighway'=>isset($_POST['enableHighway']) ?  (bool)$_POST['enableHighway'] : null,
			'enableTolls'=>isset($_POST['enableTolls']) ?  (bool)$_POST['enableTolls'] : null,
			'enableTraffic'=>isset($_POST['enableTraffic']) ?  (bool)$_POST['enableTraffic'] : null,
			'enableWaypointButton'=>isset($_POST['enableWaypointButton']) ?  (bool)$_POST['enableWaypointButton'] : null,
			'enableFindMyPosition'=>isset($_POST['enableFindMyPosition']) ?  (bool)$_POST['enableFindMyPosition'] : null,
			'enableDestinationField'=>isset($_POST['enableDestinationField']) ?  (bool)$_POST['enableDestinationField'] : null,
			'enableScrollWheel'=>isset($_POST['enableScrollWheel']) ?  (bool)$_POST['enableScrollWheel'] : null,
			'enableContextMenu'=>isset($_POST['enableContextMenu']) ?  (bool)$_POST['enableContextMenu'] : null,
			'draggableMarker'=>isset($_POST['draggableMarker']) ?  (bool)$_POST['draggableMarker'] : null,
			'showDirectionStepsInline'=>isset($_POST['showDirectionStepsInline']) ?  (bool)$_POST['showDirectionStepsInline'] : null,
			'showInfoWindow'=>isset($_POST['showInfoWindow']) ?  (bool)$_POST['showInfoWindow'] : null,
			'driving'=>isset($_POST['driving']) ?  (bool)$_POST['driving'] : null,
			'labelDriving'=>isset($_POST['labelDriving']) ? sanitize_text_field($_POST['labelDriving']) : null,
			'walking'=>isset($_POST['walking']) ?  (bool)$_POST['walking'] : null,
			'labelWalking'=>isset($_POST['labelWalking']) ? sanitize_text_field($_POST['labelWalking']) : null,
			'bicycling'=>isset($_POST['bicycling']) ?  (bool)$_POST['bicycling'] : null,
			'labelBicycling'=>isset($_POST['labelBicycling']) ? sanitize_text_field($_POST['labelBicycling']) : null,
			'transit'=>isset($_POST['transit']) ?  (bool)$_POST['transit'] : null,
			'labelTransit'=>isset($_POST['labelTransit']) ? sanitize_text_field($_POST['labelTransit']) : null,
			'defaultTravelMode'=>isset($_POST['defaultTravelMode']) ? sanitize_text_field($_POST['defaultTravelMode']) : null,
			'contentBeforeSelector'=>isset($_POST['contentBeforeSelector']) ? sanitize_text_field($_POST['contentBeforeSelector']) : null,
			'contentAfterSelector'=>isset($_POST['contentAfterSelector']) ? sanitize_text_field($_POST['contentAfterSelector']) : null,
			'contentEndSelector'=>isset($_POST['contentEndSelector']) ? sanitize_text_field($_POST['contentEndSelector']) : null,
			'departureContextMenuLabel'=>isset($_POST['departureContextMenuLabel']) ? sanitize_text_field($_POST['departureContextMenuLabel']) : null,
			'destinationContextMenuLabel'=>isset($_POST['destinationContextMenuLabel']) ? sanitize_text_field($_POST['destinationContextMenuLabel']) : null,
			'waypointContextMenuLabel'=>isset($_POST['waypointContextMenuLabel']) ? sanitize_text_field($_POST['waypointContextMenuLabel']) : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null,
			'displayMap'=>isset($_POST['displayMap']) ?  (bool)$_POST['displayMap'] : null,
			'restrictLat'=>isset($_POST['restrictLat']) ? sanitize_text_field($_POST['restrictLat']) : null,
			'restrictLng'=>isset($_POST['restrictLng']) ? sanitize_text_field($_POST['restrictLng']) : null,
			'restrictRadius'=>isset($_POST['restrictRadius']) ? sanitize_text_field($_POST['restrictRadius']) : null,
			'restrictAddress'=>isset($_POST['restrictAddress']) ? sanitize_text_field($_POST['restrictAddress']) : null
		);
		parent::__construct(null, $updateCallback, null);
	}
	public static function redirect(){
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		//if we want to redirect then we need to do it early in the wordpress life cycle
		//the Redirect controller is going to call us through this method.
		if (array_key_exists('calendarista_create', $_POST)){
			self::createMap();
		}else if(array_key_exists('calendarista_delete', $_POST)){
			self::deleteMap();
		}
	}
	public static function createMap(){
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$repo = new Calendarista_MapRepository();
		$map = new Calendarista_Map($this->map);
		$result = $repo->insert($map);
		$url = admin_url() . 'admin.php?page=calendarista-places&calendarista-tab=1&newmapservice=true&projectId=' . $map->projectId;
		if (wp_redirect($url)) {
			exit;
		}
	}
	public function update($callback){
		$repo = new Calendarista_MapRepository();
		$map = new Calendarista_Map($this->map);
		if(array_key_exists('tabName', $_POST) && $_POST['tabName'] === 'destination_settings'){
			$map->enableDestinationField = isset($_POST['enableDestinationField']);
		}
		$result = $repo->update($map);
		$this->executeCallback($callback, array($map, $result));
	}
	public static function deleteMap(){
		$maps = array_map('intval', isset($_POST['id']) ? $_POST['id'] : '');
		$mapRepo = new Calendarista_MapRepository();
		$aggregateRepo = new Calendarista_PlaceAggregateCostRepository();
		foreach($maps as $mapId){
			$aggregateRepo->deleteAll($mapId);
			$result = $mapRepo->delete($mapId);
		}
		$url = admin_url() . 'admin.php?page=calendarista-places';
		if (wp_redirect($url)) {
			exit;
		}
	}
}
?>