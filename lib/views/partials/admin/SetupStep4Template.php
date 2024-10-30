<?php
class Calendarista_SetupStep4Template extends Calendarista_ViewBase{
	public $fields;
	public $supportsCost;
	public $supportsTimeslots;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->fields = $this->getFields();
		$this->supportsCost = !in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST) || in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_AND_TIMESLOT_COST);
		$this->supportsTimeslots = in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
		$this->render();
	}
	function getFields(){
		$result = array('calendarMode'=>0, 'paymentsMode'=>-1, 'startInterval'=>'00:00', 'timesplit'=>'', 'endTime'=>'00:00', 'slotCost'=>null, 'slotSeats'=>null, 'paddingTimeBefore'=>null, 'paddingTimeAfter'=>null);
		if(isset($_POST['calendarMode'])){
			$result['calendarMode'] = (int)$_POST['calendarMode'];
		}
		if(isset($_POST['paymentsMode'])){
			$result['paymentsMode'] = (int)$_POST['paymentsMode'];
		}
		if(isset($_POST['startInterval'])){
			$result['startInterval'] = $_POST['startInterval'];
		}
		if(isset($_POST['timesplit'])){
			$result['timesplit'] = (int)$_POST['timesplit'];
		}
		if(isset($_POST['endTime'])){
			$result['endTime'] = $_POST['endTime'];
		}
		if(isset($_POST['slotCost'])){
			$result['slotCost'] = $_POST['slotCost'];
		}
		if(isset($_POST['slotSeats'])){
			$result['slotSeats'] = $_POST['slotSeats'];
		}
		if(isset($_POST['paddingTimeBefore'])){
			$result['paddingTimeBefore'] = $_POST['paddingTimeBefore'];
		}
		if(isset($_POST['paddingTimeAfter'])){
			$result['paddingTimeAfter'] = $_POST['paddingTimeAfter'];
		}
		return $result;
	}
	public function render(){
	?>
		<div id="step" data-calendarista-next-step-id="5" data-calendarista-prev-step-id="3">
			<h1><?php esc_html_e('The time you are available for booking', 'calendarista') ?></h1>
			<p class="description"><?php esc_html_e('Time slots will be generated based on the start time and the interval between each slot', 'calendarista') ?></p>
			<table class="form-table">
				<tbody>
					<tr>
					<th scope="row"><label for="weekday"><?php esc_html_e('Weekday', 'calendarista') ?></label></th>
					<td>
						<?php esc_html_e('All days of the week', 'calendarista') ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="startInterval"><?php esc_html_e('Start time', 'calendarista') ?></label></th>
					<td>
						<input id="startInterval" 
							name="startInterval" 
							type="text" 
							class="regular-text enable-readonly-input calendarista_parsley_validated" 
							data-parsley-required="true" 
							value="00:00"
							readonly/>
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
								<?php esc_html_e('The interval between each slot eg. 8:00, 9:00, 10:00, 11:00 is a 01:00 hour interval beteween each slot', 'calendarista')?>
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
					</td>
				</tr>
				<?php if($this->fields['paymentsMode'] !== -1 && $this->supportsCost):?>
				<tr>
					<th scope="row"><label for="slotCost"><?php esc_html_e('Cost', 'calendarista') ?></label></th>
					<td>
						<input id="slotCost" 
							name="slotCost" 
							type="text" 
							class="small-text calendarista_parsley_validated" 
							data-parsley-trigger="change focusout"
							data-parsley-min="0"
							data-parsley-pattern="^\d+(\.\d{1,2})?$"
							placeholder="0.00"  />
					</td>
				</tr>
				<?php endif; ?>
				<?php if(in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_PADDING)):?>
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
				<?php if(in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_TIMESLOT_AND_SEATS)):?>
				<tr>
					<th scope="row"><label for="slotSeats"><?php esc_html_e('Seats', 'calendarista') ?></label></th>
					<td>
						<input id="slotSeats" 
							name="slotSeats" 
							type="text" 
							class="small-text calendarista_parsley_validated" 
							data-parsley-type="digits" 
							placeholder="0"
							<?php echo isset($_POST['seats']) && (int)$_POST['seats'] > 0 ? 'disabled' : '' ?>/>
						<?php if(isset($_POST['seats']) && (int)$_POST['seats'] > 0): ?>
							<p><?php esc_html_e('Seat already set per day previously, hence you cannot set it again per time slot', 'calendarista') ?></p>
						<?php else: ?>
							<p class="description"><?php esc_html_e('Applies for each slot selected. The default value of 0 means seats are unlimited eg. If you do not want a slot to be booked more than once, set the value 1', 'calendarista') ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.setupStep4 = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupStep4.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$root = $('#step');
					this.$startIntervalTextbox = this.$root.find('input[name="startInterval"]');
					this.$timeSplitTextbox = this.$root.find('input[name="timeSplit"]');
					this.$endTimeTextbox = this.$root.find('input[name="endTime"]');
					this.$startIntervalTextbox.timepicker({'hour': 0});
					this.$endTimeTextbox.timepicker({'hour': 0});
					this.$paddingTimeBefore = this.$root.find('select[name="paddingTimeBefore"]');
					this.$paddingTimeAfter = this.$root.find('select[name="paddingTimeAfter"]');
					this.$timeSplitTextbox.timepicker({'onSelect': function(){
							context.fillpaddingTime();
						}
					});
					this.fillpaddingTime();
				};
				calendarista.setupStep4.prototype.fillpaddingTime = function(){
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
						Calendarista.wizard.isValid(this.$root);
					}
				};
				calendarista.setupStep4.prototype.fillPaddingTimeList = function($list, min){
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
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupStep4({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}