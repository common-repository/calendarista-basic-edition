<?php
class Calendarista_AuthRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $auth_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->auth_table_name = $wpdb->prefix . 'calendarista_auth';
	}
	public function read($id){
		$sql = "SELECT * FROM $this->auth_table_name WHERE id = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'password'=>$result[0]->password
					, 'userEmail'=>$result[0]->userEmail
				);
		}
		return false;
	}
	public function readByEmail($email){
		$sql = "SELECT * FROM $this->auth_table_name WHERE userEmail = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $email) );
		if($result){
			return array(
					'id'=>(int)$result[0]->id
					, 'password'=>$result[0]->password
					, 'userEmail'=>$result[0]->userEmail
				);
		}
		return false;
	}
	public function isValid($password, $email){
		$sql = "SELECT count(*) as count FROM $this->auth_table_name where password = %s AND userEmail = %s";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $password, $email));
		if($result){
			return $result[0]->count > 0;
		}
		return false;
	}
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc'){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'id';
		}
		if($order === null){
			$order = 'asc';
		}
		$where = array();
		$params = array();
		$query = "SELECT * FROM  $this->auth_table_name";
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= " ORDER BY $orderBy $order";
		if($pageIndex > -1){
			$query .= ' LIMIT %d, %d;';
			array_push($params, $pageIndex, $limit);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$query = "SELECT COUNT(*) as total FROM $this->auth_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$records = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array(
					'id'=>(int)$result[0]->id
					, 'password'=>$result[0]->password
					, 'userEmail'=>$result[0]->userEmail
				));
			}
			return array('resultset'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	public function insert($args){
		$params = $this->parseParams($args);
		$result = $this->wpdb->insert($this->auth_table_name,  $params[0], $params[1]);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	public function update($args){
		$params = $this->parseParams($args);
		$result = $this->wpdb->update($this->auth_table_name, $params[0], array('id'=>$args['id']),  $params[1]);
		return $result;
	}
	public function parseParams($args){
		$params = array();
		$values = array();
		
		$params['password'] = $args['password'];
		array_push($values, '%s');

		$params['userEmail'] = $args['userEmail'];
		array_push($values, '%s');
	
		return array($params, $values);
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->auth_table_name WHERE id = %d", $id));
	}
	public function deleteByUserEmail($userEmail){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->auth_table_name where userEmail = %s", $userEmail));
	}
	public function deleteAll(){
		return $this->wpdb->query(("DELETE FROM $this->auth_table_name"));
	}
	public static function genPassword($email){
		return uniqid($email . '_');
	}
}
?>