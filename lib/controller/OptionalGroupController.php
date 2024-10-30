<?php
class Calendarista_OptionalGroupController extends Calendarista_BaseController{
	private $repo;
	private $optionalGroup;
	public function __construct($optionalGroup, $newOptionalGroupCallback, $sortOrderCallback, $createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'optional_group')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->optionalGroup = $optionalGroup;
		$this->repo = new Calendarista_OptionalGroupRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_new', $_POST)){
			$this->newOptionalGroup($newOptionalGroupCallback);
		}else if (array_key_exists('calendarista_sortorder', $_POST)){
			$this->sortOrder($sortOrderCallback);
		}
	}
	
	public function newOptionalGroup($callback){
		$this->executeCallback($callback, array());
	}
	public function sortOrder($callback){
		$sortOrder = $this->getPostValue('groupSortOrder');
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
	
	public function create($callback){
		$result = $this->repo->insert($this->optionalGroup);
		if($result){
			$this->optionalGroup->id = $result;
		}
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$result = $this->repo->update($this->optionalGroup);
		$this->executeCallback($callback, array($this->optionalGroup, $result));
	}
	public function delete($callback){
		$repo = new Calendarista_OptionalRepository();
		$repo->deleteByGroup($this->optionalGroup->id);
		$result = $this->repo->delete($this->optionalGroup->id);
		$this->executeCallback($callback, array($result));
	}
}
?>