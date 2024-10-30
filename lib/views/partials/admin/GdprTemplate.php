<?php
class Calendarista_GdprTemplate extends Calendarista_ViewBase{
	public $gdprList;
	public $generalSetting;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
				new Calendarista_GdprController(
				array($this, 'updateNotification')
				, array($this, 'denyRequestdNotification')
				, array($this, 'deleteNotification')
		);
		$this->gdprList = new Calendarista_GdprList();
		$this->gdprList->bind();
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		$this->generalSetting = $generalSettingsRepository->read();
		$this->render();
	}
	function updateNotification(){
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('The setting has been updated.', 'calendarista') ?></p>
		</div>
		<?php
	}
	function denyRequestdNotification(){
		?>
		<div class="settings error notice is-dismissible">
			<p><?php esc_html_e('The GDPR request has been cancelled.', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function deleteNotification($result) {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php echo sprintf(__('Good job. You have complied with the GDPR. In total %d past appointments belonging to the user were found and deleted along with all related data.', 'calendarista'), $result) ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="wrap">
			<p class="description"><?php esc_html_e('Customers that want their data deleted are listed below. Only past appointments are deleted. Please note that you have to comply within a month to each notice.', 'calendarista') ?></p>
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<input type="hidden" name="controller" value="calendarista_gdpr">
			<p>
			<input type="checkbox" name="enableGDPR" <?php echo $this->generalSetting->enableGDPR ? 'checked=checked' : '' ?>>
			<?php esc_html_e('Enable GDPR', 'calendarista') ?>
			</p>
			<p class="description">
				<?php esc_html_e('Enabling will automatically send GDPR instructions to existing customers, if they booked before GDPR was enabled.', 'calendarista') ?>
				<br>
				<?php esc_html_e('Make sure you add one or more terms and conditions using the custom form builder. Terms need to be in clear and plain language.', 'calendarista') ?>
			</p>
			<p>
			<button type="submit" name="calendarista_update" class="button button-primary"><?php esc_html_e('Update', 'calendarista') ?></button>
			</p>
			<?php $this->gdprList->display(); ?>
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
				calendarista.gdpr = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.gdpr.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.gdpr({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}