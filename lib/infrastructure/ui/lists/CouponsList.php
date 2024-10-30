<?php
class Calendarista_CouponsList extends Calendarista_List {
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	public $count;
	function __construct( ){
		$this->perPage = 10;
        parent::__construct( array(
            'singular'  => 'coupon', 
            'plural'    => 'coupons', 
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
		$expired = strtotime('now') >= strtotime($item['expirationDate']);
		$result = array();
		array_push($result, sprintf(
			'<button class="button button-primary" name="editCoupon" 
				value="%d">
				%s
			</button>'
			, $item['id']
			, __('Edit', 'calendarista')
		));
		if(!$item['emailedTo'] && !$expired){
			array_push($result, '<br>');
			array_push($result, sprintf(
				'<button class="button button-primary" name="emailCoupon"  
					value="%d">
					%s
				</button>'
				, $item['id']
				, __('Email', 'calendarista')
			));
		}
        return join("\n", $result);
	}
	
	function column_projectId($item){
		return $item['projectId'] === -1 ? __('Any service', 'calendarista') : $item['projectName'];
	}
	function column_expiryDate($item){
		$expired = strtotime('now') >= strtotime($item['expirationDate']);
		$result = sprintf(
			'<p>
				%s%s
			</p>'
			, Calendarista_TimeHelper::formatDate($item['expirationDate'])
			, $expired ? '<br>(' . __('Expired', 'calendarista') . ')' : ''
		);
		
		return $result;
	}
	
	function column_discount($item){
		$result = $item['discountMode'] ? 
				Calendarista_MoneyHelper::toLongCurrency($item['discount']) : 
				Calendarista_MoneyHelper::toDouble($item['discount']) . '%';
		return sprintf('<p><span title="%s">%s</span></p>'
					, __('Discount provided by code', 'calendarista')
					, $result
				);
	}

	function column_code($item){
		return sprintf('<p title="%s">%s</p>'
			, __('The code to use for discount to apply.', 'calendarista')
			, $item['code']
		);
	}
	
	function column_emailedTo($item){
		$expired = strtotime('now') >= strtotime($item['expirationDate']);
		$result = array();
		if($item['emailedTo']){
			array_push($result, sprintf('Emailed to: %s', $item['emailedTo']));
		}
		array_push($result, $expired ? 'Claimed' : 'Unused');
		return implode('<br>', $result);
	}
	
    function get_columns(){
        $columns = array(
			'projectId'=>__('Service', 'calendarista')
			, 'discount'=>__('Discount', 'calendarista')
			, 'expiryDate'=>__('Expiration', 'calendarista')
			, 'emailedTo'=>__('Status', 'calendarista')
			, 'code'=>__('Code', 'calendarista')
			, 'action'=>__('Action', 'calendarista')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'projectId'=>array('projectId', false)
			, 'discount'=>array('discount', false)
            , 'expiryDate'=> array('expirationDate', false)
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
		$allowedKeys = array('id', 'projectId', 'code', 'discount', 'asc', 'desc');
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? sanitize_text_field($_REQUEST[$this->orderByKey]) : 'id';
		if (!in_array($this->orderBy, $allowedKeys, true)) {
			return;
		}
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? sanitize_text_field($_REQUEST[$this->orderKey]) : 'desc'; 
		if (!in_array($this->order, $allowedKeys, true)) {
			return;
		}
		$projectId = isset($_REQUEST['projectId']) ? (int)$_REQUEST['projectId'] : null;
		$availabilityId = isset($_REQUEST['availabilityId']) ? (int)$_REQUEST['availabilityId'] : null;
		$code = isset($_REQUEST['searchByCode']) ? sanitize_text_field($_REQUEST['searchByCode']) : null;
		$couponType = isset($_REQUEST['couponType']) && $_REQUEST['couponType'] != '' ? (int)$_REQUEST['couponType'] : null;
		$discount = isset($_REQUEST['discount']) && $_REQUEST['discount'] ? (double)$_REQUEST['discount'] : null;
		$orderBy = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
		$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'desc';
		
		$couponRepository = new Calendarista_CouponRepository();
        $result = $couponRepository->readAll($currentPage, $this->perPage, $orderBy, $order, $projectId, $code, $couponType, $discount);
		$this->currentPage = $currentPage;
        $this->count = $result->total;
		$this->items = $result->toArray();
		if(!$result->total){
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