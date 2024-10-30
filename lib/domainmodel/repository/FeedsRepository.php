<?php
class Calendarista_FeedsRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $feeds_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->feeds_table_name = $wpdb->prefix . 'calendarista_feeds';
	}
	public function readAll($args = array()){
		$pageIndex = isset($args['pageIndex']) ? $args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? $args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? $args['orderBy'] : null;
		$order = isset($args['order']) ? $args['order'] : null;
		$availabilities = isset($args['availabilities']) && is_array($args['availabilities']) ? implode(',', array_map('intval', $args['availabilities'])) : false;
		if($pageIndex === null){
			$pageIndex = -1;
		}else{
			$pageIndex = intval($pageIndex);
		}
		if($limit === null){
			$limit = 5;
		}
		else{
			$limit = intval($limit);
		}
		if($orderBy === null){
			$orderBy = 'id';
		}

		if($order === null){
			$order = 'asc';
		}
		$query = "SELECT *
			FROM $this->feeds_table_name";
		$where = array();
		if($availabilities){
			array_push($where, 'availabilityId IN (' . $availabilities . ')');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		} 
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results($query);
		if (is_array($result) ){
			$query = "SELECT COUNT(*) as total FROM $this->feeds_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$records = $this->wpdb->get_results($query);
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return array('items'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT *
				FROM $this->feeds_table_name
				WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			return (array)$result[0];
		}
		return false;
	}
	public function readByProjectAndAvailability($projectId, $availabilityId){
		$sql = "SELECT *
				FROM $this->feeds_table_name 
				WHERE projectId = %d AND (availabilityId = %d OR availabilityId IS NULL)";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $projectId, $availabilityId));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function readByProject($projectId){
		$sql = "SELECT *
				FROM $this->feeds_table_name 
				WHERE projectId = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $projectId));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function readByAvailability($availabilityId){
		$sql = "SELECT *
				FROM $this->feeds_table_name 
				WHERE availabilityId = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function insert($args){
		$p = $this->parseParams($args);
		 $result = $this->wpdb->insert($this->feeds_table_name,  $p['params'], $p['values']);
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}

	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->feeds_table_name WHERE id = %d", $id));
	}
	public function deleteByProject($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->feeds_table_name WHERE projectId = %d", $projectId));
	}
	public function deleteByAvailability($availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->feeds_table_name WHERE availabilityId = %d", $availabilityId));
	}
	public function deleteAll(){
		return $this->wpdb->query("DELETE FROM $this->feeds_table_name");
	}
	public function parseParams($args){
		$dateCreated = new Calendarista_DateTime();
		$params = array(
			'projectId'=>$args['projectId']
			, 'projectName'=>$args['projectName']
			, 'feedUrl'=>$args['feedUrl']
			, 'dateCreated'=>$dateCreated->format(CALENDARISTA_DATEFORMAT)
		);
		$values = array('%d', '%s', '%s', '%s');
		if(isset($args['availabilityId'])){
			$params['availabilityId'] = $args['availabilityId'];
			array_push($values, '%d');
		}
		if(isset($args['availabilityName'])){
			$params['availabilityName'] = $args['availabilityName'];
			array_push($values, '%s');
		}
		
		return array('params'=>$params, 'values'=>$values);
	}
}
?>