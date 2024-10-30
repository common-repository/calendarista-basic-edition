<?php
class Calendarista_GdprEmailer extends Calendarista_Emailer{
	private $customerEmail;
	private $customerName;
	private $password;
	public function __construct($customerEmail, $customerName, $password){
		$this->customerEmail = $customerEmail;
		$this->customerName = $customerName;
		$this->password = $password;
		parent::__construct(Calendarista_EmailType::GDPR);
	}
	
	public function send(){
		if(!$this->customerEmail){
			return false;
		}
		if($this->emailSetting && $this->emailSetting->enable){
			$content = $this->getEmailBody();
			$this->smtp();
			add_filter('wp_mail_from', array($this, 'getSenderAddress'));
			add_filter('wp_mail_from_name',array($this, 'getSenderName'));

			$content = $this->setLayout($content);
			$headers = array('Content-Type: text/html; charset=UTF-8');
			if (!function_exists('wp_mail')){
				require_once ABSPATH . 'wp-includes/pluggable.php';
			}
			$result = wp_mail($this->customerEmail, $this->getUtfEncoded($this->emailSetting->subject), $content, $headers);

			remove_filter('wp_mail_from', array($this, 'getSenderAddress'));
			remove_filter('wp_mail_from_name',array($this, 'getSenderName'));
				
			parent::logErrorIfAny($result);
		}
		return $result;
	}
	
	public function getSenderAddress(){
		return $this->generalSetting->senderEmail;
	}
	
	public function getSenderName(){
		return $this->getUtfEncoded($this->generalSetting->emailSenderName);
	}
	protected function getEmailBody(){
		$gdprPageUrl = self::getGdprPageUrl();
		if($gdprPageUrl){
			$gdprPageUrl = esc_url_raw(add_query_arg(array('email'=>$this->customerEmail, 'password'=>$this->password), $gdprPageUrl));
		}
		return $this->mustacheEngine->render($this->emailSetting->content, array(
			'customer_name'=>$this->customerName
			, 'customer_email'=>$this->customerEmail
			, 'gdpr_url'=>$gdprPageUrl
			, 'site_name'=>htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES)
		));
	}
	public static function getGdprPageUrl(){
		Calendarista_PermissionHelper::wpIncludes();
		if (!function_exists('get_page_permastruct')){
			require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
			$GLOBALS['wp_rewrite'] = new WP_Rewrite();
		}
		if (!function_exists('get_page_link')){
			require_once ABSPATH . WPINC . '/link-template.php ';
		}
		$pages = new WP_Query(array( 
			'meta_key'=>CALENDARISTA_META_KEY_NAME
			, 'post_type'=>'page'
		));
		$pageId = null;
		foreach($pages->posts as $page){
			$result = get_post_meta($page->ID, CALENDARISTA_META_KEY_NAME, true);
			if($result != '' && (int)$result == 2/*gdpr page identifier*/){
				$pageId = $page->ID;
				break;
			}
		}
		if($pageId){
			return get_page_link($pageId);
		}
		return null;
	}
}
?>