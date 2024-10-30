<?php
class Calendarista_CalendarViewTmpl extends Calendarista_TemplateBase{
	public $generalSetting;
	public $projectList;
	public $view;
	public $formElementList;
	public $status;
	public $includeNameField;
	public $includeEmailField;
	public $includeAvailabilityNameField;
	public $includeSeats;
	public $uniqueId;
	public $locale = 'en';
	function __construct($projectList, $view, $formElementList, $status, $includeNameField, $includeEmailField, $includeAvailabilityNameField, $includeSeats){
		parent::__construct();
		$this->formElementList = $formElementList;
		$this->projectList = $projectList;
		$this->view = $view;
		$this->status = $status;
		$this->includeNameField = $includeNameField;
		$this->includeEmailField = $includeEmailField;
		$this->includeAvailabilityNameField = $includeAvailabilityNameField;
		$this->includeSeats = $includeSeats;
		$this->uniqueId = join('_', $projectList);
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$locale = apply_filters('locale',  get_locale(), 'calendarista');
		if($locale){
			$loc = explode('_', $locale);
			if(count($loc) > 0){
				$this->locale = strtolower($loc[0]);
			}
		}
		$this->render();
	}
	
	public function render(){
	?>
	
	<div class="calendarista">
		<div id="spinner_callback_<?php echo $this->uniqueId ?>" class="calendarista-spinner spinner-border text-dark calendarista-invisible" role="status">
		  <span class="sr-only"><?php echo esc_html($this->stringResources['AJAX_SPINNER'])?></span>
		</div>
		<div id="calendarista_calendar_<?php echo $this->uniqueId ?>" class="calendarista-fullcalendar-frontend"></div>
	</div>
	<script type="text/javascript">
		(function($){
			"use strict";
			$(window).ready(function(){
				new Calendarista.fullcalendar({
					'ajaxUrl': '<?php echo admin_url("admin-ajax.php")?>'
					, 'fullcalendarId': 'calendarista_calendar_<?php echo $this->uniqueId ?>'
					, 'spinnerId': 'spinner_callback_<?php echo $this->uniqueId ?>'
					, 'projectList': '<?php echo join(',', $this->projectList); ?>'
					, 'view': '<?php echo $this->view ?>'
					, 'formElementList': '<?php echo join(',', $this->formElementList); ?>'
					, 'locale': '<?php echo $this->locale ?>'
					, 'status': <?php echo $this->status ?>
					, 'includeNameField': <?php echo (int)$this->includeNameField ?>
					, 'includeEmailField': <?php echo (int)$this->includeEmailField ?>
					, 'includeAvailabilityNameField':  <?php echo (int)$this->includeAvailabilityNameField ?>
					, 'includeSeats': <?php echo (int)$this->includeSeats ?>
					, 'firstDayOfWeek': <?php echo $this->generalSetting->firstDayOfWeek ?>
					, 'nonce': '<?php wp_create_nonce('ajax-nonce') ?>'
				});
			});
	})(window['jQuery']);
	</script>
		<?php
	}
}