<?php
class Calendarista_TimeoffTemplate extends Calendarista_ViewBase{
	public $weeklyTimeslots;
	public $weekday;
	public $selectedDateTimeslots;
	public $savedHolidays;
	public $selectedDate;
	public $selectedLongDateFormat;
	public $weekdays = array();
	public $availabilityId;
	public $availabilities;
	public $project;
	function __construct( ){
		parent::__construct();
		$this->availabilityId = isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : null;
		$this->selectedDate = isset($_POST['selectedDate']) ? (string)$_POST['selectedDate'] : null;
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$this->weeklyTimeslots = new Calendarista_Timeslots();
		$this->selectedDateTimeslots = new Calendarista_Timeslots();
		$holidaysRepo = new Calendarista_HolidaysRepository();
		new Calendarista_TimeoffController(
			array($this, 'updated')
		);
		$this->savedHolidays = $holidaysRepo->readHolidayContainsTimeslot($this->selectedDate, $this->availabilityId);
		if($this->availabilityId){
			$this->weekday = (int)date('N', strtotime($this->selectedDate));
			$result1 = $timeslotRepo->readAllByWeekday($this->weekday, $this->availabilityId);
			$this->weeklyTimeslots = $this->sortTimeslots($result1);
			if($this->selectedDate){
				$result2 = $timeslotRepo->readSingleDayByAvailability($this->selectedDate, $this->availabilityId);
				$this->selectedDateTimeslots = $this->sortTimeslots($result2);
			}
		}
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$this->availabilities = $availabilityRepo->readAll($this->selectedProjectId);
		$this->project = $this->getProject();
		setlocale(LC_TIME, get_locale());
		$this->selectedLongDateFormat = strftime('%A, %d %b, %Y', strtotime($this->selectedDate));
		$this->render();
	}
	public function sortTimeslots($timeslots){
		$result = array();
		foreach($timeslots as $timeslot){
			array_push($result, $timeslot);
		}
		usort($result, array('Calendarista_TimeslotHelper', 'sortByTime'));
		return $result;
	}
	public function timeslotStrike($id){
		foreach($this->savedHolidays as $h){
			if($h['timeslotId'] === $id){
				return 'calendarista-linethrough';
			}
		}
		return null;
	}
	public function timeslotSelected($id){
		foreach($this->savedHolidays as $h){
			if($h['timeslotId'] === $id){
				return true;
			}
		}
		return null;
	}
	public function updated($result){
		if($result){
			$this->updatedNotice();
		}
	}
	public function updatedNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The timeslot has been updated.', 'calendarista'); ?></p>
		</div>
		<hr>
		<?php
	}
	public function render(){
	?>
		<p class="description">
			<strong><?php esc_html_e('Check a row(s) to take timeoff. To reinstate a timeslot, uncheck it.', 'calendarista')?></strong>
		</p>
		<div>	
			<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_timeoff" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>"/>
				<input type="hidden" name="availabilityId" value="<?php echo $this->availabilityId ?>"/>
				<input type="hidden" name="selectedDate" value="<?php echo $this->selectedDate ?>"/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<?php if(count($this->selectedDateTimeslots) === 0 && count($this->weeklyTimeslots) > 0):?>
			<ul>
				<li class="horizontal-list-item medium-list-size">
					<ul>
						<li>
							<p><strong><?php echo iconv("ISO-8859-1", "UTF-8", $this->selectedLongDateFormat);?></strong></p>
							<table class="wp-list-table calendarista wp-list-table widefat fixed striped timeslots">
								<thead>
									<th><input type="checkbox" name="delete_all_timeslots" class="calendarista-fullday-timeoff"></th>
									<th><?php esc_html_e('Time', 'calendarista')?></th>
								</thead>
								<tbody class="timeslots">
									<?php foreach($this->weeklyTimeslots as $timeslot):?>
									<tr>
										<td>
											<?php if(!$this->timeslotSelected($timeslot->id)): ?>
											<input id="checkbox_<?php echo $timeslot->id ?>" type="checkbox" 
												name="timeslots[]" value="<?php echo $timeslot->id ?>"> 
											<?php else: ?>
											<input id="checkbox_undo_<?php echo $timeslot->id ?>" type="checkbox" 
												name="timeslots_undo[]" value="<?php echo $timeslot->id ?>">
											<?php endif; ?>
											<?php esc_html_e('Timeoff', 'calendarista')?>
										</td>
										<td>
											<span class="<?php echo $this->timeslotStrike($timeslot->id) ?>"><?php echo $timeslot->timeslot ?></span>
										</td>
									</tr>
									<?php endforeach;?>
								</tbody>
							</table>
						</li>
					</ul>
				</li>
			</ul>
			<?php elseif(count($this->selectedDateTimeslots) > 0): ?>
				<ul>
					<li class="horizontal-list-item medium-list-size">
						<ul>
							<li>
								<p><strong><?php echo date('l, d M, Y', strtotime($this->selectedDate))?></strong></p>
								<table class="wp-list-table calendarista wp-list-table widefat fixed striped timeslots">
									<thead>
										<th><input type="checkbox" class="calendarista-fullday-timeoff"></th>
										<th><?php esc_html_e('Time', 'calendarista')?></th>
									</thead>
									<tbody class="timeslots">
										<?php foreach($this->selectedDateTimeslots as $timeslot):?>
										<tr>
											<td>
												<?php if(!$this->timeslotSelected($timeslot->id)): ?>
												<input id="checkbox_<?php echo $timeslot->id ?>" type="checkbox" 
													name="timeslots[]" value="<?php echo $timeslot->id ?>"> 
												<?php else: ?>
												<input id="checkbox_undo_<?php echo $timeslot->id ?>" type="checkbox" 
													name="timeslots_undo[]" value="<?php echo $timeslot->id ?>">
												<?php endif; ?>
												<?php esc_html_e('Timeoff', 'calendarista')?>
											</td>
											<td>
												<span class="<?php echo $this->timeslotStrike($timeslot->id) ?>"><?php echo $timeslot->timeslot ?></span>
											</td>
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
						<?php esc_html_e('No timeslots found.', 'calendarista')?>
					</div>
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
			calendarista.timeoff = function(options){
				this.init(options);
			};
			calendarista.timeoff.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.requestUrl = options['requestUrl'];
				this.$form = $('#calendarista_form');
				this.$weekdayCheckboxes = $('.timeslots input[type="checkbox"]');
				this.$fullDayTimeoff = $('input[name="delete_all_timeslots"]');
				this.$timeoffButton = $('button[name="timeoff"]');
				this.$updateTimeoff = $('button[name="updateTimeoff"]');
				this.submitFormDelegate = calendarista.createDelegate(this, this.submitForm);
				this.timeoffState();
				this.$fullDayTimeoff.on('change', function(e){
					var $target = $(this)
						, hasChecked = $target.is(':checked')
						, $container = $('tbody.timeslots')
						, $checkboxes = $container.find('input[type="checkbox"]');
					if(hasChecked){
						$checkboxes.prop('checked', true);
					}else{
						$checkboxes.prop('checked', false);
					}
					context.timeoffState();
				});
				this.$weekdayCheckboxes.on('change', function(e){
					context.timeoffState();
				});
			};
			calendarista.timeoff.prototype.timeoffState = function(){
				var hasChecked = this.$weekdayCheckboxes.is(':checked');
				if(hasChecked){
					this.$updateTimeoff.prop('disabled', false).removeClass('ui-state-disabled');
				}else{
					this.$updateTimeoff.prop('disabled', true).addClass('ui-state-disabled');
				}
			};
			calendarista.timeoff.prototype.submitForm = function(e){
				this.$form[0].submit();
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.timeoff({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
	<?php
	}
}