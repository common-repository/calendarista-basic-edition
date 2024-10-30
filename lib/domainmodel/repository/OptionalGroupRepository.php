<?php
class Calendarista_OptionalGroupRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $optional_group_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->optional_group_table_name = $wpdb->prefix . 'calendarista_optional_group';
	}
	
	public function readAll($projectId){
		$sql = "SELECT * FROM   $this->optional_group_table_name WHERE  projectId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if ( is_array($result) ){
			$optionalGroups = new Calendarista_OptionalGroups();
			foreach($result as $r){
				$optionalGroups->add( new Calendarista_OptionalGroup((array)$r));
			}
			return $optionalGroups;
		}
		return false;
	}
	public function readAllByIdList($list){
		if(count($list) === 0){
			return false;
		}
		$sql = sprintf("SELECT * FROM   $this->optional_group_table_name WHERE  id IN (%s) ORDER BY orderIndex", implode(',', array_map('intval', $list)));
		$result = $this->wpdb->get_results($sql);
		if ( is_array($result) ){
			$optionalGroups = new Calendarista_OptionalGroups();
			foreach($result as $r){
				$optionalGroups->add( new Calendarista_OptionalGroup((array)$r));
			}
			return $optionalGroups;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->optional_group_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_OptionalGroup((array)$r);
		}
		return false;
	}
	
	public function insert($optionalGroup){
		 $result = $this->wpdb->insert($this->optional_group_table_name,  array(
			'projectId'=>$optionalGroup->projectId
			, 'minRequired'=>$optionalGroup->minRequired
			, 'name'=>$this->encode($optionalGroup->name)
			, 'displayMode'=>$optionalGroup->displayMode
			, 'multiply'=>$optionalGroup->multiply
			, 'maxSelection'=>$optionalGroup->maxSelection
		  ), array('%d', '%d', '%s', '%d', '%d', '%d'));
		  
		 if($result !== false){
			$optionalGroup->id = $this->wpdb->insert_id;
			$optionalGroup->orderIndex = $optionalGroup->id;
			$this->update($optionalGroup);
			return $optionalGroup->id;
		 }
		 return $result;
	}
	
	public function update($optionalGroup){
		$result = $this->wpdb->update($this->optional_group_table_name,  array(
			'orderIndex'=>$optionalGroup->orderIndex
			, 'minRequired'=>$optionalGroup->minRequired
			, 'name'=>$this->encode($optionalGroup->name)
			, 'displayMode'=>$optionalGroup->displayMode
			, 'multiply'=>$optionalGroup->multiply
			, 'maxSelection'=>$optionalGroup->maxSelection
		), array('id'=>$optionalGroup->id), array('%d', '%d', '%s', '%d', '%d', '%d'));
		$optionalGroup->updateResources();
		return $result;
	}
	
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->optional_group_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_group_table_name WHERE id = %d", $id));
	}
	
	public function deleteAll($id){
		$optionalGroups = $this->readAll($id);
		if($optionalGroups->count() === 0){
			return;
		}
		foreach($optionalGroups as $optionalGroup){
			$optionalGroup->deleteResources();
		}
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_group_table_name WHERE projectId = %d", $id));
	}
	
	public function deleteResources($id){
		$optionalGroup = $this->read($id);
		if($optionalGroup){
			$optionalGroup->deleteResources();
		}
	}
}
?>