<?php
class Calendarista_EmailCouponTemplate{
	public $id;
	public $name;
	public $email;
	function __construct( ){
		$this->id = (int)$this->getPostValue('id');
		$this->email = (string)$this->getPostValue('email');
		$this->name = (string)$this->getPostValue('name');
		if(isset($_POST['sendEmail'])){
			$this->sendEmail();
		}
		$this->render();
	}
	function sendEmail(){
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$repo = new Calendarista_CouponRepository();
		$coupon = $repo->read($this->id);
		if($coupon){
			//ToDO: use notification class (delete coupon emailer class)
			$couponEmailer = new Calendarista_CouponEmailer($coupon, $this->email, $this->name);
			$status = $couponEmailer->send();
			$repo = new Calendarista_CouponRepository();
			$coupon->emailedTo = $this->email;
			$repo->update($coupon);
			$this->emailSendNotification();
		}
	}
	protected function getPostValue($key, $default = null){
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}
	public function emailSendNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('The coupon has been emailed successfully', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<form id="form1" data-parsley-validate method="post">
			<input type="hidden" name="controller" value="calendarista_coupons" />
			<input type="hidden" name="id" value="<?php echo $this->id ?>">
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<table class="form-table">
				<tbody>
					<tr>
						<td><label for="name"><?php esc_html_e('Customer Name', 'calendarista')?></label></td>
						<td>
							<input type="text" 
								id="name" 
								class="regular-text calendarista_parsley_validated"
								name="name" 
								value="<?php echo esc_attr($this->name) ?>"
								data-parsley-required="true"
								data-parsley-trigger="change"  />
						</td>
					</tr>
					<tr>
						<td><label for="email"><?php esc_html_e('Customer Email', 'calendarista')?></label></td>
						<td>
							<input type="text" 
								id="email" 
								class="regular-text calendarista_parsley_validated"
								name="email" 
								value="<?php echo esc_attr($this->email) ?>"
								data-parsley-required="true"
								data-parsley-type="email" 
								data-parsley-trigger="change" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
	}
}