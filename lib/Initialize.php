<?php
class Calendarista_Initialize{
	public function __construct(){
		if($this->is_plugin_active('/Calendarista.php')){
			add_action('after_setup_theme', array($this, 'exitMenu'));
			return;
		}
		$this->autoload();
		do_action('calendarista_loading');
		new Calendarista_RegisterHandlers();
		if (Calendarista_AjaxHelper::doingAjax()) {
			new Calendarista_AjaxRouter();
		}
		add_action('after_setup_theme', array($this, 'init'));
		if (!Calendarista_AjaxHelper::doingAjax()) {
			add_filter('cron_schedules', array($this, 'cronSchedule'));
			register_activation_hook(CALENDARISTA_ROOT_FILE, array('Calendarista_Install', 'init'));
			register_deactivation_hook(CALENDARISTA_ROOT_FILE, array('Calendarista_Install', 'deactivate'));
			add_action('activated_plugin', array('Calendarista_Initialize', 'activated'));
			self::checkDBVersion();
		}
	}
	protected function is_plugin_active($slug) {
		$p1 = get_option('active_plugins', array());
		if(!$p1){
			return false;
		}
		foreach($p1 as $p){
			if(strrpos($p, $slug) !== false){
				return true;
			}
		}
		return false;
	}
	protected function getPluginSlug($name){
		$p1 = get_option('active_plugins', array());
		if(!$p1){
			return $name;
		}
		foreach($p1 as $p){
			if(strrpos($p, $name) !== false){
				return $p;
			}
		}
		return $name;
	}
	public function exitMenu(){
		add_action('admin_menu', array($this, 'menu'));
	}
	public function menu(){
		if ( function_exists('add_menu_page') ){
			add_menu_page(
					__('Calendarista: He who compiles a calendar', 'calendarista'), 
					'Calendarista', 
					'administrator', 
					'calendarista-index',
					array($this, 'exitNotice')
			);
		}
	}
	public function exitNotice(){
		$slug = $this->getPluginSlug('/CalendaristaBasic.php');
	?>
	<div class="wrap">
		<div class="column-pane">
		   <div class="notice notice-error is-dismissible">
			  <p><?php echo sprintf(__('You cannot use both Calendarista Premium and Calendarista Basic at the same time. Please deactivate and delete the Basic version on the %splugins page%s', 'calendarista'), '<a href="' . wp_nonce_url(sprintf('plugins.php?action=deactivate&amp;plugin=%s&amp;plugin_status=all&amp;paged=1&amp;s=', $slug), sprintf('deactivate-plugin_%s', $slug)) . '">', '</a>'); ?></p>
		   </div>
		</div>
	</div>
	<?php
	}
	public static function checkDBVersion(){
		$dbVersion = floatval(get_option('calendarista_db_version'));
		$newVersion = floatval(Calendarista_Install::getSqlScript('version.txt'));
		if($dbVersion !== $newVersion){
			Calendarista_Install::init();
		}
	}
	public static function activated($slug){
		if(!defined('CALENDARISTA_ACTIVATION_NOREDIRECT') && $slug === plugin_basename(CALENDARISTA_ROOT_FILE)){
			$url = esc_url(admin_url('admin.php?page=calendarista-setup&welcome=1'));
			wp_redirect($url);
			exit;
		}
	}
	public static function adminNotice(){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!Calendarista_NetworkHelper::isLocalhost() && !$generalSetting->purchaseCode){
			$shopLink = '<a href="https://www.calendarista.com/get-calendarista/" target="_blank">Envato</a>';
			$registerLink = sprintf('<a href="%s" target="_blank">%s</a>', admin_url() . 'admin.php?page=calendarista-settings&calendarista-tab=10', __('register', 'calendarista'));
		?>
		<div class="wrap">
			<div class="calendarista-notice error notice is-dismissible calendarista-license-warning">
				<p><strong><?php echo sprintf('Please %s Calendarista now to ensure all features are enabled and that you are running a valid licensed copy. For a valid license, go to our shop on %s.', $registerLink, $shopLink); ?></strong></p>
			</div>
		</div>
		<?php
		}
	}
	protected function autoload(){
		require_once CALENDARISTA_ROOT_FOLDER . '/autoload.php';
	}
	function cronSchedule($schedules){
		$schedules['calendarista_everyminute'] = array(
				'interval'  => 60, // time in seconds
				'display'   => 'Every Minute'
		);
		return $schedules;
	}
	public function init(){
		Calendarista_TranslationHelper::internationalization();
		if (!Calendarista_AjaxHelper::doingAjax()) {
			add_action('admin_notices', array('Calendarista_Initialize', 'adminNotice'));
		}
		if (is_admin()){
			new Calendarista_Admin();
			new Calendarista_Register();
		} else{
			new Calendarista_Register();
		}
	}
}
?>