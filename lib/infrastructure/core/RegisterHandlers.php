<?php
class Calendarista_RegisterHandlers{
	public $invoiceId;
	public $generalSetting;
	public $paymentFailureMessage;
	public function __construct(){
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->init();
	}
	public function init(){
		$this->controllerTypeHandlers();
		$this->generalHandlers();
		$this->googleHandler();
		if(isset($_GET['calendarista_staging_id'])){
			add_filter('calendarista_checkout_invoice_id', array($this, 'getInvoiceId'));
		}
	}
	protected function generalHandlers(){
		$value = isset($_GET['calendarista_handler']) ? strtolower(sanitize_text_field($_GET['calendarista_handler'])) : null;
		switch ($value){
			case 'paypal':
				$paypalHelper = new Calendarista_PayPalHelper();
				$paypalHelper->verifyIPN();
			break;
			case 'regcheckout':
				$tmpl = new Calendarista_TemplateBase();
				new Calendarista_CheckoutController($tmpl->viewState, array($this, 'regularCheckout'), true, $tmpl->projectId);
			break;
			case 'cssgen':
				ob_start();
				header('Content-Type: text/css');
				new Calendarista_CSSHandler();
				ob_end_flush();
				exit();
			break;
			case 'gdpr':
				ob_start();
				$name = md5(uniqid() . microtime(true) . mt_rand()). '.csv';
				header('Content-Type: text/csv');
				header("Content-Disposition: attachment; filename*=UTF-8''data" . $name);
				header('Pragma: no-cache');
				header("Expires: 0");	
				$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : null;
				$password = isset($_GET['password']) ? sanitize_text_field($_GET['password']) : null;
				if($password && $email){
					$authRepo = new Calendarista_AuthRepository();
					$result = $authRepo->isValid($password, $email);
					if($result){
						$export = new Calendarista_ExportHandler(array(
							'calendarista_email'=>$email
							, 'calendarista_sales'=>true
							, 'calendarista_optionals'=>true
							, 'calendarista_formfields'=>true
							, 'calendarista_maps'=>true
						));
						echo $export->render();
					}
				}
				ob_end_flush();
				exit();
			break;
			case 'add_to_calendar':
				ob_start();
				$orderId = isset($_GET['orderId']) ? (int)$_GET['orderId'] : null;
				if(!$orderId){
					ob_end_flush();
					exit();
				}
				header('Content-Type: text/calendar; charset=utf-8');
				header(sprintf('Content-Disposition: inline; filename="%s.ics"', $orderId));
				header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				$repo = new Calendarista_BookedAvailabilityRepository();
				$bookedAvailabilityList = $repo->readByOrderId($orderId);
				echo Calendarista_AddToCalendarButtonHelper::icalendar($bookedAvailabilityList);
				ob_end_flush();
				exit();
			break;
			case 'woo_cron':
				add_action('woocommerce_loaded', array($this, 'logUnregisterdOrders'));
			break;
			case 'reminder_cron':
				Calendarista_Helper::reminder();
				exit();
			break;
		}
	}
	public function logUnregisterdOrders(){
		Calendarista_WooCommerceHelper::logUnregisterdOrders();
		exit();
	}
	protected function googleHandler(){
		$val = isset($_GET['code']) && isset($_GET['state']) ? sanitize_text_field($_GET['state']) : null;
		if($val){
			$state = json_decode(self::base64UrlDecode($val), true);
			$token = sanitize_text_field($_GET['code']);
			if(isset($state['calendarista_action']) && $state['calendarista_action'] === 'gcal'){
				Calendarista_GoogleCalendarHelper::handleAccessToken((int)$state['calendarista_userid'], $token);
				$redirectUrl = admin_url() . 'admin.php?page=calendarista-settings&calendarista-tab=9';
				self::_wp_redirect($redirectUrl);
				exit();
			}
		}
	}
	public function getInvoiceId(){
		if($this->invoiceId){
			return $this->invoiceId;
		}
		if(!isset($_GET['calendarista_staging_id'])){
			return null;
		}
		$repo = new Calendarista_OrderRepository();
		return $repo->readInvoiceByStagingId($_GET['calendarista_staging_id']);
	}
	public function getPaymentFailureMessage(){
		return $this->paymentFailureMessage;
	}
	public function regularCheckout($invoiceId, $orderIsValid = true){
		$this->invoiceId = $invoiceId;
		if(!$orderIsValid){
			return;
		}
		$this->handleConfirmPage($this->invoiceId);
	}
	public function handleConfirmPage($invoiceId){
		if($this->generalSetting->confirmUrl){
			if (!function_exists('get_page_permastruct')){
				require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
				$GLOBALS['wp_rewrite'] = new WP_Rewrite();
			}
			if (!function_exists('get_page_link')){
				require_once ABSPATH . WPINC . '/link-template.php ';
			}
			$post = get_post($this->generalSetting->confirmUrl);
			if(!$post){
				return;
			}
			$url = get_page_link($post);
			if($url){
				$failureMessage = apply_filters('calendarista_checkout_payment_failure_message', null);
				$failureMessage .= sprintf(' %s', $this->paymentFailureMessage);
				if(!$invoiceId && !trim($failureMessage)){
					$failureMessage = $this->stringResources['BOOKING_PAYMENT_FAILED'];
				}
				$url = self::appendQueryString($url, array('calendarista_invoice_id'=>$invoiceId, 'calendarista_failure_msg'=>$failureMessage));
				if(self::_wp_redirect($url)){
					exit;
				}
			}
		}
	}
	protected function controllerTypeHandlers(){
		//handlers via controllers, go here.
		$this->uninstallHandler();
	}
	protected function uninstallHandler(){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_uninstall')){
				return;
		}
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		if(!current_user_can('delete_plugins')){
			return;
		}
		if (array_key_exists('calendarista_delete', $_POST)){
			Calendarista_Install::uninstall();
		}
	}
	public static function base64UrlDecode($value)
	{
		return base64_decode(strtr($value, '-_,', '+/='));
	}
	public static function _wp_redirect($url){
		if (!function_exists('wp_redirect')){
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}
		return wp_redirect($url);
	}
	public static function appendQueryString($url, $args){
		foreach($args as $key=>$value){
			if(!$value){
				continue;
			}
			$url .= (strpos($url,'?') !== false) ? '&' : '?';
			$url .= sprintf('%s=%s', $key, $value);
		}
		return $url;
	}
}
?>
