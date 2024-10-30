<?php
class Calendarista_SearchResultList extends Calendarista_UList {
	public $perPage;
	public $orderBy;
	public $projects;
	protected $pages;
	protected $generalSetting;
	protected $tagsRepo;
	protected $timezone;
	protected $clientTime;
  function __construct($projects = null){
	  $this->projects = $projects;
	  $this->pages = $this->prepareGetPageLink();
		parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
		$repo = new Calendarista_GeneralSettingsRepository();
		$this->generalSetting = $repo->read();
		$this->tagsRepo = new Calendarista_TagsRepository();
		$this->timezone = isset($_REQUEST['timezone']) ? $_REQUEST['timezone'] : null;
		$this->clientTime = isset($_REQUEST['clientTime']) ? $_REQUEST['clientTime'] : null;
    }
    
	function single_row( $item ) {
		echo $this->single_row_columns( $item );
	}
	
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
    
	function column_item($item){
		$thumbnail = null;
		$pageId = $item['pageId'];
		$url = $this->getPageUrl($pageId);
		$tagResult = $this->tagsRepo->readAllByAvailability(array('availabilityId'=>(int)$item['id']));
		if($url){
			$url .= (strpos($url,'?') !== false) ? '&' : '?';
			$url .= sprintf('cal-service-id=%s&cal-availability-id=%s'
							, $item['serviceId']
							, $item['availabilityId']);
		}
		if($item['start'] && $url){
			$url .= sprintf('&cal-start=%s&cal-end=%s', $item['start'], $item['end']);
			if($item['startTime']){
				$url .= sprintf('&cal-start-time=%s', $item['startTime']);
			}
			if($item['endTime']){
				$url .= sprintf('&cal-end-time=%s', $item['endTime']);
			}
		}
		if($item['thumbnail']){
			$thumbnail = sprintf('<img src="%s" alt="...">', $item['thumbnail']);
			if($url){
				$thumbnail = sprintf('<a href="%s" target="_blank">%s</a>', $url, $thumbnail);
			}
		}
		//SEARCH_BUTTON_SELECT
		$title = $url ? sprintf('<a href="%s" class="calendarista-media-link" target="_blank">%s</a>', $url, $item['title']) : $item['title'];
		$goToLink = $url ? sprintf('<a class="btn btn-primary calendarista-typography--button" href="%s" target="_blank">%s</a>', $url, $this->generalSetting->searchFilterSelectButtonLabel) : '';
		$description = null;
		if(!$item['active']){
			$title = $item['title'];
			$goToLink = null;
			$description = sprintf('<div class="mb-1"><span class="badge text-bg-danger">%s</span></div>', $this->generalSetting->searchFilterSoldOutLabel);
		}else if(!$item['status']){
			$description = sprintf('<div class="mb-1"><span class="badge text-bg-warning">%s</span></div>', $this->generalSetting->searchFilterAlternateDateLabel);
		}
		if($item['description']){
			$description .= '<div class="mb-1">' . $item['description'] . '</div>';
		}
		if($tagResult){
			$tags = $tagResult['items'];
			if(count($tags) > 0){
				$description .= '<div class="mb-1">';
				foreach($tags as $tag){
					$description .= sprintf('<span class="badge rounded-pill text-bg-secondary">%s</span>&nbsp;', $tag->name);
				}
				$description .= '</div>';
			}
		}
		$result = '<div class="container">';
		$result .= 		'<div class="row align-items-center">';
		$result .= 			'<div class="col">';
		$result .= 				'<div class="container">';
		$result .= 					'<div class="row align-items-center">';
		if($thumbnail){
			$result .= 					'<div class="col-md-auto">' . $thumbnail . '</div>';
		}
		$result .= 							'<div class="col-md-auto h-100">';
		$result .= 								'<h5 class="mb-1">' . $title . '</h5>';
		$result .= 								'<div class="mb-1">' . $item['subTitle'] . '</div>';
		$result .= $description;
		$result .= 						'</div>';//col h-100
		$result .= 					'</div>';//row
		$result .= 				'</div>';//container
		$result .= 			'</div>';//col-10
		$result .= 			'<div class="col-md-auto">';
		$result .= 				'<div class="container">';
		$result .= 					'<div class="row">';
		$result .= 						'<div class="col">';
		$result .= 							'<div class="align-items-center h-100 w-100">';
		$result .=								'<div class="align-self-center">';
		$result .= 									$goToLink;
		$result .=								'</div>';//align-self-center
		$result .=							'</div>';//align-items-center h-100 w-100
		$result .=						'</div>';//col
		$result .=					'</div>';//row
		$result .=				'</div>';//container
		$result .= 			'</div>';//col-2
		$result .= 		'</div>';//row
		$result .= 	'</div>';//container
		return $result;
	}
	
	
    function get_columns(){
        $columns = array(
			'item'=>''
        );
		
        return $columns;
    }
	public function timeslotInStock($selectedStartTime, $selectedEndTime, $timeslots){
		if(!$timeslots || count($timeslots) === 0){
			return false;
		}
		$flag = false;
		foreach($timeslots as $timeslot){
			$currentTime = strtotime($timeslot->timeslot);
			if($selectedEndTime && $selectedStartTime !== $selectedEndTime){
				if(date('H:i', $currentTime) === $selectedStartTime){
					//matching slot found
					$flag = true;
				}
				if((date('H:i', $currentTime) === $selectedStartTime && $timeslot->outOfStock) || 
					($currentTime >= strtotime($selectedStartTime) && ($currentTime < strtotime($selectedEndTime) && $timeslot->outOfStock))){
					return false;
				}
			}else{
				if(date('H:i', $currentTime) === $selectedStartTime){
					return !$timeslot->outOfStock;
				}
			}
		}
		return $flag;
	}
    public function timeSlotAvailable($selectedStartDate, $selectedEndDate, $selectedStartTime, $selectedEndTime, $availability){
		$availabilityHelper = new Calendarista_AvailabilityHelper(array(
			'projectId'=>$availability->projectId
			, 'availabilityId'=>$availability->id
			, 'clientTime'=>$this->clientTime
			, 'timezone'=>$this->timezone
		));
		$bookedAvailabilities = $availabilityHelper->getCurrentMonthAvailabilities($selectedStartDate);
		$startTimeslots = $availabilityHelper->timeslotHelper->getTimeslots($selectedStartDate, $bookedAvailabilities, 0/*slotType*/, -1/*appointment*/);
		$result = $this->timeslotInStock($selectedStartTime, $selectedEndTime, $startTimeslots);
		if(!$result){
			return false;
		}
		$endTimeslots = null;
		if($selectedEndDate && ($selectedStartDate !== $selectedEndDate && $selectedEndTime)){
			$bookedAvailabilities = $availabilityHelper->getCurrentMonthAvailabilities($selectedEndDate);
			$endTimeslots = $availabilityHelper->timeslotHelper->getTimeslots($selectedEndDate, $bookedAvailabilities, 1/*slotType*/, -1/*appointment*/);
		}else if($selectedEndTime && $selectedStartTime !== $selectedEndTime){
			$endTimeslots = $availabilityHelper->timeslotHelper->getTimeslots($selectedStartDate, $bookedAvailabilities, 1/*slotType*/, -1/*appointment*/);
		}
		if($endTimeslots){
			$result = $this->timeslotInStock($selectedEndTime, null, $endTimeslots);
			if(!$result){
				return false;
			}
		}
		return true;
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
        $per_page = 10;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $currentPage = $this->get_pagenum() - 1;
		if($currentPage){
			$currentPage = $currentPage * $per_page;
		}
		$projectId = isset($_REQUEST['projectId']) ? (int)$_REQUEST['projectId'] : null;
		$projectList = (isset($_REQUEST['projectList']) && $_REQUEST['projectList']) ? explode(',', sanitize_text_field($_REQUEST['projectList'])) : null;
		$tags = (isset($_REQUEST['tags']) && $_REQUEST['tags']) ? explode(',', $_REQUEST['tags']) : null;
		$_startDate = isset($_REQUEST['fromDate']) ? sanitize_text_field($_REQUEST['fromDate']) : null;
		$_endDate = isset($_REQUEST['toDate']) ? sanitize_text_field($_REQUEST['toDate']) : null;
		$startTime = isset($_REQUEST['fromTime']) ? sanitize_text_field($_REQUEST['fromTime']) : null;
		$endTime = isset($_REQUEST['toTime']) ? sanitize_text_field($_REQUEST['toTime']) : null;
		$startDate = $_startDate;
		$endDate = $_endDate;
		if($projectId){
			$this->projects = array($projectId);
		}
		if($projectList && count($projectList) > 0){
			$this->projects = $projectList;
		}
		if($startDate && $startTime){
			$startDate .= ' ' . $startTime;
		}
		if($endDate && $endTime){
			$endDate .= ' ' . $endTime;
		}
		if($startDate && (!$endDate && $endTime)){
			$endDate = $_startDate . ' ' . $endTime;
		}
		if($endDate && !$startDate){
			$startDate = $endDate;
		}
		$availabilityRepository = new Calendarista_AvailabilityRepository();
        $result = $availabilityRepository->search(array(
			'pageIndex'=>$currentPage
			, 'limit'=>$per_page
			, 'startDate'=>$startDate
			, 'endDate'=>$endDate
			, 'services'=>$this->projects
			, 'tags'=>$tags
		));
		if(!$result){
			$this->items = array();
			$this->set_pagination_args(array(
				'total_items' => 0,
				'per_page'    => $per_page,
				'total_pages' => $total_pages
			));
			return;
		}
		$total = $result['total'];
		$items = array();
		//we need these two fields in availability
		//1. enable image only for search
		//2. page url where the service short-code is used
		if(!$endDate){
			$endDate = $startDate;
		}
		$sd = new Calendarista_DateTime($startDate);
		$sd->setTime(0,0);
		$ed = new Calendarista_DateTime($endDate);
		$ed->setTime(0,0);
		$diff = $sd->diff($ed);
		
		foreach($result['resultset'] as $availability){
			$active = Calendarista_AvailabilityHelper::active($availability);
			$resultset = Calendarista_AvailabilityHelper::checkAvailability($availability, $startDate, $endDate, true/*search*/);
			if($availability->minimumNotice || $availability->maximumNotice){
				$minimumNotice = new Calendarista_DateTime();
				$minimumNotice->setTime(0,0);
				
				$maximumNotice = new Calendarista_DateTime();
				$maximumNotice->setTime(0,0);
			
				if($availability->minimumNotice){
					$minimumNotice->modify('+' . $availability->minimumNotice . ' days');
				}
				if($availability->maximumNotice){
					$maximumNotice->modify('+' . $availability->maximumNotice . ' days');
				}
				if(($availability->minimumNotice && $sd < $minimumNotice) || 
						($availability->maximumNotice && $ed > $maximumNotice)){
					--$total;
					continue;
				}
			}
			if($availability->bookingDaysMinimum > 1 && $diff->days < $availability->bookingDaysMinimum){
				--$total;
				continue;
			}
			if($availability->bookingDaysMaximum > 0 && $diff->days > $availability->bookingDaysMaximum){
				--$total;
				continue;
			}
			$flag = true;
			$supportsTimeslots = false;
			if($startTime || $endTime){
				$supportsTimeslots = in_array((int)$availability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
				if($supportsTimeslots){
					$flag = $this->timeSlotAvailable($startDate, $endDate, $startTime, $endTime, $availability);
				}
			}
			if(!in_array((int)$availability->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
				$holidays = Calendarista_RepeatHelper::getHolidays(strtotime($startDate), strtotime($endDate), $availability->id);
				if(count($holidays) > 0 && (in_array($startDate, $holidays) || in_array($endDate, $holidays))){
					$flag = false;
				}
			}
			if(!$flag){
				$resultset = false;
			}
			$status = !is_array($resultset) || !$flag ? false : true;
			if(!$status && !$this->generalSetting->searchIncludeAlternateDates){
				$active = false;
			}
			if(!$active && !$this->generalSetting->searchIncludeSoldoutDates){
				continue;
			}
			array_push($items, array(
				'id'=>$availability->id
				, 'title'=>is_array($resultset) ? $resultset['serviceName'] : $resultset
				, 'subTitle'=>$availability->name
				, 'description'=>$availability->description
				, 'thumbnail'=>$availability->searchThumbnailUrl
				, 'pageId'=>$availability->searchPage ? $availability->searchPage : null
				, 'serviceId'=>$availability->projectId
				, 'availabilityId'=>$availability->id
				, 'active'=>$active
				, 'status'=>$status
				, 'start'=>is_array($resultset) ? $resultset['startDate'] : null
				, 'startTime'=>is_array($resultset) && $supportsTimeslots ? $startTime : null
				, 'end'=>is_array($resultset) ? $resultset['endDate'] : null 
				, 'endTime'=>is_array($resultset) && $supportsTimeslots ? $endTime : null
			));
		}
        $total_pages = ceil($total / $per_page);
        $this->items = $items;
        $this->set_pagination_args(array(
            'total_items' => $total,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ));
    }
	protected function getPageUrl($id){
		if(!$id){
			return null;
		}
		return get_page_link($id);
	}
	protected function prepareGetPageLink(){
		Calendarista_PermissionHelper::wpIncludes();
		if (!function_exists('get_page_permastruct')){
			require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
			$GLOBALS['wp_rewrite'] = new WP_Rewrite();
		}
		if (!function_exists('get_page_link')){
			require_once ABSPATH . WPINC . '/link-template.php ';
		}
	}
	function get_table_classes() {
		return array('calendarista-search-list list-group');
	}
	public function no_items() {
		esc_html_e('No items found.', 'calendarista');
	}
}
?>