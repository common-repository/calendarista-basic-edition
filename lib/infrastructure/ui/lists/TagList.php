<?php
class Calendarista_TagList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	function __construct( ){
		$this->perPage = 10;
        parent::__construct( array(
            'singular'  => 'tag', 
            'plural'    => 'tag', 
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
	function column_action($item){
		$item = (array)$item;
		$result = array();
		array_push($result, sprintf(
			'<button type="submit" class="button button-primary" name="calendarista_edit" 
				value="%d">
				%s
			</button>'
			, $item['id']
			, __('Edit', 'calendarista')
		));
		array_push($result, sprintf(
			'<button type="submit" class="button button-primary" name="calendarista_delete" 
				value="%d">
				%s
			</button>'
			, $item['id']
			, __('Delete', 'calendarista')
		));
        return join("\n", $result);
	}
	function column_ck($item){
		$item = (array)$item;
		return sprintf('<input type="checkbox" name="tags[]" value="%d">', $item['id']);
	}
	function column_name($item){
		$item = (array)$item;
		return $item['name'];
	}
    function get_columns(){
        $columns = array(
			'ck'=>'<input type="checkbox" name="selectall">'
			, 'name'=>__('Attribute', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
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
		$allowedKeys = array('id', 'name', 'orderIndex', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc';
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$staffMemberAvailabilities = Calendarista_PermissionHelper::staffMemberAvailabilities();
		$tagsRepository = new Calendarista_TagsRepository();
        $result = $tagsRepository->readAll(array(
			'pageIndex'=>$currentPage
			, 'limit'=>$this->perPage
			, 'orderBy'=>$this->orderBy
			, 'order'=>$this->order
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
		return array('wp-list-table', 'calendarista',  'widefat', 'striped', 'calendarista-tag-list');
	}
	public function no_items() {
		esc_html_e('No search attributes found.', 'calendarista');
	}
}
?>