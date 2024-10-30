<?php
class Calendarista_CustomFormElementController extends Calendarista_BaseController{
	private $repo;
	private $formElement;
	public function __construct($formElement, $newFormCallback, $sortOrderCallback, $createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'customform')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->formElement = $formElement;
		$this->repo = new Calendarista_FormElementRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_new', $_POST)){
			$this->newForm($newFormCallback);
		}
		if (array_key_exists('calendarista_sortorder', $_POST)){
			$this->updateSortOrder($sortOrderCallback);
		}
	}
	
	public function newForm($callback){
		$this->executeCallback($callback, array($this->formElement));
	}
	public function create($callback){
		$result = $this->repo->insert($this->formElement);
		if($result !== false){
			$this->formElement->id = $result;
			$this->formElement->orderIndex = $result;
		}
		$this->executeCallback($callback, array($this->formElement, $result));
	}
	public function update($callback){
		$result = $this->repo->update($this->formElement);
		$this->executeCallback($callback, array($this->formElement, $result));
	}
	public function updateSortOrder($callback){
		$sortOrder = $this->getPostValue('sortOrder');
		$result = false;
		if($sortOrder){
			$orderList = explode(',', $sortOrder);
			foreach($orderList as $ol){
				$item = explode(':', $ol);
				$this->repo->updateSortOrder((int)$item[0], (int)$item[1]);
			}
			$result = true;
		}
		$this->executeCallback($callback, array($result));
	}
	public function delete($callback){
		$formElements = $this->getPostValue('formelements');
		if(!$formElements){
			$formElements = array($this->formElement->id);
		}
		$result = false;
		foreach($formElements as $formElement){
			$result = $this->repo->delete((int)$formElement);
			if(!$result){
				break;
			}
		}
		$this->executeCallback($callback, array($result));
	}
}
?>