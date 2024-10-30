<?php
class Calendarista_BookedFormElementRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $form_element_booked_table_name;
	private $form_element_table_name;
	private $availability_booked_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->form_element_booked_table_name = $wpdb->prefix . 'calendarista_formelement_booked';
		$this->form_element_table_name = $wpdb->prefix . 'calendarista_formelement';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
	}
	
	public function readAll($orderId){
		$sql = "SELECT * FROM $this->form_element_booked_table_name WHERE orderId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if( is_array( $result )){
			return $result;
		}
		return false;
	}
	public function getPhoneNumber($orderId){
		$sql = "SELECT feb.value as phoneNumber FROM $this->form_element_booked_table_name as feb INNER JOIN $this->form_element_table_name as fe ON feb.elementId = fe.id WHERE feb.orderId = %d AND ((fe.elementType = 0 AND fe.phoneNumberField = 1) OR fe.elementType = 8) ORDER BY feb.orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if( is_array( $result )){
			return $result;
		}
		return false;
	}
	public function readByElements($orderId, $formElements){
		if(!$formElements){
			return false;
		}
		$elementList =  join(',', array_map('intval', $formElements));
		$sql = "SELECT * FROM $this->form_element_booked_table_name WHERE orderId = %d AND elementId IN (" . $elementList . ") ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId));
		if(is_array($result)){
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
		$order = isset($args['order']) && !empty($args['order']) ? $args['order'] : null;
		$query = "SELECT f.*, a.id as availabilityId FROM $this->form_element_booked_table_name as f LEFT JOIN $this->availability_booked_table_name as a ON f.orderId = a.orderId";
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
		if($order){
			$query .= ' ' . $order;
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->form_element_booked_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ($result ){
			return $result[0];
		}
		return false;
	}
	
	public function insert($formElement){
		$p = $this->parseParams($formElement);
		$result = $this->wpdb->insert($this->form_element_booked_table_name, $p['params'], $p['values']);
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function update($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->update($this->form_element_booked_table_name,  $p['params'], array('id'=>$args['id']), $p['values']);
		return $result;
	}
	public function parseParams($args){
		$params = array();
		$values = array();

		if(array_key_exists('orderId', $args)){
			$params['orderId'] = $args['orderId'];
			array_push($values, '%d');
		}
		
		if(array_key_exists('elementId', $args)){
			$params['elementId'] = $args['elementId'];
			array_push($values, '%d');
		}
		
		if(array_key_exists('projectId', $args)){
			$params['projectId'] = $args['projectId'];
			array_push($values, '%d');
		}
		
		if(array_key_exists('orderIndex', $args)){
			$params['orderIndex'] = $args['orderIndex'];
			array_push($values, '%d');
		}
		
		if(array_key_exists('label', $args)){
			$params['label'] = $args['label'];
			array_push($values, '%s');
		}
		
		if(array_key_exists('value', $args)){
			$params['value'] = $args['value'];
			array_push($values, '%s');
		}
		
		if(array_key_exists('guestIndex', $args)){
			$params['guestIndex'] = $args['guestIndex'];
			array_push($values, '%d');
		}
		return array('params'=>$params, 'values'=>$values);
	}
	public function delete($id){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_booked_table_name WHERE id = %d", $id));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_booked_table_name WHERE orderId = %d", $orderId));
	}
	public function deleteAll($projectId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_booked_table_name WHERE projectId = %d", $projectId));
	}
}
?>