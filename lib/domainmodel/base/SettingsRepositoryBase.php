<?php
class Calendarista_SettingsRepositoryBase extends Calendarista_RepositoryBase
{
	public $wpdb;
	public $settings_table_name;
	public $settingName;
	public function __construct($settingName){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->settingName = $settingName;
		$this->settings_table_name = $wpdb->prefix . 'calendarista_settings';
	}
	public function read($param = null/*in case we are overriding*/){
		$sql = "SELECT id, 
					   name, 
					   data
				FROM   $this->settings_table_name 
				WHERE  name = '$this->settingName'";
		$result = $this->wpdb->get_results($sql);
		$data = array();
		if($result){
			$r = $result[0];
			$data = (array)unserialize($r->data);
			$data['id'] = (int)$r->id;
		}
		return $data;
	}
	
	public function insert($settings){
		$data = $settings->toArray();
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($data)
		  ), array('%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($settings){
		$data = !is_array($settings) ? $settings->toArray() : $settings;
		$id = !is_array($settings) ? $settings->id : $settings['id'];
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($data)
		), array('id'=>$id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
}
?>