<?php
class Calendarista_SetupTemplate extends Calendarista_ViewBase{
	public $stepClassName;
	public $step;
	public $fields;
	public $supportsTimeslots;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
		$this->stepClassName  = array_fill(1, 5, '');
		$this->stepClassName[$this->step] = 'calendarista-setup-active-step';
		$this->fields = $this->getFields();
		$this->supportsTimeslots = in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS);
		$this->render();
	}
	function getFields(){
		$result = array('calendarMode'=>0);
		if(isset($_POST['calendarMode'])){
			$result['calendarMode'] = (int)$_POST['calendarMode'];
		}
		return $result;
	}
	public function render(){
	?>
		<h2><?php echo __( 'Calendarista Setup', 'calendarista' ); ?></h2>
		<ul class="calendarista-setup-steps">
			<li class="<?php echo $this->stepClassName[1]; ?>"><?php esc_html_e('The service', 'calendarista'); ?></li>
			<li class="<?php echo $this->stepClassName[2]; ?>"><?php esc_html_e('When does it begin', 'calendarista'); ?></li>
			<li class="<?php echo $this->stepClassName[3]; ?>"><?php esc_html_e('For how long', 'calendarista'); ?></li>
			<?php if($this->supportsTimeslots): ?>
			<li class="<?php echo $this->stepClassName[4]; ?>"><?php esc_html_e('At what time', 'calendarista'); ?></li>
			<?php endif; ?>
			<li class="<?php echo $this->stepClassName[5]; ?>"><?php esc_html_e('Done', 'calendarista'); ?></li>
		</ul>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<?php switch($this->step){
					case 1:
						new Calendarista_SetupStep1Template();
					break;
					case 2:
						new Calendarista_SetupStep2Template();
					break;
					case 3:
						new Calendarista_SetupStep3Template();
					break;
					case 4:
						new Calendarista_SetupStep4Template();
					break;
					case 5:
						new Calendarista_SetupStep5Template();
					break;
				}
				?>
			</div>
		</div>
		<div class="clear"></div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.setupOne = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupOne.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupOne({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}