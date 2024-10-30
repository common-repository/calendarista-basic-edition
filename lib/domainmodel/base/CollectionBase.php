<?php
class Calendarista_CollectionBase implements IteratorAggregate
{
    private $_items = array();
    protected $_count = 0;
	
	public function get_items(){
		return $this->_items;
	}
    public function getIterator() : Traversable {
        return new ArrayIterator($this->_items);
    }
	
	public function item($key){
		return isset($this->_items[$key]) ? $this->_items[$key] : null;
	}
	
    public function add($value) {
		array_push($this->_items, $value);
        ++$this->_count;
    }
	
	public function count(){
		return $this->_count;
	}
	
	public function remove_item($value){
		$key = $this->get_key($value);
		$this->remove($key);
	}
	
	public function remove($key){
		if(isset($this->_items[$key])) {
			unset($this->_items[$key]);
			--$this->_count;
		} else {
			throw new Exception("Invalid key $key specified.");
		}
	}
	
	public function clear(){
		$this->_items = array();
		$this->_count = 0;
	}
	
	function get_key($item){
		foreach($this->_items as $key => $value) {
			if( $value === $item){
				return $key;
			}
		}
		return NULL;
	}
	
	public function toArray(){
		$a = array();
		$items = $this->get_items();
		foreach($items as $item){
			if (method_exists($item, 'toArray')){
				array_push($a, $item->toArray());
			}else{
				array_push($a, $item);
			}
		}
		return $a;
	}
}
?>