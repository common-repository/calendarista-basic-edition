<?php
class Calendarista_RemindersRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $reminder_table_name;
	private $availability_booked;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->reminder_table_name = $wpdb->prefix . 'calendarista_reminders';
		$this->availability_booked = $wpdb->prefix . 'calendarista_availability_booked';
	}
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc'){
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
		$query = "SELECT rm.* FROM $this->reminder_table_name AS rm";
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( $query);
		if (is_array($result) ){
			$query = "SELECT COUNT(*) as total FROM $this->reminder_table_name";
			$records = $this->wpdb->get_results($query);
			$emailReminders = new Calendarista_EmailReminders();
			$emailReminders->total = (is_array($records) && count($records) > 0) ? (int)$records[0]->total : 0;
			foreach($result as $r){
				$emailReminders->add(new Calendarista_EmailReminder(array(
					'fullName'=>$r->fullName
					, 'email'=>$r->email
					, 'sentDate'=>new Calendarista_DateTime((string)$r->sentDate)
					, 'bookedAvailabilityId'=>(int)$r->bookedAvailabilityId
					, 'orderId'=>(int)$r->orderId
					, 'projectId'=>(int)$r->projectId
					, 'reminderType'=>is_numeric($r->reminderType) ? (int)$r->reminderType : 0
					, 'id'=> (int)$r->id
				)));
			}
			return $emailReminders;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT * FROM $this->reminder_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_EmailReminder(array(
				'fullName'=>$r->fullName
				, 'email'=>$r->email
				, 'sentDate'=>new Calendarista_DateTime((string)$r->sentDate)
				, 'bookedAvailabilityId'=>(int)$r->bookedAvailabilityId
				, 'orderId'=>(int)$r->orderId
				, 'projectId'=>(int)$r->projectId
				, 'reminderType'=>is_numeric($r->reminderType) ? (int)$r->reminderType : 0
				, 'id'=> (int)$r->id
			));
		}
		return false;
	}
	public function readByOrder($orderId){
		$sql = "SELECT * FROM $this->reminder_table_name WHERE orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	
	public function insert($emailReminder){
		 $result = $this->wpdb->insert($this->reminder_table_name,  array(
			'projectId'=>$emailReminder->projectId
			, 'orderId'=>$emailReminder->orderId
			, 'bookedAvailabilityId'=>$emailReminder->bookedAvailabilityId
			, 'fullName'=>$emailReminder->fullName
			, 'email'=>$emailReminder->email
			, 'sentDate'=>$emailReminder->sentDate->format(CALENDARISTA_DATEFORMAT)
			, 'reminderType'=>$emailReminder->reminderType
		  ), array('%d', '%d', '%d', '%s', '%s', '%s', '%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}

	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->reminder_table_name WHERE id = %d", $id));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->reminder_table_name WHERE orderId = %d", $orderId));
	}
	public function deleteAll(){
		return $this->wpdb->query("DELETE FROM $this->reminder_table_name");
	}
}
?>