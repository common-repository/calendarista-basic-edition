<?php
class Calendarista_SeasonList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	public $projectId;
	function __construct($projectId){
		$this->perPage = 10;
		$this->projectId = $projectId;
        parent::__construct( array(
            'singular'  => 'season', 
            'plural'    => 'seasons', 
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
		$editUrl = esc_url(add_query_arg(array(
			'page'=>'calendarista-index',
			'calendarista-tab'=>3,
			'id'=>$item['id'], 
			'projectId'=>$item['projectId'],
			'availabilityId'=>$item['availabilityId']
		), 'admin.php'));
		array_push($result, sprintf(
			'<a class="button button-primary" name="editSeason" href="%s">
				%s
			</a>'
			, $editUrl
			, __('Edit', 'calendarista')
		));
        return join("\n", $result);
	}
	function column_id($item){
		return sprintf('<input type="checkbox" name="seasons[]" value="%s">', $item['id']);
	}
	function column_projectId($item){
		return sprintf('%s', $item['availabilityName']);
	}
	function column_start($item){
		return sprintf('%s<br>%s', $item['start'], $item['end']);
	}
	function column_cost($item){
		$cost = $item['cost'] ? Calendarista_MoneyHelper::toDouble($item['cost']) : '--';
		if($item['cost']){
			$costMode = $item['costMode'] ? '-' : '+';
			$cost = sprintf('%s%s%s', $costMode, $cost,  $item['percentageBased'] ? '%' : '');
		}
		return $cost;
	}
	function column_bookingDaysMinimum($item){
		return $item['bookingDaysMinimum'] ? $item['bookingDaysMinimum'] : '--';
	}
	function column_bookingDaysMaximum($item){
		return $item['bookingDaysMaximum'] ? $item['bookingDaysMaximum'] : '--';
	}
    function get_columns(){
        $columns = array(
			'id' =>'<input type="checkbox" name="deleteall">'
			, 'projectId'=>__('Avail.', 'calendarista')
			, 'start'=>__('Period', 'calendarista')
			, 'cost'=>__('Cost', 'calendarista')
			, 'bookingDaysMinimum'=>__('Min days', 'calendarista')
			, 'bookingDaysMaximum'=>__('Max days', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
        );
        return $columns;
    }
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'projectId'=>array('projectId', false)
            , 'cost'=> array('cost', false)
			, 'start'=>array('start', false)
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
		$allowedKeys = array('id', 'projectId', 'availabilityId', 'seasonId', 'start', 'end', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc'; 
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$seasonRepository = new Calendarista_SeasonRepository();
        $result = $seasonRepository->readAll($this->projectId, $currentPage, $this->perPage, $this->orderBy, $this->order);
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
		return array('wp-list-table', 'calendarista',  'widefat', 'fixed', 'striped', 'calendarista-season-list');
	}
}
?>