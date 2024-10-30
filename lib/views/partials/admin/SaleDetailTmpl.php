<?php
class Calendarista_SaleDetailTmpl extends Calendarista_TemplateBase{
	public $orderId;
	public $order;
	public $bookedAvailability;
	public $orderDeleted;
	public $availabilityNames = array();
	public function __construct(){
		$this->orderId = (int)$this->getPostValue('orderId');
		parent::__construct();
		new Calendarista_SalesController(
			array($this, 'requestPayment')
			, array($this, 'confirmPayment')
			, array($this, 'delete')
		);
		if($this->orderDeleted){
			return;
		}
		$orderRepo = new Calendarista_OrderRepository();
		$this->order = $orderRepo->read($this->orderId);
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$bookedAvailabilityList = $bookedAvailabilityRepo->readByOrderId($this->orderId);
		$this->bookedAvailability = $bookedAvailabilityList[0];
		$idList = array();
		foreach($bookedAvailabilityList as $bal){
			if(in_array((int)$bal->availabilityId, $idList)){
				continue;
			}
			array_push($this->availabilityNames, $bal->availabilityName);
			array_push($idList, (int)$bal->availabilityId);
		}
		$this->render();
	}
	function confirmPayment($result){
	?>
		<div class="index updated notice is-dismissible notification-flat">
			<?php esc_html_e('Payment for the sale has been confirmed.', 'calendarista') ?>
		</div>
		<hr>
	<?php
	}
	function requestPayment($result){
	?>
		<div class="index updated notice is-dismissible notification-flat">
			<?php esc_html_e('An email has been sent to customer to solicit payment.', 'calendarista') ?>
		</div>
		<hr>
	<?php
	}
	function delete($result){
		$this->orderDeleted = true;
	?>
		<div class="index updated notice is-dismissible notification-flat">
			<?php esc_html_e('The order has been deleted. Wait for the page to complete refreshing.', 'calendarista') ?>
		</div>
		<hr>
	<?php
	}
	function getDiscount(){
		if($this->order->discount){
			return $this->order->discountMode ? 
				$this->formatCurrency($this->order->discount) : 
				$this->order->discount . '%';
		}
		return null;
	}
	function getDeposit(){
		if($this->order->deposit){
			return $this->order->depositMode ? 
				$this->formatCurrency($this->order->deposit) : 
				$this->order->deposit . '%';
		}
		return null;
	}
	function formatCurrency($value, $shortFormat = false){
		return Calendarista_MoneyHelper::formatCurrencySymbol(Calendarista_MoneyHelper::toDouble($value), $shortFormat, $this->order->currency, $this->order->currencySymbol);
	}
	public function render(){
	?>
	<p><strong><?php echo $this->order->invoiceId ?></strong></p>
	<?php if($this->order->paymentStatus === 1/*PAID*/):?>
	<p>
		<?php esc_html_e('Payment Method', 'calendarista') ?>: <?php echo $this->order->paymentOperator; ?>
		<br>
		<?php esc_html_e('Transaction ID', 'calendarista') ?>: <?php echo $this->order->transactionId ? $this->order->transactionId : '--'; ?>
		<?php if($this->order->paymentDate): ?>
		<br>
		<?php esc_html_e('Payment date', 'calendarista') ?>: <?php echo $this->order->paymentDate->format(CALENDARISTA_FULL_DATEFORMAT) ?>
		<?php endif; ?>
	</p>
	<?php endif;?>
	<?php if($this->order->wooCommerceOrderId):?>
	<p>
		<?php echo sprintf(__('WooCommerce Order %s', 'calendarista'), $this->order->wooCommerceOrderId) ?>
	</p>
	<?php endif; ?>
	<table class="wp-list-table calendarista widefat fixed striped">
		  <thead>
			<th><?php esc_html_e('Name', 'calendarista')?></th>
			<th><?php esc_html_e('Service', 'calendarista')?></th>
			<th><?php esc_html_e('Status', 'calendarista')?></th>
		  </thead>
		  <tbody>
			<tr>
				<td>
					<?php echo esc_html($this->order->fullName) ?>,
					<br>
					<?php echo esc_html($this->order->email) ?>
				</td>
				<td>
					<?php echo esc_html($this->order->projectName) ?>
					<hr>
					<?php echo esc_html(implode('<br>', $this->availabilityNames)); ?>
				</td>
				<td>
					<?php echo esc_html(Calendarista_Order::getPaymentStatus($this->order->paymentStatus)); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<br>
	<table class="wp-list-table calendarista widefat fixed striped">
		  <thead>
			<th></th>
			<th class="calendarista-align-right" align="right"><?php esc_html_e('Amount', 'calendarista')?></th>
		  </thead>
		  <tbody>
			<?php if($this->order->discount):?>
			<tr>
				<td class="calendarista-align-right"><?php esc_html_e('Discount', 'calendarista') ?></td>
				<td class="calendarista-align-right">
					<?php echo $this->getDiscount() ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if($this->order->tax):?>
			<tr>
				<td class="calendarista-align-right"><?php esc_html_e('Tax', 'calendarista') ?></td>
				<td class="calendarista-align-right">
					<?php echo $this->order->tax ?>%
				</td>
			</tr>
			<?php endif; ?>
			<?php if($this->order->deposit):?>
			<tr>
				<td class="calendarista-align-right"><?php esc_html_e('Deposit', 'calendarista') ?></td>
				<td class="calendarista-align-right">
				<?php echo $this->getDeposit() ?>
				</td>
			</tr>
			<tr>
				<td class="calendarista-align-right"><?php esc_html_e('Payment required on arrival', 'calendarista') ?></td>
				<td class="calendarista-align-right">
				<?php echo $this->formatCurrency($this->order->balance) ?>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="calendarista-align-right"><strong><?php esc_html_e('Total Amount', 'calendarista') ?></strong></td>
				<td class="calendarista-align-right">
					<strong><?php echo $this->formatCurrency($this->order->totalAmount); ?></strong>
				</td>
			</tr>
		  </tbody>
		</table>
	<?php
	}
}?>

