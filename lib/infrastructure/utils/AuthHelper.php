<?php
class Calendarista_AuthHelper{
	private $projectId;
	private $customerType = -1;
	private $email;
	private $name;
	private $password;
	private $stringResources;
	public function __construct(){
		if(array_key_exists('projectId', $_POST)){
			$this->projectId = (int)$_POST['projectId'];
		}
		if(array_key_exists('customerType', $_POST)){
			$this->customerType = (int)$_POST['customerType'];
		}
		if(array_key_exists('email', $_POST)){
			$this->email = (string)$_POST['email'];
		}
		if(array_key_exists('name', $_POST)){
			$this->name = (string)$_POST['name'];
		}
		if(array_key_exists('password', $_POST)){
			$this->password = (string)$_POST['password'];
		}
		$stringResourcesRepo = new Calendarista_StringResourcesRepository();
		$stringResources = $stringResourcesRepo->read($this->projectId);
		$this->stringResources = $stringResources->resources;
		add_action('set_logged_in_cookie', array($this, 'updateCookie'));
	}
	public function updateCookie($logged_in_cookie){
		$_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
	}
	public function signOn(){
		Calendarista_PermissionHelper::wpIncludes();
		$result = null;
		if(is_user_logged_in()){
			$user = wp_get_current_user();
		}else{
			//maintain compatibility with current wp_signon which is username based.
			$user = get_user_by('email', $this->email);
			$creds = array(
				'user_login'=>$user ? $user->user_login : null
				, 'user_password'=>$this->password
				, 'remember'=>false
			);
			$user = wp_signon($creds, false);
		}
		if (!is_wp_error($user)){
			wp_set_current_user($user->ID);
			$userMeta = get_user_meta($user->ID);
			$result = array(
				'userId'=>$user->ID
				, 'name'=>trim(implode(' ', array($userMeta['first_name'][0], $userMeta['last_name'][0])))
				, 'email'=>$user->user_email
				, 'nonce'=>wp_create_nonce('calendarista-ajax-nonce')
			);
		}
		return $result;
	}
	public function createUser(){
		Calendarista_PermissionHelper::wpIncludes();
		$names = explode(' ', $this->name);
		$userInfo = get_user_by('email', $this->email);
		$result = null;
		if(!$userInfo){
			$userId = wp_create_user($this->email, $this->password, $this->email);
			if (isset($names[0])){
				update_user_meta($userId, 'first_name', $names[0]);
			}
			if (isset($names[1])){
				update_user_meta($userId, 'last_name', $names[1]);
			}
			Calendarista_PermissionHelper::wpNotificationIncludes();
			wp_new_user_notification($userId, null, 'both');
			$user = wp_signon(array(
				'user_login'=>$this->email
				, 'user_password'=>$this->password
				, 'remember'=>false), false
			);
			if (!is_wp_error($user)){
				wp_set_current_user($user->ID);
				$result = $user->ID;
			}
		}
		if(!$result){
			return null;
		}
		return array('userId'=>$result, 'nonce'=>wp_create_nonce('calendarista-ajax-nonce'));
	}
	public static function getUserData(){
		Calendarista_PermissionHelper::wpIncludes();
		if(!is_user_logged_in()){
			return array('userId'=>null, 'name'=>null, 'email'=>null);
		}
		$user = wp_get_current_user();
		$userMeta = get_user_meta($user->ID);
		return array(
			'userId'=>$user->ID
			, 'name'=>trim(implode(' ', array($userMeta['first_name'][0], $userMeta['last_name'][0])))
			, 'email'=>$user->user_email
		);
	}
}
?>