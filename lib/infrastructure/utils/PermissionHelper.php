<?php
class Calendarista_PermissionHelper{
	public static function wpIncludes(){
		if (!function_exists('is_user_logged_in')){
			require_once ABSPATH . WPINC . '/pluggable.php';
		}
		if (!function_exists('is_super_admin')){
			require_once ABSPATH . WPINC . '/capabilities.php';
		}
		if (!function_exists('get_user_meta')){
			require_once ABSPATH . WPINC . '/user.php';
		}
	}
	public static function wpNotificationIncludes(){
		global $wp_locale_switcher;
		if(!$wp_locale_switcher){
			require_once  ABSPATH . WPINC . '/l10n.php';
			require_once ABSPATH . WPINC . '/class-wp-locale.php';
			require_once ABSPATH . WPINC . '/class-wp-locale-switcher.php';
			$GLOBALS['wp_locale_switcher'] = new WP_Locale_Switcher();
			$GLOBALS['wp_locale_switcher']->init();
		}
	}
	public static function allowAccess($adminOnly = false){
		if(self::isAdmin()){
			return true;
		}
		if($adminOnly){
			return false;
		}
		return false;
	}
	public static function userHasPermission($projectIdList = array()){
		if(self::isAdmin()){
			return true;
		}
		if(count($projectIdList) === 0){
			return false;
		}
		$result = Calendarista_PermissionHelper::findAllStaffMembers(1);
		if(!$result){
			return false;
		}
		foreach($projectIdList as $projectId){
			if(!in_array($projectId, $result)){
				return false;
			}
		}
		return true;
	}
	public static function findAllStaffMembers($mode = 0/*return 0: availabilities, 1: projects, 2: staffMembers, 3: userId */){
		$userId = $userId = self::getUserId();
		if(!$userId){
			return false;
		}
		$repo = new Calendarista_StaffRepository();
		$args = array('userId'=>$userId);
		$staffMemberAvailabilities = $repo->readAll($args);
		if($staffMemberAvailabilities !== false && (int)$staffMemberAvailabilities['total'] > 0){
			$result = array();
			foreach($staffMemberAvailabilities['items'] as $item){
				if($mode === 0){
					array_push($result, $item['availabilityId']);
				}else if($mode === 1){
					array_push($result, $item['projectId']);
				}else if($mode === 2){
					array_push($result, $item);
				}else if($mode === 3){
					array_push($result, $item['userId']);
				}
			}
			return $result;
		}
		return false;
	}
	public static function isUserLoggedIn(){
		self::wpIncludes();
		return is_user_logged_in();
	}
	public static function isAdmin(){
		self::wpIncludes();
		return current_user_can('manage_options');
	}
	public static function userHasRole( $role, $user_id = null ) {
		self::wpIncludes();
		$user = null;
		if ( is_numeric($user_id)){
			$user = get_userdata($user_id);
		}else{
			$user = wp_get_current_user();
		}
		if (empty($user)){
			return false;
		}
		return in_array( $role, (array) $user->roles );
	}
	public static function wpUserRole( $user_id = null ) {
		self::wpIncludes();
		$user = null;
		if ( is_numeric($user_id)){
			$user = get_userdata($user_id);
		}else{
			$user = wp_get_current_user();
		}
		if (empty($user)){
			return false;
		}
		return (array)$user->roles;
	}
	public static function getUserId(){
		self::wpIncludes();
		$user = wp_get_current_user();
		if (empty($user)){
			return false;
		}
		return $user->ID;
	}
	public static function readStaffMembers($mode = 0/*return 0: availabilities, 1: projects, 2: staffMembers*/, $availabilityId = null){
		if(!$availabilityId && self::isAdmin()){
			return false;
		}
		$userId = null;
		if(!$availabilityId){
			$userId = self::getUserId();
			if(!$userId){
				return false;
			}
		}
		$repo = new Calendarista_StaffRepository();
		$args = array('userId'=>$userId);
		if($availabilityId){
			$args['availabilityId'] = $availabilityId;
		}
		$staffMemberAvailabilities = $repo->readAll($args);
		if($staffMemberAvailabilities !== false && (int)$staffMemberAvailabilities['total'] > 0){
			$result = array();
			foreach($staffMemberAvailabilities['items'] as $item){
				if($mode === 0){
					array_push($result, $item['availabilityId']);
				}else if($mode === 1){
					array_push($result, $item['projectId']);
				}else if($mode === 2){
					array_push($result, $item);
				}
			}
			return $result;
		}
		return false;
	}
	public static function getUserById($id){
		self::wpIncludes();
		return get_userdata($id);
	}
	public static function staffMemberAvailabilities(){
		return self::readStaffMembers(0);
	}
	public static function staffMemberProjects(){
		return self::readStaffMembers(1);
	}
	public static function userExistsByEmail($email){
		self::wpIncludes();
		$result = get_user_by('email', $email);
		if($result !== false){
			return $result->ID;
		}
		return false;
	}
	public static function staffMemberRole(){
		if(self::staffMemberAvailabilities() !== false){
			return self::wpUserRole();
		}
		return false;
	}
	public static function getUserInfo($userId = null){
		if($userId === null){
			$userId = get_current_user_id();
		}
		if($userId === 0){
			return null;
		}
		$user = get_userdata($userId);
		$userMeta = get_user_meta($userId);
		$username = $user->user_login;
		$firstname = $userMeta['first_name'][0];
		$lastname = $userMeta['last_name'][0];
		return array('email'=>$user->user_email, 'userId'=>$userId, 'firstname'=>$firstname, 'lastname'=>$lastname);
	}
	public static function get_role_names() {
		global $wp_roles;
		if (!isset($wp_roles)){
			$wp_roles = new WP_Roles();
		}
		$result = array();
		foreach($wp_roles->roles as $x=>$y){
			array_push($result, array('id'=>$x, 'name'=>$y['name']));
		}
		return $result;
		//return $wp_roles->get_names();
	}
}
?>