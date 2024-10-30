<?php
class Calendarista_Roles extends Calendarista_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Calendarista_Role) ){
			throw new Exception('Invalid value. Expected an instance of the Calendarista_Role class.');
		}
        parent::add($value);
    }
	public function getProjectIdList(){
		$list = array();
		$items = $this->get_items();
		foreach($items as $key=>$value) {
			array_push($list, $value->projectId);
		}
		return $list;
	}
}
?>