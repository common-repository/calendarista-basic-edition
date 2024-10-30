<?php
class Calendarista_HolidaysRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $holidays_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->holidays_table_name = $wpdb->prefix . 'calendarista_holidays';
	}
	public function read($id){
		$sql = "SELECT * FROM $this->holidays_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'projectId'=>(int)$result[0]->projectId
					, 'availabilityId'=>(int)$result[0]->availabilityId
					, 'holiday'=>date(CALENDARISTA_DATEFORMAT, strtotime($result[0]->holiday))
				);
		}
		return false;
	}
	public function readHolidayContainsTimeslot($holiday, $availabilityId, $containsTimeslot = true){
		$sql = "SELECT * FROM   $this->holidays_table_name";
		$where = array();
		$params = array($availabilityId, $holiday);
		array_push($where, 'availabilityId  = %d');
		array_push($where, 'DATE(holiday) = CONVERT(%s, DATE)');
		if($containsTimeslot){
			array_push($where, 'timeslotId IS NOT NULL');
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
					, 'holiday'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->holiday))
					, 'timeslotId'=>(int)$r->timeslotId
				));
			}
			return $resultset;
		}
		return false;
	}
	public function readByDateRange($fromDate, $toDate, $availabilityId, $ignoreTimeslots = true){
		$sql = "SELECT * FROM   $this->holidays_table_name";
		$where = array();
		$params = array($availabilityId, $fromDate, $toDate);
		array_push($where, 'availabilityId  = %d');
		array_push($where, 'DATE(holiday) >= CONVERT(%s, DATE)');
		array_push($where, 'DATE(holiday) <= CONVERT(%s, DATE)');
		if($ignoreTimeslots){
			array_push($where, 'timeslotId IS NULL');
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'holiday'=>date(CALENDARISTA_DATEFORMAT, strtotime($r->holiday))
					, 'timeslotId'=>$r->timeslotId ? (int)$r->timeslotId : null
				));
			}
			return $resultset;
		}
		return false;
	}
	public function readByAvailabilityIdAndDate($availabilityId, $date){
		$sql = "SELECT id FROM $this->holidays_table_name";
		$where = array();
		$params = array($availabilityId, $date);
		array_push($where, 'availabilityId  = %d');
		array_push($where, 'DATE(holiday) = CONVERT(%s, DATE)');
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if($result){
			return $result[0]->id;
		}
		return false;
	}
	public function insert($args){
		$params = $this->parseParms($args);
		$result = $this->wpdb->insert($this->holidays_table_name,  $params[0], $params[1]);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function parseParms($args){
		$params = array(
			'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'holiday'=>$args['holiday']
		);
		$values = array('%d','%d', '%s');
		if(isset($args['timeslotId'])){
		  $params['timeslotId'] = $args['timeslotId'];
		  array_push($values, '%d');
		}
		return array($params, $values);
	}
	public function deleteByAvailabilityIdAndDate($availabilityId, $date){
		$sql = "DELETE FROM $this->holidays_table_name";
		$where = array();
		$params = array($availabilityId, $date);
		array_push($where, 'availabilityId  = %d');
		array_push($where, 'DATE(holiday) = CONVERT(%s, DATE)');
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		return $this->wpdb->query($this->wpdb->prepare($sql, $params));
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->holidays_table_name WHERE id = %d", $id));
	}
	public function deleteByTimeslot($id, $availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->holidays_table_name WHERE timeslotId = %d AND availabilityId = %d", $id, $availabilityId));
	}
	public function deleteByAvailabilityId($availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->holidays_table_name where availabilityId = %d", $availabilityId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->holidays_table_name where projectId = %d", $projectId));
	}
}
?>