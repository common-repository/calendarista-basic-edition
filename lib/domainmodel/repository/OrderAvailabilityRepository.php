<?php
class Calendarista_OrderAvailabilityRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $order_availability_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_availability_table_name = $wpdb->prefix . 'calendarista_order_availability';
	}
	public function readAll($orderId){
		$where = array();
		$params = array();
		$query = "SELECT * FROM   $this->order_availability_table_name";
		if($orderId){
			array_push($where, 'orderId = %d');
			array_push($params, $orderId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$list = array();
			foreach($result as $r){
				array_push($list, array(
					'id'=>(int)$r->id
					, 'orderId'=>(int)$r->orderId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'availabilityName'=>$r->availabilityName
				));
			}
			return $list;
		}
		return false;
	}
	
	public function insert($args){
		 $result = $this->wpdb->insert($this->order_availability_table_name,  array(
			'orderId'=>$args['orderId']
			, 'availabilityId'=>$args['availabilityId']
			, 'availabilityName'=>$args['availabilityName']
		  ), array('%d', '%d', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function delete($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->order_availability_table_name WHERE orderId = %d", $orderId));
	}
}
?>