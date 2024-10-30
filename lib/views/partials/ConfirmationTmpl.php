<?php
class Calendarista_ConfirmationTmpl extends Calendarista_TemplateBase{
	public $invoiceId;
	public $projectId;
	public $failureMessage;
	public function __construct(){
		parent::__construct();
		$this->invoiceId = isset($_GET['calendarista_invoice_id']) ? sanitize_text_field($_GET['calendarista_invoice_id']) : null;
		$this->failureMessage = isset($_GET['calendarista_failure_msg']) ? sanitize_text_field(trim($_GET['calendarista_failure_msg'])) : null;
		$this->projectId = apply_filters('calendarista_project_id', null);
		$repo = new Calendarista_OrderRepository();
		$order = $repo->readByInvoiceId($this->invoiceId);
		if($order){
			$this->projectId = $order->projectId;
		}
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->render();
	}
	public function render(){
	?>
		<div class="calendarista">
			<?php if($this->invoiceId):?>
				<div class="alert alert-success calendarista-alert alert-dismissible calendarista-alert-confirmation" role="alert" id="CAL<?php echo $this->projectId ?>">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<div class="calendarista-typography--subtitle3">
						<?php echo esc_html($this->stringResources['BOOKING_THANKYOU']) ?>
					</div>
					<div class="calendarista-typography--subtitle4">
						<?php echo sprintf($this->stringResources['BOOKING_CREATED'], $this->invoiceId) ?>
					</div>
					<?php Calendarista_BookingWizardTmpl::addToCalendarButtonRender($this->invoiceId, $this->stringResources, $this->generalSetting->displayAddToCalendarOption); ?>
				</div>
			<?php elseif($this->failureMessage): ?>
				<!-- Sometimes the notification from Paypal may take longer and hence it might seem as though the payment was not successful. 
				This is why we do not report payment failed but ask customer to check their email. -->
				<div class="alert alert-warning calendarista-alert alert-dismissible calendarista-alert-confirmation" role="alert" id="CAL<?php echo $this->projectId ?>">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<div class="calendarista-typography--subtitle3">
						<?php echo esc_html($this->stringResources['BOOKING_THANKYOU']) ?>
					</div>
					<div class="calendarista-typography--subtitle4">
						<?php echo esc_html($this->failureMessage) ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}
}