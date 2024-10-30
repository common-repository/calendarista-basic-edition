<?php
class Calendarista_ShortCodeGeneratorTemplate extends Calendarista_ViewBase{
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		$this->render();
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<div class="column-pane">
						<div class="calendarista-borderless-accordion">
							<div id="shortcode_gen">
								<h3><?php esc_html_e('Services', 'calendarista') ?></h3>
								<p class="description"><?php esc_html_e('Select one or more services below', 'calendarista') ?></p>
								<?php if($this->projects->count() > 0): ?>
								<div style="overflow: auto; height: 400px; width: 100%">
									<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post"> 
										<table class="wp-list-table calendarista wp-list-table widefat fixed striped">
											<thead></thead>
											<tbody>
											<?php foreach($this->projects as $project):?>
											<tr>
												<td>
													<input type="checkbox" name="projects[]" value="<?php echo $project->id ?>" data-calendarista-name="<?php echo esc_attr($project->name) ?>"><?php echo esc_html($project->name) ?><br>
												</td>
											</tr>
											<?php endforeach;?>
											</tbody>
										</table>
									</form>
								</div>
								<?php else: ?>
								<p class="description"><?php esc_html_e('Please create at least one service first', 'calendarista') ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right">
			<div id="widgets-right">
				<div class="wrap">
					<div class="column-pane">
						<div class="calendarista-borderless-accordion">
							<div id="shortcode">
								<div id="multi_booking_section" class="hide">
									<p>
										<input type="checkbox" name="enableMultipleBookings"><?php esc_html_e('Enable booking multiple availabilities', 'calendarista') ?>
									</p>
									<p>
										<input type="checkbox" name="enableServiceThumbnailView"><?php esc_html_e('Enable service thumbnail view', 'calendarista') ?>
									</p>
									<p>
										<input type="checkbox" name="enableAvailabilityThumbnailView"><?php esc_html_e('Enable availability thumbnail view', 'calendarista') ?>
									</p>
								</div>
								<h3><?php esc_html_e('Result', 'calendarista') ?></h3>
								<div>
									<p class="description"><?php esc_html_e('Select one or more services in the left pane. The resulting short-code to insert in a page or post will be printed below.', 'calendarista') ?></p>
									<textarea id="shortcode_result1" style="width: 100%" rows="5" readonly></textarea>
								</div>
								<h3><?php esc_html_e('User profile', 'calendarista') ?></h3>
								<div>
									<p>
										<input type="checkbox" name="enableEditProfile"><?php esc_html_e('Enable edits, until', 'calendarista') ?>
										<input type="text" name="editPolicy" class="small-text" value="0">
										<?php esc_html_e('minutes before the appointment', 'calendarista') ?>
									</p>
									<p class="description"><?php esc_html_e('Note: Do not set a value in minutes to allow last minute edits.', 'calendarista') ?></p>
								</div>
								<div>
									<p class="description"><?php esc_html_e('Insert the following short-code in any page or post. If the user is logged in, they will see upcoming appointments and appointment history.', 'calendarista') ?></p>
									<textarea id="shortcode_result2" style="width: 100%" rows="1" readonly>[calendarista-user-profile]</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.shortcodeGen = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.shortcodeGen.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$projectList = $('input[name="projects[]"]');
					this.$multiBookingSection = $('#multi_booking_section');
					this.$enableMultipleBookings = $('input[name="enableMultipleBookings"]');
					this.$enableServiceThumbnailView = $('input[name="enableServiceThumbnailView"]');
					this.$enableAvailabilityThumbnailView = $('input[name="enableAvailabilityThumbnailView"]');
					this.$enableEditProfile = $('input[name="enableEditProfile"]');
					this.$editPolicy = $('input[name="editPolicy"]');
					this.$output = $('#shortcode_result1');
					this.$output2 = $('#shortcode_result2');
					this.outputShortCodeDelegate = calendarista.createDelegate(this, this.outputShortCode);
					this.outputShortCode2Delegate = calendarista.createDelegate(this, this.outputShortCode2);
					this.$enableMultipleBookings.on('change', function(e){
						context.outputShortCode();
					});
					this.$enableServiceThumbnailView.on('change', function(e){
						context.outputShortCode();
					});
					this.$enableAvailabilityThumbnailView.on('change', function(e){
						context.outputShortCode();
					});
					this.$enableEditProfile.on('change', this.outputShortCode2Delegate);
					this.$editPolicy.on('change', this.outputShortCode2Delegate);
					this.$projectList.on('change', this.outputShortCodeDelegate);
				};
				calendarista.shortcodeGen.prototype.outputShortCode2 = function(){
					var result = '[calendarista-user-profile]'
						, policy = this.$editPolicy.val() ? parseInt(this.$editPolicy.val(), 10) : 0;
					if(this.$enableEditProfile[0].checked){
						result = '[calendarista-user-profile enable-edit="true"]';
						if(!isNaN(policy) && policy){
							result = '[calendarista-user-profile enable-edit="true" edit-policy="' + policy + '"]';
						}
					}
					this.$output2.val(result);
				};
				calendarista.shortcodeGen.prototype.outputShortCode = function(){
					var $checkedItems = $('input[name="projects[]"]:checked')
						, checkedValues = $checkedItems.map(function () {
						return this.value;
					}).get()
					, output;
					//reset
					this.$output.val('');
					if(checkedValues.length > 0){
						this.$multiBookingSection.removeClass('hide');
						this.$enableServiceThumbnailView.removeClass('hide');
						this.$enableAvailabilityThumbnailView.removeClass('hide');
						output = '[calendarista-booking id="' + checkedValues.join(',') + '"';
						if(this.$enableMultipleBookings.is(':checked')){
							output += ' enable-multiple-booking="true"';
						}
						if(this.$enableServiceThumbnailView.is(':checked')){
							output += ' service-thumbnail-view="true"';
						}
						if(this.$enableAvailabilityThumbnailView.is(':checked')){
							output += ' availability-thumbnail-view="true"';
						}
						output += ']';
						this.$output.val(output);
					}else{
						this.$multiBookingSection.addClass('hide');
					}
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.shortcodeGen({
			<?php echo $this->requestUrl ?>'
		});
		</script>
		<?php
	}
}