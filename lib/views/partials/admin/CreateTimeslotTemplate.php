<?php
class Calendarista_CreateTimeslotTemplate extends Calendarista_ViewBase{
	public $timeslot;
	public $selectedId;
	public $timeslotRepo;
	public $availabilityId;
	public $supportsCost;
	public $dealEnabled;
	public $project;
	public $returnTrip;
	function __construct(){
		parent::__construct();
		$this->availabilityId = (int)$this->getPostValue('availabilityId', -1);
		$this->selectedId = (int)$this->getPostValue('id', -1);
		$this->returnTrip = isset($_REQUEST['returnTrip']) ? (int)$_REQUEST['returnTrip'] : 0;
		$selectedDate = isset($_GET['selectedDate']) ? sanitize_text_field($_GET['selectedDate']) : null;
		if(!$selectedDate){
			$selectedDate = $this->getPostValue('day');
		}
		$this->timeslotRepo = new Calendarista_TimeslotRepository();
		$this->timeslot = new Calendarista_Timeslot(array());
		if($this->selectedId !== -1){
			$this->timeslot = $this->timeslotRepo->read($this->selectedId);
		}
		$this->requestUrl = admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=2&projectId=' . $this->selectedProjectId;
		$this->project = $this->getProject();
		if($this->availabilityId !== -1){
			$this->requestUrl .= '&availabilityId=' . $this->availabilityId;
			$availabilityRepo = new Calendarista_AvailabilityRepository();
			$this->availability = $availabilityRepo->read($this->availabilityId);
			if($this->availability){
				$this->supportsDeals = in_array($this->project->calendarMode, array(
													Calendarista_CalendarMode::SINGLE_DAY_AND_TIME
													, Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_WITH_PADDING
													, Calendarista_CalendarMode::MULTI_DATE_AND_TIME));
				$this->dealEnabled = $this->availability->timeDisplayMode === 1/*Deals*/ ? true : false;
			}
		}
		if($selectedDate){
			$this->requestUrl .= '&selectedDate=' . $selectedDate;
		}
		$this->render();
	}
	public function timeslotEditMode(){
		return isset($this->timeslot->id) && $this->timeslot->id !== -1;
	}
	public function timeslotCreate(){
		return $this->timeslot->timeslot === null;
	}
	public function weekdaySelected($weekday){
		return $this->timeslot->weekday === $weekday ? 'selected=selected' : '';
	}
	public function render(){
	?>
		<form class="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
			<input type="hidden" name="controller" value="calendarista_timeslot" />
			<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>">
			<input type="hidden" name="availabilityId" value="<?php echo $this->availabilityId ?>">
			<input type="hidden" name="id" value="<?php echo $this->timeslot->id ?>" />
			<input type="hidden" name="returnTrip" value="<?php echo $this->returnTrip ?>">
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<table class="form-table calendarista-jqueryui-dialog">
				<tbody>
				<?php if($this->timeslot->id == -1):?>
					<tr>
						<th></th>
						<td>
							<input id="applyToService" 
								name="applyToService"
								type="checkbox"><?php esc_html_e('Apply to entire service', 'calendarista') ?>
								<p class="description">
									<?php esc_html_e('Generates the slot in all availabilities', 'calendarista')?>
								</p>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><label for="weekday"><?php esc_html_e('Weekday', 'calendarista') ?></label></th>
						<td>
							<?php if($this->timeslotEditMode()):?>
							<input type="hidden" name="weekday" value="<?php echo $this->timeslot->weekday?>"/>
							<?php endif;?>
							<select id="weekday" name="weekday" <?php echo $this->timeslotEditMode() ? 'disabled' : ''?>>
								<option value="7" <?php echo $this->weekdaySelected(7); ?>><?php esc_html_e('Sunday', 'calendarista')?></option>
								<option value="1" <?php echo $this->weekdaySelected(1); ?>><?php esc_html_e('Monday', 'calendarista')?></option>
								<option value="2" <?php echo $this->weekdaySelected(2); ?>><?php esc_html_e('Tuesday', 'calendarista')?></option>
								<option value="3" <?php echo $this->weekdaySelected(3); ?>><?php esc_html_e('Wednesday', 'calendarista')?></option>
								<option value="4" <?php echo $this->weekdaySelected(4); ?>><?php esc_html_e('Thursday', 'calendarista')?></option>
								<option value="5" <?php echo $this->weekdaySelected(5); ?>><?php esc_html_e('Friday', 'calendarista')?></option>
								<option value="6" <?php echo $this->weekdaySelected(6); ?>><?php esc_html_e('Saturday', 'calendarista')?></option>
								<option value="-1" <?php echo $this->weekdaySelected(null); ?>><?php esc_html_e('Manual date selection', 'calendarista')?></option>
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
								data-parsley-required="true"
								value="<?php echo $this->timeslot->day ? $this->timeslot->day->format(CALENDARISTA_DATEFORMAT) : '' ?>">
						</td>
					</tr>
					<?php if(!$this->timeslotEditMode()):?>
					<tr class="mode-row">
						<th scope="row"></th>
						<td>
							<input
								name="mode" 
								type="radio"  
								value="1"
								checked><?php esc_html_e('Single custom slot', 'calendarista') ?>
								&nbsp; &nbsp;
								<input
								name="mode" 
								type="radio"  
								value="2"><?php esc_html_e('Full day', 'calendarista') ?>
							<p class="description"><?php esc_html_e('Full day will retrieve slots from the relevant week day, if exists.', 'calendarista') ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<tr class="single-custom-slot">
						<th scope="row"><label for="timeslot"><?php esc_html_e('Timeslot', 'calendarista') ?></label></th>
						<td>
							<input id="timeslot" 
								name="timeslot" 
								type="text" 
								class="regular-text enable-readonly-input calendarista_parsley_validated"  
								data-parsley-required="true"  
								placeholder="00:00" 
								value="<?php echo $this->timeslot->timeslot ?>"
								readonly/>
						</td>
					</tr>
					<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
					<tr>
						<th scope="row"><label for="seats"><?php esc_html_e('Seats', 'calendarista') ?></label></th>
						<td>
							<input id="seats" 
								name="seats" 
								type="text" 
								class="small-text calendarista_parsley_validated" 
								data-parsley-type="digits" 
								placeholder="0"
								value="<?php echo $this->timeslot->seats ?>"/>
							<p class="description"><?php esc_html_e('A value of 0 means seats are unlimited', 'calendarista') ?></p>
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
								data-parsley-morethan="#seats"
								value="<?php echo $this->timeslot->seatsMaximum ?>" />
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
								class="small-text calendarista_parsley_validated" 
								data-parsley-trigger="change focusout"
								placeholder="1"
								data-parsley-type="digits"
								data-parsley-lessthan="#seats"
								value="<?php echo $this->timeslot->seatsMinimum ?>" />
							<p class="description"><?php esc_html_e('Force a minimum number of seats required to make a booking.', 'calendarista') ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<?php if($this->project->paymentsMode !== -1):?>
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
								placeholder="0.00" 
								value="<?php echo $this->timeslot->cost ?>" />
						</td>
					</tr>
					<?php if($this->supportsDeals): ?>
					<tr>
						<th scope="row"><label for="deal"><?php esc_html_e('Deals', 'calendarista') ?></label></th>
						<td>
							<input id="deal" 
								name="deal" 
								type="checkbox" 
								value="1"
								<?php echo $this->timeslot->deal ? 'checked' : '' ?> <?php echo !$this->dealEnabled ? 'disabled' : '' ?>/>
								<?php esc_html_e('This slot cost is a deal', 'calendarista') ?>
						</td>
					</tr>
					<?php endif; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</form>
	<?php
	}
}