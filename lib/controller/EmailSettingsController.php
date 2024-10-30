<?php
class Calendarista_EmailSettingsController extends Calendarista_BaseController
{
	private $repo;
	private $mustacheEngine;
	private $emailSetting;
	public function __construct($updateCallback = null, $deleteCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_emailsettings')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		if(!class_exists('Mustache_Engine')){
			require_once CALENDARISTA_MUSTACHE . 'Autoloader.php';
			Mustache_Autoloader::register();
		}
		$this->mustacheEngine = new Mustache_Engine();
		$this->emailSetting = new Calendarista_EmailSetting(array(
			'emailType'=>isset($_POST['emailType']) ? (int)$_POST['emailType'] : null,
			'content'=>isset($_POST['content']) ? wp_kses_post($_POST['content']) : null,
			'subject'=>isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : null,
			'enable'=>isset($_POST['enable']) ? (bool)$_POST['enable'] : true,
			'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null
		));
		$this->repo = new Calendarista_EmailSettingRepository($this->emailSetting->emailType);
		parent::__construct(null, $updateCallback, $deleteCallback);
		if(array_key_exists('calendarista_updatesetting', $_POST)){
			$this->updateGeneralSetting($updateCallback);
		}
	}
	
	public function update($callback){
		$errorMessage = false;
		$result = false;
		try{
			$this->mustacheEngine->render($this->emailSetting->subject, array());
		} catch (Exception $e) {
			$errorMessage = $e->getMessage();
		}
		if(!$errorMessage){
			try{
				$this->mustacheEngine->render($this->emailSetting->content, array());
			} catch (Exception $e) {
				$errorMessage = $e->getMessage();
			} 
		}
		if(!$errorMessage){
			if($this->emailSetting->id === -1){
				$result = $this->repo->insert($this->emailSetting);
			}else{
				$result = $this->repo->update($this->emailSetting);
			}
		}
		$this->executeCallback($callback, array($result, $errorMessage));
	}
	
	public function delete($callback){
		$emailSetting = $this->repo->read();
		$result = false;
		if($emailSetting){
			$result = $this->repo->delete($emailSetting->id);
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function updateGeneralSetting($callback){
		$repo = new Calendarista_GeneralSettingsRepository();
		$result = null;

		$generalSettings = $repo->read();
		$generalSettings->emailSenderName = isset($_POST['emailSenderName']) ? sanitize_text_field($_POST['emailSenderName']) : null;
		$generalSettings->senderEmail = isset($_POST['senderEmail']) ? sanitize_email($_POST['senderEmail']) : null;
		$generalSettings->utf8EncodeEmailSubject = isset($_POST['utf8EncodeEmailSubject']) ?  (bool)$_POST['utf8EncodeEmailSubject'] : false;
		$generalSettings->adminNotificationEmail = isset($_POST['adminNotificationEmail']) ? sanitize_email($_POST['adminNotificationEmail']) : null;
		$generalSettings->smtpHostName = isset($_POST['smtpHostName']) ? sanitize_text_field($_POST['smtpHostName']) : null;
		$generalSettings->smtpUserName = isset($_POST['smtpUserName']) ? sanitize_text_field($_POST['smtpUserName']) : null;
		$generalSettings->smtpPassword = isset($_POST['smtpPassword']) ? sanitize_text_field($_POST['smtpPassword']) : null;
		$generalSettings->smtpPortNumber = isset($_POST['smtpPortNumber']) ?  (int)$_POST['smtpPortNumber'] : false;
		$generalSettings->smtpSecure = isset($_POST['smtpSecure']) ? sanitize_text_field($_POST['smtpSecure']) : null;
		$generalSettings->smtpAuthenticate = isset($_POST['smtpAuthenticate']) ?  filter_var($_POST['smtpAuthenticate'], FILTER_VALIDATE_BOOLEAN) : false;
		
		$generalSettings->emailTemplateHeaderImage = isset($_POST['emailTemplateHeaderImage']) ? sanitize_text_field($_POST['emailTemplateHeaderImage']) : null;
		$generalSettings->emailTemplateHeaderTitle = isset($_POST['emailTemplateHeaderTitle']) ? sanitize_text_field($_POST['emailTemplateHeaderTitle']) : null;
		$generalSettings->emailTemplateHeaderBackground = isset($_POST['emailTemplateHeaderBackground']) ? sanitize_text_field($_POST['emailTemplateHeaderBackground']) : null;
		$generalSettings->emailTemplateHeaderColor = isset($_POST['emailTemplateHeaderColor']) ? sanitize_text_field($_POST['emailTemplateHeaderColor']) : null;
		$generalSettings->emailTemplateBodyColor = isset($_POST['emailTemplateBodyColor']) ? sanitize_text_field($_POST['emailTemplateBodyColor']) : null;
	
		if($generalSettings->id === -1){
			$result = $repo->insert($generalSettings);
		}else{
			$result = $repo->update($generalSettings);
		}
		$this->executeCallback($callback, array($result, null));
	}
	
}
?>