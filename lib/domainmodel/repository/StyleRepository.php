<?php
class Calendarista_StyleRepository extends Calendarista_RepositoryBase
{
	public $wpdb;
	public $style_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->style_table_name = $wpdb->prefix . 'calendarista_style';
	}
	protected function getStyle($val){
		$args = array();
		if(array_key_exists('theme', $val)){
			$args = array(
				'projectId'=>(int)$val['projectId'], 
				'id'=>(int)$val['id'], 
				'theme'=>$val['theme'], 
				'partiallyThemed'=>isset($val['partiallyThemed']) ? $val['partiallyThemed'] : false,
				'fontFamily'=>$val['fontFamily'], 
				'thumbnailWidth'=>$val['thumbnailWidth'], 
				'thumbnailHeight'=>$val['thumbnailHeight'],
				'bookingSummaryTemplate'=>$val['bookingSummaryTemplate']
			);
		}
		return Calendarista_StyleHelper::getStyle($args);
	}
	public function read($id){
		$sql = "SELECT id, projectId, data FROM   $this->style_table_name WHERE id = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $id));
		$data = array();
		if($result){
			$r = $result[0];
			$data = (array)unserialize($r->data);
			$data['id'] = (int)$r->id;
			$data['projectId'] = (int)$r->projectId;
		}
		return $this->getStyle($data);
	}
	public function readByProject($projectId){
		$sql = "SELECT id, projectId, data FROM   $this->style_table_name WHERE projectId = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $projectId));
		$data = array();
		if($result){
			$r = $result[0];
			$data = (array)unserialize($r->data);
			$data['id'] = (int)$r->id;
			$data['projectId'] = (int)$r->projectId;
		}
		return $this->getStyle($data);
	}
	public function readAll(){
		$sql = "SELECT id, projectId, data FROM   $this->style_table_name";
		$result = $this->wpdb->get_results($sql);
		if (is_array($result)){
			$styles = array();
			foreach($result as $r){
				$data = (array)unserialize($r->data);
				$data['id'] = (int)$r->id;
				$data['projectId'] = (int)$r->projectId;
				array_push($styles, $this->getStyle($data));
			}
			return $styles;
		}
		return false;
	}
	public function insert($style){
		$data = $style->toArray();
		$data['bookingSummaryTemplate'] = stripslashes($data['bookingSummaryTemplate']);
		 $result = $this->wpdb->insert($this->style_table_name,  array(
			'projectId'=>$style->projectId
			, 'data'=>serialize($data)
		  ), array('%d', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($style){
		$data = $style->toArray();
		$data['bookingSummaryTemplate'] = stripslashes($data['bookingSummaryTemplate']);
		$result = $this->wpdb->update($this->style_table_name,  array(
			'data'=>serialize($data)
		), array('id'=>$style->id), array('%s'));
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->style_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	public function deleteByProject($projectId){
		$sql = "DELETE FROM $this->style_table_name WHERE projectId = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $projectId) );
		return $rows_affected;
	}
}
?>