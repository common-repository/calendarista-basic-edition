<?php
class Calendarista_AvailabilityDayList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	public $availabilityId;
	function __construct($availabilityId){
		$this->availabilityId = $availabilityId;
		$this->perPage = 10;
        parent::__construct( array(
            'singular'  => 'availability_day_list', 
            'plural'    => 'availability_day_list', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$item = (array)$item;
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
                return print_r((array)$item,true); 
        }
    }
	function column_ck($item){
		return sprintf('<input type="checkbox" name="individualDay[]" value="%d">', $item['id']);
	}
	function column_individualDay($item){
		return date(CALENDARISTA_DATEFORMAT, strtotime($item['individualDay']));
	}
	function column_action($item){
		$item = (array)$item;
		$result = array();
		array_push($result, sprintf(
			'<button type="button" class="button button-primary" name="calendarista_delete" 
				value="%d">
				%s
			</button>'
			, $item['id']
			, __('Delete', 'calendarista')
		));
        return join("\n", $result);
	}
    function get_columns(){
        $columns = array(
			'ck'=>'<input type="checkbox" name="selectall">'
			, 'individualDay'=>__('Available date', 'calendarista')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array();
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
		$allowedKeys = array('id', 'projectId', 'availabilityId', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc'; 
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$availabilityDayRepository = new Calendarista_AvailabilityDayRepository();
        $result = $availabilityDayRepository->readAll(array(
			'pageIndex'=>$currentPage
			, 'limit'=>$this->perPage
			, 'orderBy'=>$this->orderBy
			, 'order'=>$this->order
			, 'availabilityId'=>$this->availabilityId
		));
		$this->currentPage = $currentPage;
        $this->count = $result['total'];
        $this->items = $result['items'];
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
		return array('wp-list-table', 'calendarista',  'widefat', 'striped', 'calendarista-day-list');
	}
	public function no_items() {
		esc_html_e('No individual days found.', 'calendarista');
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