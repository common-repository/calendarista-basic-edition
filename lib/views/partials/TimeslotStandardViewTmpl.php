<?php
class Calendarista_TimeslotStandardViewTmpl extends Calendarista_TemplateBase{
	public $container;
	public $now;
	public function __construct($container){
		parent::__construct();
		$this->container = $container;
		$now = strtotime('now');
		$this->render();
	}
	public function getHour($timeslot){
		return date('G', strtotime(date(CALENDARISTA_DATEFORMAT, $this->now) . ' ' . $timeslot->timeslot));
	}
	public function getMinute($timeslot){
		return (int)date('i', strtotime(date(CALENDARISTA_DATEFORMAT, $this->now) . ' ' . $timeslot->timeslot));
	}
	public function get24HFormat($timeslot){
		$minute = date('i', strtotime(date(CALENDARISTA_DATEFORMAT, $this->now) . ' ' . $timeslot->timeslot));
		return sprintf('%s:%s', $this->getHour($timeslot), $minute);
	}
	public function render(){
	?>
	<div class="<?php echo $this->container->placeholderClassName ?>">
		<?php if(!$this->container->slotType):?>
		<input type="hidden" name="availabilityId" value="<?php echo $this->container->availabilityId ?>"/>
		<?php endif; ?>
		<div class="form-group calendarista-row-single">
			<div class="<?php echo !$this->container->multipleSlotSelection || $this->container->enableResetButton ? 'input-group' : '' ?>">
				<?php if($this->container->multipleSlotSelection): ?>
					<label class="form-control-label calendarista-typography--caption1" for="<?php echo $this->container->fieldName . $this->uniqueId ?>">
						<?php echo $this->container->timeLabel ?>
					</label>
				<?php endif; ?>
				<select id="<?php echo $this->container->fieldName . $this->uniqueId ?>" name="<?php echo $this->container->fieldName ?>" 
					class="calendarista-time form-select calendarista-typography--caption1 calendarista_parsley_validated" 
						data-parsley-required="true"
					data-parsley-errors-container="#<?php echo $this->container->errorContainerId ?>_error_container"
					<?php if($this->container->multipleSlotSelection): ?>
					multiple="multiple"
					data-calendarista-max-timeslots="<?php echo $this->container->availability->maxTimeslots ?>"
					<?php endif;?>>
					<?php if(!$this->container->multipleSlotSelection): ?>
						<?php if($this->container->slotType && $this->container->availability->returnSameDay): ?>
							<option value=""><?php echo esc_html($this->stringResources['BOOKING_RETURN_DATE_LABEL']) ?></option>
						<?php else: ?>
							<option value=""><?php echo $this->container->timeLabel ?></option>
						<?php endif; ?>
					<?php endif; ?>
					<?php foreach($this->container->timeslots as $key=>$timeslot):?>
					<option value="<?php echo $timeslot->id?>" data-calendarista-time="<?php echo $this->container->formatTime($timeslot) ?>"
							<?php echo $this->container->selectedValue($timeslot);?>
							<?php echo $this->container->returnTripSlot($timeslot);?>
							<?php echo $this->getHour($timeslot) ?>
							data-calendarista-hour="<?php echo $this->getHour($timeslot)?>" 
							data-calendarista-minute="<?php echo $this->getMinute($timeslot)?>"
							data-calendarista-24h="<?php echo $this->get24HFormat($timeslot)?>"><?php echo $this->container->formatTime($timeslot) ?></option>
					<?php endforeach; ?>
				</select>
				<?php if($this->container->enableResetButton): ?>
					<button type="button"  class="btn btn-outline-secondary <?php echo $this->container->resetButton ?> calendarista-not-active" title="<?php echo $this->container->stringResources['BOOKING_RESET_TIME']; ?>" disabled><i class="fa fa-undo"></i></button>
				<?php endif; ?>
				<?php if(!$this->container->multipleSlotSelection): ?>
					<label class="input-group-text">
						<i class="fa fa-clock"></i>
					</label>
				<?php endif;?>
			</div>
			<div id="<?php echo $this->container->errorContainerId ?>_error_container" class="calendarista-typography--caption1"></div>
			<?php if($this->container->multipleSlotSelection):?>
			<p class="calendarista-max-timeslot-message calendarista-typography--caption1 form-text text-muted">
				<span class="hidden-xs hidden-sm"><?php echo $this->container->stringResources['BOOKING_MULTI_SELECT_TIMESLOT_NOTICE']; ?></span>
				<span><?php echo sprintf($this->container->stringResources['BOOKING_MAX_TIMESLOT_NOTICE'], $this->container->availability->maxTimeslots); ?></span>
			</p>
			<?php endif; ?>
		</div>
		<?php if($this->container->availability->returnOptional && ($this->container->availability->returnSameDay && $this->container->slotType === 1/*enddate*/)):?>
		<div class="form-group calendarista-return-optional">
			<div class="calendarista-typography--caption1 calendarista-row-single">
				<?php echo esc_html($this->stringResources['BOOKING_RETURN_IS_OPTIONAL']) ?>
			</div>
		</div>
		<?php endif; ?>
		<div class="clearfix"></div>
	</div>
<?php
	}
}
