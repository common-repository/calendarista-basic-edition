<?php
class Calendarista_RedirectController extends Calendarista_BaseController{
	private $repo;
	private $project;
	public function __construct(){
		if (!(array_key_exists('controller', $_POST))){
			return;
		}
		$controller = isset($_POST['controller']) ? $_POST['controller'] : null;
		switch($controller){
			case 'project':
			Calendarista_ProjectController::redirect();
			break;
			case 'map':
			Calendarista_MapController::redirect();
			break;
		}
	}
}
?>