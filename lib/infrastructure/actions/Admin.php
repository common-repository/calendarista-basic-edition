<?php
	class Calendarista_Admin{
		private $generalSetting;
		public function __construct(){
			$this->generalSetting = Calendarista_GeneralSettingHelper::get();
			add_action('admin_init', array($this, 'adminInit'));
			add_action('init', array($this, 'init'));
			add_action('admin_menu', array($this, 'menu'));
			add_action('wp_head', array($this, 'metaData'));
			add_action('wp_loaded', array($this, 'wpLoaded'));
			//we dont need update nags from third party, so remove
			$this->removeCoreUpdateNag();
			new Calendarista_RegisterJobs();
		}
		public function removeCoreUpdateNag(){
			$page = isset($_GET['page']) ? strtolower(sanitize_text_field($_GET['page'])) : null;
			if(strrpos($page, 'calendarista-') === false){
				return;
			}
			add_filter('pre_option_update_core','__return_null');
			add_filter('pre_site_transient_update_core','__return_null');
		}
		public function wpVersionWarning(){
			echo '
			<div class="update-nag"><p><strong> ' .__('Calendarista has been tested to work with WordPress 3.0 or higher. We recommend you upgrade.') . '</strong> ' . sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress') . '</p></div>
			';
		}
		public function adminInit(){
			global $wp_version;
			// all admin functions are disabled in old versions
			if ( version_compare( $wp_version, '3.0', '<' ) ) {
				add_action('admin_notices', array($this, 'wpVersionWarning' ) );
			}
		}
		public function init() {
			Calendarista_TranslationHelper::registerEmailResource();
			new Calendarista_ExpiredErrorLogJob();
		}
		public function menu() {
			new Calendarista_RegisterAdminMenuAndAssets();
		}
		public function metaData(){
			echo '<meta name="plugins" content="calendarista ' . CALENDARISTA_VERSION . '" />';
		}
		public function wpLoaded(){
			//do any serverside redirections here, otherwise it's too late in the wp life cycle.
			new Calendarista_RedirectController();
		}
	}
?>
