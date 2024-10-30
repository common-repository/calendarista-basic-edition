<?php
class Calendarista_AggregateCostList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	public $map;
	public $departurePlaces;
	public $destinationPlaces;
	function __construct($map, $departurePlaces, $destinationPlaces){
		$this->perPage = 20;
		$this->map = $map;
		$this->departurePlaces = $departurePlaces;
		$this->destinationPlaces = $destinationPlaces;
        parent::__construct( array(
            'singular'  => 'cost_aggregate', 
            'plural'    => 'cost_aggregate', 
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
		$name1 = sprintf('calendarista_%s', is_null($item['id']) ? 'create' : 'update');
		$value1 = sprintf('%s_%s', $item['pair'], $item['id']);
		$title1 = __('Update', 'calendarista');
		array_push($result, sprintf(
			'<button type="submit" class="button button-primary" name="%s" 
				value="%s" title="%s">
				<i class="fa fa-floppy fa-lg"></i>
			</button>'
			, $name1
			, $value1
			, $title1
		));
		array_push($result, sprintf(
			'<button type="submit" name="calendarista_delete"
				%s
				value="%s" 
				title="%s"
				class="button-primary">
				<i class="fa fa-times fa-lg"></i>
			</button>
			<input type="hidden" name="updateMany[]" value="%s">'
			, $item['status']
			, $item['id']
			, __('Reset', 'calendarista')
			, sprintf('%s_%s', $item['pair'], $item['id'])
		));
        return join("\n", $result);
	}
	function column_ck1($item){
		return sprintf('<input type="checkbox" name="deleteMany[]" value="%d">', $item['id']);
	}
	function column_ck2($item){
		$name = sprintf('exclude_%s', $item['pair']);
		return sprintf('<input type="checkbox" name="%s" class="updateMany" value="%d" %s>', $name, $item['id'], $item['exclude'] ? 'checked=checked' : '');
	}
	function column_departure($item){
		return $item['departure'];
	}
	function column_destination($item){
		return $item['destination'];
	}
	function column_cost($item){
		$name = sprintf('cost_%s', $item['pair']);
		$cost = !$item['cost'] ? '' : $item['cost'];
		return sprintf('<input
							name="%s" 
							type="text" 
							class="small-text" 
							data-parsley-trigger="change focusout"
							data-parsley-min="0"
							data-parsley-pattern="^\d+(\.\d{1,2})?$"
							placeholder="0.00" 
							value="%s" />', $name, $cost);
	}
	function column_projectName($item){
		return $item['projectName'];
	}
	function column_availabilityName($item){
		return $item['availabilityName'];
	}
	
    function get_columns(){
        $columns = array(
			'ck1'=>sprintf('<input type="checkbox" name="resetall">&nbsp;%s', __('Reset', 'calendarista'))
			, 'ck2'=>sprintf('<input type="checkbox" name="excludeall">&nbsp;%s', __('Exclude', 'calendarista'))
			, 'departure'=>__('Departure', 'calendarista')
			, 'destination'=>__('Destination', 'calendarista')
			, 'cost'=>__('Cost', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'name'=>array('name', false)
			, 'email'=>array('email', false)
            , 'projectName'=> array('serviceName', false)
			, 'availabilityName'=> array('availability', false)
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
		$this->currentPage = $currentPage;
	    $placeAggregateCostRepo = new Calendarista_PlaceAggregateCostRepository();
	    $aggregates = $placeAggregateCostRepo->readAll($this->map->id);
		$result = Calendarista_PlaceAggregateCostRepository::getAggregateList($currentPage, $this->perPage, $this->departurePlaces, $this->destinationPlaces, $aggregates);

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
		return array('wp-list-table', 'calendarista',  'widefat', 'fixed', 'striped', 'calendarista-aggregate-cost-list');
	}
	public function no_items() {
		esc_html_e('No staff members found.', 'calendarista');
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