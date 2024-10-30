<?php
class Calendarista_BookedOptionalRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $optional_booked_table_name;
	private $availability_booked_table_name;
	private $optional_group_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->optional_booked_table_name = $wpdb->prefix . 'calendarista_optionals_booked';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
		$this->optional_group_table_name = $wpdb->prefix . 'calendarista_optional_group';
	}
	
	public function readAll($orderId){
		$sql = "SELECT ob.*, (SELECT og.displayMode FROM $this->optional_group_table_name as og WHERE og.id = ob.groupId) as displayMode FROM $this->optional_booked_table_name as ob WHERE  ob.orderId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if ( is_array($result) ){
			$optionals = array();
			foreach($result as $r){
				if(!isset($optionals[$r->groupName])){
					$optionals[$r->groupName] = array();
				}
				array_push($optionals[$r->groupName], $r);
			}
			return $optionals;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT ob.*, (SELECT og.displayMode FROM $this->optional_group_table_name as og WHERE og.id = ob.groupId) as displayMode FROM $this->optional_booked_table_name as ob WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
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
		$query = "SELECT op.*, a.id as availabilityId FROM $this->optional_booked_table_name as op LEFT JOIN $this->availability_booked_table_name as a ON op.orderId = a.orderId";
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
		$query .= ' ORDER BY op.orderIndex';
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	public function insert($optional){
		$p = $this->parseParams($optional);
		$result = $this->wpdb->insert($this->optional_booked_table_name, $p['params'], $p['values']);
		if($result !== false){
			$this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->update($this->optional_booked_table_name,  $p['params'], array('id'=>$args['id']), $p['values']);
		return $result;
	}
	public function parseParams($args){
		$params = array();
		$values = array();

		if(array_key_exists('orderId', $args)){
			$params['orderId'] = $args['orderId'];
			array_push($values, '%d');
		}
		if(array_key_exists('projectId', $args)){
			$params['projectId'] = $args['projectId'];
			array_push($values, '%d');
		}
		if(array_key_exists('optionalId', $args)){
			$params['optionalId'] = $args['optionalId'];
			array_push($values, '%d');
		}
		if(array_key_exists('name', $args)){
			$params['name'] = $args['name'];
			array_push($values, '%s');
		}
		if(array_key_exists('groupName', $args)){
			$params['groupName'] = $args['groupName'];
			array_push($values, '%s');
		}
		if(array_key_exists('cost', $args)){
			$params['cost'] = $args['cost'];
			array_push($values, '%f');
		}
		if(array_key_exists('orderIndex', $args)){
			$params['orderIndex'] = $args['orderIndex'];
			array_push($values, '%d');
		}
		if(array_key_exists('groupOrderIndex', $args)){
			$params['groupOrderIndex'] = $args['groupOrderIndex'];
			array_push($values, '%d');
		}
		if(array_key_exists('groupId', $args)){
			$params['groupId'] = $args['groupId'];
			array_push($values, '%d');
		}
		if(array_key_exists('incrementValue', $args)){
			$params['incrementValue'] = $args['incrementValue'];
			array_push($values, '%d');
		}
		
		return array('params'=>$params, 'values'=>$values);
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_booked_table_name WHERE id = %d", $id));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_booked_table_name WHERE orderId = %d", $orderId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_booked_table_name WHERE projectId = %d", $projectId));
	}
}
?>