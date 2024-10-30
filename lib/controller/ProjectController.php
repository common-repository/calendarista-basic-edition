<?php
class Calendarista_ProjectController extends Calendarista_BaseController{
	private $repo;
	private $project;
	public function __construct($project, $newProjectCallback, $sortOrderCallback, $beforeDeleteCallback, $updateCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'project')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->project = $project;
		$this->repo = new Calendarista_ProjectRepository();
		parent::__construct(null, $updateCallback);
		if (array_key_exists('calendarista_new', $_POST)){
			$this->newProject($newProjectCallback);
		} else if (array_key_exists('calendarista_sortorder', $_POST)){
			$this->sortOrder($sortOrderCallback);
		} else if (array_key_exists('calendarista_beforedelete', $_POST)){
			$this->beforeDelete($beforeDeleteCallback);
		}
	}
	public function newProject($callback){
		$this->executeCallback($callback, array($this->project));
	}
	public static function redirect(){
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		//if we want to redirect then we need to do it early in the wordpress life cycle
		//the Redirect controller is going to call us through this method.
		if (array_key_exists('calendarista_create', $_POST)){
			self::createProject();
		}else if(array_key_exists('calendarista_duplicate', $_POST)){
			self::duplicateProject();
		}else if(array_key_exists('calendarista_delete', $_POST)){
			self::deleteProject();
		}
	}
	protected static function createProject(){
		$repo = new Calendarista_ProjectRepository();
		$project = new Calendarista_Project(array(
			'reminder'=>isset($_POST['reminder']) ? (int)$_POST['reminder'] : null,
			'thankyouReminder'=>isset($_POST['thankyouReminder']) ? (int)$_POST['thankyouReminder'] : null,
			'paymentsMode'=>isset($_POST['paymentsMode']) ? (int)$_POST['paymentsMode'] : null,
			'enableStrongPassword'=>isset($_POST['enableStrongPassword']) ? (bool)$_POST['enableStrongPassword'] : null,
			'membershipRequired'=>isset($_POST['membershipRequired']) ? (bool)$_POST['membershipRequired'] : null,
			'calendarMode'=>isset($_POST['calendarMode']) ? (int)$_POST['calendarMode'] : null,
			'enableCoupons'=>isset($_POST['enableCoupons']) ? (bool)$_POST['enableCoupons'] : null,
			'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
			'wooProductId'=>isset($_POST['wooProductId']) ? (int)$_POST['wooProductId'] : null,
			'optionalByService'=>isset($_POST['optionalByService']) ? (bool)$_POST['optionalByService'] : null,
			'currentDayColor'=>isset($_POST['currentDayColor']) ? sanitize_text_field($_POST['currentDayColor']) : null,
			'unavailableColor'=>isset($_POST['unavailableColor']) ? sanitize_text_field($_POST['unavailableColor']) : null,
			'availableColor'=>isset($_POST['availableColor']) ? sanitize_text_field($_POST['availableColor']) : null,
			'selectedDayColor'=>isset($_POST['selectedDayColor']) ? sanitize_text_field($_POST['selectedDayColor']) : null,
			'halfDayRangeColor'=>isset($_POST['halfDayRangeColor']) ? sanitize_text_field($_POST['halfDayRangeColor']) : null,
			'selectedDayRangeColor'=>isset($_POST['selectedDayRangeColor']) ? sanitize_text_field($_POST['selectedDayRangeColor']) : null,
			'rangeUnavailableDayColor'=>isset($_POST['rangeUnavailableDayColor']) ? sanitize_text_field($_POST['rangeUnavailableDayColor']) : null,
			'previewUrl'=>isset($_POST['previewUrl']) ? sanitize_url($_POST['previewUrl']) : null,
			'previewImageHeight'=>isset($_POST['previewImageHeight']) ? (int)$_POST['previewImageHeight'] : null,
			'searchPage'=>isset($_POST['searchPage']) ? (int)$_POST['searchPage'] : null,
			'status'=>isset($_POST['status']) ? (int)$_POST['status'] : null,
			'orderIndex'=>isset($_POST['orderIndex']) ? (int)$_POST['orderIndex'] : null,
			'repeatPageSize'=>isset($_POST['repeatPageSize']) ? (int)$_POST['repeatPageSize'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null
		));
		$newProjectId = $repo->insert($project);
		if($newProjectId !== false){
			$url = admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=1&newservice=true&projectId=' . $newProjectId;
			if (wp_redirect($url)) {
				exit;
			}
		}
	}
	public static function duplicateProject(){
		$repo = new Calendarista_ProjectRepository();
		$projectId = isset($_POST['id']) ? (int)$_POST['id'] : null;
		$name = isset($_POST['duplicateProjectName']) ? $_POST['duplicateProjectName'] : null;
		//create project duplicate
		$project = $repo->read($projectId);
		$project->name = $name;
		$newProjectId = $repo->insert($project);
		//duplicate availabilities and timeslots
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availabilities = $availabilityRepo->readAll($projectId);
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$seasonRepo = new Calendarista_SeasonRepository();
		$pricingSchemeRepo = new Calendarista_PricingSchemeRepository();
		foreach($availabilities as $availability){
			$availability->projectId = $newProjectId;
			$availability->syncList = array();
			$oldAvailabilityId = $availability->id;
			$newAvailabilityId = $availabilityRepo->insert($availability);
			//duplicate timeslots
			$timeslots = $timeslotRepo->readAllByAvailability($oldAvailabilityId);
			foreach($timeslots as $timeslot){
				$timeslot->projectId = $newProjectId;
				$timeslot->availabilityId = $newAvailabilityId;
				$timeslotRepo->insert($timeslot);
			}
			$seasons = $seasonRepo->readByAvailability($oldAvailabilityId);
			$pricingSchemes = $pricingSchemeRepo->readByAvailabilityId($oldAvailabilityId);
			foreach($seasons as $season){
				$season['projectId'] = $newProjectId;
				$season['availabilityId'] = $newAvailabilityId;
				$season['projectName'] = $name;
				$oldSeasonId = $season['id'];
				$newSeasonId = $seasonRepo->insert($season);
				foreach($pricingSchemes as $pricingScheme){
					//if pricing scheme is season based...
					if($pricingScheme['seasonId'] == $oldSeasonId){
						$pricingScheme['projectId'] = $newProjectId;
						$pricingScheme['availabilityId'] = $newAvailabilityId;
						$pricingScheme['seasonId'] = $newSeasonId;
						$pricingSchemeRepo->insert($pricingScheme);
					}
				}
			}
			//if pricing scheme is not season based...
			foreach($pricingSchemes as $pricingScheme){
				if(!$pricingScheme['seasonId']){
					$pricingScheme['projectId'] = $newProjectId;
					$pricingScheme['availabilityId'] = $newAvailabilityId;
					$pricingSchemeRepo->insert($pricingScheme);
				}
			}
			
			$dynamicFieldRepo = new Calendarista_DynamicFieldRepository();
			$dynamicFields = $dynamicFieldRepo->readByAvailabilityId(array('availabilityId'=>$oldAvailabilityId));
			if($dynamicFields && count($dynamicFields['resultset']) > 0){
				foreach($dynamicFields['resultset'] as $dynamicField){
					$dynamicField->projectId = $newProjectId;
					$dynamicField->availabilityId = $newAvailabilityId;
					$dynamicFieldRepo->insert($dynamicField);
				}
			}
		}
		//duplicate optional groups
		$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
		$optionalGroups = $optionalGroupRepo->readAll($projectId);
		$optionalRepo = new Calendarista_OptionalRepository();
		foreach($optionalGroups as $group){
			$optionals = $optionalRepo->readAllByGroup($group->id);
			$group->projectId = $newProjectId;
			$newGroupId = $optionalGroupRepo->insert($group);
			foreach($optionals as $optional){
				$optional->projectId = $newProjectId;
				$optional->groupId = $newGroupId;
				$optionalRepo->insert($optional);
			}
		}
		//duplicate custom form fields
		$formElementRepo = new Calendarista_FormElementRepository();
		$formElements = $formElementRepo->readAll($projectId);
		foreach($formElements as $formElement){
			$formElement->projectId = $newProjectId;
			$formElementRepo->insert($formElement);
		}
		//duplicate style
		$styleRepo = new Calendarista_StyleRepository();
		$style = $styleRepo->readByProject($projectId);
		if($style->id !== -1){
			$style->projectId = $newProjectId;
			$styleRepo->insert($style);
		}
		$stringResourcesRepo = new Calendarista_StringResourcesRepository();
		$stringResource = $stringResourcesRepo->readByProject($projectId);
		if($stringResource->id !== -1){
			$stringResource->projectId = $newProjectId;
			$stringResourcesRepo->insert($stringResource);
		}
		$url = admin_url() . 'admin.php?page=calendarista-index&duplicated=true&projectId=' . $newProjectId;
		if (wp_redirect($url)) {
			exit;
		}
	}
	public static function deleteProject(){
		$repo = new Calendarista_ProjectRepository();
		$projects = isset($_POST['projects']) ? $_POST['projects'] : null;
		$projectId = isset($_POST['id']) ? (int)$_POST['id'] : null;
		if(!$projects){
			$projects = array($projectId);
		}
		$result = false;
		foreach($projects as $project){
			$result = $repo->delete((int)$project);
			if(!$result){
				break;
			}
		}
		$url = admin_url() . 'admin.php?page=calendarista-index&projectId=-1&deleted=true';
		if (wp_redirect($url)) {
			exit;
		}
	}
	public function update($callback){
		$result = $this->repo->update($this->project);
		$this->executeCallback($callback, array($this->project, $result));
	}
	public function sortOrder($callback){
		$sortOrder = $this->getPostValue('sortOrder');
		$result = false;
		if($sortOrder){
			$orderList = explode(',', $sortOrder);
			foreach($orderList as $ol){
				$item = explode(':', $ol);
				$this->repo->updateSortOrder((int)$item[0], (int)$item[1]);
			}
			$result = true;
		}
		$this->executeCallback($callback, array($result));
	}
	public function beforeDelete($callback){
		$projects = $this->getPostValue('projects');
		if(!$projects){
			$projects = array($this->project->id);
		}
		$this->executeCallback($callback, array($projects));
	}
}
?>