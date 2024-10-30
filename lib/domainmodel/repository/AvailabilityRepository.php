<?php
class Calendarista_AvailabilityRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $availability_table_name;
	private $availability_booked_table_name;
	private $project_table_name;
	private $tags_availability_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->availability_table_name = $wpdb->prefix . 'calendarista_availability';
		$this->availability_booked_table_name = $wpdb->prefix . 'calendarista_availability_booked';
		$this->project_table_name = $wpdb->prefix . 'calendarista_project';
		$this->tags_availability_table_name = $wpdb->prefix . 'calendarista_tags_availability';
	}

	public function readAll($projectId, $availabilities = false){
		$sql = "SELECT * FROM   $this->availability_table_name";
		$where = array();
		$params = array($projectId);
		array_push($where, 'projectId = %d');
		if(is_array($availabilities)){
			array_push($where, 'id IN (' . implode(',', array_map('intval', $availabilities)) . ')');
		}
		$sql .= ' WHERE ' . implode(' AND ', $where);
		$sql .= ' ORDER BY orderIndex';
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
		if( is_array( $result )){
			$availabilities = new Calendarista_Availabilities();
			foreach($result as $r){
				$availabilities->add(new Calendarista_Availability((array)$r));
			}
			return $availabilities;
		}
		return false;
	}
	
	public function readAllByService($services = array()){
		$sql = "SELECT * FROM   $this->availability_table_name";
		$where = array('projectId IN (' . implode(',', array_map('intval', $services)) . ')');
		$sql .= ' WHERE ' . implode(' AND ', $where);
		$sql .= ' ORDER BY orderIndex';
		$result = $this->wpdb->get_results($sql);
		if( is_array( $result )){
			$availabilities = new Calendarista_Availabilities();
			foreach($result as $r){
				$availabilities->add(new Calendarista_Availability((array)$r));
			}
			return $availabilities;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT * FROM   $this->availability_table_name WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if( $result ){
			$r = $result[0];
			return new Calendarista_Availability((array)$r);
		}
		return false;
	}
	public function search($args){
		$pageIndex = isset($args['pageIndex']) ? $args['pageIndex'] : -1;
		$limit = isset($args['limit']) ? (int)$args['limit'] : 5;
		$services = isset($args['services']) ? $args['services'] : null;
		$availabilities = isset($args['availabilities']) ? $args['availabilities'] : null;
		$startDate = isset($args['startDate']) ? $args['startDate'] : null;
		$tags = isset($args['tags']) ? $args['tags'] : null;
		//no need to check end date, as this is done in Calendarista_AvailabilityHelper::checkAvailability 
		//this allows us to propose other bookable dates prior to end date
		$endDate = null;//isset($args['endDate']) ? $args['endDate'] : null;
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		$sql = "SELECT a.*, p.calendarMode, p.searchPage FROM $this->availability_table_name as a INNER JOIN $this->project_table_name as p ON a.projectId = p.id";
		if($tags && count($tags) > 0){
			$sql = "SELECT DISTINCT a.*, p.calendarMode, p.searchPage FROM $this->availability_table_name as a INNER JOIN $this->project_table_name as p ON a.projectId = p.id LEFT JOIN $this->tags_availability_table_name as ta ON a.id = ta.availabilityId";
		}
		$where = array();
		$params = array();
		if($startDate){
			array_push($where, 'DATE(a.availableDate) <= CONVERT(%s, DATE)');
			array_push($params, $startDate);
		}
		if($endDate){
			array_push($where, "((a.endDate IS NULL OR a.endDate = '') OR CONVERT(%s, DATE)  <= DATE(a.endDate))");
			array_push($params, $endDate);
		}
		if($services && count($services) > 0){
			array_push($where, 'a.projectId IN (' . implode(',', array_map('intval', $services)) . ')');
		}
		if($availabilities && count($availabilities) > 0){
			array_push($where, 'a.id IN (' . implode(',', array_map('intval', $availabilities)) . ')');
		}
		if($tags && count($tags) > 0){
			array_push($where, 'ta.tagId IN (' . implode(',', array_map('intval', $tags)) . ')');
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY p.orderIndex, a.orderIndex';
		if($pageIndex !== null && $pageIndex !== -1){
			$sql .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
		if( is_array( $result )){
			$sql = "SELECT count(a.id) as total FROM $this->availability_table_name as a INNER JOIN $this->project_table_name as p ON a.projectId = p.id";
			if($tags && count($tags) > 0){
				$sql = "SELECT count(DISTINCT a.id) as total FROM $this->availability_table_name as a INNER JOIN $this->project_table_name as p ON a.projectId = p.id LEFT JOIN $this->tags_availability_table_name as ta ON a.id = ta.availabilityId";
			}
			if(count($where) > 0){
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}
			$records = $this->wpdb->get_results(count($params) > 0 ? $this->wpdb->prepare($sql, $params) : $sql);
			$availabilities = new Calendarista_Availabilities();
			foreach($result as $r){
				$availabilities->add(new Calendarista_Availability((array)$r));
			}
			return array('resultset'=>$availabilities, 'total'=>$records ? (int)$records[0]->total : 0);
		}
		return false;
	}
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->availability_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	public function insert($availability){
		$p = $this->parseParams($availability);
		$result = $this->wpdb->insert($this->availability_table_name, $p['params'], $p['values']);
		 if($result !== false){
			$availability->id = $this->wpdb->insert_id;
			$availability->orderIndex = $availability->id;
			$this->update($availability);
			return $availability->id;
		 }
		 return $result;
	}
	
	public function update($availability){
		$availability->updateResources();
		$p = $this->parseParams($availability);
		$result = $this->wpdb->update($this->availability_table_name, $p['params'], array('id'=>$availability->id), $p['values']);
		//color needs to be updated in bookedAvailability table, 
		//unconventional approach to save us an extra unnecessary join statement later
		$this->updateColor($availability);
		return $result;
	}
	public function updateColor($availability){
		$result = $this->wpdb->update($this->availability_booked_table_name, array('color'=>$availability->color), array('availabilityId'=>$availability->id), array('%s'));
		return $result;
	}
	public function delete($id){
		$this->deleteResources($id);
		$seasonRepo = new Calendarista_SeasonRepository();
		$seasonRepo->deleteByAvailabilityId($id);
		$pricingSchemeRepo = new Calendarista_PricingSchemeRepository();
		$pricingSchemeRepo->deleteByAvailabilityId($id);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$timeslotRepo->deleteAllByAvailability($id);
		$orderRepo = new Calendarista_OrderRepository();
		$orderRepo->deleteAllByAvailability($id);
		$bookedRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedRepo->deleteAllByAvailability($id);
		$availabilityDayRepo = new Calendarista_AvailabilityDayRepository();
		$availabilityDayRepo->deleteByAvailabilityId($id);
		$tagRepo = new Calendarista_TagsRepository();
		$tagRepo->deleteFromTagByAvailabilityId($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->availability_table_name WHERE id = %d", $id) );
	}
	
	public function deleteAll($projectId){
		$availabilityDayRepo = new Calendarista_AvailabilityDayRepository();
		$availabilityDayRepo->deleteByProjectId($projectId);
		$tagRepo = new Calendarista_TagsRepository();
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$timeslotRepo->deleteByProject($projectId);
		$availabilities = $this->readAll($projectId);
		if($availabilities->count() > 0){
			foreach($availabilities as $availability){
				$this->deleteResources($availability->id);
				$tagRepo->deleteFromTagByAvailabilityId($availability->id);
			}
		}
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->availability_table_name WHERE projectId = %d", $projectId) );
	}
	
	protected function parseParams($availability){
		$params = array();
		$values = array();

		$params['projectId'] = $availability->projectId;
		array_push($values, '%d');

		$params['name'] = $availability->name;
		array_push($values, '%s');

		$params['cost'] = $availability->cost;
		array_push($values, '%f');
		
		$params['customChargeDays'] = $availability->customChargeDays;
		array_push($values, '%d');
		
		$params['customCharge'] = $availability->customCharge;
		array_push($values, '%f');
		
		$params['customChargeMode'] = $availability->customChargeMode;
		array_push($values, '%d');
		
		$params['deposit'] = $availability->deposit;
		array_push($values, '%f');
		
		$params['depositMode'] = $availability->depositMode;
		array_push($values, '%d');
		
		$params['returnOptional'] = $availability->returnOptional;
		array_push($values, '%d');
		
		$params['returnCost'] = $availability->returnCost;
		array_push($values, '%f');

		$params['seats'] = $availability->seats;
		array_push($values, '%d');
		
		$params['seatsMaximum'] = $availability->seatsMaximum;
		array_push($values, '%d');
		
		$params['seatsMinimum'] = $availability->seatsMinimum;
		array_push($values, '%d');

		$params['selectableSeats'] = $availability->selectableSeats;
		array_push($values, '%d');

		$params['daysInPackage'] = $availability->daysInPackage;
		array_push($values, '%d');
		
		$params['fullDay'] = $availability->fullDay;
		array_push($values, '%d');

		$params['hasRepeat'] = $availability->hasRepeat;
		array_push($values, '%d');

		$params['repeatFrequency'] = $availability->repeatFrequency;
		array_push($values, '%d');

		$params['repeatInterval'] = $availability->repeatInterval;
		array_push($values, '%d');

		$params['terminateAfterOccurrence'] = $availability->terminateAfterOccurrence;
		array_push($values, '%d');

		$params['terminateMode'] = $availability->terminateMode;
		array_push($values, '%d');

		$params['repeatWeekdayList'] = trim(implode(',', $availability->repeatWeekdayList));
		array_push($values, '%s');
		
		$params['checkinWeekdayList'] = trim(implode(',', $availability->checkinWeekdayList));
		array_push($values, '%s');
		
		$params['checkoutWeekdayList'] = trim(implode(',', $availability->checkoutWeekdayList));
		array_push($values, '%s');
		
		$params['color'] = $availability->color;
		array_push($values, '%s');
		
		$params['timezone'] = $availability->timezone;
		array_push($values, '%s');

		$params['imageUrl'] = $availability->imageUrl;
		array_push($values, '%s');
		
		$params['searchThumbnailUrl'] = $availability->searchThumbnailUrl;
		array_push($values, '%s');
		
		$params['availableDate'] = $availability->availableDate ? $availability->availableDate->format(CALENDARISTA_FULL_DATEFORMAT) : null;
		array_push($values, '%s');


		$params['endDate'] = $availability->endDate ? $availability->endDate->format(CALENDARISTA_FULL_DATEFORMAT) : null;
		array_push($values, '%s');

		if($availability->regionAddress !== null){
			$params['regionAddress'] = $availability->regionAddress;
			array_push($values, '%s');
		}
		if($availability->regionLat !== null){
			$params['regionLat'] = $availability->regionLat;
			array_push($values, '%s');
		}
		if($availability->regionLng !== null){
			$params['regionLng'] = $availability->regionLng;
			array_push($values, '%s');
		}
		if($availability->regionMarkerIconUrl !== null){
			$params['regionMarkerIconUrl'] = $availability->regionMarkerIconUrl;
			array_push($values, '%s');
		}
		if($availability->regionMarkerIconWidth !== null){
			$params['regionMarkerIconWidth'] = $availability->regionMarkerIconWidth;
			array_push($values, '%d');
		}
		if($availability->regionMarkerIconHeight !== null){
			$params['regionMarkerIconHeight'] = $availability->regionMarkerIconHeight;
			array_push($values, '%d');
		}
		if($availability->regionInfoWindowIcon !== null){
			$params['regionInfoWindowIcon'] = $availability->regionInfoWindowIcon;
			array_push($values, '%s');
		}
		if($availability->regionInfoWindowDescription !== null){
			$params['regionInfoWindowDescription'] = $availability->regionInfoWindowDescription;
			array_push($values, '%s');
		}
		if($availability->styledMaps !== null){
			$params['styledMaps'] = $availability->styledMaps;
			array_push($values, '%s');
		}
		if($availability->showMapMarker !== null){
			$params['showMapMarker'] = $availability->showMapMarker;
			array_push($values, '%d');
		}
		$params['maxTimeslots'] = $availability->maxTimeslots;
		array_push($values, '%d');
		
		$params['minimumTimeslotCharge'] = $availability->minimumTimeslotCharge;
		array_push($values, '%f');
		
		$params['bookingDaysMinimum'] = $availability->bookingDaysMinimum;
		array_push($values, '%d');
		
		$params['bookingDaysMaximum'] = $availability->bookingDaysMaximum;
		array_push($values, '%d');
		
		$params['maximumNotice'] = $availability->maximumNotice;
		array_push($values, '%d');
		
		$params['minimumNotice'] = $availability->minimumNotice;
		array_push($values, '%d');
		
		$params['turnoverBefore'] = $availability->turnoverBefore;
		array_push($values, '%d');
		
		$params['turnoverAfter'] = $availability->turnoverAfter;
		array_push($values, '%d');
		
		$params['turnoverBeforeMin'] = $availability->turnoverBeforeMin;
		array_push($values, '%d');
		
		$params['turnoverAfterMin'] = $availability->turnoverAfterMin;
		array_push($values, '%d');
		
		$params['syncList'] = trim(implode(',', $availability->syncList));
		array_push($values, '%s');
		
		$params['description'] = $availability->description;
		array_push($values, '%s');
		
		$params['timeMode'] = $availability->timeMode;
		array_push($values, '%d');
		
		$params['displayRemainingSeats'] = $availability->displayRemainingSeats;
		array_push($values, '%d');
		
		$params['displayRemainingSeatsMessage'] = $availability->displayRemainingSeatsMessage;
		array_push($values, '%d');
		
		$params['timeDisplayMode'] = $availability->timeDisplayMode;
		array_push($values, '%d');
		
		$params['dayCountMode'] = $availability->dayCountMode;
		array_push($values, '%d');
		
		$params['appendPackagePeriodToName'] = $availability->appendPackagePeriodToName;
		array_push($values, '%d');
		
		$params['minimumNoticeMinutes'] = $availability->minimumNoticeMinutes;
		array_push($values, '%d');
		
		$params['orderIndex'] = $availability->orderIndex;
		array_push($values, '%d');
		
		$params['extendTimeRangeNextDay'] = $availability->extendTimeRangeNextDay;
		array_push($values, '%d');
		
		$params['minTime'] = $availability->minTime;
		array_push($values, '%d');
		
		$params['maxTime'] = $availability->maxTime;
		array_push($values, '%d');
		
		$params['maxDailyRepeatFrequency'] = $availability->maxDailyRepeatFrequency;
		array_push($values, '%d');

		$params['maxWeeklyRepeatFrequency'] = $availability->maxWeeklyRepeatFrequency;
		array_push($values, '%d');

		$params['maxMonthlyRepeatFrequency'] = $availability->maxMonthlyRepeatFrequency;
		array_push($values, '%d');

		$params['maxYearlyRepeatFrequency'] = $availability->maxYearlyRepeatFrequency;
		array_push($values, '%d');
		
		$params['maxRepeatOccurrence'] = $availability->maxRepeatOccurrence;
		array_push($values, '%d');
		
		$params['returnSameDay'] = $availability->returnSameDay;
		array_push($values, '%d');
		
		$params['maxRepeatFrequency'] = $availability->maxRepeatFrequency;
		array_push($values, '%d');
		
		$params['guestNameRequired'] = $availability->guestNameRequired;
		array_push($values, '%d');
		
		$params['displayDateSelectionReq'] = $availability->displayDateSelectionReq;
		array_push($values, '%d');
		
		$params['enableFullAmountOrDeposit'] = $availability->enableFullAmountOrDeposit;
		array_push($values, '%d');
		
		$params['fullAmountDiscount'] = $availability->fullAmountDiscount;
		array_push($values, '%f');
		
		$params['instructions'] = $availability->instructions;
		array_push($values, '%s');
		
		$params['hideMapDisplay'] = $availability->hideMapDisplay;
		array_push($values, '%d');
		
		return array('params'=>$params, 'values'=>$values);
	}
	public function deleteResources($id){
		$availability = $this->read($id);
		if($availability){
			$availability->deleteResources();
		}
		return $availability;
	}
}
?>