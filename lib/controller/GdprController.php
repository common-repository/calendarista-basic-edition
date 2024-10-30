<?php
class Calendarista_GdprController extends Calendarista_BaseController{
	const PAGE_UNIQUE_ID = 2;
	public function __construct($updateCallback, $denyRequestCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_gdpr')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		parent::__construct(null, $updateCallback, null);
		if (array_key_exists('calendarista_deny_request', $_POST)){
			$this->denyRequest($denyRequestCallback);
		}
		
		if (array_key_exists('calendarista_delete_user_history', $_POST)){
			$this->deleteUserHistory($deleteCallback);
		}
	}
	
	public function denyRequest($callback){
		$id = isset($_POST['calendarista_deny_request']) ? (int)$_POST['calendarista_deny_request'] : null;
		if($id){
			$repo = new Calendarista_GdprRepository();
			$repo->delete($id);
		}
		$this->executeCallback($callback, array());
	}
	
	public function deleteUserHistory($callback){
		$userEmail = isset($_POST['calendarista_delete_user_history']) ? $_POST['calendarista_delete_user_history'] : null;
		$result = 0;
		if($userEmail){
			$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
			$orderRepo = new Calendarista_OrderRepository();
			$bookings = $bookedAvailabilityRepo->readAllPastAppointmentsByEmail($userEmail);
			$gdprRepo = new Calendarista_GdprRepository();
			$gdprRepo->deleteByUserEmail($userEmail);
			$result = $bookings ? count($bookings) : 0;
			foreach($bookings as $booking){
				Calendarista_GoogleCalendarHelper::deleteEvent((int)$booking['id']);
				$orderRepo->delete((int)$booking['orderId']);
			}
		}
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$enableGDPR = isset($_POST['enableGDPR']);
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		$generalSetting = $generalSettingsRepository->read();
		$generalSetting->enableGDPR = $enableGDPR;
		$repo = new Calendarista_GeneralSettingsRepository();
		$repo->update($generalSetting);
		if($enableGDPR){
			self::registerGdprPage();
			$this->prepare();
		}else{
			self::deleteGdprPage();
		}
		$this->executeCallback($callback, array());
	}
	protected function prepare(){
		//send a notification with link to gdpr page for past customers who booked when gdpr was disabled.
		$authRepo = new Calendarista_AuthRepository();
		$orderRepo = new Calendarista_OrderRepository();
		$customers = $orderRepo->requiresGdprNotification();
		if($customers && count($customers) > 0){
			foreach($customers as $customer){
				$password = Calendarista_AuthRepository::genPassword($customer['email']);
				$authRepo->insert(array('password'=>$password, 'userEmail'=>$customer['email']));
				$emailer = new Calendarista_GdprEmailer($customer['email'], $customer['fullname'], $password);
				$emailer->send();
			}
		}
	}
	public static function registerGdprPage(){
		self::deleteGdprPage();
		$created = true;
		try{
			$pages = new WP_Query(array( 
				'meta_key'=>CALENDARISTA_META_KEY_NAME
				, 'post_type'=>'page'
			));
			$attrs = array(
				'title'=>__('General data protection regulation', 'calendarista')
				, 'content'=>'[calendarista-gdpr]'
			);
			if(!self::hasPage($pages, self::PAGE_UNIQUE_ID)){
				$id = wp_insert_post(array(
					'post_title'=>$attrs['title']
					, 'post_content'=>$attrs['content']
					, 'post_type'=>'page'
					, 'post_status'=>'publish'
					, 'show_ui'=>false
					, 'show_in_menu' =>false
					, 'show_in_admin_bar'=>false
					, 'comment_status'=> 'closed'
					, 'ping_status'=>'closed'
					, 'exclude_from_search' =>true
				));
				add_post_meta($id, CALENDARISTA_META_KEY_NAME, self::PAGE_UNIQUE_ID);
			}
		}catch(Exception $e){
			Calendarista_ErrorLogHelper::insert($e->getMessage());
			$created = false;
		}
		return $created;
	}
	
	protected static function hasPage($pages, $pageId){
		foreach($pages->posts as $page){
			$result = get_post_meta($page->ID, CALENDARISTA_META_KEY_NAME, true);
			if($result != '' && (int)$result == $pageId){
				return true;
			}
		}
		return false;
	}
	
	protected static function deleteGdprPage(){
		$args = array( 
			'meta_key'=>CALENDARISTA_META_KEY_NAME
			, 'post_type'=>'page'
		);
		try{
			$pages = new WP_Query($args);
			foreach($pages->posts as $page){
				$result = get_post_meta($page->ID, CALENDARISTA_META_KEY_NAME, true);
				if($result != '' && (int)$result == self::PAGE_UNIQUE_ID){
					wp_delete_post($page->ID, true);
					break;
				}
			}
		}catch(Exception $e){
			Calendarista_ErrorLogHelper::insert($e->getMessage());
		}
	}
}
?>