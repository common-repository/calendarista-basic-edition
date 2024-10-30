<?php
class Calendarista_AvailabilityDayController extends Calendarista_BaseController{
	private $repo;
	public function __construct($createCallback = null, $deleteCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'availability_day')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_AvailabilityDayRepository();
		if($createCallback && $deleteCallback){
			parent::__construct($createCallback, null, $deleteCallback);
		}
	}
	public function create($callback = null){
		$individualDay = $this->getPostValue('individualDay');
		$projectId = (int)$this->getPostValue('projectId');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$result = false;
		$exists = $this->repo->readByDateAndAvailability($individualDay, $availabilityId);
		if(!$exists){
			$result = $this->repo->insert(array('projectId'=>$projectId, 'availabilityId'=>$availabilityId, 'individualDay'=>$individualDay));
		}
		if($callback){
			$this->executeCallback($callback, array($result));
		}
		return $result;
	}
	public function delete($callback = null){
		$days = explode(',', $this->getPostValue('id'));
		$result = false;
		foreach($days as $id){
			$result = $this->repo->delete((int)$id);
			if(!$result){
				break;
			}
		}
		if($callback){
			$this->executeCallback($callback, array($result));
		}
		return $result;
	}
}
?>