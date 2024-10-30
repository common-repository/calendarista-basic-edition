<?php
class Calendarista_BookingCalendarFieldsTmpl extends Calendarista_TemplateBase{
	public $project;
	public $availabilities = array();
	public $hasAvailability;
	public $availability;
	public $mapOptions;
	public $availabilityId;
	public $hasEndDate;
	public $thumbnails;
	public $availabilityMapOptions;
	public $searchResultAvailabilityId;
	public $searchResultStartDate;
	public $searchResultEndDate;
	public $searchResultStartTime;
	public $searchResultEndTime;
	public $appointment;
	public $map;
	public $_stateBag;
	public $seasons = array();
	public function __construct($appointment = -1, $stateBag = null, $enableMultipleBooking = null){
		$this->_stateBag = $stateBag;
		parent::__construct($stateBag);
		if($enableMultipleBooking !== null){
			if(!in_array($this->project->calendarMode, 
				array(Calendarista_CalendarMode::MULTI_DATE, Calendarista_CalendarMode::MULTI_DATE_AND_TIME))){
				$this->enableMultipleBooking = $enableMultipleBooking;
			}
		}
		$this->appointment = $appointment;
		$this->searchResultAvailabilityId = isset($_GET['cal-availability-id']) && $_GET['cal-availability-id'] ? (int)$_GET['cal-availability-id'] : null;
		$this->searchResultStartDate = isset($_GET['cal-start']) ? sanitize_text_field($_GET['cal-start']) : null;
		$this->searchResultEndDate = isset($_GET['cal-end']) ? sanitize_text_field($_GET['cal-end']) : null;
		$this->searchResultStartTime = isset($_GET['cal-start-time']) ? sanitize_text_field($_GET['cal-start-time']) : null;
		$this->searchResultEndTime = isset($_GET['cal-end-time']) ? sanitize_text_field($_GET['cal-end-time']) : null;
		$projectRepo = new Calendarista_ProjectRepository();
		$this->project = $projectRepo->read($this->projectId);
		$this->hasEndDate = in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_END_DATE);
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availabilities = $availabilityRepo->readAll($this->projectId);
		$this->thumbnails = array();
		$this->availabilityId = (int)$this->getPostValue('availabilityId');
		if(!$this->availabilityId){
			$this->availabilityId = $this->searchResultAvailabilityId;
		}
		foreach($availabilities as $availability){
			//filter availabilities
			if(Calendarista_AvailabilityHelper::active($availability)){
				if($this->availabilityId === $availability->id){
					$this->availability = $availability;
					$this->availability->calendarMode = $this->project->calendarMode;
				}
				array_push($this->availabilities, $availability);
				if(!$this->availabilityThumbnailView && $availability->imageUrl){
					array_push($this->thumbnails, array('id'=>$availability->id, 'url'=>$availability->imageUrl));
				}
			}
		}
		$this->hasAvailability = count($this->availabilities) > 0;
		$mapRepo = new Calendarista_MapRepository();
		$this->map = $mapRepo->readByProject($this->projectId);
		if(!$this->availability){
			if($this->hasAvailability){
				$this->availability = $this->availabilities[0];
			}else{
				$this->availability = new Calendarista_Availability(array());
			}
		}
		if($this->availability->id){
			$seasonRepo = new Calendarista_SeasonRepository();
			$seasons = $seasonRepo->readByAvailability($this->availability->id);
			foreach($seasons as $season){
				if(!$season['bookingDaysMinimum'] && !$season['bookingDaysMaximum']){
					continue;
				}
				array_push($this->seasons, array(
					'startDate'=>$season['start']
					, 'endDate'=>$season['end']
					, 'bookingDaysMinimum'=>$season['bookingDaysMinimum']
					, 'bookingDaysMaximum'=>$season['bookingDaysMaximum']
				));
			}
			//pass seasons to js code after json_encode
		}
		$this->setAvailabilityMapOptions();
		$this->validateSearchResultFromQueryString();
		if($this->appointment === 1/*appointment mode*/){
			//keeping this option enabled for edits, always.
			$this->enableMultipleBooking = true;
			$repo = new Calendarista_ProjectRepository();
			$projectList = $repo->getProjectByCalendarMode($this->project->calendarMode);
			if($projectList && count($projectList) > 0){
				$this->projectList = $projectList;
			}
		}
		$this->render();
	}
	public function validateSearchResultFromQueryString(){
		if($this->searchResultStartDate){
			$result = Calendarista_AvailabilityHelper::checkAvailability($this->availability, $this->searchResultStartDate, $this->searchResultEndDate);
			$sd = new Calendarista_DateTime($this->searchResultStartDate);
			$ed = new Calendarista_DateTime($this->searchResultEndDate);
			$diff = $sd->diff($ed);
			if(is_array($result)){
				if($this->availability->bookingDaysMinimum > 1 && $diff->days < $this->availability->bookingDaysMinimum){
					$this->searchResultStartDate = null;
					$this->searchResultStartTime = null;
				}else{
					$this->searchResultStartDate = $result['startDate']; 
				}
				if($this->availability->bookingDaysMaximum > 0 && $diff->days > $this->availability->bookingDaysMaximum){
					$this->searchResultEndDate = null;
					$this->searchResultEndTime = null;
				}else{
					$this->searchResultEndDate = $result['endDate'];
				}
			}else{
				$this->searchResultStartDate = null;
				$this->searchResultEndDate = null;
				$this->searchResultStartTime = null;
				$this->searchResultEndTime = null;
			}
		}
	}
	protected function convertToHoursMins($time) {
		if ($time < 1) {
			return;
		}
		$hourString = $this->stringResources['HOUR'];
		$minuteString = $this->stringResources['MINUTE'];
		$format = '%2d ' . $hourString . ' %2d ' . $minuteString;
		$hours = floor($time / 60);
		$minutes = ($time % 60);
		if($hours === 0 && $minutes > 0){
			$format = ' %2d ' . $minuteString;
			return sprintf($format, $minutes);
		}
		if($minutes === 0 && $hours > 0){
			$format = ' %2d ' . $hourString;
			return sprintf($format, $hours);
		}
		return sprintf($format, $hours, $minutes);
	}
	public function getDynamicFieldsCount(){
		$items = $this->getViewStateValue('dynamicFields');
		if($items && count($items) > 0){
			return count($items);
		}
		if($this->hasAvailability){
			$repo = new Calendarista_DynamicFieldRepository();
			return $repo->getFieldsCount($this->availability->id);
		}
		return 0;
	}
	public function setAvailabilityMapOptions(){
		if($this->availability && !$this->availability->hideMapDisplay){
			$this->availabilityMapOptions = $this->availability->toMapArray($this->uniqueId);
		}
	}
	protected function getAvailableDate(){
		$availableDate = $this->getViewStateValue('availableDate');
		$result = null;
		if($availableDate){
			$result = $availableDate; 
		}else if($this->searchResultStartDate){
			$result = $this->searchResultStartDate;
		}
		return $result;
	}
	protected function getEndDate(){
		$endDate = $this->getViewStateValue('endDate');
		$result = null;
		if($endDate){
			$result = $endDate; 
		}else if($this->searchResultEndDate){
			$result = $this->formatDateString($this->searchResultEndDate);
		}
		return $result;
	}
	protected function getClientStartDate(){
		$availableDate = $this->getViewStateValue('availableDate');
		$result = null;
		if($availableDate){
			$result = Calendarista_TimeHelper::formatDate($availableDate);
		}else if($this->searchResultStartDate){
			$result = $this->formatDateString($this->searchResultStartDate);
		}
		return $result;
	}
	protected function getClientEndDate(){
		$endDate = $this->getViewStateValue('endDate');
		$result = null;
		if($endDate){
			$result = Calendarista_TimeHelper::formatDate($endDate);
		}else if($this->searchResultEndDate){
			$result = $this->formatDateString($this->searchResultEndDate);
		}
		return $result;
	}
	protected function getMinDate(){
		$lastAvailableDate = Calendarista_AvailabilityHelper::getMinDate($this->availability);
		//when using timezone, we want to make sure we are on the right day
		$timezone = $this->availability ? $this->availability->timezone : null;
		$now = Calendarista_TimeHelper::formatDate(date(CALENDARISTA_DATEFORMAT, $lastAvailableDate));
		return $now;
	}
	protected function formatDateString($val){
		if(!$val){
			return null;
		}
		return Calendarista_TimeHelper::formatDate(date(CALENDARISTA_DATEFORMAT, strtotime($val)));
	}
	public function render(){
	?>
	<?php new Calendarista_BookingCalendarLegendTmpl(); ?>
	<input type="hidden" name="availableDate" value="<?php echo $this->getAvailableDate() ?>"/>
	<input type="hidden" name="minDate" value="<?php echo $this->getMinDate() ?>"/>
	<input type="hidden" name="endDate" value="<?php echo $this->getEndDate() ?>"/>
	<input type="hidden" name="clientAvailableDate" value="<?php echo $this->getClientStartDate() ?>"/>
	<input type="hidden" name="clientEndDate" value="<?php echo $this->getClientEndDate() ?>"/>
	<input type="hidden" name="maxTimeslots" value="<?php echo $this->availability->maxTimeslots ?>"/>
	<input type="hidden" name="timezone" value="<?php echo $this->getViewStateValue('timezone') ?>"/>
	<input type="hidden" name="dynamicFieldsCount" value="<?php echo $this->getDynamicFieldsCount() ?>" />
	<input type="hidden" name="multiDateSelection" value="<?php echo  $this->getViewStateValue('multiDateSelection') ?>"/>
	<input type="hidden" name="bookingDaysMinimum" value="<?php echo  $this->availability->bookingDaysMinimum ?>"/>
	<?php  if($this->map && $this->map->costMode === Calendarista_CostMode::DISTANCE): ?>
	<input type="hidden" name="costAppliesByDistance"/>
	<?php endif;?>
	<?php if(count($this->projectList) > 1):
			new Calendarista_BookingServiceSwitcherTmpl($this->projectList);
	endif; ?>
	<?php if($this->hasAvailability):?>
		<?php if($this->project->calendarMode === Calendarista_CalendarMode::PACKAGE):
			$bookingPackage = new Calendarista_BookingPackageTmpl($this->availabilities, $this->appointment, $this->_stateBag, $this->searchResultAvailabilityId);
			if(count($bookingPackage->packages) === 0):
				$this->hasAvailability = false;
			endif;
		else: 
			new Calendarista_BookingAvailabilitySwitcherTmpl($this->availabilities, $this->searchResultAvailabilityId); 
	?>
	<?php if($this->hasEndDate): ?>
	<div class="row">
	<?php endif; ?>
		<div class="<?php echo ($this->hasEndDate && !$this->availability->returnSameDay) ? 'col-xl-6' : 'col-xl-12' ?>">
			<div id="calendarista_liveregion" role="log" aria-live="assertive" aria-atomic="true" aria-relevant="additions" class="sr-only"></div>
			<div class="form-group">
				<label class="form-control-label calendarista-typography--caption1" for="start_date_<?php echo $this->uniqueId ?>">
					<?php if(in_array($this->project->calendarMode, array(
											Calendarista_CalendarMode::ROUND_TRIP
											, Calendarista_CalendarMode::ROUND_TRIP_WITH_TIME))):?>
						<?php echo esc_html($this->stringResources['BOOKING_DEPARTING_DATE_LABEL']) ?>
					<?php else: ?>
						<?php echo esc_html($this->stringResources['BOOKING_START_DATE_LABEL']) ?>
					<?php endif; ?>
				</label>
				<div class="input-group">
					<input type="text" 
							id="start_date_<?php echo $this->uniqueId ?>" 
							class="calendarista-start-date form-control calendarista-typography--caption1 calendarista-readonly-field calendarista_parsley_validated" 
							readonly
							data-calendarista-loading="<?php echo esc_html($this->stringResources['CALENDAR_LOADING']) ?>"
							placeholder="<?php echo esc_html($this->stringResources['BOOKING_DATE_FIELD_PLACEHOLDER']) ?>"
							data-parsley-trigger="change" 
							data-parsley-errors-container="#start_date_error_container_<?php echo $this->uniqueId ?>" 
							data-parsley-required="true"
							value="<?php echo $this->formatDateString($this->searchResultStartDate) ?>"
							aria-label="<?php esc_html_e('Please note: page up/down for previous/next month, ctrl plus page up/down for previous/next year, ctrl plus left/right for previous/next day, enter key to accept the selected date', 'calendarista') ?>">
					<label for="start_date_<?php echo $this->uniqueId ?>" class="input-group-text">
						<i class="fa fa-calendar"></i>
					</label>
				</div>
				<div id="start_date_error_container_<?php echo $this->uniqueId ?>" class="calendarista-typography--caption1"></div>
			</div>
			<div class="clearfix"></div>
			<?php new Calendarista_BookingTimeslotsTmpl(0, true/*placeholder required*/); ?>
		</div>
		<?php if($this->hasEndDate):?>
		<div class="<?php echo $this->availability->returnSameDay ? 'col-xl-12' : 'col-xl-6'?>">
			<div class="form-group">
				<?php if(!$this->availability->returnSameDay): ?>
				<label class="form-control-label calendarista-typography--caption1" for="end_date_<?php echo $this->uniqueId ?>">
					<?php if(in_array($this->project->calendarMode, array(
											Calendarista_CalendarMode::ROUND_TRIP
											, Calendarista_CalendarMode::ROUND_TRIP_WITH_TIME))):?>
						<?php echo esc_html($this->stringResources['BOOKING_RETURN_DATE_LABEL']) ?>
					<?php else: ?>
						<?php echo esc_html($this->stringResources['BOOKING_END_DATE_LABEL']) ?>
					<?php endif; ?>
				</label>
				<div class="input-group">
					<input type="text" 
							id="end_date_<?php echo $this->uniqueId ?>" 
							class="calendarista-end-date form-control calendarista-typography--caption1 calendarista-readonly-field calendarista_parsley_validated" 
							readonly
							data-calendarista-loading="<?php echo esc_html($this->stringResources['CALENDAR_LOADING']) ?>"
							placeholder="<?php echo esc_html($this->stringResources['BOOKING_DATE_FIELD_PLACEHOLDER']); ?>"
							data-parsley-trigger="change" 
							data-parsley-errors-container="#end_date_error_container_<?php echo $this->uniqueId ?>" 
							value="<?php echo $this->formatDateString($this->searchResultEndDate) ?>"
							<?php if(!$this->availability->returnOptional):?>
							data-parsley-required="true"
							<?php endif; ?>
							aria-label="<?php esc_html_e('Please note: page up/down for previous/next month, ctrl plus page up/down for previous/next year, ctrl plus left/right for previous/next day, enter key to accept the selected date', 'calendarista') ?>">
					<label for="end_date_<?php echo $this->uniqueId ?>" class="input-group-text">
						<i class="fa fa-calendar"></i>
					</label>
				</div>
				<div id="end_date_error_container_<?php echo $this->uniqueId ?>" class="calendarista-typography--caption1"></div>
				<?php else: ?>
				<input type="hidden" 
							id="end_date_<?php echo $this->uniqueId ?>" value="<?php echo $this->formatDateString($this->searchResultEndDate) ?>">
				<?php endif; ?>
			</div>
			<?php  new Calendarista_BookingTimeslotsTmpl(1); ?>
			<?php if($this->availability->returnOptional && !$this->availability->returnSameDay):?>
			<div class="form-group calendarista-return-optional">
				<div class="calendarista-typography--caption1 calendarista-row-single">
					<?php echo esc_html($this->stringResources['BOOKING_RETURN_IS_OPTIONAL']) ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	<?php if($this->hasEndDate):?>
	</div>
	<?php endif; ?>
	<div class="col-xl-12">
		<div class="form-group calendarista-date-range-error calendarista-row-single hide">
			<div class="alert alert-warning calendarista-typography--caption1" role="alert">
				<strong><?php echo esc_html($this->stringResources['WARNING']) ?></strong>&nbsp;<?php echo esc_html($this->stringResources['BOOKING_DATE_RANGE_ERROR']) ?>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="form-group calendarista-timeslots-error calendarista-row-single hide">
			<div class="alert alert-warning calendarista-typography--caption1" role="alert">
				<strong><?php echo esc_html($this->stringResources['WARNING']) ?></strong>&nbsp;<?php echo esc_html($this->stringResources['BOOKING_TIMESLOTS_ERROR']) ?>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="form-group calendarista-timeslots-return-error calendarista-row-single hide">
			<div class="alert alert-warning calendarista-typography--caption1" role="alert">
				<strong><?php echo esc_html($this->stringResources['WARNING']) ?></strong>&nbsp;<?php echo esc_html($this->stringResources['BOOKING_TIMESLOTS_RETURN_ERROR']) ?>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php if($this->availability->minTime): ?>
		<input type="hidden" name="minTime" value="<?php echo $this->availability->minTime ?>" data-parsley-required="true" class="calendarista_parsley_validated" data-parsley-error-message="<?php echo sprintf($this->stringResources['BOOKING_MIN_TIME_ERROR'], $this->convertToHoursMins($this->availability->minTime))?>" />
		<?php endif; ?>
		<?php if($this->availability->maxTime): ?>
		<input type="hidden" name="maxTime" value="<?php echo $this->availability->maxTime ?>" data-parsley-required="true" class="calendarista_parsley_validated" data-parsley-error-message="<?php echo sprintf($this->stringResources['BOOKING_MAX_TIME_ERROR'], $this->convertToHoursMins($this->availability->maxTime))?>" />
		<?php endif; ?>
	</div>
	<?php if($this->availability->displayDateSelectionReq && ($this->availability->bookingDaysMinimum || $this->availability->bookingDaysMaximum)):?>
	<div class="col-xl-12">
		<div class="form-group">
			<div class="calendarista-typography--caption1 calendarista-row-single">
				<strong><?php echo esc_html($this->stringResources['NOTE']) ?></strong>
				<?php if($this->availability->bookingDaysMinimum):?>
					<?php echo sprintf($this->stringResources['BOOKING_DATE_MIN_REQUIRED'], $this->availability->bookingDaysMinimum) ?>
				<?php endif;?>
				<?php if($this->availability->bookingDaysMaximum):?>
					<?php echo sprintf($this->stringResources['BOOKING_DATE_MAX_LIMITED'], $this->availability->bookingDaysMaximum) ?>
				<?php endif;?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif;?>
	<?php endif; ?>
	<?php else: ?>
	<div class="col-xl-12">
		<div class="form-group">
			<div class="calendarista-no-availability-message calendarista-typography--caption1 calendarista-row-single"><?php echo esc_html($this->stringResources['NO_AVAILABILITY_FOUND']) ?></div>
		</div>
	</div>
	<?php endif;?>
	<?php do_action('calendarista_personal_fields', $this->projectId, $this->availability->id); ?>
	<?php do_action('calendarista_calendar_fields_error_msg', $this->projectId, $this->availability->id); ?>
	<?php if(!$this->availabilityThumbnailView && $this->availability->description):?>
	<div class="col-xl-12 calendarista-description-col calendarista-row-single">
		<div class="alert alert-secondary calendarista-description-alert calendarista-typography--caption1" role="alert">
			<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($this->availability->description)) ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="calendarista-seats-placeholder"></div>
	<?php if(!in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING) && $this->availability->seatsMinimum > 1): ?>
	<input type="hidden" name="seats" value="<?php echo $this->availability->seatsMinimum ?>">
	<?php endif; ?>
	<div class="calendarista-dynamicfield-placeholder"></div>
	<?php if($this->enableMultipleBooking): ?>
	<div class="calendarista-bookmore-placeholder"></div>
	<?php endif; ?>
	<div class="calendarista-repeat-appointment-placeholder"></div>
	<?php if($this->availabilityMapOptions):?>
	<div class="col-xl-12 calendarista-row-single">
		<div class="form-group">
			<div class="woald-map-placeholder">
				<div class="woald-map-venue-location"><?php echo sprintf($this->stringResources['VENUE_LOCATION'], esc_html($this->availability->regionAddress));?></div>
				<div class="woald-map">
					<div class="woald-map-canvas"></div>
				</div>
			</div>
		</div>
	</div>
	<?php elseif($this->availability->regionAddress): ?>
	<div class="col-xl-12 calendarista-row-single">
		<div class="form-group">
			<div class="woald-map-venue-location"><?php echo sprintf($this->stringResources['VENUE_LOCATION'], esc_html($this->availability->regionAddress));?></div>
		</div>
	</div>
	<?php endif;?>
	<?php do_action('calendarista_calendar_fields_add_script_block_begin', $this->projectId, $this->availability->id); ?>
	<script type="text/javascript">
	(function(){
		function init(){
			var calendar = new Calendarista.calendar({
				'id': '<?php echo $this->uniqueId?>'
				, 'calendarMode': <?php echo $this->project->calendarMode ?> 
				, 'firstDayOfWeek': <?php echo $this->generalSetting->firstDayOfWeek ?>
				, 'ajaxUrl': '<?php echo $this->ajaxUrl ?>'
				, 'projectId': <?php echo $this->projectId ?> 
				, 'dateFormat': '<?php echo $this->generalSetting->shorthandDateFormat ?>'
				, 'thumbnails': <?php echo wp_json_encode($this->thumbnails); ?>
				, 'appointment': <?php echo $this->appointment ?>
				, 'bookingDaysMinimum': <?php echo (int)$this->availability->bookingDaysMinimum ?>
				, 'bookingDaysMaximum': <?php echo (int)$this->availability->bookingDaysMaximum ?>
				, 'dayCountMode': <?php echo (int)$this->availability->dayCountMode ?>
				, 'searchResultStartTime': '<?php echo $this->searchResultStartTime ?>'
				, 'searchResultEndTime':  '<?php echo $this->searchResultEndTime ?>'
				, 'timeDisplayMode': <?php echo $this->availability->timeDisplayMode ?>
				, 'enableMultipleBooking': <?php echo $this->enableMultipleBooking ? 1 : 0 ?>
				, 'clearLabel': '<?php echo $this->decodeString($this->stringResources["CALENDAR_CLEAR_DATE"]) ?>'
				, 'seasons': <?php echo wp_json_encode($this->seasons); ?>
				, 'minTime': <?php echo (int)$this->availability->minTime ?>
				, 'maxTime': <?php echo (int)$this->availability->maxTime ?>
				, 'returnSameDay': <?php echo $this->availability->returnSameDay ? 1 : 0 ?>
				, 'returnOptional': <?php echo $this->availability->returnOptional ? 1 : 0 ?>
				, 'repeatPageSize': <?php echo $this->project->repeatPageSize ?>
			})
			, gmaps;
			<?php if($this->availabilityMapOptions):?>
			gmaps = new Woald.gmaps(<?php echo wp_json_encode($this->availabilityMapOptions); ?>);
			<?php endif;?>
			this.$nextButton = calendar.$root.find('button[name="next"]');
			<?php if(!$this->hasAvailability):?>
			this.$nextButton.attr('data-calendarista-closed', true);
			this.$nextButton.prop('disabled', true).addClass('ui-state-disabled');
			<?php endif; ?>
			this.$nextButton.on('click', function(e){
				var $multiDateSelection = calendar.$root.find('input[name="multiDateSelection"]')
					, bookingDaysMinimum = calendar.bookingDaysMinimum
					, multiDatesVal = $multiDateSelection.val()
					, multiDates = multiDatesVal ? multiDatesVal.split(';') : []
					, sel = (multiDates.length > 0 && (!bookingDaysMinimum || multiDates.length >= bookingDaysMinimum)) ? '.calendarista-dynamicfield.calendarista_parsley_validated' : null;
				if(!Calendarista.wizard.isValid(calendar.$root, sel)){
					e.preventDefault();
					return false;
				}else if (multiDates.length > 0){
					calendar.$startDate.parsley().reset();
				}
				calendar.unload();
				if(gmaps){
					gmaps.unload();
				}
			});
		}
		<?php if($this->notAjaxRequest):?>
		
		if (window.addEventListener){
		  window.addEventListener('load', onload, false); 
		} else if (window.attachEvent){
		  window.attachEvent('onload', onload);
		}
		function onload(e){
			init();
		}
		<?php else: ?>
		init();
		<?php endif; ?>
		
	})();
	</script>
	<?php do_action('calendarista_calendar_fields_add_script_block_end', $this->projectId, $this->availability->id); ?>
<?php
	}
}
