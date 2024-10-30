<?php
class Calendarista_Render{
	public $projects;
	public $enableMultipleBooking;
	public $mainServiceId;
	public $serviceThumbnailView;
	public $availabilityThumbnailView;
	
	public function __construct()
	{
		$this->projects = array();
	}
	public function booking($projects, $enableMultipleBooking, $serviceThumbnailView, $availabilityThumbnailView) {
		$this->projects = $projects;
		$this->enableMultipleBooking = $enableMultipleBooking;
		$this->serviceThumbnailView = $serviceThumbnailView;
		$this->availabilityThumbnailView = $availabilityThumbnailView;
		if(count($projects) === 0){
			return;
		}
		add_filter('calendarista_shortcode_id', array($this, 'getProjects'));
		add_filter('calendarista_enable_multi_booking', array($this, 'enableMultipleBooking'));
		add_filter('calendarista_service_thumbnail_view', array($this, 'getServiceThumbnailView'));
		add_filter('calendarista_availability_thumbnail_view', array($this, 'getAvailabilityThumbnailView'));
		$output = null;
		ob_start();
		new Calendarista_BookingWizardTmpl();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function publicCalendar($projects, $view, $formElements, $status, $includeName, $includeEmail, $includeAvailabilityName, $includeSeats) {
		$this->projects = $projects;
		if(count($projects) === 0){
			return;
		}
		$output = null;
		ob_start();
		new Calendarista_CalendarViewTmpl($projects, $view, $formElements, $status, $includeName, $includeEmail, $includeAvailabilityName, $includeSeats);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function search($projects, $includeService, $includeTime, $excludeEndDate, $excludeEndDateTime, $filterAttr, $resultPage = null) {
		$this->projects = $projects;
		if(count($projects) === 0){
			return;
		}
		$output = null;
		ob_start();
		new Calendarista_BookingSearchTmpl($projects, $includeService, $includeTime, $excludeEndDate, $excludeEndDateTime, $filterAttr, $resultPage);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function cancelAppointment(){
		$output = null;
		ob_start();
		new Calendarista_CancelAppointmentTmpl();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function gdpr(){
		$output = null;
		ob_start();
		new Calendarista_GdprComplianceTmpl();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function confirmationMessage(){
		$output = null;
		ob_start();
		new Calendarista_ConfirmationTmpl();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function userProfile($upcomingLabel, $historyLabel, $enableEdit, $editPolicy){
		$output = null;
		ob_start();
		new Calendarista_UserProfileTmpl($upcomingLabel, $historyLabel, $enableEdit, $editPolicy);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function getProjects(){
		return $this->projects;
	}
	public function enableMultipleBooking(){
		return $this->enableMultipleBooking;
	}
	public function getMainServiceId(){
		return $this->mainServiceId;
	}
	public function getServiceThumbnailView(){
		return $this->serviceThumbnailView;
	}
	public function getAvailabilityThumbnailView(){
		return $this->availabilityThumbnailView;
	}
}

?>