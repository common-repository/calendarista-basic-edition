<?php
class Calendarista_SalesController extends Calendarista_BaseController{
	public function __construct($requestPaymentCallback, $confirmPaymentCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_sales')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		parent::__construct();
		if(array_key_exists('requestPayment', $_POST)){
			$this->requestPayment($requestPaymentCallback);
		}else if (array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if (array_key_exists('confirmPayment', $_POST)){
			$this->confirmPayment($confirmPaymentCallback);
		}
	}
	public function confirmPayment($callback){
		$orderId = (int)$this->getPostValue('orderId');
		$projectId = (int)$this->getPostValue('projectId');
		$paymentReceivedNotification = (bool)$this->getPostValue('paymentReceivedNotification');
		$confirmBookingNotification = (bool)$this->getPostValue('confirmBookingNotification');
		$repo = new Calendarista_OrderRepository();
		$repo->updatePaymentStatus($orderId, Calendarista_PaymentStatus::PAID);
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		//confirm the appointment as well
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
		foreach($bookedAvailabilityList as $bookedAvailability){
			$bookedAvailabilityRepo->updateStatus((int)$bookedAvailability->id, Calendarista_AvailabilityStatus::APPROVED);
		}
		$emails = array();
		if($paymentReceivedNotification){
			array_push($emails, Calendarista_EmailType::BOOKING_PAYMENT_RECEVIED);
		}
		if($confirmBookingNotification){
			array_push($emails, Calendarista_EmailType::BOOKING_CONFIRMATION);
		}
		foreach($emails as $emailType){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>$orderId
				, 'emailType'=>$emailType
			));
			$notification->send();
		}
		do_action('calendarista_after_confirm_payment_notification', $orderId);
		$this->executeCallback($callback, array($orderId));
	}
	
	public function requestPayment($callback){
		$orderId = (int)$this->getPostValue('orderId');
		$projectId = (int)$this->getPostValue('projectId');
		$notification = new Calendarista_NotificationEmailer(array(
			'orderId'=>$orderId
			, 'emailType'=>Calendarista_EmailType::PAYMENT_REQUIRED
			, 'projectId'=>$projectId
		));
		$notification->send();
		$this->executeCallback($callback, array($orderId));
	}
	public function delete($callback){
		$orderId = (int)$this->getPostValue('orderId');
		$projectId = (int)$this->getPostValue('projectId');
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
		$bookingCancelledNotification = (bool)$this->getPostValue('bookingCancelledNotification');
		Calendarista_EmailReminderJob::cancelSchedule($orderId);
		foreach($bookedAvailabilityList as $bookedAvailability){
			$status = (int)$bookedAvailability->status;
			if($status != Calendarista_AvailabilityStatus::CANCELLED && $bookingCancelledNotification){
				$notification = new Calendarista_NotificationEmailer(array(
					'orderId'=>$orderId
					, 'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED
					, 'projectId'=>$projectId
					, 'bookedAvailabilityId'=>(int)$bookedAvailability->id
				));
				$notification->send();
			}
		}
		$repo = new Calendarista_OrderRepository();
		$result = $repo->delete($orderId);
		$this->executeCallback($callback, array($result));
	}
}
?>