<?php
class Calendarista_Optional extends Calendarista_EntityBase{
	public $id;
	public $groupId;
	public $orderIndex;
	public $projectId;
	public $name;
	public $cost;
	public $quantity;
	public $bookedQuantity;
	public $status = null;
	public $count = 0;
	public $doubleCostIfReturn;
	public $description;
	public $thumbnailUrl;
	public $minIncrement = 0;
	public $maxIncrement = 10;
	public $incrementValue;
	public $bookedIncrementQuantity;
	public $limitMode = 0;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('quantity', $args)){
			$this->quantity = (int)$args['quantity'];
		}
		if(array_key_exists('doubleCostIfReturn', $args)){
			$this->doubleCostIfReturn = (bool)$args['doubleCostIfReturn'];
		}
		if(array_key_exists('description', $args)){
			$this->description = $this->decode((string)$args['description']);
		}
		if(array_key_exists('thumbnailUrl', $args)){
			$this->thumbnailUrl = (string)$args['thumbnailUrl'];
		}
		if(array_key_exists('minIncrement', $args)){
			$this->minIncrement = (int)$args['minIncrement'];
		}
		if(array_key_exists('maxIncrement', $args)){
			$this->maxIncrement = (int)$args['maxIncrement'];
		}
		if(array_key_exists('bookedQuantity', $args)){
			$this->bookedQuantity = (int)$args['bookedQuantity'];
		}
		if(array_key_exists('limitMode', $args) && isset($args['limitMode'])){
			//0 = full day, 1 = by timeslot
			$this->limitMode = (int)$args['limitMode'];
		}
		if(array_key_exists('status', $args) && isset($args['status'])){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('count', $args)){
			$this->count = (int)$args['count'];
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex = (int)$args['orderIndex'];
		}
		if(array_key_exists('groupId', $args)){
			$this->groupId = (int)$args['groupId'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	public function hasQuantity(){
		if(!$this->quantity){
			return true;//unlimited
		}
		if($this->bookedIncrementQuantity){
			if($this->minIncrement && $this->getQuantity() < $this->minIncrement){
				return false;
			}
			if($this->bookedIncrementQuantity < $this->quantity){
				return true;
			}
			return false;
		}
		return $this->bookedQuantity < $this->quantity;
	}
	public function getQuantity(){
		if(!$this->quantity){
			return 0;
		}
		$result = 0;
		if($this->bookedIncrementQuantity){
			if($this->bookedIncrementQuantity < $this->quantity){
				$result = $this->quantity - $this->bookedIncrementQuantity;
			}
			return $result < 0 ? 0 : $result;
		}
		if($this->bookedQuantity < $this->quantity){
			$result = $this->quantity - $this->bookedQuantity;
		}
		return $result < 0 ? 0 : $result;
	}
	public function getMaxIncrement(){
		$result = $this->maxIncrement;
		if($this->quantity > 0 && ($this->getQuantity() < $result)){
			$result = $this->getQuantity();
		}
		return $result;
	}
	public function updateResources(){
		$this->registerWPML();
	}
	public function deleteResources(){
		$this->unregisterWPML();
	}
	protected function init(){
		$this->name = Calendarista_TranslationHelper::t('optional_item_name_' . $this->id, $this->name);
	}
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('optional_item_name_' . $this->id, $this->name);
	}
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('optional_item_name_' . $this->id);
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'name'=>$this->name
			, 'cost'=>$this->cost
		);
	}
}
?>