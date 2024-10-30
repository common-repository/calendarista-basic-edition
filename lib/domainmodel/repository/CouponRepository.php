<?php
class Calendarista_CouponRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $coupons_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->coupons_table_name = $wpdb->prefix . 'calendarista_coupons';
	}
	
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc', $projectId = -1, $code = null, $couponType = null, $discount = null){
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
		$query = "SELECT * FROM  $this->coupons_table_name";
		if($projectId && $projectId !== -1){
			array_push($where, 'projectId = %d');
			array_push($params, $projectId);
		}
		if($code){
			array_push($where, 'code = %s');
			array_push($params, $code);
		}
		if($couponType !== null){
			array_push($where, 'couponType = %d');
			array_push($params, $couponType);
		}
		if($discount){
			array_push($where, 'discount = %f');
			array_push($params, $discount);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$subQuery = $query;
		$subParams = array_merge($params, array());
		$query .= " ORDER BY $orderBy $order";
		
		if($pageIndex > -1){
			$query .= ' LIMIT %d, %d;';
			array_push($params, $pageIndex, $limit);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if ( is_array($result) ){
			$records = $this->wpdb->get_results(count($subParams) > 0 ? $this->wpdb->prepare($subQuery, $subParams) : $subQuery);
			$coupons = new Calendarista_Coupons();
			$coupons->total = count($records);
			foreach($result as $r){
				$coupons->add(new Calendarista_Coupon((array)$r));
			}
			return $coupons;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT * FROM   $this->coupons_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $id));
		if( $result ){
			$r = $result[0];
			return new Calendarista_Coupon((array)$r);
		}
		return false;
	}
	
	public function find($coupon, $id = -1){
		$code = $coupon instanceOf Calendarista_Coupon ? $coupon->code : $coupon;
		$params = array($code);
		$sql = "SELECT * FROM  $this->coupons_table_name WHERE  code = %s";
		if($id && $id !== -1){
			$sql .= ' AND id <> %d';
			array_push($params, $id);
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $params) );
		if( $result ){
			$r = $result[0];
			return new Calendarista_Coupon((array)$r);
		}
		return false;
	}
	
	public function insert($coupon){
		$p = $this->parseParams($coupon);
		$result = $this->wpdb->insert($this->coupons_table_name, $p['params'], $p['values']);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	
	public function insertMany($coupon, $count){
		if($count < 0){
			return false;
		}
		$values = array();
		$sql = "INSERT INTO $this->coupons_table_name (code, projectId, projectName, discount, orderMinimum, expirationDate, discountMode, couponType) VALUES ";
		for($i = 0; $i < $count; $i++){
			$value = "(%s, %d, %s, %f, %f, %s, %d, %d)";
			array_push($values, $this->wpdb->prepare($value
				, $coupon->code
				, $coupon->projectId
				, $coupon->projectName
				, $coupon->discount
				, $coupon->orderMinimum
				, $coupon->expirationDate->format(CALENDARISTA_DATEFORMAT)
				, $coupon->discountMode
				, $coupon->couponType
			));
		}
		
		$sql .= (implode(', ', $values) . ';');
		return $this->wpdb->query($sql);
	}
	
	public function update($coupon){
		$p = $this->parseParams($coupon);
		$result = $this->wpdb->update($this->coupons_table_name, $p['params'], array('id'=>$coupon->id),  $p['values']);
		return $result;
	}
	
	private function parseParams($coupon){
		$params = array();
		$values = array();
		if(isset($coupon->discount)){
			$params['discount'] = $coupon->discount;
			array_push($values, '%f');
		}
		if(isset($coupon->orderMinimum)){
			$params['orderMinimum'] = $coupon->orderMinimum;
			array_push($values, '%f');
		}
		if(isset($coupon->code)){
			$params['code'] = trim($coupon->code);
			array_push($values, '%s');
		}
		if(isset($coupon->expirationDate)){
			$params['expirationDate'] = $coupon->expirationDate->format(CALENDARISTA_DATEFORMAT);
			array_push($values, '%s');
		}
		if(isset($coupon->projectId)){
			$params['projectId'] = $coupon->projectId;
			array_push($values, '%d');
		}
		if(isset($coupon->projectName)){
			$params['projectName'] = $coupon->projectName;
			array_push($values, '%s');
		}
		if(isset($coupon->emailedTo)){
			$params['emailedTo'] = $coupon->emailedTo;
			array_push($values, '%s');
		}
		if(isset($coupon->couponType)){
			$params['couponType'] = $coupon->couponType;
			array_push($values, '%d');
		}
		if(isset($coupon->discountMode)){
			$params['discountMode'] = $coupon->discountMode;
			array_push($values, '%d');
		}
		return array('params'=>$params, 'values'=>$values);
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->coupons_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	public function deleteAll(){
		$sql = "DELETE FROM $this->coupons_table_name";
		$rows_affected = $this->wpdb->query($sql);
		return $rows_affected;
	}
}
?>