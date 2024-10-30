<?php
class Calendarista_AvailabilityController extends Calendarista_BaseController{
	private $repo;
	private $availability;
	public function __construct($availability, $newAvailabilityCallback, $sortOrderCallback, $editCallback, $createCallback, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'availability')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->availability = $availability;
		$this->repo = new Calendarista_AvailabilityRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_new_availability', $_POST)){
			$this->newAvailability($newAvailabilityCallback);
		}else if (array_key_exists('calendarista_edit', $_POST)){
			$this->edit($editCallback);
		} else if (array_key_exists('calendarista_sortorder', $_POST)){
			$this->sortOrder($sortOrderCallback);
		}
	}
	
	public function newAvailability($callback){
		$this->executeCallback($callback, array());
	}
	public function sortOrder($callback){
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
	public function edit($callback){
		$id = (int)$this->getPostValue('calendarista_edit');
		$this->executeCallback($callback, array($id));
	}
	public function create($callback){
		$result = $this->repo->insert($this->availability);
		/*if($result !== false){
			$projectRepo = new Calendarista_ProjectRepository();
			$project = $projectRepo->read($this->availability->projectId);
			if(in_array($project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
				//create default set of timeslots
				Calendarista_AutogenTimeslotsController::createDefaultSlots((int)$result, $this->availability->projectId);
			}
		}*/
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$this->repo->update($this->availability);
		$this->executeCallback($callback, array($this->availability->id));
	}
	public function delete($callback){
		$availabilitiesList = $this->getPostValue('availabilities');
		if($availabilitiesList){
			foreach($availabilitiesList as $id){
				$this->repo->delete((int)$id);
			}
		}else{
			$this->repo->delete($this->availability->id);
		}
		$this->executeCallback($callback, array(true));
	}
}
?>