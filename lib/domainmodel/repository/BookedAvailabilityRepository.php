<?php
class Calendarista_BookedAvailabilityRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $availability_table_name;
	private $availability_booked_table_name;
	private $order_table_name;
	private $auth_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
		$this->availability_table_name = $wpdb->prefix . 'calendarista_availability';
		$this->order_table_name = $wpdb->prefix . 'calendarista_order';
		$this->auth_table_name = $wpdb->prefix . 'calendarista_auth';
	}

	public function readAll($args){
		$pageIndex = isset($args['pageIndex']) ? $args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? $args['limit'] : 5;
		$orderBy = isset($args['orderBy']) ? $args['orderBy'] : null;
		$order = isset($args['order']) ? $args['order'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) ? $args['toDate'] : null;
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$projectList = isset($args['projectList']) ? $args['projectList'] : null;
		$orderId = isset($args['orderId']) ? $args['orderId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$showExpired = isset($args['showExpired']) ? $args['showExpired'] : true;
		$availabilities = isset($args['availabilities']) && is_array($args['availabilities']) ? implode(',', array_map('intval', $args['availabilities'])) : false;
		$syncDataFilter = isset($args['syncDataFilter']) ? $args['syncDataFilter'] : false;
		$calendarModeList = isset($args['calendarModeList']) ? $args['calendarModeList'] : false;
		$userId = isset($args['userId']) ? $args['userId'] : false;
		$email = isset($args['email']) ? $args['email'] : null;
		$customerName = isset($args['customerName']) ? $args['customerName'] : null;
		$invoiceId = isset($args['invoiceId']) ? $args['invoiceId'] : null;
		$status1 = isset($args['status']) ? $args['status'] : null;
		$status2 = isset($args['status2']) ? $args['status2'] : null;
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'a.fromDate';
		}
		if($order === null){
			$order = 'desc';
		}
		$query = "SELECT a.*, o.fullName, o.email, o.invoiceId, o.orderDate FROM  $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id";
		$where = array();
		$params = array();
		if($orderId === null){
			if(!empty($fromDate) && !$email){
				$whereClause = '(DATE(a.fromDate) >= CONVERT(%s, DATE))';
				if(!empty($toDate)){
					$whereClause = '(' . $whereClause;
				}
				array_push($where, $whereClause);
				array_push($params, $fromDate);
			}
			if(!$email){
				if(empty($fromDate) && !empty($toDate)){
					array_push($where, '(DATE(a.toDate) >= CONVERT(%s, DATE))');
					array_push($params, $toDate);
				} else if(!empty($toDate)){
					array_push($where, '(DATE(a.fromDate) <= CONVERT(%s, DATE)) OR (DATE(a.fromDate) <= CONVERT(%s, DATE) AND DATE(a.toDate) >= CONVERT(%s, DATE)))');
					array_push($params, $toDate, $toDate, $fromDate);
				}
			}
			if($customerName){
				array_push($where, 'o.fullName LIKE %s');
				array_push($params, '%' . $customerName . '%');
			} else if($email){
				array_push($where, 'o.email = %s');
				array_push($params, $email);
			}else if ($invoiceId){
				array_push($where, 'o.invoiceId = %s');
				array_push($params, $invoiceId);
			}
			if($userId){
				array_push($where, 'o.userId = %d');
				array_push($params, $userId);
			}
			if($projectId !== null && $projectId !== -1){
				array_push($where, 'a.projectId = %d');
				array_push($params, $projectId);
			} else if($projectList && count($projectList) > 0){
				array_push($where, "a.projectId IN (" . join(',', array_map('intval', $projectList)) . ")");
			}
			if($calendarModeList){
				array_push($where, "a.calendarMode IN (" . join(',', array_map('intval', $calendarModeList)) . ")");
			}
			if(!$showExpired){
				array_push($where, 'DATE(a.fromDate) >= CURDATE()');
			}
		}
		if($orderId !== null && $orderId !== -1){
			array_push($where, 'a.orderId = %d');
			array_push($params, $orderId);
		}
		if($availabilityId !== null && $availabilityId !== -1){
			array_push($where, 'a.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if($availabilities){
			array_push($where, 'a.availabilityId IN (' . $availabilities . ')');
		}
		if($status1 !== null){
			//where status does not match
			array_push($where, 'IFNULL(a.status, -1) != %d');
			array_push($params, $status1);
		}else if($status2 !== null){
			//where status matches
			array_push($where, 'IFNULL(a.status, -1) = %d');
			array_push($params, $status2);
		}
		if($syncDataFilter === 0){
			array_push($where, '(IFNULL(a.synchedMode, 0) IN (0, 1, 2))');
		}else if($syncDataFilter === 1){
			//synch mode of 1 has a synchedbookingid but it's an appointment exported to gcal, not imported.
			array_push($where, 'a.orderId IS NOT NULL');
		}else if($syncDataFilter === 2){
			array_push($where, 'a.orderId IS NULL');
		}
		if($invoiceId){
			array_push($where, 'o.invoiceId = %s');
			array_push($params, $invoiceId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex !== null && $pageIndex !== -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			$query = "SELECT count(a.id) as total FROM $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$records = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return array('resultset'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT a.*, o.fullName, o.email, o.invoiceId FROM  $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id WHERE  a.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if( $result ){
			return $result[0];
		}
		return false;
	}
	public function findBySyncInfo($args){
		$synchedBookingId = isset($args['synchedBookingId']) ? $args['synchedBookingId'] : null;
		$gcalId = isset($args['gcalId']) ? $args['gcalId'] : null;
		$calendarId = isset($args['calendarId']) ? $args['calendarId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		
		$sql = "SELECT a.*, o.fullName, o.email, o.invoiceId FROM  $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id";
		$where = array();
		$params = array();
		
		if($synchedBookingId !== null){
			array_push($where, 'a.synchedBookingId = %s');
			array_push($params, $synchedBookingId);
		}
		if($gcalId !== null){
			array_push($where, 'a.gcalId = %d');
			array_push($params, $gcalId);
		}
		if($calendarId !== null){
			array_push($where, 'a.calendarId = %s');
			array_push($params, $calendarId);
		}
		if($availabilityId !== null){
			array_push($where, 'a.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if($result){
			return $result[0];
		}
		return false;
	}
	public function readBySynchedBookingId($synchedBookingId){
		$sql = "SELECT a.*, o.fullName, o.email, o.invoiceId FROM  $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id WHERE  a.synchedBookingId = %s ORDER BY a.id";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $synchedBookingId));
		if( $result ){
			return $result[0];
		}
		return false;
	}
	public function readByOrderId($orderId){
		$sql = "SELECT a1.*, o.fullName, o.email, o.invoiceId, a2.instructions FROM  $this->availability_booked_table_name as a1 LEFT JOIN $this->order_table_name as o ON a1.orderId = o.id LEFT JOIN $this->availability_table_name as a2 ON a1.availabilityId = a2.id WHERE  a1.orderId = %d ORDER BY a1.id";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId));
		if( $result ){
			return $result;
		}
		return false;
	}
	public function readByOrderIdList($args){
		$orderIdList = isset($args['orderIdList']) ? $args['orderIdList'] : array();
		$pageIndex = isset($args['pageIndex']) ? $args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? $args['limit'] : 5;
		$upcoming = isset($args['upcoming']) ? $args['upcoming'] : false;
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		$sql = "SELECT a1.*, o.fullName, o.email, o.invoiceId, a2.instructions FROM  $this->availability_booked_table_name as a1 LEFT JOIN $this->order_table_name as o ON a1.orderId = o.id LEFT JOIN $this->availability_table_name as a2 ON a1.availabilityId = a2.id";
		$where = array();
		$params = array();
		if($orderIdList && count($orderIdList) > 0){
			array_push($where, "a1.orderId IN (" . join(',', array_map('intval', $orderIdList)) . ")");
		}
		if($upcoming){
			array_push($where, "a1.fromDate >= NOW()");
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY id';
		if($pageIndex !== null && $pageIndex !== -1){
			$sql .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results($sql);
		if(is_array($result)){
			$query = "SELECT count(a1.id) as total FROM  $this->availability_booked_table_name as a1 LEFT JOIN $this->order_table_name as o ON a1.orderId = o.id";
			if(count($where) > 0){
				$query .= ' WHERE ' . implode(' AND ', $where);
			}
			$records = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return array('resultset'=>$resultset, 'total'=>(int)$records[0]->total);
		}
		return false;
	}
	public function readByInvoiceId($invoiceId){
		$sql = "SELECT a1.*, o.fullName, o.email, o.invoiceId, a2.instructions FROM  $this->availability_booked_table_name as a1 LEFT JOIN $this->order_table_name as o ON a1.orderId = o.id LEFT JOIN $this->availability_table_name as a2 ON a1.availabilityId = a2.id WHERE o.invoiceId = %s ORDER BY a1.id";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $invoiceId));
		if( $result ){
			return $result;
		}
		return false;
	}
	public function readAllSynchedData($availabilityId){
		$sql = "SELECT synchedBookingId FROM   $this->availability_booked_table_name";
		$where = array();
		$today = new Calendarista_DateTime();
		array_push($where, 'availabilityId = %d AND synchedBookingId IS NOT NULL');
		$params = array($availabilityId);
		array_push($where, '(IFNULL(synchedMode, 0) IN (0, 2))');
		array_push($where, 'CONVERT(%s, DATE) <= DATE(toDate)');
		array_push($params, $today->format(CALENDARISTA_DATEFORMAT));
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
		$resultset = array();
		if(is_array($result)){
			foreach($result as $r){
				array_push($resultset, $r->synchedBookingId);
			}
		}
		return $resultset;
	}
	public function readAllByAvailabilityForExport($availabilityId, $gcalId){
		$sql = "SELECT a.*, o.fullName, o.email, o.invoiceId FROM  $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id";
		$where = array();
		$today = new Calendarista_DateTime();
		array_push($where, 'a.availabilityId = %d AND IFNULL(a.gcalId, 0) <> %d');
		$params = array($availabilityId, $gcalId);
		array_push($where, 'a.synchedBookingId IS NULL');
		array_push($where, '(DATE(a.fromDate) >= CONVERT(%s, DATE) AND (CONVERT(%s, DATE) <= DATE(a.toDate)))');
		array_push($params, $today->format(CALENDARISTA_DATEFORMAT));
		array_push($params, $today->format(CALENDARISTA_DATEFORMAT));
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	public function readAllImported($availabilityId, $gcalId){
		$sql = "SELECT id, synchedBookingId, synchedMode, orderId, gcalId FROM  $this->availability_booked_table_name";
		$where = array();
		array_push($where, 'availabilityId = %d AND IFNULL(gcalId, 0) = %d');
		array_push($where, '(IFNULL(synchedMode, 0) IN (2))');
		$params = array($availabilityId, $gcalId);
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
		$resultset = array();
		if(is_array($result)){
			foreach($result as $r){
				array_push($resultset, array('bookingId'=>(int)$r->id, 'eventId'=>$r->synchedBookingId, 'gcalId'=>(int)$r->gcalId, 'synchedMode'=>(int)$r->synchedMode, 'orderId'=>(int)$r->orderId));
			}
		}
		return $resultset;
	}
	public function readAllExported($availabilityId, $gcalId){
		$sql = "SELECT id, synchedBookingId, synchedMode, orderId, calendarId, gcalId FROM  $this->availability_booked_table_name";
		$where = array();
		array_push($where, 'availabilityId = %d AND IFNULL(gcalId, 0) = %d');
		array_push($where, '(IFNULL(synchedMode, 0) IN (1))');
		$params = array($availabilityId, $gcalId);
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
		$resultset = array();
		if(is_array($result)){
			foreach($result as $r){
				array_push($resultset, array('bookingId'=>(int)$r->id, 'eventId'=>$r->synchedBookingId, 'gcalId'=>(int)$r->gcalId, 'synchedMode'=>(int)$r->synchedMode, 'orderId'=>(int)$r->orderId, 'calendarId'=>$r->calendarId));
			}
		}
		return $resultset;
	}
	public function readAllImportedAndExported($availabilityId, $gcalId){
		$sql = "SELECT id, synchedBookingId, synchedMode, calendarId, gcalId, orderId FROM  $this->availability_booked_table_name";
		$where = array();
		array_push($where, 'availabilityId = %d AND IFNULL(gcalId, 0) = %d AND IFNULL(synchedMode, 0) IN (1, 2)');
		$params = array($availabilityId, $gcalId);
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
		$resultset = array();
		if(is_array($result)){
			foreach($result as $r){
				array_push($resultset, array('bookingId'=>(int)$r->id, 'eventId'=>$r->synchedBookingId, 'gcalId'=>(int)$r->gcalId, 'synchedMode'=>(int)$r->synchedMode, 'orderId'=>(int)$r->orderId, 'calendarId'=>$r->calendarId));
			}
		}
		return $resultset;
	}
	public function readAllByAvailability($availabilityId, $status = null){
		$sql = "SELECT * FROM   $this->availability_booked_table_name";
		$where = array();
		$params = array($availabilityId);
		array_push($where, 'availabilityId = %d');
		array_push($where, '(IFNULL(synchedMode, 0) IN (0, 1))');
		if($status !== null){
			array_push($where, 'IFNULL(status, -1) != %d');
			array_push($params, $status);
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function readAllByDateRange($fromDate, $toDate, $availabilityId, $projectId = null, $status = Calendarista_AvailabilityStatus::CANCELLED){
		$sql = "SELECT * FROM   $this->availability_booked_table_name";
		$where = array();
		$params = array();
		if(is_array($availabilityId) && count($availabilityId) > 0){
			array_push($where, 'availabilityId IN (' . implode(',', array_map('intval', $availabilityId)) . ')');
		}else if($availabilityId !== null && $availabilityId !== -1){
			array_push($where, 'availabilityId = %d');
			array_push($params, $availabilityId);
		}
		if($status !== null){
			array_push($where, 'IFNULL(status, -1) != %d');
			array_push($params, $status);
		}
		
		if($projectId !== null){
			array_push($where, 'projectId = %d');
			array_push($params, $projectId);
		}
		array_push($where, '(IFNULL(synchedMode, 0) IN (0, 1, 2))');
		array_push($where, '(DATE(fromDate) >= \'%s\'');
		array_push($where, 'DATE(fromDate) <= \'%s\'');
		array_push($params, $fromDate);
		array_push($params, $toDate);
		
		$sql .= ' WHERE ' . implode(' AND ', $where);
		$sql .= ' OR (DATE(fromDate) <= DATE(\'%s\') AND DATE(toDate) >= \'%s\'))';
		array_push($params, $toDate);
		array_push($params, $fromDate);
		
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function readAllPastAppointmentsByEmail($email){
		$sql = "SELECT * FROM   $this->availability_booked_table_name";
		$where = array("userEmail = %s");
		$params = array($email);
		$yesterday = date(CALENDARISTA_DATEFORMAT, strtotime( '-1 days' ));
		array_push($where, 'DATE(toDate) <= \'%s\'');
		array_push($params, $yesterday);
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode( ' AND ', $where);
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function readAllByProject($projectId){
		$sql = "SELECT * FROM $this->availability_booked_table_name WHERE projectId = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $projectId));
		if(is_array( $result )){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function readAllByDate($date){
		$sql = "SELECT * FROM $this->availability_booked_table_name WHERE %s BETWEEN fromDate AND toDate AND (IFNULL(synchedMode, 0) IN (0, 1))";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $date));
		if(is_array( $result )){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function getCountByOrder($id){
		$sql = "SELECT count(*) as total FROM   $this->availability_booked_table_name WHERE  orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if( $result ){
			return (int)$result[0]->total;
		}
		return false;
	}
	public function export($args){
		$projectId = isset($args['projectId']) ? $args['projectId'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) && !empty($args['toDate']) ? $args['toDate'] : null;
		$email = isset($args['email']) && !empty($args['email']) ? $args['email'] : null;
		$status = isset($args['status']) ? $args['status'] : null;
		$order = isset($args['order']) && !empty($args['order']) ? $args['order'] : null;
		$query = "SELECT a.*, o.invoiceId, o.fullName, o.wooCommerceOrderId FROM $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id";
		$where = array();
		$params = array();
		if($fromDate !== null && !$email){
			array_push($where, '(DATE(a.fromDate) >= CONVERT(%s, DATE) AND (CONVERT(%s, DATE) <= DATE(a.toDate)))');
			array_push($params, $fromDate, $fromDate);
		}
		if($toDate !== null && !$email){
			array_push($where, '(DATE(a.toDate) <= CONVERT(%s, DATE))');
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
		if($status !== null){
			array_push($where, 'IFNULL(a.status, 0) = %d');
			array_push($params, $status);
		}
		array_push($where, 'IFNULL(a.synchedMode, 0) IN (0, 1)');
		array_push($where, 'a.orderId IS NOT NULL');
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		if($order){
			$query .= ' ' . $order;
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	public function readAllByUserEmail($args){
		$orderBy = isset($args['orderBy']) ? $args['orderBy'] : null;
		$order = isset($args['order']) ? $args['order'] : null;
		$fromDate = isset($args['fromDate']) ? $args['fromDate'] : null;
		$toDate = isset($args['toDate']) ? $args['toDate'] : null;
		$availabilityId = isset($args['availabilityId']) ? $args['availabilityId'] : null;
		$email = isset($args['email']) ? $args['email'] : null;
		$customerName = isset($args['customerName']) ? $args['customerName'] : null;
		if($orderBy === null){
			$orderBy = 'a.fromDate';
		}
		if($order === null){
			$order = 'desc';
		}
		$query = "SELECT a.*, o.fullName, o.email FROM  $this->availability_booked_table_name as a LEFT JOIN $this->order_table_name as o ON a.orderId = o.id";
		$where = array();
		$params = array();
		if(!empty($fromDate)){
			$whereClause = '(DATE(a.fromDate) >= CONVERT(%s, DATE))';
			if(!empty($toDate)){
				$whereClause = '(' . $whereClause;
			}
			array_push($where, $whereClause);
			array_push($params, $fromDate);
		}
		if(!empty($toDate)){
			array_push($where, '(DATE(a.fromDate) <= CONVERT(%s, DATE)) OR (DATE(a.fromDate) <= CONVERT(%s, DATE) AND DATE(a.toDate) >= CONVERT(%s, DATE)))');
			array_push($params, $toDate, $toDate, $fromDate);
		}
		if($customerName){
			array_push($where, 'o.fullName LIKE %s');
			array_push($params, '%' . $customerName . '%');
		}
		if($email){
			array_push($where, 'o.email = %s');
			array_push($params, $email);
		}
		if($availabilityId !== null && $availabilityId !== -1){
			array_push($where, 'a.availabilityId = %d');
			array_push($params, $availabilityId);
		}
		array_push($where, 'a.orderId IS NOT NULL');
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($query, $params) : $query);
		if(is_array($result)){
			$resultset = array();
			foreach($result as $r){
				array_push($resultset, (array)$r);
			}
			return $resultset;
		}
		return false;
	}
	public function insert($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->insert($this->availability_booked_table_name, $p['params'], $p['values']);
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->update($this->availability_booked_table_name,  $p['params'], array('id'=>$args['id']), $p['values']);
		return $result;
	}
	public function parseParams($args){
		$params = array();
		$values = array();

		if(array_key_exists('orderId', $args)){
			$params['orderId'] = $args['orderId'];
			array_push($values, '%d');
		}
		if(array_key_exists('availabilityId', $args)){
			$params['availabilityId'] = $args['availabilityId'];
			array_push($values, '%d');
		}
		if(array_key_exists('projectId', $args)){
			$params['projectId'] = $args['projectId'];
			array_push($values, '%d');
		}
		if(array_key_exists('projectName', $args)){
			$params['projectName'] = $args['projectName'];
			array_push($values, '%s');
		}
		if(array_key_exists('availabilityName', $args)){
			$params['availabilityName'] = $args['availabilityName'];
			array_push($values, '%s');
		}
		if(array_key_exists('cost', $args)){
			$params['cost'] = $args['cost'];
			array_push($values, '%f');
		}
		if(array_key_exists('returnCost', $args)){
			$params['returnCost'] = $args['returnCost'];
			array_push($values, '%f');
		}
		if(array_key_exists('status', $args) && is_numeric($args['status'])){
			$params['status'] = $args['status'];
			array_push($values, '%d');
		}
		if(array_key_exists('timezone', $args) && $args['timezone']){
			$params['timezone'] = $args['timezone'];
			array_push($values, '%s');
		}
		if(array_key_exists('serverTimezone', $args) && $args['serverTimezone']){
			$params['serverTimezone'] = $args['serverTimezone'];
			array_push($values, '%s');
		}
		if(array_key_exists('fromDate', $args) && $args['fromDate']){
			$params['fromDate'] = $args['fromDate'];
			array_push($values, '%s');
		}
		if(array_key_exists('toDate', $args) && $args['toDate']){
			$params['toDate'] = $args['toDate'];
			array_push($values, '%s');
		}
		if(array_key_exists('startTimeId', $args)){
			$params['startTimeId'] = $args['startTimeId'];
			array_push($values, '%d');
		}
		if(array_key_exists('endTimeId', $args)){
			$params['endTimeId'] = $args['endTimeId'];
			array_push($values, '%d');
		}
		if(array_key_exists('fullDay', $args)){
			$params['fullDay'] = $args['fullDay'];
			array_push($values, '%d');
		}
		if(array_key_exists('calendarMode', $args)){
			$params['calendarMode'] = $args['calendarMode'];
			array_push($values, '%d');
		}
		if(array_key_exists('seats', $args) && $args['seats'] !== null){
			$params['seats'] = $args['seats'];
			array_push($values, '%d');
		}
		if(array_key_exists('color', $args)){
			$params['color'] = $args['color'];
			array_push($values, '%s');
		}
		if(array_key_exists('userEmail', $args) && $args['userEmail']){
			$params['userEmail'] = $args['userEmail'];
			array_push($values, '%s');
		}
		if(array_key_exists('regionAddress', $args) && $args['regionAddress']){
			$params['regionAddress'] = $args['regionAddress'];
			array_push($values, '%s');
		}
		if(array_key_exists('regionLat', $args) && $args['regionLat']){
			$params['regionLat'] = $args['regionLat'];
			array_push($values, '%s');
		}
		if(array_key_exists('regionLng', $args) && $args['regionLng']){
			$params['regionLng'] = $args['regionLng'];
			array_push($values, '%s');
		}
		if(array_key_exists('synchedBookingId', $args) && $args['synchedBookingId']){
			$params['synchedBookingId'] = $args['synchedBookingId'];
			array_push($values, '%s');
		}
		if(array_key_exists('synchedBookingDescription', $args) && $args['synchedBookingDescription']){
			$params['synchedBookingDescription'] = $args['synchedBookingDescription'];
			array_push($values, '%s');
		}
		if(array_key_exists('synchedBookingSummary', $args) && $args['synchedBookingSummary']){
			$params['synchedBookingSummary'] = $args['synchedBookingSummary'];
			array_push($values, '%s');
		}
		if(array_key_exists('synchedBookingLocation', $args) && $args['synchedBookingLocation']){
			$params['synchedBookingLocation'] = $args['synchedBookingLocation'];
			array_push($values, '%s');
		}
		if(array_key_exists('synchedMode', $args) && in_array($args['synchedMode'], array(0,1,2))){
			$params['synchedMode'] = $args['synchedMode'];
			array_push($values, '%d');
		}
		if(array_key_exists('gcalId', $args) && $args['gcalId']){
			$params['gcalId'] = $args['gcalId'];
			array_push($values, '%d');
		}
		if(array_key_exists('calendarId', $args) && $args['calendarId']){
			$params['calendarId'] = $args['calendarId'];
			array_push($values, '%s');
		}
		if(array_key_exists('repeated', $args)){
			$params['repeated'] = $args['repeated'];
			array_push($values, '%d');
		}
		return array('params'=>$params, 'values'=>$values);
	}
	public function updateStatus($id, $status){
		$result = $this->wpdb->update($this->availability_booked_table_name,  array('status'=>$status), array('id'=>$id), array('%d'));
		return $result;
	}
	public function updateSynchedData($args){
		$p = $this->parseParams($args);
		$result = $this->wpdb->update($this->availability_booked_table_name,  $p['params'], array('synchedBookingId'=>$args['synchedBookingId']), $p['values']);
		return $result;
	}
	public function undoGcalSync($args){
		$params = array();
		$values = array();
		$params['synchedBookingId'] = null;
		array_push($values, '%s');
		
		$params['synchedBookingDescription'] = null;
		array_push($values, '%s');
		
		$params['synchedBookingSummary'] = null;
		array_push($values, '%s');
		
		$params['synchedBookingLocation'] = null;
		array_push($values, '%s');
		
		$params['synchedMode'] = null;
		array_push($values, '%d');
		
		$params['gcalId'] = null;
		array_push($values, '%d');
		
		$params['calendarId'] = null;
		array_push($values, '%s');
		
		$identifiers = array();
		if(isset($args['id'])){
			$identifiers['id'] = $args['id'];
		}
		if(isset($args['gcalId'])){
			$identifiers['gcalId'] = $args['gcalId'];
		}
		if(isset($args['availabilityId'])){
			$identifiers['availabilityId'] = $args['availabilityId'];
		}
		if(isset($args['synchedMode'])){
			$identifiers['synchedMode'] = $args['synchedMode'];
		}
		$result = $this->wpdb->update($this->availability_booked_table_name,  $params, $identifiers, $values);
		return $result;
	}
	public function deleteSyncedDataById($synchedBookingId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE synchedBookingId = %s", $synchedBookingId));
	}
	public function deleteSyncedDataByAvailability($availabilityId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE synchedBookingId IS NOT NULL AND availabilityId = %d AND synchedMode = 0", $availabilityId));
	}
	public function deleteAllImportedFeeds(){
		return $this->wpdb->query("DELETE FROM $this->availability_booked_table_name WHERE synchedBookingId IS NOT NULL AND IFNULL(synchedMode, 0) IN (0, 2)");
	}
	public function deleteImportedDataByAvailability($availabilityId, $gcalId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE availabilityId = %d AND gcalId = %d AND synchedMode = 2", $availabilityId, $gcalId));
	}
	public function delete($id){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE id = %d", $id) );
	}
	
	public function deleteByOrder($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE orderId = %d", $orderId) );
	}
	public function deleteAllByAvailability($availabilityId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE availabilityId = %d", $availabilityId) );
	}
	public function deleteAll($projectId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->availability_booked_table_name WHERE projectId = %d", $projectId));
	}
}
?>