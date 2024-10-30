<?php
class Calendarista_TimeoffController extends Calendarista_BaseController{
	public function __construct($updateCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_timeoff')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		parent::__construct(null, $updateCallback);
	}
	
	public function update($callback){
		$id = isset($_POST['id']) ? intval($_POST['id']) : null;
		$projectId = isset($_POST['projectId']) ? intval($_POST['projectId']) : null;
		$availabilityId = isset($_POST['availabilityId']) ? intval($_POST['availabilityId']) : null;
		$holiday = isset($_POST['selectedDate']) ? sanitize_text_field($_POST['selectedDate']) : null;
		$timeslots = isset($_POST['timeslots']) ? array_map('intval', $_POST['timeslots']) : array();
		$timeslotsUndo = isset($_POST['timeslots_undo']) ? array_map('intval', $_POST['timeslots_undo']) : array();
		$repo = new Calendarista_HolidaysRepository();
		$result = false;
		if(count($timeslots) > 0){
			foreach($timeslots as $timeslotId){
				$result = $repo->insert(array(
					'projectId'=>$projectId
					, 'availabilityId'=>$availabilityId
					, 'holiday'=>$holiday
					, 'timeslotId'=>$timeslotId
				));
			}
		}
		if(count($timeslotsUndo) > 0){
			foreach($timeslotsUndo as $timeslotId){
				$result = $repo->deleteByTimeslot($timeslotId, $availabilityId);
			}
		}
		$this->executeCallback($callback, array($result));
	}
}
?>