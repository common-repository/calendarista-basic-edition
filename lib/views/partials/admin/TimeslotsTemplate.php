<?php
class Calendarista_TimeslotsTemplate extends Calendarista_ViewBase{
	public $weeklyTimeslots;
	public $selectedDateTimeslots;
	public $selectedDate;
	public $allDates;
	public $timeslot;
	public $weekdays = array();
	public $availabilityId;
	public $availabilities;
	public $availability;
	public $supportsDeals;
	public $project;
	public $supportsCost;
	public $dealEnabled;
	public $returnTrip;
	function __construct( ){
		parent::__construct();
		$this->availabilityId = isset($_GET['availabilityId']) ? (int)$_GET['availabilityId'] : null;
		$this->selectedDate = isset($_GET['selectedDate']) ? sanitize_text_field($_GET['selectedDate']) : null;
		$this->returnTrip = isset($_REQUEST['returnTrip']) ? (int)$_REQUEST['returnTrip'] : 0;
		if(!$this->selectedDate){
			$this->selectedDate = $this->getPostValue('day');
		}
		$timeslotRepo = new Calendarista_TimeslotRepository();
		//clean up timeslots by manual date selection (in the past).
		$timeslotRepo->deleteExpired();
		$this->timeslot = new Calendarista_Timeslot(array(
			'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
			'availabilityId'=>isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : null,
			'weekday'=>isset($_POST['weekday']) ? (int)$_POST['weekday'] : null,
			'timeslot'=>isset($_POST['timeslot']) ? sanitize_text_field($_POST['timeslot']) : null,
			'cost'=>isset($_POST['cost']) ? (double)$_POST['cost'] : null,
			'day'=>isset($_POST['day']) ? sanitize_text_field($_POST['day']) : null,
			'seats'=>isset($_POST['seats']) ? (int)$_POST['seats'] : null,
			'seatsMaximum'=>isset($_POST['seatsMaximum']) ? (int)$_POST['seatsMaximum'] : null,
			'seatsMinimum'=>isset($_POST['seatsMinimum']) ? (int)$_POST['seatsMinimum'] : null,
			'bookedSeats'=>isset($_POST['bookedSeats']) ? (int)$_POST['bookedSeats'] : null,
			'paddingTimeBefore'=>isset($_POST['paddingTimeBefore']) ? (int)$_POST['paddingTimeBefore'] : null,
			'paddingTimeAfter'=>isset($_POST['paddingTimeAfter']) ? (int)$_POST['paddingTimeAfter'] : null,
			'deal'=>isset($_POST['deal']) ? (int)$_POST['deal'] : null,
			'startTime'=>isset($_POST['startTime']) ? (bool)$_POST['startTime'] : null,
			'returnTrip'=>isset($_POST['returnTrip']) ? (bool)$_POST['returnTrip'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null
		));
		$this->allDates = array();
		$this->weeklyTimeslots = new Calendarista_Timeslots();
		$this->selectedDateTimeslots = array();
		new Calendarista_TimeslotController(
			$this->timeslot
			, array($this, 'created')
			, array($this, 'updated')
			, array($this, 'deleted')
		);
		new Calendarista_AutogenTimeslotsController(
			array($this, 'autogenSlots')
			, array($this, 'dealUpdated')
			, array($this, 'deleted')
			, array($this, 'startTimeChanged')
		);
		if($this->availabilityId){
			$this->weeklyTimeslots = $timeslotRepo->readAllWeekdaysByAvailability($this->availabilityId, $this->returnTrip);
			$this->allDates = $timeslotRepo->readAllDaysByAvailability($this->availabilityId, $this->returnTrip);
			if($this->selectedDate){
				$timeslots = $timeslotRepo->readSingleDayByAvailability($this->selectedDate, $this->availabilityId, $this->returnTrip);
				if($timeslots->count() > 0){
					foreach($timeslots as $timeslot){
						array_push($this->selectedDateTimeslots, $timeslot);
					}
					usort($this->selectedDateTimeslots, array($this, 'sortByTime'));
				}
			}
		}
		$this->requestUrl = admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=2&projectId=' . $this->selectedProjectId;
		if($this->availabilityId !== -1){
			$this->requestUrl .= '&availabilityId=' . $this->availabilityId;
		}
		if($this->selectedDate){
			$this->requestUrl .= '&selectedDate=' . $this->selectedDate;
		}
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$this->availabilities = $availabilityRepo->readAll($this->selectedProjectId);
		$this->categorizeWeeklyTimeslots();
		$this->project = $this->getProject();
		if($this->availabilities->count() === 0){
			$this->availabilityMissingNotice();
			return;
		}else if($this->availabilityId === -1){
			$this->availabilityNotSelectedNotice();
		}
		if($this->availabilityId){
			$this->availability = $availabilityRepo->read($this->availabilityId);
			if($this->availability){
				$this->supportsDeals = in_array($this->project->calendarMode, array(
													Calendarista_CalendarMode::SINGLE_DAY_AND_TIME
													, Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_WITH_PADDING
													, Calendarista_CalendarMode::MULTI_DATE_AND_TIME));
				$this->dealEnabled = $this->availability->timeDisplayMode === 1/*Deals*/ ? true : false;
			}
			$this->extendNextDayController();
		}
		$this->supportsCost = !in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST) || in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_AND_TIMESLOT_COST);
		$this->defaultStartTime();
		$this->render();
	}
	public function extendNextDayController(){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_next_day')){
			return;
		}
		$availabilityRepository = new Calendarista_AvailabilityRepository();
		$this->availability->extendTimeRangeNextDay = (bool)$this->getPostValue('extendTimeRangeNextDay');
		$availabilityRepository->update($this->availability);
	}
	public function defaultStartTime(){
		foreach($this->weekdays as $weekday){
			$flag1 = false;
			foreach($weekday as $timeslot){
				if($timeslot->startTime){
					$flag1 = true;
					break;
				}
			}
			if(!$flag1){
				$weekday[0]->startTime = true;
			}
		}
		if(count($this->selectedDateTimeslots) > 0){
			foreach($this->selectedDateTimeslots as $timeslot){
				$flag1 = false;
				if($timeslot->startTime){
					$flag1 = true;
					break;
				}
			}
			if(!$flag1){
				$defaultItem = $this->selectedDateTimeslots[0];
				$defaultItem->startTime = true;
			}
		}
	}
	public function timeslotEditMode(){
		return isset($this->timeslot->id) && $this->timeslot->id !== -1;
	}
	public function autogenSlots($result){
		if($result){
			$this->autogenNotice();
		}
	}
	public function categorizeWeeklyTimeslots(){
		$weekdays = array();
		foreach($this->weeklyTimeslots as $timeslot){
			if(isset($timeslot->weekday) && !in_array($timeslot->weekday, $weekdays)){
				array_push($weekdays, $timeslot->weekday);
			}
		}
		sort($weekdays, SORT_NUMERIC);
		foreach($weekdays as $weekday){
			if(!isset($this->weekdays[$weekday])){
				$this->weekdays[$weekday] = array();
			}
			foreach($this->weeklyTimeslots as $timeslot){
				if ($timeslot->weekday !== $weekday){
					continue;
				}
				array_push($this->weekdays[$weekday], $timeslot);
			}
			usort($this->weekdays[$weekday], array($this, 'sortByTime'));
		}
	}
	public function sortByTime($a, $b){
		return (strtotime($a->timeslot) <=> strtotime($b->timeslot));
	}
	public function getWeekdayName($weekday){
		$name = '';
		switch($weekday){
			case 1:
				$name = __('Monday', 'calendarista');
				break;
			case 2:
				$name = __('Tuesday', 'calendarista');
				break;
			case 3:
				$name = __('Wednesday', 'calendarista');
				break;
			case 4:
				$name = __('Thursday', 'calendarista');
				break;
			case 5:
				$name = __('Friday', 'calendarista');
				break;
			case 6:
				$name = __('Saturday', 'calendarista');
				break;
			case 7:
				$name = __('Sunday', 'calendarista');
				break;
		}
		return $name;
	}
	public function timeslotSelected($id){
		return $this->timeslot->id === $id ? 'timeslot-selected' : '';
	}
	public function created($duplicate, $result){
		if($result){
			$this->createdNotice();
		}else if($duplicate){
			$this->duplicateTimeslotNotice();
		}
	}
	public function dealUpdated($result){
		if($result){
			$this->dealUpdatedNotice();
		}
	}
	public function updated($duplicate, $result){
		if($result){
			$this->updatedNotice();
		}else if($duplicate){
			$this->duplicateTimeslotNotice();
		}
	}
	public function deleted($result){
		if($result){
			$this->deletedNotice();
		}
	}
	public function startTimeChanged($result){
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The start time has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function updatedNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The timeslot has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function dealUpdatedNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The timeslot(s) deal option has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function createdNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The timeslot has been created.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function duplicateTimeslotNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('Last operation was aborted because the timeslot already exists and would result in a duplicate. Did you instead want to update?', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function deletedNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The timeslot(s) deletion was successful.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function autogenNotice(){
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p>
				<?php esc_html_e('The timeslots have been generated.', 'calendarista'); ?>
			</p>
		</div>
		<?php
	}
	public function availabilityMissingNotice(){
		?>
		<div class="calendarista-notice error notice is-dismissible">
			<p>
				<?php esc_html_e('You must create atleast one availability first.', 'calendarista'); ?>
			</p>
		</div>
		<?php
	}
	public function availabilityNotSelectedNotice(){
		?>
		<div class="calendarista-notice error notice is-dismissible">
			<p>
				<?php esc_html_e('You must select an availability to view, create or edit timeslots.', 'calendarista'); ?>
			</p>
		</div>
		<?php
	}
	public function render(){
	?>
		<p class="description">
			<?php esc_html_e('List of timeslots by availability below.', 'calendarista')?>
			<strong><?php esc_html_e('Click individual timeslot to edit or check timeslot(s) to delete.', 'calendarista')?></strong>
		</p>
		<div class="wrap">
			<div class="column-pane">
				<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_RETURN)):?>
					<form class="calendarista_form" data-parsley-validate method="post">
						<input type="radio" name="returnTrip" value="0" <?php echo !$this->returnTrip ? "checked" : ""?>> <?php esc_html_e('Regular', 'calendarista') ?>
						<input type="radio" name="returnTrip" value="1" <?php echo $this->returnTrip ? "checked" : ""?>> <?php esc_html_e('Return trip', 'calendarista') ?>
					</form>
					<hr>
				<?php endif; ?>
				<form class="calendarista_form" data-parsley-validate method="post">
					<div>
						<select name="availabilityId" id="availabilityId" data-parsley-required="true" class="calendarista_parsley_validated">
						<option value=""><?php esc_html_e('Select an availability', 'calendarista'); ?></option>
						<?php foreach($this->availabilities as $availability):?>
							<option value="<?php echo $availability->id; ?>" <?php echo $availability->id === $this->availabilityId ? 'selected=selected' : '';?>><?php echo $availability->name; ?></option>
						<?php endforeach;?>
						</select>
						<?php if(count($this->allDates) > 0):?>
						<select name="selectedDate" id="selectedDate" class="calendarista_parsley_validated">
							<option value=""><?php esc_html_e('Timeslots by week days', 'calendarista'); ?></option>
							<?php foreach($this->allDates as $date):?>
								<option value="<?php echo $date; ?>" <?php echo $date === $this->selectedDate ? 'selected=selected' : '';?>><?php echo $date; ?></option>
							<?php endforeach;?>
						</select>
						<?php endif; ?>
						<?php if(!in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
						<button type="button" name="createTimeslots" 
							class="button button-primary" <?php echo !$this->availabilityId ? 'disabled' : ''; ?>><?php esc_html_e('Create Timeslot', 'calendarista') ?></button>
						<?php endif; ?>
						<button type="button" name="autogenTimeslots" 
							class="button button-primary" <?php echo !$this->availabilityId ? 'disabled' : ''; ?>><?php esc_html_e('Autogenerate Timeslots', 'calendarista') ?></button>
					</div>
				</form>
				<?php if($this->availability && $this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE):?>
				<hr>
					<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
					<input type="hidden" name="controller" value="calendarista_next_day"/>
					<input name="extendTimeRangeNextDay" type="hidden" value="0">
						<input id="extendTimeRangeNextDay" name="extendTimeRangeNextDay" 
							type="checkbox" <?php echo $this->availability->extendTimeRangeNextDay ? "checked" : ""?> /> 
								<?php esc_html_e('Extend timeslots to next day', 'calendarista')?>
						<button type="submit" class="button button-primary" 
								name="calendarista_extend">
								<?php esc_html_e('Save', 'calendarista') ?>
						</button>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<div class="create-timeslots-modal calendarista" 
			title="<?php esc_html_e('Timeslot', 'calendarista') ?>">
			<div class="create_timeslots_placeholder"></div>
			<div id="spinner_timeslots" class="calendarista-spinner calendarista-invisible">
				<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading dialog...', 'calendarista') ?>
			</div>
		</div>
		<div class="autogen-timeslots-modal calendarista" 
			title="<?php esc_html_e('Autogenerate timeslots', 'calendarista') ?>">
			<div class="autogen_timeslots_placeholder"></div>
			<div id="spinner_timeslots" class="calendarista-spinner calendarista-invisible">
				<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading dialog...', 'calendarista') ?>
			</div>
		</div>
		<div>	
			<form class="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_autogen_timeslots" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<?php if(!$this->selectedDate): ?>
			<?php if($this->weeklyTimeslots->count() > 0):?>
			<?php foreach($this->weekdays as $key=>$weekday):?>
				<ul>
					<li class="horizontal-list-item medium-list-size">
						<ul>
							<li>
								<table class="widefat timeslots_<?php echo $key ?>">
									<caption><strong><?php echo $this->getWeekdayName($key);?></strong></caption>
									<thead>
										<th><input type="checkbox" name="delete_all_timeslots" class="calendarista-delete-all-timeslots" value="timeslots_<?php echo $key ?>"></th>
										<th><?php esc_html_e('Time', 'calendarista')?></th>
										<?php if($this->supportsCost):?>
											<?php if($this->project->paymentsMode !== -1):?>
												<th><?php esc_html_e('Cost', 'calendarista')?></th>
												<?php if($this->supportsDeals):?>
												<th><?php esc_html_e('Deal', 'calendarista')?></th>
												<?php endif; ?>
											<?php endif;?>
										<?php endif; ?>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
											<th><?php esc_html_e('Seats', 'calendarista')?></th>
											<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
											<th><?php esc_html_e('Seats Max', 'calendarista')?></th>
											<?php endif; ?>
											<th><?php esc_html_e('Seats Min', 'calendarista')?></th>
										<?php endif; ?>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
											<th><?php esc_html_e('Before pad', 'calendarista')?></th>
											<th><?php esc_html_e('After pad', 'calendarista')?></th>
										<?php endif; ?>
										<?php if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE):?>
										<th><?php esc_html_e('Start time', 'calendarista')?></th>
										<?php endif;?>
									</thead>
									<tbody class="timeslots">
										<?php foreach($weekday as $timeslot):?>
										<tr class="<?php echo $this->timeslotSelected($timeslot->id);?>">
											<td>
												<input id="checkbox_<?php echo $timeslot->id ?>" type="checkbox" name="timeslots[]" value="<?php echo $timeslot->id ?>"> 
											</td>
											<td>
												<?php if(!in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
												<button type="button" class="edit-linkbutton" name="editTimeslots" value="<?php echo $timeslot->id; ?>">
												<?php endif; ?>
													<?php echo $timeslot->timeslot ?> 
												<?php if(!in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
												</button>
												<?php endif; ?>
											</td>
											<?php if($this->supportsCost):?>
												<?php if($this->project->paymentsMode !== -1):?>
												<td><?php echo number_format($timeslot->cost, 2, '.', '') ?></td>
													<?php if($this->supportsDeals):?>
													<td><input name="deal[]" value="<?php echo $timeslot->id ?>" type="checkbox" <?php echo $timeslot->deal ? 'checked' : '' ?>  <?php echo !$this->dealEnabled ? 'disabled' : '' ?>/></td>
													<?php endif; ?>
												<?php endif; ?>
											<?php endif; ?>
											<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
												<td><?php echo $timeslot->seats ?></td>
												<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
												<td><?php echo $timeslot->seats > 0 ? $timeslot->seatsMaximum : '--' ?></td>
												<?php endif; ?>
												<td><?php echo $timeslot->seats > 0 ? $timeslot->seatsMinimum : '--' ?></td>
											<?php endif; ?>
											<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
												<td><?php echo $timeslot->paddingTimeBefore ?></td>
												<td><?php echo $timeslot->paddingTimeAfter ?></td>
											<?php endif; ?>
											<?php if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE):?>
												<td><input type="radio" name="starttime_<?php echo $timeslot->weekday ?>" <?php echo $timeslot->startTime ? 'checked' : '' ?> value="<?php echo $timeslot->id ?>"></td>
											<?php endif;?>
										</tr>
										<?php endforeach;?>
									</tbody>
								</table>
							</li>
						</ul>
					</li>
				</ul>
			<?php endforeach;?>
			<?php else:?>
				<div>
					<?php esc_html_e('No weekly timeslots found.', 'calendarista')?>
				</div>
			<?php endif; ?>
			<?php else: ?>
			<?php if(count($this->selectedDateTimeslots) > 0):?>
				<ul>
					<li class="horizontal-list-item medium-list-size">
						<ul>
							<li>
								<table class="widefat timeslots_<?php echo $this->selectedDate ?>">
									<caption><strong><?php echo $this->selectedDate ?></strong></caption>
									<thead>
										<th><input type="checkbox" class="calendarista-delete-all-timeslots" value="timeslots_<?php echo $this->selectedDate ?>"></th>
										<th><?php esc_html_e('Time', 'calendarista')?></th>
										<?php if($this->supportsCost):?>
											<?php if($this->project->paymentsMode !== -1):?>
												<th><?php esc_html_e('Cost', 'calendarista')?></th>
													<?php if($this->supportsDeals):?>
														<th><?php esc_html_e('Deal', 'calendarista')?></th>
													<?php endif; ?>
											<?php endif; ?>
										<?php endif; ?>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
											<th><?php esc_html_e('Seats', 'calendarista')?></th>
											<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
											<th><?php esc_html_e('Seats Max', 'calendarista')?></th>
											<?php endif; ?>
											<th><?php esc_html_e('Seats Min', 'calendarista')?></th>
										<?php endif; ?>
										<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
											<th><?php esc_html_e('Before pad', 'calendarista')?></th>
											<th><?php esc_html_e('After pad', 'calendarista')?></th>
										<?php endif; ?>
										<?php if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE):?>
										<th><?php esc_html_e('Start time', 'calendarista')?></th>
										<?php endif;?>
									</thead>
									<tbody class="timeslots">
										<?php foreach($this->selectedDateTimeslots as $timeslot):?>
										<tr class="<?php echo $this->timeslotSelected($timeslot->id);?>">
											<td>
												<input id="checkbox_<?php echo $timeslot->id ?>" type="checkbox" name="timeslots[]" value="<?php echo $timeslot->id ?>"> 
											</td>
											<td>
												<button type="button" class="edit-linkbutton" name="editTimeslots" value="<?php echo $timeslot->id; ?>">
													<?php echo $timeslot->timeslot ?> 
												</button>
											</td>
											<?php if($this->supportsCost):?>
												<?php if($this->project->paymentsMode !== -1):?>
													<td><?php echo number_format($timeslot->cost, 2, '.', '') ?></td>
													<?php if($this->supportsDeals):?>
														<td><input name="deal[]" value="<?php echo $timeslot->id ?>" type="checkbox" <?php echo $timeslot->deal ? 'checked' : '' ?> <?php echo !$this->dealEnabled ? 'disabled' : '' ?>/></td>
													<?php endif; ?>
												<?php endif; ?>
											<?php endif; ?>
											<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
												<td><?php echo $timeslot->seats ?></td>
												<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
												<td><?php echo $timeslot->seats > 0 ? $timeslot->seatsMaximum : '--' ?></td>
												<?php endif; ?>
												<td><?php echo $timeslot->seats > 0 ? $timeslot->seatsMinimum : '--' ?></td>
											<?php endif; ?>
											<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
												<td><?php echo $timeslot->paddingTimeBefore ?></td>
												<td><?php echo $timeslot->paddingTimeAfter ?></td>
											<?php endif; ?>
											<?php if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE):?>
												<td><input type="radio" name="starttime_<?php echo $timeslot->weekday ?>" <?php echo $timeslot->startTime ? 'checked' : '' ?> value="<?php echo $timeslot->id ?>"></td>
											<?php endif;?>
										</tr>
										<?php endforeach;?>
									</tbody>
								</table>
							</li>
						</ul>
					</li>
				</ul>
				<?php else:?>
				<div>
					<?php esc_html_e('No timeslots by selected date found.', 'calendarista')?>
				</div>
				<?php endif; ?>
				<?php endif; ?>
				<?php if($this->weeklyTimeslots->count() > 0 || count($this->selectedDateTimeslots) > 0):?>
				<br class="clear">
				<p class="submit">
					<button type="submit" name="calendarista_delete" class="button button-primary delete-timeslots" disabled><?php esc_html_e('Delete', 'calendarista') ?></button>
					<button type="submit" name="calendarista_update" class="button button-primary update-timeslots"  value="<?php echo $this->availabilityId ?>" disabled><?php esc_html_e('Update', 'calendarista') ?></button>
					<button type="submit" name="calendarista_starttime_changed" class="button button-primary update-timeslots"  value="<?php echo $this->availabilityId ?>"><?php esc_html_e('Update Start time', 'calendarista') ?></button>
				</p>
				<?php endif; ?>
			</form>
			<br class="clear">
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.timeslot = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.timeslot.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.dateTimepickerOptions = {
					'showHour': false
					, 'showMinute': false
					, 'showTime': false
					, 'alwaysSetTime': false
					, 'dateFormat': 'yy-mm-dd'
					, 'minDate': 0
				};
				this.requestUrl = options['requestUrl'];
				this.projectId = options['projectId'];
				this.actionCreateTimeslot = 'calendarista_create_timeslot';
				this.actionAutogenTimeslot = 'calendarista_autogen_timeslots';
				this.$availabilityId = $('select[name="availabilityId"]');
				this.$selectedDate = $('select[name="selectedDate"]');
				this.$form = $('.calendarista_form');
				this.$weekdayCheckboxes = $('.timeslots input[name="timeslots[]"]');
				this.$deleteAllTimeslots = $('.calendarista-delete-all-timeslots');
				this.$timeslotsDeleteButton = $('.delete-timeslots');
				this.$timeslotsUpdateButton = $('.update-timeslots');
				this.$deals = $('.timeslots input[name="deal[]"]');
				this.$returnTrip = $('input[name="returnTrip"]');
				this.$returnTrip.on('change', function(e){
					var requestUrl = context.requestUrl.replace(/&?returnTrip=([^&]$|[^&]*)/i, "");
					window.location.href = requestUrl + '&returnTrip=' + $(this).val();
				});
				this.$deals.on('change', function(){
					context.$timeslotsUpdateButton.prop('disabled', false);
				});
				this.$createTimeslotsPlaceHolder = $('.create_timeslots_placeholder');
				this.$autogenTimeslotsPlaceHolder = $('.autogen_timeslots_placeholder');
				this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'timeslots'});
				this.submitFormDelegate = calendarista.createDelegate(this, this.submitForm);
				this.$availabilityId.on('change', function(e){
					var selectedIndex = parseInt($(this).val(), 10)
						, requestUrl = context.requestUrl.replace(/&?availabilityId=([^&]$|[^&]*)/i, "");
					if(selectedIndex){
						requestUrl += '&availabilityId=' + selectedIndex;
					}
					window.location.href = requestUrl;
				});
				this.$selectedDate.on('change', function(e){
					var selectedDate = $(this).val()
						, requestUrl = context.requestUrl.replace(/&?selectedDate=([^&]$|[^&]*)/i, "");
					if(selectedDate){
						requestUrl += '&selectedDate=' + selectedDate;
					}
					window.location.href = requestUrl;
				});
				this.$deleteAllTimeslots.on('change', function(e){
					var $target = $(this)
						, hasChecked = $target.is(':checked')
						, $container = $('.' + $target.val())
						, $checkboxes = $container.find('input[name="timeslots[]"]');
					if(hasChecked){
						$checkboxes.prop('checked', true);
					}else{
						$checkboxes.prop('checked', false);
					}
					context.deleteButtonState();
				});
				this.$weekdayCheckboxes.on('change', function(e){
					context.deleteButtonState();
				});
				this.$createTimeslotsButton = $('button[name="createTimeslots"]');
				this.$editTimeslotsButton = $('button[name="editTimeslots"]');
				this.$autogenTimeslotsButton = $('button[name="autogenTimeslots"]');
				this.$createTimeslotsButton.on('click', function(e){
					var model = [
							{ 'name': 'projectId', 'value':  context.projectId }
							, { 'name': 'availabilityId', 'value': parseInt(context.$availabilityId.val(), 10) }
							, { 'name': 'returnTrip', 'value': $('input[name="returnTrip"]:checked').val() }
							, { 'name': 'action', 'value': context.actionCreateTimeslot }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }
						];
					context.$createTimeslotsModalDialog.dialog('open');
					context.createEditTimeslotButtonText(0);
					context.ajax.request(context, context.createTimeslotResponse, $.param(model));
				});
				this.$editTimeslotsButton.on('click', function(e){
					var id = $(e.currentTarget).val()
						, model = [
							{ 'name': 'projectId', 'value':  context.projectId }
							, { 'name': 'availabilityId', 'value': parseInt(context.$availabilityId.val(), 10) }
							, { 'name': 'returnTrip', 'value': $('input[name="returnTrip"]:checked').val() }
							, { 'name': 'id', 'value':  parseInt(id, 10) }
							, { 'name': 'action', 'value': context.actionCreateTimeslot }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }
						];
					context.$createTimeslotsModalDialog.dialog('open');
					context.createEditTimeslotButtonText(1);
					context.ajax.request(context, context.createTimeslotResponse, $.param(model));
				});
				this.$autogenTimeslotsButton.on('click', function(e){
					var model = [
							{ 'name': 'projectId', 'value':  context.projectId }
							, { 'name': 'availabilityId', 'value': parseInt(context.$availabilityId.val(), 10) }
							, { 'name': 'returnTrip', 'value': $('input[name="returnTrip"]:checked').val() }
							, { 'name': 'action', 'value': context.actionAutogenTimeslot }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }
						];
					context.$autogenTimeslotsModalDialog.dialog('open');
					context.ajax.request(context, context.autogenTimeslotResponse, $.param(model));
				});
				this.$createTimeslotsModalDialog = $('.create-timeslots-modal').dialog({
					autoOpen: false
					, height: '480'
					, width: '640'
					, modal: true
					, resizable: false
					, dialogClass: 'calendarista-dialog'
					, buttons: [
						{
							'text': 'Create'
							, 'name': 'create'
							, 'click':  function(e){
								var $target = $(e.currentTarget)
									, $form = context.$createTimeslotsModalDialog.dialog('widget').find('form');
								if(!Calendarista.wizard.isValid($form)){
									e.preventDefault();
									return false;
								}
								$form.append('<input type="hidden" name="calendarista_create" />');
								$form.submit();
							}
						}
						, {
							'text': 'Update'
							, 'name': 'update'
							, 'click':  function(e){
								var $target = $(e.currentTarget)
									, $form = context.$createTimeslotsModalDialog.dialog('widget').find('form');
								if(!Calendarista.wizard.isValid($form)){
									e.preventDefault();
									return false;
								}
								$form.append('<input type="hidden" name="calendarista_update" />');
								$form.submit();
							}
						}
						, {
							'text': 'Delete'
							, 'name': 'delete'
							, 'click':  function(e){
								var $form = context.$createTimeslotsModalDialog.dialog('widget').find('form');
								$form.append('<input type="hidden" name="calendarista_delete" />');
								$form.submit();
							}
						}
						, {
							'text': 'Close'
							, 'click':  function(){
								context.$createTimeslotsModalDialog.dialog('close');
							}
						}
					]
				});
				this.$autogenTimeslotsModalDialog = $('.autogen-timeslots-modal').dialog({
					autoOpen: false
					, height: '480'
					, width: '640'
					, modal: true
					, resizable: false
					, dialogClass: 'calendarista-dialog'
					, buttons: [
						{
							'text': 'Create'
							, 'name': 'create'
							, 'click':  function(e){
								var $target = $(e.currentTarget)
									, $form = context.$autogenTimeslotsModalDialog.dialog('widget').find('form');
								if(!Calendarista.wizard.isValid($form)){
									e.preventDefault();
									return false;
								}
								$dialog = $('<p title="<?php echo $this->decodeString(__('Auto generate timeslots', 'calendarista')) ?>"><?php echo $this->decodeString(__('If there are existing timeslots for selected day, these will be wiped out and replaced. Are you sure?', 'calendarista')); ?></p>').dialog({
									dialogClass: 'calendarista-dialog'
									, buttons: {
										'Yes': function() {
											$form.append('<input type="hidden" name="calendarista_create" value="0" />');
											$form.submit();
										}
										, 'Cancel':  function() {
											$dialog.dialog('close');
										}
									}
								});
							}
						}
						, {
							'text': 'Update'
							, 'name': 'update'
							, 'click':  function(e){
								var $target = $(e.currentTarget)
									, $form = context.$autogenTimeslotsModalDialog.dialog('widget').find('form');
								if(!Calendarista.wizard.isValid($form)){
									e.preventDefault();
									return false;
								}
								$dialog = $('<p title="<?php echo $this->decodeString(__('This is an update operation', 'calendarista')) ?>"><?php echo $this->decodeString(__('If you intended to auto generate slots, use the create button instead.', 'calendarista')); ?></p>').dialog({
									dialogClass: 'calendarista-dialog'
									, buttons: {
										'Yes': function() {
											$form.append('<input type="hidden" name="calendarista_create" value="1" />');
											$form.submit();
										}
										, 'Cancel':  function() {
											$dialog.dialog('close');
										}
									}
								});
							}
						}
						, {
							'text': 'Close'
							, 'click':  function(){
								context.$autogenTimeslotsModalDialog.dialog('close');
							}
						}
					]
				});
			};
			calendarista.timeslot.prototype.modalDialogButtons = function($dialog, status){
				var $buttonCreate = $dialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="create"]')
					, $buttonUpdate = $dialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="update"]');
				$buttonCreate.prop('disabled', false).removeClass('ui-state-disabled');
				$buttonUpdate.prop('disabled', false).removeClass('ui-state-disabled');
				switch(status){
					case 0:
						$buttonCreate.prop('disabled', true).addClass('ui-state-disabled');
					break;
					case 1:
						$buttonUpdate.prop('disabled', true).addClass('ui-state-disabled');
					break;
				}
			};
			calendarista.timeslot.prototype.deleteButtonState = function(){
				var hasChecked = this.$weekdayCheckboxes.is(':checked');
				if(hasChecked){
					this.$timeslotsDeleteButton.prop('disabled', false);
				}else{
					this.$timeslotsDeleteButton.prop('disabled', true);
				}
			};
			calendarista.timeslot.prototype.submitForm = function(e){
				this.$form[0].submit();
			};
			calendarista.timeslot.prototype.weekdaySelectionHandlers = function($root){
				var context = this;
				if(this.$weekday){
					this.$weekday.off();
				}
				this.$weekday = $root.find('select[name="weekday"]');
				this.$manualDaySelection = $root.find('.manual-date-selection');
				this.$weekday.on('change', function(e){
					context.weekdaySelectionChanged();
				});
				this.weekdaySelectionChanged();
			};
			calendarista.timeslot.prototype.weekdaySelectionChanged = function(e){
				var selectedIndex = parseInt(this.$weekday.val(), 10);
				this.$manualDaySelection.hide();
				if(selectedIndex === -1){
					this.$manualDaySelection.show();
				}
			};
			calendarista.timeslot.prototype.createTimeslotResponse = function(result){
				var $day;
				this.$createTimeslotsPlaceHolder.replaceWith('<div class="create_timeslots_placeholder">' + result + '</div>');
				this.$createTimeslotsPlaceHolder = $('.create_timeslots_placeholder');
				$day = this.$createTimeslotsPlaceHolder.find('input[name="day"]');
				$day.datepicker('destroy');
				$day.removeClass('hasDatepicker').removeProp('id');
				$day.datetimepicker(this.dateTimepickerOptions);
				this.weekdaySelectionHandlers(this.$createTimeslotsPlaceHolder);
				this.initializeTimepickerFields(this.$createTimeslotsPlaceHolder);
				this.initSeats(this.$createTimeslotsPlaceHolder);
				this.modeSelection(this.$createTimeslotsPlaceHolder);
			};
			calendarista.timeslot.prototype.modeSelection = function($root){
				var context = this
					, $mode = $root.find('input[name="mode"]')
					, $singleCustomSlotRow = $root.find('.single-custom-slot');
				$mode.off();
				$mode.on('change', function(e){
					var val = parseInt($(this).val(), 10);
					if(val === 1){
						$singleCustomSlotRow.show();
						return;
					}
					$singleCustomSlotRow.hide();
				});
			};
			calendarista.timeslot.prototype.autogenTimeslotResponse = function(result){
				var $day
					, context = this;
				this.$autogenTimeslotsPlaceHolder.replaceWith('<div class="autogen_timeslots_placeholder">' + result + '</div>');
				this.$autogenTimeslotsPlaceHolder = $('.autogen_timeslots_placeholder');
				$day = this.$autogenTimeslotsPlaceHolder.find('input[name="day"]');
				$day.datepicker('destroy');
				$day.removeClass('hasDatepicker').removeProp('id');
				$day.datetimepicker(this.dateTimepickerOptions);
				this.weekdaySelectionHandlers(this.$autogenTimeslotsPlaceHolder);
				this.initializeTimepickerFields(this.$autogenTimeslotsPlaceHolder);
				this.initSeats(this.$autogenTimeslotsPlaceHolder);
			};
			calendarista.timeslot.prototype.initSeats = function($root){
				var context = this;
				this.$seats = $root.find('input[name="seats"]');
				this.$seatsMaximum = $('input[name="seatsMaximum"]');
				this.$seatsMinimum = $root.find('input[name="seatsMinimum"]');
				this.$seats.off();
				this.$seats.on('change', function(){
					context.validateSeatsMaximum();
					context.validateSeatsMinimum();
				});
				this.validateSeatsMaximum();
				this.validateSeatsMinimum();
			};
			calendarista.timeslot.prototype.validateSeatsMaximum = function(){
				var seats = parseInt(this.$seats.val(), 10)
					, seatsMaximum = parseInt(this.$seatsMaximum.val(), 10);
				this.$seatsMaximum.prop('disabled', false);
				if(this.$seats.length !== 0 && (isNaN(seats) || seats === 0)){
					this.$seatsMaximum.val(0);
					this.$seatsMaximum.prop('disabled', true);
				}
			};
			calendarista.timeslot.prototype.validateSeatsMinimum = function(){
				var seats = parseInt(this.$seats.val(), 10)
					, seatsMinimum = parseInt(this.$seatsMinimum.val(), 10);
				this.$seatsMinimum.prop('disabled', false);
				if(this.$seats.length !== 0 && (isNaN(seats) || seats === 0)){
					this.$seatsMinimum.val(1);
					this.$seatsMinimum.prop('disabled', true);
				}
			};
			calendarista.timeslot.prototype.initializeTimepickerFields = function($root){
				var context = this;
				this.$timeslotTextbox = $root.find('input[name="timeslot"]');
				this.$startIntervalTextbox = $root.find('input[name="startInterval"]');
				this.$timeSplitTextbox = $root.find('input[name="timeSplit"]');
				this.$endTimeTextbox = $root.find('input[name="endTime"]');
				this.$timeslotTextbox.timepicker({'timeFormat': 'h:mm tt'});
				this.$paddingTimeBefore = $root.find('select[name="paddingTimeBefore"]');
				this.$paddingTimeAfter = $root.find('select[name="paddingTimeAfter"]');
				this.$startIntervalTextbox.timepicker({'hour': 0});
				this.$endTimeTextbox.timepicker({'hour': 0});
				this.$timeSplitTextbox.timepicker({'onSelect': function(){
						context.fillpaddingTime();
					}
				});
				this.fillpaddingTime();
			};
			calendarista.timeslot.prototype.fillpaddingTime = function(){
				var time
					, min
					, i;
				if(this.$timeSplitTextbox.length === 0){
					return;
				}
				time = this.$timeSplitTextbox.val().split(':');
				min = time ? (parseInt(time[0], 10) * 60) : 0;
				if(time.length === 2 && time[1]){
					min += parseInt(time[1], 10);
				}
				if(this.$paddingTimeBefore.length > 0){
					this.fillPaddingTimeList(this.$paddingTimeBefore, min);
				}
				if(this.$paddingTimeAfter.length > 0){
					this.fillPaddingTimeList(this.$paddingTimeAfter, min);
				}
				if(!isNaN(min) && min > 0){
					Calendarista.wizard.isValid(this.$autogenTimeslotsModalDialog.dialog('widget').find('form'));
				}
			};
			calendarista.timeslot.prototype.fillPaddingTimeList = function($list, min){
				var i;
				$list[0].length = 0;
				$list.append($('<option>', {
					'value': '0',
					'text': '<?php echo $this->decodeString(__('Off', 'calendarista')) ?>'
				}));
				if(isNaN(min) || min === 0){
					$list.prop('disabled', true);
					return;
				}
				$list.prop('disabled', false);
				for(i = min; i <= 1380;i += min){
					$list.append($('<option>', {
						'value': i,
						'text': i
					}));
				}
			};
			calendarista.timeslot.prototype.createEditTimeslotButtonText = function(status){
				var $createButton = this.$createTimeslotsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="create"]')
					, $updateButton = this.$createTimeslotsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="update"]')
					, $deleteButton = this.$createTimeslotsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="delete"]');
				$createButton.prop('disabled', false).removeClass('ui-state-disabled');
				$deleteButton.prop('disabled', true).addClass('ui-state-disabled');
				if(status){
					$createButton.prop('disabled', true).addClass('ui-state-disabled');
					$deleteButton.prop('disabled', false).removeClass('ui-state-disabled');
				}
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
				return parseFloat(value) <= x;
			  },
			  priority: 32
			}
		  }
		};
		new calendarista.timeslot({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
			, 'projectId': <?php echo $this->selectedProjectId ?>
		});
		</script>
	<?php
	}
}