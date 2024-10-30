<?php
class Calendarista_AvailabilityTemplate extends Calendarista_ViewBase{
	public $availability;
	public $availabilities;
	public $selectedId = -1;
	public $repeatFrequency;
	public $weekdays;
	public $createNew;
	public $autoGen = false;
	public $syncAvailabilities = array();
	public $monthlyRepeatDay = null;
	public $yearlyRepeatDate = null;
	public $tagList;
	function __construct( ){
		parent::__construct();
		$this->repeatFrequency = Calendarista_RepeatFrequency::toArray();
		$this->weekdays = Calendarista_Weekday::toArray();
		$this->submitButtonText = __('Save changes', 'calendarista');
		$this->project = $this->getProject();
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		
		$this->availability = new Calendarista_Availability(array(
			'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
			'availableDate'=>isset($_POST['availableDate']) ? sanitize_text_field($_POST['availableDate']) : null,
			'cost'=>isset($_POST['cost']) ? (double)$_POST['cost'] : null,
			'customChargeDays'=>isset($_POST['customChargeDays']) ? (int)$_POST['customChargeDays'] : null,
			'customCharge'=>isset($_POST['customCharge']) ? (double)$_POST['customCharge'] : null,
			'customChargeMode'=>isset($_POST['customChargeMode']) ? (int)$_POST['customChargeMode'] : null,
			'deposit'=>isset($_POST['deposit']) ? (double)$_POST['deposit'] : null,
			'depositMode'=>isset($_POST['depositMode']) ? (int)$_POST['depositMode'] : null,
			'returnOptional'=>isset($_POST['returnOptional']) ? (bool)$_POST['returnOptional'] : null,
			'returnCost'=>isset($_POST['returnCost']) ? (double)$_POST['returnCost'] : null,
			'seats'=>isset($_POST['seats']) ? (int)$_POST['seats'] : null,
			'seatsMaximum'=>isset($_POST['seatsMaximum']) ? (int)$_POST['seatsMaximum'] : null,
			'seatsMinimum'=>isset($_POST['seatsMinimum']) ? (int)$_POST['seatsMinimum'] : null,
			'selectableSeats'=>isset($_POST['selectableSeats']) ? (bool)$_POST['selectableSeats'] : null,
			'daysInPackage'=>isset($_POST['daysInPackage']) ? (int)$_POST['daysInPackage'] : null,
			'fullDay'=>isset($_POST['fullDay']) ? (bool)$_POST['fullDay'] : null,
			'hasRepeat'=>isset($_POST['hasRepeat']) ? (bool)$_POST['hasRepeat'] : null,
			'repeatFrequency'=>isset($_POST['repeatFrequency']) ? (int)$_POST['repeatFrequency'] : null,
			'repeatInterval'=>isset($_POST['repeatInterval']) ? (int)$_POST['repeatInterval'] : null,
			'terminateMode'=>isset($_POST['terminateMode']) ? (int)$_POST['terminateMode'] : null,
			'terminateAfterOccurrence'=>isset($_POST['terminateAfterOccurrence']) ? (int)$_POST['terminateAfterOccurrence'] : null,
			'repeatWeekdayList'=>isset($_POST['repeatWeekdayList']) ? sanitize_text_field($_POST['repeatWeekdayList']) : null,
			'checkinWeekdayList'=>isset($_POST['checkinWeekdayList']) ? sanitize_text_field($_POST['checkinWeekdayList']) : null,
			'checkoutWeekdayList'=>isset($_POST['checkoutWeekdayList']) ? sanitize_text_field($_POST['checkoutWeekdayList']) : null,
			'syncList'=>isset($_POST['syncList']) ? sanitize_text_field($_POST['syncList']) : null,
			'endDate'=>isset($_POST['endDate']) ? sanitize_text_field($_POST['endDate']) : null,
			'color'=>isset($_POST['color']) ? sanitize_text_field($_POST['color']) : null,
			'timezone'=>isset($_POST['timezone']) ? sanitize_text_field($_POST['timezone']) : null,
			'imageUrl'=>isset($_POST['imageUrl']) ? sanitize_text_field($_POST['imageUrl']) : null,
			'searchThumbnailUrl'=>isset($_POST['searchThumbnailUrl']) ? sanitize_text_field($_POST['searchThumbnailUrl']) : null,
			'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
			'maxTimeslots'=>isset($_POST['maxTimeslots']) ? (int)$_POST['maxTimeslots'] : null,
			'minimumTimeslotCharge'=>isset($_POST['minimumTimeslotCharge']) ? (double)$_POST['minimumTimeslotCharge'] : null,
			'maximumNotice'=>isset($_POST['maximumNotice']) ? (int)$_POST['maximumNotice'] : null,
			'minimumNotice'=>isset($_POST['minimumNotice']) ? (int)$_POST['minimumNotice'] : null,
			'bookingDaysMinimum'=>isset($_POST['bookingDaysMinimum']) ? (int)$_POST['bookingDaysMinimum'] : null,
			'bookingDaysMaximum'=>isset($_POST['bookingDaysMaximum']) ? (int)$_POST['bookingDaysMaximum'] : null,
			'turnoverBefore'=>isset($_POST['turnoverBefore']) ? (int)$_POST['turnoverBefore'] : null,
			'turnoverAfter'=>isset($_POST['turnoverAfter']) ? (int)$_POST['turnoverAfter'] : null,
			'turnoverBeforeMin'=>isset($_POST['turnoverBeforeMin']) ? (int)$_POST['turnoverBeforeMin'] : null,
			'turnoverAfterMin'=>isset($_POST['turnoverAfterMin']) ? (int)$_POST['turnoverAfterMin'] : null,
			'description'=>isset($_POST['description']) ? sanitize_text_field($_POST['description']) : null,
			'timeMode'=>isset($_POST['timeMode']) ? (int)$_POST['timeMode'] : null,
			'displayRemainingSeats'=>isset($_POST['displayRemainingSeats']) ? (bool)$_POST['displayRemainingSeats'] : null,
			'displayRemainingSeatsMessage'=>isset($_POST['displayRemainingSeatsMessage']) ? (bool)$_POST['displayRemainingSeatsMessage'] : null,
			'calendarMode'=>isset($_POST['calendarMode']) ? (int)$_POST['calendarMode'] : null,
			'searchPage'=>isset($_POST['searchPage']) ? (int)$_POST['searchPage'] : null,
			'timeDisplayMode'=>isset($_POST['timeDisplayMode']) ? (int)$_POST['timeDisplayMode'] : null,
			'dayCountMode'=>isset($_POST['dayCountMode']) ? (int)$_POST['dayCountMode'] : null,
			'appendPackagePeriodToName'=>isset($_POST['appendPackagePeriodToName']) ? (bool)$_POST['appendPackagePeriodToName'] : null,
			'minimumNoticeMinutes'=>isset($_POST['minimumNoticeMinutes']) ? (int)$_POST['minimumNoticeMinutes'] : null,
			'extendTimeRangeNextDay'=>isset($_POST['extendTimeRangeNextDay']) ? (bool)$_POST['extendTimeRangeNextDay'] : null,
			'minTime'=>isset($_POST['minTime']) ? (int)$_POST['minTime'] : null,
			'maxTime'=>isset($_POST['maxTime']) ? (int)$_POST['maxTime'] : null,
			'maxDailyRepeatFrequency'=>isset($_POST['maxDailyRepeatFrequency']) ? (bool)$_POST['maxDailyRepeatFrequency'] : null,
			'maxWeeklyRepeatFrequency'=>isset($_POST['maxWeeklyRepeatFrequency']) ? (bool)$_POST['maxWeeklyRepeatFrequency'] : null,
			'maxMonthlyRepeatFrequency'=>isset($_POST['maxMonthlyRepeatFrequency']) ? (bool)$_POST['maxMonthlyRepeatFrequency'] : null,
			'maxYearlyRepeatFrequency'=>isset($_POST['maxYearlyRepeatFrequency']) ? (bool)$_POST['maxYearlyRepeatFrequency'] : null,
			'maxRepeatOccurrence'=>isset($_POST['maxRepeatOccurrence']) ? (int)$_POST['maxRepeatOccurrence'] : null,
			'returnSameDay'=>isset($_POST['returnSameDay']) ? (bool)$_POST['returnSameDay'] : null,
			'maxRepeatFrequency'=>isset($_POST['maxRepeatFrequency']) ? (int)$_POST['maxRepeatFrequency'] : null,
			'guestNameRequired'=>isset($_POST['guestNameRequired']) ? (bool)$_POST['guestNameRequired'] : null,
			'displayDateSelectionReq'=>isset($_POST['displayDateSelectionReq']) ? (bool)$_POST['displayDateSelectionReq'] : null,
			'enableFullAmountOrDeposit'=>isset($_POST['enableFullAmountOrDeposit']) ? (bool)$_POST['enableFullAmountOrDeposit'] : null,
			'fullAmountDiscount'=>isset($_POST['fullAmountDiscount']) ? (double)$_POST['fullAmountDiscount'] : null,
			'instructions'=>isset($_POST['instructions']) ? sanitize_text_field($_POST['instructions']) : null,
			'orderIndex'=>isset($_POST['orderIndex']) ? (int)$_POST['orderIndex'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null
		));
		$this->availabilities = new Calendarista_Availabilities();
		new Calendarista_AvailabilityController(
			$this->availability
			, array($this, 'newAvailability')
			, array($this, 'sortOrder')
			, array($this, 'editAvailability')
			, array($this, 'createdAvailability')
			, array($this, 'updatedAvailability')
			, array($this, 'deletedAvailability')
		);
		if(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'pricing_scheme'){
			if($this->getPostValue('availabilityId')){
				$this->selectedId = (int)$this->getPostValue('availabilityId');
			}
		}
		
		if($this->selectedProjectId !== -1){
			$this->availabilities = $availabilityRepo->readAll($this->selectedProjectId);
		}
		if($this->selectedId !== -1 && $this->selectedId !== null){
			$this->availability = $availabilityRepo->read($this->selectedId);
		}
		$this->createNew = $this->selectedId === -1 ? true : false;
		if(isset($_GET['newservice'])){
			$this->newServiceCreatedNotice();
		}
		$services = $this->getSyncServiceList();
		if(count($services) > 0){
			$result = $availabilityRepo->readAllByService($services);
			$this->syncAvailabilities = $this->groupAvailabilities($result);
		}
		$this->tagList = new Calendarista_TagByAvailabilityList($this->availability->id);
		$this->tagList->bind();
		if($this->availability->availableDate){
			$this->monthlyRepeatDay = date('jS', strtotime($this->availability->availableDate));
			$this->yearlyRepeatDate = date('jS F', strtotime($this->availability->availableDate));
		}
		$this->render();
	}
	public function getSelectableSeatCheckedStatus(){
		return '';
	}
	protected function groupAvailabilities($availabilities){
		$result = array();
		foreach($availabilities as $availability){
			if(!isset($result[$availability->projectId])){
				$result[$availability->projectId] = array();
			}
			array_push($result[$availability->projectId], $availability);
		}
		return $result;
	}
	protected function getSyncServiceList(){
		if($this->createNew){
			return array();
		}
		$result = array();
		$this->readAllProjects();
		foreach($this->projects as $project){
			if($this->project->calendarMode === $project->calendarMode){
				array_push($result, $project->id);
			}
		}
		return $result;
	}
	protected function getProjectNameById($projectId){
		foreach($this->projects as $project){
			if($project->id === $projectId){
				return $project->name;
			}
		}
		return null;
	}
	public function wp_timezone_choice( $selected_zone ) {
		static $mo_loaded = false;

		$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');

		// Load translations for continents and cities
		if ( !$mo_loaded ) {
			$locale = get_locale();
			$mofile = WP_LANG_DIR . '/continents-cities-' . $locale . '.mo';
			load_textdomain( 'continents-cities', $mofile );
			$mo_loaded = true;
		}

		$zonen = array();
		foreach ( timezone_identifiers_list() as $zone ) {
			$zone = explode( '/', $zone );
			if ( !in_array( $zone[0], $continents ) ) {
				continue;
			}

			// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
			$exists = array(
				0 => ( isset( $zone[0] ) && $zone[0] ),
				1 => ( isset( $zone[1] ) && $zone[1] ),
				2 => ( isset( $zone[2] ) && $zone[2] ),
			);
			$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
			$exists[4] = ( $exists[1] && $exists[3] );
			$exists[5] = ( $exists[2] && $exists[3] );

			$zonen[] = array(
				'continent'   => ( $exists[0] ? $zone[0] : '' ),
				'city'        => ( $exists[1] ? $zone[1] : '' ),
				'subcity'     => ( $exists[2] ? $zone[2] : '' ),
				't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
				't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
				't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' )
			);
		}
		usort( $zonen, '_wp_timezone_choice_usort_callback' );

		$structure = array();
		$structure[] = '<option value="">' . __( 'None' ) . '</option>';

		foreach ( $zonen as $key => $zone ) {
			// Build value in an array to join later
			$value = array( $zone['continent'] );

			if ( empty( $zone['city'] ) ) {
				// It's at the continent level (generally won't happen)
				$display = $zone['t_continent'];
			} else {
				// It's inside a continent group

				// Continent optgroup
				if ( !isset( $zonen[$key - 1] ) || $zonen[$key - 1]['continent'] !== $zone['continent'] ) {
					$label = $zone['t_continent'];
					$structure[] = '<optgroup label="'. esc_attr( $label ) .'">';
				}

				// Add the city to the value
				$value[] = $zone['city'];

				$display = $zone['t_city'];
				if ( !empty( $zone['subcity'] ) ) {
					// Add the subcity to the value
					$value[] = $zone['subcity'];
					$display .= ' - ' . $zone['t_subcity'];
				}
			}

			// Build the value
			$value = join( '/', $value );
			$selected = '';
			if ( $value === $selected_zone ) {
				$selected = 'selected="selected" ';
			}
			$structure[] = '<option ' . $selected . 'value="' . esc_attr( $value ) . '">' . esc_html( $display ) . "</option>";

			// Close continent optgroup
			if ( !empty( $zone['city'] ) && ( !isset($zonen[$key + 1]) || (isset( $zonen[$key + 1] ) && $zonen[$key + 1]['continent'] !== $zone['continent']) ) ) {
				$structure[] = '</optgroup>';
			}
		}

		// Do UTC
		$structure[] = '<optgroup label="'. esc_attr__( 'UTC' ) .'">';
		$selected = '';
		if ( 'UTC' === $selected_zone )
			$selected = 'selected="selected" ';
		$structure[] = '<option ' . $selected . 'value="' . esc_attr( 'UTC' ) . '">' . __('UTC') . '</option>';
		$structure[] = '</optgroup>';

		return join( "\n", $structure );
	}
	public function getAvailableDate(){
		if($this->availability->availableDate){
			return $this->availability->availableDate->format(CALENDARISTA_FULL_DATEFORMAT);
		}
		return '';
	}
	public function getEndDate(){
		if($this->availability->endDate){
			return $this->availability->endDate->format(CALENDARISTA_FULL_DATEFORMAT);
		}
		return '';
	}
	public function repeatWeekdayChecked($value){
		if(count($this->availability->repeatWeekdayList) > 0){
			return in_array($value, $this->availability->repeatWeekdayList) ? 'checked' : '';
		}
		return $value === 1 ? 'checked' : '';
	}
	public function checkinWeekdayChecked($value){
		if(count($this->availability->checkinWeekdayList) > 0){
			return in_array($value, $this->availability->checkinWeekdayList) ? 'checked' : '';
		}
		return null;
	}
	public function checkoutWeekdayChecked($value){
		if(count($this->availability->checkoutWeekdayList) > 0){
			return in_array($value, $this->availability->checkoutWeekdayList) ? 'checked' : '';
		}
		return null;
	}
	public function terminateModeChecked($value){
		return $this->availability->terminateMode === $value ? 'checked' : '';
	}
	public function terminateModeStatus($value){
		return $this->availability->terminateMode !== $value ? 'disabled' : '';
	}
	public function repeatFrequencySelected($value){
		return  $this->availability->repeatFrequency === $value ? 'selected' : '';
	}
	public function repeatIntervalSelected($value){
		return  $this->availability->repeatInterval === $value ? 'selected' : '';
	}
	public function getRepeatIntervalLabel(){
		switch($this->availability->repeatFrequency){
			case 1:
			return __('days', 'calendarista');
			case 6:
			return __('months', 'calendarista');
			break;
			case 7:
			return __('years', 'calendarista');
			break;
			default:
			return __('weeks', 'calendarista');
		}
	}
	public function getTerminateAfterOccurance(){
		return !$this->availability->terminateAfterOccurrence ? 35 : $this->availability->terminateAfterOccurrence;
	}
	public function availabilitySelected($id){
		return $this->availability->id === $id ? 'availability-selected' : '';
	}
	public function getAvailabilityTitle($availability){
		$name = Calendarista_StringResourceHelper::decodeString($availability->name);
		if($this->project->calendarMode === Calendarista_CalendarMode::PACKAGE && $availability->availableDate){
			$name .= sprintf(': %s', $availability->availableDate->format(CALENDARISTA_DATEFORMAT));
		}
		return $name;
	}
	public function getAvailabilityName($availability){
		return Calendarista_StringResourceHelper::decodeString(trim($availability->name));
	}
	public function newAvailability(){
		$this->availability = new Calendarista_Availability(array());
		$this->newAvailabilityNotice();
	}
	public function sortOrder($result){
		if($result){
			$this->sortOrderNotice();
		}
	}
	public function editAvailability($id){
		$this->selectedId = $id;
	}
	public function createdAvailability($result){
		if($result){
			$this->availability->id = $result;
			$this->selectedId = $result;
			$this->createdAvailabilityNotice();
		}
	}
	public function updatedAvailability($id){
		$this->selectedId = $id;
		$this->updatedAvailabilityNotice();
	}
	public function deletedAvailability($result){
		if($result){
			$this->availability = new Calendarista_Availability(array());
			$this->selectedId = -1;
			$this->deletedAvailabilityNotice();
		}
	}
	public function newServiceCreatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The service has been created, now setup your availability below.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function sortOrderNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The sort order has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function updatedAvailabilityNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The availability has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function createdAvailabilityNotice() {
		$adminUrl = sprintf(admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=2&projectId=%d&availabilityId=%d'
				, $this->selectedProjectId, $this->availability->id);
		$url = sprintf('<a href="%s">%s</a>',$adminUrl , __('timeslots', 'calendarista'));
		?>
		<div class="index updated notice is-dismissible">
			<p>
				<span><?php esc_html_e('The availability has been created.', 'calendarista'); ?>&nbsp;</span>
				<?php 
					if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
						echo sprintf(__('Please click on the %s tab and create timeslots as necessary.', 'calendarista'), $url);
					}
				?>
			</p>
		</div>
		<?php
	}
	public function deletedAvailabilityNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The availability has been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function newAvailabilityNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('Create new availability.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function errorNotice($message) {
		?>
		<div class="index error notice">
			<p><?php echo sprintf(__('The operation failed unexpectedly with [%s]. Try again?', 'calendarista'), $message); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<form id="calendarista_form1" action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-excluded="[disabled=disabled]">
						<input type="hidden" name="controller" value="availability" />
						<input type="hidden" name="id" value="<?php echo $this->availability->id ?>"  />
						<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
						<input type="hidden" name="fullDay" value="<?php echo in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST) ?>"/>
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<?php if($this->selectedProjectId !== -1):?>
								<?php if($this->availability->id !== -1): ?>
								<tr>
									<td>
										<label title="<?php esc_html_e('Availability ID', 'calendarista') ?>" class="calendarista-rounded-border">
											<?php echo sprintf('#%s', $this->availability->id) ?>
										</label>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<div><label for="name"><?php esc_html_e('Name', 'calendarista') ?></label></div>
										<input id="name" 
											name="name" 
											type="text" 
											class="regular-text" 
											data-parsley-required="true"
											data-parsley-group="block1"
											value="<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($this->availability->name)) ?>" />
										<?php echo do_action('calendarista_availability_info', $this->availability->id); ?>
									</td>
								</tr>
								<?php if(in_array($this->project->calendarMode, array(Calendarista_CalendarMode::PACKAGE))): ?>
								<tr>
									<td>
										<fieldset>
											<legend><span><?php esc_html_e('Append booking period to name', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<li>
													<label>
														<input name="appendPackagePeriodToName" value="0" type="radio" <?php echo !$this->availability->appendPackagePeriodToName ? 'checked' : ''?>>
														<?php esc_html_e('No', 'calendarista')?>
													</label>
												</li>
												<li>
													<label>
														<input name="appendPackagePeriodToName" value="1" type="radio" <?php echo $this->availability->appendPackagePeriodToName ? 'checked' : ''?>>
														<?php esc_html_e('Yes', 'calendarista')?>
													</label>
												</li>
											</ul>
										</fieldset>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<div><label for="availableDate"><?php esc_html_e('Date', 'calendarista') ?></label></div>
										<input id="availableDate" 
											name="availableDate" 
											type="text" 
											class="regular-text enable-readonly-input" 
											data-parsley-required="true"
											data-parsley-group="block1"
											readonly
											value="<?php echo $this->getAvailableDate() ?>"
aria-label="<?php esc_html_e('Please note: page up/down for previous/next month, ctrl plus page up/down for previous/next year, ctrl plus left/right for previous/next day, enter key to accept the selected date', 'calendarista') ?>"											/>
											<p class="description"><?php esc_html_e('Availability date, format later in settings', 'calendarista')?></p>
									</td>
								</tr>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)):?>
								<tr>
									<td>
										<div><label for="timezone"><?php esc_html_e('Timezone', 'calendarista') ?></label></div>
										<select id="timezone_" name="timezone" aria-describedby="timezone-description">
											<?php echo $this->wp_timezone_choice($this->availability->timezone); ?>
										</select>
										<p class="description" id="timezone-description"><?php echo __( 'Choose an area in the same timezone as you', 'calendarista'); ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if($this->project->paymentsMode !== -1 && in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST)):?>
								<tr>
									<td>
										<div><label for="cost"><?php esc_html_e('Cost', 'calendarista') ?></label></div>
										<input id="cost" 
											name="cost" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-min="0"
											data-parsley-pattern="^\d+(\.\d{1,2})?$"
											data-parsley-errors-container="#cost_error_container"
											placeholder="0.00" 
											data-parsley-group="block1"
											value="<?php echo $this->emptyStringIfZero($this->availability->cost) ?>" />&nbsp;<?php esc_html_e('full price', 'calendarista') ?>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_AND_TIMESLOT_COST)):?>
										<p class="description"><?php esc_html_e('**Leave cost blank if you want to set cost by time slot', 'calendarista') ?></p>
										<?php endif; ?>
										<?php if($this->project->calendarMode === Calendarista_CalendarMode::PACKAGE):?>
										<p class="description"><?php esc_html_e('Insert cost for entire package and not individual days', 'calendarista') ?></p>
										<?php elseif(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_CUSTOM_CHARGE)):?>
										<p>
										<?php esc_html_e('However, if', 'calendarista') ?>&nbsp;
											<input id="customChargeDays" 
													name="customChargeDays" 
													type="text" 
													class="small-text" 
													title="<?php esc_html_e('must be 2 or more', 'calendarista') ?>"
													data-parsley-type="digits"
													data-parsley-cal-hasval="#cost"
													data-parsley-error-message="<?php esc_html_e('Ensure full price is set.', 'calendarista') ?>"
													data-parsley-errors-container="#cost_error_container"
													placeholder="0"
													value="<?php echo $this->emptyStringIfZero($this->availability->customChargeDays) ?>"/>&nbsp;
											<?php esc_html_e('day(s) or more selected,', 'calendarista')?>
										</p>
										<p>
											<?php esc_html_e('then charge', 'calendarista')?>&nbsp;
											<input id="customCharge" 
													name="customCharge" 
													type="text" 
													class="small-text" 
													data-parsley-trigger="change focusout"
													data-parsley-pattern="^-?\d+(\.\d{1,2})?$"
													data-parsley-cal-hasval="#cost"
													data-parsley-error-message="<?php esc_html_e('Ensure both full price and days is set.', 'calendarista') ?>"
													data-parsley-errors-container="#cost_error_container"
													placeholder="0.00" 
													value="<?php echo $this->emptyStringIfZero($this->availability->customCharge) ?>"/>&nbsp;
											<label>
												 <input type="radio"  
														name="customChargeMode" 
														value="0"
														<?php echo !$this->availability->customChargeMode ? 'checked' : '' ?>>
												<?php esc_html_e('percentage', 'calendarista') ?>
											</label>
											&nbsp;
											<label>
												 <input type="radio"  
														name="customChargeMode" 
														value="1"
														<?php echo $this->availability->customChargeMode ? 'checked' : '' ?>>
												<?php esc_html_e('or flat fee', 'calendarista') ?>
												</label>
										</p>
											<p class="description"><?php esc_html_e('Note: Setting custom charge will add or subtract from full price above. To subtract, use negative value.', 'calendarista') ?></p>
										<?php endif; ?>
										<div id="cost_error_container"></div>
									</td>
								</tr>
								<?php if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE):?>
								<tr>
									<td>
										<div><label for="timeMode"><?php esc_html_e('Time mode', 'calendarista') ?></label></div>
										<input id="timeMode" 
											name="timeMode" 
											type="radio" 
											<?php echo !$this->availability->timeMode ? 'checked' : '' ?> value="0" />
											<?php esc_html_e('By time', 'calendarista') ?>
										<input id="timeMode" 
											name="timeMode" 
											type="radio" 
											<?php echo $this->availability->timeMode ? 'checked' : '' ?> value="1" />
											<?php esc_html_e('By day', 'calendarista') ?>
										<p class="description"><?php esc_html_e('By time means 24h or less in a range will cost 1 day.', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, array(
												Calendarista_CalendarMode::SINGLE_DAY_AND_TIME
												, Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_WITH_PADDING
												, Calendarista_CalendarMode::MULTI_DATE_AND_TIME))):?>
								<tr>
									<td>
										<div><label for="timeDisplayMode"><?php esc_html_e('Time display mode', 'calendarista') ?></label></div>
										<input id="timeDisplayMode" 
											name="timeDisplayMode" 
											type="radio" 
											<?php echo !$this->availability->timeDisplayMode ? 'checked' : '' ?> value="0" />
											<?php esc_html_e('Standard view', 'calendarista') ?>
										<input id="timeDisplayMode" 
											name="timeDisplayMode" 
											type="radio" 
											<?php echo $this->availability->timeDisplayMode ? 'checked' : '' ?> value="1" />
											<?php esc_html_e('Deals view', 'calendarista') ?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if($this->project->paymentsMode !== -1):?>
								<tr>
									<td>
										<div><label for="deposit"><?php esc_html_e('Deposit', 'calendarista') ?></label></div>
										<i><?php esc_html_e('Customer will be charged an upfront', 'calendarista') ?></i>
										<br>
										<label>
											 <input type="radio"  
													name="depositMode" 
													value="0"
													<?php echo !$this->availability->depositMode ? 'checked' : '' ?>>
											<?php esc_html_e('percentage', 'calendarista') ?>
										</label>
										<label>
											 <input type="radio"  
													name="depositMode" 
													value="1"
													<?php echo $this->availability->depositMode === 1 ? 'checked' : '' ?>>
											<?php esc_html_e('flat fee', 'calendarista') ?>
										</label>
										<label>
											 <input type="radio"  
													name="depositMode" 
													value="2"
													<?php echo $this->availability->depositMode === 2 ? 'checked' : '' ?>>
											<?php esc_html_e('flat fee x seats', 'calendarista') ?>
										</label>
										<input id="deposit" 
											name="deposit" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-min="0"
											data-parsley-pattern="^\d+(\.\d{1,2})?$"
											data-parsley-cal-hasval="#cost"
											data-parsley-errors-container="#deposit_error_container"
											data-parsley-error-message="<?php esc_html_e('Ensure full price is set.', 'calendarista') ?>"
											placeholder="0.00" 
											value="<?php echo $this->availability->deposit ?>" />
										<label for="deposit"><?php esc_html_e('deposit', 'calendarista') ?></label>
										<div id="deposit_error_container"></div>
										<p>
											<input id="enableFullAmountOrDeposit" 
											name="enableFullAmountOrDeposit" 
											type="checkbox" 
											<?php echo $this->availability->enableFullAmountOrDeposit ? 'checked' : '' ?> />
											<?php esc_html_e('Enable paying full amount and give a discount', 'calendarista') ?>
										<input id="fullAmountDiscount" 
											name="fullAmountDiscount" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-min="0"
											data-parsley-pattern="^\d+(\.\d{1,2})?$"
											placeholder="0.00" 
											value="<?php echo $this->availability->fullAmountDiscount ?>" />
											
										</p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_RETURN)):?>
								<tr>
									<td>
										<div><label for="returnOptional"><?php esc_html_e('Return trip', 'calendarista') ?></label></div>
										<p><input id="returnOptional" 
											name="returnOptional" 
											type="checkbox" 
											<?php echo $this->availability->returnOptional ? 'checked' : '' ?> />
											<?php esc_html_e('Return is optional', 'calendarista') ?></p>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)): ?>
										<p>
											<input id="returnSameDay" 
												name="returnSameDay" 
												type="checkbox" 
												<?php echo $this->availability->returnSameDay ? 'checked' : '' ?> />
												<?php esc_html_e('Return on same day', 'calendarista') ?>
										</p>
										<?php endif; ?>
									</td>
								</tr>
								<?php if($this->project->paymentsMode !== -1):?>
								<tr>
									<td>
										<div><label for="returnCost"><?php esc_html_e('Return cost', 'calendarista') ?></label></div>
										<input id="returnCost" 
											name="returnCost" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-min="0"
											data-parsley-pattern="^\d+(\.\d{1,2})?$"
											placeholder="0.00" 
											data-parsley-group="block1"
											value="<?php echo $this->availability->returnCost ?>" />
									</td>
								</tr>
								<?php endif; ?>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, array(Calendarista_CalendarMode::SINGLE_DAY, Calendarista_CalendarMode::PACKAGE))):?>
								<tr>
									<td>
										<div><label for="daysInPackage"><?php esc_html_e('No. of days', 'calendarista') ?></label></div>
										<input id="daysInPackage" 
											name="daysInPackage" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-required="true"
											data-parsley-type="digits"
											data-parsley-min="1"
											value="<?php echo $this->availability->daysInPackage ?>" />
										<?php if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY):?>
										<p class="description"><?php esc_html_e('When a start date is selected, the end date will depend on the No. of days value supplied above.', 'calendarista') ?></p>
										<?php else: ?>
										<p class="description"><?php esc_html_e('Number of days in package. Minimum 1 day', 'calendarista') ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_SEATS) || in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
								<tr>
									<td>
										<div><label for="seats"><?php esc_html_e('Seats', 'calendarista') ?></label></div>
										<input id="seats" 
											name="seats" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											data-parsley-group="block1"
											value="<?php echo $this->availability->seats ?>" />
										<?php if($this->project->calendarMode === Calendarista_CalendarMode::PACKAGE):?>
											<?php esc_html_e('apply to entire package', 'calendarista') ?>
										<?php elseif($this->project->calendarMode === Calendarista_CalendarMode::ROUND_TRIP): ?>
											<?php esc_html_e('apply to the departure and destination day selected', 'calendarista') ?>
										<?php else: ?>
											<?php esc_html_e('apply to each day', 'calendarista') ?>
										<?php endif; ?>
										<p class="description"><?php esc_html_e('A value of 0 means seats are unlimited', 'calendarista') ?></p>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)): ?>
											<p class="description"><?php esc_html_e('Note: If you set seats on the individual time slot, it will override the value set above', 'calendarista')  ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="seatsMinimum"><?php esc_html_e('Seats Minimum', 'calendarista') ?></label></div>
										<input id="seatsMinimum" 
											name="seatsMinimum" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="1"
											data-parsley-type="digits"
											data-parsley-lessthan="#seats"
											data-parsley-group="block1"
											value="<?php echo $this->availability->seatsMinimum ?>" />
										<p class="description"><?php esc_html_e('Force a minimum number of seats required to make a booking.', 'calendarista') ?></p>
									</td>
								</tr>
								<?php else: ?>
								<tr>
									<td>
										<input id="seats" 
										name="seats" 
										type="hidden" 
										value="0" />
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
								<tr>
									<td>
										<label for="selectableSeats">
											<input id="selectableSeats" name="selectableSeats" type="checkbox" 
											<?php echo $this->availability->selectableSeats ? 'checked' : ''?> 
											<?php echo $this->getSelectableSeatCheckedStatus() ?>>
											<?php esc_html_e('Allows customer to select more than one seat (Group booking)', 'calendarista')?>
										</label>
									</td>
								</tr>
								<?php if($this->availability->guestNameRequired):?>
								<tr>
									<td>
										<label for="guestNameRequired">
											<input id="guestNameRequired" name="guestNameRequired" type="checkbox" 
											<?php echo $this->availability->guestNameRequired ? 'checked' : ''?> 
											<?php echo $this->getSelectableSeatCheckedStatus() ?>>
											<?php esc_html_e('For each additional seat selected, get the customer name', 'calendarista')?>
										</label>
										<p class="description" style="color: red">Important: This feature will be discontinued soon. Instead use the custom form fields and check "This is a guest field" option.</p>
									</td>
								</tr>
								<?php endif; ?>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_SEATS) || in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
								<tr>
									<td>
										<div><label for="seatsMaximum"><?php esc_html_e('Seats Maximum', 'calendarista') ?></label></div>
										<input id="seatsMaximum" 
											name="seatsMaximum" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="1"
											data-parsley-type="digits"
											data-parsley-morethan="#seats"
											data-parsley-group="block1"
											value="<?php echo $this->availability->seatsMaximum ?>" />
										<p class="description"><?php esc_html_e('Maximum number of seats selectable when group booking is enabled. 0 means no limit.', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_TIMESLOT_SELECTION)):?>
								<tr>
									<td>
										<label for="maxTimeslots">
											<input type="text" 
													id="maxTimeslots"
													name="maxTimeslots"
													class="small-text" 
													placeholder="1"
													value="<?php echo $this->availability->maxTimeslots ?>"
													data-parsley-min="1"
													data-parsley-type="digits" 
													data-parsley-trigger="change"
													data-parsley-errors-container="#max_timeslots_error_container"/> 
											<?php esc_html_e('time slot(s) can be selected at the same time.', 'calendarista') ?>
											<?php if($this->project->paymentsMode !== -1): ?>
											<?php esc_html_e('If less timeslots are selected then charge the fixed cost of', 'calendarista') ?>
											<input type="text" 
													id="minimumTimeslotCharge"
													name="minimumTimeslotCharge"
													class="small-text" 
													value="<?php echo $this->availability->minimumTimeslotCharge ?>"
													data-parsley-trigger="change focusout"
													data-parsley-min="0"
													data-parsley-pattern="^\d+(\.\d{1,2})?$"
													data-parsley-errors-container="#minimum_timeslot_charge_error_container"
													placeholder="0.00" 
													<?php echo $this->availability->maxTimeslots <= 1 ? 'disabled' : '' ?> /> 
										</label>
										<div id="minimum_timeslot_charge_error_container"></div>
										<?php endif; ?>
										<div id="max_timeslots_error_container"></div>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SINGLE_DAY_EVENT)): ?>
								<tr>
									<td>
										<div><label><?php esc_html_e('Recurring Appointments', 'calendarista') ?></label></div>
										<p class="description"><?php esc_html_e('Allow customer to repeat appointment every:', 'calendarista')?></p>
										<div>
										<label for="maxDailyRepeatFrequency"><?php esc_html_e('Day', 'calendarista') ?></label>&nbsp;
										<input id="maxDailyRepeatFrequency" 
											name="maxDailyRepeatFrequency" 
											type="checkbox" 
											class="calendarista-repeat-options"
											data-parsley-trigger="change focusout"
											<?php echo $this->availability->maxDailyRepeatFrequency ? 'checked' : '' ?> />
										<label for="maxWeeklyRepeatFrequency"><?php esc_html_e('Week', 'calendarista') ?></label>
										<input id="maxWeeklyRepeatFrequency" 
											name="maxWeeklyRepeatFrequency" 
											type="checkbox" 
											class="calendarista-repeat-options"
											data-parsley-trigger="change focusout"
											<?php echo $this->availability->maxWeeklyRepeatFrequency ? 'checked' : '' ?> /> 
										<label for="maxMonthlyRepeatFrequency"><?php esc_html_e('Month', 'calendarista') ?></label>
										<input id="maxMonthlyRepeatFrequency" 
											name="maxMonthlyRepeatFrequency" 
											type="checkbox" 
											class="calendarista-repeat-options"
											data-parsley-trigger="change focusout"
											<?php echo $this->availability->maxMonthlyRepeatFrequency ? 'checked' : '' ?> />
										<label for="maxYearlyRepeatFrequency"><?php esc_html_e('Year', 'calendarista') ?></label>
										<input id="maxYearlyRepeatFrequency" 
											name="maxYearlyRepeatFrequency" 
											type="checkbox" 
											class="calendarista-repeat-options"
											data-parsley-trigger="change focusout"
											<?php echo $this->availability->maxYearlyRepeatFrequency ? 'checked' : '' ?> />
										</div>
										<br>
										<div>
										<label for="maxRepeatFrequency"><?php esc_html_e('Max Repeat Frequency', 'calendarista') ?></label>
										<input id="maxRepeatFrequency" 
											name="maxRepeatFrequency" 
											type="text" 
											class="small-text"
											data-parsley-isnotempty="input.calendarista-repeat-options:checked"
											data-parsley-trigger="change focusout"
											data-parsley-type="digits"
											data-parsley-errors-container="#repeat_error_container"
											data-parsley-error-message="<?php esc_html_e('A value greater than 0 is required for Max Repeat Frequency', 'calendarista') ?>"
											value="<?php echo $this->availability->maxRepeatFrequency ?>" />
										<label for="maxRepeatOccurrence"><?php esc_html_e('Max Occurrence', 'calendarista') ?></label>
										<input id="maxRepeatOccurrence" 
											name="maxRepeatOccurrence" 
											type="text" 
											class="small-text"
											data-parsley-isnotempty="input.calendarista-repeat-options:checked"
											data-parsley-trigger="change focusout"
											data-parsley-type="digits"
											data-parsley-errors-container="#repeat_error_container"
											data-parsley-error-message="<?php esc_html_e('A value greater than 0 is required for Max Occurrence', 'calendarista') ?>"
											value="<?php echo $this->availability->maxRepeatOccurrence ?>" />
										</div>
										<div id="repeat_error_container"></div>
										<hr>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, array(
															Calendarista_CalendarMode::MULTI_DATE_RANGE
															, Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE
															, Calendarista_CalendarMode::CHANGEOVER
															, Calendarista_CalendarMode::MULTI_DATE
															, Calendarista_CalendarMode::MULTI_DATE_AND_TIME))):?>
								<tr>
									<td>
										<div><label for="bookingDaysMinimum"><?php esc_html_e('Min days', 'calendarista') ?></label></div>
										<input id="bookingDaysMinimum" 
											name="bookingDaysMinimum" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											value="<?php echo $this->availability->bookingDaysMinimum ?>" />&nbsp;<?php esc_html_e('day(s)', 'calendarista') ?>
											<p class="description"><?php esc_html_e('Minimum  days bookable at a time', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="bookingDaysMaximum"><?php esc_html_e('Max days', 'calendarista') ?></label></div>
										<input id="bookingDaysMaximum" 
											name="bookingDaysMaximum" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											value="<?php echo $this->availability->bookingDaysMaximum ?>" />&nbsp;<?php esc_html_e('day(s)', 'calendarista') ?>
											<p class="description"><?php esc_html_e('Maximum days bookable at a time', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<label for="displayDateSelectionReq">
											<input id="displayDateSelectionReq" name="displayDateSelectionReq" type="checkbox" 
											<?php echo $this->availability->displayDateSelectionReq ? 'checked' : ''?>>
											<?php esc_html_e('Display message to indicate min/max days requirement', 'calendarista')?>
										</label>
									</td>
								</tr>
								<?php else: ?>
								<input id="bookingDaysMinimum" 
											name="bookingDaysMinimum" type="hidden" value="0">
								<input id="bookingDaysMaximum" 
											name="bookingDaysMaximum" type="hidden" value="0">
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TURNOVER)):?>
								<tr>
									<td>
										<div><label for="turnoverBefore"><?php esc_html_e('Turnover days', 'calendarista') ?></label></div>
										<?php esc_html_e('before', 'calendarista') ?>
										<input id="turnoverBefore" 
											name="turnoverBefore" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											data-parsley-errors-container="#turnover_error_container"
											value="<?php echo $this->availability->turnoverBefore ?>" />&nbsp;<?php esc_html_e('and', 'calendarista') ?>
											<input id="turnoverAfter" 
											name="turnoverAfter" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											data-parsley-errors-container="#turnover_error_container"
											value="<?php echo $this->availability->turnoverAfter ?>" />
											&nbsp;<?php esc_html_e('after', 'calendarista') ?>
											<div id="turnover_error_container"></div>
										<p class="description"><?php esc_html_e('Prep time (in days) needed before and after the next booking', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS) && 
											$this->project->calendarMode !== Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_WITH_PADDING): ?>
								<tr>
									<td>
										<div><label for="turnoverBeforeMin"><?php esc_html_e('Turnover in minutes', 'calendarista') ?></label></div>
										<?php esc_html_e('before', 'calendarista') ?>
										<input id="turnoverBeforeMin" 
											name="turnoverBeforeMin" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											min="0"
											max="1440"
											data-parsley-type="digits"
											data-parsley-errors-container="#turnover_error_container2"
											value="<?php echo $this->availability->turnoverBeforeMin ?>" />&nbsp;<?php esc_html_e('and', 'calendarista') ?>
											<input id="turnoverAfterMin" 
											name="turnoverAfterMin" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											min="0"
											max="1440"
											data-parsley-type="digits"
											data-parsley-errors-container="#turnover_error_container2"
											value="<?php echo $this->availability->turnoverAfterMin ?>" />
											&nbsp;<?php esc_html_e('after', 'calendarista') ?>
											<div id="turnover_error_container2"></div>
										<p class="description"><?php esc_html_e('Prep time (in minutes) needed before and after the next booking', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_NOTICE)):?>
								<tr>
									<td>
										<div><label for="minimumNotice"><?php esc_html_e('Notice', 'calendarista') ?></label></div>
										<?php esc_html_e('Min', 'calendarista') ?>
										<input id="minimumNotice" 
											name="minimumNotice" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											data-parsley-errors-container="#notice_error_container"
											value="<?php echo $this->availability->minimumNotice ?>" />&nbsp;<?php esc_html_e('and', 'calendarista') ?>
											<input id="maximumNotice" 
											name="maximumNotice" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											data-parsley-errors-container="#notice_error_container"
											value="<?php echo $this->availability->maximumNotice ?>" />
											<?php esc_html_e('max', 'calendarista') ?>
										<div id="notice_error_container"></div>
										<p class="description"><?php esc_html_e('Restrict (in days) from booking too soon or too late into the future', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)):?>
								<tr>
									<td>
										<div><label for="minimumNoticeMinutes"><?php esc_html_e('Minimum notice in minutes', 'calendarista') ?></label></div>
										<input id="minimumNoticeMinutes" 
											name="minimumNoticeMinutes" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-required="true"
											data-parsley-type="digits"
											value="<?php echo $this->availability->minimumNoticeMinutes ?>" /> (<?php esc_html_e('Minutes', 'calendarista')?>)
										<p class="description"><?php esc_html_e('Applies only to the current time. Eg: If the current time is 08:00am a 60min notice will not allow booking before 09:00am', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIME_RANGE)):?>
								<tr>
									<td>
										<div><label for="minTime"><?php esc_html_e('Minimum time bookable', 'calendarista') ?></label></div>
										<input id="minTime" 
											name="minTime" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-required="true"
											data-parsley-type="digits"
											value="<?php echo $this->availability->minTime ?>" /> (<?php esc_html_e('Minutes', 'calendarista')?>)
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="maxTime"><?php esc_html_e('Maximum time bookable', 'calendarista') ?></label></div>
										<input id="maxTime" 
											name="maxTime" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-required="true"
											data-parsley-type="digits"
											value="<?php echo $this->availability->maxTime ?>" /> (<?php esc_html_e('Minutes', 'calendarista')?>)
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
								<tr>
									<td>
									<input id="displayRemainingSeats" name="displayRemainingSeats" 
											type="checkbox" <?php echo $this->availability->displayRemainingSeats ? "checked" : ""?> /> 
										<?php esc_html_e('Display remaining seats alongside timeslot', 'calendarista')?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_SEATS) && !in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)):?>
								<tr>
									<td>
									<input id="displayRemainingSeatsMessage" name="displayRemainingSeatsMessage" 
											type="checkbox" <?php echo $this->availability->displayRemainingSeatsMessage ? "checked" : ""?> /> 
										<?php esc_html_e('Display remaining seats message box', 'calendarista')?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_END_DATE)): ?>
								<tr>
									<td>
										<div><label for="checkinWeekdayList"><?php esc_html_e('Checkin', 'calendarista') ?></label></div>
										<fieldset>
											<legend class="screen-reader-text"><span><?php esc_html_e('Checkin', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<?php foreach($this->weekdays as $key=>$value):?>
												<li>
													<label for="<?php echo $value ?>">
														<input 
															id="checkin_<?php echo $value ?>"
															name="checkinWeekdayList[]" 
															value="<?php echo $key ?>"
															type="checkbox" 
															data-parsley-maxcheck="6"
															data-parsley-errors-container="#checkin_weekdays_error_message"
															<?php echo $this->checkinWeekdayChecked((int)$key); ?> />
															<?php echo $value ?>
													</label>
												</li>
												<?php endforeach;?>
											</ul>
											<p class="description"><?php esc_html_e('Check-in weekday(s)', 'calendarista') ?></p>
											<div id="checkin_weekdays_error_message"></div>
										</fieldset>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="checkoutWeekdayList"><?php esc_html_e('Checkout', 'calendarista') ?></label></div>
										<fieldset>
											<legend class="screen-reader-text"><span><?php esc_html_e('Checkout', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<?php foreach($this->weekdays as $key=>$value):?>
												<li>
													<label for="<?php echo $value ?>">
														<input 
															id="checkout_<?php echo $value ?>"
															name="checkoutWeekdayList[]" 
															value="<?php echo $key ?>"
															type="checkbox"  
															data-parsley-maxcheck="6"
															data-parsley-errors-container="#checkout_weekdays_error_message"
															<?php echo $this->checkoutWeekdayChecked((int)$key); ?> />
															<?php echo $value ?>
													</label>
												</li>
												<?php endforeach;?>
											</ul>
											<p class="description"><?php esc_html_e('Check-out weekday(s)', 'calendarista') ?></p>
											<div id="checkout_weekdays_error_message"></div>
										</fieldset>
									</td>
								</tr>
								<?php endif;?>
								<tr>
									<td>
										<div><label for="color"><?php esc_html_e('Color', 'calendarista') ?></label></div>
										<input id="color" 
											name="color" 
											type="text" 
											class="regular-text" 
											placeholder="#000" 
											value="<?php echo $this->availability->color ?>" />
										<p class="description"><?php esc_html_e('Appointments made will have this color (in the appointments calendar)', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="imageUrl"><?php esc_html_e('Image URL', 'calendarista') ?></label></div>
										<input name="imageUrl" type="hidden" 
											value="<?php echo esc_url($this->availability->imageUrl) ?>" />
										<div data-calendarista-preview-icon="imageUrl" class="preview-icon" 
											style="<?php echo $this->availability->imageUrl ?
																sprintf('background-image: url(%s)', esc_url($this->availability->imageUrl)) : ''?>">
										</div>
										<button
											type="button"
											name="iconUrlRemove"
											data-calendarista-preview-icon="imageUrl"
											class="button button-primary remove-image" 
											title="<?php __('Remove image', 'calendarista')?>">
											<i class="fa fa-remove"></i>
										</button>
										<p class="description"><?php esc_html_e('An image to display in the wizard when this availability is active or as a thumbnail when using multiple availability. Note: A good size for thumbnail view is 200 - 300 pixels.', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="description"><?php esc_html_e('Description', 'calendarista') ?></label></div>
										<textarea type="text" 
												class="large-text"
												name="description"
												rows="3"
												id="description"><?php echo esc_html(Calendarista_StringResourceHelper::decodeString($this->availability->description)) ?></textarea>
										<p class="description"><?php esc_html_e('A description to display in the wizard and search result or when thumbnail view is enabled.', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="instructions"><?php esc_html_e('Instructions', 'calendarista') ?></label></div>
										<textarea type="text" 
												class="large-text"
												name="instructions"
												rows="3"
												id="instructions"><?php echo esc_html(Calendarista_StringResourceHelper::decodeString($this->availability->instructions)) ?></textarea>
										<p class="description"><?php esc_html_e('These instructions will be part of the appointment when the customer adds it to their calendar. Example, make sure to arrive at the venue 1h before the appointment etc', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="searchThumbnailUrl"><?php esc_html_e('Search result image URL', 'calendarista') ?></label></div>
										<input name="searchThumbnailUrl" type="hidden" 
											value="<?php echo esc_url($this->availability->searchThumbnailUrl) ?>" />
										<div data-calendarista-preview-icon="searchThumbnailUrl" class="preview-icon" 
											style="<?php echo $this->availability->searchThumbnailUrl ?
																sprintf('background-image: url(%s)', esc_url($this->availability->searchThumbnailUrl)) : ''?>">
										</div>
										<button
											type="button"
											name="iconUrlRemove"
											data-calendarista-preview-icon="searchThumbnailUrl"
											class="button button-primary remove-image" 
											title="<?php __('Remove image', 'calendarista')?>">
											<i class="fa fa-remove"></i>
										</button>
										<p class="description"><?php esc_html_e('A thumbnail image to display in the search result. Hint, a good size to use: 64x64px', 'calendarista')?></p>
									</td>
								</tr>
								<?php if(in_array($this->project->calendarMode, array(Calendarista_CalendarMode::MULTI_DATE_RANGE))): ?>
								<tr>
									<td>
										<fieldset>
											<legend><span><?php esc_html_e('Days count mode', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<li>
													<label>
														<input name="dayCountMode" value="0" type="radio" <?php echo !$this->availability->dayCountMode ? 'checked' : ''?>>
														<?php esc_html_e('Standard', 'calendarista')?>
													</label>
												</li>
												<li>
													<label>
														<input name="dayCountMode" value="1" type="radio" <?php echo $this->availability->dayCountMode ? 'checked' : ''?>>
														<?php esc_html_e('Difference', 'calendarista')?>
													</label>
												</li>
											</ul>
										</fieldset>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<fieldset>
											<legend><span><?php esc_html_e('How often are you available for booking?', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<li>
													<label for="hasRepeat">
														<input id="hasRepeat" name="hasRepeat" type="checkbox" <?php echo $this->availability->hasRepeat ? 'checked' : ''?>>
														<?php esc_html_e('Repeat', 'calendarista')?>
													</label>
												</li>
												<li>
													<label for="editRepeat">
														<a href="#" id="editRepeat">
															<?php esc_html_e('Edit', 'calendarista')?>
														</a>
													</label>
												</li>
											</ul>
											<p class="description" id="repeatModeSummary"></p>
										</fieldset>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<?php if(!$this->createNew):?>
						<div class="calendarista-borderless-accordion">
							<div id="syncronize">
								<h3><?php esc_html_e('Synchronize', 'calendarista') ?></h3>
								<div>
									<?php if($this->syncAvailabilities && count($this->syncAvailabilities) > 0): ?>
									<?php foreach($this->syncAvailabilities as $availabilities): ?>
										<?php if(count($availabilities) === 1 && $this->availability->id === $availabilities[0]->id){continue;}?>
										<p><?php echo $this->getProjectNameById($availabilities[0]->projectId) ?></p>
										<?php foreach($availabilities as $availability): ?>
										<?php if($this->availability->id === $availability->id){continue;}?>
										<p>
											<input type="checkbox" name="syncList[]" <?php echo in_array($availability->id, $this->availability->syncList) ? 'checked' : '' ?> value="<?php echo $availability->id ?>">&nbsp;<?php echo $availability->name?>
										</p>
										<?php endforeach; ?>
									<?php endforeach; ?>
									<?php else: ?>
									<p class="description"><?php esc_html_e('No services to synchronize.', 'calendarista') ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<?php if($this->selectedProjectId !== -1):?>
						<p class="submit">
							<?php if(!$this->createNew):?>
							<input type="submit" name="calendarista_new_availability" id="calendarista_new_availability" class="button" value="<?php esc_html_e('New', 'calendarista') ?>">
							<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button" value="<?php esc_html_e('Delete', 'calendarista') ?>">
							<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary block1" value="<?php esc_html_e('Save changes', 'calendarista') ?>">
							<?php else:?>
							<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary block1" value="<?php esc_html_e('Create new', 'calendarista') ?>">
							<?php endif;?>
						</p>
						<?php endif;?>
					</form>
				</div>
				<div id="repeat_dialog" title="<?php esc_html_e('Repeat', 'calendarista')?>">
					<form id="calendarista_form2" action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-excluded="[disabled=disabled]" data-parsley-validate>
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<div><label for="repeatFrequency"><?php esc_html_e('Repeat Frequency', 'calendarista')?></label></div>
										<select id="repeatFrequency" name="repeatFrequency">
											<?php foreach($this->repeatFrequency as $key=>$value):?>
											<?php if($key === 0){ continue; }?>
											<option value="<?php echo $key?>" <?php echo $this->repeatFrequencySelected($key)?>>
												<?php echo $value?>
											</option>
											<?php endforeach;?>
										</select>
									</td>
								</tr>
								<tr id="repeatIntervalRow">
									<td>
										<div><label for="repeatInterval"><?php esc_html_e('Repeat every', 'calendarista') ?></label></div>
										<select id="repeatInterval" name="repeatInterval">
											<?php for($i = 1; $i < 31; $i++):?>
												<option value="<?php echo $i?>" <?php echo $this->repeatIntervalSelected($i)?>><?php echo $i?></option>
											<?php endfor;?>
										</select> <span id="repeatIntervalLabel"><?php echo $this->getRepeatIntervalLabel();?></span>
									</td>
								</tr>
								<tr id="repeatWeekdayListRow">
									<td>
										<div><label for="repeatWeekdayList"><?php esc_html_e('Repeat week days', 'calendarista') ?></label></div>
										<fieldset>
											<legend class="screen-reader-text"><span><?php esc_html_e('Repeat week days', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<?php foreach($this->weekdays as $key=>$value):?>
												<li>
													<label for="<?php echo $value ?>">
														<input 
															id="<?php echo $value ?>"
															name="repeatWeekdayList[]" 
															value="<?php echo $key ?>"
															type="checkbox"  
															<?php if((int)$key === 7):?>
															data-parsley-required="true"
															data-parsley-group="block2"
															data-parsley-errors-container="#repeat_weekday_error_container" 
															<?php endif;?>
															<?php echo $this->repeatWeekdayChecked((int)$key); ?> />
															<?php echo $value ?>
													</label>
												</li>
												<?php endforeach;?>
											</ul>
											<div id="repeat_weekday_error_container"></div>
										</fieldset>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="startDate"><?php esc_html_e('Starts on', 'calendarista') ?></label></div>
										<input id="startDate" 
											name="startDate" 
											type="text" 
											class="regular-text enable-readonly-input" 
											readonly
											value="<?php echo $this->getAvailableDate(); ?>"
aria-label="<?php esc_html_e('Please note: page up/down for previous/next month, ctrl plus page up/down for previous/next year, ctrl plus left/right for previous/next day, enter key to accept the selected date', 'calendarista') ?>"											/>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="never"><?php esc_html_e('Terminates on', 'calendarista') ?></label></div>
										<fieldset>
											<ul class="terminates-on">
												<li>
													<label for="never">
														<input id="never" name="terminateMode" type="radio" value="0" 
														<?php echo $this->terminateModeChecked(0);?>>
														<?php esc_html_e('Never', 'calendarista')?>
													</label>
												</li>
												<li>
													<label for="after_occurrence">
														<input id="after_occurrence" name="terminateMode" type="radio" value="1" 
														<?php echo $this->terminateModeChecked(1);?>>
														<?php esc_html_e('After', 'calendarista')?>
														<input id="terminateAfterOccurrence" 
															name="terminateAfterOccurrence" 
															type="text" 
															class="small-text"
															data-parsley-trigger="change focusout"
															placeholder="0"
															data-parsley-type="digits"
															data-parsley-errors-container="#occurrence_error_message"
															data-parsley-group="block2"
															 <?php echo $this->terminateModeStatus(1);?>
															value="<?php echo $this->getTerminateAfterOccurance(); ?>" />
															<span class="example"><?php esc_html_e('occurrence', 'calendarista')?></span>
													</label>
													<div id="occurrence_error_message"></div>
												</li>
												<li>
													<label for="on_end_date">
														<input id="on_end_date" name="terminateMode" type="radio" value="2" 
														<?php echo $this->terminateModeChecked(2);?>>
														<?php esc_html_e('On date', 'calendarista')?>
														<input id="endDate" 
															name="endDate" 
															type="text"
															class="regular-text enable-readonly-input"
															data-parsley-errors-container="#enddate_error_message"
															data-parsley-group="block2"
															readonly
															 <?php echo $this->terminateModeStatus(2);?>
															value="<?php echo $this->getEndDate(); ?>"
															aria-label="<?php esc_html_e('Please note: page up/down for previous/next month, ctrl plus page up/down for previous/next year, ctrl plus left/right for previous/next day, enter key to accept the selected date', 'calendarista') ?>"/>
													</label>
													<div id="enddate_error_message"></div>
												</li>
											</ul>
										</fieldset>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="summary"><?php esc_html_e('Summary', 'calendarista') ?></label></div>
										<p class="description" id="summary"></p>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right calendarista-widgets-right">
			<div id="widgets-right">
				<div class="single-sidebar">
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables ui-droppable ui-sortable">
							<div class="sidebar-name">
								<h3><?php esc_html_e('Availabilities', 'calendarista') ?></h3>
							</div>
							<?php if($this->availabilities->count() > 0):?>
							<form id="calendarista_form3" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
								<input type="hidden" name="controller" value="availability" />
								<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
								<input type="hidden" name="sortOrder" />
								<div class="widgets-sortables ui-droppable ui-sortable">	
									<div class="sidebar-description">
										<p class="description">
											<?php esc_html_e('List of availabilities below. Drag and drop header to rearrange the order.', 'calendarista')?>
										</p>
									</div>
									<div class="accordion-container availability-items">
										<ul class="outer-border">
										<?php foreach($this->availabilities as $availability):?>
											<li class="control-section accordion-section">
												<h3 class="accordion-section-title <?php echo $this->availability->id === $availability->id ? 'calendarista-accordion-selected' : '' ?>" tabindex="0">
													<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
													<input id="checkbox_<?php echo $availability->id ?>" title="#<?php echo $availability->id ?>" type="checkbox" name="availabilities[]" value="<?php echo $availability->id ?>"> 
													<span title="<?php echo $this->getAvailabilityTitle($availability) ?>">
														<?php echo $this->getAvailabilityName($availability) ?>
													</span> 
													<button type="submit" 
														class="edit-linkbutton alignright" 
														name="calendarista_edit" 
														value="<?php echo $availability->id; ?>">
														<?php esc_html_e('Edit', 'calendarista') ?>
													</button>
												</h3>
											</li>
										<?php endforeach;?>
									</div>
								</div>
								<p class="submit">
									<button type="submit" name="calendarista_delete" class="button button-primary delete-availabilities" disabled><?php esc_html_e('Delete', 'calendarista') ?></button>
									<input type="submit" 
											name="calendarista_sortorder" 
											id="calendarista_sortorder" 
											class="button button-primary sort-button" 
											title="<?php esc_html_e('Save sort order', 'calendarista')?>" 
											value="<?php esc_html_e('Save order', 'calendarista') ?>" disabled>
								</p>
							</form>
							<?php else:?>
							<hr>
							<div>
								<?php esc_html_e('No availabilities found.', 'calendarista')?>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<?php if($this->selectedId !== -1): ?>
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables ui-droppable ui-sortable">
							<div class="sidebar-name">
								<h3><?php esc_html_e('Days available', 'calendarista') ?></h3>
							</div>
							<?php if($this->project->calendarMode === Calendarista_CalendarMode::PACKAGE):?>
								<p class="description"><?php esc_html_e('If adding individual days, ensure number of days in package is always 1', 'calendarista') ?></p>
							<?php endif; ?>
							<?php new Calendarista_AvailabilityDayTemplate($this->selectedProjectId, $this->availability->id); ?>
						</div>
					</div>
					<?php endif; ?>
					<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PRICING_SCHEME)):?>
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables ui-droppable ui-sortable">
							<div class="sidebar-name">
								<h3><?php esc_html_e('Pricing scheme x day', 'calendarista') ?></h3>
							</div>
							<?php if(!$this->createNew): ?>
								<?php new Calendarista_PricingSchemeTemplate($this->availability->id);?>
							<?php else: ?>
								<p class="description"><?php esc_html_e('Create or select an existing availability to setup a pricing scheme', 'calendarista') ?></p>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>
					<?php if(!$this->createNew): ?>
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables ui-droppable ui-sortable">
							<div class="sidebar-name">
								<h3><?php esc_html_e('Search attributes', 'calendarista') ?></h3>
							</div>
								<form id="calendarista_form4" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
									<input type="hidden" name="controller" value="availability" />
									<input type="hidden" name="availabilityId" value="<?php echo $this->availability->id ?>" />
									<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
									<div>
										<span id="spinner_update_tag_list" class="calendarista-spinner calendarista-invisible">
											<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
										</span>
									</div>
									<div id="calendarista_tag_list"  class="table-responsive">
										<?php $this->tagList->printVariables() ?>
										<?php $this->tagList->display(); ?>
									</div>
									<p class="submit">
										<input type="button" 
												name="calendarista_tag" 
												id="calendarista_tag" 
												class="button button-primary" 
												title="<?php esc_html_e('Save search attributes', 'calendarista')?>" 
												value="<?php esc_html_e('Save', 'calendarista') ?>" disabled>
									</p>
							</form>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<div id="liveregion" role="log" aria-live="assertive" aria-atomic="true" aria-relevant="additions" class="sr-only"></div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.availability = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
					window.Parsley.addValidator('cal-hasval', {
					  validateString: function (value, requirement) {
						if(parseFloat(value) > 0 && parseRequirement(requirement) == 0){
							return false;
						}
						return true;
					  },
					  priority: 32
					});
					window.Parsley.addValidator('isnotempty', {
					  validateString: function (value, requirement) {
						var flag1 = $(requirement).length > 0
							, newValue = parseInt($.trim(value), 10);
						if(flag1 && (!isNaN(newValue) && newValue == 0)){
							return false;
						}
						return true;
					  },
					  priority: 32
					});
				});
			};
			calendarista.availability.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.OKButtonFlag = false;
				this.dateTimepickerOptions = {
					'timeFormat': 'HH:mm'
					, 'dateFormat': 'yy-mm-dd'
				};
				this.selectedId = options['selectedId'];
				this.requestUrl = options['requestUrl'];
				this.daysLabelText = options['daysLabelText'];
				this.weeksLabelText = options['weeksLabelText'];
				this.monthsLabelText = options['monthsLabelText'];
				this.yearsLabelText = options['yearsLabelText'];
				this.everyDaySummary =  options['everyDaySummary'];
				this.everyWeekdaySummary =  options['everyWeekdaySummary'];
				this.everyWeekMo_We_Fr =  options['everyWeekMo_We_Fr'];
				this.everyWeekTu_Th =  options['everyWeekTu_Th'];
				this.everyWeekOn =  options['everyWeekOn'];
				this.everyMonth = options['everyMonth'];
				this.everyYear = options['everyYear'];
				this.everyDayOfTheWeek = options['everyDayOfTheWeek'];
				this.occurrenceTimes = options['occurrenceTimes'];
				this.until = options['until'];
				this.justOnce = options['justOnce'];
				this.su =  options['su'];
				this.mo =  options['mo'];
				this.tu =  options['tu'];
				this.we =  options['we'];
				this.th =  options['th'];
				this.fr =  options['fr'];
				this.sa =  options['sa'];
				this.availability = options['availability'];
				this.actionGetTagList = 'calendarista_get_tag_list';
				this.actionSaveTagList = 'calendarista_save_tag_list';
				this.$form1 = $('#calendarista_form1');
				this.$form2 = $('#calendarista_form2');
				this.$formSubmitButton = $('input.button.block1');
				this.$hasRepeatCheckbox = $('input[name="hasRepeat"]');
				this.$color = $('input[name="color"]');
				this.$repeatModeSummary = $('#repeatModeSummary');
				this.$editRepeat = $('#editRepeat');
				this.$availableDateTextbox = $('input[name="availableDate"]');
				this.$maxTimeslotsTextbox = $('input[name="maxTimeslots"]');
				this.$minimumTimeslotCharge = $('input[name="minimumTimeslotCharge"]');
				this.$seats = $('input[name="seats"]');
				this.$seatsMaximum = $('input[name="seatsMaximum"]');
				this.$seatsMinimum = $('input[name="seatsMinimum"]');
				this.$selectableSeatsCheckbox = $('input[name="selectableSeats"]');
				this.$returnSameDay = $('input[name="returnSameDay" ]');
				this.$startDateTextbox = $('input[name="startDate"]');
				this.$endDateTextbox = $('input[name="endDate"]');
				this.$repeatFrequencySelectList = $('select[name="repeatFrequency"]');
				this.$repeatIntervalRow = $('#repeatIntervalRow');
				this.$repeatIntervalSelectList = $('select[name="repeatInterval"]');
				this.$repeatIntervalLabel = $('#repeatIntervalLabel');
				this.$repeatWeekdayListRow = $('#repeatWeekdayListRow');
				this.$repeatWeekdayCheckboxList = this.$repeatWeekdayListRow.find('input[type="checkbox"]');
				this.$terminateAfterOccurrenceTextbox = $('input[name="terminateAfterOccurrence"]');
				this.$terminateModeCheckboxList = $('input[name="terminateMode"]');
				this.$summary = $('#summary'); 
				this.$availabilityCheckboxes = $('.availability-items input[type="checkbox"]');
				this.$availabilityDeleteButton = $('.delete-availabilities');
				this.$checkinWeekdayList = $('input[name="checkinWeekdayList[]"]');
				this.$checkoutWeekdayList = $('input[name="checkoutWeekdayList[]"]');
				this.$availabilityItemInputFields = $('.availability-items input[type="checkbox"], .availability-items button[type="submit"], .availability-items a');
				this.$availabilityItems = $('.accordion-container.availability-items ul>li');
				this.$sortOrder = $('input[name="sortOrder"]');
				this.$sortOrderButton = $('input[name="calendarista_sortorder"]');
				this.$tagCheckboxes = $('input[name="tags[]"]');
				this.$tagAllCheck = $('input[name="selectall"]');
				this.checkedAllDelegate = calendarista.createDelegate(this, this.checkedAll);
				this.$tagCheckboxes.on('change', this.checkedAllDelegate);
				this.tagCheckAllDelegate = calendarista.createDelegate(this, this.tagsCheckall);
				this.$tagAllCheck.on('change', this.tagCheckAllDelegate);
				this.$tagSaveButton = $('input[name="calendarista_tag"]');
				this.$editAvailabilityButton = $('button[name="calendarista_edit"]');
				this.$ariaLogDelegate = calendarista.createDelegate(this, this.ariaLog);
				this.$form3 = $('#calendarista_form3');
				this.$editAvailabilityButton.on('keydown', function(e){
					if (e.keyCode == 13) {
						context.$form3.submit();
					}
				});
				this.$tagSaveButton.on('click', function(){
					context.saveTagListRequest();
					return false;
				});
				this.$tagList = $('#calendarista_tag_list');
				this.ajax1 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'update_tag_list'});
				this.pagerButtonDelegates();
				$('.accordion-container.availability-items ul').accordion({
				  collapsible: false
				   , active: null
				}).sortable({
					axis: 'y'
					, handle: '.calendarista-drag-handle'
					, stop: function( event, ui ) {
						var $this = $(this);
						context.updateSortOrder();
					  // IE doesn't register the blur when sorting
					  // so trigger focusout handlers to remove .ui-state-focus
					  ui.item.children('h3').triggerHandler('focusout');
					  // Refresh accordion to handle new order
					  $this.accordion('refresh');
					  $this.accordion({active: ui.item.index()});
					}
				});
				this.$availabilityItemInputFields.on('click', function(e){
					e.stopPropagation();
				});
				$('#syncronize').accordion({
					collapsible: true
					, active: false
					, heightStyle: 'content'
					, autoHeight: false
					, clearStyle: true
				});
				this.$checkinWeekdayList.on('change', function(e){
					//mutuallyExclusive discontinued
					//context.mutuallyExclusive($(this), 'checkoutWeekdayList[]');
				});
				this.$checkoutWeekdayList.on('change', function(e){
					//mutuallyExclusive discontinued
					//context.mutuallyExclusive($(this), 'checkinWeekdayList[]');
				});
				new Calendarista.imageSelector({'id': '#calendarista_form1', 'previewImageUrl': options['previewImageUrl']});
				this.terminateModeChangedDelegate = calendarista.createDelegate(this, this.terminateModeChanged);
				this.repeatFrequencyChangedDelegate = calendarista.createDelegate(this, this.repeatFrequencyChanged);
				this.$hasRepeatCheckbox.on('change', function(e){
					context.$repeatDialog.dialog('open');
					context.setSummary();
				});
				this.$editRepeat.on('click', function(e){
					e.preventDefault();
					context.$repeatDialog.dialog('open');
					context.setSummary();
				});
				this.$repeatWeekdayCheckboxList.on('change', function(e){
					context.setSummary();
				});
				this.$repeatIntervalSelectList.on('change', function(e){
					context.setSummary();
				});
				this.$terminateAfterOccurrenceTextbox.on('keyup', function(e){
					context.setSummary();
				});
				this.$endDateTextbox.on('change', function(e){
					context.setSummary();
				});
				this.$repeatFrequencySelectList.on('change', this.repeatFrequencyChangedDelegate)
				this.$terminateModeCheckboxList.on('change', this.terminateModeChangedDelegate);
				this.$formSubmitButton.on('click', function(e){
					if (!context.$form1.parsley().validate({'group': 'block1', 'force': false})){
						 e.preventDefault();
						 return false;
					 }
				});
				this.$repeatDialog = $('#repeat_dialog').dialog({
					autoOpen: false
					, height: 'auto'
					, width: 'auto'
					, modal: true
					, resizable: false
					, dialogClass: 'calendarista-dialog'
					, buttons: [
						{
							'class': 'block2'
							, 'text': '<?php echo $this->decodeString(__("Ok", "calendarista")) ?>'
							, 'click': function() {
								 if (!context.$form2.parsley().validate({'group': 'block2', 'force': true})){
									 return;
								 }
								context.OKButtonFlag = true;
								if(context.$repeatDialog){
									context.$repeatDialog.dialog('close');
								}
								context.setSummary();
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__("Cancel", "calendarista")) ?>'
							, 'click': function() {
								context.OKButtonFlag = false;
								context.repeatDialogCancel();
							}
						}
					]
					, close: function(e) {
						if(!context.OKButtonFlag){
							context.repeatDialogCancel();
						}
						context.OKButtonFlag = false;
					}
				});
				this.$availabilityCheckboxes.on('change', function(e){
					context.deleteAvailabilityButtonState();
				});
				this.$color.wpColorPicker();
				this.$liveAriaRegion = $('#liveregion');
				this.$availableDateTextbox.datetimepicker(this.dateTimepickerOptions).on('keydown', this.ariaLogDelegate);
				this.$startDateTextbox.datetimepicker(this.dateTimepickerOptions).on('keydown', this.ariaLogDelegate);
				this.$endDateTextbox.datetimepicker(this.dateTimepickerOptions).on('keydown', this.ariaLogDelegate);
				this.$availableDateTextbox.on('change', function(e){
					var d = context.$availableDateTextbox.val();
					context.$startDateTextbox.val(d);
				});
				this.$startDateTextbox.on('change', function(e){
					var d = context.$startDateTextbox.val();
					context.$availableDateTextbox.val(d);
				});
				this.$form1.submit(function(e){
					var $form2 = context.$form2.find('.form-table');
					if (context.$form1.parsley().isValid({'group': 'block1', 'force': false})){
						$form2.css({'visibility': 'hidden', 'position': 'absolute'});
						context.$form2.parsley().destroy();
						$form2.appendTo(context.$form1);
					}
				});
				this.$maxTimeslotsTextbox.on('change', function(e){
					context.checkSelectableSeats();
					context.groupBookingValidate(this);
				});
				this.$seats.on('change', function(){
					context.validateSeatsMaximum();
					context.validateSeatsMinimum();
				});
				this.$selectableSeatsCheckbox.on('change', function(){
					context.validateSeatsMaximum();
					context.validateSeatsMinimum();
					context.maxTimeslotsValidate(this);
				});
				this.$returnSameDay.on('change', function(){
					if(this.checked){
						context.$selectableSeatsCheckbox.prop('disabled', false);
					}else{
						context.$selectableSeatsCheckbox.prop('checked', false);
						context.$selectableSeatsCheckbox.prop('disabled', true);
					}
				});
				this.validateSeatsMaximum();
				this.validateSeatsMinimum();
				this.checkSelectableSeats();
				this.setSummary();
				this.repeatFrequencyChanged();
			};
			calendarista.availability.prototype.ariaLog = function(e){
				var result;
				if (e.keyCode !== 13) {
					result  = ' ' + $('.ui-state-hover').html() + 
						' ' + $('.ui-datepicker-month').html() + 
						' ' + $('.ui-datepicker-year').html();
					this.$liveAriaRegion.html(result);
				}
			};
			calendarista.availability.prototype.checkedAll = function(){
				this.$tagSaveButton.prop('disabled', false);
			};
			calendarista.availability.prototype.tagsCheckall = function(e){
				var target = e.currentTarget;
				if(target.checked){
					this.$tagCheckboxes.prop('checked', true);
				}else{
					this.$tagCheckboxes.prop('checked', false);
				}
				this.checkedAll();
			};
			calendarista.availability.prototype.saveTagListRequest = function(){
				//actionSaveTagList
				var tags = []
					, $selectedTags = $('input[name="tags[]"]')
					, availabilityId = <?php echo $this->availability->id ?>
					, model = [
						{ 'name': 'availabilityId', 'value': availabilityId }
						, { 'name': 'controller', 'value': 'tags' }
						, { 'name': 'calendarista_save_tag_list', 'value': 1 }
						, { 'name': 'action', 'value': this.actionSaveTagList }
						, { 'name': 'calendarista_nonce', 'value': this.nonce }
					];
				$.each($selectedTags, function(){
					var $tag = $(this)
						, id = $tag.val()
						, value = $tag.is(':checked') ? '1' : '0';
					tags.push(id + ':' + value);
				});	
				model.push({ 'name': 'tags', 'value': tags.join(',') });
				this.ajax1.request(this, this.tagListSaveResponse, $.param(model));
			};
			calendarista.availability.prototype.tagListSaveResponse = function(result){
				this.$tagSaveButton.prop('disabled', true);
			};
			calendarista.availability.prototype.tagListRequest = function(cleanUrl, values){
				var paged = $('input[name="paged"]').val()
					, orderby = $('input[name="orderby"]').val()
					, order = $('input[name="order"]').val()
					, availabilityId = <?php echo $this->availability->id ?>
					, url = window.location.pathname + window.location.search
					, model = [
						{ 'name': 'availabilityId', 'value': availabilityId }
						, { 'name': 'current_url', 'value': url }
						, { 'name': 'action', 'value': this.actionGetTagList }
						, { 'name': 'calendarista_nonce', 'value': this.nonce }
					];
				if(!cleanUrl){
					model.push({ 'name': 'orderby', 'value': orderby } , { 'name': 'order', 'value': order });
					if(!values){
						model.push({ 'name': 'paged', 'value': paged });
					}
				}
				if(values){
					model = model.concat(values);
				}
				window.history.replaceState({}, document.title, window.location.href);
				this.ajax1.request(this, this.tagListResponse, $.param(model));
			};
			calendarista.availability.prototype.tagListResponse = function(result){
				var context = this;
				this.$tagAllCheck.off();
				this.$tagList.replaceWith('<div id="calendarista_tag_list">' + result + '</div>');
				this.$tagList = $('#calendarista_tag_list');
				this.$tagAllCheck = $('input[name="selectall"]');
				this.$tagAllCheck.on('change', this.tagCheckAllDelegate);
				this.$tagCheckboxes = $('input[name="tags[]"]');
				this.$tagCheckboxes.on('change', this.checkedAllDelegate);
				this.pagerButtonDelegates();
			};
			calendarista.availability.prototype.updateSortOrder = function(){
				var sortOrder = this.getSortOrder(this.$availabilityItems, 'input[name="availabilities[]"]');
				this.$sortOrder.val(sortOrder.join(','));
				this.$sortOrderButton.prop('disabled', false);
			};
			calendarista.availability.prototype.getSortOrder = function($sortItems, selector){
				var i
					, sortOrder = []
					, $item;
				for(i = 0; i < $sortItems.length; i++){
					$item = $($sortItems[i]);
					sortOrder.push($item.find(selector).val() + ':' + $item.index());
				}
				return sortOrder;
			};
			calendarista.availability.prototype.groupBookingValidate = function(elem){
				var val = parseInt($(elem).val(), 10);
				if(val > 1){
					this.$selectableSeatsCheckbox.prop('checked', false);
				}
			};
			calendarista.availability.prototype.maxTimeslotsValidate = function(elem){
				var checked = $(elem).is(':checked');
				if(checked && this.$maxTimeslotsTextbox.length > 0){
					this.$maxTimeslotsTextbox.val(1);
				}
			};
			calendarista.availability.prototype.validateSeatsMaximum = function(){
				var seats = parseInt(this.$seats.val(), 10)
					, seatsMaximum = parseInt(this.$seatsMaximum.val(), 10);
				this.$seatsMaximum.prop('disabled', false);
				if(this.$seats.length !== 0 && (isNaN(seats) || seats === 0/* || !this.$selectableSeatsCheckbox.is(':checked')*/)){
					this.$seatsMaximum.val(0);
					this.$seatsMaximum.prop('disabled', true);
				}
			};
			calendarista.availability.prototype.validateSeatsMinimum = function(){
				var seats = parseInt(this.$seats.val(), 10)
					, seatsMinimum = parseInt(this.$seatsMinimum.val(), 10);
				this.$seatsMinimum.prop('disabled', false);
				if(this.$seats.length !== 0 && (isNaN(seats) || seats === 0/* || !this.$selectableSeatsCheckbox.is(':checked')*/)){
					this.$seatsMinimum.val(1);
					this.$seatsMinimum.prop('disabled', true);
				}
			};
			calendarista.availability.prototype.checkSelectableSeats = function(){
				if(this.$maxTimeslotsTextbox.length === 0){
					return;
				}
				if(parseInt(this.$maxTimeslotsTextbox.val(), 10) > 1){
					this.$selectableSeatsCheckbox.prop('checked', false);
					this.$selectableSeatsCheckbox.prop('disabled', true);
					this.$minimumTimeslotCharge.prop('disabled', false);
					return;
				}
				this.$maxTimeslotsTextbox.val('1');
				this.$selectableSeatsCheckbox.prop('disabled', false);
				this.$minimumTimeslotCharge.val('');
				this.$minimumTimeslotCharge.prop('disabled', true);
			};
			calendarista.availability.prototype.mutuallyExclusive = function($a, selector){
				$('input[name="' + selector + '"]:checked').each(function(){
					var $b = $(this);
					if($a.val() === $b.val()){
						$b.prop('checked', false);
					}
				});
			};
			calendarista.availability.prototype.deleteAvailabilityButtonState = function(){
				var hasChecked = this.$availabilityCheckboxes.is(':checked');
				if(hasChecked){
					this.$availabilityDeleteButton.prop('disabled', false);
				}else{
					this.$availabilityDeleteButton.prop('disabled', true);
				}
			};
			calendarista.availability.prototype.terminateModeChanged = function(){
				var value = parseInt($('input[name="terminateMode"]:checked').val(), 10);
				this.$terminateAfterOccurrenceTextbox.prop('disabled', true);
				this.$endDateTextbox.removeAttr('data-parsley-required');
				this.$endDateTextbox.prop('disabled', true);
				switch(value){
					case 1:
					//occurrence
					this.$terminateAfterOccurrenceTextbox.prop('disabled', false);
					break;
					case 2:
					//on date
					this.$endDateTextbox.prop('disabled', false);
					this.$endDateTextbox.attr('data-parsley-required', 'true');
					break;
				}
				this.setSummary();
			};
			calendarista.availability.prototype.repeatFrequencyChanged = function(){
				var value = parseInt(this.$repeatFrequencySelectList.val(), 10)
					, summary = ''
					, interval = parseInt(this.$repeatIntervalSelectList.val(), 10);
				this.$repeatWeekdayListRow.hide();
				this.$repeatWeekdayCheckboxList.prop('disabled', true);
				this.$repeatIntervalRow.hide();
				switch(value){
					case 1:
					//daily
					this.$repeatIntervalRow.show();
					this.$repeatIntervalLabel.html(this.daysLabelText);
					summary = this.everyDaySummary.replace('%s', interval);
					break;
					case 2:
					//Every day of the week (from Monday to Friday)
					summary = this.everyWeekdaySummary.replace('%s', interval);
					break;
					case 3:
					//Every Monday, Wednesday and Friday
					summary = this.everyWeekMo_We_Fr.replace('%s', interval);
					break;
					case 4:
					//Every Tuesday and Thursday
					summary = this.everyWeekTu_Th.replace('%s', interval);
					break;
					case 5:
					//Weekly
					this.$repeatIntervalRow.show();
					this.$repeatIntervalLabel.html(this.weeksLabelText);
					this.$repeatWeekdayListRow.show();
					this.$repeatWeekdayCheckboxList.prop('disabled', false);
					summary = this.getSelectedWeekdaySummary();
					break;
					case 6:
					//Monthly
					this.$repeatIntervalRow.show();
					this.$repeatIntervalLabel.html(this.monthsLabelText);
					summary = this.everyMonth.replace('%s', interval);
					break;
					case 7:
					//Yearly
					this.$repeatIntervalRow.show();
					this.$repeatIntervalLabel.html(this.yearsLabelText);
					summary = this.everyYear.replace('%s', interval);
					break;
				}
				this.setSummary();
			};
			calendarista.availability.prototype.getSelectedWeekdaySummary = function(){
				var i
					, val
					, values = []
					, weekdayName
					, $checkedList = this.$repeatWeekdayListRow.find('input:checked')
					, interval = parseInt(this.$repeatIntervalSelectList.val(), 10);;
				for(i = 0; i < $checkedList.length; i++){
					val = parseInt($($checkedList[i]).val(), 10);
					switch(val){
						case 7:
						weekdayName = this.su;
						break;
						case 1:
						weekdayName = this.mo;
						break;
						case 2:
						weekdayName = this.tu;
						break;
						case 3:
						weekdayName = this.we;
						break;
						case 4:
						weekdayName = this.th;
						break;
						case 5:
						weekdayName = this.fr;
						break;
						case 6:
						weekdayName = this.sa;
						break;
					}
					values.push(weekdayName);
				}
				if(values.length === 7){
					return this.everyDayOfTheWeek.replace('%s', interval);
				}
				return this.everyWeekOn.replace('%s', interval).replace('%s', values.join(','));
			};
			calendarista.availability.prototype.setSummary = function(){
				var value = parseInt(this.$repeatFrequencySelectList.val(), 10)
					, summary = ''
					, interval = parseInt(this.$repeatIntervalSelectList.val(), 10)
					, $terminateMode = $('input[name="terminateMode"]:checked')
					, terminateMode = parseInt($terminateMode.val(), 10)
					, occurrence = parseInt(this.$terminateAfterOccurrenceTextbox.val(), 10)
					, endDate = this.$endDateTextbox.val();
				switch(value){
					case 1:
					//daily
					summary = this.everyDaySummary.replace('%s', interval);
					break;
					case 2:
					//Every day of the week (from Monday to Friday)
					summary = this.everyWeekdaySummary.replace('%s', interval);
					break;
					case 3:
					//Every Monday, Wednesday and Friday
					summary = this.everyWeekMo_We_Fr.replace('%s', interval);
					break;
					case 4:
					//Every Tuesday and Thursday
					summary = this.everyWeekTu_Th.replace('%s', interval);
					break;
					case 5:
					//Weekly
					summary = this.getSelectedWeekdaySummary();
					break;
					case 6:
					//Monthly
					summary = this.everyMonth.replace('%s', interval);
					break;
					case 7:
					//Yearly
					summary = this.everyYear.replace('%s', interval);
					break;
				}
				switch(terminateMode){
					case 1:
						if(!isNaN(occurrence) && occurrence !== 0){
							 if(occurrence === 1){
								 summary = this.justOnce;
							 }
							else{
								summary += ', ' + this.occurrenceTimes.replace('%s', this.$terminateAfterOccurrenceTextbox.val());
							}
						}
					break;
					case 2:
						if(endDate){
							summary += ', ' + this.until.replace('%s', $.datepicker.formatDate('d M, yy', new Date(endDate)));
						}
					break;
				}
				this.$summary.html(summary);
				if(this.$hasRepeatCheckbox.is(':checked')){
					this.$editRepeat.show();
					this.$repeatModeSummary.html(summary);
				}else{
					this.$repeatModeSummary.html('');
					this.$editRepeat.hide();
				}
			};
			calendarista.availability.prototype.repeatDialogCancel = function(){
				var repeatFrequency = this.availability.repeatFrequency ? this.availability.repeatFrequency : 5
					, repeatInterval = this.availability.repeatInterval ? this.availability.repeatInterval : 1
					, i;
				if(this.availability.hasRepeat){
					this.$hasRepeatCheckbox.prop('checked', true);
				}else{
					this.$hasRepeatCheckbox.prop('checked', false);
				}
				this.$repeatModeSummary.html('');
				this.$editRepeat.hide();
				$('select[name="repeatFrequency"][value="' + repeatFrequency + '"]').prop('selected', true);
				$('select[name="repeatInterval"][value="' + repeatInterval + '"]').prop('selected', true);
				this.$repeatWeekdayCheckboxList.prop('checked', false);
				if(this.availability.repeatWeekdayList.length > 0){
					for(i in this.availability.repeatWeekdayList){
						$('input[name="repeatWeekdayList[]"][value="' + i + '"]').prop('checked', true);
					}
				}else{
					$('input[name="repeatWeekdayList[]"][value="1"]').prop('checked', true);	
				}
				if(this.availability.terminateAfterOccurrence){
					this.$terminateAfterOccurrenceTextbox.val(this.availability.terminateAfterOccurrence);
				}else{
					this.$terminateAfterOccurrenceTextbox.val(35);
				}
				$('input[name="terminateMode"][value="' + this.availability.terminateMode + '"]').prop('checked', true);
				if(this.availability.availableDate){
					this.$startDateTextbox.val(this.availability.availableDate);
					this.$availableDateTextbox.val(this.availability.availableDate);
				}else{
					this.$startDateTextbox.val('');
					this.$availableDateTextbox.val('')
				}
				if(this.availability.endDate){
					this.$endDateTextbox.val(this.availability.endDate);
				}else{
					this.$endDateTextbox.val('');
				}
				this.terminateModeChanged();
				this.setSummary();
				if(this.$repeatDialog){
					this.$repeatDialog.dialog('close');
				}
			};
			calendarista.availability.prototype.pagerButtonDelegates = function(){
				var context = this;
				this.$nextPage = $('a[class="next-page"]');
				this.$lastPage = $('a[class="last-page"]');
				this.$prevPage = $('a[class="prev-page"]');
				this.$firstPage = $('a[class="first-page"]');
				this.$nextPage.on('click', function(e){
					context.gotoPage(e);
				});
				this.$lastPage.on('click', function(e){
					context.gotoPage(e);
				});
				this.$prevPage.on('click', function(e){
					context.gotoPage(e);
				});
				this.$firstPage.on('click', function(e){
					context.gotoPage(e);
				});
			};
			calendarista.availability.prototype.gotoPage = function(e){
				var pagedValue = this.getUrlParameter('paged', $(e.currentTarget).prop('href'))
					, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
				this.$nextPage.off();
				this.$lastPage.off();
				this.$prevPage.off();
				this.$firstPage.off();
				this.tagListRequest(false, model);
				e.preventDefault();
				return false;
			};
			calendarista.availability.prototype.removeURLParameter = function(parameter) {
				 var url = window.location.href;
				//prefer to use l.search if you have a location/link object
				var urlparts= url.split('?');   
				if (urlparts.length>=2) {

					var prefix= encodeURIComponent(parameter)+'=';
					var pars= urlparts[1].split(/[&;]/g);

					//reverse iteration as may be destructive
					for (var i= pars.length; i-- > 0;) {    
						//idiom for string.startsWith
						if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
							pars.splice(i, 1);
						}
					}

					url= urlparts[0]+'?'+pars.join('&');
				}
				window.history.replaceState({}, document.title, url);
			};
			calendarista.availability.prototype.getUrlParameter = function(param, url) {
				var regex, results;
				param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
				regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
				results = regex.exec(url);
				return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		window.ParsleyConfig = {
		  validators: {
			notdefault: {
			  fn: function (value, requirement) {
				return value !== requirement;
			  },
			  priority: 32
			},
			morethan: {
			  fn: function (value, requirement) {
				var x = parseRequirement(requirement);
				return x > parseFloat(value)
			  },
			  priority: 32
			},
			lessthan: {
			  fn: function (value, requirement) {
				var x = parseRequirement(requirement);
				return parseFloat(value) <= x
			  },
			  priority: 32
			}
		  }
		};
		new calendarista.availability({
				"requestUrl": "<?php echo $this->requestUrl; ?>"
				, "selectedId": <?php echo $this->availability->id; ?>
				, "daysLabelText": "<?php esc_html_e('days', 'calendarista')?>"
				, "weeksLabelText": "<?php esc_html_e('weeks', 'calendarista')?>"
				, "monthsLabelText": "<?php esc_html_e('months', 'calendarista')?>"
				, "yearsLabelText": "<?php esc_html_e('years', 'calendarista')?>"
				, "everyDaySummary": "<?php esc_html_e('every %s day(s)', 'calendarista')?>"
				, "everyWeekdaySummary": "<?php esc_html_e('every weekday', 'calendarista')?>"
				, "everyWeekMo_We_Fr": "<?php esc_html_e('every week on Monday, Wednesday and Friday', 'calendarista') ?>"
				, "everyWeekTu_Th": "<?php esc_html_e('every week on Tuesday and Thursday', 'calendarista') ?>"
				, "everyWeekOn": "<?php esc_html_e('every %s week(s) on %s', 'calendarista')?>"
				, "everyMonth": "<?php esc_html_e('every %s month(s) on the ', 'calendarista')  . $this->monthlyRepeatDay ?>"
				, "everyYear": "<?php esc_html_e('every %s year(s) on ', 'calendarista')  . $this->yearlyRepeatDate ?>"
				, "everyDayOfTheWeek": "<?php esc_html_e('every %s week(s)', 'calendarista')?>"
				, "occurrenceTimes": "<?php esc_html_e('%s times', 'calendarista')?>" 
				, "until": "<?php esc_html_e('until the %s', 'calendarista')?>" 
				, "justOnce": "<?php esc_html_e('repeats just once', 'calendarista')?>" 
				, "su": "<?php esc_html_e('Sunday', 'calendarista')?>"
				, "mo": "<?php esc_html_e('Monday', 'calendarista')?>"
				, "tu": "<?php esc_html_e('Tuesday', 'calendarista')?>"
				, "we": "<?php esc_html_e('Wednesday', 'calendarista')?>"
				, "th": "<?php esc_html_e('Thursday', 'calendarista')?>"
				, "fr": "<?php esc_html_e('Friday', 'calendarista')?>"
				, "sa": "<?php esc_html_e('Saturday', 'calendarista')?>"
				, "availability": <?php echo wp_json_encode($this->availability->toArray()); ?>
				, "previewImageUrl": "<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/no-preview-thumbnail.png"
		});
		</script>
	<?php
	}
}