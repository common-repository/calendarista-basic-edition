<?php
class Calendarista_GeneralSetting extends Calendarista_EntityBase{
	public $id = -1;
	public $autoConfirmOrderAfterPayment = true;
	public $autoApproveBooking = false;
	public $autoInvoiceNotification = false;
	public $autoNotifyAdminNewBooking = true;
	public $notifyBookingReceivedSuccessfully = true;
	public $notifyBookingHasChanged = true;
	public $outOfStockNotification = true;
	public $notifyBookingConfirmation = true;
	public $enableCoupons = true;
	public $refBootstrapStyleSheet = true;
	public $refParsleyJS = true;
	public $calendarTheme = 'smoothness';
	public $shorthandDateFormat = 'dd/mm/yy';
	public $timeFormat = Calendarista_TimeFormat::AMPM;
	public $enableUserCancelBooking = false;
	public $cancelBookingUrl = null;
	public $customerBookingCancelNotification = true;
	public $enableCancelBookingAlert = false;
	public $enableGDPR = false;
	public $debugMode = false;
	public $membershipRequired = true;
	public $firstDayOfWeek = 7;
	public $googleMapsKey;
	public $currency = 'USD';
	public $enableMobileInitialScale = true;
	public $emailSenderName;
	public $senderEmail;
	public $adminNotificationEmail;
	public $emailTemplateHeaderImage;
	public $emailTemplateHeaderTitle;
	public $emailTemplateHeaderBackground = '#999999';
	public $emailTemplateHeaderColor = '#ffffff';
	public $emailTemplateBodyColor = '#000';
	public $fontFamilyUrl;
	public $enableFeeds;
	public $appKey;
	public $purchaseCode;
	public $cronJobFeedTimeout= 5;
	public $confirmUrl;
	public $translationEngine = 0;
	public $cancellationPolicy = 0;
	public $wooCommerceCheckoutUrl;
	public $wooCommerceCartUrl;
	public $wooCommerceCheckoutMode = 0;
	public $prefix = 'CA';
	public $tax = 0;
	public $taxMode = 0/*exclusive*/;
	public $currencySymbolPlacement = -1;
	public $approvedColor = '#317300';
	public $pendingApprovalColor = '#e8943c';
	public $cancelledColor = '#dd3333';
	public $enableCancelNotificationOnDelete = false;
	public $smtpHostName = '';
	public $smtpUserName = '';
	public $smtpPassword = '';
	public $smtpPortNumber = '';
	public $smtpSecure = 'tls';
	public $smtpAuthenticate = false;
	public $searchFilterFindButtonLabel;
	public $searchFilterSelectButtonLabel;
	public $searchFilterSoldOutLabel;
	public $searchFilterAlternateDateLabel;
	public $searchIncludeAlternateDates = true;
	public $searchIncludeSoldoutDates = true;
	public $thousandSep = ',';
	public $decimalPoint = '.';
	public $googleCalendarAltCronJob = false;
	public $wooCommerceAltCronJob = false;
	public $reminderAltCronJob = true;
	public $searchFilterTheme;
	public $appointmentTabOrder = 0;
	public $appointmentListOrder = 1;
	public $fontSize = 0;
	public $brandName;
	public $disablePlacesPage = false;
	public $disableSalesPage = false;
	public $disableStaffPage = false;
	public $disableSeasonsPage = false;
	public $disableMapPage = false;
	public $autoCompleteWooCommerceOrder = false;
	public $utf8EncodeEmailSubject = true;
	public $addBookingFormWooProduct = true;
	public $cancelWooCommerceOrder = false;
	public $includeCustomFieldsWooCommerce = false;
	public $includeGuestsWooCommerce = false;
	public $newAppointmentZap;
	public $updatedAppointmentZap;
	public $newCustomerZap;
	public $displayAddToCalendarOption = true;
	public $wooCommerceProcessingStatusOnly = false;
	public $accessRoles = array('administrator');
	public $searchTimeslots = array();
	public $twilioAccountSID;
	public $twilioAuthToken;
	public $twilioFromNumber;
	public $twilioCountryCode;
	public $smsContentTemplate;
	public $displayStepsMobileView;
	public function __construct($args){
		if(array_key_exists('translationEngine', $args)){
			$this->translationEngine = (int)$args['translationEngine'];
		}
		$requiresTranslation = !$this->translationEngine ? false : true;
		if(array_key_exists('fontFamilyUrl', $args)){
			$this->fontFamilyUrl = (string)$args['fontFamilyUrl'];
		}
		if(array_key_exists('emailTemplateHeaderImage', $args)){
			$this->emailTemplateHeaderImage = (string)$args['emailTemplateHeaderImage'];
		}
		if(array_key_exists('emailTemplateHeaderTitle', $args)){
			$this->emailTemplateHeaderTitle = (string)$args['emailTemplateHeaderTitle'];
		}
		if(array_key_exists('emailTemplateHeaderBackground', $args)){
			$this->emailTemplateHeaderBackground = (string)$args['emailTemplateHeaderBackground'];
		}
		if(array_key_exists('emailTemplateHeaderColor', $args)){
			$this->emailTemplateHeaderColor = (string)$args['emailTemplateHeaderColor'];
		}
		if(array_key_exists('emailTemplateBodyColor', $args)){
			$this->emailTemplateBodyColor = (string)$args['emailTemplateBodyColor'];
		}
		if(array_key_exists('emailSenderName', $args)){
			$this->emailSenderName = (string)$args['emailSenderName'];
		}
		if(array_key_exists('senderEmail', $args)){
			$this->senderEmail = (string)$args['senderEmail'];
		}
		if(array_key_exists('utf8EncodeEmailSubject', $args)){
			$this->utf8EncodeEmailSubject = (bool)$args['utf8EncodeEmailSubject'];
		}
		if(array_key_exists('adminNotificationEmail', $args)){
			$this->adminNotificationEmail = (string)$args['adminNotificationEmail'];
		}
		if(array_key_exists('autoConfirmOrderAfterPayment', $args)){
			$this->autoConfirmOrderAfterPayment = (bool)$args['autoConfirmOrderAfterPayment'];
		}
		if(array_key_exists('autoApproveBooking', $args)){
			$this->autoApproveBooking = (bool)$args['autoApproveBooking'];
		}
		if(array_key_exists('autoInvoiceNotification', $args)){
			$this->autoInvoiceNotification = (bool)$args['autoInvoiceNotification'];
		}
		if(array_key_exists('autoNotifyAdminNewBooking', $args)){
			$this->autoNotifyAdminNewBooking = (bool)$args['autoNotifyAdminNewBooking'];
		}
		if(array_key_exists('notifyBookingReceivedSuccessfully', $args)){
			$this->notifyBookingReceivedSuccessfully = (bool)$args['notifyBookingReceivedSuccessfully'];
		}
		if(array_key_exists('notifyBookingHasChanged', $args)){
			$this->notifyBookingHasChanged = (bool)$args['notifyBookingHasChanged'];
		}
		if(array_key_exists('outOfStockNotification', $args)){
			$this->outOfStockNotification = (bool)$args['outOfStockNotification'];
		}
		if(array_key_exists('notifyBookingConfirmation', $args)){
			$this->notifyBookingConfirmation = (bool)$args['notifyBookingConfirmation'];
		}
		if(array_key_exists('enableCoupons', $args)){
			$this->enableCoupons = (bool)$args['enableCoupons'];
		}
		if(array_key_exists('refBootstrapStyleSheet', $args)){
			$this->refBootstrapStyleSheet = (bool)$args['refBootstrapStyleSheet'];
		}
		if(array_key_exists('refParsleyJS', $args)){
			$this->refParsleyJS = (bool)$args['refParsleyJS'];
		}
		if(array_key_exists('calendarTheme', $args)){
			$this->calendarTheme = (string)$args['calendarTheme'];
		}
		if(array_key_exists('shorthandDateFormat', $args) && $args['shorthandDateFormat']){
			$this->shorthandDateFormat = (string)$args['shorthandDateFormat'];
		}
		if(array_key_exists('timeFormat', $args)){
			$this->timeFormat = (int)$args['timeFormat'];
		}
		if(array_key_exists('enableUserCancelBooking', $args)){
			$this->enableUserCancelBooking = (bool)$args['enableUserCancelBooking'];
		}
		if(array_key_exists('cancelBookingUrl', $args)){
			$this->cancelBookingUrl = $args['cancelBookingUrl'];
		}
		if(array_key_exists('enableCancelBookingAlert', $args)){
			$this->enableCancelBookingAlert = (bool)$args['enableCancelBookingAlert'];
		}
		if(array_key_exists('customerBookingCancelNotification', $args)){
			$this->customerBookingCancelNotification = (bool)$args['customerBookingCancelNotification'];
		}
		if(array_key_exists('enableCancelNotificationOnDelete', $args)){
			$this->enableCancelNotificationOnDelete = (bool)$args['enableCancelNotificationOnDelete'];
		}
		if(array_key_exists('enableGDPR', $args)){
			$this->enableGDPR = (bool)$args['enableGDPR'];
		}
		if(array_key_exists('debugMode', $args)){
			$this->debugMode = (bool)$args['debugMode'];
		}
		if(array_key_exists('membershipRequired', $args)){
			$this->membershipRequired = (bool)$args['membershipRequired'];
		}
		if(array_key_exists('firstDayOfWeek', $args)){
			$this->firstDayOfWeek = (int)$args['firstDayOfWeek'];
		}
		if(array_key_exists('googleMapsKey', $args) && $args['googleMapsKey']){
			$this->googleMapsKey = (string)$args['googleMapsKey'];
		}
		if(array_key_exists('currency', $args)){
			$this->currency = (string)$args['currency'];
		}
		if(array_key_exists('confirmUrl', $args)){
			$this->confirmUrl = (int)$args['confirmUrl'];
		}
		if(array_key_exists('enableMobileInitialScale', $args)){
			$this->enableMobileInitialScale = (bool)$args['enableMobileInitialScale'];
		}
		if(array_key_exists('enableFeeds', $args)){
			$this->enableFeeds = (bool)$args['enableFeeds'];
		}
		if(array_key_exists('appKey', $args)){
			$this->appKey = (string)$args['appKey'];
		}
		if(array_key_exists('purchaseCode', $args)){
			$this->purchaseCode = (string)$args['purchaseCode'];
		}
		if(array_key_exists('cronJobFeedTimeout', $args)){
			$this->cronJobFeedTimeout = (int)$args['cronJobFeedTimeout'];
		}
		if(array_key_exists('cancellationPolicy', $args)){
			$this->cancellationPolicy = (int)$args['cancellationPolicy'];
		}
		if(array_key_exists('wooCommerceCheckoutUrl', $args)){
			$this->wooCommerceCheckoutUrl = (string)$args['wooCommerceCheckoutUrl'];
		}
		if(array_key_exists('wooCommerceCartUrl', $args)){
			$this->wooCommerceCartUrl = (string)$args['wooCommerceCartUrl'];
		}
		if(array_key_exists('wooCommerceCheckoutMode', $args)){
			$this->wooCommerceCheckoutMode = (int)$args['wooCommerceCheckoutMode'];
		}
		if(array_key_exists('wooCommerceProcessingStatusOnly', $args)){
			$this->wooCommerceProcessingStatusOnly = (bool)$args['wooCommerceProcessingStatusOnly'];
		}
		
		if(array_key_exists('prefix', $args)){
			$this->prefix = (string)$args['prefix'];
		}
		if(array_key_exists('tax', $args)){
			$this->tax = (float)$args['tax'];
		}
		if(array_key_exists('currencySymbolPlacement', $args)){
			$this->currencySymbolPlacement = (int)$args['currencySymbolPlacement'];
		}
		if(array_key_exists('taxMode', $args)){
			$this->taxMode = (int)$args['taxMode'];
		}
		if(array_key_exists('approvedColor', $args)){
			$this->approvedColor = (string)$args['approvedColor'];
		}
		if(array_key_exists('pendingApprovalColor', $args)){
			$this->pendingApprovalColor = (string)$args['pendingApprovalColor'];
		}
		if(array_key_exists('cancelledColor', $args)){
			$this->cancelledColor = (string)$args['cancelledColor'];
		}
		if(array_key_exists('smtpHostName', $args)){
			$this->smtpHostName = (string)$args['smtpHostName'];
		}
		if(array_key_exists('smtpUserName', $args)){
			$this->smtpUserName = (string)$args['smtpUserName'];
		}
		if(array_key_exists('smtpPassword', $args)){
			$this->smtpPassword = (string)$args['smtpPassword'];
		}
		if(array_key_exists('smtpPortNumber', $args)){
			$this->smtpPortNumber = (int)$args['smtpPortNumber'];
		}
		if(array_key_exists('smtpSecure', $args)){
			$this->smtpSecure = (string)$args['smtpSecure'];
		}
		if(array_key_exists('smtpAuthenticate', $args)){
			$this->smtpAuthenticate = (bool)$args['smtpAuthenticate'];
		}
		if(!$requiresTranslation && (array_key_exists('searchFilterFindButtonLabel', $args) &&  $args['searchFilterFindButtonLabel'])){
			$this->searchFilterFindButtonLabel = $args['searchFilterFindButtonLabel'];
		}else{
			$this->searchFilterFindButtonLabel = __('Find', 'calendarista');
		}
		if(!$requiresTranslation && (array_key_exists('searchFilterSelectButtonLabel', $args) && $args['searchFilterSelectButtonLabel'])){
			$this->searchFilterSelectButtonLabel = $args['searchFilterSelectButtonLabel'];
		} else{
			$this->searchFilterSelectButtonLabel = __('Select', 'calendarista');
		}
		if(!$requiresTranslation && (array_key_exists('searchFilterSoldOutLabel', $args) && $args['searchFilterSoldOutLabel'])){
			$this->searchFilterSoldOutLabel = $args['searchFilterSoldOutLabel'];
		} else{
			$this->searchFilterSoldOutLabel = __('Sold out', 'calendarista');
		}
		if(!$requiresTranslation && (array_key_exists('searchFilterAlternateDateLabel', $args) && $args['searchFilterAlternateDateLabel'])){
			$this->searchFilterAlternateDateLabel = $args['searchFilterAlternateDateLabel'];
		} else{
			$this->searchFilterAlternateDateLabel = __('Selected period sold out but other dates are available', 'calendarista');
		}
		if(array_key_exists('searchIncludeAlternateDates', $args)){
			$this->searchIncludeAlternateDates = (bool)$args['searchIncludeAlternateDates'];
		}
		if(array_key_exists('searchIncludeSoldoutDates', $args)){
			$this->searchIncludeSoldoutDates = (bool)$args['searchIncludeSoldoutDates'];
		}
		if(array_key_exists('disablePlacesPage', $args)){
			$this->disablePlacesPage = (bool)$args['disablePlacesPage'];
		}
		if(array_key_exists('disableSalesPage', $args)){
			$this->disableSalesPage = (bool)$args['disableSalesPage'];
		}
		if(array_key_exists('disableStaffPage', $args)){
			$this->disableStaffPage = (bool)$args['disableStaffPage'];
		}
		if(array_key_exists('disableSeasonsPage', $args)){
			$this->disableSeasonsPage = (bool)$args['disableSeasonsPage'];
		}
		if(array_key_exists('disableMapPage', $args)){
			$this->disableMapPage = (bool)$args['disableMapPage'];
		}
		if(array_key_exists('thousandSep', $args)){
			$this->thousandSep = $args['thousandSep'];
		}
		if(array_key_exists('decimalPoint', $args)){
			$this->decimalPoint = $args['decimalPoint'];
		}
		if(array_key_exists('googleCalendarAltCronJob', $args)){
			$this->googleCalendarAltCronJob = (bool)$args['googleCalendarAltCronJob'];
		}
		if(array_key_exists('wooCommerceAltCronJob', $args)){
			$this->wooCommerceAltCronJob = (bool)$args['wooCommerceAltCronJob'];
		}
		if(array_key_exists('reminderAltCronJob', $args)){
			$this->reminderAltCronJob = (bool)$args['reminderAltCronJob'];
		}
		if(array_key_exists('searchFilterTheme', $args)){
			$this->searchFilterTheme = $args['searchFilterTheme'];
		}
		if(array_key_exists('appointmentTabOrder', $args)){
			// 0 = calendarview
			// 1 = listview
			$this->appointmentTabOrder = (int)$args['appointmentTabOrder'];
		}
		if(array_key_exists('appointmentListOrder', $args)){
			//case 0: Start date ASC
			//case 1: Start date DESC
			//case 2: Order date ASC
			//case 3: Order date DESC
			$this->appointmentListOrder = (int)$args['appointmentListOrder'];
		}
		if(array_key_exists('fontSize', $args)){
			$this->fontSize = (double)$args['fontSize'];
		}
		if(array_key_exists('autoCompleteWooCommerceOrder', $args)){
			$this->autoCompleteWooCommerceOrder = (bool)$args['autoCompleteWooCommerceOrder'];
		}
		if(array_key_exists('addBookingFormWooProduct', $args)){
			$this->addBookingFormWooProduct = (bool)$args['addBookingFormWooProduct'];
		}
		if(array_key_exists('cancelWooCommerceOrder', $args)){
			$this->cancelWooCommerceOrder = (bool)$args['cancelWooCommerceOrder'];
		}
		if(array_key_exists('includeCustomFieldsWooCommerce', $args)){
			$this->includeCustomFieldsWooCommerce = (bool)$args['includeCustomFieldsWooCommerce'];
		}
		if(array_key_exists('includeGuestsWooCommerce', $args)){
			$this->includeGuestsWooCommerce = (bool)$args['includeGuestsWooCommerce'];
		}
		if(array_key_exists('brandName', $args)){
			$this->brandName = $args['brandName'];
		}
		if(array_key_exists('newAppointmentZap', $args)){
			$this->newAppointmentZap = $args['newAppointmentZap'];
		}
		if(array_key_exists('updatedAppointmentZap', $args)){
			$this->updatedAppointmentZap = $args['updatedAppointmentZap'];
		}
		if(array_key_exists('newCustomerZap', $args)){
			$this->newCustomerZap = $args['newCustomerZap'];
		}
		if(array_key_exists('displayAddToCalendarOption', $args)){
			$this->displayAddToCalendarOption = (bool)$args['displayAddToCalendarOption'];
		}
		if(array_key_exists('accessRoles', $args)){
			$this->accessRoles = (array)$args['accessRoles'];
		}
		if(array_key_exists('searchTimeslots', $args)){
			$this->searchTimeslots = $args['searchTimeslots'];
		}else{
			$this->searchTimeslots = $this->getDefaultTimeslots();
		}
		if(array_key_exists('twilioAccountSID', $args)){
			$this->twilioAccountSID = $args['twilioAccountSID'];
		}
		if(array_key_exists('twilioAuthToken', $args)){
			$this->twilioAuthToken = $args['twilioAuthToken'];
		}
		if(array_key_exists('twilioFromNumber', $args)){
			$this->twilioFromNumber = $args['twilioFromNumber'];
		}
		if(array_key_exists('twilioCountryCode', $args)){
			$this->twilioCountryCode = $args['twilioCountryCode'];
		}
		if(array_key_exists('smsContentTemplate', $args)){
			$this->smsContentTemplate = $args['smsContentTemplate'];
		}
		if(!$this->smsContentTemplate){
			$this->smsContentTemplate = <<<EOT
Dear {{customer_name}}, this is to confirm your booking  for a {{service_name}} on {{start_datetime}} 
{{#if_has_end_date}} 
	through {{end_datetime}} 
{{/if_has_end_date}} 
{{#if_has_return_trip}} 
	and a return trip on {{end_datetime}}.
{{/if_has_return_trip}}
{{#if_has_group_booking}}
Your booking includes {{booked_seats_count}} seats.
{{/if_has_group_booking}}
{{#if_has_dynamic_fields}}
{{{dynamic_fields}}}
{{/if_has_dynamic_fields}}
EOT;
		}
		if(array_key_exists('displayStepsMobileView', $args)){
			$this->displayStepsMobileView = (bool)$args['displayStepsMobileView'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if(!$this->senderEmail)
		{
			$this->senderEmail = get_site_option( 'admin_email' );
		}
		if(!$this->adminNotificationEmail)
		{
			$this->adminNotificationEmail = get_site_option( 'admin_email' );
		}
	}
	public function getDefaultTimeslots(){
		$timeslots = Calendarista_AutogenTimeslotsController::createTimeSlots(
			0/*$hours*/
			, 30/*$minutes*/
			, 0/*$hourStartInterval*/
			, 0/*$minuteStartInterval*/
			, 23/*$hourEnd*/
			, 59/*$minuteEnd*/
			, true
			, 'H:i'/*timeFormat*/
		);
		return $timeslots;
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'emailSenderName'=>$this->emailSenderName
			, 'senderEmail'=>$this->senderEmail
			, 'utf8EncodeEmailSubject'=>$this->utf8EncodeEmailSubject
			, 'adminNotificationEmail'=>$this->adminNotificationEmail
			, 'emailTemplateHeaderImage'=>$this->emailTemplateHeaderImage
			, 'emailTemplateHeaderTitle'=>$this->emailTemplateHeaderTitle
			, 'emailTemplateHeaderBackground'=>$this->emailTemplateHeaderBackground
			, 'emailTemplateHeaderColor'=>$this->emailTemplateHeaderColor
			, 'emailTemplateBodyColor'=>$this->emailTemplateBodyColor
			, 'autoConfirmOrderAfterPayment'=>$this->autoConfirmOrderAfterPayment
			, 'autoApproveBooking'=>$this->autoApproveBooking
			, 'autoInvoiceNotification'=>$this->autoInvoiceNotification
			, 'autoNotifyAdminNewBooking'=>$this->autoNotifyAdminNewBooking
			, 'notifyBookingReceivedSuccessfully'=>$this->notifyBookingReceivedSuccessfully
			, 'notifyBookingHasChanged'=>$this->notifyBookingHasChanged
			, 'outOfStockNotification'=>$this->outOfStockNotification
			, 'notifyBookingConfirmation'=>$this->notifyBookingConfirmation
			, 'enableCoupons'=>$this->enableCoupons
			, 'refBootstrapStyleSheet'=>$this->refBootstrapStyleSheet
			, 'refParsleyJS'=>$this->refParsleyJS
			, 'calendarTheme'=>$this->calendarTheme
			, 'shorthandDateFormat'=>$this->shorthandDateFormat
			, 'timeFormat'=>$this->timeFormat
			, 'enableUserCancelBooking'=>$this->enableUserCancelBooking
			, 'cancelBookingUrl'=>$this->cancelBookingUrl
			, 'enableCancelBookingAlert'=>$this->enableCancelBookingAlert
			, 'customerBookingCancelNotification'=>$this->customerBookingCancelNotification
			, 'enableCancelNotificationOnDelete'=>$this->enableCancelNotificationOnDelete
			, 'enableGDPR'=>$this->enableGDPR
			, 'debugMode'=>$this->debugMode
			, 'membershipRequired'=>$this->membershipRequired
			, 'firstDayOfWeek'=>$this->firstDayOfWeek
			, 'googleMapsKey'=>$this->googleMapsKey
			, 'currency'=>$this->currency
			, 'confirmUrl'=>$this->confirmUrl
			, 'fontFamilyUrl'=>$this->fontFamilyUrl
			, 'enableMobileInitialScale'=>$this->enableMobileInitialScale
			, 'enableFeeds'=>$this->enableFeeds
			, 'appKey'=>$this->appKey
			, 'purchaseCode'=>$this->purchaseCode
			, 'cronJobFeedTimeout'=>$this->cronJobFeedTimeout
			, 'translationEngine'=>$this->translationEngine
			, 'cancellationPolicy'=>$this->cancellationPolicy
			, 'wooCommerceCheckoutUrl'=>$this->wooCommerceCheckoutUrl
			, 'wooCommerceCartUrl'=>$this->wooCommerceCartUrl
			, 'wooCommerceCheckoutMode'=>$this->wooCommerceCheckoutMode
			, 'wooCommerceProcessingStatusOnly'=>$this->wooCommerceProcessingStatusOnly
			, 'prefix'=>$this->prefix
			, 'tax'=>$this->tax
			, 'taxMode'=>$this->taxMode
			, 'currencySymbolPlacement'=>$this->currencySymbolPlacement
			, 'approvedColor'=>$this->approvedColor
			, 'pendingApprovalColor'=>$this->pendingApprovalColor
			, 'cancelledColor'=>$this->cancelledColor
			, 'smtpHostName'=>$this->smtpHostName
			, 'smtpUserName'=>$this->smtpUserName
			, 'smtpPassword'=>$this->smtpPassword
			, 'smtpPortNumber'=>$this->smtpPortNumber
			, 'smtpSecure'=>$this->smtpSecure
			, 'smtpAuthenticate'=>$this->smtpAuthenticate
			, 'searchFilterFindButtonLabel'=>$this->searchFilterFindButtonLabel
			, 'searchFilterSelectButtonLabel'=>$this->searchFilterSelectButtonLabel
			, 'searchFilterSoldOutLabel'=>$this->searchFilterSoldOutLabel
			, 'searchFilterAlternateDateLabel'=>$this->searchFilterAlternateDateLabel
			, 'searchIncludeAlternateDates'=>$this->searchIncludeAlternateDates
			, 'searchIncludeSoldoutDates'=>$this->searchIncludeSoldoutDates
			, 'thousandSep'=>$this->thousandSep
			, 'decimalPoint'=>$this->decimalPoint
			, 'googleCalendarAltCronJob'=>$this->googleCalendarAltCronJob
			, 'wooCommerceAltCronJob'=>$this->wooCommerceAltCronJob
			, 'reminderAltCronJob'=>$this->reminderAltCronJob
			, 'searchFilterTheme'=>$this->searchFilterTheme
			, 'appointmentTabOrder'=>$this->appointmentTabOrder
			, 'appointmentListOrder'=>$this->appointmentListOrder
			, 'fontSize'=>$this->fontSize
			, 'autoCompleteWooCommerceOrder'=>$this->autoCompleteWooCommerceOrder
			, 'addBookingFormWooProduct'=>$this->addBookingFormWooProduct
			, 'cancelWooCommerceOrder'=>$this->cancelWooCommerceOrder
			, 'includeCustomFieldsWooCommerce'=>$this->includeCustomFieldsWooCommerce
			, 'includeGuestsWooCommerce'=>$this->includeGuestsWooCommerce
			, 'brandName'=>$this->brandName
			, 'newAppointmentZap'=>$this->newAppointmentZap
			, 'updatedAppointmentZap'=>$this->updatedAppointmentZap
			, 'newCustomerZap'=>$this->newCustomerZap
			, 'disablePlacesPage'=>$this->disablePlacesPage
			, 'disableSalesPage'=>$this->disableSalesPage
			, 'disableStaffPage'=>$this->disableStaffPage
			, 'disableSeasonsPage'=>$this->disableSeasonsPage
			, 'disableMapPage'=>$this->disableMapPage
			, 'displayAddToCalendarOption'=>$this->displayAddToCalendarOption
			, 'accessRoles'=>$this->accessRoles
			, 'searchTimeslots'=>$this->searchTimeslots
			, 'twilioAccountSID'=>$this->twilioAccountSID
			, 'twilioAuthToken'=>$this->twilioAuthToken
			, 'twilioFromNumber'=>$this->twilioFromNumber
			, 'twilioCountryCode'=>$this->twilioCountryCode
			, 'smsContentTemplate'=>$this->smsContentTemplate
			, 'displayStepsMobileView'=>$this->displayStepsMobileView
		);
	}
	public function hasZap(){
		return $this->newAppointmentZap || $this->updatedAppointmentZap || $this->newCustomerZap;
	}
	public function hasTwilio(){
		return $this->twilioAccountSID && $this->twilioAuthToken;
	}
	public function generateAppKey(){
		if(!$this->appKey){
			$this->appKey = uniqid('CA');
		}
	}
}
?>