<?php
class Calendarista_PayPalHelper{
	private $name = 'PayPal';
	 /**
     * @var bool $use_sandbox     Indicates if the sandbox endpoint is used.
     */
    private $use_sandbox = false;
    /**
     * @var bool $use_local_certs Indicates if the local certificates are used.
     */
    private $use_local_certs = true;

    /** Production Postback URL */
    const VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';
    /** Sandbox Postback URL */
    const SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';


    /** Response from PayPal indicating validation was successful */
    const VALID = 'VERIFIED';
    /** Response from PayPal indicating validation failed */
    const INVALID = 'INVALID';
	
	public function __construct(){
		$settings = self::getSettings();
		$this->use_sandbox = $settings->useSandbox;
	}
    /**
     * Sets the IPN verification to sandbox mode (for use when testing,
     * should not be enabled in production).
     * @return void
     */
    public function useSandbox()
    {
        $this->use_sandbox = true;
    }

    /**
     * Sets curl to use php curl's built in certs (may be required in some
     * environments).
     * @return void
     */
    public function usePHPCerts()
    {
        $this->use_local_certs = false;
    }


    /**
     * Determine endpoint to post the verification data to.
     * @return string
     */
    public function getPaypalUri()
    {
        if ($this->use_sandbox) {
            return self::SANDBOX_VERIFY_URI;
        } else {
            return self::VERIFY_URI;
        }
    }


    /**
     * Verification Function
     * Sends the incoming post data back to PayPal using the cURL library.
     *
     * @return bool
     * @throws Exception
     */
    public function verifyIPN()
    {
        if ( ! count($_POST)) {
			//return false;
            //throw new Exception("Missing POST Data");
        }

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
		$decodedPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                // Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
                if ($keyval[0] === 'payment_date') {
                    if (substr_count($keyval[1], '+') === 1) {
                        $keyval[1] = str_replace('+', '%2B', $keyval[1]);
                    }
                }
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        // Build the body of the verification post request, adding the _notify-validate command.
        $req = 'cmd=_notify-validate';
        foreach ($myPost as $key => $value) {
            $value = urlencode($value);
            $req .= "&$key=$value";
        }
		$checkoutHelper = null;
		if(isset($myPost['custom'])){
			//log booking
			$paymentDate = new Calendarista_DateTime(date('Y-m-d H:i:s', strtotime($myPost['payment_date'])));
			$checkoutHelper = new Calendarista_CheckoutHelper(array(
				'stagingId'=>$myPost['custom']
			));
		}
		if($checkoutHelper && !$checkoutHelper->stockValid()){
			$checkoutHelper->notifyOutOfStock(true/*forcedNotification*/);
			exit();
		}
        // Post the data back to PayPal, using curl. Throw exceptions if errors occur.
		$response = wp_remote_post($this->getPaypalUri(), array(
			'body'=>$req,
			'timeout'     => '30',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'cookies'     => array(),
		));
		if($response && isset($response['body'])){
			if($response['body'] == self::VALID){
				$this->log($myPost);
			}else{
				Calendarista_ErrorLogHelper::insert(sprintf('Paypal response: %s', $response['body']));
			}
		}
		exit();
    }
	public function log($post){
		if(isset($post['custom'])){
			$upfrontPayment = isset($_REQUEST['upfrontPayment']) ? (int)$_REQUEST['upfrontPayment'] : 0;
			//log booking
			$paymentDate = new Calendarista_DateTime(date('Y-m-d H:i:s', strtotime($post['payment_date'])));
			$checkoutHelper = new Calendarista_CheckoutHelper(array(
				'stagingId'=>$post['custom']
				, 'paymentOperator'=>$this->name
				, 'paymentDate'=>$paymentDate
				, 'transactionId'=>$post['txn_id']
				, 'upfrontPayment'=>$upfrontPayment
			));
			$order = $checkoutHelper->log();
			Calendarista_CheckoutHelper::confirmAndNotify($order->id, false);
			do_action('calendarista_after_payment', $order->id, Calendarista_PaymentOperator::PAYPAL);
			return $order->invoiceId;
		}
		return null;
	}
	public static function getSettings(){
		$repo = new Calendarista_PaymentSettingRepository();
		$result = $repo->read(Calendarista_PaymentOperator::PAYPAL);
		if($result){
			return new Calendarista_PayPalSetting($result);
		}
		return false;
	}
	
	public static function getCurrencies(){
		//https://developer.paypal.com/docs/payouts/reference/country-and-currency-codes/
		return array(
			'AUD'=>'Australian Dollar (A $)'
			,'CAD'=>'Canadian Dollar (C $)'
			,'EUR'=>'Euro (&euro;)'
			,'GBP'=>'British Pound (&pound;)'
			//,'JPY'=>'Japanese Yen (&yen;)'
			,'USD'=>'U.S. Dollar ($)'
			,'NZD'=>'New Zealand Dollar ($)'
			,'CHF'=>'Swiss Franc'
			,'HKD'=>'Hong Kong Dollar ($)'
			,'SGD'=>'Singapore Dollar ($)'
			,'SEK'=>'Swedish Krona'
			,'DKK'=>'Danish Krone'
			,'PLN'=>'Polish Zloty'
			,'NOK'=>'Norwegian Krone'
			//,'HUF'=>'Hungarian Forint'
			,'CZK'=>'Czech Koruna'
			,'ILS'=>'Israeli New Shekel'
			,'MXN'=>'Mexican Peso'
			//,'BRL'=>'Brazilian Real (only for Brazilian members)'
			//,'MYR'=>'Malaysian Ringgit (only for Malaysian members)'
			,'PHP'=>'Philippine Peso'
			//,'TWD'=>'New Taiwan Dollar'
			,'THB'=>'Thai Baht'
			,'TRY'=>'Turkish Lira (only for Turkish members)'
			, 'RUB'=>'Russian Ruble'
		);
	}
}
?>