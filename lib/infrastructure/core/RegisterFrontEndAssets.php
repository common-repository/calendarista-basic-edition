<?php
class Calendarista_RegisterFrontEndAssets{
	const scriptFolder = 'assets/scripts/';
    const cssFolder = 'assets/css/';
	const scriptFolder2 = 'assets/admin/scripts/';
	const cssFolder2 = 'assets/admin/css/';
	private $registeredPaymentOperators;
	public function __construct(){
		add_action('wp_enqueue_scripts', array($this, 'cssIncludes'), 99);
		add_action('wp_enqueue_scripts', array($this, 'jsIncludes'), 0);
	}
	public function cssIncludes(){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		//custom calendar themes deprecated, too heavy. might bring it back as an add-on in the future
		$calendarStyleSheet = sprintf('jquery-ui/%s/jquery-ui.structure.css', $generalSetting->calendarTheme);
		$calendarThemeFile = CALENDARISTA_PLUGINDIR . self::cssFolder . $calendarStyleSheet;
		$cssPath1 = CALENDARISTA_PLUGINDIR . self::cssFolder;
		$cssPath2 = CALENDARISTA_PLUGINDIR . self::cssFolder2;
		if($generalSetting->fontFamilyUrl){
			wp_enqueue_style('calendarista-font-family', $generalSetting->fontFamilyUrl, null, null);
		}
		if($this->paymentOperatorEnabled(Calendarista_PaymentOperator::TWOCHECKOUT)){
			wp_enqueue_style( 'card-js', $cssPath1 . 'card-js.min.css?calendarista=' . CALENDARISTA_VERSION);
		}
		if($generalSetting->refBootstrapStyleSheet){
			wp_enqueue_style( 'calendarista-bootstrap', $cssPath1 . 'bootstrap.min.css?calendarista=' . CALENDARISTA_VERSION);
			wp_enqueue_style( 'calendarista-font-awesome', $cssPath1 . 'font-awesome.min.css?calendarista=' . CALENDARISTA_VERSION);
		}
		if($generalSetting->calendarTheme){
			wp_enqueue_style( 'jquery-ui-' . $generalSetting->calendarTheme, $calendarThemeFile);
		}
		if($generalSetting->debugMode){
			wp_enqueue_style( 'calendarista-frontend', $cssPath1 . 'calendarista.debug.css?calendarista=' . CALENDARISTA_VERSION);
		} else{
			wp_enqueue_style( 'calendarista-frontend', $cssPath1 . 'calendarista.min.css?calendarista=' . CALENDARISTA_VERSION);
		}
		$homeUrl = home_url('/');
		if(strpos($homeUrl, '?') === false){
			$homeUrl .= '?';
		}else{
			$homeUrl .= '&';
		}
		wp_enqueue_style('calendarista-fullcalendar', $cssPath2 . 'fullcalendar.min.css?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('calendarista-fullcalendar-daygrid', $cssPath2 . 'fullcalendar.daygrid.min.css?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('calendarista-fullcalendar-list', $cssPath2 . 'fullcalendar.list.min.css?calendarista=' . CALENDARISTA_VERSION);
		wp_enqueue_style('calendarista-user-generated-styles', $homeUrl . 'calendarista_handler=cssgen', null, null);
		wp_enqueue_style('wp-jquery-ui-dialog');
		do_action('calendarista_frontend_css_include', CALENDARISTA_PLUGINDIR, self::cssFolder, self::cssFolder2, CALENDARISTA_VERSION);
    }
    
    public function jsIncludes(){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$scriptPath1 =  CALENDARISTA_PLUGINDIR . self::scriptFolder;
		$scriptPath2 =  CALENDARISTA_PLUGINDIR . self::scriptFolder2;
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('calendarista-jqueryui-dialog-responsive', $scriptPath2 . 'jquery.dialog.responsive.js', array(), '', true);
		Calendarista_ScriptHelper::enqueueDatePickerLocale();
		
		if($generalSetting->refParsleyJS){
			wp_enqueue_script('parsely', $scriptPath1 . 'parsley.min.js', null, null, true);
			Calendarista_ScriptHelper::enqueueParsleyLocale();
		}
		wp_enqueue_script('jsTimezoneDetect', $scriptPath1 . 'jstz.min.js');
		wp_enqueue_script('accounting', $scriptPath1 . 'accounting.min.js');
		if($generalSetting->googleMapsKey){
			wp_enqueue_script('google-maps', sprintf('https://maps.googleapis.com/maps/api/js?v=weekly&libraries=places&key=%s', $generalSetting->googleMapsKey), null, null, true);
		}
		if($generalSetting->debugMode){
			wp_enqueue_script('calendarista-frontend', $scriptPath1 . 'calendarista.debug.js?ver=' . CALENDARISTA_VERSION, null, null, true);
			if($generalSetting->googleMapsKey){
				wp_enqueue_script('calendarista-maps', $scriptPath1 . 'woald-gmaps.debug.js?ver=' . CALENDARISTA_VERSION, null, null, true);
			}
		} else{
			wp_enqueue_script('calendarista-frontend', $scriptPath1 . 'calendarista.1.0.min.js?ver=' . CALENDARISTA_VERSION, null, null, true);
			if($generalSetting->googleMapsKey){
				wp_enqueue_script('calendarista-maps', $scriptPath1 . 'woald-gmaps.1.0.min.js?ver=' . CALENDARISTA_VERSION, null, null, true);
			}
		}
		wp_register_script('calendarista-admin-ajax', '');
		wp_enqueue_script('calendarista-admin-ajax' );
		wp_localize_script('calendarista-admin-ajax', 'calendarista_wp_ajax', array(
			 'url' => admin_url('admin-ajax.php'),
			 'nonce' => wp_create_nonce('calendarista-ajax-nonce')
		));
		wp_enqueue_script('calendarista-bootstrap-util', $scriptPath1 . 'bootstrap.util.js?ver=' . CALENDARISTA_VERSION, null, null, true);
		wp_enqueue_script('calendarista-bootstrap-alert', $scriptPath1 . 'bootstrap.alert.js?ver=' . CALENDARISTA_VERSION, null, null, true);
		wp_enqueue_script('calendarista-bootstrap-collapse', $scriptPath1 . 'bootstrap.collapse.js?ver=' . CALENDARISTA_VERSION, null, null, true);

		if($this->paymentOperatorEnabled(Calendarista_PaymentOperator::STRIPE)){
			wp_enqueue_script('stripe', 'https://js.stripe.com/v3/');
		}
		if($this->paymentOperatorEnabled(Calendarista_PaymentOperator::TWOCHECKOUT)){
			wp_enqueue_script('2checkout', 'https://www.2checkout.com/checkout/api/2co.min.js');
			wp_enqueue_script('cardjs', $scriptPath1 . 'card-js.min.js');
		}

		if(self::hasPhoneNumberField()){
			wp_enqueue_script('libphonenumber', $scriptPath1 . 'libphonenumber-js.min.js');
		}
		wp_enqueue_script('moment');
		wp_enqueue_script('calendarista-fullcalendar', $scriptPath2 . 'fullcalendar.min.js?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-daygrid', $scriptPath2 . 'fullcalendar.daygrid.min.js?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-list', $scriptPath2 . 'fullcalendar.list.min.js?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		wp_enqueue_script('calendarista-fullcalendar-locale', $scriptPath2 . 'fullcalendar.locales.all.min.js?calendarista=' . CALENDARISTA_VERSION, array(), '', true);
		do_action('calendarista_frontend_js_include', CALENDARISTA_PLUGINDIR, self::scriptFolder, self::scriptFolder2, CALENDARISTA_VERSION);
    }
	public function paymentOperatorEnabled($paymentOperator){
		if(!isset($this->registeredPaymentOperators)){
			$repo = new Calendarista_PaymentSettingRepository();
			$this->registeredPaymentOperators = $repo->readAll();
		}
		if($this->registeredPaymentOperators){
			foreach($this->registeredPaymentOperators as $operator){
				if($operator['enabled'] && $operator['paymentOperator'] === $paymentOperator){
					return true;
				}
			}
		}
		return false;
	}
	public static function hasPhoneNumberField(){
		$formElementRepo = new Calendarista_FormElementRepository();
		return $formElementRepo->hasPhoneNumber();
	}
}
?>
