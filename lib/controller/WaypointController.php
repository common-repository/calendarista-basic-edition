<?php
class Calendarista_WaypointController extends Calendarista_BaseController{
	private $repo;
	private $mapRepo;
	public function __construct($createCallback, $updateCallback){
		if (!(array_key_exists('secondary_controller', $_POST) 
			&& $_POST['secondary_controller'] == 'waypoint')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_waypointRepository();
		$this->mapRepo = new Calendarista_MapRepository();
		parent::__construct($createCallback, $updateCallback);
	}
	private function createMapIfNotExist(){
		$mapId = (int)$this->getPostValue('mapId');
		if(!isset($mapId)){
			$projectId = (int)$this->getPostValue('projectId');
			$map = $this->mapRepo->readByProject($projectId);
			if(!$map){
				return $this->mapRepo->insert(new Calendarista_Map(array('projectId'=>$projectId)));
			}else{
				return $map->id;
			}
		}
		return $mapId;
	}
	public function create($callback){
		$mapId = $this->createMapIfNotExist();
		$this->repo->deleteAll($mapId);
		$result = $this->recreate($mapId);
		$this->executeCallback($callback, array($this->waypoint, $result));
	}
	public function update($callback){
		$mapId = $this->createMapIfNotExist();
		$this->repo->deleteAll($mapId);
		$result = $this->recreate($mapId);
		$this->executeCallback($callback, array($result));
	}
	private function recreate($mapId){
		$projectId = (int)$this->getPostValue('projectId');
		$waypointFieldNames = $this->getPostValue('waypointFieldNames');
		$groups = explode(';', $waypointFieldNames);
		$result = false;
		foreach($groups as $group){
			$fieldNames = array_filter(explode(',', $group));
			if(count($fieldNames) > 0){
				$waypoint = new Calendarista_Waypoint(array('mapId'=>$mapId, 'projectId'=>$projectId));
				foreach($fieldNames as $name){
					if(strrpos($name, 'waypointAddress') === 0){
						$waypoint->address = sanitize_text_field($_POST[$name]);
					}else if(strrpos($name, 'waypointLat') === 0){
						$waypoint->lat = floatval($_POST[$name]);
					}else if (strrpos($name, 'waypointLng') === 0){
						$waypoint->lng = floatval($_POST[$name]);
					}
				}
				$result = $this->repo->insert($waypoint);
				if(!$result){
					break;
				}
			}
		}
		return $result;
	}
}
?>