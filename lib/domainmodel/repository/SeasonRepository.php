<?php
class Calendarista_SeasonRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $season_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->season_table_name = $wpdb->prefix . 'calendarista_season';
	}
	public function read($id){
		$sql = "SELECT * FROM $this->season_table_name WHERE id = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'projectId'=>(int)$result[0]->projectId
					, 'availabilityId'=>(int)$result[0]->availabilityId
					, 'projectName'=>$result[0]->projectName
					, 'availabilityName'=>$result[0]->availabilityName
					, 'start'=>date(CALENDARISTA_DATEFORMAT, strtotime($result[0]->start))
					, 'end'=>date(CALENDARISTA_DATEFORMAT, strtotime($result[0]->end))
					, 'cost'=>(float)$result[0]->cost
					, 'percentageBased'=>(int)$result[0]->percentageBased
					, 'costMode'=>(int)$result[0]->costMode
					, 'repeatWeekdayList'=>$this->getRepeatWeekdayList($result[0])
					, 'bookingDaysMinimum'=>$result[0]->bookingDaysMinimum
					, 'bookingDaysMaximum'=>$result[0]->bookingDaysMaximum
				);
		}
		return false;
	}
	public function readByDate($startDate, $availabilityId){
		$sql = "SELECT * FROM   $this->season_table_name";
		$where = array();
		$params = array($availabilityId, $startDate, $startDate);
		array_push($where, 'availabilityId  = %d');
		array_push($where, 'DATE(start) >= CONVERT(%s, DATE)');
		array_push($where, 'DATE(end) <= CONVERT(%s, DATE)');
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
					, 'projectName'=>$r->projectName
					, 'availabilityName'=>$r->availabilityName
					, 'start'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->start))
					, 'end'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->end))
					, 'cost'=>(float)$r->cost
					, 'percentageBased'=>(int)$r->percentageBased
					, 'costMode'=>(int)$r->costMode
					, 'repeatWeekdayList'=>$this->getRepeatWeekdayList($r)
					, 'bookingDaysMinimum'=>$r->bookingDaysMinimum
					, 'bookingDaysMaximum'=>$r->bookingDaysMaximum
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
		$query = "SELECT * FROM  $this->season_table_name";
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
			$query = "SELECT COUNT(*) as total FROM $this->season_table_name WHERE projectId = %d";
			$records = $this->wpdb->get_results($this->wpdb->prepare($query, $projectId));
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'projectName'=>$r->projectName
					, 'availabilityName'=>$r->availabilityName
					, 'start'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->start))
					, 'end'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->end))
					, 'cost'=>(float)$r->cost
					, 'percentageBased'=>(int)$r->percentageBased
					, 'costMode'=>(int)$r->costMode
					, 'repeatWeekdayList'=>$this->getRepeatWeekdayList($r)
					, 'bookingDaysMinimum'=>$r->bookingDaysMinimum
					, 'bookingDaysMaximum'=>$r->bookingDaysMaximum
				));
			}
			return array('resultset'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	public function readByAvailability($availabilityId){
		$sql = "SELECT * FROM   $this->season_table_name";
		$where = array();
		$params = array($availabilityId);
		array_push($where, 'availabilityId  = %d');
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
					, 'projectName'=>$r->projectName
					, 'availabilityName'=>$r->availabilityName
					, 'start'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->start))
					, 'end'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->end))
					, 'cost'=>(float)$r->cost
					, 'percentageBased'=>(int)$r->percentageBased
					, 'costMode'=>(int)$r->costMode
					, 'repeatWeekdayList'=>$this->getRepeatWeekdayList($r) 
					, 'bookingDaysMinimum'=>$r->bookingDaysMinimum
					, 'bookingDaysMaximum'=>$r->bookingDaysMaximum
				));
			}
			return $resultset;
		}
		return false;
	}
	public function insert($args){
		$params = $this->parseParams($args);
		$result = $this->wpdb->insert($this->season_table_name,  $params[0], $params[1]);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($args){
		$params = $this->parseParams($args);
		$result = $this->wpdb->update($this->season_table_name, $params[0], array('id'=>$args['id']),  $params[1]);
		return $result;
	}
	public function parseParams($args){
		$params = array(
			'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'projectName'=>$args['projectName']
			, 'availabilityName'=>$args['availabilityName']
			, 'start'=>$args['start']
			, 'end'=>$args['end']
			, 'bookingDaysMinimum'=>$args['bookingDaysMinimum']
			, 'bookingDaysMaximum'=>$args['bookingDaysMaximum']
		);
		$values = array('%d','%d', '%s', '%s', '%s', '%s', '%d', '%d');
		if(isset($args['cost'])){
		  $params['cost'] = $args['cost'];
		  array_push($values, '%f');
		}
		if(isset($args['percentageBased'])){
		  $params['percentageBased'] = $args['percentageBased'];
		  array_push($values, '%d');
		}
		if(isset($args['costMode'])){
		  $params['costMode'] = $args['costMode'];
		  array_push($values, '%d');
		}
		if(isset($args['repeatWeekdayList'])){
			$params['repeatWeekdayList'] = trim(implode(',', $args['repeatWeekdayList']));
			array_push($values, '%s');
		}
		return array($params, $values);
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->season_table_name WHERE id = %d", $id));
	}
	public function deleteByAvailabilityId($availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->season_table_name where availabilityId = %d", $availabilityId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->season_table_name where projectId = %d", $projectId));
	}
	protected function getRepeatWeekdayList($result){
		return $result->repeatWeekdayList ? array_map('intval', explode(',', $result->repeatWeekdayList)) : array();
	}
}
?>