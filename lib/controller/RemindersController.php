<?php
class Calendarista_RemindersController extends Calendarista_BaseController{
	private $repo;
	private $project;
	public function __construct($project, $saveCallback, $deleteCallback, $deleteAllCallback, $clearSchedulesCallback, $resendCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_reminders')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->project = $project;
		$this->repo = new Calendarista_RemindersRepository();
		if(array_key_exists('save', $_POST)){
			$this->save($saveCallback);
		}else if(array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('deleteAll', $_POST)){
			$this->deleteAll($deleteAllCallback);
		}else if(array_key_exists('clearSchedules', $_POST)){
			$this->clearSchedules($clearSchedulesCallback);
		}else if(array_key_exists('resend', $_POST)){
			$this->resend($resendCallback);
		}
	}
	
	public function save($callback){
		$reminder = isset($_POST['reminder']) ? intval($_POST['reminder']) : null;
		$thankyouReminder = isset($_POST['thankyouReminder']) ? intval($_POST['thankyouReminder']) : null;
		if(isset($this->project->id)){
			$projectRepository = new Calendarista_ProjectRepository();
			$this->project->reminder = $reminder;
			$this->project->thankyouReminder = $thankyouReminder;
			$result = $projectRepository->update($this->project);
		}
		$this->executeCallback($callback, array($this->project));
	}
	public function delete($callback){
		$id = isset($_POST['id']) ? intval($_POST['id']) : null;
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
	
	public function deleteAll($callback){
		$result = $this->repo->deleteAll();
		$this->executeCallback($callback, array($result));
	}
	
	public function clearSchedules($callback){
		Calendarista_EmailReminderJob::cancelAllSchedules();
		$this->executeCallback($callback, array(true));
	}
	
	public function resend($callback){
		$orderId = isset($_POST['orderId']) ? intval($_POST['orderId']) : null;
		$bookedAvailabilityId = isset($_POST['bookedAvailabilityId']) ? intval($_POST['bookedAvailabilityId']) : null;
		$reminderType = isset($_POST['reminderType']) ? intval($_POST['reminderType']) : 0/*appointment*/;
		Calendarista_EmailReminderJob::resendReminder($orderId, $bookedAvailabilityId, $reminderType);
		$this->executeCallback($callback, array(true));
	}
}
?>