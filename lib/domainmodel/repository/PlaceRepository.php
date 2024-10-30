<?php
class Calendarista_PlaceRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $place_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->place_table_name = $wpdb->prefix . 'calendarista_place';
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->place_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_Place((array)$r);
		}
		return false;
	}
	public function readAll($mapId, $placeType = null){
		$sql = "SELECT * FROM   $this->place_table_name WHERE  mapId = %d";
		if($placeType !== null){
			$sql .= " AND placeType = %d";
		}
		$sql .= " ORDER BY orderIndex";
		$params = array($mapId);
		if($placeType !== null){
			array_push($params, $placeType);
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $params) );
		if ( is_array( $result) ){
			$places = new Calendarista_Places();
			foreach($result as $r){
				$places->add(new Calendarista_Place((array)$r));
			}
			return $places;
		}
		return false;
	}
	public function insert($place){
		$p = $this->parseParams($place);
		$result = $this->wpdb->insert($this->place_table_name,  $p['params'], $p['values']);
		if($result !== false){
			$place->id = $this->wpdb->insert_id;
			$this->updateSortOrder($place->id, $place->id);
			$place->updateResources();
			return $place->id;
		}
		return $result;
	}
	public function update($place){
		$p = $this->parseParams($place);
		$result = $this->wpdb->update($this->place_table_name,  $p['params'], array('id'=>$place->id), $p['values']);
		$place->updateResources();
		return $result;
	}
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->place_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	public function delete($id){
		$this->deleteResources($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->place_table_name WHERE id = %d", $id));
	}
	public function deleteAll($mapId){
		$places = $this->readAll($mapId);
		foreach($places as $p){
			$this->deleteResources($p->id);
		}
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->place_table_name WHERE mapId = %d", $mapId));
	}
	public function parseParams($place){
		$params = array('projectId'=>$place->projectId, 'mapId'=>$place->mapId);
		$values = array('%d', '%d');

		$params['orderIndex'] = $place->orderIndex;
		array_push($values, '%d');

		$params['placeType'] = $place->placeType;
		array_push($values, '%d');

		$params['lat'] = $place->lat;
		array_push($values, '%s');

		$params['lng'] = $place->lng;
		array_push($values, '%s');

		$params['name'] = $place->name;
		array_push($values, '%s');

		$params['markerIcon'] = $place->markerIcon;
		array_push($values, '%s');

		$params['markerIconWidth'] = $place->markerIconWidth;
		array_push($values, '%d');
	
		$params['markerIconHeight'] = $place->markerIconHeight;
		array_push($values, '%d');

		$params['infoWindowIcon'] = $place->infoWindowIcon;
		array_push($values, '%s');

		$params['infoWindowDescription'] = $place->infoWindowDescription;
		array_push($values, '%s');
		
		$params['cost'] = $place->cost;
		array_push($values, '%f');
		
		return array('params'=>$params, 'values'=>$values);
	}
	public function deleteResources($id){
		$place = $this->read($id);
		if($place){
			$place->deleteResources();
		}
	}
}
?>