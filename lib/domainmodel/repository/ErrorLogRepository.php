<?php
class Calendarista_ErrorLogRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $error_log_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->error_log_table_name = $wpdb->prefix . 'calendarista_error_log';
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->error_log_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT id, entryDate, message FROM $this->error_log_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $id) );
		if($result){
			return new Calendarista_ErrorLog((array)$result[0]);
		}
		return false;
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
		if(!$orderBy){
			$orderBy = 'id';
		}
		if(!$order){
			$order = 'asc';
		}
		$query = "SELECT id, entryDate, message FROM  $this->error_log_table_name";
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results($query);
		$total = 0;
		if( is_array($result) ){
			$query = "SELECT count(id) as total FROM   $this->error_log_table_name";
			$records = $this->wpdb->get_results($query);
			$errorsLog = new Calendarista_ErrorsLog();
			$errorsLog->total = (int)$records[0]->total;
			foreach($result as $r){
				$errorsLog->add(new Calendarista_ErrorLog((array)$r));
			}
			return $errorsLog;
		}
		return false;
	}
	
	public function insert($errorLog){
		 $result = $this->wpdb->insert($this->error_log_table_name,  array(
			'message'=>serialize($errorLog->message)
			, 'entryDate'=>$errorLog->entryDate->format(CALENDARISTA_DATEFORMAT)
		  ), array('%s', '%s'));
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($errorLog){
		$result = $this->wpdb->update($this->error_log_table_name,  array(
			'message'=>serialize($errorLog->message)
		), array('id'=>$errorLog->id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->error_log_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	
	public function deleteAll(){
		$sql = "DELETE FROM $this->error_log_table_name";
		$rows_affected = $this->wpdb->query( $sql );
		return $rows_affected;
	}
	
	public function deleteExpired($fromDate){
		$sql = "DELETE FROM $this->error_log_table_name WHERE (entryDate < CONVERT( '$fromDate', DATETIME))";
		$rows_affected = $this->wpdb->query( $sql );
		return $rows_affected;
	}
}
?>