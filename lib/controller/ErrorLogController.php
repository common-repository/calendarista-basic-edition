<?php
class Calendarista_ErrorLogController extends Calendarista_BaseController{
	private $repo;
	public function __construct($deleteCallback, $deleteAllCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_errorlog')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_ErrorLogRepository();
		if(array_key_exists('calendarista_delete', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('calendarista_deleteall', $_POST)){
			$this->deleteAll($deleteAllCallback);
		}
	}
	
	
	public function delete($callback){
		$id = isset($_POST['calendarista_delete']) ? intval($_POST['calendarista_delete']) : null;
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
	
	public function deleteAll($callback){
		$result = $this->repo->deleteAll();
		$this->executeCallback($callback, array($result));
	}
}
?>