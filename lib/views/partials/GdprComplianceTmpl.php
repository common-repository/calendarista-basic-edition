<?php
class Calendarista_GdprComplianceTmpl extends Calendarista_TemplateBase{
	public $password;
	public $email;
	public $isValid;
	public $handlerUrl;
	public $requestPending;
	public function __construct(){
		parent::__construct();
		$this->password = isset($_GET['password']) ? sanitize_text_field($_GET['password']) : null;
		$this->email = isset($_GET['email']) ? sanitize_email($_GET['email']) : null;
		$this->requestUrl = remove_query_arg(array('calendarista_handler', 'email', 'password'), $_SERVER['REQUEST_URI']);
		$this->handlerUrl = esc_url_raw(add_query_arg(array('calendarista_handler'=>'gdpr', 'email'=>$this->email, 'password'=>$this->password), $this->requestUrl));
		$this->requestUrl = esc_url_raw(add_query_arg(array('email'=>$this->email, 'password'=>$this->password), $this->requestUrl));
		$this->isValid = $this->isValid($this->password, $this->email);
		if (array_key_exists('calendarista_delete_data_request', $_POST)){
			$this->deleteDataRequest();
		}
		$gdprRepo = new Calendarista_GdprRepository();
		$this->requestPending = $gdprRepo->exists($this->email);
		//check to see if a request had already been made, so that we can disable teh delete data button.
		$this->render();
	}
	public function isValid($password, $email){
		if($password && $email){
			$authRepo = new Calendarista_AuthRepository();
			return $authRepo->isValid($password, $email);
		}
		return false;
	}
	public function deleteDataRequest(){
		$password = $this->getPostValue('password');
		$email = $this->getPostValue('email');
		if($this->isValid($password, $email)){
			$gdprRepo = new Calendarista_GdprRepository();
			$today = new Calendarista_DateTime();
			$gdprRepo->insert(array('requestDate'=>$today->format(CALENDARISTA_DATEFORMAT), 'userEmail'=>$email));
			$this->statusNotification();
		}
	}
	public function statusNotification(){
	?>
		<div class="calendarista">
			<div class="col-xl-12">
				<div class="form-group">
					<div class="alert alert-warning">
						<?php echo esc_html($this->stringResources['GDPR_REQUEST_SUCCESS']) ?>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	<?php
	}
	public function render(){
	?>
	<div class="calendarista">
		<div class="col-xl-12">
			<div class="form-group">
				<?php if(!$this->isValid):?>
				<p><?php esc_html_e('If you would like to view or delete your data, please follow the GDPR link sent to you by email.', 'calendarista') ?></p>
				<?php else: ?>
				<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
					<input type="hidden" name="controller" value="calendarista_gdpr">
					<input type="hidden" name="password" value="<?php echo esc_attr($this->password) ?>">
					<input type="hidden" name="email" value="<?php echo esc_attr($this->email) ?>">
					<p>
						<a href="<?php echo esc_url($this->handlerUrl) ?>" class="btn btn-outline-primary calendarista-remove-box-shadow"><i class="fa fa-download"></i>&nbsp;<?php esc_html_e('Download a copy of my data', 'calendarista') ?></a>
					</p>
					<p>
						<button type="submit" name="calendarista_delete_data_request" class="btn btn-outline-danger" <?php echo $this->requestPending ? 'disabled' : '' ?>><i class="fa fa-trash-o"></i>&nbsp;<?php esc_html_e('Request deletion of my data', 'calendarista') ?></button>	
					</p>
					<?php if($this->requestPending): ?>
					<p><i><?php esc_html_e('Your request for data deletion is being reviewed.', 'calendarista') ?></i></p>
					<?php endif; ?>
					<p>
						<?php esc_html_e('Please note that we cannot delete an ongoing appointment. Only past appointments are deleted.', 'calendarista') ?>
					</p>
				</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
	}
}