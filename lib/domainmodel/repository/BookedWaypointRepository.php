<?php
class Calendarista_BookedWaypointRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $waypoint_booked_table_name;
	private $availability_booked_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->waypoint_booked_table_name = $wpdb->prefix . 'calendarista_waypoint_booked';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->waypoint_booked_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			return $result[0];
		}
		return false;
	}
	public function readByOrderId($orderId){
		$sql = "SELECT * FROM   $this->waypoint_booked_table_name WHERE  orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if (is_array( $result)){
			return $result;
		}
		return false;
	}
	public function export($args){
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) && !empty($args['toDate']) ? $args['toDate'] : null;
		$email = isset($args['email']) && !empty($args['email']) ? $args['email'] : null;
		$query = "SELECT w.*, a.id as availabilityId FROM $this->waypoint_booked_table_name as w LEFT JOIN $this->availability_booked_table_name as a ON w.orderId = a.orderId";
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
		$result = $this->wpdb->insert($this->waypoint_booked_table_name,  $p['params'], $p['values']);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($waypoint){
		$p = $this->parseParams($waypoint);
		$result = $this->wpdb->update($this->waypoint_booked_table_name,  $p['params'], array('id'=>$args['id']), $p['values']);
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->waypoint_booked_table_name WHERE id = %d", $id));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->waypoint_booked_table_name WHERE orderId = %d", $orderId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->waypoint_booked_table_name WHERE projectId = %d", $projectId));
	}
	public function parseParams($args){
		$params = array('projectId'=>$args['projectId']);
		$values = array('%d');
		
		$params['orderId'] = $args['orderId'];
		array_push($values, '%d');
		
		$params['lat'] = $args['lat'];
		array_push($values, '%s');

		$params['lng'] = $args['lng'];
		array_push($values, '%s');

		$params['address'] = $args['address'];
		array_push($values, '%s');

		return array('params'=>$params, 'values'=>$values);
	}
}
?>