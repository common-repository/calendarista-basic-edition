<?php
class Calendarista_RenderFrontEndShortCodes{
	public function __construct(){
		add_shortcode('calendarista-booking', array($this, 'processShortCodeBooking'));
		add_shortcode('calendarista-search', array($this, 'processShortCodeSearch'));
		add_shortcode('calendarista-search-result', array($this, 'processShortCodeSearchResult'));
		add_shortcode('calendarista-public-calendar', array($this, 'processShortCodeCalendarView'));
		add_shortcode('calendarista-cancel-appointment', array($this, 'processShortCodeCancelAppointment'));
		add_shortcode('calendarista-gdpr', array($this, 'processShortCodeGdpr'));
		add_shortcode('calendarista-confirmation', array($this, 'processConfirmationMessage'));
	}
	function processShortCodeBooking( $atts, $content = null ) {
		$projects = isset($atts['id']) ? array_map('intval', explode(',', $atts['id'])) : array();
		$enableMultipleBooking = isset($atts['enable-multiple-booking']) ? filter_var($atts['enable-multiple-booking'], FILTER_VALIDATE_BOOLEAN) : false;
		$serviceThumbnailView = isset($atts['service-thumbnail-view']) ? filter_var($atts['service-thumbnail-view'], FILTER_VALIDATE_BOOLEAN) : false;
		$availabilityThumbnailView = isset($atts['availability-thumbnail-view']) ? filter_var($atts['availability-thumbnail-view'], FILTER_VALIDATE_BOOLEAN) : false;
		$render = new Calendarista_Render();
		return $render->booking($projects, $enableMultipleBooking, $serviceThumbnailView, $availabilityThumbnailView);
	}
	public function processShortCodeCalendarView($atts, $content = null) {
		$projects = isset($atts['id']) ? array_map('intval', explode(',', $atts['id'])) : array(-1);
		$view = isset($atts['view']) ? $atts['view'] : 'calendar';
		$formElements = isset($atts['form-elements']) ? array_map('intval', explode(',', $atts['form-elements'])) : array(-1);
		$status = isset($atts['status']) ? (int)$atts['status'] : null;
		$includeName = isset($atts['name']) ? filter_var($atts['name'], FILTER_VALIDATE_BOOLEAN) : false;
		$includeEmail = isset($atts['email']) ? filter_var($atts['email'], FILTER_VALIDATE_BOOLEAN) : false;
		$includeAvailabilityName = isset($atts['availability-name']) ? filter_var($atts['availability-name'], FILTER_VALIDATE_BOOLEAN) : false;
		$includeSeats = isset($atts['seats']) ? filter_var($atts['seats'], FILTER_VALIDATE_BOOLEAN) : false;
		$render = new Calendarista_Render();
		return $render->publicCalendar($projects, $view, $formElements, $status, $includeName, $includeEmail, $includeAvailabilityName, $includeSeats);
	}
	public function processShortCodeSearch($atts, $content = null) {
		$projects = isset($atts['id']) ? array_map('intval', explode(',', $atts['id'])) : array(-1);
		$filterAttr = isset($atts['filter-attr']) ? array_map('intval', explode(',', $atts['filter-attr'])) : array(-1);
		$includeService = isset($atts['service']) ? filter_var($atts['service'], FILTER_VALIDATE_BOOLEAN) : false;
		$includeTime = isset($atts['time']) ? filter_var($atts['time'], FILTER_VALIDATE_BOOLEAN) : false;
		$excludeEndDate = isset($atts['exclude-end-date']) ? filter_var($atts['exclude-end-date'], FILTER_VALIDATE_BOOLEAN) : false;
		$excludeEndDateTime = isset($atts['exclude-end-date-time']) ? filter_var($atts['exclude-end-date-time'], FILTER_VALIDATE_BOOLEAN) : false;
		$resultPage = isset($atts['result-page']) ? (int)$atts['result-page'] : null;
		$render = new Calendarista_Render();
		return $render->search($projects, $includeService, $includeTime, $excludeEndDate, $excludeEndDateTime, $filterAttr, $resultPage);
	}
	public function processShortCodeSearchResult($atts, $content = null) {
		$projects = isset($atts['id']) ? array_map('intval', explode(',', $atts['id'])) : array(-1);
		$filterAttr = isset($atts['filter-attr']) ? array_map('intval', explode(',', $atts['filter-attr'])) : array(-1);
		$includeService = isset($atts['service']) ? filter_var($atts['service'], FILTER_VALIDATE_BOOLEAN) : false;
		$includeTime = isset($atts['time']) ? filter_var($atts['time'], FILTER_VALIDATE_BOOLEAN) : false;
		$excludeEndDate = isset($atts['exclude-end-date']) ? filter_var($atts['exclude-end-date'], FILTER_VALIDATE_BOOLEAN) : false;
		$excludeEndDateTime = isset($atts['exclude-end-date-time']) ? filter_var($atts['exclude-end-date-time'], FILTER_VALIDATE_BOOLEAN) : false;
		$render = new Calendarista_Render();
		return $render->search($projects, $includeService, $includeTime, $excludeEndDate, $excludeEndDateTime, $filterAttr);
	}
	public function processShortCodeCancelAppointment( $atts, $content = null ) {
		$render = new Calendarista_Render();
		return $render->cancelAppointment();
	}
	public function processShortCodeGdpr( $atts, $content = null ) {
		$render = new Calendarista_Render();
		return $render->gdpr();
	}
	public function processConfirmationMessage($atts, $content = null){
		$render = new Calendarista_Render();
		return $render->confirmationMessage();
	}
}
?>
