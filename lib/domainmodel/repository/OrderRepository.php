<?php
class Calendarista_OrderRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $availability_booked_table_name;
	private $auth_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'calendarista_order';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
		$this->auth_table_name = $wpdb->prefix . 'calendarista_auth';
	}
	
	public function readAll($args){
		$pageIndex = isset($args['pageIndex']) ? $args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? $args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? $args['orderBy'] : null;
		$order = isset($args['order']) ? $args['order'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) ? $args['toDate'] : null;
		$customerName = isset($args['customerName']) ? $args['customerName'] : null;
		$email = isset($args['email']) ? $args['email'] : null;
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$orderId = isset($args['orderId']) ? $args['orderId'] : null;
		$invoiceId = isset($args['invoiceId']) ? $args['invoiceId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$showExpired = isset($args['showExpired']) ? $args['showExpired'] : true;
		$sales = isset($args['sales']) ? $args['sales'] : false;
		$availabilities = isset($args['availabilities']) && is_array($args['availabilities']) ? implode(',', array_map('intval', $args['availabilities'])) : false;
		$fromDateString = null;
		$toDateString = null;
		if($fromDate !== null){
			$fromDateString = $fromDate->format(CALENDARISTA_DATEFORMAT);
		}
		if($toDate !== null){
			$toDateString = $toDate->format(CALENDARISTA_DATEFORMAT);
		}
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'asc';
		}
		$query = "SELECT * FROM $this->order_table_name";
		$where = array();
		$params = array();
		if($fromDate !== null && $toDate !== null){
			array_push($where, 'CONVERT(%s, DATE) <= DATE(orderDate) AND CONVERT(%s, DATE) >= DATE(orderDate)');
			array_push($params, $fromDateString, $toDateString);
		} else if($fromDate !== null){
			array_push($where, 'DATE(orderDate) = CONVERT(%s, DATE)');
			array_push($params, $fromDateString);
		}
		if($customerName){
			array_push($where, 'fullName LIKE %s');
			array_push($params, '%' . $customerName . '%');
		} else if($email){
			array_push($where, 'email = %s');
			array_push($params, $email);
		}else if ($invoiceId){
			array_push($where, 'invoiceId = %s');
			array_push($params, $invoiceId);
		}
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'projectId = %d');
			array_push($params, $projectId);
		}
		if($orderId !== null && $orderId !== -1){
			array_push($where, 'id = %d');
			array_push($params, $orderId);
		}
		if($availabilityId !== null && $availabilityId !== -1){
			array_push($where, 'availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if($availabilities){
			array_push($where, 'availabilityId IN (' . $availabilities . ')');
		}
		if($sales){
			array_push($where, '(totalAmount > 0 OR discount > 0)');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if( is_array($result) ){
			$orders = new Calendarista_Orders();
			$query = "SELECT count(id) as count FROM $this->order_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$total = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$orders->total = $total[0]->count;
			$query = "SELECT sum(totalAmount) as amount FROM $this->order_table_name";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$sum = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$orders->totalAmount = $sum[0]->amount;
			foreach($result as $r){
				$order = new Calendarista_Order((array)$r);
				$orders->add($order);
			}
			return $orders;
		}
		return false;
	}
	public function readByUserIdOrEmail($args){
		$userId = isset($args['userId']) ? $args['userId'] : null;
		$email = isset($args['email']) ? $args['email'] : null;
		$orderBy = 'orderDate';
		$order = 'asc';
		$query = "SELECT id FROM $this->order_table_name";
		$where = array();
		$params = array();
		if ($userId){
			array_push($where, 'userId = %d');
			array_push($params, $userId);
		}
		if($email){
			array_push($where, 'email = %s');
			array_push($params, $email);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' OR ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if( is_array($result) ){
			$orders = array();
			foreach($result as $r){
				array_push($orders, (int)$r->id);
			}
			return $orders;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT * FROM $this->order_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if($result){
			return new Calendarista_Order((array)$result[0]);
		}
		return false;
	}
	public function orderExists($requestId){
		$sql = "SELECT requestId FROM $this->order_table_name WHERE requestId = %s";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $requestId));
		if($result && count($result) > 0){
			return $result[0];
		}
		return false;
	}
	public function readByInvoiceId($invoiceId){
		$sql = "SELECT * FROM $this->order_table_name WHERE invoiceId = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $invoiceId));
		if($result){
			return new Calendarista_Order((array)$result[0]);
		}
		return false;
	}
	public function readBySecretKey($secretKey){
		$sql = "SELECT * FROM $this->order_table_name WHERE secretKey = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $secretKey));
		if($result){
			return new Calendarista_Order((array)$result[0]);
		}
		return false;
	}
	public function readInvoiceByStagingId($stagingId){
		$sql = "SELECT invoiceId FROM $this->order_table_name WHERE stagingId = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $stagingId));
		if($result){
			return $result[0]->invoiceId;
		}
		return false;
	}
	public function readByProject($projectId){
		$sql = "SELECT * FROM $this->order_table_name WHERE projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId));
		if ( $result !== false ){
			return $result;
		}
		return false;
	}
	public function emailExists($email){
		$sql = "SELECT count(id) as count FROM $this->order_table_name WHERE email = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $email));
		if ($result !== false){
			return (int)$result[0]->count > 0;
		}
		return false;
	}
	public function export($args){
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) && !empty($args['toDate']) ? $args['toDate'] : null;
		$email = isset($args['email']) && !empty($args['email']) ? $args['email'] : null;
		$query = "SELECT o.*, a.id as availabilityId FROM $this->order_table_name as o LEFT JOIN $this->availability_booked_table_name as a ON o.id = a.orderId";
		$where = array();
		$params = array();
		if($fromDate !== null && !$email){
			array_push($where, '(DATE(a.fromDate) >= CONVERT(%s, DATE) AND (CONVERT(%s, DATE) <= DATE(a.toDate) OR a.toDate IS NULL))');
			array_push($params, $fromDate, $fromDate);
		}
		if($toDate !== null && !$email){
			array_push($where, '(DATE(a.toDate) <= CONVERT(%s, DATE) OR a.toDate IS NULL)');
			array_push($params, $toDate);
		}
		if($email){
			array_push($where, 'a.userEmail = %s');
			array_push($params, $email);
		}
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'a.projectId = %d');
			array_push($params, $projectId);
		}
		if($availabilityId !== null && $availabilityId !== -1){
			array_push($where, 'a.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	public function insert($order, $prefix = 'CA'){
		$p = $this->parseParams($order);
		$result = $this->wpdb->insert($this->order_table_name,  $p['params'], $p['values']);
		if($result !== false){
			$newId = $this->wpdb->insert_id;
			//invoice format: CA-currenttime-orderid
			$invoiceId = sprintf('%s-%s-%s', $prefix, time(), $newId);
			$secretKey = sprintf('%s-%s', uniqid('CA'), $newId);
			$this->wpdb->update($this->order_table_name,  
								array(
									'invoiceId'=>$invoiceId
									, 'secretKey'=>$secretKey
								), array('id'=>$newId), array('%s', '%s'));
			return $newId;
		}
		return false;
	}
	public function requiresGdprNotification(){
		$sql = "SELECT o.email, o.fullname FROM  $this->order_table_name as o INNER JOIN $this->availability_booked_table_name as ab on o.id = ab.orderId";
		$where = array("o.email NOT IN (SELECT userEmail FROM $this->auth_table_name WHERE userEmail IS NOT NULL)");
		$params = array();
		$yesterday = date(CALENDARISTA_DATEFORMAT, strtotime( '-1 days' ));
		array_push($where, 'DATE(ab.toDate) <= \'%s\'');
		array_push($params, $yesterday);
		if(count($where) > 0){
			$sql .= ' WHERE ' .  implode( ' AND ', $where);
		}
		$sql .= ' GROUP BY o.email';
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, array('email'=>$r->email, 'fullname'=>$r->fullname));
			}
			return $resultset;
		}
		return false;
	}
	public function update($order){
		$p = $this->parseParams($order);
		$result = $this->wpdb->update($this->order_table_name, $p['params'], array('id'=>$order->id),  $p['values']);
		return $result;
	}
	public function updateBookingStatus($orderId, $status){
		 $result = $this->wpdb->update($this->order_table_name,  array(
			'bookingStatus'=>$status
		  ), array('id'=>$orderId), array('%d'));
		 return $result;
	}
	public function updatePaymentStatus($orderId, $status){
		$today = new Calendarista_DateTime();
		 $result = $this->wpdb->update($this->order_table_name,  array(
			'paymentStatus'=>$status
			, 'paymentDate'=>$today->format(CALENDARISTA_FULL_DATEFORMAT)
		  ), array('id'=>$orderId), array('%d', '%s'));
		 return $result;
	}
	public function updateChanges($args){
		$params = array();
		$values = array();
		
		if(!empty($args['tax'])){
			$params['tax'] = $args['tax'];
			array_push($values, '%f');
		}
		
		if(!empty($args['discount'])){
			$params['discount'] = $args['discount'];
			array_push($values, '%f');
		}
		
		if(!empty($args['discountMode'])){
			$params['discountMode'] = $args['discountMode'];
			array_push($values, '%d');
		}
		
		if(!empty($args['totalAmount'])){
			$params['totalAmount'] = $args['totalAmount'];
			array_push($values, '%f');
		}
		$result = $this->wpdb->update($this->order_table_name, $params, array('id'=>$args['id']),  $values);
		return $result;
	}
	public function delete($id){
		$mapRepo = new Calendarista_BookedMapRepository();
		$mapRepo->deleteByOrder($id);
		$waypointRepo = new Calendarista_BookedWaypointRepository();
		$waypointRepo->deleteByOrder($id);
		$optionalRepo = new Calendarista_BookedOptionalRepository();
		$optionalRepo->deleteByOrder($id);
		$formElementRepo = new Calendarista_BookedFormElementRepository();
		$formElementRepo->deleteByOrder($id);
		$dynamicFieldRepo = new Calendarista_BookedDynamicFieldRepository();
		$dynamicFieldRepo->deleteByOrder($id);
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($id);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		foreach($bookedAvailabilityList as $bookedAvailability){
			if($bookedAvailability->startTimeId && $bookedAvailability->endTimeId){
				$timeslots = $timeslotRepo->readAllByStartEnd((int)$bookedAvailability->startTimeId, (int)$bookedAvailability->endTimeId);
				foreach($timeslots as $timeslot){
					$timeslot = $this->updateBookedSeats($timeslot, (int)$bookedAvailability->seats);
					$timeslotRepo->update($timeslot);
				}
			}else if($bookedAvailability->startTimeId){
				$timeslot = $timeslotRepo->read($bookedAvailability->startTimeId);
				if($timeslot){
					$timeslot = $this->updateBookedSeats($timeslot, (int)$bookedAvailability->seats);
					$timeslotRepo->update($timeslot);
				}
			}
		}
		$bookedAvailabilityRepo->deleteByOrder($id);
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_table_name WHERE id = %d", $id) );
	}
	protected function updateBookedSeats($timeslot, $seats){
		if($timeslot->bookedSeats > 0){
			$timeslot->bookedSeats -= $seats;
			if($timeslot->bookedSeats < 0){
				$timeslot->bookedSeats = 0;
			}
		}
		return $timeslot;
	}
	public function deleteAll($projectId){
		$mapRepo = new Calendarista_BookedMapRepository();
		$mapRepo->deleteAll($projectId);
		$waypointRepo = new Calendarista_BookedWaypointRepository();
		$waypointRepo->deleteAll($projectId);
		$optionalRepo = new Calendarista_BookedOptionalRepository();
		$optionalRepo->deleteAll($projectId);
		$formElementRepo = new Calendarista_BookedFormElementRepository();
		$formElementRepo->deleteAll($projectId);
		$dynamicFieldRepo = new Calendarista_BookedDynamicFieldRepository();
		$dynamicFieldRepo->deleteAll($projectId);
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityRepo->deleteAll($projectId);
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_table_name WHERE projectId = %d", $projectId));
	}
	
	public function deleteAllByAvailability($availabilityId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_table_name WHERE availabilityId = %d", $availabilityId));
	}
	
	public function parseParams($order){
		$params = array();
		$values = array();
		
		if(isset($order->orderDate)){
			$params['orderDate'] = $order->orderDate->format(CALENDARISTA_FULL_DATEFORMAT);
			array_push($values, '%s');
		}
		if(isset($order->projectId)){
			$params['projectId'] = $order->projectId;
			array_push($values, '%d');
		}
		if(isset($order->stagingId)){
			$params['stagingId'] = $order->stagingId;
			array_push($values, '%s');
		}
		if(isset($order->availabilityId)){
			$params['availabilityId'] = $order->availabilityId;
			array_push($values, '%d');
		}
		if(isset($order->availabilityName)){
			$params['availabilityName'] = $order->availabilityName;
			array_push($values, '%s');
		}
		if(isset($order->projectName)){
			$params['projectName'] = $order->projectName;
			array_push($values, '%s');
		}
		if(isset($order->userId)){
			$params['userId'] = $order->userId;
			array_push($values, '%d');
		}
		if(isset($order->fullName)){
			$params['fullname'] = $order->fullName;
			array_push($values, '%s');
		}
		if(isset($order->email)){
			$params['email'] = $order->email;
			array_push($values, '%s');
		}
		if(isset($order->paymentStatus)){
			$params['paymentStatus'] = $order->paymentStatus;
			array_push($values, '%d');
		}
		if(isset($order->transactionId)){
			$params['transactionId'] = $order->transactionId;
			array_push($values, '%s');
		}
		if(isset($order->wooCommerceOrderId)){
			$params['wooCommerceOrderId'] = $order->wooCommerceOrderId;
			array_push($values, '%d');
		}
		if(isset($order->totalAmount)){
			$params['totalAmount'] = $order->totalAmount;
			array_push($values, '%f');
		}
		if(isset($order->currency)){
			$params['currency'] = $order->currency;
			array_push($values, '%s');
		}
		if(isset($order->currencySymbol)){
			$params['currencySymbol'] = $order->currencySymbol;
			array_push($values, '%s');
		}
		if(isset($order->discount)){
			$params['discount'] = $order->discount;
			array_push($values, '%f');
		}
		if(isset($order->discountMode)){
			$params['discountMode'] = $order->discountMode;
			array_push($values, '%d');
		}
		if(isset($order->tax)){
			$params['tax'] = $order->tax;
			array_push($values, '%f');
		}
		if(isset($order->taxMode)){
			$params['taxMode'] = $order->taxMode;
			array_push($values, '%d');
		}
		if(isset($order->refundAmount)){
			$params['refundAmount'] = $order->refundAmount;
			array_push($values, '%f');
		}
		if(isset($order->timezone)){
			$params['timezone'] = $order->timezone;
			array_push($values, '%s');
		}
		if(isset($order->serverTimezone)){
			$params['serverTimezone'] = $order->serverTimezone;
			array_push($values, '%s');
		}
		if(isset($order->paymentsMode)){
			$params['paymentsMode'] = $order->paymentsMode;
			array_push($values, '%d');
		}
		if(isset($order->paymentOperator)){
			$params['paymentOperator'] = $order->paymentOperator;
			array_push($values, '%s');
		}
		if(isset($order->deposit)){
			$params['deposit'] = $order->deposit;
			array_push($values, '%f');
		}
		if(isset($order->depositMode)){
			$params['depositMode'] = $order->depositMode;
			array_push($values, '%d');
		}
		if(isset($order->balance)){
			$params['balance'] = $order->balance;
			array_push($values, '%f');
		}
		if($order->paymentDate){
			$params['paymentDate'] = $order->paymentDate->format(CALENDARISTA_FULL_DATEFORMAT);
			array_push($values, '%s');
		}
		if(isset($order->secretKey)){
			$params['secretKey'] = $order->secretKey;
			array_push($values, '%s');
		}
		if(isset($order->requestId)){
			$params['requestId'] = $order->requestId;
			array_push($values, '%s');
		}
		if(isset($order->repeatWeekdayList)){
			$repeatWeekdayList = $order->repeatWeekdayList;
			if(is_array($repeatWeekdayList)){
				$repeatWeekdayList = implode(',', $repeatWeekdayList);
			}
			$params['repeatWeekdayList'] = $repeatWeekdayList;
			array_push($values, '%s');
		}
		if(isset($order->repeatFrequency)){
			$params['repeatFrequency'] = $order->repeatFrequency;
			array_push($values, '%d');
		}
		if(isset($order->repeatInterval)){
			$params['repeatInterval'] = $order->repeatInterval;
			array_push($values, '%d');
		}
		if(isset($order->terminateAfterOccurrence)){
			$params['terminateAfterOccurrence'] = $order->terminateAfterOccurrence;
			array_push($values, '%d');
		}
		if(isset($order->couponCode)){
			$params['couponCode'] = $order->couponCode;
			array_push($values, '%s');
		}
		if(isset($order->upfrontPayment)){
			$params['upfrontPayment'] = $order->upfrontPayment;
			array_push($values, '%d');
		}
		return array('params'=>$params, 'values'=>$values);
	}
}
?>