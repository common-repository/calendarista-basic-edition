<?php
class Calendarista_RepeatItemsTmpl extends Calendarista_TemplateBase{
	public $repeatDates;
	public $repeatPageSize;
	public function __construct($repeatDates, $repeatPageSize = 10){
		parent::__construct();
		$this->repeatDates = $repeatDates;
		$this->repeatPageSize = $repeatPageSize;
		//add a marker in bookedavailability table to indicate the date is a repeated one.
		if(count($this->repeatDates) > 0){
			$this->render();
		}
	}
	public function render(){
	?>
	<div class="col-xl-12">
		<div class="form-group">
			<div class="calendarista-repeat-date-list">
				<div class="calendarista-typography--caption1">
					<?php echo esc_html($this->stringResources['REPEAT_REGISTER_NAME_LABEL']); ?>
				</div>
				<p>
				<?php foreach($this->repeatDates as $key=>$val): ?>
					<span class="badge text-bg-secondary calendarista-repeat-date-badge <?php echo ($this->repeatPageSize && $key > $this->repeatPageSize) ? 'hide' : '' ?>" id="calendarista_<?php echo $key ?>_repeat">
						<?php echo $val['formattedDate']; ?><?php if(count($this->repeatDates) > 1): ?>&nbsp;&nbsp;<i class="fa fa-close calendarista-repeat-date-btn" data-calendarista-value="calendarista_<?php echo $key ?>_repeat"></i><?php endif; ?>
						<span class="sr-only"><?php echo $val['formattedDate']; ?></span>
						<input
							name="repeatAppointmentDates[]" 
							type="hidden" value="<?php echo $val['raw'] ?>" />
					</span>
				<?php endforeach; ?>
				<?php if($this->repeatPageSize && count($this->repeatDates) > $this->repeatPageSize): ?>
				<span class="badge text-bg-secondary calendarista-repeat-show-more-badge" id="calendarista_<?php echo $this->projectId ?>_show_more_repeat_button" title="<?php esc_html_e('Show more dates', 'calendarista') ?>">
					...&nbsp;&nbsp;<i class="fa fa-chevron-circle-down calendarista-repeat-show-more-btn" data-calendarista-value="calendarista_<?php echo $this->projectId ?>_show_more_repeat_button"></i>
					<span class="sr-only">...</span>
				</span>
				<?php endif; ?>
				</p>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	(function(){
		function init(){
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
