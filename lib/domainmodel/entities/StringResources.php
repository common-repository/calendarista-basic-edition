<?php
class Calendarista_StringResources extends Calendarista_EntityBase{
	public $id = -1;
	public $projectId = -1;
	public $resources;
	public function __construct($args, $id = null, $projectId = null){
		if($id !== null){
			$this->id = (int)$id;
		}
		if($projectId !== null){
			$this->projectId = (int)$projectId;
		}
		$this->resources = $this->getResources();
		foreach($this->resources as $key1=>$value1){
			foreach($args as $key2=>$value2){
				if($key1 === $key2){
					$this->resources[$key1] = stripslashes($value2);
				}
			}
		}
		foreach($this->resources as $key=>$value){
			$key2 = sprintf('%s_%d', $key, $this->projectId);
			$this->resources[$key] = Calendarista_TranslationHelper::t($key2, $value);
		}
	}
	private function getResources(){
		return array(
			'WIZARD_STEP_1'=>__('Date and time', 'calendarista')
			, 'WIZARD_STEP_2'=>__('Route', 'calendarista')
			, 'WIZARD_STEP_3'=>__('Extras','calendarista')
			, 'WIZARD_STEP_4'=>__('Details','calendarista')
			, 'WIZARD_STEP_5'=>__('Check out','calendarista')
			, 'AJAX_SPINNER'=>__('Loading...','calendarista')
			, 'CALENDAR_LOADING'=>__('Loading...', 'calendarista')
			, 'NEXT_BUTTON'=>__('Next', 'calendarista')
			, 'PREV_BUTTON'=>__('Prev', 'calendarista')
			, 'CONCLUDE_BOOKING_BUTTON'=>__('Book now', 'calendarista')
			, 'SELECT_PAYMENT_MODE_ERROR_MESSAGE'=>__('You must select a payment mode', 'calendarista')
			, 'PAYMENT_WOOCOMMERCE_SELECTION_TITLE'=>__('Secure payment at checkout', 'calendarista')
			, 'ATTENDEES'=>__('Attendees', 'calendarista')
			, 'ORDER_ID_REF'=> __('For your reference, your order id is #%s', 'calendarista')
			, 'SUBTOTAL'=>__('Subtotal','calendarista')
			, 'DEPOSIT'=>__('Deposit','calendarista')
			, 'DISCOUNT'=>__('Discount','calendarista')
			, 'TAX'=>__('Tax', 'calendarista')
			, 'TOTAL'=>__('Total amount','calendarista')
			, 'BOOKING_SUMMARY_LABEL'=>__('Booking summary', 'calendarista')
			, 'BOOKING_NIGHT_SINGULAR_LABEL'=>__('%d night stay', 'calendarista')
			, 'BOOKING_NIGHT_PLURAL_LABEL'=>__('%d nights stay', 'calendarista')
			, 'BOOKING_FOR'=>__('Booking for', 'calendarista')
			, 'BOOKING_BASE_COST_LABEL'=>__('Single day (%s)', 'calendarista')
			, 'BOOKING_BASE_COST_SINGLE_SLOT_LABEL'=>__('Single slot %s', 'calendarista')
			, 'BOOKING_BASE_COST_DATE_RANGE_LABEL'=>__('Appointment period %s - %s', 'calendarista')
			, 'BOOKING_BASE_COST_ROUND_TRIP_LABEL'=>__('Round trip on %s with return on %s', 'calendarista')
			, 'BOOKING_BASE_COST_ONEWAY_TRIP_LABEL'=>__('Oneway trip %s', 'calendarista')
			, 'BOOKING_BASE_COST_X_SEATS_LABEL'=>__('(price x %d seats)', 'calendarista')
			, 'BOOKING_EXTEND_NEXT_DAY_LABEL'=>__('Note: booking extends the next day', 'calendarista')
			, 'BOOKING_TOTAL_HOURS_MINUTES_LABEL'=>__('Total time in booking', 'calendarista')
			, 'BOOKING_HOURS_LABEL'=>__('%s hr', 'calendarista')
			, 'BOOKING_MINUTES_LABEL'=>__('%s min', 'calendarista')
			, 'DAYS_BOOKED'=>__('Days Booked', 'calendarista')
			, 'EXTRAS'=>__('Extras', 'calendarista')
			, 'COUPON'=>__('Coupon code', 'calendarista')
			, 'BOOKING_START_DATE_LABEL'=>__('Start date', 'calendarista')
			, 'BOOKING_END_DATE_LABEL'=>__('End date', 'calendarista')
			, 'BOOKING_DEPARTING_DATE_LABEL'=>__('Departing', 'calendarista')
			, 'BOOKING_RETURN_DATE_LABEL'=>__('Returning', 'calendarista')
			, 'BOOKING_RETURN_IS_OPTIONAL'=>__('**Return date is optional!', 'calendarista')
			, 'BOOKING_DATE_FIELD_PLACEHOLDER'=>__('Select a date', 'calendarista')
			, 'BOOKING_SELECT_START_TIME'=>__('Start time', 'calendarista')
			, 'BOOKING_SELECT_END_TIME'=>__('End time', 'calendarista')
			, 'CALENDAR_LEGEND_AVAILABLE'=>__('Available', 'calendarista')
			, 'CALENDAR_LEGEND_UNAVAILABLE'=>__('Unavailable', 'calendarista')
			, 'CALENDAR_LEGEND_HALF_DAY'=>__('Changeover', 'calendarista')
			, 'CALENDAR_LEGEND_RANGE'=>__('Range', 'calendarista')
			, 'CALENDAR_LEGEND_SELECTED_DAY'=>__('Selected day', 'calendarista')
			, 'CALENDAR_LEGEND_CURRENT_DAY'=>__('Current day', 'calendarista')
			, 'CALENDAR_LEGEND_RANGE_UNAVAILABLE'=>__('Cannot be part of a range', 'calendarista')
			, 'CALENDAR_CLEAR_DATE'=>__('Clear', 'calendarista')
			, 'BOOKING_DATE_MIN_REQUIRED'=>__('A minimum of %s day(s) need to be selected.', 'calendarista')
			, 'BOOKING_DATE_MAX_LIMITED'=>__('A maximum of %s day(s) can be selected.', 'calendarista')
			, 'BOOKING_DATE_RANGE_ERROR'=>__('The date range selected is invalid and has been reset.', 'calendarista')
			, 'BOOKING_TIMESLOTS_ERROR'=>__('Time slots are unavailable for the selected day.', 'calendarista')
			, 'BOOKING_TIMESLOTS_RETURN_ERROR'=>__('Time slots are unavailable for the return trip.', 'calendarista')
			, 'BOOKING_MIN_TIME_ERROR'=>__('Booking requires a minimum of %s', 'calendarista')
			, 'BOOKING_MAX_TIME_ERROR'=>__('Booking allows a maximum of %s', 'calendarista')
			, 'HOUR'=>__('Hrs', 'calendarista')
			, 'MINUTE'=>__('Mins', 'calendarista')
			, 'BOOKING_MULTI_SELECT_TIMESLOT_NOTICE'=>__('Hold down CTRL + Click for multi selection.', 'calendarista')
			, 'BOOKING_MAX_TIMESLOT_NOTICE'=>__('Maximum of %d slots allowed.', 'calendarista')
			, 'SELECT_DAY_AND_TIMESLOT'=>__('Select a day and a timeslot.', 'calendarista')
			, 'SELECTED_DAY_TIMESLOT_UNAVAILABLE'=>__('No timeslot available for selected day.', 'calendarista')
			, 'BOOKING_DATE_SUMMARY'=>__('Booking on %s', 'calendarista')
			, 'BOOKING_DATE_RANGE_SUMMARY'=>__('Booking on %s through %s', 'calendarista')
			, 'BOOKING_DATETIME_RANGE_SUMMARY'=>__('Booking on %s - %s', 'calendarista')
			, 'BOOKING_DATE_RANGE_PARTIAL_CHARGE_SUMMARY'=>__('Check-in on %s and check-out on %s', 'calendarista')
			, 'BOOKING_ROUND_TRIP_DATE_SUMMARY'=>__('Booking a round trip on %s with return on %s', 'calendarista')
			, 'BOOKING_ONEWAY_TRIP_DATE_SUMMARY'=>__('Booking a oneway trip on %s', 'calendarista')
			, 'BOOKING_MULTI_DATE_SUMMARY'=>__('Booking summary', 'calendarista')
			, 'BOOKING_DEPOSIT_LABEL'=>__('deposit, required now', 'calendarista')
			, 'BOOKING_PAY_NOW_FULL_AMOUNT_LABEL'=>__('Full amount', 'calendarista')
			, 'BOOKING_PACKAGE_SUMMARY'=>__('Booking for the %s package that begins on %s and ends on %s', 'calendarista')
			, 'BOOKING_RETURN_COST'=>__('Return cost', 'calendarista')
			, 'BOOKING_DISTANCE_MINIMUM_CHARGE'=>__('We have a minimum charge of %s, if booking a distance less than %s', 'calendarista')
			, 'BOOKING_SEATS_SUMMARY'=>__('for %d seat', 'calendarista')
			, 'BOOKING_SERVICE_SELECTION_LABEL'=>__('Services', 'calendarista')
			, 'BOOKING_AVAILABILITY_SELECTION_LABEL'=>__('Availabilities', 'calendarista')
			, 'BOOKING_OPTIONAL_MULTIPLY_BY_DAY_LABEL'=>__('%s (price x %d day/s)', 'calendarista')
			, 'BOOKING_OPTIONAL_MULTIPLY_BY_SLOTS_LABEL'=>__('%s (price x %d slot/s)', 'calendarista')
			, 'BOOKING_OPTIONAL_MULTIPLY_BY_SEAT_LABEL'=>__('%s (price x %d seat/s)', 'calendarista')
			, 'BOOKING_OPTIONAL_QUANTITY_LABEL'=>__('Qty %s', 'calendarista')
			, 'BOOKING_PACKAGE_LABEL'=>__('Available packages', 'calendarista')
			, 'BOOKING_PACKAGE_DEFAULT_VALUE'=>__('Select a package', 'calendarista')
			, 'BOOKING_PACKAGE_EXHAUSTED'=>__('There are no more packages to book, please check back with us again at a later time.', 'calendarista')
			, 'BOOKING_REDEEM_COUPON'=>__('Redeem coupon', 'calendarista')
			, 'BOOKING_RESET_COUPON'=>__('Reset', 'calendarista')
			, 'BOOKING_COUPON_ERROR'=>__('Coupon code is invalid', 'calendarista')
			, 'BOOKING_RESET_TIME'=>__('Reset', 'calendarista')
			, 'BOOKING_THANKYOU'=>__('Thank you for booking with us', 'calendarista')
			, 'BOOKING_CREATED'=>__('Please check your email for further reference.', 'calendarista')
			, 'BOOKING_CREATED_INVOICE_NUMBER'=>__('The invoice number is %s. ', 'calendarista')
			, 'BOOKING_PAYMENT_FAILED'=>__('Please check your email for further reference.', 'calendarista')
			, 'OPTIONAL_QUANTITY_SUMMARY'=>__('in limited quantity, only %d left', 'calendarista')
			, 'OPTIONAL_QUANTITY_EXHAUSTED'=>__('Out of stock', 'calendarista')
			, 'OPTIONAL_LISTBOX_NOTE'=>__('Hold CTRL key + Click for multiple selections', 'calendarista')
			, 'PAYMENT_ITEM_NAME'=>__('%s - %s', 'calendarista')
			, 'PAYMENT_METHOD_PAYPAL_LABEL'=>__('Payment via PayPal', 'calendarista')
			, 'PAYMENT_METHOD_CREDITCARD_LABEL'=>__('Payment with Credit Card', 'calendarista')
			, 'PAYMENT_METHOD_BANK_OR_LOCAL_LABEL'=>__('Payment via bank wire transfer or locally in person', 'calendarista')
			, 'PAYMENT_METHOD_BANK_OR_LOCAL_MESSAGE'=>__('We will send you an email with payment instructions', 'calendarista')
			, 'PAYMENT_OPTIONALLY_DEPOSIT_LABEL'=>__('Pay just the deposit: ', 'calendarista')
			, 'PAYMENT_OPTIONALLY_FULL_AMOUNT_LABEL'=>__('Pay full amount:', 'calendarista')
			, 'PAYMENT_FULL_AMOUNT_DISCOUNT_LABEL'=>__('(Save %s by paying now in full)', 'calendarista')
			, 'PAYMENT_FULL_AMOUNT_PAID_LABEL'=>__('Paid full amount', 'calendarista')
			, 'CREDIT_CARD_NUMBER_LABEL'=>__('Credit card number', 'calendarista')
			, 'CREDIT_CARD_EXPIRY_DATE_LABEL'=>__('Expiry date', 'calendarista')
			, 'CREDIT_CARD_SECURITYCODE_LABEL'=>__('Security code', 'calendarista')
			, 'CREDIT_CARD_NUMBER_PLACEHOLDER'=>__('Card number', 'calendarista')
			, 'CREDIT_CARD_FULLNAME_PLACEHOLDER'=>__('Full name', 'calendarista')
			, 'CREDIT_CARD_MONTH_PLACEHOLDER'=>__('MM', 'calendarista')
			, 'CREDIT_CARD_YEAR_PLACEHOLDER'=>__('YY', 'calendarista')
			, 'CREDIT_CARD_CVC_PLACEHOLDER'=>__('CVC', 'calendarista')
			, 'CREDIT_CARD_LABEL'=>__('Credit or debit card', 'calendarista')
			, 'TERMS_AND_CONDITIONS'=>__('You must accept our terms and conditions to proceed.', 'calendarista')
			, 'NO_AVAILABILITY_FOUND'=>__('No bookings available', 'calendarista')
			, 'WARNING'=>__('Warning!', 'calendarista')
			, 'NOTE'=>__('Note!', 'calendarista')
			, 'AVAILABLE_DAYS'=>__('Available days', 'calendarista')
			, 'TIMESLOT_SEATS_LABEL'=>__('%s - (%d left)', 'calendarista')
			, 'SEATS_LABEL'=>__('Seats', 'calendarista')
			, 'SEATS_CUSTOMER_NAME_LABEL'=>__('Name of each person coming to the event', 'calendarista')
			, 'SEATS_CUSTOMER_NAME_PLACEHOLDER'=>__('Full name', 'calendarista')
			, 'SEATS_CUSTOMER_NAME_SUMMARY'=>__('Guest #%s', 'calendarista')
			, 'SEATS_GUEST_FIELD'=>__('Guest #%s: %s', 'calendarista')
			, 'GUEST_REQUIRED_INFO'=>__('Guest #%d required info', 'calendarista')
			, 'SEATS_REMAINING'=>__('%d seats left.', 'calendarista')
			, 'SEATS_EXHAUSTED'=>__('There are no more seats left. Try another day', 'calendarista')
			, 'COUPON_MINIMUM_AMOUNT_ERROR'=>__('Coupon is valid only on bookings that exceed %s.', 'calendarista')
			, 'COUPON_INVALID_ERROR'=>__('Coupon is not valid. No discount applied.', 'calendarista')
			, 'APPLIED'=>__('applied', 'calendarista')
			, 'BALANCE_LABEL'=>__('Balance', 'calendarista')
			, 'BALANCE'=>__('on arrival', 'calendarista')
			, 'TAX_LABEL'=>__('Tax', 'calendarista')
			, 'MAP_LOADING'=>__('Loading map', 'calendarista')
			, 'MAP_DEPARTURE_LABEL'=>__('Departure', 'calendarista')
			, 'MAP_DEPARTURE_PLACEHOLDER'=>__('Address', 'calendarista')
			, 'MAP_DESTINATION_LABEL'=>__('Destination', 'calendarista')
			, 'MAP_DESTINATION_PLACEHOLDER'=>__('Address', 'calendarista')
			, 'MAP_DEPARTURE_DEFAULT_LIST_ITEM'=>__('Select a departure', 'calendarista')
			, 'MAP_DESTINATION_DEFAULT_LIST_ITEM'=>__('Select a destination', 'calendarista')
			, 'MAP_WAYPOINT_LABEL'=>__('Waypoint', 'calendarista')
			, 'MAP_ADD_WAYPOINT'=>__('+ Waypoint', 'calendarista')
			, 'MAP_HINT'=>__('Note: You can also right click on map to set directions', 'calendarista')
			, 'MAP_DISTANCE_LABEL'=>__('Total distance', 'calendarista')
			, 'MAP_DURATION_LABEL'=>__('Estimated duration', 'calendarista')
			, 'TIME_UNIT_DAY_LABEL'=>__('day(s)', 'calendarista') 
			, 'TIME_UNIT_HOUR_LABEL'=>__('hr', 'calendarista')  
			, 'TIME_UNIT_MINUTE_LABEL'=>__('min', 'calendarista')  
		    , 'TIME_UNIT_SECOND_LABEL'=>__('sec', 'calendarista')  
			, 'VENUE_LOCATION'=>__('The venue is located at %s', 'calendarista')
			, 'DIRECTION_EMPTY_ERROR'=>__('Provide both a departure and arrival address above to view direction data', 'calendarista')
			, 'NO_DIRECTION_ERROR'=>__('No routes available from/to the selected location', 'calendarista')
			, 'REGISTRATION_NEW_CUSTOMER'=>__('I am a new customer', 'calendarista')
			, 'REGISTRATION_RETURN_CUSTOMER'=>__('I am a returning customer', 'calendarista')
			, 'REGISTRATION_EMAIL_EXISTS'=>__('You appear to have registered before. Lost your password?', 'calendarista')
			, 'REGISTER_NAME_LABEL'=>__('Full name', 'calendarista')
			, 'REGISTER_EMAIL_LABEL'=>__('Email', 'calendarista')
			, 'REGISTER_PASSWORD_LABEL'=>__('Password', 'calendarista')
			, 'REGISTER_REPEAT_PASSWORD_LABEL'=>__('Repeat password', 'calendarista')
			, 'REGISTER_PASSWORD_ERROR'=>__('Ensure two uppercase letters, two digits and a minimum of eight characters.', 'calendarista')
			, 'REGISTER_ADDRESS1_LABEL'=>__('Billing Address 1', 'calendarista')
			, 'REGISTER_ADDRESS2_LABEL'=>__('Billing Address 2', 'calendarista')
			, 'REGISTER_CITY_LABEL'=>__('City', 'calendarista')
			, 'REGISTER_STATE_LABEL'=>__('State', 'calendarista')
			, 'REGISTER_ZIPCODE_LABEL'=>__('Zip code', 'calendarista')
			, 'REGISTER_COUNTRY_LABEL'=>__('Country', 'calendarista')
			, 'LOGIN_EMAIL_LABEL'=>__('Email', 'calendarista')
			, 'LOGIN_PASSWORD_LABEL'=>__('Password', 'calendarista')
			, 'LOGIN_FORGOT_PASSWORD_LABEL'=>__('Lost your password?', 'calendarista')
			, 'LOGIN_INCORRECT_CREDENTIALS'=>__('Incorrect email or password. Try again.', 'calendarista')
			, 'PHONE_NUMBER_INCORRECT'=>__('The phone number is incorrect', 'calendarista')
			, 'GDPR_REQUEST_SUCCESS'=>__('Your request for data deletion has been sent successfully and your appointment history will be deleted soon.', 'calendarista')
			, 'ADD_APPOINTMENT_TO_CALENDAR'=>__('Add the appointment to your calendar', 'calendarista')
			, 'WOOCOMMERCE_DEPOSIT_LABEL'=>__('This is a deposit. The balance amount required upon arrival is %s', 'calendarista')
			, 'ICAL'=>__('iCal', 'calendarista')
			, 'OUTLOOK'=>__('Outlook', 'calendarista')
			, 'GOOGLE'=>__('Google Calendar', 'calendarista')
			, 'SEARCH_BUTTON_LABEL'=>__('Find', 'calendarista')
			, 'SEARCH_BUTTON_SELECT'=>__('Select', 'calendarista')
			, 'RACE_CONDITION'=>__('A race condition has occurred. We are out of stock. Someone else booked this appointment before you did.', 'calendarista')
			, 'RACE_CONDITION_WOOCOMMERCE'=>__('Sorry, a race condition has occurred. We are out of stock. One or more appointments have been removed from cart. Please refresh the page or %s', 'calendarista')
			, 'GUESTS_NONE'=>__('None', 'calendarista')
			, 'DEALS_MORNING'=>__('Morning deals', 'calendarista')
			, 'DEALS_AFTERNOON'=>__('Afternoon deals', 'calendarista')
			, 'DEALS_EVENING'=>__('Evening deals', 'calendarista')
			, 'DEALS_NIGHT'=>__('Night deals', 'calendarista')
			, 'DEALS_AT'=>__('Deal times at', 'calendarista')
			, 'DEALS_TIMESLOT_AVAILABLE'=>__('Available', 'calendarista')
			, 'DEALS_TIMESLOT_SEAT_REMAINING'=>__('%d Left', 'calendarista')
			, 'DEALS_SOLDOUT'=>__('SOLD', 'calendarista')
			, 'DEALS_AVERAGE_COST'=>__('from %s', 'calendarista')
			, 'DEALS_TIMESLOT_ERROR'=>__('Please select a time slot', 'calendarista')
			, 'CUSTOM_CHARGE_LABEL'=>__('Custom charge', 'calendarista')
			, 'BOOK_ANOTHER_AVAILABILITY'=>__('Book more availabilities, for the selected period? Check below.', 'calendarista')
			, 'MULTIPLE_AVAILABILITY_LIST'=>__('The booking includes %s', 'calendarista')
			, 'CANCEL_APPOINTMENT_CONFIRM'=>__('You are about to cancel an appointment due on %s. Would you like to proceed?', 'calendarista')
			, 'CANCEL_APPOINTMENT_SUCCESS'=>__('Your appointment has been cancelled successfully. You will receive an email shortly with confirmation. Thank you for booking with us.', 'calendarista')
			, 'REPEAT_FOR_LABEL'=>__('Repeat for', 'calendarista')
			, 'REPEAT_DAY_LABEL'=>__('Day', 'calendarista')
			, 'REPEAT_WEEK_LABEL'=>__('Weekly', 'calendarista')
			, 'REPEAT_MONTH_LABEL'=>__('Monthly', 'calendarista')
			, 'REPEAT_DAY_LABEL'=>__('Yearly', 'calendarista')
			, 'REPEAT_REGISTER_NAME_LABEL'=>__('Your appointment will repeat on the following dates', 'calendarista')
			, 'REPEAT_THIS_APPOINTMENT_LABEL'=>__('Repeat this appointment', 'calendarista')
			, 'DEPOSIT_METHOD_REQUIRED_ERROR'=>__('A selection is required', 'calendarista')
		);
	}
	
	public function updateResources(){
		foreach($this->resources as $key=>$value){
			Calendarista_TranslationHelper::register(sprintf('%s_%d', $key, $this->projectId), $value);
		}
	}
	
	public function deleteResources(){
		foreach($this->resources as $key=>$value){
			Calendarista_TranslationHelper::unregister(sprintf('%s_%d', $key, $this->projectId));
		}
	}

	public function toArray(){
		foreach($this->resources as $key=>$value){
			$this->resources[$key] = stripslashes($value);
		}
		return $this->resources;
	}
	
	public static function getTimeUnitLabels($resources){
		return array(
			'day'=>$resources['TIME_UNIT_DAY_LABEL'], 
			'hour'=>$resources['TIME_UNIT_HOUR_LABEL'], 
			'minute'=>$resources['TIME_UNIT_MINUTE_LABEL'],
		    'second'=>$resources['TIME_UNIT_SECOND_LABEL']);
	}
}
?>