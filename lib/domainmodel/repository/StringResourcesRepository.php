<?php
class Calendarista_StringResourcesRepository extends Calendarista_RepositoryBase
{
	public $wpdb;
	public $string_resources_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->string_resources_table_name = $wpdb->prefix . 'calendarista_string_resources';
	}
	public function read($id){
		$sql = "SELECT id, projectId, data FROM $this->string_resources_table_name WHERE id = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $id));
		$data = array();
		$id = null;
		$projectId = $id;
		if($result){
			$r = $result[0];
			$data = (array)unserialize($r->data);
			$id = (int)$r->id;
			$projectId = (int)$r->projectId;
		}
		return new Calendarista_StringResources($data, $id, $projectId);
	}
	public function readByProject($projectId){
		$sql = "SELECT id, projectId, data FROM   $this->string_resources_table_name WHERE projectId = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $projectId));
		$data = array();
		$id = null;
		$projectId = $projectId;
		if($result){
			$r = $result[0];
			$data = (array)unserialize($r->data);
			$id = (int)$r->id;
			$projectId = (int)$r->projectId;
		}
		return new Calendarista_StringResources($data, $id, $projectId);
	}
	
	public function insert($stringResources){
		$data = $stringResources->toArray();
		 $result = $this->wpdb->insert($this->string_resources_table_name,  array(
			'projectId'=>$stringResources->projectId
			, 'data'=>serialize($data)
		  ), array('%d', '%s'));
		  
		 if($result !== false){
			$stringResources->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($stringResources){
		$data = $stringResources->toArray();
		$result = $this->wpdb->update($this->string_resources_table_name,  array(
			'data'=>serialize($data)
		), array('id'=>$stringResources->id), array('%s'));
		$stringResources->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		$sql = "DELETE FROM $this->string_resources_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	
	public function deleteByProject($projectId){
		$result = $this->readByProject($projectId);
		if($result){
			$this->deleteResources($result->id);
		}
		$sql = "DELETE FROM $this->string_resources_table_name WHERE projectId = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $projectId) );
		return $rows_affected;
	}
	
	public function deleteResources($id){
		$result = $this->read($id);
		if($result){
			$result->deleteResources();
		}
	}
}
?>