<?php
class Calendarista_SetupStep1Template extends Calendarista_ViewBase{
	public $calendarMode;
	public $fields;
	public $welcome;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->calendarMode = Calendarista_CalendarMode::toArray();
		$this->fields = $this->getFields();
		$this->welcome = isset($_GET['welcome']) ? true : false;
		$this->render();
	}
	function getFields(){
		$result = array('name'=>null, 'calendarMode'=>0, 'paymentsMode'=>-1, 'wooProductId'=>null);
		if(isset($_POST['name'])){
			$result['name'] = $_POST['name'];
		}
		if(isset($_POST['calendarMode'])){
			$result['calendarMode'] = (int)$_POST['calendarMode'];
		}
		if(isset($_POST['paymentsMode'])){
			$result['paymentsMode'] = (int)$_POST['paymentsMode'];
		}
		if(isset($_POST['wooProductId'])){
			$result['wooProductId'] = (int)$_POST['wooProductId'];
		}
		return $result;
	}
	public function render(){
	?>
		<div id="step" data-calendarista-next-step-id="2" data-calendarista-prev-step-id="1">
			<?php if($this->welcome): ?>
			<h1><?php esc_html_e('Welcome to Calendarista', 'calendarista') ?></h1>
			<p class="description"><?php esc_html_e('This wizard uses the bare minimum options required to configure a service. If you need more advanced features, you may edit the service later. If this is your first time or if you are an advanced user and want a quick configuration, we highly advice you to use this wizard.', 'calendarista') ?></p>
			<?php endif; ?>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<div><label for="name"><?php esc_html_e('The name of your service', 'calendarista') ?></label></div>
							<input id="name" name="name" type="text" 
								class="regular-text calendarista_parsley_validated" 
								data-parsley-required="true" 
								data-parsley-pattern="^[^<>'`\u0022]+$"
								value="<?php echo Calendarista_StringResourceHelper::decodeString($this->fields['name']) ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<div><label for="calendarMode"><?php esc_html_e('The date and time selection mode', 'calendarista') ?></label></div>
							<select id="calendarMode" name="calendarMode">
								<?php foreach($this->calendarMode as $mode):?>
								<option value="<?php echo $mode['key']?>"
									<?php echo $this->fields['calendarMode'] === $mode['key'] ? "selected" : ""?>><?php echo $mode['value'] ?></option>
							   <?php endforeach;?>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<div>
								<label for=""><?php esc_html_e('Do you want to accept payments?', 'calendarista')?></label>
							</div>
							<ul>
								<li>
									<label for="">
										<input name="paymentsMode"
											value="-1"
											type="radio" <?php echo $this->fields['paymentsMode'] === -1 ? "checked" : ""?> /> 
											<?php esc_html_e('No', 'calendarista')?>
									</label>
								</li>
								<li>
									<label for="">
										<input name="paymentsMode" 
											value="0"
											type="radio" <?php echo $this->fields['paymentsMode'] === 0 ? "checked" : ""?> /> 
											<?php esc_html_e('Collect payment offline', 'calendarista')?>
									</label>
								</li>
								<li>
									<label for="">
											<input name="paymentsMode" 
												value="1"
												type="radio" <?php echo $this->fields['paymentsMode'] === 1 ? "checked" : ""?> /> 
												<?php esc_html_e('Enable online payments', 'calendarista')?>
									</label>
								</li>
								<li>
									<label for="">
										<input name="paymentsMode" 
											value="2"
											type="radio" <?php echo $this->fields['paymentsMode'] === 2 ? "checked" : ""?> /> 
											<?php esc_html_e('Enable online payments and offline mode', 'calendarista')?>
									</label>
								</li>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.setupStep1 = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupStep1.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$paymentModes = $('input[name="paymentsMode"]');
					this.$wooProductId = $('select[name="wooProductId"]');
					this.$paymentModes.on('change', function(){
						var val = parseInt($(this).val(), 10);
						context.$wooProductId.parsley().reset();
						if(!context.$wooProductId.is(':disabled')){
							context.$wooProductId.prop('disabled', true);
						}
						if(val === 3/*woocommerce*/){
							context.$wooProductId.prop('disabled', false);
						}
					});
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupStep1({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}