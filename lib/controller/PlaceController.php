<?php
class Calendarista_PlaceController extends Calendarista_BaseController{
	private $repo;
	private $mapRepo;
	private $place;
	public function __construct($sortOrderCallback, $createCallback = null, $updateCallback = null, $deleteCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'place')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->place = new Calendarista_Place(array(
			'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
			'orderIndex'=>isset($_POST['orderIndex']) ? (int)$_POST['orderIndex'] : null,
			'mapId'=>isset($_POST['mapId']) ? (int)$_POST['mapId'] : null,
			'placeType'=>isset($_POST['placeType']) ? (int)$_POST['placeType'] : null,
			'lat'=>isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : null,
			'lng'=>isset($_POST['lng']) ? sanitize_text_field($_POST['lng']) : null,
			'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
			'markerIcon'=>isset($_POST['markerIcon']) ? sanitize_text_field($_POST['markerIcon']) : null,
			'markerIconWidth'=>isset($_POST['markerIconWidth']) ? (int)$_POST['markerIconWidth'] : null,
			'markerIconHeight'=>isset($_POST['markerIconWidth']) ? (int)$_POST['markerIconWidth'] : null,
			'infoWindowTitle'=>isset($_POST['infoWindowTitle']) ? sanitize_text_field($_POST['infoWindowTitle']) : null,
			'infoWindowIcon'=>isset($_POST['infoWindowIcon']) ? sanitize_text_field($_POST['infoWindowIcon']) : null,
			'infoWindowDescription'=>isset($_POST['infoWindowDescription']) ? sanitize_text_field($_POST['infoWindowDescription']) : null,
			'cost'=>isset($_POST['cost']) ? (double)$_POST['cost'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null,
		));
		$this->repo = new Calendarista_PlaceRepository();
		$this->mapRepo = new Calendarista_MapRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if(array_key_exists('calendarista_delete_places', $_POST)){
			$this->delete($deleteCallback);
		} else if (array_key_exists('calendarista_sortorder', $_POST)){
			$this->sortOrder($sortOrderCallback);
		}
	}
	private function createMapIfNotExist(){
		$projectId = $this->place->projectId;
		if(!isset($this->place->mapId)){
			$map = $this->mapRepo->readByProject($projectId);
			if(!$map){
				$this->place->mapId = $this->mapRepo->insert(new Calendarista_Map(array('projectId'=>$projectId)));
			}else{
				$this->place->mapId = $map->id;
			}
		}
	}
	public function create($callback){
		$this->createMapIfNotExist();
		$result = $this->repo->insert($this->place);
		if($result !== false){
			$this->place->id = $result;
		}
		$this->executeCallback($callback, array($this->place, $result));
	}
	public function update($callback){
		$result = $this->repo->update($this->place);
		$this->executeCallback($callback, array($this->place, $result));
	}
	public function sortOrder($callback){
		$sortOrder = isset($_POST['sortOrder']) ? $_POST['sortOrder'] : '';
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
	public function delete($callback){
		$places = $this->getPostValue('places');
		if(!$places){
			$places = array($this->place->id);
		}
		$result = false;
		$aggregateRepo = new Calendarista_PlaceAggregateCostRepository();
		foreach($places as $placeId){
			$aggregateRepo->deleteByPlace((int)$placeId);
			$result = $this->repo->delete((int)$placeId);
			if(!$result){
				break;
			}
		}
		$this->executeCallback($callback, array($result));
	}
}
?>