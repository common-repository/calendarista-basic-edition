<?php
class Calendarista_BookedMapRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $map_booked_table_name;
	private $availability_booked_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->map_booked_table_name = $wpdb->prefix . 'calendarista_map_booked';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
	}
	
	public function read($id){
		$sql = "SELECT * FROM   $this->map_booked_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			return $result[0];
		}
		return false;
	}
	public function readByOrderId($orderId){
		$sql = "SELECT * FROM   $this->map_booked_table_name WHERE  orderId = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $orderId));
		if( $result ){
			return $result[0];
		}
		return false;
	}
	public function export($args){
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) && !empty($args['toDate']) ? $args['toDate'] : null;
		$email = isset($args['email']) && !empty($args['email']) ? $args['email'] : null;
		$query = "SELECT m.*, a.id as availabilityId FROM $this->map_booked_table_name as m LEFT JOIN $this->availability_booked_table_name as a ON m.orderId = a.orderId";
		$where = array();
		$params = array();
		if($fromDate !== null && !$email){
			array_push($where, '(DATE(a.fromDate) >= CONVERT(%s, DATE) AND (CONVERT(%s, DATE) <= DATE(a.toDate) OR a.toDate IS NULL))');
			array_push($params, $fromDate, $fromDate);
		}
		if($toDate !== null && !$email){
			array_push($where, '(DATE(a.toDate) <= CONVERT(%s, DATE) OR a.toDate IS NULL)');
			array_push($params, $toDate);
		}
		if($email){
			array_push($where, 'a.userEmail = %s');
			array_push($params, $email);
		}
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'a.projectId = %d');
			array_push($params, $projectId);
		}
		if($availabilityId !== null && $availabilityId !== -1){
			array_push($where, 'a.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	public function insert($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->insert($this->map_booked_table_name,  $p['params'], $p['values']);
		if($result !== false){
			$result = $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->update($this->map_booked_table_name,  $p['params'], array('id'=>$args['id']), $p['values']);
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->map_booked_table_name WHERE id = %d", $id));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->map_booked_table_name WHERE orderId = %d", $orderId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->map_booked_table_name WHERE projectId = %d", $projectId));
	}
	public function parseParams($args){
		$params = array('projectId'=>$args['projectId']);
		$values = array('%d');
		
		$params['orderId'] = $args['orderId'];
		array_push($values, '%d');
		
		$params['fromAddress'] = $args['fromAddress'];
		array_push($values, '%s');

		$params['fromLat'] = $args['fromLat'];
		array_push($values, '%s');

		$params['fromLng'] = $args['fromLng'];
		array_push($values, '%s');

		$params['toAddress'] = $args['toAddress'];
		array_push($values, '%s');

		$params['toLat'] = $args['toLat'];
		array_push($values, '%s');

		$params['toLng'] = $args['toLng'];
		array_push($values, '%s');

		$params['unitType'] = $args['unitType'];
		array_push($values, '%d');
		
		$params['distance'] = $args['distance'];
		array_push($values, '%f');
		
		$params['duration'] = $args['duration'];
		array_push($values, '%f');
		
		if(isset($args['fromPlaceId'])){
			$params['fromPlaceId'] = $args['fromPlaceId'];
			array_push($values, '%d');
		}
		
		if(isset($args['fromPlaceId'])){
			$params['toPlaceId'] = $args['toPlaceId'];
			array_push($values, '%d');
		}
		return array('params'=>$params, 'values'=>$values);
	}
}
?>