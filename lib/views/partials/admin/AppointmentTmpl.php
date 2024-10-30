<?php
class Calendarista_AppointmentTmpl extends Calendarista_TemplateBase{
	public $project;
	public $map;
	public $optionals;
	public $stringResources;
	public $steps;
	public $counter;
	public $prev;
	public $next;
	public $selectedStepName;
	public $appointment;
	public $_stateBag;
	public $status;
	public $orderId;
	public $bookedAvailabilityId;
	public $showSales = true;
	public $availabilityId;
	public $appointmentCreated;
	public $appointmentUpdated;
	public $invoiceId;
	public $editMode;
	public $resetViewState;
	public function __construct($appointment){
		$this->appointment = $appointment;
		$this->_stateBag = null;
		$this->orderId = (int)$this->getPostValue('orderId');
		$this->bookedAvailabilityId = isset($_POST['bookedAvailabilityId']) ? (int)$_POST['bookedAvailabilityId'] : null;
		$this->availabilityId = (int)$this->getPostValue('availabilityId');
		$this->editMode = isset($_POST['editMode']) ? (int)$_POST['editMode'] : 0;
		if($this->appointment && (!$this->getPostValue('__viewstate'))){
			$this->_stateBag = $this->getStateBag();
		}
		parent::__construct($this->_stateBag);
		$this->appointmentsController();
		if($this->resetViewState){
			$this->_stateBag = $this->getStateBag();
			parent::__construct($this->_stateBag);
		}
		$this->status = (int)$this->getViewStateValue('status');
		new Calendarista_CheckoutController(
			$this->viewState
			, array($this, 'checkout')
			, false
			, $this->projectId
		);
		if($this->appointmentCreated){
			$this->appointmentCreatedNotification();
			return false;
		}
		$this->prev = $this->selectedStep - 1;
		$this->next = $this->selectedStep + 1;
		$this->project = Calendarista_ProjectHelper::getProject($this->projectId);
		$repo = new Calendarista_MapRepository();
		$this->map = $repo->readByProject($this->projectId);
		$repo = new Calendarista_OptionalRepository();
		$this->optionals = $repo->readAll($this->projectId);
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->initSteps();
		$this->selectedStepName = $this->steps[$this->selectedStep - 1]['name'];
		$this->invoiceId = $this->getViewStateValue('invoiceId');
		if($this->invoiceId){
			$orderRepo = new Calendarista_OrderRepository();
			$order = $orderRepo->readByInvoiceId($this->invoiceId);
			$this->showSales = $order && $order->totalAmount > 0;
		}
		$this->enableMultipleBooking = true;
		$this->render();
	}
	public function appointmentsController(){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_appointments')){
			return;
		}
		new Calendarista_AppointmentsController(
			$this->viewState
			, array($this, 'updateAppointmentStatus')
			, array($this, 'updateAppointment')
		);
		$this->resetViewState = true;
	}
	public function getStateBag(){
		$result = Calendarista_AppointmentHelper::getAppointmentViewState($this->bookedAvailabilityId, $this->orderId, $this->availabilityId);
		if(isset($result[1])){
			//in the front-end this is retrieved via short-code
			$this->_enableMultipleBooking = $result[1]['enableMultipleBooking'];
		}
		return serialize($result);
	}
	public function initSteps(){
		$this->steps = array();
		$this->counter = 0;
		array_push($this->steps, array(
			'name'=>'calendar'
			, 'counter'=>++$this->counter
			, 'label'=>$this->stringResources['WIZARD_STEP_1']
		));
		if($this->map){
			array_push($this->steps, array(
				'name'=>'map'
				, 'counter'=>++$this->counter
				, 'label'=>$this->stringResources['WIZARD_STEP_2']
			));
		}
		if($this->optionals->count() > 0){
			array_push($this->steps, array(
				'name'=>'optionals'
				, 'counter'=>++$this->counter
				, 'label'=>$this->stringResources['WIZARD_STEP_3']
			));
		}
		array_push($this->steps, array(
			'name'=>'form'
			, 'counter'=>++$this->counter
			, 'label'=>$this->stringResources['WIZARD_STEP_4']
		));
	}
	public function checkout($invoiceId, $orderIsValid){
		$this->invoiceId = $invoiceId;
		if($invoiceId){
			$orderRepo = new Calendarista_OrderRepository();
			$order = $orderRepo->readByInvoiceId($invoiceId);
			$this->showSales = $order && $order->totalAmount > 0;
		}
		$this->appointmentCreated = true;
		$this->clearViewState();
	}
	public function getInvoiceUrl(){
		return admin_url() . 'admin.php?page=calendarista-sales&controller=calendarista_sales&invoiceId=' . $this->invoiceId;
	}
	public function appointmentCreatedNotification(){
		
		$salesLink = $this->showSales ? sprintf('<a href="%s" target="_blank">%s</a>', $this->getInvoiceUrl(), $this->invoiceId) : $this->invoiceId;
		?>
			<?php if($this->invoiceId):?>
			<div class="alert alert-success calendarista-alert">
				<div class="calendarista-typography--subtitle3">
					<?php echo sprintf(__('The booking was created successfully.', 'calendarista'))?>
				</div>
				<div class="calendarista-typography--subtitle4">
					<?php echo sprintf(__('For your reference, the invoice number is %s.', 'calendarista'), $salesLink) ?>
				</div>
			</div>
			<?php else: ?>
			<div class="alert alert-error calendarista-alert">
				<div class="calendarista-typography--subtitle3">
					<?php echo sprintf(__('Something went wrong.', 'calendarista'))?>
				</div>
				<div class="calendarista-typography--subtitle4">
					<?php esc_html_e('Creating the booking has failed.', 'calendarista') ?>
				</div>
			</div>
			<?php endif; ?>
		<?php 
	}
	public function updateAppointment($result){
		$this->appointmentUpdated = true;
	}
	public function updateAppointmentStatus($result){
		$this->status = $result;
	}
	public function render(){
	?>
	<div id="<?php echo $this->uniqueId ?>"  class="calendarista calendarista-typography">
		<div class="col-xl-12">
			<?php if($this->invoiceId):?>
				<p class="alert alert-info calendarista-alert">
					<?php esc_html_e('Invoice ID', 'calendarista') ?>:&nbsp;
					<?php if($this->showSales):?>
					<a href="<?php echo esc_url($this->getInvoiceUrl()) ?>" target="_blank"><?php echo esc_html($this->invoiceId) ?></a>
					<?php else: ?>
					<?php echo $this->invoiceId; ?>
					<?php endif; ?>
				</p>
			<?php endif;?>
			<?php if($this->appointmentUpdated):?>
				<p class="alert alert-success calendarista-alert">
					<?php esc_html_e('The appointment has been updated successfully.', 'calendarista') ?>
				</p>
			<?php endif;?>
			<?php if($this->status === 1):?>
				<div class="alert alert-success" role="alert">
					<strong><?php esc_html_e('Note', 'calendarista') ?>!</strong>&nbsp;<?php esc_html_e('This is a confirmed appointment.', 'calendarista') ?>
				</div>
			<?php elseif($this->status === 2):?>
				<div class="alert alert-danger" role="alert">
					<strong><?php esc_html_e('Note', 'calendarista') ?>!</strong>&nbsp;<?php esc_html_e('This is a cancelled appointment.', 'calendarista') ?>
				</div>
			<?php endif;?>
		</div>
		<div class="card-header">
			<div class="col-xl-12 calendarista-navbar-container">
				<div id="navbar_<?php echo $this->uniqueId ?>">
					<ol class="nav nav-tabs calendarista-wizard-nav card-header-tabs calendarista-typography--caption1">
					<?php foreach($this->steps as $step):?>
					  <li class="nav-item">
						<a href="#" class="<?php echo $this->selectedStepName === $step['name'] ? 'nav-link active' : 'nav-link' ?><?php echo $step['counter'] < $this->selectedStep ? ' nav-link-enabled' : '' ?>" data-calendarista-index="<?php echo $step['counter']?>">
						  <span class="calendarista-nav-label"><?php echo $step['label']?></span>
						</a>
					  </li>
					  <?php endforeach; ?>
					</ol>
					<select id="dropdown_<?php echo esc_attr($this->uniqueId) ?>" class="form-select hide">
						<?php foreach($this->steps as $i=>$step):?>
						  <option value="<?php echo esc_attr($i) ?>" data-calendarista-index="<?php echo esc_attr($step['counter']) ?>"
							<?php echo $this->selectedStepName === $step['name'] ? 'selected' : '' ?>>
							<?php echo esc_html($step['label']) ?>
						  </option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="card-body">
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post" 
				data-parsley-inputs="input, textarea, select, hidden" 
				data-parsley-excluded="input[type=button], input[type=submit], input[type=reset]">
				<input type="hidden" name="controller" value="calendarista_checkout"/>
				<input type="hidden" name="projectId" value="<?php echo esc_html($this->projectId) ?>">
				<input type="hidden" name="orderId" value="<?php echo esc_html($this->orderId) ?>">
				<input type="hidden" name="bookedAvailabilityId" value="<?php echo esc_html($this->bookedAvailabilityId) ?>">
				<input type="hidden" name="calendarMode" value="<?php echo esc_html($this->project->calendarMode) ?>">
				<input type="hidden" name="postbackStep" value="<?php echo esc_html($this->selectedStepName) ?>"> 
				<input type="hidden" name="availabilityPreviewUrl" value="">
				<input type="hidden" name="__viewstate" value="<?php echo esc_html($this->stateBag) ?>">
				<input type="hidden" name="appointment" value="<?php echo esc_html($this->appointment) ?>">
				<input type="hidden" name="paymentsMode" value="2"/>
				<!-- sales page requires status -->
				<input type="hidden" name="availabilityStatus" value="<?php echo esc_html($this->status) ?>"/>
				<?php switch($this->selectedStepName){
						case 'calendar':
							new Calendarista_BookingCalendarFieldsTmpl($this->appointment, $this->_stateBag, $this->enableMultipleBooking);
						break;
						case 'map':
							new Calendarista_BookingMapTmpl($this->_stateBag);
						break;
						case 'optionals':
							new Calendarista_BookingOptionalsTmpl($this->_stateBag);
						break;
						case 'form':
							new Calendarista_BookingCustomFormFieldsTmpl(false, $this->_stateBag);
						break;
					}		
				?>
				<div class="col-xl-12">
					<?php if($this->selectedStepName !== 'checkout'):?>
					<div class="calendarista-row-single calendarista-cost-summary-placeholder"></div>
					<?php endif; ?>
					<div id="spinner_<?php echo $this->uniqueId ?>" class="calendarista-spinner calendarista-invisible">
						<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"> <?php echo esc_html($this->stringResources['AJAX_SPINNER'])?>
					</div>
				</div>
			</form>
			<div class="clearfix"></div>
		</div>
		<script type="text/javascript">
		(function(){
			function init(){
				new Calendarista.wizard({
					'id': '<?php echo $this->uniqueId?>'
					, 'wizardAction': 'calendarista_wizard'
					, 'bookMoreAction': 'calendarista_bookmore'
					, 'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
					, 'prevIndex': <?php echo $this->prev ?>
					, 'nextIndex': <?php echo $this->next ?>
					, 'stepCounter': <?php echo $this->counter ?>
					, 'appointment': <?php echo $this->appointment ?>
					, 'editMode': <?php echo $this->editMode ?>
					, 'steps': <?php echo wp_json_encode($this->steps) ?>
					, 'selectedStepName': '<?php echo $this->selectedStepName ?>'
					, 'selectedStepIndex': <?php echo $this->selectedStep ?> 
					, 'invoiceId': '<?php echo $this->invoiceId ?>'
					, 'externalDialog': true
				});
			}
			init();
		})();
		</script>
	</div>
	<?php
	}
}?>

