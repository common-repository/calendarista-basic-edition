<?php
class Calendarista_PayPalController extends Calendarista_BaseController
{
	private $repo;
	private $setting;
	public function __construct($createCallback, $updateCallback, $deleteCallback)
	{
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_paypal')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->repo = new Calendarista_PaymentSettingRepository();
		
		$this->setting = new Calendarista_PayPalSetting(array(
			'businessEmail'=>isset($_POST['businessEmail']) ? sanitize_email($_POST['businessEmail']) : null,
			'useSandbox'=>isset($_POST['useSandbox']) ? (bool)$_POST['useSandbox'] : null,
			'enabled'=>isset($_POST['enabled']) ? (bool)$_POST['enabled'] : null,
			'title'=>isset($_POST['title']) ? sanitize_text_field($_POST['title']) : null,
			'imageUrl'=>isset($_POST['imageUrl']) ? sanitize_url($_POST['imageUrl']) : null,
			'orderIndex'=>isset($_POST['orderIndex']) ? (int)$_POST['orderIndex'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null
		));
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$result = $this->repo->insert($this->setting);
		$this->executeCallback($callback, array($result));
	}
	
	public function update($callback){
		$result = $this->repo->update($this->setting);
		$this->executeCallback($callback, array($result));
	}
	
	public function delete($callback){
		$result = $this->repo->delete($this->setting->id);
		$this->executeCallback($callback, array($result));
	}
}
?>