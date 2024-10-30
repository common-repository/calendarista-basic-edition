<?php
class Calendarista_AppointmentsController extends Calendarista_BaseController{
	public $viewState;
	public $generalSetting;
	public $projectId;
	public $orderId;
	public $checkoutHelper;
	public $upfrontPayment;
	public $data = array();
	public function __construct($viewState = null, $statusCallback = null, $updateCallback = null, $deleteCallback = null, $deleteImportedCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_appointments')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->viewState = $viewState;
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->projectId = (int)$this->getViewStateValue('projectId');
		$this->orderId = (int)$this->getPostValue('orderId');
		$this->upfrontPayment = isset($_POST['upfrontPayment']) && $_POST['upfrontPayment'] ? true : false;
		if(array_key_exists('calendarista_update', $_POST)){
			$this->checkoutHelper = new Calendarista_CheckoutHelper(array('viewState'=>$viewState, 'appointment'=>1));
			$this->update($updateCallback);
		}else if(array_key_exists('calendarista_delete', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('calendarista_delete_imported', $_POST)){
			$this->deleteImported($deleteImportedCallback);
		}else if(isset($_POST['updateAppointmentStatus'])){
			$this->updateAppointmentStatus($statusCallback);
		}
	}
	public function update($callback = null){
		$projectRepo = new Calendarista_ProjectRepository();
		$project = $projectRepo->read($this->projectId);
		$newProjectId = isset($_POST['projectId']) ? (int)$_POST['projectId'] : null;
		$availabilityId = isset($_POST['availabilityId']) ? (int)$_POST['availabilityId'] : (int)$this->getViewStateValue('availabilityId');
		$availabilities = $this->getPostValue('availabilities');
		$bookedAvailabilityId = isset($_POST['bookedAvailabilityId']) ? (int)$_POST['bookedAvailabilityId'] : null;
		if($this->projectId !== $newProjectId){
			$newProject = $projectRepo->read($newProjectId);
			if($newProject->calendarMode !== $project->calendarMode){
				if(!$callback){
					return false;
				}
				$this->executeCallback($callback, array(false));
				return;
			}
			$project = $newProject;
			if(isset($_POST['availabilityId'])){
				$availabilityId =  (int)$_POST['availabilityId'];
			}
		}
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$availability = $availabilityRepo->read($availabilityId);
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailability = $bookedAvailabilityRepo->read($bookedAvailabilityId);
		$status = null;
		$seats = null;
		if($bookedAvailability){
			if(is_null($status)){
				$status = (int)$bookedAvailability->status;
			}
			if(is_null($seats)){
				$seats = (int)$bookedAvailability->seats;
			}
			Calendarista_GoogleCalendarHelper::deleteEvent((int)$bookedAvailability->id);
		}
		//update the order
		$this->saveOrder($project, $availability);
		//availability delete and insert
		$bookedAvailabilityRepo->deleteByOrder($this->orderId);
		$repeatDateList = $this->checkoutHelper->getRepeatDateList();
		if(count($repeatDateList) > 0){
			$availabilityData = $this->checkoutHelper->saveAvailability($this->orderId, $project, $availability, $status, $seats, false, $repeatDateList);
		}else{
			$availabilityData = $this->checkoutHelper->saveAvailability($this->orderId, $project, $availability, $status, $seats);
		}
		//map delete and insert
		$mapRepo = new Calendarista_BookedMapRepository();
		$mapRepo->deleteByOrder($this->orderId);
		$this->checkoutHelper->saveMap($this->orderId);
		//waypoint delete and insert
		$waypointRepo = new Calendarista_BookedWaypointRepository();
		$waypointRepo->deleteByOrder($this->orderId);
		$this->checkoutHelper->saveWaypoints($this->orderId);
		//optionals delete and insert
		$bookedOptionalRepo = new Calendarista_BookedOptionalRepository();
		$bookedOptionalRepo->deleteByOrder($this->orderId);
		$this->checkoutHelper->saveOptionals($this->orderId);
		//custom form fields and insert
		$formElementRepo = new Calendarista_BookedFormElementRepository();
		$formElementRepo->deleteByOrder($this->orderId);
		$this->checkoutHelper->saveFormFields($this->orderId);
		//dynamic fields
		$dynamicFieldRepo = new Calendarista_BookedDynamicFieldRepository();
		$dynamicFieldRepo->deleteByOrder($this->orderId);
		$this->checkoutHelper->saveDynamicFields($this->orderId, $project, $availability);
		
		if($availabilities && is_array($availabilities)){
			//this order has multiple availabilities, so handle
			$orderAvailabilityRepo = new Calendarista_OrderAvailabilityRepository();
			foreach($availabilities as $id){
				$availability = $availabilityRepo->read((int)$id);
				$this->checkoutHelper->saveDynamicFields($this->orderId, $project, $availability);
				$this->checkoutHelper->saveAvailability($this->orderId, $project, $availability, $status, $seats, true);
				$orderAvailabilityRepo->insert(array(
					'orderId'=>$this->orderId
					, 'availabilityId'=>$availability->id
					, 'availabilityName'=>$availability->name
				));
			}
		}
		foreach($availabilityData as $data){
			if($this->generalSetting->notifyBookingHasChanged){
				$notification = new Calendarista_NotificationEmailer(array(
					'orderId'=>$this->orderId
					, 'emailType'=>Calendarista_EmailType::BOOKING_HAS_CHANGED
					, 'bookedAvailabilityId'=>(int)$data['bookedAvailabilityId']
				));
				$notification->send();
			}
			if($status === Calendarista_AvailabilityStatus::APPROVED){
				Calendarista_EmailReminderJob::cancelScheduleByAvailability((int)$data['bookedAvailabilityId']);
				new Calendarista_EmailReminderJob($this->orderId, $project->id, (int)$data['bookedAvailabilityId']);
			}
			Calendarista_GoogleCalendarHelper::insertEvent((int)$data['bookedAvailabilityId']);
			if($this->generalSetting->updatedAppointmentZap){
				Calendarista_WebHookHelper::postDataToUrl($this->generalSetting->updatedAppointmentZap, $data);
			}
		}
		if(!$callback){
			return true;
		}
		$this->executeCallback($callback, array(true));
	}
	public function saveOrder($project, $availability){
		$costHelper = new Calendarista_CostHelper($this->viewState);
		$orderRepo = new Calendarista_OrderRepository();
		$order = $orderRepo->read($this->orderId);
		$orderRepo->update(new Calendarista_Order(array(
			'fullName'=>$this->sanitize($this->getViewStateValue('name'))
			, 'email'=>$this->getViewStateValue('email')
			, 'projectId'=>$project->id
			, 'projectName'=>$project->name
			, 'availabilityName'=>$availability->name
			, 'availabilityId'=>$availability->id
			, 'userId'=>$this->checkoutHelper->getUserId($project->membershipRequired)
			, 'totalAmount'=>$costHelper->getTotalAmount($this->upfrontPayment)
			, 'currency'=>Calendarista_MoneyHelper::getCurrency()
			, 'currencySymbol'=>Calendarista_MoneyHelper::getCurrencySymbol()
			, 'discount'=>$costHelper->couponHelper->discount
			, 'discountMode'=>$costHelper->couponHelper->discountMode
			, 'tax'=>$this->generalSetting->tax
			, 'paymentStatus'=>$order->paymentStatus
			//, 'timezone'=>$this->getViewStateValue('timezone')
			, 'serverTimezone'=>$availability->timezone
			, 'deposit'=>$availability->deposit
			, 'depositMode'=>$availability->depositMode
			, 'balance'=>(float)$costHelper->balance
			, 'couponCode'=>$costHelper->couponHelper->coupon->code
			, 'upfrontPayment'=>$this->upfrontPayment
			, 'id'=>$this->orderId
		)));
		$this->checkoutHelper->customerName = $this->sanitize($this->getViewStateValue('name'));
		$this->checkoutHelper->totalAmount = Calendarista_MoneyHelper::toLongCurrency($costHelper->totalAmount);
		$this->checkoutHelper->totalAmountRaw = $costHelper->totalAmount;
		if($this->generalSetting->newCustomerZap){
			$emailExists = $repo->emailExists($this->getViewStateValue('email'));
			if($emailExists){
				Calendarista_WebHookHelper::postDataToUrl($this->generalSetting->newCustomerZap, array(
					'name'=>$this->sanitize($this->getViewStateValue('name'))
					, 'email'=>$this->getViewStateValue('email')
				));
			}
		}
		$orderAvailabilityRepo = new Calendarista_OrderAvailabilityRepository();
		$orderAvailabilityRepo->delete($this->orderId);
	}
	public function updateAppointmentStatus($callback){
		$status = (int)$this->getPostValue('status');
		$orderId = (int)$this->getPostValue('orderId');
		$bookedAvailabilityId = isset($_POST['bookedAvailabilityId']) ? (int)$_POST['bookedAvailabilityId'] : null;
		if(!$bookedAvailabilityId){
			return;
		}
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
		$bookedAvailabilityRepo->updateStatus($bookedAvailabilityId, $status);
		Calendarista_GoogleCalendarHelper::updateEvent($bookedAvailabilityId);
		if($status === Calendarista_AvailabilityStatus::APPROVED){
			if($this->generalSetting->notifyBookingConfirmation){
				$notification = new Calendarista_NotificationEmailer(array(
					'orderId'=>$orderId
					, 'emailType'=>Calendarista_EmailType::BOOKING_CONFIRMATION
					, 'bookedAvailabilityId'=>$bookedAvailabilityId
				));
				$notification->send();
			}
			new Calendarista_EmailReminderJob($orderId, $bookedAvailabilityId);
		}else{
			if(count($bookedAvailabilityList) === 1){
				if($this->generalSetting->cancelWooCommerceOrder && Calendarista_WooCommerceHelper::wooCommerceInitiated()){
					$orderRepo = new Calendarista_OrderRepository();
					$order = $orderRepo->read($orderId);
					if($order && $order->wooCommerceOrderId){
						$wcOrder = new WC_Order($order->wooCommerceOrderId);
						if (!empty($wcOrder)) {
							$wcOrder->update_status('cancelled');
						}
					}
				}
			}
			if($this->generalSetting->customerBookingCancelNotification){
				$notification = new Calendarista_NotificationEmailer(array(
					'orderId'=>$orderId
					, 'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED
					, 'bookedAvailabilityId'=>$bookedAvailabilityId
				));
				$notification->send();
			}
			Calendarista_EmailReminderJob::cancelScheduleByAvailability($bookedAvailabilityId);
		}
		$this->executeCallback($callback, array($status));
	}
	public function delete($callback = null){
		$orderId = (int)$this->getPostValue('orderId');
		$bookedAvailabilityId = isset($_POST['bookedAvailabilityId']) ? (int)$_POST['bookedAvailabilityId'] : null;
		if(!$bookedAvailabilityId){
			return;
		}
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($orderId);
		$bookedAvailability = $bookedAvailabilityList[0];
		if(count($bookedAvailabilityList) > 1){
			foreach($bookedAvailabilityList as $ba){
				if((int)$ba->id === $bookedAvailabilityId){
					$bookedAvailability = $ba;
					break;
				}
			}
		}
		
		$status = (int)$bookedAvailability->status;
		if($status != Calendarista_AvailabilityStatus::CANCELLED && ($this->generalSetting->enableCancelNotificationOnDelete || 
					($status === Calendarista_AvailabilityStatus::APPROVED && $this->generalSetting->customerBookingCancelNotification))){
			$notification = new Calendarista_NotificationEmailer(array(
				'orderId'=>$orderId
				, 'emailType'=>Calendarista_EmailType::BOOKING_CANCELLED
				, 'bookedAvailabilityId'=>(int)$bookedAvailability->id
			));
			$notification->send();
		}
		Calendarista_GoogleCalendarHelper::deleteEvent((int)$bookedAvailability->id);
		Calendarista_EmailReminderJob::cancelScheduleByAvailability((int)$bookedAvailability->id);
		$result = null;
		if(count($bookedAvailabilityList) === 1){
			$orderRepo = new Calendarista_OrderRepository();
			$result = $orderRepo->delete($orderId);
			$orderAvailabilityRepo = new Calendarista_OrderAvailabilityRepository();
			$orderAvailabilityRepo->delete($orderId);
		}else{
			$result = $bookedAvailabilityRepo->delete((int)$bookedAvailability->id);
		}
		if(!$callback){
			return $result;
		}
		$this->executeCallback($callback, array($orderId, $result));
	}
	public function deleteImported($callback = null){
		$synchedBookingId = $this->getPostValue('synchedBookingId');
		$repo = new Calendarista_BookedAvailabilityRepository();
		$result = $repo->deleteSyncedDataById($synchedBookingId);
		if(!$callback){
			return $result;
		}
		$this->executeCallback($callback, array($result));
	}
	protected function getViewStateValue($key, $default = null){
		return isset($this->viewState) && isset($this->viewState[$key]) ? $this->viewState[$key] : $default;
	}
	protected function sanitize($value){
		return stripslashes($value);
	}
	protected function hasPermission($emails){
		if(!Calendarista_PermissionHelper::isAdmin()){
			$currentUser = wp_get_current_user();
			if($currentUser->ID === 0 || !in_array($currentUser->user_email, $emails)){
				return false;
			}
		}
		return true;
	}
}
?>