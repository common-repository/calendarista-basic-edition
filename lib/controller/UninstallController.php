<?php
class Calendarista_UninstallController extends Calendarista_BaseController{

	public function __construct($permissionCallback, $clearCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_uninstall')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		if(!current_user_can('delete_plugins')){
			$this->executeCallback($permissionCallback, array());
			return;
		}
		if (array_key_exists('calendarista_clear', $_POST)){
			$this->clear($clearCallback);
		}
		
		//delete has been moved to RegisterFrontEndHandlers.
		/*if (array_key_exists('calendarista_delete', $_POST)){
			$this->delete($deleteCallback);
		}*/
	}
	
	public function clear($callback){
		Calendarista_Install::clearDatabase();
		$this->executeCallback($callback, array());
	}
	
	public function delete($callback){
		Calendarista_Install::uninstall();
		$this->executeCallback($callback, array());
	}
}
?>