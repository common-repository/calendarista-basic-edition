<?php
class Calendarista_ErrorLogTemplate extends Calendarista_ViewBase{
	public $errorLogList;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		new Calendarista_ErrorLogController(
				array($this, 'errorLogDelete')
				, array($this, 'errorLogDeleteAll')
		);
		$this->errorLogList = new Calendarista_ErrorLogList();
		$this->errorLogList->bind();
		$this->render();
	}
	function errorLogDelete($result){
		if($result){
			$this->errorLogDeleteNotification();
		}
	}
	function errorLogDeleteAll($result){
		if($result){
			$this->errorLogDeleteAllNotification();
		}
	}
	public function errorLogDeleteNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('The error from the log has been deleted', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function errorLogDeleteAllNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('The error log has been cleared', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="wrap">
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_errorlog" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<p><button class="button button-primary" name="calendarista_deleteall"><?php esc_html_e('Clear Log', 'calendarista') ?></button></p>
			</form>
			<div class="table-responsive">
				<?php $this->errorLogList->display() ?>
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
				calendarista.errorLog = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.errorLog.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.errorLog({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}