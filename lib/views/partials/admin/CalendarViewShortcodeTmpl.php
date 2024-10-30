<?php
class Calendarista_CalendarViewShortcodeTmpl extends Calendarista_ViewBase{
	public $url;
	function __construct(){
		parent::__construct(false, true);
		$this->render();
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<div class="column-pane">
						<div id="shortcode_gen">
							<h3><?php esc_html_e('Services', 'calendarista') ?></h3>
							<p class="description"><?php esc_html_e('Select one or more services below', 'calendarista') ?></p>
							<?php if($this->projects->count() > 0): ?>
							<div style="overflow: auto; height: 200px; width: 100%">
								<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post"> 
									<table class="wp-list-table calendarista wp-list-table widefat fixed striped">
										<thead></thead>
										<tbody>
										<?php foreach($this->projects as $project):?>
										<tr>
											<td>
												<div><input type="checkbox" name="projects" value="<?php echo $project->id ?>">&nbsp;<?php echo esc_html($project->name) ?><div>
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
					<div class="column-pane">
						<div id="formElements">
							<div>
								<span id="spinner_custom_fields" class="calendarista-spinner calendarista-invisible">
									<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
								</span>
							</div>
							<p class="description"><?php esc_html_e('Select the custom form field(s), whose value will be used in the appointment title', 'calendarista') ?></p>
							<div style="overflow: auto; height: 200px; width: 100%">
								<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post"> 
									<table class="wp-list-table calendarista wp-list-table widefat fixed striped">
										<thead></thead>
										<tbody id="customFieldsContainer">
											<tr>
												<td>--</td>
											</tr>
										</tbody>
									</table>
								</form>
							</div>
							<p class="description"><?php esc_html_e('Note, if no fields are selected, customer name will be used in the appointment title.', 'calendarista') ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right">
			<div id="widgets-right">
				<div class="wrap">
					<div class="column-pane">
						<div id="shortcode">
							<p>
									<select name="status">
									<option value="0"><?php esc_html_e('Status pending', 'calendarista') ?></option>
									<option value="1" selected><?php esc_html_e('Status approved', 'calendarista') ?></option>
									<option value="2"><?php esc_html_e('Status cancelled', 'calendarista') ?></option>
									<option value="3"><?php esc_html_e('Status any', 'calendarista') ?></option>
								</select>
							</p>
							<p>
								<input type="radio" name="view" value="0" checked><?php esc_html_e('Monthly view', 'calendarista') ?>
								&nbsp;&nbsp;<input type="radio" name="view" value="1"><?php esc_html_e('Weekly view', 'calendarista') ?>
								&nbsp;&nbsp;<input type="radio" name="view" value="2"><?php esc_html_e('Day view', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="includeNameField"><?php esc_html_e('Include name in title', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="includeEmailField"><?php esc_html_e('Include email in title', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="includeAvailabilityNameField"><?php esc_html_e('Include availability in title', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="includeSeat"><?php esc_html_e('Include seat in title', 'calendarista') ?>
							</p>
							<h3><?php esc_html_e('Result', 'calendarista') ?></h3>
							<div>
								<p class="description"><?php esc_html_e('Select a service in the left pane. The resulting short-code to insert in a page or post will be printed below.', 'calendarista') ?></p>
								<textarea id="shortcode_result" style="width: 100%" rows="5" readonly></textarea>
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
				calendarista.shortcode = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.shortcode.prototype.init = function(options){
					var context = this;
					this.requestUrl = options['requestUrl'];
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.$projectList = $('input[name="projects"]');
					this.$output = $('#shortcode_result');
					this.$formFieldsContainer = $('#customFieldsContainer');
					this.$status = $('select[name="status"]');
					this.$view = $('input[name="view"]');
					this.$includeNameField = $('input[name="includeNameField"]');
					this.$includeEmailField = $('input[name="includeEmailField"]');
					this.$includeAvailabilityNameField = $('input[name="includeAvailabilityNameField"]');
					this.$includeSeat =  $('input[name="includeSeat"]');
					this.actionCustomFormFields = 'calendarista_shortcode_custom_form_fields';
					this.customFormFieldsAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'spinner_custom_fields'});
					this.formElementsCheckedValuesHistory = null;
					this.$projectList.on('change', function(){
						var model
							, checkedValues = $('input[name="projects"]:checked').map(function () {
								return this.value;
							}).get();
						context.formElementsCheckedValuesHistory = context.$formFieldsContainer.find('input:checked').map(function () {
							return this.value;
						}).get();
						model = [{ 'name': 'projectList', 'value': checkedValues.join(',') }
								, { 'name': 'action', 'value': context.actionCustomFormFields }
								, { 'name': 'calendarista_nonce', 'value': context.nonce }];
						context.customFormFieldsAjax.request(context, context.customFormFieldsResponse, $.param(model));
					});
					this.$status.on('change', function(){
						context.shortcodeOutput();
					});
					this.$view.on('change', function(){
						context.shortcodeOutput();
					});
					this.$includeNameField.on('change', function(){
						context.shortcodeOutput();
					});
					this.$includeEmailField.on('change', function(){
						context.shortcodeOutput();
					});
					this.$includeAvailabilityNameField.on('change', function(){
						context.shortcodeOutput();
					});
					this.$includeSeat.on('change', function(){
						context.shortcodeOutput();
					});
				};
				calendarista.shortcode.prototype.shortcodeOutput = function(){
					var projectListCheckedValues = $('input[name="projects"]:checked').map(function () {
							return this.value;
						}).get()
						, view = parseInt($('input[name="view"]:checked').val(), 10)
						, formElementsCheckedValues = this.$formFieldsContainer.find('input:checked').map(function () {
							return this.value;
						}).get()
						, output;
						//reset
						this.$output.val('');
						if(projectListCheckedValues.length === 0){
							return [];
						}
						if(projectListCheckedValues.length > 0){
							output = '[calendarista-public-calendar id="' + projectListCheckedValues.join(',') + '"';
						}
						
						output += ' view="';
						if(view === 0){
							output += 'month"';
						}else if(view === 1){
							output += 'week"';
						}else if(view === 2){
							output += 'day"';
						}
						if(formElementsCheckedValues.length > 0){
							output += ' form-elements="' + formElementsCheckedValues.join(',') + '"';
						}
						if(this.$status.length > 0){
							output += ' status="' + this.$status.val() + '"';
						}
						if(this.$includeNameField.is(':checked')){
							output += ' name="true"';
						}
						if(this.$includeEmailField.is(':checked')){
							output += ' email="true"';
						}
						if(this.$includeAvailabilityNameField.is(':checked')){
							output += ' availability-name="true"';
						}
						if(this.$includeSeat.is(':checked')){
							output += ' seats="true"';
						}
					if(output){
						output += ']';
					}
					this.$output.val(output);
					return projectListCheckedValues;
				};
				calendarista.shortcode.prototype.customFormFieldsResponse = function(result){
					var context = this
						, i;
					this.$formFieldsContainer.find('input').off();
					this.$formFieldsContainer.replaceWith('<tbody id="customFieldsContainer">' + result + '</tbody>');
					this.$formFieldsContainer = $('#customFieldsContainer');
					this.$formFieldsContainer.find('input').on('change', function(){
						context.shortcodeOutput();
					});
					if(this.formElementsCheckedValuesHistory){
						for(i = 0; i < this.formElementsCheckedValuesHistory.length; i++){
							this.$formFieldsContainer.find('input[value="' + this.formElementsCheckedValuesHistory[i] + '"]').prop('checked', true);
						}
					}
					this.shortcodeOutput();
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.shortcode({
			'requestUrl': '<?php echo $_SERVER["REQUEST_URI"] ?>'
		});
		</script>
		<?php
	}
}