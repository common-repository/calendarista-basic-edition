<?php
class Calendarista_CancelAppointmentTmpl extends Calendarista_TemplateBase{
	public $secretKey;
	public $cancelled;
	public $appointmentDate;
	public function __construct(){
		parent::__construct();
		$this->secretKey = isset($_GET['calendarista_cancel_key']) ? sanitize_text_field($_GET['calendarista_cancel_key']) : null;
		$this->appointmentDate = isset($_GET['start_date']) ? trim(sanitize_text_field($_GET['start_date'])) : '';
		$endDate = isset($_GET['end_date']) ? trim(sanitize_text_field($_GET['end_date'])) : '';
		if($endDate && strpos($endDate, $this->appointmentDate) !== false){
			$this->appointmentDate = $endDate;
		}else{
			$this->appointmentDate .= sprintf(' - %s', $endDate);
		}
		$this->cancelled = $this->controller();
		$this->render();
	}
	public function controller(){
		if(!$this->secretKey){
			return 5;
		}
		$orderRepo = new Calendarista_OrderRepository();
		$order = $orderRepo->readBySecretKey($this->secretKey);
		if(!$order){
			return 5;
		}
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($order->id);
		$bookedAvailability = $bookedAvailabilityList[0];
		//Note: The best way to use modified strings is to use translations and not Services->Text page.
		$this->stringResources = Calendarista_StringResourceHelper::getResource($bookedAvailability->projectId);
		if((int)$bookedAvailability->status === Calendarista_AvailabilityStatus::CANCELLED){
			return 6;
		}
		if($this->generalSetting->cancellationPolicy > 0){
			$originalTimezone = $bookedAvailability->serverTimezone ? Calendarista_TimeHelper::setTimezone($bookedAvailability->serverTimezone) : null;
			$bookingDate = $bookedAvailability->fromDate;
			if($bookedAvailability->fromDate != $bookedAvailability->toDate){
				$bookingDate = $bookedAvailability->toDate;
			}
			$bookingDate = strtotime($bookingDate);
			$now = strtotime('now');
			if($bookingDate < $now){
				//booking is already expired
				if($originalTimezone){
					Calendarista_TimeHelper::setTimezone($originalTimezone);
				}
				return 2;
			}
			$cancelPolicy = strtotime('+' . $this->generalSetting->cancellationPolicy . ' minutes', $now);
			if($cancelPolicy > $bookingDate){
				//cannot cancel, violating policy
				if($originalTimezone){
					Calendarista_TimeHelper::setTimezone($originalTimezone);
				}
				return 3;
			}
			if($originalTimezone){
					Calendarista_TimeHelper::setTimezone($originalTimezone);
				}
		}
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'cancel_appointment')){
				return 0;
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$secretKey = isset($_POST['key']) ? $_POST['key'] : null;
		if(!$secretKey){
			return 1;
		}
		foreach($bookedAvailabilityList as $ba){
			$bookedAvailabilityRepo->updateStatus((int)$ba->id, Calendarista_AvailabilityStatus::CANCELLED);
			Calendarista_GoogleCalendarHelper::updateEvent((int)$ba->id);
		}
		$notification = new Calendarista_NotificationEmailer(array(
			'orderId'=>$order->id
			, 'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED
		));
		$notification->send();
		Calendarista_EmailReminderJob::cancelSchedule($order->id);
		//Notify admin that a booking has been cancelled.
		if($this->generalSetting->enableCancelBookingAlert){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>$order->id
				, 'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED_ALERT
			));
			$staffMembers = Calendarista_PermissionHelper::readStaffMembers(2/*include staffmember object*/, $order->availabilityId);
			if($staffMembers && count($staffMembers) > 0){
				foreach($staffMembers as $staff){
					$notification->send($staff['email']);
				}
			}else{
				$notification->send();
			}
		}
		return 4;
	}
	public function cancelPolicyViolated(){
		?>
		<div class="alert alert-danger">
			<?php esc_html_e('Cancel policy violation. It is too late to cancel your appointment.', 'calendarista')?>
		</div>
		<?php
	}
	public function render(){
		?>
		<div class="calendarista">
			<?php if($this->cancelled === 1):?>
			<div class="alert alert-danger">
				<?php esc_html_e('It is not possible to cancel your appointment at this time.', 'calendarista')?>
			</div>
			<?php elseif($this->cancelled === 2): ?>
			<div class="alert alert-danger">
				<?php esc_html_e('You cannot cancel an appointment that has already expired.', 'calendarista')?>
			</div>
			<?php elseif($this->cancelled === 3): ?>
			<div class="alert alert-danger">
				<?php esc_html_e('Cancel policy violation. It is too late to cancel your appointment.', 'calendarista')?>
			</div>
			<?php elseif($this->cancelled === 4):?>
			<div class="alert alert-success">
				<?php echo esc_html($this->stringResources['CANCEL_APPOINTMENT_SUCCESS']) ?>
			</div>
			<?php elseif($this->cancelled === 5):?>
			<div class="alert alert-danger">
				<?php esc_html_e('Please use the cancellation link provided to you by email.', 'calendarista')?>
			</div>
			<?php elseif($this->cancelled === 6):?>
			<div class="alert alert-danger">
				<?php esc_html_e('The appointment has already been cancelled.', 'calendarista')?>
			</div>
			<?php else: ?>
			<form action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="key" value="<?php echo esc_html($this->secretKey) ?>"/>
				<input type="hidden" name="controller" value="cancel_appointment"/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<div class="alert alert-warning">
					<?php echo sprintf($this->stringResources['CANCEL_APPOINTMENT_CONFIRM'], esc_html($this->appointmentDate)) ?>
				</div>
				<div class="calendarista-align-right">
					<button type="submit" class="calendarista-wizard-action-button" name="cancel"><?php esc_html_e('Yes, cancel', 'calendarista') ?></button>
					<br class="clearfix">
				</div>
			</form>
			<?php endif; ?>
		</div>
		<?php 
	}
}