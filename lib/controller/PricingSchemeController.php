<?php
class Calendarista_PricingSchemeController extends Calendarista_BaseController{
	private $repo;
	private $availability;
	public function __construct($createCallback, $updateCallback, $deleteCallback, $autogenCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'pricing_scheme')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_PricingSchemeRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_autogen_create', $_POST)){
			$this->autogen($autogenCallback);
		}
	}
	public function autogen($callback){
		$projectId = (int)$this->getPostValue('projectId');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$seasonId = $this->getPostValue('seasonId') ? (int)$this->getPostValue('seasonId') : null;
		$from = (int)$this->getPostValue('autogen_days_from');
		$to = (int)$this->getPostValue('autogen_days_to');
		$cost = (float)$this->getPostValue('autogen_cost');
		$duplicates = array();
		for($i = $from; $i <= $to; $i++){
			if(!$seasonId){
				$pricingScheme = $this->repo->readByAvailabilityIdAndDays($availabilityId, $i);
			} else{
				$pricingScheme = $this->repo->readBySeasonIdAndDays($seasonId, $i);
			}
			if(!$pricingScheme){
				$newId = $this->repo->insert(array('projectId'=>$projectId, 'availabilityId'=>$availabilityId, 'days'=>$i, 'cost'=>$cost, 'seasonId'=>$seasonId));
			}else{
				array_push($duplicates, $i);
			}
		}
		$this->executeCallback($callback, array($duplicates));
	}
	public function create($callback){
		$newId = null;
		$projectId = (int)$this->getPostValue('projectId');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$seasonId = $this->getPostValue('seasonId') ? (int)$this->getPostValue('seasonId') : null;
		$days = (int)$this->getPostValue('days_0');
		$cost = (float)$this->getPostValue('cost_0');
		if(!$seasonId){
			$pricingScheme = $this->repo->readByAvailabilityIdAndDays($availabilityId, $days);
		} else{
			$pricingScheme = $this->repo->readBySeasonIdAndDays($seasonId, $days);
		}
		$duplicate = true;
		if(!$pricingScheme){
			$duplicate = false;
			$newId = $this->repo->insert(array('projectId'=>$projectId, 'availabilityId'=>$availabilityId, 'days'=>$days, 'cost'=>$cost, 'seasonId'=>$seasonId));
		}
		$this->executeCallback($callback, array($newId, $duplicate));
	}
	public function update($callback){
		$id = (int)$this->getPostValue('calendarista_update');
		$projectId = (int)$this->getPostValue('projectId');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$seasonId = $this->getPostValue('seasonId') ? (int)$this->getPostValue('seasonId') : null;
		$days = (int)$this->getPostValue('days_' . $id);
		$cost = (float)$this->getPostValue('cost_' . $id);
		if(!$seasonId){
			$pricingScheme = $this->repo->readByAvailabilityIdAndDays($availabilityId, $days, $id);
		} else{
			$pricingScheme = $this->repo->readBySeasonIdAndDays($seasonId, $days, $id);
		}
		$duplicate = true;
		if(!$pricingScheme){
			$duplicate = false;
			$this->repo->update(array('id'=>$id, 'projectId'=>$projectId, 'availabilityId'=>$availabilityId, 'days'=>$days, 'cost'=>$cost));
		}
		$this->executeCallback($callback, array($id, $duplicate));
	}
	public function delete($callback){
		$pricingSchemes = $this->getPostValue('pricingSchemes');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		if($pricingSchemes){
			foreach($pricingSchemes as $id){
				$this->repo->delete((int)$id);
			}
		}else{
			$id = (int)$this->getPostValue('calendarista_delete');
			$this->repo->delete($id);
		}
		$this->executeCallback($callback, array());
	}
}
?>