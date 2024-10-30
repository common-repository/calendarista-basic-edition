<?php
class Calendarista_AppointmentList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	public $generalSetting;
	function __construct(){
		$this->perPage = 10;
        parent::__construct( array(
            'singular'  => 'appointment-list', 
            'plural'    => 'appointment-list', 
            'ajax'      => false    
        ) );
    }
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
		if($item['id'] == $id){
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
	function column_action($item){
		$result = array();
		array_push($result, sprintf(
			'<a class="button button-primary edit-appointment-list-item" href="javascript:void(0);" 
				data-calendarista-id="%d"
				data-calendarista-order-id="%d"
				data-calendarista-project-id="%d" 
				data-calendarista-availability-id="%d" 
				data-calendarista-status="%d"
				data-calendarista-synched-booking-id="%s"
				data-calendarista-synched="%d"
				data-calendarista-raw-title="%s"
				data-calendarista-raw-description="%s">
				%s
			</a>'
			, $item['id']
			, $item['orderId']
			, $item['projectId']
			, $item['availabilityId']
			, $item['status']
			, $item['synchedBookingId']
			, $item['synched']
			, $item['rawTitle']
			, $item['rawDescription']
			, __('Details', 'calendarista')
		));
        return join("\n", $result);
	}
	function column_date($item){
		return $item['date'];
	}
	function column_orderDate($item){
		return $item['orderDate'] ? $item['orderDate'] : __('Imported', 'calendarista');
	}
	function column_name($item){
		if((bool)$item['synched']){
			return str_replace('\n', '<br>', $item['name']);
		}
		return $item['name'];
	}
	function column_email($item){
		if(!$item['email']){
			return '--';
		}
		return $item['email'];
	}
	function column_info($item){
		$result = array();
		array_push($result, sprintf('<strong>%s</strong>  —%s', $item['serviceName'], $item['availabilityName']));
		array_push($result, sprintf('<strong>%s</strong>  —%s', __('Status', 'calendarista'), $item['statusLabel']));
		array_push($result, sprintf('<strong>%s</strong>  —%s', __('Seats', 'calendarista'), $item['seats']));
		if((bool)$item['synched']){
			array_push($result, sprintf('<strong>%s</strong>  %s', __('Synched', 'calendarista'), '<i class="fa fa-check" aria-hidden="true"></i>'));
		}else{
			$customFormFields = Calendarista_NotificationEmailer::getCustomFormElements($item['orderId']);
			$optionals = Calendarista_NotificationEmailer::getOptionals($item['orderId']);
			$guests = Calendarista_NotificationEmailer::getDynamicFields($item['orderId']);
			if(trim($customFormFields)){
				array_push($result, $customFormFields);
			}
			if(trim($optionals)){
				array_push($result, $optionals);
			}
			if(trim($guests)){
				array_push($result, $guests);
			}
		}
		return join('<br>', $result);
	}

    function get_columns(){
        $columns = array(
			'date'=>__('Appointment Date', 'calendarista')
			, 'orderDate'=>__('Order Date', 'calendarista')
			, 'name'=>__('Name', 'calendarista')
			, 'email'=>__('Email', 'calendarista')
			, 'info'=>__('Info', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
        );
        return $columns;
    }
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
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
		if($currentPage){
			$currentPage = $currentPage * $this->perPage;
		}
		$this->currentPage = $currentPage;
        $orderBy = isset($_REQUEST['orderby']) ? (int)$_REQUEST['orderby'] : $this->generalSetting->appointmentListOrder;
		$start = isset($_REQUEST['start']) ? sanitize_text_field($_REQUEST['start']) : null;
		$end = isset($_REQUEST['end']) ? sanitize_text_field($_REQUEST['end']) : null;
		$invoiceId = isset($_REQUEST['invoiceId']) ? sanitize_text_field($_REQUEST['invoiceId']) : null;
		$syncDataFilter = isset($_REQUEST['syncDataFilter']) ? (int)$_REQUEST['syncDataFilter'] : 1;
		$status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? (int)$_REQUEST['status'] : null;
		$customerName = isset($_REQUEST['customerName']) ? sanitize_text_field($_REQUEST['customerName']) : null;
		$email = isset($_REQUEST['email']) ? sanitize_email($_REQUEST['email']) : null;
		$this->orderBy = null;
		$this->order = null;
		switch($orderBy){
			case 0: //Start date ASC
			case 1: //Start date DESC
			$this->orderBy = 'a.fromDate';
			$this->order = $orderBy === 0 ? 'asc' : 'desc';
			break;
			case 2: //Order date ASC
			case 3: //Order date DESC
			$this->orderBy = 'o.orderDate';
			$this->order = $orderBy === 2 ? 'asc' : 'desc';
			break;
		}
		if(isset($_REQUEST['orderby']) && $orderBy != $this->generalSetting->appointmentListOrder){
			$repo = new Calendarista_GeneralSettingsRepository();
			$this->generalSetting->appointmentListOrder =  $orderBy;
			$repo->update($this->generalSetting);
		}
		$args = array(
			'projectId'=>isset($_REQUEST['projectId']) && !in_array($_REQUEST['projectId'], array('', '-1')) ? (int)$_REQUEST['projectId'] : null
			, 'availabilityId'=>isset($_REQUEST['availabilityId']) && !in_array($_REQUEST['availabilityId'], array('', '-1')) ? (int)$_REQUEST['availabilityId'] : null
			, 'start'=>$start
			, 'end'=>$end
			, 'syncDataFilter'=>$syncDataFilter
			, 'invoiceId'=>$invoiceId
			, 'pageIndex'=>$currentPage
			, 'limit'=>$this->perPage
			, 'orderBy'=>$this->orderBy
			, 'order'=>$this->order
			, 'returnList'=>true
			, 'customerName'=>$customerName
			, 'email'=>$email
			, 'invoiceId'=>$invoiceId
			, 'status'=>$status
		);
		
		$result = Calendarista_FeedHelper::getBookedAvailabilities($args);
        $this->count = $result['total'];
		$this->items = $result['resultset'];
		if(!$this->count){
			return;
		}
		$this->totalPages = ceil($this->count / $this->perPage);
        $this->set_pagination_args( array(
            'total_items' => $this->count,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages
        ) );
    }
	function get_table_classes() {
		return array('wp-list-table', 'calendarista',  'widefat', 'striped', 'calendarista-appointment-list');
	}
	public function printVariables(){
		$list = array(
			'<input type="hidden" name="paged" value="' . $this->get_pagenum() . '">'
		);
		echo implode('', $list);
	}
}
?>