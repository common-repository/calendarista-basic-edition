<?php
class Calendarista_AjaxRouter{
	
	public function __construct(){
		//error_reporting(E_ALL); 
		//ini_set("display_errors", 1);
		$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : null;
		switch($action){
			case 'calendarista_wizard': 
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'wizardCallback'));
				}else{
					$this->wizardCallback(); 
				}
				break;
			case 'calendarista_bookmore': 
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'bookMoreCallback'));
				}else{
					$this->bookMoreCallback(); 
				}
				break;
			case 'calendarista_repeat': 
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'repeatCallback'));
				}else{
					$this->repeatCallback(); 
				}
				break;
			case 'calendarista_calendar_month_change':  
				$this->calendarMonthChangeCallback(); 
				break;
			case 'calendarista_calendar_start_day_selected':  
				$calendarMode = isset($_POST['calendarMode']) ? (int)$_POST['calendarMode'] : -1;
				//1: SINGLE_DAY_AND_TIME, 2: SINGLE_DAY_AND_TIME_RANGE, 4: MULTI_DATE_AND_TIME_RANGE, 8: ROUND_TRIP_WITH_TIME, 12: MULTI_DATE_AND_TIME
				if(in_array($calendarMode, array(1, 2, 4, 8, 12)) && Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'startDaySelectedCallback'));
				} else{
					$this->startDaySelectedCallback(); 
				}
				break;
			case 'calendarista_calendar_end_day_selected':  
				$calendarMode = isset($_POST['calendarMode']) ? (int)$_POST['calendarMode'] : -1;
				//4: MULTI_DATE_AND_TIME_RANGE, 8: ROUND_TRIP_WITH_TIME, 12: MULTI_DATE_AND_TIME
				if(in_array($calendarMode, array(4, 8, 12)) && Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'endDaySelectedCallback'));
				} else{
					$this->endDaySelectedCallback();
				}
				break;
			case 'calendarista_seats':  
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'seatsCallback'));
				}else{
					$this->seatsCallback(); 
				}
				break;
			case 'calendarista_cost_summary':
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'costSummaryCallback'));
				}else{
					$this->costSummaryCallback(); 
				}
				break;
			case 'calendarista_dynamic_fields':
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'dynamicFieldsCallback'));
				}else{
					$this->dynamicFieldsCallback();
				}
				break;
			case 'calendarista_coupon_validator':  
				$this->couponValidatorCallback(); 
				break;
			case 'calendarista_customer_type_changed':  
				$this->customerTypeChangedCallback(); 
				break;
			case 'calendarista_signon':  
				$this->signOnCallback();
				break;
			case 'calendarista_create_user':  
				$this->createUserCallback(); 
				break;
			case 'calendarista_read_appointment':
				add_action('init', array($this, 'readAppointmentCallback'));
				break;
			case 'calendarista_edit_appointment': 
				add_action('init', array($this, 'editAppointmentCallback'));
				break;
			case 'calendarista_update_appointment':  
				$this->updateAppointmentCallback(); 
				break;
			case 'calendarista_user_edit_appointment':
				add_action('init', array($this, 'userEditAppointmentCallback'));
				break;
			case 'calendarista_user_update_appointment':
				add_action('init', array($this, 'userUpdateAppointmentCallback'));
				break;
			case 'calendarista_delete_appointment':  
				$this->deleteAppointmentCallback(); 
				break;
			case 'calendarista_confirm_appointment':  
				$status = isset($_POST['status']) ? (int)$_POST['status'] : null;
				$generalSetting = Calendarista_GeneralSettingHelper::get();
				if($status === 2/*cancel*/ && $generalSetting->cancelWooCommerceOrder){
					add_action('init', array($this, 'confirmAppointmentCallback'));
				}else{
					$this->confirmAppointmentCallback(); 
				}
				break;
			case 'calendarista_delete_sync_appointment':  
				$this->deleteSyncAppointmentCallback(); 
				break;
			case 'calendarista_appointments_feed': 
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->appointmentsFeedCallback(); 
				break;
			case 'calendarista_appointments_public_feed':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->getPublicAppointmentsFeedCallback();
				break;
			case 'calendarista_get_availabilities':  
				$this->getAvailabilitiesCallback(); 
				break;
			case 'calendarista_get_appointment_list':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->getAppointmentListCallback();
				break;
			case 'calendarista_update_feed_status':  
				$this->updateFeedStatusCallback(); 
				break;
			case 'calendarista_timeoff': 
				add_action('init', array($this, 'timeoffCallback'));
				break;
			case 'calendarista_sale_details':  
				$this->saleDetailsCallback(); 
				break;
			case 'calendarista_create_coupon':  
				$this->createCouponCallback(); 
				break;
			case 'calendarista_email_coupon':  
				$this->emailCouponCallback(); 
				break;
			case 'calendarista_create_staff':  
				add_action('init', array($this, 'createStaffCallback'));
				break;
			case 'calendarista_get_staff_list':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->getStaffListCallback();
				break;
			case 'calendarista_save_tag_list':  
				$this->saveTagListCallback(); 
				break;
			case 'calendarista_get_tag_list':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->getTagListCallback();
				break;
			case 'calendarista_get_coupons_list':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->getCouponsListCallback();
				break;
			case 'calendarista_get_sales_list':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				if(Calendarista_WooCommerceHelper::wooCommerceActive()){
					add_action('init', array($this, 'getSalesListCallback'));
				}else{
					$this->getSalesListCallback();
				}
				break;
			case 'calendarista_create_timeslot':  
				add_action('init', array($this, 'createTimeslotCallback'));
				break;
			case 'calendarista_autogen_timeslots': 
				add_action('init', array($this, 'autogenTimeslotsCallback'));
				break;
			case 'calendarista_autogen_search_timeslots':
				add_action('init', array($this, 'autogenSearchTimeslotsCallback'));
				break;
			case 'calendarista_edit_place':  
				$this->editPlaceCallback(); 
				break;
			case 'calendarista_get_places':  
				$this->getPlacesCallback(); 
				break;
			case 'calendarista_stripe_charge':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'handleStripe'));
				}else{
					$this->handleStripe();
				}
				break;
			case 'calendarista_shortcode_custom_form_fields':
				$this->getCustomFormFieldsShortCode();
				break;
			case 'calendarista_search':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'search'));
				}else{
					$this->search();
				}
				break;
			case 'create_availability_day':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->createAvailabilityDay();
				break;
			case 'get_availability_day_list':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->getAvailabilityDayList();
				break;
			case 'delete_availability_day':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				$this->deleteAvailabilityDay();
				break;
			case 'calendarista_setup_wizard':
				add_action('init', array($this, 'setupWizard'));
				break;
			case 'calendarista_woocommerce_submit':
				add_action('woocommerce_cart_loaded_from_session', array($this, 'wooCommerceSubmit'), 10, 1);
				break;
			case 'calendarista_user_profile':
				if(!function_exists('wp_create_nonce')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				if(Calendarista_TranslationHelper::requiresTranslation()){
					add_action('init', array($this, 'userProfile'));
				}else{
					$this->userProfile();
				}
				break;
			break;
		}
	}
	public function userProfile(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$GLOBALS['hook_suffix'] = 'calendarista_userprofile_list';
		$selectedIndex = isset($_POST['selectedIndex']) ? (int)$_POST['selectedIndex'] : null;
		switch($selectedIndex){
			case 1:
			new Calendarista_UpcomingAppointmentTmpl();
			break;
			case 2:
			new Calendarista_UserHistoryTmpl();
			break;
		}
		//based on index, switch to upcoming events or user history
		wp_die();
	}
	public function wooCommerceSubmit($cart){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$productId = isset($_POST['add-to-cart']) ? (int)$_POST['add-to-cart'] : null;
		$result = false;
		if($productId && $cart){
			$result = true;
			$cart->add_to_cart($productId);
		}
		echo wp_json_encode(array('result'=>$result, 'checkoutMode'=>$generalSetting->wooCommerceCheckoutMode, 'checkoutUrl'=>$generalSetting->wooCommerceCheckoutUrl, 'cartUrl'=>$generalSetting->wooCommerceCartUrl));
		wp_die();
	}
	public function setupWizard(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_SetupTemplate();
		wp_die();
	}
	public function createAvailabilityDay(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$availabilityId = isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : null;
		$GLOBALS['hook_suffix'] = 'calendarista_availability_day_list';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$controller = new Calendarista_AvailabilityDayController();
		$result = $controller->create();
		if($result):
		?>
		<div class="updated notice is-dismissible">
			<p><?php esc_html_e('The day was created successfully.', 'calendarista'); ?></p>
		</div>
		<?php
		else:
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e('Creation failed. It is most likely a duplicate. Try another date.', 'calendarista'); ?></p>
		</div>
		<?php
		endif;
		$availabilityList = new Calendarista_AvailabilityDayList($availabilityId);
		$availabilityList->bind();
		$availabilityList->printVariables();
		$availabilityList->display();
		wp_die();
	}
	public function getAvailabilityDayList(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$availabilityId = isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : null;
		$GLOBALS['hook_suffix'] = 'calendarista_availability_day_list';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$availabilityList = new Calendarista_AvailabilityDayList($availabilityId);
		$availabilityList->bind();
		$availabilityList->printVariables();
		$availabilityList->display();
		wp_die();
	}
	public function deleteAvailabilityDay(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$availabilityId = isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : null;
		$GLOBALS['hook_suffix'] = 'calendarista_availability_day_list';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$controller = new Calendarista_AvailabilityDayController();
		$result = $controller->delete();
		if($result):
		?>
		<div class="updated notice is-dismissible">
			<p><?php esc_html_e('The selected day(s) was deleted successfully.', 'calendarista'); ?></p>
		</div>
		<?php
		else:
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e('Deletion failed.', 'calendarista'); ?></p>
		</div>
		<?php
		endif;
		$availabilityList = new Calendarista_AvailabilityDayList($availabilityId);
		$availabilityList->bind();
		$availabilityList->printVariables();
		$availabilityList->display();
		wp_die();
	}
	public function search(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$GLOBALS['hook_suffix'] = 'calendarista_search_list';
		new Calendarista_BookingSearchResultTmpl();
		wp_die();
	}
	public function getCustomFormFieldsShortCode(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$projectList = isset($_POST['projectList']) ? array_map('intval', explode(',', sanitize_text_field($_POST['projectList']))) : null;
		$result = array();
		if($projectList):
			$formElementsRepo = new Calendarista_FormElementRepository();
			foreach($projectList as $projectId):
				$formElements = $formElementsRepo->readAll($projectId);
				if($formElements->count() > 0):?>
				<tr>
					<td><p class="description"><?php echo esc_html(sprintf('Service ID: %d', $projectId)) ?></p></td>
				</tr>
				<?php else:?>
				<tr>
					<td>--</td>
				</tr>
				<?php endif;
				foreach($formElements as $formElement): 
					if(in_array($formElement->elementType, array(4,5,6,7))){
						continue;
					}
				?>
					<tr>
						<td>
							<input type="checkbox" name="formElements" value="<?php echo esc_html($formElement->id) ?>">&nbsp;<?php echo $formElement->label ?>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endforeach;?>
		<?php endif;
		wp_die();
	}
	public function handleStripe(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$stripeHelper = new Calendarista_StripeHelper();
		$stripeHelper->charge();
		wp_die();
	}
	public function wizardCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$appointment = isset($_POST['appointment']) ? (int)$_POST['appointment'] : -1;
		switch($appointment){
			case 0:
			case 1:
				new Calendarista_AppointmentTmpl($appointment);
			break;
			default:
				new Calendarista_BookingWizardTmpl();
		}
		wp_die();
	}
	public function bookMoreCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_BookMoreTmpl();
		wp_die();
	}
	public function repeatCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_RepeatTmpl();
		wp_die();
	}
	public function calendarMonthChangeCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$projectId =  isset($_POST['projectId']) ? (int)$_POST['projectId'] : null;
		$availabilityId = isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : null;
		$appointment = null;//isset($_POST['appointment']) ? (int)$_POST['appointment'] : null;
		$startDate =  isset($_POST['changeMonthYear']) ? sanitize_text_field($_POST['changeMonthYear']) : date(CALENDARISTA_DATEFORMAT);
		$clientTime = isset($_POST['clientTime']) ? sanitize_text_field($_POST['clientTime']) : null;
		$timezone = isset($_POST['timezone']) ? sanitize_text_field($_POST['timezone']) : null;
		$requestBy = isset($_POST['requestBy']) ? (int)$_POST['requestBy'] : 0;
		$result = array(
			'exclusions'=>array()
			, 'halfDays'=>array('start'=>array(), 'end'=>array())
			, 'checkinWeekdayList'=>array()
			, 'checkoutWeekdayList'=>array()
			, 'bookedAvailabilityList'=>array()
		);
		if($availabilityId && $appointment !== 1/*not edit mode*/){
			$availabilityHelper = new Calendarista_AvailabilityHelper(array(
				'projectId'=>$projectId
				, 'availabilityId'=>$availabilityId
				, 'clientTime'=>$clientTime
				, 'timezone'=>$timezone
			));
			$result = $availabilityHelper->getAllExcludedDates(strtotime($startDate));
		}
		$result['requestBy'] = $requestBy;
		echo wp_json_encode($result);
		wp_die();
	}
	public function startDaySelectedCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		Calendarista_TranslationHelper::internationalization();
		new Calendarista_BookingTimeslotsTmpl();
		wp_die();
	}
	public function endDaySelectedCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		Calendarista_TranslationHelper::internationalization();
		new Calendarista_BookingTimeslotsTmpl(1);
		wp_die();
	}
	public function seatsCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_BookingSeatsTmpl();
		wp_die();
	}
	public function costSummaryCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_BookingCostSummaryTmpl(true);
		wp_die();
	}
	public function dynamicFieldsCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_BookingDynamicFieldsTmpl();
		wp_die();
	}
	
	public function couponValidatorCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
		$args = array(
			'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
			'coupon'=>isset($_POST['coupon']) ? sanitize_text_field($_POST['coupon']) : null,
			'discount'=>isset($_POST['discount']) ? (double)$_POST['discount'] : null,
			'discountMode'=>isset($_POST['discountMode']) ? (int)$_POST['discountMode'] : null,
			'expirationDate'=>isset($_POST['expirationDate']) ? sanitize_text_field($_POST['expirationDate']) : null,
		);
		$couponHelper = new Calendarista_CouponHelper($args);
		echo wp_json_encode($couponHelper->clientSideValidation($total));
		wp_die();
	}
	public function customerTypeChangedCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_BookingCustomFormFieldsTmpl(true);
		wp_die();
	}
	public function createUserCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$auth = new Calendarista_AuthHelper();
		$result = $auth->createUser();
		echo wp_json_encode($result);
		wp_die();
	}
	public function signOnCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$auth = new Calendarista_AuthHelper();
		$result = $auth->signOn();
		echo wp_json_encode($result);
		wp_die();
	}
	public function appointmentsFeedCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$args = array(
			'projectId'=>isset($_POST['projectId']) && !in_array($_POST['projectId'], array('', '-1')) ? (int)$_POST['projectId'] : null,
			'returnList'=>isset($_POST['returnList']) ? (bool)$_POST['returnList'] : null,
			'availabilityId'=>isset($_POST['availabilityId']) && !in_array($_POST['availabilityId'], array('', '-1')) ? (int)$_POST['availabilityId'] : null,
			'start'=>isset($_POST['start']) ? sanitize_text_field($_POST['start']) : null,
			'end'=>isset($_POST['end']) ? sanitize_text_field($_POST['end']) : null,
			'syncDataFilter'=>isset($_POST['syncDataFilter']) ? (int)$_POST['syncDataFilter'] : null,
			'pageIndex'=>isset($_POST['pageIndex']) ? (int)$_POST['pageIndex'] : -1,
			'limit'=>isset($_POST['limit']) ? (int)$_POST['limit'] : 5,
			'orderBy'=>isset($_POST['orderBy']) ? sanitize_text_field($_POST['orderBy']) : null,
			'order'=>isset($_POST['order']) ? sanitize_text_field($_POST['order']) : null,
			'status'=>isset($_POST['status']) ? (int)$_POST['status'] : null,
			'email'=>isset($_POST['email']) ? sanitize_email($_POST['email']) : null,
			'customerName'=>isset($_POST['customerName']) ? sanitize_text_field($_POST['customerName']) : null,
			'invoiceId'=>isset($_POST['invoiceId']) ? sanitize_text_field($_POST['invoiceId']) : null
		);
		$result = Calendarista_FeedHelper::getBookedAvailabilities($args);
		echo wp_json_encode($result);
		wp_die();
	}
	public function getPublicAppointmentsFeedCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$result = array();
		$result = Calendarista_PublicFeedHelper::getBookedAvailabilities(array(
			'projectList'=>isset($_POST['projectList']) ? array_map('intval', explode(',', $_POST['projectList'])) : null,
			'fromDate'=>isset($_POST['start']) ? sanitize_text_field($_POST['start']) : null,
			'toDate'=>isset($_POST['end']) ? sanitize_text_field($_POST['end']) : null,
			'calendarModeList'=>isset($_POST['calendarModeList']) ? (int)$_POST['calendarModeList'] : null,
			'pageIndex'=>isset($_POST['pageIndex']) ? (int)$_POST['pageIndex'] : -1,
			'limit'=>isset($_POST['limit']) ? (int)$_POST['limit'] : 5,
			'orderBy'=>isset($_POST['orderBy']) ? sanitize_text_field($_POST['orderBy']) : null,
			'order'=>isset($_POST['order']) ? sanitize_text_field($_POST['order']) : null,
			'returnList'=>isset($_POST['returnList']) ? (bool)$_POST['returnList'] : null,
			'formElementList'=>isset($_POST['formElementList']) ?  array_map('intval', explode(',', $_POST['formElementList'])) : null,
			'includeNameField'=>isset($_POST['includeNameField']) ?  (bool)$_POST['includeNameField'] : false,
			'includeEmailField'=>isset($_POST['includeEmailField']) ?  (bool)$_POST['includeEmailField'] : false,
			'includeSeats'=>isset($_POST['includeSeats']) ?  (bool)$_POST['includeSeats'] : false,
			'includeAvailabilityNameField'=>isset($_POST['includeAvailabilityNameField']) ?  (bool)$_POST['includeAvailabilityNameField'] : false,
			'status2'=>isset($_POST['status']) && (int)$_POST['status'] !== 3 ? (int)$_POST['status'] : null,
			'userId'=>isset($_POST['userId']) ? (int)$_POST['userId'] : null
		));
		echo wp_json_encode($result);
		wp_die();
	}
	public function readAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_AppointmentReadTmpl();
		wp_die();
	}
	public function userEditAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_EditAppointmentTmpl();
		wp_die();
	}
	public function userUpdateAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_EditAppointmentTmpl();
		wp_die();
	}
	public function editAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_AppointmentTmpl(1/*edit mode*/);
		wp_die();
	}
	public function updateAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_AppointmentTmpl(1/*edit mode*/);
		wp_die();
	}
	public function confirmAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_AppointmentReadTmpl();
		wp_die();
	}
	public function deleteAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$controller = new Calendarista_AppointmentsController();
		echo wp_json_encode(array('result'=>$controller->delete()));
		wp_die();
	}
	public function deleteSyncAppointmentCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$controller = new Calendarista_AppointmentsController();
		echo wp_json_encode(array('result'=>$controller->deleteImported()));
		wp_die();
	}
	public function getAvailabilitiesCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$projectId =  isset($_POST['projectId']) ? (int)$_POST['projectId'] : null;
		$defaultLabel = isset($_POST['defaultLabel']) ? sanitize_text_field($_POST['defaultLabel']) : __('All availabilities', 'calendarista');
		$defaultValue = isset($_POST['defaultValue']) ? sanitize_text_field($_POST['defaultValue']) : '-1';
		$selectedValue = isset($_POST['selectedValue']) ? (int)$_POST['selectedValue'] : '-1';
		$staffMemberAvailabilities = Calendarista_PermissionHelper::staffMemberAvailabilities();
		$repo = new Calendarista_AvailabilityRepository();
		$availabilities = $repo->readAll($projectId, $staffMemberAvailabilities);
		$result = array(sprintf('<option value="%s">%s</option>', $defaultValue, $defaultLabel));
		if($availabilities){
			foreach($availabilities as $availability){
				$sel = ($selectedValue && $availability->id == $selectedValue) ? 'selected' : '';
				array_push($result, sprintf('<option value="%d" %s>%s</option>', $availability->id, $sel, $availability->name));
			}
		}
		echo join('', $result);
		wp_die();
	}
	public function getAppointmentListCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		//workaround, apparently using wordpress lists in ajax request is too early and hook_suffix isn't set.
		$GLOBALS['hook_suffix'] = 'calendarista_page_calendarista-appointments';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$appointmentList = new Calendarista_AppointmentList();
		$appointmentList->bind();
		$appointmentList->printVariables();
		$appointmentList->display();
		wp_die();
	}
	public function updateFeedStatusCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$repo = new Calendarista_GeneralSettingsRepository();
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$generalSetting->enableFeeds =  (bool)$_POST['enableFeeds'];
		if($generalSetting->enableFeeds){
			$generalSetting->generateAppKey();
		}
		if($generalSetting->id === -1){
			$repo->insert($generalSetting);
		}else{
			$repo->update($generalSetting);
		}
		echo $generalSetting->appKey;
		wp_die();
	}
	public function timeoffCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_TimeoffTemplate();
		wp_die();
	}
	public function saleDetailsCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_SaleDetailTmpl();
		wp_die();
	}
	public function emailCouponCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_EmailCouponTemplate();
		wp_die();
	}
	public function createCouponCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_EditCouponTemplate();
		wp_die();
	}
	public function createStaffCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_EditStaffTemplate();
		wp_die();
	}
	public function getTagListCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		//workaround, apparently using wordpress lists in ajax request is too early and hook_suffix isn't set.
		$GLOBALS['hook_suffix'] = 'calendarista_page_calendarista-tag';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$tagList = new Calendarista_TagByAvailabilityList();
		$tagList->bind();
		$tagList->printVariables();
		$tagList->display();
		wp_die();
	}
	public function getStaffListCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		//workaround, apparently using wordpress lists in ajax request is too early and hook_suffix isn't set.
		$GLOBALS['hook_suffix'] = 'calendarista_page_calendarista-staff';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$staffList = new Calendarista_StaffList();
		$staffList->bind();
		$staffList->printVariables();
		$staffList->display();
		wp_die();
	}
	public function saveTagListCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_TagsController();
		wp_die();
	}
	public function getCouponsListCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		//workaround, apparently using wordpress lists in ajax request is too early and hook_suffix isn't set.
		$GLOBALS['hook_suffix'] = 'calendarista_page_calendarista-coupons';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$couponsList = new Calendarista_CouponsList();
		$couponsList->bind();
		$couponsList->printVariables();
		$couponsList->display();
		wp_die();
	}
	public function getSalesListCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		//workaround, apparently using wordpress lists in ajax request is too early and hook_suffix isn't set.
		$GLOBALS['hook_suffix'] = 'calendarista_page_calendarista-coupons';
		//workaround, apparently previous ajax requests change request_uri to admin-ajax.php which messes wordpress lists.
		$_SERVER['REQUEST_URI'] = sanitize_url($_POST['current_url']);
		$salesList = new Calendarista_SalesList();
		$salesList->bind();
		?>
		<h1><?php echo sprintf(__('Total Amount: %s', 'calendarista'), Calendarista_MoneyHelper::toShortCurrency($salesList->sum)) ?></h1>
		<?php
		$salesList->printVariables();
		$salesList->display();
		wp_die();
	}
	public function createTimeslotCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_CreateTimeslotTemplate();
		wp_die();
	}
	public function autogenTimeslotsCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_AutogenTimeslotsTemplate();
		wp_die();
	}
	public function autogenSearchTimeslotsCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_AutogenSearchTimeslotsTemplate();
		wp_die();
	}
	public function editPlaceCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		new Calendarista_PlaceTemplate();
		wp_die();
	}
	public function getPlacesCallback(){
		if(ob_get_length()){
			ob_clean();
		}
		$this->validateRequest();
		$placeType = isset($_POST['placeType']) ? (int)$_POST['placeType'] : null;
		new Calendarista_PlacesTemplate($placeType);
		wp_die();
	}
	public function validateRequest(){
		if(!function_exists('wp_create_nonce')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$noncePost = isset($_POST['calendarista_nonce']) ? sanitize_text_field($_POST['calendarista_nonce']) : null;
		$result = false;
		if($noncePost && wp_verify_nonce($noncePost, 'calendarista-ajax-nonce')){
			$result = true;
		}
		if(!$result){
			esc_html_e('You have been inactive for too long and your session has expired. Please refresh the page again to continue.', 'calendarista');
			wp_die();
		}
	}
}
?>
