<?php
class Calendarista_PlaceAggregateCostRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $place_aggregate_cost;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->place_aggregate_cost = $wpdb->prefix . 'calendarista_place_aggregate_cost';
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->place_aggregate_cost WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			return $result[0];
		}
		return false;
	}
	public function readByLocation($departurePlaceId, $destinationPlaceId){
		$sql = "SELECT * FROM   $this->place_aggregate_cost WHERE  departurePlaceId = %d AND destinationPlaceId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $departurePlaceId, $destinationPlaceId) );
		if( $result ){
			return $result[0];
		}
		return false;
	}
	public function readAll($mapId){
		$sql = "SELECT * FROM   $this->place_aggregate_cost WHERE  mapId = %d ORDER BY id";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $mapId) );
		if (is_array( $result) ){
			$aggregates = array();
			foreach($result as $r){
				array_push($aggregates, $r);
			}
			return $aggregates;
		}
		return false;
	}
	public function insert($aggregate){
		$p = $this->parseParams($aggregate);
		$result = $this->wpdb->insert($this->place_aggregate_cost,  $p['params'], $p['values']);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($aggregate){
		$p = $this->parseParams($aggregate);
		$result = $this->wpdb->update($this->place_aggregate_cost,  $p['params'], array('id'=>$aggregate['id']), $p['values']);
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->place_aggregate_cost WHERE id = %d", $id));
	}
	public function deleteByPlace($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->place_aggregate_cost WHERE departurePlaceId = %d OR destinationPlaceId = %d", $id, $id));
	}
	public function deleteAll($mapId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->place_aggregate_cost WHERE mapId = %d", $mapId));
	}
	public function deleteByProject($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->place_aggregate_cost WHERE projectId = %d", $projectId));
	}
	public function parseParams($aggregate){
		$params = array('projectId'=>$aggregate['projectId'], 'mapId'=>$aggregate['mapId']);
		$values = array('%d', '%d');

		$params['departurePlaceId'] = $aggregate['departurePlaceId'];
		array_push($values, '%d');

		$params['destinationPlaceId'] = $aggregate['destinationPlaceId'];
		array_push($values, '%d');
		
		$params['cost'] = $aggregate['cost'];
		array_push($values, '%f');
		
		$params['exclude'] = $aggregate['exclude'];
		array_push($values, '%d');
		
		return array('params'=>$params, 'values'=>$values);
	}
	public static function getAggregateList($pageIndex, $limit, $departure, $destination, $aggregates){
		$result = array();
		$length = 0;
		$rowIndex = 0;
		$recordIndex = $pageIndex * $limit;
		$departureLength = $departure->count();
		$destinationLength = $destination->count();
		$total = ($departureLength * $destinationLength);
		for($i = 0; $i < $departureLength; $i++){
			$departurePlace = $departure->item($i);
			for($j = 0; $j < $destinationLength; $j++){
				$rowIndex++;
				if($rowIndex <= $recordIndex){
					continue;
				}
				$length++;
				if($length > $limit){
					break 2;
				}
				$destinationPlace = $destination->item($j);
				$aggregate = self::getAggregate($departurePlace->id, $destinationPlace->id, $aggregates);
				array_push($result, array(
					'id'=>$aggregate ? (int)$aggregate->id : null
					, 'departure'=>$departurePlace->name
					, 'destination'=>$destinationPlace->name
					, 'status'=>!$aggregate ? 'disabled=disabled' : ''
					, 'pair'=>sprintf('%d_%d', $departurePlace->id, $destinationPlace->id)
					, 'cost'=>$aggregate ? (float)$aggregate->cost : null
					, 'exclude'=>$aggregate ? (int)$aggregate->exclude : null
				));
			}
		}
		return array('total'=>$total, 'items'=>$result);
	}
	protected static function getAggregate( $departurePlaceId, $destinationPlaceId, $aggregates){
		if($aggregates){
			foreach($aggregates as $aggregate){
				if((int)$aggregate->departurePlaceId === $departurePlaceId && (int)$aggregate->destinationPlaceId === $destinationPlaceId){
					return $aggregate;
				}
			}
		}
		return null;
	}
}
?>