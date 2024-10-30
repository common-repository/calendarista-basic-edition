<?php
class Calendarista_SalesList extends Calendarista_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	public $orderId;
	public $sum = 0.00;
	public $bookedAvailabilityRepo;
	function __construct( ){
		$this->perPage = 10;
		$orderId = isset($_REQUEST['orderid']) ? (int)$_REQUEST['orderid'] : null;
		$this->bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	function getAvailabilityNames($orderId){
		$bookedAvailabilityList = $this->bookedAvailabilityRepo->readByOrderId((int)$orderId);
		$bookedAvailability = $bookedAvailabilityList[0];
		$idList = array();
		$availabilityNames = array();
		foreach($bookedAvailabilityList as $bal){
			if(in_array((int)$bal->availabilityId, $idList)){
				continue;
			}
			array_push($availabilityNames, $bal->availabilityName);
			array_push($idList, (int)$bal->availabilityId);
		}
		return $availabilityNames;
	}
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		if($item['id'] == $this->orderId){
			$row_class .= ' table-selected-row';
		}
		$row_class = $row_class ? ' class="' . $row_class . '"' : '';
		echo '<tr' . esc_html($row_class) . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}
	
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
    
	function column_id($item){
		return $item['invoiceId'];
	}
	function column_projectName($item){
		$availabilityNames = $this->getAvailabilityNames($item['id']);
		return sprintf('%s <hr> (%s) ', $item['projectName'], implode('<br>', $availabilityNames));
	}
	function column_name($item){
		return '<strong>' . implode(',<br>', array(sanitize_text_field($item['fullName']), sanitize_email($item['email']))) . '</strong>';
	}
	
	function column_orderDate($item){
		$orderDate = new Calendarista_DateTime($item['orderDate']);
		return $orderDate->format(CALENDARISTA_DATEFORMAT);
	}
	function column_status($item){
		return Calendarista_Order::getPaymentStatus($item['paymentStatus']);
	}
	function column_totalAmount($item){
		return Calendarista_MoneyHelper::toShortCurrency($item['totalAmount'], true, $item['currency'], $item['currencySymbol']);
	}
    
	function column_Action($item){
		$result = array();
		array_push($result, sprintf(
			'<p><button type="button" class="button button-primary" name="viewAppointment" 
				data-calendarista-project-id="%d" 
				data-calendarista-availability-id="%d" 
				value="%d">
				%s
			</button></p>'
			, $item['projectId']
			, $item['availabilityId']
			, $item['id']
			, __('Appointment', 'calendarista')
		));
		array_push($result, sprintf(
			'<p><button type="button" class="button button-primary" name="details" 
				value="%d" data-calendarista-payment-status="%d" data-calendarista-project-id="%d">
				%s
			</button></p>'
			, $item['id']
			, $item['paymentStatus']
			, $item['projectId']
			, __('Details', 'calendarista')
		));
		if($item['wooCommerceOrderId']){
			array_push($result, sprintf(
				'<p><a href="%s" target="_blank" class="button button-primary">
					%s
				</a></p>'
				, get_edit_post_link((int)$item['wooCommerceOrderId'])
				, __('Edit Woo order', 'calendarista')
			));
		}
        return join("\n", $result);
	}
	
    function get_columns(){
        $columns = array(
			 'id'=>__('id', 'calendarista')
			, 'name'=>__('Customer', 'calendarista')
            , 'projectName'=>__('Service', 'calendarista')
			, 'orderDate'=>__('Sale Date', 'calendarista')
			, 'status'=>__('Status', 'calendarista')
			, 'totalAmount'=>__('Total Amount', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
			, 'projectName'=>array('projectId', false)
			, 'name'=> array('email', false)
            , 'orderDate'=> array('orderDate', false)
			, 'paymentDate'=> array('paymentDate', false)
			, 'totalAmount'=>array('totalAmount', false)
			, 'status'=>array('paymentStatus', false)
        );
        return $sortable_columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $currentPage = $this->get_pagenum() - 1;
		if( $currentPage ){
			$currentPage = $currentPage * $this->perPage;
		}
		
		$projectId = !empty($_REQUEST['projectId']) ? (int)$_REQUEST['projectId'] : null;
		$availabilityId = !empty($_REQUEST['availabilityId']) ? (int)$_REQUEST['availabilityId'] : null;
		$allowedKeys = array('id', 'email', 'projectId', 'orderDate', 'paymentDate', 'totalAmount', 'paymentStatus', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc'; 
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$fromDate = !empty($_REQUEST['from']) ? new Calendarista_DateTime(sanitize_text_field($_REQUEST['from'])) : null;
		$toDate = !empty($_REQUEST['to']) ? new Calendarista_DateTime(sanitize_text_field($_REQUEST['to'])) : null;
		$customerName = !empty($_REQUEST['customerName']) ? sanitize_text_field($_REQUEST['customerName']) : null;
		$email = !empty($_REQUEST['email']) ? sanitize_email($_REQUEST['email']) : null;
		$invoiceId = !empty($_REQUEST['invoiceId']) ? sanitize_text_field($_REQUEST['invoiceId']) : null;
		$showExpired = !empty($_REQUEST['showExpired']) ? (bool)$_REQUEST['showExpired'] : null;
		$orderId = !empty($_REQUEST['orderId']) ? (int)$_REQUEST['orderId'] : null;
		$staffMemberAvailabilities = Calendarista_PermissionHelper::staffMemberAvailabilities();
		$orderRepository = new Calendarista_OrderRepository();
        $orders = $orderRepository->readAll(array(
			'pageIndex'=>$currentPage
			, 'limit'=>$this->perPage
			, 'orderBy'=>$this->orderBy
			, 'order'=>$this->order
			, 'fromDate'=>$fromDate
			, 'toDate'=>$toDate
			, 'customerName'=>$customerName
			, 'email'=>$email
			, 'projectId'=>$projectId
			, 'orderId'=>$orderId
			, 'availabilityId'=>$availabilityId
			, 'invoiceId'=>$invoiceId
			, 'showExpired'=>$showExpired
			, 'availabilities'=>$staffMemberAvailabilities
			, 'sales'=>true
		));
		$this->items = $orders->toArray();
		if(!$orders->total){
			return;
		}
		$this->sum = $orders->totalAmount;
        $this->totalPages = ceil($orders->total / $this->perPage);
        $this->set_pagination_args( array(
            'total_items' => $orders->total,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages
        ));
    }
	
	function get_table_classes() {
		return array('wp-list-table', 'calendarista',  'widefat', 'fixed', 'striped');
	}
	public function no_items() {
		if (array_key_exists('customerName', $_REQUEST)){
			esc_html_e('The selected customer name has no sales data.', 'calendarista');
		}else if (array_key_exists('email', $_REQUEST)){
			esc_html_e('The selected customer email has no sales data.', 'calendarista');
		}else if(array_key_exists('invoiceId', $_REQUEST)){
			esc_html_e('The selected invoice ID has no sales data.', 'calendarista');
		}else{
			_e( 'No items found.' );
		}
	}
	public function printVariables(){
		$list = array(
			'<input type="hidden" name="paged" value="' . $this->get_pagenum() . '">'
			, '<input type="hidden" name="orderby" value="' .  $this->orderBy . '">'
			, '<input type="hidden" name="order" value="' . $this->order . '">'
		);
		echo implode('', $list);
	}
}
?>