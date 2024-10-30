<?php
class Calendarista_BookedDynamicFieldRepository extends Calendarista_RepositoryBase
{
	private $wpdb;
	private $dynamic_field_booked_table_name;
	private $availability_booked_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->dynamic_field_booked_table_name = $wpdb->prefix . 'calendarista_dynamic_field_booked';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
	}
	public function read($id){
		$sql = "SELECT id, 
					   orderId, 
					   projectId,
					   availabilityId,
					   dynamicFieldId,
					   label,
					   value,
					   cost,
					   limitBySeat,
					   byOptional,
					   fixedCost
				FROM   $this->dynamic_field_booked_table_name 
				WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if($result){
			$r = $result[0];
			$data = array(
				'id'=>$r->id
				, 'orderId'=>$r->orderId
				, 'projectId'=>$r->projectId
				, 'availabilityId'=>$r->availabilityId
				, 'dynamicFieldId'=>$r->dynamicFieldId
				, 'label'=>$r->label
				, 'value'=>(int)$r->value
				, 'cost'=>(float)$r->cost
				, 'limitBySeat'=>(bool)$r->limitBySeat
				, 'byOptional'=>(bool)$r->byOptional
				, 'fixedCost'=>(bool)$r->fixedCost
			);
			return $data;
		}
		return false;
	}
	public function readByOrderId($orderId){
		$sql = "SELECT id, 
					   orderId, 
					   projectId,
					   availabilityId,
					   dynamicFieldId,
					   label,
					   value,
					   cost,
					   byOptional,
					   limitBySeat,
					   fixedCost
				FROM   $this->dynamic_field_booked_table_name 
				WHERE  orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId));
		if( is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>$r->id
					, 'orderId'=>$r->orderId
					, 'projectId'=>$r->projectId
					, 'availabilityId'=>$r->availabilityId
					, 'dynamicFieldId'=>$r->dynamicFieldId
					, 'label'=>$r->label
					, 'value'=>(int)$r->value
					, 'cost'=>(float)$r->cost
					, 'limitBySeat'=>(bool)$r->limitBySeat
					, 'byOptional'=>(bool)$r->byOptional
					, 'fixedCost'=>(bool)$r->fixedCost
				));
			}
			return $resultset;
		}
		return false;
	}
	public function export($args){
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$where = array();
		$params = array();
		$query = "SELECT d.id, 
					   d.orderId, 
					   d.projectId,
					   a.id as availabilityId,
					   d.dynamicFieldId,
					   d.label,
					   d.value,
					   d.cost
				FROM   $this->dynamic_field_booked_table_name as d LEFT JOIN $this->availability_booked_table_name as a ON d.orderId = a.orderId";
		if($projectId){
			array_push($where, 'd.projectId = %d');
			array_push($params, $projectId);
		}
		if($availabilityId && $availabilityId != -1){
			array_push($where, 'd.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if( is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>$r->id
					, 'orderId'=>$r->orderId
					, 'projectId'=>$r->projectId
					, 'availabilityId'=>$r->availabilityId
					, 'dynamicFieldId'=>$r->dynamicFieldId
					, 'label'=>$r->label
					, 'value'=>(int)$r->value
					, 'cost'=>(float)$r->cost
				));
			}
			return $resultset;
		}
		return false;
	}
	public function insert($args){
		 $result = $this->wpdb->insert($this->dynamic_field_booked_table_name,  array(
			'orderId'=>$args['orderId']
			, 'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'dynamicFieldId'=>$args['dynamicFieldId']
			, 'label'=>$args['label']
			, 'value'=>$args['value']
			, 'cost'=>$args['cost']
			, 'limitBySeat'=>$args['limitBySeat']
			, 'byOptional'=>$args['byOptional']
			, 'fixedCost'=>$args['fixedCost']
		  ), array('%d', '%d','%d','%d', '%s','%s', '%f', '%d', '%d', '%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function update($args){
		$result = $this->wpdb->update($this->dynamic_field_booked_table_name,  array(
			'orderId'=>$args['orderId']
			, 'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'dynamicFieldId'=>$args['dynamicFieldId']
			, 'label'=>$args['label']
			, 'value'=>$args['value']
			, 'cost'=>$args['cost']
			, 'limitBySeat'=>$args['limitBySeat']
			, 'byOptional'=>$args['byOptional']
			, 'fixedCost'=>$args['fixedCost']
		), array('id'=>$args['id']), array('%d', '%d','%d','%d', '%s','%s', '%f', '%d', '%d', '%d'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->dynamic_field_booked_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	public function deleteByOrder($orderId){
		$sql = "DELETE FROM $this->dynamic_field_booked_table_name WHERE orderId = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $orderId) );
		return $rows_affected;
	}
	public function deleteByAvailabilityId($availabilityId){
		$sql = "DELETE FROM $this->dynamic_field_booked_table_name WHERE availabilityId = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $availabilityId) );
		return $rows_affected;
	}
	public function deleteAll($projectId){
		$sql = "DELETE FROM $this->dynamic_field_booked_table_name WHERE projectId = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $projectId) );
		return $rows_affected;
	}
}
?>