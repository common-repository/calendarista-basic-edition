<?php
class Calendarista_StaffRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $staff_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->staff_table_name = $wpdb->prefix . 'calendarista_staff';
	}
	public function readAll($args){
		$projectId = isset($args['projectId']) ? (int)$args['projectId'] : null;
		$userId = isset($args['userId']) && $args['userId'] ? (int)$args['userId'] : null;
		$availabilityId = isset($args['availabilityId']) ? (int)$args['availabilityId'] : null;
		$name = isset($args['name']) && $args['name'] ? trim($args['name']) : null;
		$email = isset($args['email']) && $args['email'] ? trim($args['email']) : null;
		$pageIndex = isset($args['pageIndex']) ? (string)$args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? (string)$args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? (string)$args['orderBy'] : 'id';
		$order = isset($args['order']) ? (string)$args['order'] : 'asc';
		
		$where = array();
		$params = array();
		$query = "SELECT * FROM   $this->staff_table_name";
		if($projectId){
			array_push($where, 'projectId = %d');
			array_push($params, $projectId);
		}
		if(!is_null($userId)){
			array_push($where, 'userId = %d');
			array_push($params, $userId);
		}
		if($availabilityId){
			array_push($where, 'availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if($email){
			array_push($where, 'email = %s');
			array_push($params, $email);
		}
		if($name){
			array_push($where, 'name = %s');
			array_push($params, $name);
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
			$query = "SELECT count(id) as count FROM $this->staff_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$subResult = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$staff = array();
			foreach($result as $r){
				array_push($staff, array(
					'id'=>(int)$r->id
					, 'userId'=>(int)$r->userId
					, 'projectId'=>(int)$r->projectId
					, 'availabilityId'=>(int)$r->availabilityId
					, 'projectName'=>$r->projectName
					, 'availabilityName'=>$r->availabilityName
					, 'name'=>$r->name
					, 'email'=>$r->email
				));
			}
			return array('items'=>$staff, 'total'=>(int)$subResult[0]->count);
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT * FROM   $this->staff_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			return array(
				'id'=>(int)$result[0]->id
				, 'userId'=>(int)$result[0]->userId
				, 'projectId'=>(int)$result[0]->projectId
				, 'availabilityId'=>(int)$result[0]->availabilityId
				, 'projectName'=>$result[0]->projectName
				, 'availabilityName'=>$result[0]->availabilityName
				, 'name'=>$result[0]->name
				, 'email'=>$result[0]->email
			);
		}
		return false;
	}

	public function insert($args){
		 $result = $this->wpdb->insert($this->staff_table_name,  array(
			'userId'=>$args['userId']
			, 'projectId'=>$args['projectId']
			, 'availabilityId'=>$args['availabilityId']
			, 'projectName'=>$args['projectName']
			, 'availabilityName'=>$args['availabilityName']
			, 'name'=>$args['name']
			, 'email'=>$args['email']
		  ), array('%d', '%d', '%d', '%s', '%s', '%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($args){
		$result = $this->wpdb->update($this->staff_table_name,  array(
			'userId'=>$args['userId']
			, 'name'=>$args['name']
			, 'email'=>$args['email']
		), array('id'=>$args['id']), array('%d', '%s', '%s'));
		return $result;
	}
	
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->staff_table_name WHERE id = %d", $id));
	}
	
	public function deleteAll($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->staff_table_name WHERE projectId = %d", $projectId));
	}
}
?>