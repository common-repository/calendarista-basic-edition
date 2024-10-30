<?php
class Calendarista_SetupStep5Template extends Calendarista_ViewBase{
	public $projectId;
	public $availabilityId;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->projectId = null;
		$this->completeFinalStep();
		$this->render();
	}
	function completeFinalStep(){
		$projectRepo = new Calendarista_ProjectRepository();
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
			'optionalByService'=>isset($_POST['optionalByService']) ? (bool)$_POST['optionalByService'] : null
		));
		$this->projectId = $projectRepo->insert($project);
		if(!$this->projectId){
			return;
		}
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availability = new Calendarista_Availability(array(
			'availableDate'=>isset($_POST['availableDate']) ? sanitize_text_field($_POST['availableDate']) : null,
			'calendarMode'=>isset($_POST['calendarMode']) ? (int)$_POST['calendarMode'] : null,
			'paymentsMode'=>isset($_POST['paymentsMode']) ? (int)$_POST['paymentsMode'] : null,
			'daysInPackage'=>isset($_POST['daysInPackage']) ? (int)$_POST['daysInPackage'] : null,
			'cost'=>isset($_POST['cost']) ? (double)$_POST['cost'] : null,
			'returnCost'=>isset($_POST['returnCost']) ? (double)$_POST['returnCost'] : null,
			'depositMode'=>isset($_POST['depositMode']) ? (int)$_POST['depositMode'] : null,
			'deposit'=>isset($_POST['deposit']) ? (double)$_POST['deposit'] : null,
			'returnOptional'=>isset($_POST['returnOptional']) ? (bool)$_POST['returnOptional'] : null
		));
		$availability->name = 'Availability #1';
		$availability->projectId = $this->projectId;
		$this->availabilityId = $availabilityRepo->insert($availability);
		if($this->getPostValue('startInterval')){
			$this->genTimeSlots();
		}
	}
	function genTimeSlots(){
		$calendarMode = (int)$this->getPostValue('calendarMode');
		$startInterval = (string)$this->getPostValue('startInterval');
		$timeSplit = (string)$this->getPostValue('timeSplit');
		$endTime = (string)$this->getPostValue('endTime');
		$weekday = 0;
		$seats = $this->getPostValue('seats') ? 0 : $this->getIntValue('slotSeats');
		$seatsMaximum = 0;
		$seatsMinimum = 1;
		$cost = isset($_POST['slotCost']) ? (float)$_POST['slotCost'] : null;
		$paddingTimeBefore = isset($_POST['paddingTimeBefore']) ? (float)$_POST['paddingTimeBefore'] : null;
		$paddingTimeAfter = isset($_POST['paddingTimeAfter']) ? (float)$_POST['paddingTimeAfter'] : null;
		$si = $this->parseTime($startInterval);
		$ts = $this->parseTime($timeSplit);
		$ed = $this->parseTime($endTime);
		$enableSingleHourMinuteFormat = true;
		if(in_array($calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIME_RANGE)){
			$enableSingleHourMinuteFormat = false;
		}
		$result = $this->createTimeSlots($ts[0], $ts[1], $si[0], $si[1], $ed[0], $ed[1], $enableSingleHourMinuteFormat);
		$timeslots = new Calendarista_Timeslots();
		$weekdays = array(1, 2, 3, 4, 5, 6, 7);
		foreach($weekdays as $wday){
			foreach($result as $r){
				$timeslot = new Calendarista_Timeslot(array(
					'availabilityId'=>$this->availabilityId
					, 'projectId'=>$this->projectId
					, 'timeslot'=>$r['value']
					, 'cost'=>$cost
					, 'seats'=>$seats
					, 'seatsMaximum'=>$seatsMaximum
					, 'seatsMinimum'=>$seatsMinimum
					, 'paddingTimeBefore'=>$paddingTimeBefore
					, 'paddingTimeAfter'=>$paddingTimeAfter
					, 'weekday'=>$wday
				));
				$timeslots->add($timeslot);
			}
		}
		$result = null;
		$repo = new Calendarista_TimeslotRepository();
		foreach($timeslots as $timeslot){
			$result = $repo->insert($timeslot);
			if(!$result){
				break;
			}
		}
	}
	protected function parseTime($time){
		return Calendarista_AutogenTimeslotsController::parseTime($time);
	}
	protected function createTimeSlots($hours, $minutes, $hourStartInterval, $minuteStartInterval, $lastSlotHour, $lastSlotMinute, $enableSingleHourMinuteFormat){
		return Calendarista_AutogenTimeslotsController::createTimeSlots($hours, $minutes, $hourStartInterval, $minuteStartInterval, $lastSlotHour, $lastSlotMinute, $enableSingleHourMinuteFormat);
	}
	protected function getIntValue($key, $default = null){
		return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
	}
	public function render(){
	?>
		<div id="step">
			<h1><?php esc_html_e('Service is ready', 'calendarista') ?></h1>
			<p class="description"><?php esc_html_e('Copy and paste the following short code on any page or post. If you would like to further customize the service, a ton of features are waiting for you in the service settings.', 'calendarista') ?></p>
			<div>
				<div><label for="single-short-code"><?php esc_html_e('Short code', 'calendarista') ?></label></div>
				<input readonly class="regular-text" value='[calendarista-booking id="<?php echo $this->projectId ?>"]'/>
				<input type="hidden" value="<?php echo $this->projectId ?>" id="projectId">
				<?php echo do_action('calendarista_service_info', $this->projectId); ?>
			</div>
			<br>
			<p class="description"><span><?php esc_html_e('Feeling stuck? Start by reading the', 'calendarista') ?></span>&nbsp;<a href="<?php echo CALENDARISTA_ABSOLUTE_PATH_TO_DOCUMENTATION ?>"><?php echo __("documentation", "calendarista") ?></a></p>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.setupStep5 = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupStep5.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$root = $('#step');
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupStep5({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}