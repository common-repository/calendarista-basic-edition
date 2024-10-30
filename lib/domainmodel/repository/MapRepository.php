<?php
class Calendarista_MapRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $map_table_name;
	private $project_table_name;
	private $placesRepo;
	private $waypointRepo;
	private $placeAggregateRepo;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->map_table_name = $wpdb->prefix . 'calendarista_map';
		$this->project_table_name = $wpdb->prefix . 'calendarista_project';
		$this->placesRepo = new Calendarista_PlaceRepository();
		$this->waypointRepo = new Calendarista_WaypointRepository();
		$this->placeAggregateRepo = new Calendarista_PlaceAggregateCostRepository();
	}
	public function readAll(){
		$sql = "SELECT m.*, p.name as projectName FROM   $this->map_table_name as m INNER JOIN $this->project_table_name as p ON m.projectId = p.id";
		$result = $this->wpdb->get_results($sql );
		if (is_array( $result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->map_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_Map((array)$r);
		}
		return false;
	}
	public function readByProject($id){
		$sql = "SELECT * FROM   $this->map_table_name WHERE  projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_Map((array)$r);
		}
		return false;
	}
	public function insert($map){
		$m = $this->parseParams($map);
		$result = $this->wpdb->insert($this->map_table_name,  $m['params'], $m['values']);
		if($result !== false){
			$map->updateResources();
			$result = $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($map){
		$m = $this->parseParams($map);
		$result = $this->wpdb->update($this->map_table_name,  $m['params'], array('id'=>$map->id), $m['values']);
		$map->updateResources();
		return $result;
	}
	public function delete($id){
		$this->deleteResources($id);
		$this->placesRepo->deleteAll($id);
		$this->placeAggregateRepo->deleteAll($id);
		$this->waypointRepo->deleteAll($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->map_table_name WHERE id = %d", $id));
	}
	public function deleteByProject($id){
		$map = $this->readByProject($id);
		if($map){
			$this->placesRepo->deleteAll($map->id);
			$this->placeAggregateRepo->deleteAll($map->id);
			$this->waypointRepo->deleteAll($map->id);
		}
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->map_table_name WHERE projectId = %d", $id));
	}
	public function parseParams($map){
		$params = array('projectId'=>$map->projectId);
		$values = array('%d');
		if($map->tabName === 'region_settings'){
			$params['regionAddress'] = $map->regionAddress;
			array_push($values, '%s');
				
			$params['regionLat'] = $map->regionLat;
			array_push($values, '%s');

			$params['regionLng'] = $map->regionLng;
			array_push($values, '%s');
		} else if($map->tabName === 'departure_settings'){
			
			$params['enableDepartureField'] = $map->enableDepartureField;
			array_push($values, '%d');

			$params['fromPlacesPreload'] = $map->fromPlacesPreload;
			array_push($values, '%d');
		} else if($map->tabName === 'destination_settings'){
			
			$params['enableDestinationField'] = $map->enableDestinationField;
			array_push($values, '%d');
			
			if(!$map->enableDestinationField){
				$params['enableWaypointButton'] = false;
				array_push($values, '%d');
			}

			$params['toPlacesPreload'] = $map->toPlacesPreload;
			array_push($values, '%d');
		} else if($map->tabName === 'waypoint_settings'){
			$params['waypointMarkerIconUrl'] = $map->waypointMarkerIconUrl;
			array_push($values, '%s');

			$params['waypointMarkerIconWidth'] = $map->waypointMarkerIconWidth;
			array_push($values, '%d');

			$params['waypointMarkerIconHeight'] = $map->waypointMarkerIconHeight;
			array_push($values, '%d');

			$params['optimizeWayPoints'] = $map->optimizeWayPoints;
			array_push($values, '%d');
			
			$params['enableWaypointButton'] = $map->enableWaypointButton;
			array_push($values, '%d');
		} else if($map->tabName === 'new_place'){
			$params['enableDistance'] = $map->enableDistance;
			array_push($values, '%d');
			
			$params['costMode'] = $map->costMode;
			array_push($values, '%d');
			
			$params['unitCost'] = $map->unitCost;
			array_push($values, '%f');
			
			$params['minimumUnitValue'] = $map->minimumUnitValue;
			array_push($values, '%d');
			
			$params['minimumUnitCost'] = $map->minimumUnitCost;
			array_push($values, '%f');
			
			if($map->costMode === Calendarista_CostMode::DEPARTURE_ONLY){
				$params['enableDestinationField'] = false;
				array_push($values, '%d');
			}
			
		} else if($map->tabName === 'general_settings'){
			$params['queryLimitTimeout'] = $map->queryLimitTimeout;
			array_push($values, '%d');

			$params['styledMaps'] = $map->styledMaps;
			array_push($values, '%s');
			
			$params['unitType'] = $map->unitType;
			array_push($values, '%d');
			
			$params['highway'] = $map->highway;
			array_push($values, '%d');

			$params['toll'] = $map->toll;
			array_push($values, '%d');

			$params['traffic'] = $map->traffic;
			array_push($values, '%d');

			$params['zoom'] = $map->zoom;
			array_push($values, '%d');

			$params['panToZoom'] = $map->panToZoom;
			array_push($values, '%d');

			$params['mapHeight'] = $map->mapHeight;
			array_push($values, '%d');

			$params['enableDirection'] = $map->enableDirection;
			array_push($values, '%d');

			$params['enableDirectionButton'] = $map->enableDirectionButton;
			array_push($values, '%d');

			$params['enableHighway'] = $map->enableHighway;
			array_push($values, '%d');

			$params['enableTolls'] = $map->enableTolls;
			array_push($values, '%d');

			$params['enableTraffic'] = $map->enableTraffic;
			array_push($values, '%d');
			
			$params['enableFindMyPosition'] = $map->enableFindMyPosition;
			array_push($values, '%d');

			$params['enableScrollWheel'] = $map->enableScrollWheel;
			array_push($values, '%d');

			$params['enableContextMenu'] = $map->enableContextMenu;
			array_push($values, '%d');

			$params['draggableMarker'] = $map->draggableMarker;
			array_push($values, '%d');

			$params['showDirectionStepsInline'] = $map->showDirectionStepsInline;
			array_push($values, '%d');
			
			$params['showInfoWindow'] = $map->showInfoWindow;
			array_push($values, '%d');
			
			$params['enableDistance'] = $map->enableDistance;
			array_push($values, '%d');
			
			$params['enableDistanceInfo'] = $map->enableDistanceInfo;
			array_push($values, '%d');
			
			$params['driving'] = $map->driving;
			array_push($values, '%d');

			$params['labelDriving'] = $map->labelDriving;
			array_push($values, '%s');

			$params['walking'] = $map->walking;
			array_push($values, '%d');

			$params['labelWalking'] = $map->labelWalking;
			array_push($values, '%s');

			$params['bicycling'] = $map->bicycling;
			array_push($values, '%d');

			$params['labelBicycling'] = $map->labelBicycling;
			array_push($values, '%s');

			$params['transit'] = $map->transit;
			array_push($values, '%d');

			$params['labelTransit'] = $map->labelTransit;
			array_push($values, '%s');

			$params['defaultTravelMode'] = $map->defaultTravelMode;
			array_push($values, '%s');
			
			$params['departureContextMenuLabel'] = $map->departureContextMenuLabel;
			array_push($values, '%s');

			$params['destinationContextMenuLabel'] = $map->destinationContextMenuLabel;
			array_push($values, '%s');

			$params['waypointContextMenuLabel'] = $map->waypointContextMenuLabel;
			array_push($values, '%s');
			
			$params['displayMap'] = $map->displayMap;
			array_push($values, '%d');

		} else if($map->tabName === 'map_restrict'){
			$params['restrictLat'] = $map->restrictLat;
			array_push($values, '%s');
			
			$params['restrictLng'] = $map->restrictLng;
			array_push($values, '%s');
			
			$params['restrictAddress'] = $map->restrictAddress;
			array_push($values, '%s');
			
			$params['restrictRadius'] = $map->restrictRadius;
			array_push($values, '%d');
		}
		return array('params'=>$params, 'values'=>$values);
	}
	public function deleteResources($id){
		$map = $this->read($id);
		if($map){
			$map->deleteResources();
		}
	}
}
?>