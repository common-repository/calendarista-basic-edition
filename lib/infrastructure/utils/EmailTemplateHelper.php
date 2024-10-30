<?php
class Calendarista_EmailTemplateHelper{
	private static function readText($path){
		$content = '';
		if ($handle = fopen($path, 'rb')) {
			$len = filesize($path);
			if ($len > 0){
				$content = fread($handle, $len);
			}
			fclose($handle);
		}
		return trim($content);
	}
	public static function getContent($fileName){
		$path = sprintf('%s/assets/tmpl/%s.txt', CALENDARISTA_ROOT_FOLDER, $fileName);
		return self::readText($path);
	}
	public static function getTemplates(){
		return array(
			array(
				'emailType'=>Calendarista_EmailType::MASTER_TEMPLATE, 
				'name'=>'master_template', 
				'subject'=>__('Master template', 'calendarista'), 
				'content'=>self::getContent('master_template')
			)
			, array(
				'emailType'=>Calendarista_EmailType::NEW_BOOKING_RECEIVED, 
				'name'=>'new_booking_received', 
				'subject'=>__('New booking received', 'calendarista'), 
				'content'=>self::getContent('new_booking_received')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_RECEIVED_SUCCESS, 
				'name'=>'booking_received_successfully', 
				'subject'=>__('Booking received successfully', 'calendarista'), 
				'content'=>self::getContent('booking_received_success')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_CONFIRMATION, 
				'name'=>'booking_confirmation', 
				'subject'=>__('Booking confirmation', 'calendarista'), 
				'content'=>self::getContent('booking_confirmation')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED, 
				'name'=>'booking_cancelled', 
				'subject'=>__('Booking cancelled', 'calendarista'), 
				'content'=>self::getContent('booking_cancelled')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_REMINDER, 
				'name'=>'booking_reminder', 
				'subject'=>__('Booking reminder', 'calendarista'), 
				'content'=>self::getContent('booking_reminder')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_PAYMENT_RECEVIED, 
				'name'=>'booking_payment_received', 
				'subject'=>__('Booking payment received', 'calendarista'), 
				'content'=>self::getContent('booking_payment_received')
			)
			, array(
				'emailType'=>Calendarista_EmailType::COUPON, 
				'name'=>'discount_coupon_awarded', 
				'subject'=>__('Discount coupon awarded', 'calendarista'), 
				'content'=>self::getContent('coupon')
			)
			, array(
				'emailType'=>Calendarista_EmailType::PAYMENT_REQUIRED, 
				'name'=>'payment_required', 
				'subject'=>__('Payment required', 'calendarista'), 
				'content'=>self::getContent('payment_required')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_HAS_CHANGED, 
				'name'=>'booking_has_changed', 
				'subject'=>__('Booking has changed', 'calendarista'), 
				'content'=>self::getContent('booking_has_changed')
			)
			, array(
				'emailType'=>Calendarista_EmailType::GDPR, 'name'=>'gdpr', 
				'subject'=>__('General data protection regulation', 'calendarista'), 
				'content'=>self::getContent('gdpr')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED_ALERT, 
				'name'=>'booking_cancelled_alert', 
				'subject'=>__('Booking cancelled alert', 'calendarista'), 
				'content'=>self::getContent('booking_cancelled_alert')
			)
			, array(
				'emailType'=>Calendarista_EmailType::APPOINTMENT_OUT_OF_STOCK, 
				'name'=>'appointment_out_of_stock', 
				'subject'=>__('Appointment out of stock', 'calendarista'), 
				'content'=>self::getContent('appointment_out_of_stock')
			)
			, array(
				'emailType'=>Calendarista_EmailType::GOOGLE_CALENDAR_AUTHENTICATION_FAILURE, 
				'name'=>'google_calendar_authentication_failure', 
				'subject'=>__('Google Calendar authentication failure', 'calendarista'), 
				'content'=>self::getContent('google_calendar_authentication_failure')
			)
			, array(
				'emailType'=>Calendarista_EmailType::BOOKING_THANKYOU_REMINDER, 
				'name'=>'booking_thankyou_reminder', 
				'subject'=>__('Booking thankyou reminder', 'calendarista'), 
				'content'=>self::getContent('booking_thankyou_reminder')
			)
		);
	}
	public static function getTemplate($emailType){
		$templates = self::getTemplates();
		return isset($templates[$emailType]) ? new Calendarista_EmailSetting($templates[$emailType]) : null;
	}
	public static function sendNotifications($order){
		$emails = array();
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		$emailer = null;
		if($generalSetting->enableGDPR){
			$authRepo = new Calendarista_AuthRepository();
			$password = Calendarista_AuthRepository::genPassword($order->email);
			$authRepo->insert(array('password'=>$password, 'userEmail'=>$order->email));
			$emailer = new Calendarista_GdprEmailer($order->email, $order->fullName, $password);
		}
		if($generalSetting->autoNotifyAdminNewBooking){
			$staffMembers = Calendarista_PermissionHelper::readStaffMembers(2/*include staffmember object*/, $order->availabilityId);
			if($staffMembers && count($staffMembers) > 0){
				foreach($staffMembers as $staff){
					array_push($emails, array('address'=>$staff['email'], 'type'=>Calendarista_EmailType::NEW_BOOKING_RECEIVED));
				}
			}else{
				array_push($emails, array('type'=>Calendarista_EmailType::NEW_BOOKING_RECEIVED));
			}
		}
		if($generalSetting->notifyBookingReceivedSuccessfully){
			array_push($emails, array('type'=>Calendarista_EmailType::BOOKING_RECEIVED_SUCCESS));
		}
		if($generalSetting->autoApproveBooking && $generalSetting->notifyBookingConfirmation){
			array_push($emails, array('type'=>Calendarista_EmailType::BOOKING_CONFIRMATION));
		}
		foreach($emails as $email){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>$order->id
				, 'emailType'=>$email['type']
			));
			if(isset($email['address'])){
				$notification->send($email['address']);
			}else{
				$notification->send();
			}
		}
		if($emailer){
			$emailer->send();
		}
	}
	public function sendGdprEmail($customerName, $customerEmail){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if($generalSetting->enableGDPR){
			$authRepo = new Calendarista_AuthRepository();
			$password = Calendarista_AuthRepository::genPassword($customerEmail);
			$authRepo->insert(array('password'=>$password, 'userEmail'=>$customerEmail));
			$emailer = new Calendarista_GdprEmailer($customerEmail, $customerName, $password);
			$emailer->send();
		}
	}
}
?>