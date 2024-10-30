<?php
class Calendarista_SetupWizard extends Calendarista_ViewBase{
	public $step;
	public $projectId;
	public $welcome;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
		$this->projectId = isset($_POST['projectId']) ? (int)$_POST['projectId'] : null;
		$this->welcome = isset($_GET['welcome']) ? true : false;
		$this->render();
	}

	public function render(){
	?>
		<div class="wrap">
			<form id="wizardForm" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-excluded="[disabled=disabled]">
				<div id="placeholder">
					<?php new Calendarista_SetupTemplate(); ?>
				</div>
			</form>
			<p class="submit">
				<a id="modify_service" href="<?php echo esc_url(add_query_arg(array('page'=>'calendarista-index'))); ?>" class="button hide"><?php esc_html_e('Modify the settings (Advanced)', 'calendarista'); ?></a>
				<a id="skip_setup" href="<?php echo esc_url(admin_url('admin.php?page=calendarista-index')); ?>" class="button"><?php esc_html_e('Skip setup. I will set up the plugin manually', 'calendarista'); ?></a>
				<input type="button" value="<?php esc_html_e('Prev step', 'calendarista'); ?>" id="prev" class="button button-primary hide" />
				<?php if($this->step !== 5): ?>
				<input type="button" value="<?php esc_html_e('Next step', 'calendarista'); ?>" id="next" class="button button-primary" />
				<?php endif; ?>
			</p>
			<div id="spinner_setup" class="calendarista-spinner calendarista-invisible">
				<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading step...', 'calendarista') ?>
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
				calendarista.setupWizard = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupWizard.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$root = $('#setupWizard');
					this.$form = $('#wizardForm');
					this.$next = $('#next');
					this.$prev = $('#prev');
					this.$modifyService = $('#modify_service');
					this.$skipSetup = $('#skip_setup');
					this.$placeholder = $('#placeholder');
					this.requestDelegate = calendarista.createDelegate(this, this.request);
					this.$next.on('click', this.requestDelegate);
					this.$prev.on('click', this.requestDelegate);
					this.actionSetupWizard = 'calendarista_setup_wizard';
					this.model = [];
					this.ajax1 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'setup'});
				};
				calendarista.setupWizard.prototype.request = function(e){
					var model = this.$form.serializeArray()
						, $currentTarget = $(e.currentTarget)
						, params
						, $step = this.$form.find('#step')
						, step = $step.length > 0 ? parseInt($step.attr('data-calendarista-next-step-id'), 10) : null;
					if($currentTarget[0].id === 'prev'){
						step = parseInt($step.attr('data-calendarista-prev-step-id'), 10);
					}else{
						if(!Calendarista.wizard.isValid(this.$form)){
							return false;
						}
					}
					this.filter(model);
					this.model = model.concat(this.model);
					params = [].concat(this.model);
					params.push({ 'name': 'step', 'value': step });
					params.push({ 'name': 'action', 'value': this.actionSetupWizard });
					params.push({ 'name': 'calendarista_nonce', 'value': this.nonce });
					this.ajax1.request(this, this.response, $.param(params));
				};
				calendarista.setupWizard.prototype.response = function(result){
					this.$placeholder.replaceWith('<div id="placeholder">' + result + '</div>');
					this.$placeholder = $('#placeholder');
					$projectId = this.$placeholder.find('#projectId');
					$step = this.$placeholder.find('#step');
					step = parseInt($step.attr('data-calendarista-next-step-id'), 10) - 1;
					if(step > 1){
						this.$prev.removeClass('hide');
					}else if([1].indexOf(step) !== -1){
						this.$prev.addClass('hide');
					}else if(isNaN(step)){
						//last step
						this.$prev.addClass('hide');
						this.$next.addClass('hide');
						this.$modifyService.attr('href', this.$modifyService.attr('href') + '&projectId=' + $projectId.val());
						this.$modifyService.removeClass('hide');
						this.$skipSetup.addClass('hide');
					}
				};
				calendarista.setupWizard.prototype.filter = function(formFields){
					var i
						, j;
					if(this.model.length === 0){
						return;
					}
					for(i = 0; i < formFields.length; i++){
						for(j = 0; j < this.model.length; j++){
							if(formFields[i]['name'] == this.model[j]['name']){
								this.model.splice(j, 1);
							}
						}
					}
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupWizard({
			<?php echo $this->requestUrl ?>'
			, 'ajaxUrl': '<?php echo admin_url('admin-ajax.php')?>'
		});
		</script>
		<?php
	}
}