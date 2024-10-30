<?php
class Calendarista_OptionalRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $optional_table_name;
	private $optional_booked_table_name;
	private $availability_booked_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->optional_table_name = $wpdb->prefix . 'calendarista_optional';
		$this->optional_booked_table_name = $wpdb->prefix . 'calendarista_optionals_booked';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
	}
	
	public function readUsedIncrementalInputQuantity($args){
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) ? $args['toDate'] : null;
		$projectId = isset($args['projectId']) ? (int)$args['projectId'] : null;
		$optionalId = isset($args['optionalId']) ? (int)$args['optionalId'] : null;
		$sql = "SELECT IFNULL(SUM(incrementValue), 0) as quantity FROM $this->optional_booked_table_name where orderId IN (SELECT orderId FROM $this->availability_booked_table_name where fromDate = %s AND toDate = %s AND projectId = %d AND orderId IN(SELECT orderId FROM $this->optional_booked_table_name WHERE optionalId = %d)) AND optionalId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $fromDate, $toDate, $projectId, $optionalId, $optionalId) );
		if (is_array($result)){
			return (int)$result[0]->quantity;
		}
		return 0;
	}
	public function readUsedQuantity($args){
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) ? $args['toDate'] : null;
		$projectId = isset($args['projectId']) ? (int)$args['projectId'] : null;
		$optionalId = isset($args['optionalId']) ? (int)$args['optionalId'] : null;
		$sql = "SELECT count(*) as quantity FROM $this->optional_booked_table_name where orderId IN (SELECT orderId FROM $this->availability_booked_table_name where fromDate = %s AND toDate = %s AND projectId = %d AND orderId IN(SELECT orderId FROM $this->optional_booked_table_name WHERE optionalId = %d)) AND optionalId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $fromDate, $toDate, $projectId, $optionalId, $optionalId) );
		if (is_array($result)){
			return (int)$result[0]->quantity;
		}
		return 0;
	}
	public function readAll($projectId){
		$sql = "SELECT t1.* FROM $this->optional_table_name as t1 WHERE  projectId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if ( is_array($result) ){
			$optionals = new Calendarista_Optionals();
			foreach($result as $r){
				$optionals->add( new Calendarista_Optional((array)$r));
			}
			return $optionals;
		}
		return false;
	}
	public function readAllByIdList($list){
		$params = $list;
		if(is_array($list)){
			$params = implode(',', array_map('intval', $list));
		}
		$sql = "SELECT t1.* FROM  $this->optional_table_name as t1 WHERE  id IN (" . $params . ") ORDER BY orderIndex";
		$result = $this->wpdb->get_results($sql);
		if ( is_array($result) ){
			$optionals = new Calendarista_Optionals();
			foreach($result as $r){
				$optionals->add( new Calendarista_Optional((array)$r));
			}
			return $optionals;
		}
		return false;
	}
	public function readAllByGroup($groupId){
		$sql = "SELECT t1.* FROM $this->optional_table_name as t1  WHERE  t1.groupId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $groupId) );
		if ( is_array($result) ){
			$optionals = new Calendarista_Optionals();
			foreach($result as $r){
				$optionals->add( new Calendarista_Optional((array)$r));
			}
			return $optionals;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT t1.* FROM $this->optional_table_name as t1 WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_Optional((array)$r);
		}
		return false;
	}
	
	public function insert($optional){
		 $result = $this->wpdb->insert($this->optional_table_name,  array(
			'projectId'=>$optional->projectId
			, 'groupId'=>$optional->groupId
			, 'name'=>$this->encode($optional->name)
			, 'cost'=>$optional->cost
			, 'quantity'=>$optional->quantity
			, 'doubleCostIfReturn'=>$optional->doubleCostIfReturn
			, 'description'=>$optional->description
			, 'thumbnailUrl'=>$optional->thumbnailUrl
			, 'minIncrement'=>$optional->minIncrement
			, 'maxIncrement'=>$optional->maxIncrement
			, 'limitMode'=>$optional->limitMode
		  ), array('%d', '%d', '%s', '%f', '%d', '%d', '%s', '%s', '%d', '%d', '%d'));
		  
		 if($result !== false){
			$optional->id = $this->wpdb->insert_id;
			$this->updateSortOrder($optional->id, $optional->id);
			$optional->updateResources();
			return $optional->id;
		 }
		 return $result;
	}
	
	public function update($optional){
		$result = $this->wpdb->update($this->optional_table_name,  array(
			'groupId'=>$optional->groupId
			, 'orderIndex'=>$optional->orderIndex
			, 'name'=>$this->encode($optional->name)
			, 'cost'=>$optional->cost
			, 'quantity'=>$optional->quantity
			, 'doubleCostIfReturn'=>$optional->doubleCostIfReturn
			, 'description'=>$optional->description
			, 'thumbnailUrl'=>$optional->thumbnailUrl
			, 'minIncrement'=>$optional->minIncrement
			, 'maxIncrement'=>$optional->maxIncrement
			, 'limitMode'=>$optional->limitMode
		), array('id'=>$optional->id), array('%d', '%d', '%s', '%f', '%d', '%d', '%s', '%s', '%d', '%d', '%d'));
		$optional->updateResources();
		return $result;
	}
	
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->optional_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_table_name WHERE id = %d", $id));
	}
	public function deleteByGroup($id){
		$optionals = $this->readAllByGroup($id);
		foreach($optionals as $optional){
			$this->deleteResources($optional->id);
		}
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_table_name WHERE groupId = %d", $id));
	}
	public function deleteAll($id){
		$optionals = $this->readAll($id);
		if($optionals->count() === 0){
			return;
		}
		foreach($optionals as $optional){
			$optional->deleteResources();
		}
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_table_name WHERE projectId = %d", $id));
	}
	public function deleteResources($id){
		$optional = $this->read($id);
		if($optional){
			$optional->deleteResources();
		}
	}
}
?>