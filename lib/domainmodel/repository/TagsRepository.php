<?php
class Calendarista_TagsRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $tags_table_name;
	private $tags_availability_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->tags_table_name = $wpdb->prefix . 'calendarista_tags';
		$this->tags_availability_table_name = $wpdb->prefix . 'calendarista_tags_availability';
	}
	public function read($id){
		$sql = "SELECT * FROM   $this->tags_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			return new Calendarista_Tag((array)$result[0]);
		}
		return false;
	}
	public function readTagAvailability($id, $availabilityId){
		$sql = "SELECT * FROM   $this->tags_availability_table_name WHERE  tagId = %d AND availabilityId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id, $availabilityId) );
		if($result){
			return $result[0];
		}
		return false;
	}
	public function readByTagId($tags){
		$where = array();
		$params = array();
		$query = "SELECT * FROM   $this->tags_table_name WHERE id IN (" . implode(',', array_map('intval', $tags)) . ")";
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results($query);
		if (is_array($result)){
			return $result;
		}
		return false;
	}
	public function readAll($args){
		$availabilityId = isset($args['availabilityId']) ? (int)$args['availabilityId'] : null;
		$pageIndex = isset($args['pageIndex']) ? (string)$args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? (string)$args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? (string)$args['orderBy'] : 'id';
		$order = isset($args['order']) ? (string)$args['order'] : 'asc';
		$where = array();
		$params = array();
		$query = "SELECT * FROM   $this->tags_table_name";
		if($availabilityId){
			$query = "SELECT t.*, (SELECT ta.tagId FROM $this->tags_availability_table_name as ta WHERE ta.availabilityId = %d AND ta.tagId = t.id) as tagId FROM $this->tags_table_name as t";
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$query = "SELECT count(id) as count FROM $this->tags_table_name";
			$subResult = $this->wpdb->get_results($query);
			$tags = array();
			foreach($result as $r){
				array_push($tags, new Calendarista_Tag((array)$r));
			}
			return array('items'=>$tags, 'total'=>(int)$subResult[0]->count);
		}
		return false;
	}
	public function readAllByAvailability($args){
		$availabilityId = isset($args['availabilityId']) ? (int)$args['availabilityId'] : null;
		$pageIndex = isset($args['pageIndex']) ? (string)$args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? (string)$args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? (string)$args['orderBy'] : 'id';
		$order = isset($args['order']) ? (string)$args['order'] : 'asc';
		$where = array();
		$params = array();
		$query = "SELECT t.id, t.name FROM   $this->tags_availability_table_name as ta INNER JOIN $this->tags_table_name as t ON ta.tagId = t.id";
		if($availabilityId){
			array_push($where, 'ta.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$query = "SELECT count(t.id) as count FROM   $this->tags_availability_table_name as ta INNER JOIN $this->tags_table_name as t ON ta.tagId = t.id";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$subResult = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$tags = array();
			foreach($result as $r){
				array_push($tags, new Calendarista_Tag((array)$r));
			}
			return array('items'=>$tags, 'total'=>$subResult ? (int)$subResult[0]->count : 0);
		}
		return false;
	}
	public function insert($tag){
		$p = $this->parseParams($tag);
		$result = $this->wpdb->insert($this->tags_table_name,  $p['params'], $p['values']);
		if($result !== false){
			$tag->id = $this->wpdb->insert_id;
			$this->updateSortOrder($tag->id, $tag->id);
			$tag->updateResources();
			return $tag->id;
		}
		return $result;
	}
	public function insertTagAvailability($tagId, $availabilityId){
		$result = $this->wpdb->insert($this->tags_availability_table_name,  array('tagId'=>$tagId, 'availabilityId'=>$availabilityId), array('%d', '%d'));
		if($result !== false){
			$newId = $this->wpdb->insert_id;
			$this->updateSortOrder($newId, $newId);
			return $newId;
		}
		return $result;
	}
	public function update($tag){
		$p = $this->parseParams($tag);
		$result = $this->wpdb->update($this->tags_table_name,  $p['params'], array('id'=>$tag->id), $p['values']);
		$tag->updateResources();
		return $result;
	}
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->tags_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	public function delete($id){
		$this->deleteResources($id);
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->tags_availability_table_name WHERE tagId = %d", $id));
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->tags_table_name WHERE id = %d", $id));
	}
	public function deleteByAvailabilityId($availabilityId){
		$tags = $this->readAllByAvailability(array('availabilityId'=>$availabilityId));
		$idList = array();
		foreach($tags as $t){
			array_push($idList, $t->id);
			$this->deleteResources($t->id);
		}
		$result = $this->wpdb->query("DELETE FROM $this->tags_table_name WHERE id IN (" . join(',', array_map('intval', $idList)) . ")");
		$this->wpdb->query("DELETE FROM $this->tags_availability_table_name WHERE tagId IN (" . join(',', array_map('intval', $idList)) . ")");
		return $result;
	}
	public function deleteFromTagAvailability($id, $availabilityId){
		$result = $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->tags_availability_table_name WHERE tagId = %d AND availabilityId = %d", $id, $availabilityId));
		return $result;
	}
	public function deleteFromTagByAvailabilityId($availabilityId){
		$result = $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->tags_availability_table_name WHERE availabilityId = %d", $availabilityId));
		return $result;
	}
	public function deleteAll(){
		$tags = $this->readAll();
		foreach($tags as $t){
			$this->deleteResources($t->id);
		}
		return $this->wpdb->query("DELETE FROM $this->tags_table_name");
	}
	public function parseParams($tag){
		$params = array();
		$values = array();

		if(isset($tag->orderIndex)){
			$params['orderIndex'] = $tag->orderIndex;
			array_push($values, '%d');
		}

		$params['name'] = $tag->name;
		array_push($values, '%s');

		return array('params'=>$params, 'values'=>$values);
	}
	public function deleteResources($id){
		$tag = $this->read($id);
		if($tag){
			$tag->deleteResources();
		}
	}
}
?>