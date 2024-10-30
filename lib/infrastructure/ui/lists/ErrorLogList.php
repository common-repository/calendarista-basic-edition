<?php
class Calendarista_ErrorLogList extends Calendarista_List {
	public $perPage;
	public $orderBy;
  function __construct(){
		parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
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
    
	function column_id($item){
		return $item['id'] ;
	}
	
	function column_entryDate($item){
		return $item['entryDate']->format(get_option('date_format'));
	}
	
	function column_message($item){
		return $item['message'];
	}
    
	
	function column_tasks($item){
		$buttons = array();
		array_push($buttons, sprintf(
			'<button class="button button-primary" name="calendarista_delete" value="%s">
				%s
			</button>'
			, $item['id']
			, __('Delete', 'calendarista')
		));
		
		return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="calendarista_errorlog" />
				%s
			</form>'
			, $_SERVER['REQUEST_URI']
			, join("\n", $buttons)
        );
	}
	
	
    function get_columns(){
        $columns = array(
			'id'=>__('#id', 'calendarista')
			, 'entryDate'=>__('Entry Date', 'calendarista')
			, 'message'=>__('Error', 'calendarista')
			, 'tasks'=>__('Tasks', 'calendarista')
        );
		
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
            , 'entryDate'=> array('entryDate', false)
        );
        return $sortable_columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
        $per_page = 5;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $currentPage = $this->get_pagenum() - 1;
		if($currentPage){
			$currentPage = $currentPage * $per_page;
		}
		$allowedKeys = array('id', 'entryDate', 'message', 'asc', 'desc');
        $orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($orderBy, $allowedKeys, true)) {
			return;
		}
        $order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc'; 
		if (!in_array($order, $allowedKeys, true)) {
			return;
		}
		$errorLogRepository = new Calendarista_ErrorLogRepository();
        $result = $errorLogRepository->readAll($currentPage, $per_page, $orderBy, $order);
        $total_pages = ceil($result->total / $per_page);
        $this->items = $result->toArray();
        $this->set_pagination_args( array(
            'total_items' => $result->total,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ) );
    }
	function get_table_classes() {
		return array('wp-list-table', 'calendarista',  'widefat', 'fixed', 'striped');
	}
}
?>