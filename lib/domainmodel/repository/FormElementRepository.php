<?php
class Calendarista_FormElementRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $form_element_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->form_element_table_name = $wpdb->prefix . 'calendarista_formelement';
	}
	
	public function readAll($projectId){
		$sql = "SELECT * FROM  $this->form_element_table_name WHERE projectId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if( is_array( $result )){
			$formElements = new Calendarista_FormElements();
			foreach($result as $r){
				$formElements->add(new Calendarista_FormElement((array)$r));
			}
			return $formElements;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT * FROM $this->form_element_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ($result ){
			$r = $result[0];
			return new Calendarista_FormElement((array)$r);
		}
		return false;
	}
	public function readByElementType($projectId, $elementTypes){
		$sql = "SELECT * FROM $this->form_element_table_name WHERE projectId = %d AND elementType IN (" . implode(',', array_map('intval', $elementTypes)) . ")";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if( is_array( $result )){
			$formElements = new Calendarista_FormElements();
			foreach($result as $r){
				$formElements->add(new Calendarista_FormElement((array)$r));
			}
			return $formElements;
		}
		return false;
	}
	public function hasPhoneNumber(){
		$sql = "SELECT count(*) as count FROM  $this->form_element_table_name WHERE elementType = 8";
		$result = $this->wpdb->get_results($sql);
		return (int)$result[0]->count > 0;
	}
	public function insert($formElement){
		$p = $this->parseParams($formElement);
		$result = $this->wpdb->insert($this->form_element_table_name,  $p['params'], $p['values']);
		if($result !== false){
			$formElement->id = $this->wpdb->insert_id;
			$formElement->orderIndex = $formElement->id;
			$this->update($formElement);
			return $formElement->orderIndex;
		}
		return $result;
	}
	public function update($formElement){
		$p = $this->parseParams($formElement);
		$result = $this->wpdb->update($this->form_element_table_name,  $p['params'], array('id'=>$formElement->id), $p['values']);
		$formElement->updateResources();
		return $result;
	}
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->form_element_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	public function delete($id){
		$this->deleteResources($id);
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_table_name WHERE id = %d", $id) );
	}
	public function deleteAll($id){
		$this->deleteResources($id);
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_table_name WHERE projectId = %d", $id) );
	}
	public function deleteResources($id){
		$formElement = $this->read($id);
		if($formElement){
			$formElement->deleteResources();
		}
	}
	private function parseParams($formElement){
		$params = array();
		$values = array();
		$params['projectId'] = $formElement->projectId;
		array_push($values, '%d');
		if(isset($formElement->orderIndex)){
			$params['orderIndex'] = $formElement->orderIndex;
			array_push($values, '%d');
		}
		
		$params['label'] = $formElement->label ? $this->encode($formElement->label) : null;
		array_push($values, '%s');
		
		if(isset($formElement->elementType)){
			$params['elementType'] = $formElement->elementType;
			array_push($values, '%d');
		}
		
		$params['lineSeparator'] = isset($formElement->lineSeparator) ? $formElement->lineSeparator : null;
		array_push($values, '%d');
		
		$params['className'] = $formElement->className ? $this->encode($formElement->className) : null;
		array_push($values, '%s');
	
		if($formElement->options){
			$params['options'] = implode(',', $formElement->options);
			array_push($values, '%s');
		}
		
		$params['defaultOptionItem'] = $formElement->defaultOptionItem ? $this->encode($formElement->defaultOptionItem) : null;
		array_push($values, '%s');
		
		
		$params['defaultSelectedOptionItem'] = $formElement->defaultSelectedOptionItem ? $this->encode($formElement->defaultSelectedOptionItem) : null;
		array_push($values, '%s');
		
		
		$params['validation'] = $formElement->validation ? http_build_query($formElement->validation) : null;
		array_push($values, '%s');
		
		$params['placeHolder'] = $formElement->placeHolder ? $this->encode($formElement->placeHolder) : null;
		array_push($values, '%s');
		
		$params['content'] = $formElement->content ? $this->encode($formElement->content) : null;
		array_push($values, '%s');
		
		if($formElement->country){
			$params['country'] = $this->encode($formElement->country);
			array_push($values, '%s');
		}
		
		$params['phoneNumberField'] = $formElement->phoneNumberField;
		array_push($values, '%d');
		
		$params['guestField'] = $formElement->guestField;
		array_push($values, '%d');
		
		
		return array('params'=>$params, 'values'=>$values);
	}
}
?>