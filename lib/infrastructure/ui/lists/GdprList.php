<?php
class Calendarista_GdprList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	function __construct(){
		$this->perPage = 10;
        parent::__construct( array(
            'singular'  => 'gdpr', 
            'plural'    => 'gdpr', 
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
			'<button class="button button-primary" name="calendarista_delete_user_history"  
				value="%s">
				%s
			</button>'
			, $item['userEmail']
			, __('Delete user history', 'calendarista')
		));
		array_push($result, sprintf(
			'<button class="button button-primary" name="calendarista_deny_request"  
				value="%d">
				%s
			</button>'
			, $item['id']
			, __('Refuse', 'calendarista')
		));
        return join("\n", $result);
	}
	function column_userEmail($item){
		return $item['userEmail'];
	}
	function column_requestDate($item){
		return $item['requestDate']->format(CALENDARISTA_DATEFORMAT);
	}
    function get_columns(){
        $columns = array(
			'userEmail'=>__('Customer Email', 'calendarista')
			, 'requestDate'=>__('Request Date', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
        );
        return $columns;
    }
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'userEmail'=>array('userEmail', false)
			, 'requestDate'=>array('requestDate', false)
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
		$allowedKeys = array('id', 'requestDate', 'userEmail', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc'; 
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$gdprRepository = new Calendarista_GdprRepository();
        $result = $gdprRepository->readAll($currentPage, $this->perPage, $this->orderBy, $this->order);
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
		return array('wp-list-table', 'calendarista',  'widefat', 'fixed', 'striped');
	}
}
?>