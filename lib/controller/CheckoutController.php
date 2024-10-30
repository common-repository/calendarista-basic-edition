<?php
class Calendarista_CheckoutController extends Calendarista_BaseController
{
	public $checkoutHelper;
	public function __construct($viewState = null, $checkoutCallback = null, $validateRequest = true, $projectId = null)
	{
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_checkout')){
			return;
		}
		if(!Calendarista_CheckoutHelper::isHuman()){
			return;
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		if(isset($_POST['booknow']) && $projectId == $_POST['projectId']){
			$this->checkoutHelper = new Calendarista_CheckoutHelper(array('viewState'=>$viewState));
			if($validateRequest && !$this->checkoutHelper->stockValid()){
				$this->checkoutHelper->notifyOutOfStock();
				add_filter('calendarista_checkout_payment_failure_message', array($this, 'getOutOfStockMessage'));
				$this->executeCallback($checkoutCallback, array(null));
				return;
			}
			$orderIsValid = true;
			$invoiceId = null;
			if($validateRequest){
				$orderIsValid = $this->checkoutHelper->orderIsValid();
			}
			if($orderIsValid){
				$order = $this->checkoutHelper->log();
				if($order){
					if($order->totalAmount > 0 && $order->paymentsMode === 0/*offline*/){
						Calendarista_CheckoutHelper::paymentRequiredNotify($order->id);
					}
					$invoiceId = $order->invoiceId;
				} else{
					$orderIsValid = false;
				}
			}
			$this->executeCallback($checkoutCallback, array($invoiceId, $orderIsValid));
		}
	}
	protected function getViewStateValue($key, $default = null){
		return isset($this->viewState) && isset($this->viewState[$key]) ? $this->viewState[$key] : $default;
	}
	public function getOutOfStockMessage(){
		return $this->checkoutHelper->getOutOfStockErrorMessage();
	}
}
?>