<?php
class Calendarista_RepeatTmpl extends Calendarista_TemplateBase{
	public $appointment;
	public $availability;
	public $repeatFrequency;
	public $weekdays;
	public $monthlyRepeatDay;
	public $yearlyRepeatDate;
	public function __construct(){
		parent::__construct();
		$projectId = (int)$this->getPostValue('projectId');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$this->appointment = (int)$this->getPostValue('appointment', -1);
		$startDate = $this->getPostValue('startDate');
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$this->availability = $availabilityRepo->read($availabilityId);
		$this->repeatFrequency = array();
		if($this->appointment === 1){
			$project = Calendarista_ProjectHelper::getProject($projectId);
			if(in_array($project->calendarMode, Calendarista_CalendarMode::$SINGLE_DAY_EVENT)){
				$this->availability->maxDailyRepeatFrequency = true;
				$this->availability->maxWeeklyRepeatFrequency = true;
				$this->availability->maxMonthlyRepeatFrequency = true;
				$this->availability->maxYearlyRepeatFrequency = true;
				$this->availability->maxRepeatFrequency = 31;
				$this->availability->maxRepeatOccurrence = 31;
			}
		}
		if($this->availability->maxDailyRepeatFrequency > 0){
			array_push($this->repeatFrequency, array('name'=>__('Day', 'calendarista'), 'key'=>1));
		}
		if($this->availability->maxWeeklyRepeatFrequency > 0){
			array_push($this->repeatFrequency, array('name'=>__('Week', 'calendarista'), 'key'=>5));
		}
		if($this->availability->maxMonthlyRepeatFrequency > 0){
			array_push($this->repeatFrequency, array('name'=>__('Month', 'calendarista'), 'key'=>6));
		}
		if($this->availability->maxYearlyRepeatFrequency > 0){
			array_push($this->repeatFrequency,  array('name'=>__('Year', 'calendarista'), 'key'=>7));
		}
		$this->weekdays = Calendarista_Weekday::toArray();
		if($startDate){
			$this->monthlyRepeatDay = date('jS', strtotime($startDate));
			$this->yearlyRepeatDate = date('jS F', strtotime($startDate));
		}
		if($this->availability && ($this->availability->maxDailyRepeatFrequency || 
			$this->availability->maxWeeklyRepeatFrequency || 
			$this->availability->maxMonthlyRepeatFrequency || 
			$this->availability->maxYearlyRepeatFrequency)){
			$this->render();
		}
	}
	public function repeatWeekdayChecked($value){
		$repeatWeekdayList = $this->getViewStateValue('repeatWeekdayList');
		if($repeatWeekdayList && count($repeatWeekdayList) > 0){
			return in_array($value, $repeatWeekdayList) ? 'checked' : '';
		}
		return '';
	}
	public function repeatFrequencySelected($value){
		$repeatFrequency = $this->getViewStateValue('repeatFrequency');
		return  $repeatFrequency === $value ? 'selected' : '';
	}
	public function repeatIntervalSelected($value){
		$repeatInterval = $this->getViewStateValue('repeatInterval');
		return  $repeatInterval === $value ? 'selected' : '';
	}
	public function repeatOccurrenceSelected($value){
		$terminateAfterOccurrence = $this->getViewStateValue('terminateAfterOccurrence');
		return  $terminateAfterOccurrence === $value ? 'selected' : '';
	}
	public function repeatChecked(){
		$repeatAppointment = $this->getViewStateValue('repeatAppointment');
		return  $repeatAppointment ? 'checked' : '';
	}
	public function render(){
	?>
	<div class="col-xl-12 calendarista-row-double">
		<div class="form-group">
			<div class="form-check-inline">
				<label class="form-check-label calendarista-typography--caption1">
					<input 
						id="calendarista_<?php echo $this->projectId ?>_repeat_appointment"
						name="repeatAppointment" 
						type="checkbox"  
						class="form-check-input"
						<?php echo $this->repeatChecked(); ?> />
						<?php echo esc_html($this->stringResources['REPEAT_THIS_APPOINTMENT_LABEL']) ?>
				</label>
			</div>
		</div>
	</div>
	<div class="col-xl-12" id="calendarista_<?php echo $this->projectId ?>_repeat_options">
		<input type="hidden" name="___viewstate" value="<?php echo $this->stateBag ?>">
		<div class="row align-items-start" id="calendarista_<?php echo $this->projectId ?>_repeatIntervalRow">
		<?php if($this->availability->maxRepeatFrequency > 1):?>
			<div class="col">
			  <label class="form-control-label calendarista-typography--caption1" for="calendarista_<?php echo $this->projectId ?>_repeatInterval">
					<?php esc_html_e('Repeat every', 'calendarista') ?>
				</label>
				<select id="calendarista_<?php echo $this->projectId ?>_repeatInterval" name="repeatInterval" class="form-select calendarista-typography--caption1">
					<?php for($i = 1; $i <= $this->availability->maxRepeatFrequency; $i++):?>
						<option value="<?php echo $i?>" <?php echo $this->repeatIntervalSelected($i)?>><?php echo $i?></option>
					<?php endfor;?>
				</select>
			</div>
			<?php endif; ?>
			<div class="col">
				<?php if($this->availability->maxRepeatFrequency > 1):?>
				<label class="form-control-label calendarista-typography--caption1">&nbsp;</label>
				<?php else: ?>
					<input type="hidden" id="calendarista_<?php echo $this->projectId ?>_repeatInterval" name="repeatInterval" value="1">
					<label class="form-control-label calendarista-typography--caption1 calendarista_one_occurrence_label">
						<?php esc_html_e('Every', 'calendarista')?>
					</label>
				<?php endif; ?>
			  <?php if(count($this->repeatFrequency) > 1): ?>
				<select id="calendarista_<?php echo $this->projectId ?>_repeatFrequency" name="repeatFrequency" class="form-select calendarista-typography--caption1">
					<?php foreach($this->repeatFrequency as $value):?>
					<option value="<?php echo $value['key']?>" <?php echo $this->repeatFrequencySelected($value['key'])?>>
						<?php echo $value['name']?>
					</option>
					<?php endforeach;?>
				</select>
				<?php else: ?>
				<?php if($this->availability->maxRepeatFrequency > 1):?>
				<div><label class="form-control-label calendarista-typography--caption1"><?php echo strtolower($this->repeatFrequency[0]['name']) ?></label></div>
				<?php else: ?>
				<label class="form-control-label calendarista-typography--caption1"><?php echo strtolower($this->repeatFrequency[0]['name']) ?></label>
				<?php endif; ?>
				<input type="hidden" id="calendarista_<?php echo $this->projectId ?>_repeatFrequency" name="repeatFrequency" value="<?php echo $this->repeatFrequency[0]['key']?>">
				<?php endif; ?>
			</div>
		</div>
		<div class="row align-items-start" id="calendarista_<?php echo $this->projectId ?>_repeatWeekdayListRow">
			<div class="col">
				<label class="form-control-label calendarista-typography--caption1">
					<?php esc_html_e('Repeat week days', 'calendarista') ?>
				</label>
				<div class="calendarista-row-single">
				<?php foreach($this->weekdays as $key=>$value):?>
				<div class="form-check form-check-inline">
						<input 
							id="calendarista_weekday_<?php echo sprintf('%s_%s', $this->projectId, $value) ?>"
							name="repeatWeekdayList[]" 
							value="<?php echo $key ?>"
							type="checkbox"  
							class="form-check-input"
							<?php if((int)$key === 7):?>
							data-parsley-required="true"
							data-parsley-group="block2"
							data-parsley-errors-container="#calendarista_repeat_weekday_error_container" 
							<?php endif;?>
							<?php echo $this->repeatWeekdayChecked((int)$key); ?> />
					<label for="calendarista_weekday_<?php echo sprintf('%s_%s', $this->projectId, $value) ?>" class="form-check-label calendarista-typography--caption1">
							<?php echo $value ?>
					</label>
				</div>
				<?php endforeach;?>
				</div>
				<div id="calendarista_repeat_weekday_error_container"></div>
			</div>
		</div>
		<div class="row align-items-start">
			<?php if($this->availability->maxRepeatOccurrence > 1): ?>
			<div class="col">
				<label class="form-control-label calendarista-typography--caption1" for="calendarista_<?php echo $this->projectId ?>_terminateAfterOccurrence">
					<?php esc_html_e('Terminate after', 'calendarista') ?>
				</label>
				<select id="calendarista_<?php echo $this->projectId ?>_terminateAfterOccurrence" name="terminateAfterOccurrence" class="form-select calendarista-typography--caption1">
					<?php for($i = 1; $i <= $this->availability->maxRepeatOccurrence; $i++):?>
						<option value="<?php echo $i?>" <?php echo $this->repeatOccurrenceSelected($i)?>><?php echo $i?></option>
					<?php endfor;?>
				</select>
			</div>
			<div class="col">
				<label class="form-control-label calendarista-typography--caption1">&nbsp;</label>
				<div><label class="form-control-label calendarista-typography--caption1 calendarista_occurrence_label"><?php esc_html_e('occurrence', 'calendarista')?></label></div>
			</div>
			<?php else: ?>
			<div class="col">
				<input id="calendarista_<?php echo $this->projectId ?>_terminateAfterOccurrence" type="hidden" name="terminateAfterOccurrence" value="1">
				<label class="form-control-label calendarista-typography--caption1 calendarista_one_occurrence_label">
					<?php esc_html_e('Terminates after 1 occurrence', 'calendarista')?>
				</label>
			</div>
			<?php endif; ?>
		</div>
		<?php /*<div class="row align-items-start">
			<div class="col">
				<p class="calendarista-typography--caption1" id="calendarista_<?php echo $this->projectId ?>_repeat_summary"></p>
			</div>
		</div>*/?>
	</div>
	<script type="text/javascript">
	(function(){
		function init(){
			var repeatAppointment = new Calendarista.repeatAppointment({
					"ajaxUrl": "<?php echo admin_url('admin-ajax.php')?>"
					, "everyDaySummary": "<?php esc_html_e('every %s day(s)', 'calendarista')?>"
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
					, "maxDailyRepeatFrequency": "<?php echo $this->availability->maxDailyRepeatFrequency ?>"
					, "maxWeeklyRepeatFrequency": "<?php echo $this->availability->maxWeeklyRepeatFrequency ?>"
					, "maxMonthlyRepeatFrequency": "<?php echo $this->availability->maxMonthlyRepeatFrequency ?>"
					, "maxYearlyRepeatFrequency": "<?php echo $this->availability->maxYearlyRepeatFrequency ?>"
					, "id": <?php echo $this->projectId ?>
					, "availabilityId": <?php echo $this->availability->id ?>
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
<?php
	}
}
