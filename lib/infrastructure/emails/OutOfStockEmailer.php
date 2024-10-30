<?php
class Calendarista_OutOfStockEmailer extends Calendarista_Emailer{
	private $customerEmail;
	private $customerName;
	public function __construct($customerEmail, $customerName){
		$this->customerEmail = $customerEmail;
		$this->customerName = $customerName;
		parent::__construct(Calendarista_EmailType::APPOINTMENT_OUT_OF_STOCK);
	}
	
	public function send(){
		if(!$this->customerEmail || !$this->customerName){
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
			return $result;
		}
		return false;
	}
	
	public function getSenderAddress(){
		return $this->generalSetting->senderEmail;
	}
	
	public function getSenderName(){
		return  $this->getUtfEncoded($this->generalSetting->emailSenderName);
	}
	protected function getEmailBody(){
		return $this->mustacheEngine->render($this->emailSetting->content, array(
			'customer_name'=>$this->customerName
			, 'site_name'=>htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES)
		));
	}
}
?>