<?php
class Calendarista_StringResourcesController extends Calendarista_BaseController{
	private $repo;
	private $stringResources;
	public function __construct($createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_stringresources')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$args = array();
		foreach($_POST as $key=>$value){
			$args[$key] = sanitize_text_field($value);
		}
		$this->stringResources = new Calendarista_StringResources($args);
		$this->repo = new Calendarista_StringResourcesRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$this->stringResources->projectId = (int)$this->getPostValue('projectId');
		$result = $this->repo->insert($this->stringResources);
		$this->executeCallback($callback, array($result));
	}
	
	public function update($callback){
		$this->stringResources->id = (int)$this->getPostValue('id');
		$this->stringResources->projectId = (int)$this->getPostValue('projectId');
		$result = $this->repo->update($this->stringResources);
		$this->executeCallback($callback, array($result));
	}
	
	public function delete($callback){
		$id = (int)$this->getPostValue('id');
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
}
?>