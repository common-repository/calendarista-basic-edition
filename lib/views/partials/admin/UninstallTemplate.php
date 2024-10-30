<?php
class Calendarista_UninstallTemplate extends Calendarista_ViewBase{
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
				new Calendarista_UninstallController(
				array($this, 'uninstallPermissionNotification')
				, array($this, 'uninstallNotification')
				, array($this, 'uninstallNotification')
		);
		if(isset($_POST['uninstall']) && $_POST['uninstall'] === 'clear'){
			$this->uninstallClearConfirmation();
		}else if(isset($_POST['uninstall']) && $_POST['uninstall'] === 'delete'){
			$this->uninstallDeleteConfirmation();
		}
		$this->render();
	}
	function uninstallPermissionNotification($result){
		?>
		<div class="settings error notice is-dismissible">
			<p><?php esc_html_e('You do not have permission to uninstall the plugin', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function uninstallNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('All data has been cleared', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function uninstallClearConfirmation(){
		?>
		<div class="index error notice is-dismissible">
			<p>
				<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
					<input type="hidden" name="controller" value="calendarista_uninstall"/>
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
					<span class="alignleft"><?php esc_html_e('About to clear all data along with settings saved by the plugin. Proceed?', 'calendarista'); ?> </span>
						<input type="submit" name="calendarista_clear" class="button button-primary alignleft pad-left-right" value="<?php esc_html_e('Apply', 'calendarista')?>" />
						<input type="submit" name="cancel" class="button button-primary alignleft pad-left-right" value="<?php esc_html_e('Cancel', 'calendarista')?>" />
						<br class="clear">
				</form>
			</p>
		</div>
		<?php
	}
	public function uninstallDeleteConfirmation(){
		?>
		<div class="index error notice is-dismissible">
			<p>
				<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
					<input type="hidden" name="controller" value="calendarista_uninstall"/>
					<span class="alignleft"><?php esc_html_e('About to delete everything. It will be as if the plugin was never installed. Proceed?', 'calendarista'); ?> </span>
						<input type="submit" name="calendarista_delete" class="button button-primary alignleft pad-left-right" value="<?php esc_html_e('Apply', 'calendarista')?>" />
						<input type="submit" name="cancel" class="button button-primary alignleft pad-left-right" value="<?php esc_html_e('Cancel', 'calendarista')?>" />
						<br class="clear">
				</form>
			</p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="wrap">
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<p>
					<input type="radio" name="uninstall" value="clear" checked/>
					<?php esc_html_e('Clear all data but leave the calendarista tables intact. You still lose almost everything, careful!', 'calendarista') ?>
				</p>
				<p>
					<input type="radio" name="uninstall" value="delete" />
					<?php esc_html_e('Just delete everything, tables included. You lose everything. Plugin will be deactivated!', 'calendarista') ?>
				</p>
				<p class="submit">
					<button class="button button-primary" name="submit">
						<?php esc_html_e('Submit', 'calendarista') ?>
					</button>
				</p>
			</form>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.uninstall = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.uninstall.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.uninstall({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}