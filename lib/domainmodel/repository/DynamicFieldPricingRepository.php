<?php
class Calendarista_DynamicFieldPricingRepository extends Calendarista_RepositoryBase
{
	public $wpdb;
	public $dynamic_field_pricing_table_name;
	public $settingName;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->dynamic_field_pricing_table_name = $wpdb->prefix . 'calendarista_dynamic_field_pricing';
	}
	public function read($id){
		$sql = "SELECT *
				FROM   $this->dynamic_field_pricing_table_name 
				WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if($result){
			$r = $result[0];
			return array(
					'id'=>(int)$r->id
					, 'dynamicFieldId'=>(int)$r->dynamicFieldId
					, 'cost'=>(float)$r->cost
					, 'fieldValue'=>(int)$r->fieldValue
			);
		}
		return false;
	}
	public function readByDynamicFieldId($id){
		$sql = "SELECT *
				FROM   $this->dynamic_field_pricing_table_name 
				WHERE  dynamicFieldId = %d order by id";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$r->id
					, 'dynamicFieldId'=>(int)$r->dynamicFieldId
					, 'cost'=>(float)$r->cost
					, 'fieldValue'=>(int)$r->fieldValue
				));
			}
			return $resultset;
		}
		return false;
	}
	public function checkDuplicate($dynamicFieldId, $fieldValue){
		$sql = "SELECT *
				FROM   $this->dynamic_field_pricing_table_name 
				WHERE  dynamicFieldId = %d AND fieldValue = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $dynamicFieldId, $fieldValue));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset,
				array(
					'id'=>(int)$r->id
					, 'dynamicFieldId'=>(int)$r->dynamicFieldId
					, 'cost'=>(float)$r->cost
					, 'fieldValue'=>(int)$r->fieldValue
				));
			}
			return $resultset;
		}
		return false;
	}
	public function insert($args){
		 $result = $this->wpdb->insert($this->dynamic_field_pricing_table_name,  array(
			'dynamicFieldId'=>$args['dynamicFieldId']
			, 'cost'=>$args['cost']
			, 'fieldValue'=>$args['fieldValue']
		  ), array('%d', '%f','%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function update($args){
		$result = $this->wpdb->update($this->dynamic_field_pricing_table_name,  array(
			'cost'=>$args['cost']
			, 'fieldValue'=>$args['fieldValue']
		), array('id'=>$args['id']), array('%f','%d'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->dynamic_field_pricing_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
}
?>