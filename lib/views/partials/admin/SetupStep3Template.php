<?php
class Calendarista_SetupStep3Template extends Calendarista_ViewBase{
	public $fields;
	public $repeatFrequency;
	public $weekdays;
	public $supportsTimeslots;
	public $monthlyRepeatDay = null;
	public $yearlyRepeatDate = null;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->repeatFrequency = Calendarista_RepeatFrequency::toArray();
		$this->weekdays = Calendarista_Weekday::toArray();
		$this->fields = $this->getFields();
		$this->supportsTimeslots = in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
		if($this->fields['availableDate']){
			$this->monthlyRepeatDay = date('jS', strtotime($this->fields['availableDate']));
			$this->yearlyRepeatDate = date('jS F', strtotime($this->fields['availableDate']));
		}
		$this->render();
	}
	function getFields(){
		$result = array('availableDate'=>null, 'calendarMode'=>0, 'repeatWeekdayList'=>array(), 'repeatFrequency'=>0, 'repeatInterval'=>1);
		if(isset($_POST['availableDate'])){
			$result['availableDate'] = $_POST['availableDate'];
		}
		if(isset($_POST['calendarMode'])){
			$result['calendarMode'] = (int)$_POST['calendarMode'];
		}
		if(isset($_POST['repeatWeekdayList'])){
			$result['repeatWeekdayList'] = (array)$_POST['repeatWeekdayList'];
		}
		if(isset($_POST['repeatFrequency'])){
			$result['repeatFrequency'] = (int)$_POST['repeatFrequency'];
		}
		if(isset($_POST['repeatInterval'])){
			$result['repeatInterval'] = (int)$_POST['repeatInterval'];
		}
		return $result;
	}
	public function repeatWeekdayChecked($value){
		if(count($this->fields['repeatWeekdayList']) > 0){
			return in_array($value, $this->fields['repeatWeekdayList']) ? 'checked' : '';
		}
		return $value === 1 ? 'checked' : '';
	}
	public function repeatFrequencySelected($value){
		return  $this->fields['repeatFrequency'] === $value ? 'selected' : '';
	}
	public function repeatIntervalSelected($value){
		return $this->fields['repeatInterval'] === $value ? 'selected' : '';
	}
	public function getRepeatIntervalLabel(){
		switch($this->fields['repeatFrequency']){
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
	public function render(){
	?>
		<div id="step" data-calendarista-next-step-id="<?php echo $this->supportsTimeslots ? 4 : 5 ?>" data-calendarista-prev-step-id="2">
			<h1><?php esc_html_e('How often are you available for booking?', 'calendarista') ?></h1>
			<p class="description"><?php esc_html_e('Based on the repeat options selected below, days will be made available for booking on the booking calendar', 'calendarista') ?></p>
			<input type="hidden" name="hasRepeat" value="1">
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<div><label for="repeatFrequency"><?php esc_html_e('How frequently are you available?', 'calendarista')?></label></div>
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
												class="calendarista_parsley_validated"
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
				</tbody>
			</table>
			<p class="description" id="summary"></p>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.setupStep3 = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupStep3.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$repeatFrequencySelectList = $('select[name="repeatFrequency"]');
					this.$repeatIntervalRow = $('#repeatIntervalRow');
					this.$repeatIntervalSelectList = $('select[name="repeatInterval"]');
					this.$repeatIntervalLabel = $('#repeatIntervalLabel');
					this.$repeatWeekdayListRow = $('#repeatWeekdayListRow');
					this.$repeatWeekdayCheckboxList = this.$repeatWeekdayListRow.find('input[type="checkbox"]');
					this.$summary = $('#summary'); 
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
					this.su =  options['su'];
					this.mo =  options['mo'];
					this.tu =  options['tu'];
					this.we =  options['we'];
					this.th =  options['th'];
					this.fr =  options['fr'];
					this.sa =  options['sa'];
					this.repeatFrequencyChangedDelegate = calendarista.createDelegate(this, this.repeatFrequencyChanged);
					this.$repeatFrequencySelectList.on('change', this.repeatFrequencyChangedDelegate);
					this.$repeatWeekdayCheckboxList.on('change', function(e){
						context.setSummary();
					});
					this.$repeatIntervalSelectList.on('change', function(e){
						context.setSummary();
					});
					this.repeatFrequencyChanged();
				};
				calendarista.setupStep3.prototype.repeatFrequencyChanged = function(){
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
				calendarista.setupStep3.prototype.getSelectedWeekdaySummary = function(){
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
				calendarista.setupStep3.prototype.setSummary = function(){
					var value = parseInt(this.$repeatFrequencySelectList.val(), 10)
						, summary = ''
						, interval = parseInt(this.$repeatIntervalSelectList.val(), 10);
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
					this.$summary.html(summary);
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupStep3({
			<?php echo $this->requestUrl ?>'
			, "daysLabelText": "<?php echo $this->decodeString(__('days', 'calendarista')) ?>"
			, "weeksLabelText": "<?php echo $this->decodeString(__('weeks', 'calendarista')) ?>"
			, "monthsLabelText": "<?php echo $this->decodeString(__('months', 'calendarista')) ?>"
			, "yearsLabelText": "<?php echo $this->decodeString(__('years', 'calendarista')) ?>"
			, "everyDaySummary": "<?php echo $this->decodeString(__('every %s day(s)', 'calendarista')) ?>"
			, "everyWeekdaySummary": "<?php echo $this->decodeString(__('every weekday', 'calendarista')) ?>"
			, "everyWeekMo_We_Fr": "<?php echo $this->decodeString(__('every week on Monday, Wednesday and Friday', 'calendarista')) ?>"
			, "everyWeekTu_Th": "<?php echo $this->decodeString(__('every week on Tuesday and Thursday', 'calendarista')) ?>"
			, "everyWeekOn": "<?php echo $this->decodeString(__('every %s week(s) on %s', 'calendarista')) ?>"
			, "everyMonth": "<?php echo $this->decodeString(__('every %s month(s) on the ', 'calendarista'))  . $this->monthlyRepeatDay ?>"
			, "everyYear": "<?php echo $this->decodeString(__('every %s year(s) on ', 'calendarista'))  . $this->yearlyRepeatDate ?>"
			, "everyDayOfTheWeek": "<?php echo $this->decodeString(__('every %s week(s)', 'calendarista'))?>"
			, "su": "<?php echo $this->decodeString(__('Sunday', 'calendarista'))?>"
			, "mo": "<?php echo $this->decodeString(__('Monday', 'calendarista')) ?>"
			, "tu": "<?php echo $this->decodeString(__('Tuesday', 'calendarista')) ?>"
			, "we": "<?php echo $this->decodeString(__('Wednesday', 'calendarista')) ?>"
			, "th": "<?php echo $this->decodeString( __('Thursday', 'calendarista')) ?>"
			, "fr": "<?php echo $this->decodeString(__('Friday', 'calendarista')) ?>"
			, "sa": "<?php echo $this->decodeString(__('Saturday', 'calendarista')) ?>"
		});
		</script>
		<?php
	}
}