<?php
class Calendarista_RegisterAdminMenuAndAssets{
	public $generalSetting;
	public function __construct(){
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->init();
	}
	public function scriptEnqueue($prefix){
		 //Enqueue conditionally
		add_action( 'load-' . $prefix, array($this, 'enqueueAssets'));
	}
	public function registerUtilities(){
		$page = isset($_GET['page']) ? strtolower(sanitize_text_field($_GET['page'])) : null;
		$tabId = isset($_GET['calendarista-tab']) ? (int)$_GET['calendarista-tab'] : null;
		if(strpos($page, 'calendarista-plugin') !== false){
			$page = 'calendarista-plugin';
		}
		if($page){
			switch ($page){
				case 'calendarista-index':
				case 'calendarista-places':
				case 'calendarista-settings':
					wp_enqueue_script('calendarista-frontend', sprintf('%sassets/scripts/calendarista.debug.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
					wp_enqueue_media();
				break;
				case 'calendarista-appointments':
				case 'calendarista-sales':
					wp_enqueue_style('calendarista-bootstrap', sprintf('%sassets/css/bootstrap.min.css', CALENDARISTA_PLUGINDIR));
					wp_enqueue_style('calendarista-frontend', sprintf('%sassets/css/calendarista.min.css', CALENDARISTA_PLUGINDIR), null, null);
					if(in_array($page, array('calendarista-appointments'))){
						wp_enqueue_style('calendarista-webui-popover', sprintf('%sassets/admin/css/jquery.webui-popover.min.css', CALENDARISTA_PLUGINDIR), null, null);
						wp_enqueue_script('calendarista-webui-popover', sprintf('%sassets/admin/scripts/jquery.webui-popover.min.js', CALENDARISTA_PLUGINDIR), null, null, false);
						wp_enqueue_script('calendarista-accounting', sprintf('%sassets/scripts/accounting.min.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
					}
					wp_enqueue_script('jsTimezoneDetect', sprintf('%sassets/scripts/jstz.min.js', CALENDARISTA_PLUGINDIR), null, null, true);
					wp_enqueue_script('calendarista-frontend', sprintf('%sassets/scripts/calendarista.debug.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
					if($this->generalSetting->googleMapsKey){
						wp_enqueue_script('calendarista-maps', sprintf('%sassets/scripts/woald-gmaps.debug.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
					}
					if(in_array($page, array('calendarista-appointments'))){
						wp_enqueue_script('calendarista-bootstrap-collapse', sprintf('%sassets/scripts/bootstrap.collapse.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
						if(Calendarista_RegisterFrontEndAssets::hasPhoneNumberField()){
							wp_enqueue_script('libphonenumber', sprintf('%sassets/scripts/libphonenumber-js.min.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
						}
					}
				break;
				case 'calendarista-setup':
					wp_enqueue_script('calendarista-frontend', sprintf('%sassets/scripts/calendarista.debug.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
				break;
				case 'calendarista-coupons':
				wp_enqueue_script('calendarista-frontend', sprintf('%sassets/scripts/calendarista.debug.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
				break;
				case 'calendarista-plugin':
				wp_enqueue_script('calendarista-frontend', sprintf('%sassets/scripts/calendarista.debug.js?ver=%s', CALENDARISTA_PLUGINDIR, CALENDARISTA_VERSION), null, null, true);
				break;
			}
		}
	}
	public function enqueueAssets(){
		new Calendarista_EnqueueAdminAssets();
		$this->registerUtilities();
	}
	public function getAdminRole(){
		$adminAccessRoles = $this->generalSetting->accessRoles;
		$currentUserRoles = Calendarista_PermissionHelper::wpUserRole();
		$result = 'administrator';
		foreach($adminAccessRoles as $acr){
			foreach($currentUserRoles as $cur){
				if(strtolower(str_replace(' ', '_', $acr)) === strtolower(str_replace(' ', '_', $cur))){
					$result = $cur;
					break 2;
				}
			}
		}
		return $result;
	}
	public function init() {
		$mainRole = $this->getAdminRole();
		$staffRole = Calendarista_PermissionHelper::staffMemberRole();
		$staffPageRole = $staffRole ? $staffRole[0] : $mainRole;
		if ( function_exists('add_menu_page') ){
			$suffixList = array();
			array_push($suffixList,
				add_menu_page(
					sprintf(__('%s: He who compiles a calendar', 'calendarista'), Calendarista_GeneralSettingHelper::getBrandName()), 
					Calendarista_GeneralSettingHelper::getBrandName(), 
					$mainRole, 
					'calendarista-index', 
					array($this, 'registerStartPage')
			));
			//duplicate the main menu
			array_push($suffixList, 
				add_submenu_page(
					'calendarista-index', 
					sprintf(__('%s: He who compiles a calendar', 'calendarista'), Calendarista_GeneralSettingHelper::getBrandName()), 
					__('Services', 'calendarista'), 
					$mainRole, 
					'calendarista-index', 
					array($this, 'registerStartPage')
			));
			if(!$this->generalSetting->disablePlacesPage){
				array_push($suffixList, 
					add_submenu_page( 
						'calendarista-index', 
						__('Google maps with directions', 'calendarista'), 
						__('Places', 'calendarista'), 
						$mainRole, 
						'calendarista-places', 
						array($this, 'registerLocationsPage')
				));
			}
			if(!$this->generalSetting->disableSalesPage){
				array_push($suffixList, 
					add_submenu_page(
						'calendarista-index', 
						__('Sales made', 'calendarista'), 
						__('Sales', 'calendarista'), 
						$staffPageRole, 
						'calendarista-sales', 
						array($this,  'registerSalesPage')
				));
			}
			array_push($suffixList, 
				add_submenu_page(
					'calendarista-index', 
					__('Calendar and List view', 'calendarista'), 
					__('Appointments', 'calendarista'), 
					$staffPageRole, 
					'calendarista-appointments', 
					array($this,  'registerAppointmentsPage')
			));
			array_push($suffixList, 
				add_submenu_page( 
					'calendarista-index', 
					__('General settings', 'calendarista'), 
					__('Settings', 'calendarista'), 
					$mainRole, 
					'calendarista-settings', 
					array($this, 'registerSettingsPage')
			));
			array_push($suffixList, 
				add_submenu_page( 
					'calendarista-index', 
					__('Setup', 'calendarista'), 
					__('Setup Wizard', 'calendarista'), 
					$mainRole, 
					'calendarista-setup', 
					array($this, 'registerSetupPage')
			));
		}
		$suffixList = apply_filters('calendarista_admin_menu', $suffixList);
		foreach($suffixList as $key=>$value){
			$this->scriptEnqueue($value);
		}
		//register always, icon needs to be in the admin menu
		wp_enqueue_style('calendarista-font', CALENDARISTA_PLUGINDIR . 'assets/admin/css/' . 'calendarista-font.css');
	}
	public function registerStartPage(){
		new Calendarista_Index();
	}
	public function registerLocationsPage(){
		new Calendarista_ManagePlaces();
	}
	public function registerSalesPage(){
		new Calendarista_ManageSales();
	}
	public function registerAppointmentsPage(){
		new Calendarista_ManageAppointments();
	}
	public function registerSettingsPage(){
		new Calendarista_ManageSettings();
	}
	public function registerSetupPage(){
		new Calendarista_SetupWizard();
	}
}
?>
