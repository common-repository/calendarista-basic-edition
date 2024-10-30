<?php
class Calendarista_AutogenSearchTimeslotsTemplate extends Calendarista_ViewBase{
	function __construct(){
		parent::__construct(false);
		$this->requestUrl = admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=10&calendarista-sub-tab=1';
		$this->render();
	}
	public function render(){
	?>
	<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
		<input type="hidden" name="controller" value="calendarista_autogen_timeslots" />
		<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
		<table class="form-table">
			<tbody>
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
			</tbody>
		</table>
	</form>
	<?php
	}
}