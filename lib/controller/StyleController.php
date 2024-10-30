<?php
class Calendarista_StyleController extends Calendarista_BaseController{
	private $repo;
	private $mustacheEngine;
	public function __construct($createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_style')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_StyleRepository();
		if(!class_exists('Mustache_Engine')){
			require_once CALENDARISTA_MUSTACHE . 'Autoloader.php';
			Mustache_Autoloader::register();
		}
		$this->mustacheEngine = new Mustache_Engine();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$errorMessage = false;
		$result = false;
		$style = new Calendarista_Style(array());
		$style = $this->setValues($style);
		try{
			$this->mustacheEngine->render($style->bookingSummaryTemplate, array());
		} catch (Exception $e) {
			$errorMessage = $e->getMessage();
		} 
		if(!$errorMessage){
			$result = $this->repo->insert($style);
		}
		$this->executeCallback($callback, array($result, $errorMessage));
	}
	
	public function update($callback){
		$errorMessage = false;
		$result = false;
		$id = (int)$this->getPostValue('id');
		$style = $this->repo->read($id);
		if($style){
			$style = $this->setValues($style);
			try{
				$this->mustacheEngine->render($style->bookingSummaryTemplate, array());
			} catch (Exception $e) {
				$errorMessage = $e->getMessage();
			} 
			if(!$errorMessage){
				$result = $this->repo->update($style);
			}
		}
		$this->executeCallback($callback, array($result, $errorMessage));
	}
	
	public function delete($callback){
		$id = (int)$this->getPostValue('id');
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
	public function setValues($style){
		if($this->getPostValue('projectId') != null){
			$style->projectId = $this->getPostValue('projectId');
		}
		if($this->getPostValue('theme') != null){
			$style->theme = $this->getPostValue('theme');
		}
		if($this->getPostValue('fontFamily') != null){
			$style->fontFamily = $this->getPostValue('fontFamily');
		}
		$style->partiallyThemed = $this->getPostValue('partiallyThemed') == '0' ? false : true;
		if($this->getPostValue('thumbnailWidth') != null){
			$style->thumbnailWidth = $this->getPostValue('thumbnailWidth');
		}
		if($this->getPostValue('thumbnailHeight') != null){
			$style->thumbnailHeight = $this->getPostValue('thumbnailHeight');
		}
		if($this->getPostValue('bookingSummaryTemplate') != null){
			$style->bookingSummaryTemplate = isset($_POST['bookingSummaryTemplate']) ? $_POST['bookingSummaryTemplate'] : '';
		}
		return $style;
	}
}
?>