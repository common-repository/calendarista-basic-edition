<?php
class Calendarista_SetupStep2Template extends Calendarista_ViewBase{
	public $fields;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-setup');
		$this->fields = $this->getFields();
		$this->render();
	}
	function getFields(){
		$result = array('availableDate'=>null, 'calendarMode'=>0, 'paymentsMode'=>1, 'daysInPackage'=>1, 'cost'=>null, 'returnCost'=>null, 'depositMode'=>null, 'deposit'=>null, 'returnOptional'=>null);
		if(isset($_POST['availableDate'])){
			$result['availableDate'] = $_POST['availableDate'];
		}
		if(isset($_POST['calendarMode'])){
			$result['calendarMode'] = (int)$_POST['calendarMode'];
		}
		if(isset($_POST['paymentsMode'])){
			$result['paymentsMode'] = (int)$_POST['paymentsMode'];
		}
		if(isset($_POST['daysInPackage'])){
			$result['daysInPackage'] = (int)$_POST['daysInPackage'];
		}
		if(isset($_POST['cost'])){
			$result['cost'] = $_POST['cost'];
		}
		if(isset($_POST['returnCost'])){
			$result['returnCost'] = $_POST['returnCost'];
		}
		if(isset($_POST['depositMode'])){
			$result['depositMode'] = (int)$_POST['depositMode'];
		}
		if(isset($_POST['deposit'])){
			$result['deposit'] = $_POST['deposit'];
		}
		if(isset($_POST['returnOptional'])){
			$result['returnOptional'] = $_POST['returnOptional'];
		}
		return $result;
	}
	public function render(){
	?>
		<div id="step" data-calendarista-next-step-id="3" data-calendarista-prev-step-id="1">
			<h1><?php esc_html_e('Tell us when a customer can start making a booking', 'calendarista') ?></h1>
			<p class="description"><?php esc_html_e('Hint: This is usually the current date (today) but can be any date in the future. You make the call.', 'calendarista') ?></p>
			<input type="hidden" name="fullDay" value="<?php echo in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST) ?>"/>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<div><label for="availableDate"><?php esc_html_e('Date', 'calendarista') ?></label></div>
							<input id="availableDate" 
								name="availableDate" 
								type="text" 
								class="regular-text enable-readonly-input calendarista_parsley_validated" 
								data-parsley-required="true"
								data-parsley-group="block1"
								readonly
								value="<?php echo $this->fields['availableDate'] ?>" />
								<p class="description"><?php esc_html_e('Availability date, format later in settings', 'calendarista')?></p>
						</td>
					</tr>
					<?php if(in_array($this->fields['calendarMode'], array(Calendarista_CalendarMode::PACKAGE))):?>
					<tr>
						<td>
							<div><label for="daysInPackage"><?php esc_html_e('No. of days', 'calendarista') ?></label></div>
							<input id="daysInPackage" 
								name="daysInPackage" 
								type="text" 
								class="small-text calendarista_parsley_validated" 
								data-parsley-trigger="change focusout"
								data-parsley-required="true"
								data-parsley-type="digits"
								data-parsley-min="1"
								value="<?php echo $this->fields['daysInPackage'] ?>" />
							<p class="description"><?php esc_html_e('Number of days in package. Minimum 1 day', 'calendarista') ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<?php if($this->fields['paymentsMode'] !== -1 && in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST)):?>
					<tr>
						<td>
							<div><label for="cost"><?php esc_html_e('Cost', 'calendarista') ?></label></div>
							<input id="cost" 
								name="cost" 
								type="text" 
								class="small-text calendarista_parsley_validated" 
								data-parsley-trigger="change focusout"
								data-parsley-min="0"
								data-parsley-pattern="^\d+(\.\d{1,2})?$"
								data-parsley-errors-container="#cost_error_container"
								placeholder="0.00" 
								data-parsley-group="block1"
								value="<?php echo $this->emptyStringIfZero($this->fields['cost']) ?>" />
						</td>
					</tr>
					<?php endif; ?>
					<?php if($this->fields['paymentsMode'] !== -1):?>
					<tr>
						<td>
							<div><label for="deposit"><?php esc_html_e('Deposit', 'calendarista') ?></label></div>
							<i><?php esc_html_e('Customer will be charged an upfront', 'calendarista') ?></i>
							<br>
							<label>
								 <input type="radio"  
										name="depositMode" 
										value="0"
										<?php echo !$this->fields['depositMode'] ? 'checked' : '' ?>>
								<?php esc_html_e('percentage', 'calendarista') ?>
							</label>
							<label>
								 <input type="radio"  
										name="depositMode" 
										value="1"
										<?php echo $this->fields['depositMode'] === 1 ? 'checked' : '' ?>>
								<?php esc_html_e('flat fee', 'calendarista') ?>
							</label>
							<label>
								 <input type="radio"  
										name="depositMode" 
										value="2"
										<?php echo $this->availability->depositMode === 2 ? 'checked' : '' ?>>
								<?php esc_html_e('flat fee x seats', 'calendarista') ?>
							</label>
							<input id="deposit" 
								name="deposit" 
								type="text" 
								class="small-text calendarista_parsley_validated" 
								data-parsley-trigger="change focusout"
								data-parsley-min="0"
								data-parsley-pattern="^\d+(\.\d{1,2})?$"
								data-parsley-cal-hasval="#cost"
								data-parsley-errors-container="#deposit_error_container"
								data-parsley-error-message="<?php esc_html_e('Ensure full price is set.', 'calendarista') ?>"
								placeholder="0.00" 
								value="<?php echo $this->fields['deposit'] ?>" />
							<label for="deposit"><?php esc_html_e('deposit', 'calendarista') ?></label>
							<div id="deposit_error_container"></div>
						</td>
					</tr>
					<?php endif; ?>
					<?php if(in_array($this->fields['calendarMode'], Calendarista_CalendarMode::$SUPPORTS_RETURN)):?>
					<tr>
						<td>
							<div><label for="returnOptional"><?php esc_html_e('Return date', 'calendarista') ?></label></div>
							<input id="returnOptional" 
								name="returnOptional" 
								type="checkbox" 
								<?php echo $this->fields['returnOptional'] ? 'checked' : '' ?> />
								<?php esc_html_e('Return is optional', 'calendarista') ?>
						</td>
					</tr>
					<?php if($this->fields['paymentsMode'] !== -1):?>
					<tr>
						<td>
							<div><label for="returnCost"><?php esc_html_e('Return cost', 'calendarista') ?></label></div>
							<input id="returnCost" 
								name="returnCost" 
								type="text" 
								class="small-text calendarista_parsley_validated" 
								data-parsley-trigger="change focusout"
								data-parsley-min="0"
								data-parsley-pattern="^\d+(\.\d{1,2})?$"
								placeholder="0.00" 
								data-parsley-group="block1"
								value="<?php echo $this->fields['returnCost'] ?>" />
						</td>
					</tr>
					<?php endif; ?>
					<?php endif; ?>
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
				calendarista.setupStep2 = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.setupStep2.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.dateTimepickerOptions = {
						'timeFormat': 'HH:mm'
						, 'dateFormat': 'yy-mm-dd'
						//, 'minDate': 0
					};
					this.$availableDateTextbox = $('input[name="availableDate"]');
					this.$availableDateTextbox.datetimepicker(this.dateTimepickerOptions);
					window.Parsley.addValidator('cal-hasval', {
					  validateString: function (value, requirement) {
						if(parseFloat(value) > 0 && parseRequirement(requirement) == 0){
							return false;
						}
						return true;
					  },
					  priority: 32
					});
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.setupStep2({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}