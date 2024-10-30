<?php
class Calendarista_Emailer{
	private $emailSettingRepository;
	protected $emailSetting;
	protected $emailType;
	public $generalSetting;
	public $mustacheEngine;
	public $replyToEmail;
	public $replyToSenderName;
	public function __construct($emailType){
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!class_exists('Mustache_Engine')){
			require_once CALENDARISTA_MUSTACHE . 'Autoloader.php';
			Mustache_Autoloader::register();
		}
		$this->mustacheEngine = new Mustache_Engine();
		$this->emailSettingRepository = new Calendarista_EmailSettingRepository($emailType);
		$this->emailSetting = $this->emailSettingRepository->read();
		if(!$this->emailSetting){
			$this->emailSetting = Calendarista_EmailTemplateHelper::getTemplate($emailType);
		}
		$this->emailSetting->init();
	}
	protected function useSmtp(){
		if(!$this->generalSetting->emailSenderName || !$this->generalSetting->senderEmail ||  !$this->generalSetting->smtpHostName){
			return false;
		}
		return true;
	}
	public function smtp(){
		if(!$this->useSmtp()){
			return;
		}
		add_action('phpmailer_init',  array($this, 'smtpSettings'));
	}
	public function smtpSettings($phpmailer){
		if(!$this->useSmtp()){
			return;
		}
		if (!is_object($phpmailer)) {
			$phpmailer = (object)$phpmailer;
		}
		$senderEmail = $this->generalSetting->senderEmail;
		$emailSenderName = $this->generalSetting->emailSenderName;
		$replyToEmail = null;
		$replyToSenderName = null;
		if(in_array($this->emailType, array(Calendarista_EmailType::NEW_BOOKING_RECEIVED)))
		{
			$replyToEmail = $this->replyToEmail;
			$replyToSenderName = $this->replyToSenderName;
		}
		$phpmailer->Mailer     = 'smtp';
		$phpmailer->Host       = $this->generalSetting->smtpHostName;
		$phpmailer->SMTPAuth   = $this->generalSetting->smtpAuthenticate;
		$phpmailer->Port       = $this->generalSetting->smtpPortNumber;
		$phpmailer->Username   = $this->generalSetting->smtpUserName;
		$phpmailer->Password   = $this->generalSetting->smtpPassword;
		$phpmailer->SMTPSecure = $this->generalSetting->smtpSecure;
		$phpmailer->From       = $senderEmail;
		$phpmailer->FromName   = $emailSenderName;
		if($replyToEmail){
			$phpmailer->AddReplyTo($replyToEmail, $replyToSenderName);
		}
		remove_action('phpmailer_init', array($this, 'smtpSettings'));
	}
	protected function logErrorIfAny($result){
		if (!$result) {
			global $phpmailer;
			Calendarista_ErrorLogHelper::insert($phpmailer->ErrorInfo);
		}
	}
	protected function getUtfEncoded($content){
		if(!$this->generalSetting->utf8EncodeEmailSubject){
			return $content;
		}
		$this->setInternalEncoding();
		return mb_encode_mimeheader($content, 'UTF-8', 'Q');
	}
	protected function setInternalEncoding() {
		if ( function_exists('mb_internal_encoding') ) {
			$charset = get_option( 'blog_charset' );
			if ( ! $charset || ! @mb_internal_encoding( $charset ) ) {
				mb_internal_encoding( 'UTF-8' );
			}
		}
	}
	public function setLayout($content){
		$headerImage = $this->generalSetting->emailTemplateHeaderImage;
		$headerTitle = $this->generalSetting->emailTemplateHeaderTitle;
		$headerBackground = $this->generalSetting->emailTemplateHeaderBackground;
		$headerColor = $this->generalSetting->emailTemplateHeaderColor;
		$bodyColor = $this->generalSetting->emailTemplateBodyColor;
		$emailSettingRepository = new Calendarista_EmailSettingRepository(Calendarista_EmailType::MASTER_TEMPLATE);
		$emailSetting = $emailSettingRepository->read();
		if(!$emailSetting){
			$emailSetting = Calendarista_EmailTemplateHelper::getTemplate(Calendarista_EmailType::MASTER_TEMPLATE);
		}
		return $this->mustacheEngine->render($emailSetting->content, array(
			'body_font_color'=>$bodyColor
			, 'has_header_image'=>isset($headerImage) && strlen($headerImage) > 0
			, 'header_image'=>$headerImage
			, 'header_font_color'=>$headerColor
			, 'header_background_color'=>$headerBackground
			, 'site_title'=>$headerTitle
			, 'mail_content'=>$content
			, 'current_year'=>date('Y')
			, 'site_url'=>site_url()
			, 'site_name'=>htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES)
		));
	}
}
?>