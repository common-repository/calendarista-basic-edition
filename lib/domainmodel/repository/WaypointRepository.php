<?php
class Calendarista_WaypointRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $waypoint_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->waypoint_table_name = $wpdb->prefix . 'calendarista_waypoint';
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->waypoint_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_Waypoint((array)$r);
		}
		return false;
	}
	public function readAll($mapId){
		$sql = "SELECT * FROM   $this->waypoint_table_name WHERE  mapId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $mapId) );
		if ( is_array( $result) ){
			$waypoints = new Calendarista_Waypoints();
			foreach($result as $r){
				$waypoints->add(new Calendarista_Waypoint((array)$r));
			}
			return $waypoints;
		}
		return false;
	}
	public function insert($waypoint){
		$p = $this->parseParams($waypoint);
		$result = $this->wpdb->insert($this->waypoint_table_name,  $p['params'], $p['values']);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($waypoint){
		$p = $this->parseParams($waypoint);
		$result = $this->wpdb->update($this->waypoint_table_name,  $p['params'], array('id'=>$waypoint->id), $p['values']);
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->waypoint_table_name WHERE id = %d", $id));
	}
	public function deleteAll($mapId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->waypoint_table_name WHERE mapId = %d", $mapId));
	}
	public function parseParams($waypoint){
		$params = array('projectId'=>$waypoint->projectId);
		$values = array('%d');
		
		$params['mapId'] = $waypoint->mapId;
		array_push($values, '%d');
		
		$params['lat'] = $waypoint->lat;
		array_push($values, '%s');

		$params['lng'] = $waypoint->lng;
		array_push($values, '%s');

		$params['address'] = $waypoint->address;
		array_push($values, '%s');

		return array('params'=>$params, 'values'=>$values);
	}
}
?>