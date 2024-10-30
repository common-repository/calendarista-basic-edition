<?php
class Calendarista_BookingCalendarLegendTmpl extends Calendarista_TemplateBase{
	public function __construct(){
		parent::__construct();
		$this->render();
	}
	public function render(){
	?>
	<div class="calendarista calendarista-calendar-legend<?php echo $this->project->id ?> hide">
		<?php if($this->project->calendarMode === Calendarista_CalendarMode::CHANGEOVER): ?>
		<div class="pull-left">
			<div class="calendarista-halfday-legend pull-left">
				<div class="calendarista-halfday"></div>
			</div>
			<div class="calendarista-legend-label pull-left"><?php echo esc_html($this->stringResources['CALENDAR_LEGEND_HALF_DAY'])?></div>
		</div>
		<?php endif; ?>
		<div class="clearfix"></div>
	</div>
<?php
	}
}