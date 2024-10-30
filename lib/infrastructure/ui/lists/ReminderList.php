<?php
class Calendarista_ReminderList extends Calendarista_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $count;
	function __construct( ){
		$this->perPage = 10;
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
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
    
	function column_id($item){
		return $item['id'];
	}
	function column_orderId($item){
		return $item['orderId'];
	}
	function column_sentDate($item){
		return Calendarista_TimeHelper::formatDate($item['sentDate']);
	}
	function column_fullName($item){
		return  $item['fullName'] ? $item['fullName'] : '--';
	}
	function column_email($item){
		return $item['email'];
	}
	function column_reminderType($item){
		return (int)$item['reminderType'] == 0 ? 'Appointment' : 'Thankyou';
	}
	function column_action($item){
		$buttonGroups = array();
		array_push($buttonGroups, sprintf(
			'<li>
				<button class="button button-primary" name="resend" title="%s">
					<i class="fa-regular fa-paper-plane"></i> 
				</button>
			</li>'
			, __('Send reminder again.', 'calendarista')
		));
		array_push($buttonGroups, sprintf(
			'<li>
				<button class="button button-primary" name="delete" title="%s">
					<i class="fa fa-remove"></i> 
				</button>
			</li>'
			, __('Delete this reminder.', 'calendarista')
		));
        return sprintf(
			'<form action="%s" method="post">
				<input type="hidden" name="controller" value="calendarista_reminders" />
				<input type="hidden" name="id" value="%d" />
				<input type="hidden" name="projectId" value="%d"/>
				<input type="hidden" name="orderId" value="%d"/>
				<input type="hidden" name="bookedAvailabilityId" value="%d"/>
				<input type="hidden" name="reminderType" value="%d"/>
				<ul class="action-list">%s</ul>
			</form>'
			, $_SERVER['REQUEST_URI']
			, $item['id']
			, $item['projectId']
			, $item['orderId']
			, $item['bookedAvailabilityId']
			, $item['reminderType']
			, join("\n", $buttonGroups)
        );
	}
	
    function get_columns(){
        $columns = array(
			'id'=>__('ID', 'calendarista')
			, 'orderId'=>__('ORDER ID', 'calendarista')
			, 'sentDate'=>__('SENT DATE', 'calendarista')
            , 'fullName'=>__('NAME', 'calendarista')
			, 'email'=>__('EMAIL', 'calendarista')
			, 'reminderType'=>__('Reminder type', 'calendarista')
			, 'action'=>__('ACTION', 'calendarista')
        );
        return $columns;
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
		$allowedKeys = array('id', 'projectId', 'orderId', 'bookedAvailabilityId', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc';
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$remindersRepository = new Calendarista_RemindersRepository();
        $result = $remindersRepository->readAll($currentPage, $this->perPage, $this->orderBy, $this->order);
        $this->count = $result->total;
        $this->items = $result->toArray();
		if(!$this->count){
			return;
		}
		$this->totalPages = ceil($result->total / $this->perPage);
        $this->set_pagination_args( array(
            'total_items' => $this->count,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages
        ) );
    }
	
	function get_table_classes() {
		return array('wp-list-table', 'calendarista',  'widefat', 'fixed', 'striped');
	}
}
?>