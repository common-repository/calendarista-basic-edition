<?php
class Calendarista_ManageSettings extends Calendarista_ViewBase{
	public $id;
	public $tabs;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		$this->id = isset($_GET['id']) ? (int)$_GET['id'] : null;
		//solve chicken and egg problem by declaring assets controller here instead of in assets template
		new Calendarista_AssetsController(
			array($this, 'createdSetting')
			, array($this, 'updatedSetting')
		);
		$this->tabs = $this->getTabs();
		$this->render();
	}
	public function getTabs(){
		$url = admin_url() . 'admin.php?page=calendarista-settings';
		$settings = $this->getSettings();
		$result = array();
		$result[0] = array('url'=>$url, 'label'=>__('General', 'calendarista'), 'active'=>false);
		$result[1] = array('url'=>$url . '&calendarista-tab=1', 'label'=>__('Email', 'calendarista'), 'active'=>false);
		$result[2] = array('url'=>$url . '&calendarista-tab=2', 'label'=>__('Payment', 'calendarista'), 'active'=>false);
		$result[3] = array('url'=>$url . '&calendarista-tab=3', 'label'=>__('Coupons', 'calendarista'), 'active'=>false);
		$result[4] = array('url'=>$url . '&calendarista-tab=4', 'label'=>__('Reminders', 'calendarista'), 'active'=>false);
		$result[5] = array('url'=>$url . '&calendarista-tab=5', 'label'=>__('Error log', 'calendarista') . $this->getErrorLogCount(), 'active'=>false);
		$result[6] = array('url'=>$url . '&calendarista-tab=6', 'label'=>__('Assets', 'calendarista'), 'active'=>false);
		$result[11] = array('url'=>$url . '&calendarista-tab=11', 'label'=>__('GDPR', 'calendarista') . $this->getGdprCount(), 'active'=>false);
		if($settings->debugMode){
			$result[12] = array('url'=>$url . '&calendarista-tab=12', 'label'=>__('Uninstall', 'calendarista'), 'active'=>false);
		}
		if($this->selectedTab !== null){
			$result[$this->selectedTab]['active'] = true;
		}else{
			$result[0]['active'] = true;
		}
		return $result;
	}
	public function getSettings(){
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		return $generalSettingsRepository->read();
	}
	
	public function getErrorLogCount(){
		$repo = new Calendarista_ErrorLogRepository();
		$result = $repo->count();
		return sprintf('<span class="count-badge count-%1$d">
			<span class="count-badge-value">
				&nbsp;%1$d
			</span>
		</span>', $result);
	}
	public function getGdprCount(){
		$repo = new Calendarista_GdprRepository();
		$result = $repo->requestCount();
		return sprintf('<span class="count-badge count-%1$d">
			<span class="count-badge-value">
				&nbsp;%1$d
			</span>
		</span>', $result);
	}
	public function getPremiumNotice(){
	?>
	<div class="wrap">
	   <div class="notice notice-error">
		  <p>Calendarista has many more feature to help you take online appointments. Get more features by upgrading to <a href="https://www.calendarista.com/get-calendarista/" target="__blank">Calendarista Premium</a> (our paid version), which puts much more additional features and settings for a onetime fee with lifetime updates and six months of customer support.</p>
	   </div>
	</div>
	<?php
	}
	public function render(){
	?>
		<h2 class="wrap calendarista nav-tab-wrapper">
			<?php foreach($this->tabs as $tab):?>
			<?php if(!isset($tab) || !isset($tab['label'])){continue;}?>
			<a class="nav-tab <?php echo $tab['active'] ? 'nav-tab-active' : '' ?>" href="<?php echo esc_url($tab['url']) ?>"><?php echo esc_html($tab['label']) ?></a>
			<?php endforeach;?>
		</h2>
		<?php $this->getPremiumNotice(); ?>
		<?php 
			switch($this->selectedTab){
				case 0:
					new Calendarista_GeneralSettingsTemplate();
				break;
					case 1:
					new Calendarista_EmailSettingsTemplate();
				break;
					case 2:
					new Calendarista_PaymentTemplate();
				break;
					case 3:
					new Calendarista_CouponsTemplate();
				break;
					case 4:
					new Calendarista_RemindersTemplate();
				break;
					case 5:
					new Calendarista_ErrorLogTemplate();
				break;
					case 6:
					new Calendarista_AssetsTemplate();
				break;
					case 11:
					new Calendarista_GdprTemplate();
				break;
					case 12:
					new Calendarista_UninstallTemplate();
				break;
			}
		?>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.settings = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.settings.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.settings({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}