<?php
class Calendarista_OptionalController extends Calendarista_BaseController{
	private $repo;
	private $optional;
	public function __construct($optional, $newOptionalCallback, $sortOrderCallback, $createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'optional')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->optional = $optional;
		$this->repo = new Calendarista_OptionalRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_new', $_POST)){
			$this->newOptional($newOptionalCallback);
		} else if (array_key_exists('calendarista_sortorder', $_POST)){
			$this->sortOrder($sortOrderCallback);
		}
	}
	
	public function newOptional($callback){
		$this->executeCallback($callback, array($this->optional));
	}
	public function create($callback){
		$result = $this->repo->insert($this->optional);
		if($result !== false){
			$this->optional->id = $result;
			$this->optional->orderIndex = $result;
		}
		$this->executeCallback($callback, array($this->optional, $result));
	}
	public function update($callback){
		$result = $this->repo->update($this->optional);
		$this->executeCallback($callback, array($this->optional, $result));
	}
	public function sortOrder($callback){
		$sortOrder = $this->getPostValue('optionalSortOrder');
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
		$optionals = $this->getPostValue('optionals');
		if(!$optionals){
			$optionals = array($this->optional->id);
		}
		$result = false;
		foreach($optionals as $optional){
			$result = $this->repo->delete((int)$optional);
			if(!$result){
				break;
			}
		}
		$this->executeCallback($callback, array($result));
	}
}
?>