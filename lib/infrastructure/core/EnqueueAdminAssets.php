<?php
class Calendarista_EnqueueAdminAssets{
	const scriptFolder1 = 'assets/scripts/';
    const cssFolder1 = 'assets/css/';
	const scriptFolder2 = 'assets/admin/scripts/';
	const cssFolder2 = 'assets/admin/css/';
	public $generalSetting;
	public function __construct(){
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->init();
	}
	public function init(){
		$cssPath1 = CALENDARISTA_PLUGINDIR . self::cssFolder1;
		$scriptPath1 = CALENDARISTA_PLUGINDIR . self::scriptFolder1;
		$cssPath2 = CALENDARISTA_PLUGINDIR . self::cssFolder2;
		$scriptPath2 = CALENDARISTA_PLUGINDIR . self::scriptFolder2;
		if($this->generalSetting->fontFamilyUrl){
			wp_enqueue_style('calendarista-font-family', $this->generalSetting->fontFamilyUrl, null, null);
		}
		wp_enqueue_style('font-awesome', $cssPath1 . 'font-awesome.min.css');
		wp_enqueue_style('smoothness', $cssPath2 . 'jquery-ui/smoothness/jquery-ui.min.css');
		wp_enqueue_style('jquery-ui-timepicker-addon', $cssPath2 . 'jquery-ui-timepicker-addon.min.css');
		wp_enqueue_style('calendarista-fullcalendar', $cssPath2 . 'fullcalendar.min.css'. '?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('calendarista-fullcalendar-daygrid', $cssPath2 . 'fullcalendar.daygrid.min.css'. '?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('calendarista-fullcalendar-timegrid', $cssPath2 . 'fullcalendar.timegrid.min.css'. '?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('calendarista-fullcalendar-list', $cssPath2 . 'fullcalendar.list.min.css'. '?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('wp-color-picker'); 
		wp_enqueue_style('calendarista-admin', $cssPath2 . 'calendarista.admin.min.css');
		wp_enqueue_style('calendarista-user-generated-styles', home_url('/') . '?calendarista_handler=cssgen&calendarista-admin-page=1', null, null);
		wp_deregister_style('wp-jquery-ui-dialog');
		wp_enqueue_script('jquery', array(), '', true);
		wp_enqueue_script('json2', array(), '', true);
		wp_enqueue_script('jquery-ui-datepicker', array(), '', true);
		Calendarista_ScriptHelper::enqueueDatePickerLocale();
		wp_enqueue_script('jquery-ui-accordion'); 
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-progressbar');
		wp_enqueue_script('jquery-ui-menu');
		wp_enqueue_script('jquery-ui-selectmenu');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('jquery-ui-timepicker-addon', $scriptPath2 . 'jquery-ui-timepicker-addon.min.js', array(), '', true);
		wp_enqueue_script('parsley', $scriptPath1 . 'parsley.min.js', array(), '', true);
		wp_enqueue_script('parsley-comparison', $scriptPath2 . 'parsley.comparison.js', array(), '', true);
		wp_enqueue_script('calendarista-jqueryui-dialog-responsive', $scriptPath2 . 'jquery.dialog.responsive.js', array(), '', true);
		if($this->generalSetting->googleMapsKey){
			wp_enqueue_script('woald-google-maps', sprintf('https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=%s', $this->generalSetting->googleMapsKey), array(), '', true);
			wp_enqueue_script('woald-creator', $scriptPath2 . 'woald-creator.debug.js', array(), '', true);
			wp_enqueue_script('woald-styles', $scriptPath2 . 'woald-styles.1.0.min.js', array(), '', true);
		}
		if($this->generalSetting->debugMode){
			wp_enqueue_script('calendarista-admin', $scriptPath2 . 'calendarista.admin.debug.js', array(), '', true);
		}else{
			wp_enqueue_script('calendarista-admin', $scriptPath2 . 'calendarista.admin.1.0.min.js', array(), '', true);
		}
		wp_register_script('calendarista-admin-ajax', '');
		wp_enqueue_script('calendarista-admin-ajax' );
		wp_localize_script('calendarista-admin-ajax', 'calendarista_wp_ajax', array(
			 'url' => admin_url('admin-ajax.php'),
			 'nonce' => wp_create_nonce('calendarista-ajax-nonce')
		 ));
		wp_enqueue_script('moment');
		wp_enqueue_script('calendarista-fullcalendar', $scriptPath2 . 'fullcalendar.min.js'. '?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-daygrid', $scriptPath2 . 'fullcalendar.daygrid.min.js'. '?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-timegrid', $scriptPath2 . 'fullcalendar.timegrid.min.js'. '?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-list', $scriptPath2 . 'fullcalendar.list.min.js'. '?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-locale', $scriptPath2 . 'fullcalendar.locales.all.min.js'. '?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
	}
}
?>
