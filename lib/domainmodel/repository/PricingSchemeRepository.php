<?php
class Calendarista_PricingSchemeRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $pricing_scheme_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->pricing_scheme_table_name = $wpdb->prefix . 'calendarista_pricing_scheme';
	}
	public function read($id){
		$sql = "SELECT * FROM $this->pricing_scheme_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'projectId'=>(int)$result[0]->projectId
					, 'availabilityId'=>(int)$result[0]->availabilityId
					, 'seasonId'=>(int)$result[0]->seasonId
					, 'days'=>(int)$result[0]->days
					, 'cost'=>(float)$result[0]->cost
				);
		}
		return false;
	}
	public function readByAvailabilityIdAndDays($availabilityId, $days, $id = 0){
		$sql = "SELECT * FROM $this->pricing_scheme_table_name WHERE availabilityId = %d AND days = %d AND id != %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $availabilityId, $days, $id));
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'projectId'=>(int)$result[0]->projectId
					, 'availabilityId'=>(int)$result[0]->availabilityId
					, 'seasonId'=>(int)$result[0]->seasonId
					, 'days'=>(int)$result[0]->days
					, 'cost'=>(float)$result[0]->cost
				);
		}
		return false;
	}
	public function readBySeasonIdAndDays($seasonId, $days, $id = 0){
		$sql = "SELECT * FROM $this->pricing_scheme_table_name WHERE seasonId = %d AND days = %d AND id != %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $seasonId, $days, $id));
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'projectId'=>(int)$result[0]->projectId
					, 'availabilityId'=>(int)$result[0]->availabilityId
					, 'seasonId'=>(int)$result[0]->seasonId
					, 'days'=>(int)$result[0]->days
					, 'cost'=>(float)$result[0]->cost
				);
		}
		return false;
	}
	public function readByProjectId($projectId){
		$sql = "SELECT * FROM   $this->pricing_scheme_table_name";
		$where = array();
		$params = array($projectId);
		array_push($where, 'projectId  = %d');
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'seasonId'=>(int)$r->seasonId
					, 'days'=>(int)$r->days
					, 'cost'=>(float)$r->cost
				));
			}
			return $resultset;
		}
		return false;
	}
	public function readByAvailabilityId($availabilityId, $excludeSeason = false){
		$sql = "SELECT * FROM   $this->pricing_scheme_table_name";
		$where = array();
		$params = array($availabilityId);
		array_push($where, 'availabilityId  = %d');
		if($excludeSeason){
			array_push($where, '(ISNULL(seasonId) OR seasonId = 0)');
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'seasonId'=>(int)$r->seasonId
					, 'days'=>(int)$r->days
					, 'cost'=>(float)$r->cost
				));
			}
			return $resultset;
		}
		return false;
	}
	public function readBySeasonId($seasonId){
		$sql = "SELECT * FROM   $this->pricing_scheme_table_name";
		$where = array();
		$params = array($seasonId);
		array_push($where, 'seasonId  = %d');
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'seasonId'=>(int)$r->seasonId
					, 'days'=>(int)$r->days
					, 'cost'=>(float)$r->cost
				));
			}
			return $resultset;
		}
		return false;
	}
	public function readAll($projectId, $pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc'){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'id';
		}
		if($order === null){
			$order = 'asc';
		}
		$where = array('projectId = %d');
		$params = array($projectId);
		$query = "SELECT * FROM  $this->pricing_scheme_table_name";
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= " ORDER BY $orderBy $order";
		if($pageIndex > -1){
			$query .= ' LIMIT %d, %d;';
			array_push($params, $pageIndex, $limit);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$query = "SELECT COUNT(*) as total FROM $this->pricing_scheme_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$records = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'seasonId'=>(int)$r->seasonId
					, 'days'=>(int)$r->days
					, 'cost'=>(float)$r->cost
				));
			}
			return array('resultset'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	public function insert($args){
		$params = $this->parseParams($args);
		$result = $this->wpdb->insert($this->pricing_scheme_table_name,  $params[0], $params[1]);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($args){
		$params = $this->parseParams($args);
		$result = $this->wpdb->update($this->pricing_scheme_table_name, $params[0], array('id'=>$args['id']),  $params[1]);
		return $result;
	}
	public function parseParams($args){
		$params = array(
			'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'days'=>$args['days']
			, 'cost'=>$args['cost']
		);
		$values = array('%d','%d', '%d', '%f');
		if(isset($args['seasonId'])){
			$params['seasonId'] = $args['seasonId'];
			array_push($values, '%d');
		}
		return array($params, $values);
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->pricing_scheme_table_name WHERE id = %d", $id));
	}
	public function deleteByAvailabilityId($availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->pricing_scheme_table_name where availabilityId = %d", $availabilityId));
	}
	public function deleteBySeasonId($seasonId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->pricing_scheme_table_name where seasonId = %d", $seasonId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->pricing_scheme_table_name where projectId = %d", $projectId));
	}
}
?>