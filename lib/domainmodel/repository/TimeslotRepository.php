<?php
class Calendarista_TimeslotRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $timeslot_table_name;
	private $holidays_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->timeslot_table_name = $wpdb->prefix . 'calendarista_timeslot';
		$this->holidays_table_name = $wpdb->prefix . 'calendarista_holidays';
	}
	
	public function readAll($projectId){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  projectId = %d ORDER BY TIME(timeslot)";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $projectId));
		if ( is_array($result) ){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	
	public function readAllByAvailability($availabilityId){
		$sql = "SELECT *
				FROM   $this->timeslot_table_name 
				WHERE  availabilityId = %d
				ORDER BY TIME(timeslot)";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId));
		if ( is_array($result) ){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	public function readAllByStartEnd($startId, $endId){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  id BETWEEN %d AND %d ORDER BY TIME(timeslot)";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $startId, $endId));
		if (is_array($result)){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	public function readAllWeekdaysByAvailability($availabilityId, $returnTrip = null){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  availabilityId = %d AND weekday IS NOT NULL";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		$sql .= " ORDER BY TIME(timeslot)";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId));
		if ( is_array($result) ){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	public function readAllDaysByAvailability($availabilityId, $returnTrip = null){
		$sql = "SELECT DISTINCT day FROM   $this->timeslot_table_name WHERE  availabilityId = %d AND day IS NOT NULL";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		$sql .= " ORDER BY day";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId));
		if ( is_array($result) ){
			$dates = array();
			foreach($result as $r){
				array_push($dates, date(CALENDARISTA_DATEFORMAT, strtotime($r->day)));
			}
			return $dates;
		}
		return false;
	}
	public function readSingleDayByAvailability($selectedDate/*as string*/, $availabilityId, $returnTrip = null){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  availabilityId = %d AND DATE(day) = CONVERT('%s', DATE)";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		$sql .= " ORDER BY TIME(timeslot)";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId, $selectedDate));
		if ( is_array($result) ){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	public function readAllByWeekday($weekday, $availabilityId = null, $returnTrip = null){
		$sql = "SELECT * FROM   $this->timeslot_table_name";
		$where = array();
		$params = array();
		if($weekday !== null){
			array_push($where, 'weekday = %d');
			array_push($params, $weekday);
		}
		if($availabilityId !== null){
			array_push($where, 'availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(is_int($returnTrip)){
			array_push($where, 'IFNULL(returnTrip, 0) = %d');
			array_push($params, (int)$returnTrip);
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY TIME(timeslot)';
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if ( is_array($result) ){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	public function readAllByDate($day, $projectId, $availabilityId = null, $returnTrip = null){
		$sql = "SELECT * FROM   $this->timeslot_table_name";
		$where = array();
		$params = array($day);
		array_push($where, 'DATE(day) = CONVERT(%s, DATE)');
		if($projectId !== null){
			array_push($where, 'projectId = %d');
			array_push($params, $projectId);
		}
		if($availabilityId !== null){
			array_push($where, 'availabilityId = %d OR availabilityId IS NULL');
			array_push($params, $availabilityId);
		}
		if(is_int($returnTrip)){
			array_push($where, 'IFNULL(returnTrip, 0) = %d');
			array_push($params, (int)$returnTrip);
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY TIME(timeslot)';
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if ( is_array($result) ){
			$timeslots = new Calendarista_Timeslots();
			foreach($result as $r){
				$timeslots->add( new Calendarista_Timeslot((array)$r));
			}
			return $timeslots;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			$r = $result[0];
			return new Calendarista_Timeslot((array)$r);
		}
		return false;
	}
	public function readByTimeslot($availabilityId, $selectedDate, $weekday, $timeslot, $returnTrip = null){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  availabilityId = %d AND (DATE(day) = CONVERT('%s', DATE) OR (weekday = %d AND timeslot = '%s'))";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId, $selectedDate, $weekday, $timeslot));
		if($result){
			$r = $result[0];
			return new Calendarista_Timeslot((array)$r);
		}
		return false;
	}
	public function readByTimeslotId($availabilityId, $selectedDate, $weekday, $timeslotId){
		$sql = "SELECT * FROM   $this->timeslot_table_name WHERE  availabilityId = %d AND (DATE(day) = CONVERT('%s', DATE) OR (weekday = %d AND timeslot = (SELECT timeslot FROM  $this->timeslot_table_name WHERE id = %d)))";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $availabilityId, $selectedDate, $weekday, $timeslotId));
		if($result){
			$r = $result[0];
			return new Calendarista_Timeslot((array)$r);
		}
		return false;
	}
	public function availabilityHasReturnTrip($availabilityId){
		$sql = "SELECT count(*) as total FROM   $this->timeslot_table_name WHERE  availabilityId = %d AND returnTrip = 1";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $availabilityId) );
		$r = $result[0];
		return (int)$r->total > 0;
	}
	public function insert($timeslot){
		$params = $this->parseParms($timeslot);
		$result = $this->wpdb->insert($this->timeslot_table_name,  $params[0], $params[1]);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	
	public function update($timeslot){
		$params = $this->parseParms($timeslot);
		$result = $this->wpdb->update($this->timeslot_table_name,  $params[0], array('id'=>$timeslot->id), $params[1]);
		return $result;
	}
	public function updateDeals($availabilityId, $deals){
		$result = $this->wpdb->update($this->timeslot_table_name,  array('deal'=>0), array('availabilityId'=>$availabilityId), array('%d'));
		if($deals){
			foreach($deals as $id){
				$result = $this->wpdb->update($this->timeslot_table_name,  array('deal'=>1), array('id'=>(int)$id), array('%d'));
			}
		}
		return $result;
	}
	public function updateSeat($timeslot, $seats = 1, $oldSeats = 0){
		$bookedSeats = $timeslot->bookedSeats;
		if($oldSeats > 0){
			$bookedSeats -= $oldSeats;
		}
		$seats += $bookedSeats;
		return $this->wpdb->update($this->timeslot_table_name,  array('bookedSeats'=>$seats), array('id'=>$timeslot->id), array('%d'));
	}
	public function updateSeatById($id, $seats = 1, $oldSeats = 0){
		$timeslot = $this->read($id);
		if($timeslot){
			$bookedSeats = $timeslot->bookedSeats;
			if($oldSeats > 0){
				$bookedSeats -= $oldSeats;
			}
			$seats += $bookedSeats;
			return $this->wpdb->update($this->timeslot_table_name,  array('bookedSeats'=>$seats), array('id'=>$id), array('%d'));
		}
		return false;
	}
	public function resetStartTimeByAvailability($availabilityId){
		$params = array(array('startTime'=>0), array('%d'));
		$result = $this->wpdb->update($this->timeslot_table_name,  $params[0], array('availabilityId'=>$availabilityId), $params[1]);
		return $result;
	}
	public function parseParms($timeslot){
		$params = array(
			'timeslot'=>$timeslot->timeslot
			, 'cost'=>$timeslot->cost
			, 'bookedSeats'=>$timeslot->bookedSeats
		);
		$values = array('%s','%f', '%d');
		if(isset($timeslot->availabilityId)){
		  $params['availabilityId'] = $timeslot->availabilityId;
		  array_push($values, '%d');
		}
		if(isset($timeslot->weekday) && $timeslot->weekday !== -1){
		  $params['weekday'] = $timeslot->weekday;
		  array_push($values, '%d');
		}
		if(isset($timeslot->day)){
		  $params['day'] = $timeslot->day->format(CALENDARISTA_DATEFORMAT);
		  array_push($values, '%s');
		}
		if(isset($timeslot->projectId)){
		  $params['projectId'] = $timeslot->projectId;
		  array_push($values, '%d');
		}
		if(isset($timeslot->seats)){
		  $params['seats'] = $timeslot->seats;
		  array_push($values, '%d');
		}
		if(isset($timeslot->seatsMaximum)){
		  $params['seatsMaximum'] = $timeslot->seatsMaximum;
		  array_push($values, '%d');
		}
		if(isset($timeslot->seatsMinimum)){
		  $params['seatsMinimum'] = $timeslot->seatsMinimum;
		  array_push($values, '%d');
		}
		if(isset($timeslot->bookedSeats)){
		  $params['bookedSeats'] = $timeslot->bookedSeats;
		  array_push($values, '%d');
		}
		if(isset($timeslot->paddingTimeBefore)){
		  $params['paddingTimeBefore'] = $timeslot->paddingTimeBefore;
		  array_push($values, '%d');
		}
		if(isset($timeslot->paddingTimeAfter)){
		  $params['paddingTimeAfter'] = $timeslot->paddingTimeAfter;
		  array_push($values, '%d');
		}
		
		$params['deal'] = $timeslot->deal;
		array_push($values, '%d');
		
		$params['startTime'] = $timeslot->startTime;
		array_push($values, '%d');
		
		$params['returnTrip'] = $timeslot->returnTrip;
		array_push($values, '%d');
		
		return array($params, $values);
	}
	public function delete($id){
		$this->deleteHolidayByTimeslot($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->timeslot_table_name WHERE id = %d", $id));
	}
	public function deleteAllByAvailability($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->timeslot_table_name WHERE availabilityId = %d", $id));
	}
	public function deleteWeekdaysByAvailability($id, $returnTrip = null){
		$timeslots = $this->readAllWeekdaysByAvailability($id);
		foreach($timeslots as $timeslot){
			$this->deleteHolidayByTimeslot($timeslot->id);
		}
		$sql = "DELETE FROM $this->timeslot_table_name WHERE availabilityId = %d AND weekday IS NOT NULL";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		return $this->wpdb->query($this->wpdb->prepare($sql, $id));
	}
	public function deleteByWeekday($availabilityId, $weekday, $returnTrip = null){
		$timeslots = $this->readAllByWeekday($weekday, $availabilityId, $returnTrip);
		foreach($timeslots as $timeslot){
			$this->deleteHolidayByTimeslot($timeslot->id);
		}
		$sql = "DELETE FROM $this->timeslot_table_name WHERE availabilityId = %d AND weekday = %d";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		return $this->wpdb->query($this->wpdb->prepare($sql, $availabilityId, $weekday));
	}
	public function deleteByDate($availabilityId, $date, $returnTrip = null){
		$this->deleteHolidayByDate($availabilityId, $date);
		$sql = "DELETE FROM $this->timeslot_table_name WHERE availabilityId = %d AND DATE(day) = CONVERT(%s, DATE)";
		if(is_int($returnTrip)){
			$sql .= " AND IFNULL(returnTrip, 0) = " . (int)$returnTrip;
		}
		return $this->wpdb->query($this->wpdb->prepare($sql, $availabilityId, $date));
	}
	public function deleteExpired(){
		$now = strtotime('now');
		$sql = "DELETE FROM $this->timeslot_table_name WHERE DATE(day) < CONVERT('%s', DATE)";
		return $this->wpdb->query($this->wpdb->prepare($sql, date(CALENDARISTA_FULL_DATEFORMAT, $now)));
	}
	public function deleteByProject($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->timeslot_table_name WHERE projectId = %d", $projectId));
	}
	public function deleteHolidayByTimeslot($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->holidays_table_name WHERE timeslotId = %d", $id));
	}
	public function deleteHolidayByDate($availabilityId, $date){
		$sql = "DELETE FROM $this->holidays_table_name WHERE availabilityId = %d AND DATE(holiday) = CONVERT(%s, DATE)";
		return $this->wpdb->query($this->wpdb->prepare($sql, $availabilityId, $date));
	}
}
?>