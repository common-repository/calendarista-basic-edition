<?php
class Calendarista_AssetsController extends Calendarista_BaseController{
	private $repo;
	private $gs;
	public function __construct($createCallback = null, $updateCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_assets')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_GeneralSettingsRepository();
		$this->gs = $this->repo->read();
		if(array_key_exists('refBootstrapStyleSheet', $_POST)){
			$this->gs->refBootstrapStyleSheet = (bool)$_POST['refBootstrapStyleSheet'];
		}else{
			$this->gs->refBootstrapStyleSheet = false;
		}
		if(array_key_exists('refParsleyJS', $_POST)){
			$this->gs->refParsleyJS = (bool)$_POST['refParsleyJS'];
		}else{
			$this->gs->refParsleyJS = false;
		}
		if(array_key_exists('debugMode', $_POST)){
			$this->gs->debugMode = (bool)$_POST['debugMode'];
		}else{
			$this->gs->debugMode = false; 
		}
		if(array_key_exists('fontFamilyUrl', $_POST)){
			$this->gs->fontFamilyUrl = (string)$_POST['fontFamilyUrl'];
		}
		if(array_key_exists('calendarTheme', $_POST)){
			$this->gs->calendarTheme = (string)$_POST['calendarTheme'];
		}
		parent::__construct($createCallback, $updateCallback);
	}
	public function create($callback){
		$result = $this->repo->insert($this->gs);
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$result = $this->repo->update($this->gs);
		$this->executeCallback($callback, array($result));
	}
}
?>