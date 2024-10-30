<?php
class Calendarista_ProjectRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $project_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->project_table_name = $wpdb->prefix . 'calendarista_project';
	}
	
	public function readAll($projectsList = false){
		$where = array();
		$sql = "SELECT p.* FROM $this->project_table_name as p";
		if($projectsList && count($projectsList) > 0){
			array_push($where, 'id IN (' . implode(',', array_map('intval', $projectsList)) . ')');
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY p.orderIndex';
		$result = $this->wpdb->get_results($sql);
		if(is_array($result)){
			$projects = new Calendarista_Projects();
			foreach($result as $r){
				$projects->add(new Calendarista_Project((array)$r));
			}
			return $projects;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT * FROM $this->project_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result){
			$r = $result[0];
			return new Calendarista_Project((array)$r);
		}
		return false;
	}
	public function getProjectByCalendarMode($calendarMode){
		$sql = "SELECT * FROM $this->project_table_name WHERE calendarMode = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $calendarMode));
		if(is_array($result)){
			$projects = array();
			foreach($result as $r){
				array_push($projects, (int)$r->id);
			}
			return $projects;
		}
		return false;
	}
	public function getProjectByWoo($wooProductId){
		$sql = "SELECT * FROM $this->project_table_name WHERE wooProductId = %d ORDER BY orderIndex";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $wooProductId));
		if(is_array($result)){
			$projects = array();
			foreach($result as $r){
				array_push($projects, (int)$r->id);
			}
			return $projects;
		}
		return false;
	}
	public function insert($project){
		$p = $this->parseParams($project);
		$result = $this->wpdb->insert($this->project_table_name, $p['params'], $p['values']);
		  if($result !== false){
			$project->id = $this->wpdb->insert_id;
			$project->orderIndex = $project->id;
			$this->update($project);
			return $project->id;
		 }
		 return $result;
	}
	
	public function update($project){
		$p = $this->parseParams($project);
		$result = $this->wpdb->update($this->project_table_name, $p['params'], array('id'=>$project->id),  $p['values']);
		$project->updateResources();
		return $result;
	}
	private function parseParams($project){
		$params = array();
		$values = array();
		
		$params['paymentsMode'] = $project->paymentsMode;
		array_push($values, '%d');
		
		$params['enableStrongPassword'] = $project->enableStrongPassword;
		array_push($values, '%d');
		
		$params['membershipRequired'] = $project->membershipRequired;
		array_push($values, '%d');
		
		if(isset($project->reminder)){
			$params['reminder'] = $project->reminder;
			array_push($values, '%d');
		}
		
		if(isset($project->calendarMode)){
			$params['calendarMode'] = $project->calendarMode;
			array_push($values, '%d');
		}
		if(isset($project->previewUrl)){
			$params['previewUrl'] = $project->previewUrl;
			array_push($values, '%s');
		}
		if(isset($project->previewImageHeight)){
			$params['previewImageHeight'] = $project->previewImageHeight;
			array_push($values, '%d');
		}
		
		$params['searchPage'] = $project->searchPage;
		array_push($values, '%d');
		
		$params['enableCoupons'] = $project->enableCoupons;
		array_push($values, '%d');
		
		$params['name'] = $this->encode($project->name);
		array_push($values, '%s');
		
		$params['wooProductId'] = $project->wooProductId;
		array_push($values, '%d');
		
		$params['status'] = $project->status;
		array_push($values, '%d');
		
		$params['orderIndex'] = $project->orderIndex;
		array_push($values, '%d');
		
		$params['optionalByService'] = $project->optionalByService;
		array_push($values, '%d');
		
		$params['repeatPageSize'] = $project->repeatPageSize;
		array_push($values, '%d');
		
		$params['thankyouReminder'] = $project->thankyouReminder;
		array_push($values, '%d');

		return array('params'=>$params, 'values'=>$values);
	}
	public function updateSortOrder($id, $orderIndex){
		$result = $this->wpdb->update($this->project_table_name,  array(
			'orderIndex'=>$orderIndex
		), array('id'=>$id), array('%d'));
		return $result;
	}
	public function delete($id){
		$project = new Calendarista_Project(array('id'=>$id));
		$project->deleteResources();
		$mapRepo = new Calendarista_MapRepository();
		$mapRepo->deleteByProject($id);
		$optionalRepo = new Calendarista_OptionalRepository();
		$optionalRepo->deleteAll($id);
		$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
		$optionalGroupRepo->deleteAll($id);
		$formElementRepo = new Calendarista_FormElementRepository();
		$formElementRepo->deleteAll($id);
		$seasonRepo = new Calendarista_SeasonRepository();
		$seasonRepo->deleteAll($id);
		$pricingSchemeRepo = new Calendarista_PricingSchemeRepository();
		$pricingSchemeRepo->deleteAll($id);
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availabilityRepo->deleteAll($id);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$timeslotRepo->deleteByProject($id);
		$orderRepo = new Calendarista_OrderRepository();
		$orderRepo->deleteAll($id);
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->project_table_name WHERE id = %d", $id));
	}
}
?>