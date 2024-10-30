<?php
class Calendarista_AutogenTimeslotsTemplate extends Calendarista_ViewBase{
	public $availabilities;
	public $availabilityId;
	public $project;
	public $supportsCost;
	public $returnTrip;
	function __construct(){
		parent::__construct();
		$this->availabilityId = (int)$this->getPostValue('availabilityId', -1);
		$selectedDate = isset($_GET['selectedDate']) ? sanitize_text_field($_GET['selectedDate']) : null;
		$this->returnTrip = isset($_REQUEST['returnTrip']) ? (int)$_REQUEST['returnTrip'] : 0;
		if(!$selectedDate){
			$selectedDate = $this->getPostValue('day');
		}
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$this->availabilities = $availabilityRepo->readAll($this->selectedProjectId);
		$this->requestUrl = admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=2&projectId=' . $this->selectedProjectId;
		if($this->availabilityId !== -1){
			$this->requestUrl .= '&availabilityId=' . $this->availabilityId;
		}
		if($selectedDate){
			$this->requestUrl .= '&selectedDate=' . $selectedDate;
		}
		$projectRepo = new Calendarista_ProjectRepository();
		$this->project = $projectRepo->read($this->selectedProjectId);
		$this->supportsCost = !in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST) || in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_AND_TIMESLOT_COST);
		$this->render();
	}
	public function render(){
	?>
	<p class="description">
		<?php esc_html_e('Autogenerates timeslots, note that all timeslots in selected weekday will be wiped out and replaced.', 'calendarista')?>
	</p>
	<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
		<input type="hidden" name="controller" value="calendarista_autogen_timeslots" />
		<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>">
		<input type="hidden" name="availabilityId" value="<?php echo $this->availabilityId ?>">
		<input type="hidden" name="returnTrip" value="<?php echo $this->returnTrip ?>">
		<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
		<table class="form-table">
			<tbody>
				<tr>
					<th></th>
					<td>
						<input id="applyToService" 
							name="applyToService"
							type="checkbox"><?php esc_html_e('Apply to entire service', 'calendarista') ?>
							<p class="description">
								<?php esc_html_e('Generates the slots in all availabilities', 'calendarista')?>
							</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="weekday"><?php esc_html_e('Weekday', 'calendarista') ?></label></th>
					<td>
						<select id="weekday" 
							name="weekday">
							<option value="0"><?php esc_html_e('All days of the week', 'calendarista')?></option>
							<option value="7"><?php esc_html_e('Sunday', 'calendarista')?></option>
							<option value="1"><?php esc_html_e('Monday', 'calendarista')?></option>
							<option value="2"><?php esc_html_e('Tuesday', 'calendarista')?></option>
							<option value="3"><?php esc_html_e('Wednesday', 'calendarista')?></option>
							<option value="4"><?php esc_html_e('Thursday', 'calendarista')?></option>
							<option value="5"><?php esc_html_e('Friday', 'calendarista')?></option>
							<option value="6"><?php esc_html_e('Saturday', 'calendarista')?></option>
							<option value="-1"><?php esc_html_e('Manual date selection', 'calendarista')?></option>
						</select>
					</td>
				</tr>
				<tr class="manual-date-selection">
					<th scope="row">
						<label for="day"><?php esc_html_e('Date', 'calendarista')?></label>
					</th>
					<td>
						<input type="text" 
							id="day" 
							name="day" 
							class="regular-text enable-readonly-input calendarista_parsley_validated" 
							readonly
							data-parsley-required="true">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="startInterval"><?php esc_html_e('Start interval', 'calendarista') ?></label></th>
					<td>
						<input id="startInterval" 
							name="startInterval" 
							type="text" 
							class="regular-text enable-readonly-input calendarista_parsley_validated" 
							data-parsley-required="true" 
							value="00:00"
							readonly/>
							<p class="description">
								<?php esc_html_e('Start splitting slots from this time onwards', 'calendarista')?>
							</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="timeSplit"><?php esc_html_e('Time length', 'calendarista') ?></label></th>
					<td>
						<input id="timeSplit" 
							name="timeSplit" 
							type="text" 
							class="regular-text enable-readonly-input calendarista_parsley_validated" 
							data-parsley-notdefault="00:00"
							data-parsley-error-message="<?php esc_html_e('Time length is required.', 'calendarista') ?>"
							data-parsley-required="true" 
							placeholder="00:00"
							readonly/>
							<p class="description">
								<?php esc_html_e('The slots will be split into equal intervals', 'calendarista')?>
							</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="endTime"><?php esc_html_e('End time', 'calendarista') ?></label></th>
					<td>
						<input id="endTime" 
							name="endTime"
							type="text" 
							class="regular-text enable-readonly-input calendarista_parsley_validated" 
							data-parsley-required="true" 
							value="00:00"
							readonly/>
							<p class="description">
								<?php esc_html_e('Generate slots until above time is reached', 'calendarista')?>
							</p>
					</td>
				</tr>
				<?php if($this->project->paymentsMode !== -1 && $this->supportsCost):?>
				<tr>
					<th scope="row"><label for="cost"><?php esc_html_e('Cost', 'calendarista') ?></label></th>
					<td>
						<input id="cost" 
							name="cost" 
							type="text" 
							class="small-text calendarista_parsley_validated" 
							data-parsley-trigger="change focusout"
							data-parsley-min="0"
							data-parsley-pattern="^\d+(\.\d{1,2})?$"
							placeholder="0.00"  />
					</td>
				</tr>
				<?php endif; ?>
				<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
				<tr>
					<td colspan="2">
						<p class="description"><?php esc_html_e('Note: The minimum padding time possible is the time length.', 'calendarista') ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="paddingTimeBefore"><?php esc_html_e('Padding time before', 'calendarista') ?></label></th>
					<td>
						<select id="paddingTimeBefore" 
							name="paddingTimeBefore"> 
							<option value="0"><?php esc_html_e('Off', 'calendarista')?></option>
						</select>
						<?php esc_html_e('minutes', 'calendarista') ?>
						<p class="description"><?php esc_html_e('Adds waiting period before each booked timeslot.', 'calendarista') ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="paddingTimeAfter"><?php esc_html_e('Padding time after', 'calendarista') ?></label></th>
					<td>
						<select id="paddingTimeAfter" 
							name="paddingTimeAfter" > 
							<option value="0"><?php esc_html_e('Off', 'calendarista')?></option>
						</select>
						<?php esc_html_e('minutes', 'calendarista') ?>
						<p class="description"><?php esc_html_e('Adds waiting period after each booked timeslot.', 'calendarista') ?></p>
					</td>
				</tr>
				<?php endif; ?>
				<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
				<tr>
					<th scope="row"><label for="seats"><?php esc_html_e('Seats', 'calendarista') ?></label></th>
					<td>
						<input id="seats" 
							name="seats" 
							type="text" 
							class="small-text calendarista_parsley_validated" 
							data-parsley-type="digits" 
							placeholder="0"/>
						<p class="description"><?php esc_html_e('The default value of 0 means seats are unlimited', 'calendarista') ?></p>
					</td>
				</tr>
				<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
				<tr>
					<th scope="row"><label for="seatsMaximum"><?php esc_html_e('Seats Maximum', 'calendarista') ?></label></th>
					<td>
						<input id="seatsMaximum" 
							name="seatsMaximum" 
							type="text" 
							class="small-text" 
							data-parsley-trigger="change focusout"
							placeholder="1"
							data-parsley-type="digits"
							data-parsley-morethan="#seats" />
						<p class="description"><?php esc_html_e('Maximum number of seats selectable when group booking is enabled. 0 means no limit.', 'calendarista') ?></p>
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<th scope="row"><label for="seatsMinimum"><?php esc_html_e('Seats Minimum', 'calendarista') ?></label></th>
					<td>
						<input id="seatsMinimum" 
							name="seatsMinimum" 
							type="text" 
							class="small-text  calendarista_parsley_validated" 
							placeholder="1"
							data-parsley-trigger="change focusout"
							data-parsley-lessthan="#seats"
							data-parsley-type="digits" />
						<p class="description"><?php esc_html_e('Force a minimum number of seats required to make a booking.', 'calendarista') ?></p>
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<td colspan="2">
						<p class="description">
							<?php esc_html_e('Note: Clicking update will update attributes such as seats, cost etc, if a matching slot is found based on the time start, length and end properties chosen above.', 'calendarista')?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php
	}
}