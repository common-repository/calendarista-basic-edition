<?php
class Calendarista_PaymentSettingRepository extends Calendarista_SettingsRepositoryBase{
	public function __construct(){
		$settingName = 'Payment';
		parent::__construct($settingName);
	}
	public function read($paymentOperator = null){
		$sql = "SELECT id, name, data FROM   $this->settings_table_name WHERE  name = '$this->settingName'";
		$result = $this->wpdb->get_results($sql);
		if(is_array($result)){
			foreach($result as $r){
				$data = (array)unserialize($r->data);
				if($data['paymentOperator'] === $paymentOperator){
					$data['id'] = $r->id;
					return $data;
				}
			}
		}
		return false;
	}
	public function readAll(){
		$sql = "SELECT id, name, data FROM   $this->settings_table_name WHERE  name = '$this->settingName'";
		$result = $this->wpdb->get_results($sql);
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				$data = (array)unserialize($r->data);
				if($data['paymentOperator'] === 2){
					//2checkout deprecated
					continue;
				}
				$data['id'] = $r->id;
				array_push($resultset, $data);
			}
			return $resultset;
		}
		return false;
	}
	public function insert($settings){
		$data = $settings->toArray();
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($data)
		  ), array('%s', '%s'));
		  
		 if($result !== false){
			$settings->id = $this->wpdb->insert_id;
			$settings->updateResources();
			return $settings->id;
		 }
		 return $result;
	}
	
	public function update($settings){
		$data = !is_array($settings) ? $settings->toArray() : $settings;
		$id = !is_array($settings) ? $settings->id : $settings['id'];
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($data)
		), array('id'=>$id), array('%s'));
		$settings->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		return $this->wpdb->query($this->wpdb->prepare($sql, $id));
	}
	public function deleteResources($id){
		$setting = $this->read();
		if($setting){
			$setting->deleteResources();
		}
	}
}
?>