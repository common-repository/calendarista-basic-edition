<?php
class Calendarista_DynamicFieldRepository extends Calendarista_RepositoryBase
{
	public $wpdb;
	public $dynamic_field_table_name;
	public $settingName;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->dynamic_field_table_name = $wpdb->prefix . 'calendarista_dynamic_field';
	}
	public function read($id){
		$sql = "SELECT *
				FROM   $this->dynamic_field_table_name 
				WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if($result){
			$r = $result[0];
			return new Calendarista_DynamicField((array)$r);
		}
		return false;
	}
	public function readByAvailabilityId($args){
		$availabilityId = $args['availabilityId'];
		$pageIndex = isset($args['pageIndex']) ? $args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? $args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? $args['orderBy'] : null;
		$order = isset($args['order']) ? $args['order'] : null;
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
			$order = 'desc';
		}
		$sql = "SELECT *
				FROM   $this->dynamic_field_table_name 
				WHERE  availabilityId = %d";
				
		$sql .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex !== null && $pageIndex !== -1){
			$sql .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $availabilityId));
		if( is_array($result)){
			$query = "SELECT count(id) as total FROM $this->dynamic_field_table_name 
				WHERE  availabilityId = %d";
			$records = $this->wpdb->get_results($this->wpdb->prepare($query, $availabilityId));
			$resultset = array();
			foreach($result as $r){
				$df = new Calendarista_DynamicField((array)$r);
				array_push($resultset, $df);
			}
			return array('resultset'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	public function insert($dynamicField){
		$args = $dynamicField->toArray();
		$result = $this->wpdb->insert($this->dynamic_field_table_name,  array(
			'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'label'=>$args['label']
			, 'byOptional'=>$args['byOptional']
			, 'limitBySeat'=>$args['limitBySeat']
			, 'cost'=>$args['cost']
			, 'required'=>$args['required']
			, 'data'=>serialize($args['data'])
			, 'fixedCost'=>$args['fixedCost']
		), array('%d', '%d', '%s', '%d', '%d', '%f', '%d','%s', '%d'));
		  
		if($result !== false){
			$dynamicField->id = $this->wpdb->insert_id;
			$dynamicField->updateResources();
			return $dynamicField->id;
		}
		return $result;
	}
	public function getFieldsCount($availabilityId){
		$sql = "SELECT count(*) as total
				FROM   $this->dynamic_field_table_name 
				WHERE  availabilityId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $availabilityId));
		if($result){
			$r = $result[0];
			return (int)$r->total;
		}
		return 0;
	}
	public function update($dynamicField){
		$args = $dynamicField->toArray();
		$result = $this->wpdb->update($this->dynamic_field_table_name,  array(
			'label'=>$args['label']
			, 'byOptional'=>$args['byOptional']
			, 'limitBySeat'=>$args['limitBySeat']
			, 'cost'=>$args['cost']
			, 'required'=>$args['required']
			, 'data'=>serialize($args['data'])
			, 'fixedCost'=>$args['fixedCost']
		), array('id'=>$args['id']), array('%s', '%d', '%d', '%f', '%d', '%s', '%d'));
		$dynamicField->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		$sql = "DELETE FROM $this->dynamic_field_table_name WHERE id = %d";
		return $this->wpdb->query( $this->wpdb->prepare($sql, $id));
	}
	public function deleteResources($id){
		$df = $this->read($id);
		if($df){
			$df->deleteResources();
		}
	}
}
?>