<?php
class Calendarista_AvailabilityDayRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $availability_table_name;
	private $availability_day_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->availability_table_name = $wpdb->prefix . 'calendarista_availability';
		$this->availability_day_table_name = $wpdb->prefix . 'calendarista_availability_day';
	}
	public function readAll($args){
		$availabilityId = isset($args['availabilityId']) ? (int)$args['availabilityId'] : null;
		$pageIndex = isset($args['pageIndex']) ? (int)$args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? (int)$args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? (string)$args['orderBy'] : 'id';
		$order = isset($args['order']) ? (string)$args['order'] : 'asc';
		$where = array();
		$params = array();
		$query = "SELECT * FROM   $this->availability_day_table_name";
		if($availabilityId){
			array_push($where, 'availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$query = "SELECT count(id) as count FROM   $this->availability_day_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$subResult = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$rows = array();
			foreach($result as $r){
				array_push($rows, (array)$r);
			}
			return array('items'=>$rows, 'total'=>$subResult ? (int)$subResult[0]->count : 0);
		}
		return false;
	}
	public function readByDateAndAvailability($date, $availabilityId){
		$sql = "SELECT d.* FROM   $this->availability_day_table_name as d WHERE DATE(d.individualDay) = CONVERT(%s, DATE) AND d.availabilityId = %d ORDER BY d.id desc";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $date, $availabilityId) );
		if($result){
			return $result[0];
		}
		return false;
	}
	public function readByAvailability($availabilityId){
		$sql = "SELECT d.individualDay FROM   $this->availability_day_table_name as d WHERE d.availabilityId = %d  ORDER BY d.individualDay desc";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $availabilityId) );
		if($result){
			$rows = array();
			foreach($result as $r){
				array_push($rows, date('Y-m-d', strtotime($r->individualDay)));
			}
			return $rows;
		}
		return false;
	}
	public function insert($tag){
		$p = $this->parseParams($tag);
		$result = $this->wpdb->insert($this->availability_day_table_name,  $p['params'], $p['values']);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_day_table_name WHERE id = %d", $id));
	}
	public function deleteByAvailabilityId($availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_day_table_name WHERE availabilityId = %d", $availabilityId));
	}
	public function deleteByProjectId($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_day_table_name WHERE projectId = %d", $projectId));
	}
	public function parseParams($args){
		$params = array();
		$values = array();
		
		$params['projectId'] = $args['projectId'];
		array_push($values, '%d');
		
		$params['availabilityId'] = $args['availabilityId'];
		array_push($values, '%d');
		
		$params['individualDay'] = $args['individualDay'];
		array_push($values, '%s');

		return array('params'=>$params, 'values'=>$values);
	}
}
?>