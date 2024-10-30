<?php
class Calendarista_GeneralSettingsController extends Calendarista_BaseController{
	private $repo;
	private $id;
	private $project;
	const PAGE_UNIQUE_ID = 1;
	public function __construct($createCallback = null, $updateCallback = null, $deleteCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_generalsettings')){
				return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->project = array(
			'translationEngine'=>isset($_POST['translationEngine']) ? (int)$_POST['translationEngine'] : 0,
			'fontFamilyUrl'=>isset($_POST['fontFamilyUrl']) ? sanitize_url($_POST['fontFamilyUrl']) : null,
			'emailTemplateHeaderImage'=>isset($_POST['emailTemplateHeaderImage']) ? sanitize_text_field($_POST['emailTemplateHeaderImage']) : null,
			'emailTemplateHeaderTitle'=>isset($_POST['emailTemplateHeaderTitle']) ? sanitize_text_field($_POST['emailTemplateHeaderTitle']) : null,
			'emailTemplateHeaderBackground'=>isset($_POST['emailTemplateHeaderBackground']) ? sanitize_text_field($_POST['emailTemplateHeaderBackground']) : null,
			'emailTemplateHeaderColor'=>isset($_POST['emailTemplateHeaderColor']) ? sanitize_text_field($_POST['emailTemplateHeaderColor']) : null,
			'emailTemplateBodyColor'=>isset($_POST['emailTemplateBodyColor']) ? sanitize_text_field($_POST['emailTemplateBodyColor']) : null,
			'emailSenderName'=>isset($_POST['emailSenderName']) ? sanitize_text_field($_POST['emailSenderName']) : null,
			'senderEmail'=>isset($_POST['senderEmail']) ? sanitize_email($_POST['senderEmail']) : null,
			'utf8EncodeEmailSubject'=>isset($_POST['utf8EncodeEmailSubject']) ?  (bool)$_POST['utf8EncodeEmailSubject'] : false,
			'adminNotificationEmail'=>isset($_POST['adminNotificationEmail']) ? sanitize_email($_POST['adminNotificationEmail']) : null,
			'autoConfirmOrderAfterPayment'=>isset($_POST['autoConfirmOrderAfterPayment']) ?  (bool)$_POST['autoConfirmOrderAfterPayment'] : false,
			'autoApproveBooking'=>isset($_POST['autoApproveBooking']) ?  (bool)$_POST['autoApproveBooking'] : false,
			'autoInvoiceNotification'=>isset($_POST['autoInvoiceNotification']) ?  (bool)$_POST['autoInvoiceNotification'] : false,
			'autoNotifyAdminNewBooking'=>isset($_POST['autoNotifyAdminNewBooking']) ?  (bool)$_POST['autoNotifyAdminNewBooking'] : false,
			'notifyBookingReceivedSuccessfully'=>isset($_POST['notifyBookingReceivedSuccessfully']) ?  (bool)$_POST['notifyBookingReceivedSuccessfully'] : false,
			'notifyBookingHasChanged'=>isset($_POST['notifyBookingHasChanged']) ?  (bool)$_POST['notifyBookingHasChanged'] : false,
			'outOfStockNotification'=>isset($_POST['outOfStockNotification']) ?  (bool)$_POST['outOfStockNotification'] : false,
			'notifyBookingConfirmation'=>isset($_POST['notifyBookingConfirmation']) ?  (bool)$_POST['notifyBookingConfirmation'] : false,
			'enableCoupons'=>isset($_POST['enableCoupons']) ?  (bool)$_POST['enableCoupons'] : false,
			'refBootstrapStyleSheet'=>isset($_POST['refBootstrapStyleSheet']) ?  (bool)$_POST['refBootstrapStyleSheet'] : false,
			'refParsleyJS'=>isset($_POST['refParsleyJS']) ?  (bool)$_POST['refParsleyJS'] : false,
			'calendarTheme'=>isset($_POST['calendarTheme']) ? sanitize_text_field($_POST['calendarTheme']) : null,
			'shorthandDateFormat'=>isset($_POST['shorthandDateFormat']) ? sanitize_text_field($_POST['shorthandDateFormat']) : null,
			'timeFormat'=>isset($_POST['timeFormat']) ?  (int)$_POST['timeFormat'] : false,
			'enableUserCancelBooking'=>isset($_POST['enableUserCancelBooking']) ?  (bool)$_POST['enableUserCancelBooking'] : false,
			'cancelBookingUrl'=>isset($_POST['cancelBookingUrl']) ? sanitize_url($_POST['cancelBookingUrl']) : null,
			'enableCancelBookingAlert'=>isset($_POST['enableCancelBookingAlert']) ?  (bool)$_POST['enableCancelBookingAlert'] : false,
			'customerBookingCancelNotification'=>isset($_POST['customerBookingCancelNotification']) ?  (bool)$_POST['customerBookingCancelNotification'] : false,
			'enableCancelNotificationOnDelete'=>isset($_POST['enableCancelNotificationOnDelete']) ?  (bool)$_POST['enableCancelNotificationOnDelete'] : false,
			'enableGDPR'=>isset($_POST['enableGDPR']) ?  (bool)$_POST['enableGDPR'] : false,
			'debugMode'=>isset($_POST['debugMode']) ?  (bool)$_POST['debugMode'] : false,
			'membershipRequired'=>isset($_POST['membershipRequired']) ?  (bool)$_POST['membershipRequired'] : false,
			'firstDayOfWeek'=>isset($_POST['firstDayOfWeek']) ?  (int)$_POST['firstDayOfWeek'] : false,
			'googleMapsKey'=>isset($_POST['googleMapsKey']) ? sanitize_text_field($_POST['googleMapsKey']) : null,
			'currency'=>isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : null,
			'confirmUrl'=>isset($_POST['confirmUrl']) ?  (int)$_POST['confirmUrl'] : false,
			'enableMobileInitialScale'=>isset($_POST['enableMobileInitialScale']) ?  (bool)$_POST['enableMobileInitialScale'] : false,
			'enableFeeds'=>isset($_POST['enableFeeds']) ?  (bool)$_POST['enableFeeds'] : false,
			'appKey'=>isset($_POST['appKey']) ? sanitize_text_field($_POST['appKey']) : null,
			'purchaseCode'=>isset($_POST['purchaseCode']) ? sanitize_text_field($_POST['purchaseCode']) : null,
			'cronJobFeedTimeout'=>isset($_POST['cronJobFeedTimeout']) ?  (int)$_POST['cronJobFeedTimeout'] : false,
			'cancellationPolicy'=>isset($_POST['cancellationPolicy']) ?  (int)$_POST['cancellationPolicy'] : false,
			'wooCommerceCheckoutUrl'=>isset($_POST['wooCommerceCheckoutUrl']) ? sanitize_url($_POST['wooCommerceCheckoutUrl']) : null,
			'wooCommerceCartUrl'=>isset($_POST['wooCommerceCartUrl']) ? sanitize_url($_POST['wooCommerceCartUrl']) : null,
			'wooCommerceCheckoutMode'=>isset($_POST['wooCommerceCheckoutMode']) ?  (int)$_POST['wooCommerceCheckoutMode'] : false,
			'wooCommerceProcessingStatusOnly'=>isset($_POST['wooCommerceProcessingStatusOnly']) ?  (bool)$_POST['wooCommerceProcessingStatusOnly'] : false,
			'prefix'=>isset($_POST['prefix']) ? sanitize_text_field($_POST['prefix']) : null,
			'tax'=>isset($_POST['tax']) ?  (float)$_POST['tax'] : false,
			'currencySymbolPlacement'=>isset($_POST['currencySymbolPlacement']) ?  (int)$_POST['currencySymbolPlacement'] : false,
			'taxMode'=>isset($_POST['taxMode']) ?  (int)$_POST['taxMode'] : false,
			'approvedColor'=>isset($_POST['approvedColor']) ? sanitize_text_field($_POST['approvedColor']) : null,
			'pendingApprovalColor'=>isset($_POST['pendingApprovalColor']) ? sanitize_text_field($_POST['pendingApprovalColor']) : null,
			'cancelledColor'=>isset($_POST['cancelledColor']) ? sanitize_text_field($_POST['cancelledColor']) : null,
			'smtpHostName'=>isset($_POST['smtpHostName']) ? sanitize_text_field($_POST['smtpHostName']) : null,
			'smtpUserName'=>isset($_POST['smtpUserName']) ? sanitize_text_field($_POST['smtpUserName']) : null,
			'smtpPassword'=>isset($_POST['smtpPassword']) ? sanitize_text_field($_POST['smtpPassword']) : null,
			'smtpPortNumber'=>isset($_POST['smtpPortNumber']) ?  (int)$_POST['smtpPortNumber'] : false,
			'smtpSecure'=>isset($_POST['smtpSecure']) ? sanitize_text_field($_POST['smtpSecure']) : null,
			'smtpAuthenticate'=>isset($_POST['smtpAuthenticate']) ?  (bool)$_POST['smtpAuthenticate'] : false,
			'searchFilterFindButtonLabel'=>isset($_POST['searchFilterFindButtonLabel']) ? sanitize_text_field($_POST['searchFilterFindButtonLabel']) : null,
			'searchFilterSelectButtonLabel'=>isset($_POST['searchFilterSelectButtonLabel']) ? sanitize_text_field($_POST['searchFilterSelectButtonLabel']) : null,
			'searchFilterSoldOutLabel'=>isset($_POST['searchFilterSoldOutLabel']) ? sanitize_text_field($_POST['searchFilterSoldOutLabel']) : null,
			'searchFilterAlternateDateLabel'=>isset($_POST['searchFilterAlternateDateLabel']) ? sanitize_text_field($_POST['searchFilterAlternateDateLabel']) : null,
			'searchIncludeAlternateDates'=>isset($_POST['searchIncludeAlternateDates']) ?  (bool)$_POST['searchIncludeAlternateDates'] : false,
			'searchIncludeSoldoutDates'=>isset($_POST['searchIncludeSoldoutDates']) ?  (bool)$_POST['searchIncludeSoldoutDates'] : false,
			'disablePlacesPage'=>isset($_POST['disablePlacesPage']) ?  (bool)$_POST['disablePlacesPage'] : false,
			'disableSalesPage'=>isset($_POST['disableSalesPage']) ?  (bool)$_POST['disableSalesPage'] : false,
			'disableStaffPage'=>isset($_POST['disableStaffPage']) ?  (bool)$_POST['disableStaffPage'] : false,
			'disableSeasonsPage'=>isset($_POST['disableSeasonsPage']) ?  (bool)$_POST['disableSeasonsPage'] : false,
			'disableMapPage'=>isset($_POST['disableMapPage']) ?  (bool)$_POST['disableMapPage'] : false,
			'thousandSep'=>isset($_POST['thousandSep']) ? sanitize_text_field($_POST['thousandSep']) : null,
			'decimalPoint'=>isset($_POST['decimalPoint']) ? sanitize_text_field($_POST['decimalPoint']) : null,
			'googleCalendarAltCronJob'=>isset($_POST['googleCalendarAltCronJob']) ?  (bool)$_POST['googleCalendarAltCronJob'] : false,
			'wooCommerceAltCronJob'=>isset($_POST['wooCommerceAltCronJob']) ?  (bool)$_POST['wooCommerceAltCronJob'] : false,
			'reminderAltCronJob'=>isset($_POST['reminderAltCronJob']) ?  (bool)$_POST['reminderAltCronJob'] : false,
			'searchFilterTheme'=>isset($_POST['searchFilterTheme']) ? sanitize_text_field($_POST['searchFilterTheme']) : null,
			'appointmentTabOrder'=>isset($_POST['appointmentTabOrder']) ?  (int)$_POST['appointmentTabOrder'] : false,
			'appointmentListOrder'=>isset($_POST['appointmentListOrder']) ?  (int)$_POST['appointmentListOrder'] : false,
			'fontSize'=>isset($_POST['fontSize']) ?  (double)$_POST['fontSize'] : false,
			'autoCompleteWooCommerceOrder'=>isset($_POST['autoCompleteWooCommerceOrder']) ?  (bool)$_POST['autoCompleteWooCommerceOrder'] : false,
			'addBookingFormWooProduct'=>isset($_POST['addBookingFormWooProduct']) ?  (bool)$_POST['addBookingFormWooProduct'] : false,
			'cancelWooCommerceOrder'=>isset($_POST['cancelWooCommerceOrder']) ?  (bool)$_POST['cancelWooCommerceOrder'] : false,
			'includeCustomFieldsWooCommerce'=>isset($_POST['includeCustomFieldsWooCommerce']) ?  (bool)$_POST['includeCustomFieldsWooCommerce'] : false,
			'includeGuestsWooCommerce'=>isset($_POST['includeGuestsWooCommerce']) ?  (bool)$_POST['includeGuestsWooCommerce'] : false,
			'brandName'=>isset($_POST['brandName']) ? sanitize_text_field($_POST['brandName']) : null,
			'newAppointmentZap'=>isset($_POST['newAppointmentZap']) ? sanitize_url($_POST['newAppointmentZap']) : null,
			'updatedAppointmentZap'=>isset($_POST['updatedAppointmentZap']) ? sanitize_url($_POST['updatedAppointmentZap']) : null,
			'newCustomerZap'=>isset($_POST['newCustomerZap']) ? sanitize_url($_POST['newCustomerZap']) : null,
			'displayAddToCalendarOption'=>isset($_POST['displayAddToCalendarOption']) ?  (bool)$_POST['displayAddToCalendarOption'] : false,
			'accessRoles'=>isset($_POST['accessRoles']) ?  (array)$_POST['accessRoles'] : false,
			'searchTimeslots'=>isset($_POST['searchTimeslots']) ? sanitize_text_field($_POST['searchTimeslots']) : null,
			'twilioAccountSID'=>isset($_POST['twilioAccountSID']) ? sanitize_text_field($_POST['twilioAccountSID']) : null,
			'twilioAuthToken'=>isset($_POST['twilioAuthToken']) ? sanitize_text_field($_POST['twilioAuthToken']) : null,
			'twilioFromNumber'=>isset($_POST['twilioFromNumber']) ? sanitize_text_field($_POST['twilioFromNumber']) : null,
			'twilioCountryCode'=>isset($_POST['twilioCountryCode']) ? sanitize_text_field($_POST['twilioCountryCode']) : null,
			'smsContentTemplate'=>isset($_POST['smsContentTemplate']) ? wp_kses_post($_POST['smsContentTemplate']) : null,
			'displayStepsMobileView'=>isset($_POST['displayStepsMobileView']) ?  (bool)$_POST['displayStepsMobileView'] : false,
			'id'=>isset($_POST['id']) ?  (int)$_POST['id'] : false,
		);
		foreach($this->project as $key=>$value){
			if(!isset($_POST[$key])){
				unset($this->project[$key]);
			}
		}
		$this->repo = new Calendarista_GeneralSettingsRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	public function create($callback){
		$generalSetting = new Calendarista_GeneralSetting($this->project);
		$generalSetting->cancelBookingUrl = null;
		if($this->generalSetting->enableUserCancelBooking){
			$generalSetting->cancelBookingUrl = self::registerCancelPage();
		}else{
			$generalSetting->cancelBookingUrl = null;
			self::deleteCancelPage();
		}
		$result = $this->repo->insert($generalSetting);
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$old = $this->generalSetting;
		$new = new Calendarista_GeneralSetting(array_merge($old->toArray(), $this->project));
		if($old->enableUserCancelBooking != $new->enableUserCancelBooking){
			//setting has changed
			if($new->enableUserCancelBooking){
				$new->cancelBookingUrl = self::registerCancelPage();
			}else{
				$new->cancelBookingUrl = null;
				self::deleteCancelPage();
			}
		}
		$result = $this->repo->update($new);
		$this->executeCallback($callback, array($result));
	}
	public function delete($callback){
		$appKey = $this->generalSetting->appKey;
		$result = $this->repo->delete($this->generalSetting->id);
		if($appKey){
			//maintain appKey
			$generalSetting = new Calendarista_GeneralSetting();
			$generalSetting->appKey = $appKey;
			$this->repo->insert($generalSetting);
		}
		self::deleteCancelPage();
		$this->executeCallback($callback, array($result));
	}
	public static function registerCancelPage(){
		self::deleteCancelPage();
		$result;
		try{
			$pages = new WP_Query(array( 
				'meta_key'=>CALENDARISTA_META_KEY_NAME
				, 'post_type'=>'page'
			));
			$attrs = array(
				'title'=>__('Cancel Appointment', 'calendarista')
				, 'content'=>'[calendarista-cancel-appointment]'
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
				$result = get_page_link($id);
			}
		}catch(Exception $e){
			Calendarista_ErrorLogHelper::insert($e->getMessage());
			$result = false;
		}
		return $result;
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
	
	protected static function deleteCancelPage(){
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