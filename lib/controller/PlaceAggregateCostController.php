<?php
class Calendarista_PlaceAggregateCostController extends Calendarista_BaseController{
	private $repo;
	public function __construct($createCallback, $updateCallback, $deleteCallback, $deleteManyCallback, $updateManyCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'place_aggregate_cost')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_PlaceAggregateCostRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if (array_key_exists('calendarista_deleteMany', $_POST)){
			$this->deleteMany($deleteManyCallback);
		}else if (array_key_exists('calendarista_updateMany', $_POST)){
			$this->updateMany($updateManyCallback);
		}
	}
	protected function getAggregate($values){
		$args = explode('_', $values);
		$result = null;
		if(count($args) > 1){
			$cost = $this->getFloatValue(sprintf('cost_%d_%d', $args[0], $args[1]));
			$exclude = is_null($this->getIntValue(sprintf('exclude_%d_%d', $args[0], $args[1]))) ? 0 : 1;
			$result = array(
				'projectId'=>$this->getIntValue('projectId')
				, 'mapId'=>$this->getIntValue('mapId')
				, 'departurePlaceId'=>(int)$args[0]
				, 'destinationPlaceId'=>(int)$args[1]
				, 'cost'=>$cost
				, 'exclude'=>$exclude
				, 'id'=>(int)$args[2]
			);
		}
		return $result;
	}
	public function create($callback){
		$values = $this->getPostValue('calendarista_create');
		$aggregate = $this->getAggregate($values);
		$result = null;
		if($aggregate){
			$result = $this->repo->insert($aggregate);
		}
		$this->executeCallback($callback, array());
	}
	public function update($callback){
		$values = $this->getPostValue('calendarista_update');
		$aggregate = $this->getAggregate($values);
		$result = null;
		if($aggregate){
			$result = $this->repo->update($aggregate);
		}
		$this->executeCallback($callback, array());
	}
	public function updateMany($callback){
		$values = isset($_POST['updateMany']) ? $_POST['updateMany'] : array();
		$result = false;
		foreach($values as $val){
			$aggregate = $this->getAggregate($val);
			if($aggregate['id'] === 0){
				$result = $this->repo->insert($aggregate);
			}else{
				$result = $this->repo->update($aggregate);
			}
		}
		$this->executeCallback($callback, array());
	}
	public function delete($callback){
		$id = $this->getIntValue('calendarista_delete');
		$result = null;
		if($id !== null){
			$result = $this->repo->delete($id);
		}
		$this->executeCallback($callback, array());
	}
	public function deleteMany($callback){
		$values = isset($_POST['deleteMany']) ? array_map('intval', $_POST['deleteMany']) : array();
		$result = false;
		foreach($values as $id){
			$result = $this->repo->delete($id);
		}
		$this->executeCallback($callback, array($result));
	}
}
?>