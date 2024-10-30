<?php
class Calendarista_BookingCustomFormFieldsTmpl extends Calendarista_TemplateBase{
	public $formElements;
	public $checkboxGroup = false;
	public $membershipRequired;
	public $enableStrongPassword;
	public $customerType;
	public $userExists;
	public $credentialsIncorrect;
	public $name;
	public $email;
	public $userId;
	public $customerTypeChangedPostback;
	public $newAppointment;
	public $manualBooking;
	public $costHelper;
	public $seats;
	public $availability;
	public $formTypes;
	public function __construct($customerTypeChangedPostback = false, $stateBag = null){
		parent::__construct($stateBag);
		$this->customerTypeChangedPostback = $customerTypeChangedPostback;
		$this->costHelper = new Calendarista_CostHelper($this->viewState);
		$repo = new Calendarista_ProjectRepository();
		$this->project = $repo->read($this->projectId);
		$repo = new Calendarista_FormElementRepository();
		$this->formElements = $repo->readAll($this->projectId);
		$this->membershipRequired = $this->project->membershipRequired;
		$this->enableStrongPassword = $this->project->enableStrongPassword;
		//manualBooking true = creating a booking from the backend (appointments page)
		$this->manualBooking = isset($_POST['appointment']);
		$this->customerType = (int)$this->getPostValue('customerType', -1);
		$this->newAppointment = (int)$this->getViewStateValue('appointment');
		$this->seats = (int)$this->getViewStateValue('seats');
		if($this->customerType === -1 && $this->getViewStateValue('customerType')){
			$this->customerType = $this->getViewStateValue('customerType');
		}
		$authenticatedUserData = !$this->manualBooking ? Calendarista_AuthHelper::getUserData() : array('name'=>null, 'email'=>null, 'userId'=>null);
		$this->name = $authenticatedUserData['name'];
		if($this->getViewStateValue('name')){
			$this->name = $this->getViewStateValue('name');
		}
		$this->email = $authenticatedUserData['email'];
		if($this->getViewStateValue('email')){
			$this->email = $this->getViewStateValue('email');
		}
		$this->userId = $authenticatedUserData['userId'];
		if($this->getViewStateValue('userId')){
			$this->userId = $this->getViewStateValue('userId');
		}
		if(Calendarista_WooCommerceHelper::ensureWooCommerceInitiated()){
			$wc = WC();
			$cart = $wc->session->get('cart');
			if($cart){
				$keys = array_keys($cart);
				$lastIndex = count($keys);
				if($lastIndex > 0){
					$item = $cart[$keys[$lastIndex - 1]];
					$firstName = isset($item['_billing_first_name']) ? $item['_billing_first_name'] : null;
					$lastName = isset($item['_billing_last_name']) ? $item['_billing_last_name'] : null;
					$email = isset($item['_billing_email']) ? $item['_billing_email'] : null;
					if($firstName){
						$this->name = $firstName . ' ' . $lastName;
					}
					if($email){
						$this->email = $email;
					}
				}
			}
		}
		$availabilityId = (int)$this->getViewStateValue('availabilityId');
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$this->availability = $availabilityRepo->read($availabilityId);
		$this->formTypes = $this->getFormTypes();
		$this->render();
	}
	protected function getPaymentOperatorsCount($result){
		$i = 0;
		foreach($result as $r){
			if($r['enabled']){
				$i++;
			}
		}
		return $i;
	}
	public function isUserLoggedIn(){
		//if appointment is set in $_POST, then we are trying to manage appointments
		$result = Calendarista_PermissionHelper::isUserLoggedIn();
		if($result && $this->manualBooking){
			return false;
		}
		return $result;
	}
	public function getFormElementValue($id, $index = null){
		$formElements = $this->getViewStateValue('formelements');
		if(!is_array($formElements)){
			return null;
		}
		foreach($formElements as $formElement){
			if($formElement['elementId'] === $id){
				if($index !== null && (!isset($formElement['guestIndex']) ||  $formElement['guestIndex'] !== $index)){
					continue;
				}
				return $formElement['value'];
			}
		}
		return null;
	}
	public function checked($id, $val, $index = null){
		$formElements = $this->getViewStateValue('formelements');
		if(!is_array($formElements)){
			return null;
		}
		$result = array();
		foreach($formElements as $formElement){
			if($formElement['elementId'] === $id){
				if($index !== null && (!isset($formElement['guestIndex']) ||  $formElement['guestIndex'] !== $index)){
					continue;
				}
				$result = explode(',', $formElement['value']);
				break;
			}
		}
		if(in_array($val, $result)){
			return 'checked';
		}
		return null;
	}
	public function selectedList($id, $val, $defaultValue, $index = null){
		$formElements = $this->getViewStateValue('formelements');
		$result = array();
		if(is_array($formElements)){
			foreach($formElements as $formElement){
				if($formElement['elementId'] === $id){
					if($index !== null && (!isset($formElement['guestIndex']) ||  $formElement['guestIndex'] !== $index)){
						continue;
					}
					$result = explode(',', $formElement['value']);
					break;
				}
			}
		}
		if(in_array($val, $result)){
			return 'selected=selected';
		}else if(count($result) === 0 && $val === $defaultValue){
			return 'selected=selected';
		}
		return null;
	}
	public function getGuestName($index){
		//toDO: deprecate this function in future editions.
		$formElements = $this->getViewStateValue('formelements');
		if(!is_array($formElements)){
			return null;
		}
		foreach($formElements as $formElement){
			if($formElement['elementId'] !== -1 /*GUESTNAME*/ || !isset($formElement['guestIndex'])){
				//toDO: deprecate this check in future editions.
				continue;
			}
			if($formElement['guestIndex'] === $index){
				return $formElement['value'];
			}
		}
		return null;
	}
	public function getFormTypes(){
		$result = array(1);
		if($this->availability->selectableSeats && $this->seats){
			foreach($this->formElements as $formElement){
				if($formElement->guestField){
					array_push($result, 2);
					break;
				}
			}
		}
		return $result;
	}
	public function renderFormElements($formType, $index = null){
		?>
		<?php foreach($this->formElements as $formElement):?>
		<?php 
			if($formType === 1 && $formElement->guestField || $formType === 2 && !$formElement->guestField){
				continue;
			}
			$id = 'formelement_' . $formElement->id;
			if($index !== null){
				$id =  $id . '_guest_' . $index;
			}
		?>
			<div class="form-group">
				<?php if(in_array($formElement->elementType, array(0,1,2,3, 8))): ?>
					<label class="form-control-label calendarista-typography--caption1" for="calendarista_<?php echo $id ?>">
						<?php echo $formElement->label ?>
					</label>
					<?php if($formElement->elementType === Calendarista_ElementType::PHONE): ?>
						<input type="text"
								id="calendarista_<?php echo $id ?>"
								name="<?php echo $id ?>"
								data-parsley-calendarista-phone="<?php echo esc_attr(formElement->country) ?>"
								class="form-control calendarista-typography--caption1 calendarista-phone calendarista_parsley_validated <?php echo esc_attr($formElement->className) ?>" 
								value="<?php echo $this->getFormElementValue($formElement->id, $index); ?>"
								data-parsley-error-message="<?php echo esc_html($this->stringResources['PHONE_NUMBER_INCORRECT']) ?>"
								<?php echo $formElement->getValidationAttributes(); ?>
								placeholder="<?php echo $formElement->placeHolder ?>"/>
					<?php elseif($formElement->elementType === Calendarista_ElementType::TEXTBOX): ?>
						<input type="text"
								id="calendarista_<?php echo $id ?>"
								name="<?php echo $id ?>"
								class="form-control calendarista-typography--caption1 calendarista_parsley_validated <?php echo esc_attr($formElement->className) ?>" 
								value="<?php echo $this->getFormElementValue($formElement->id, $index); ?>"
								<?php echo $formElement->getValidationAttributes(); ?>
								placeholder="<?php echo $formElement->placeHolder ?>"/>
					<?php elseif($formElement->elementType === Calendarista_ElementType::TEXTAREA): ?>
						<textarea 
							id="calendarista_<?php echo $id ?>"
							name="<?php echo $id ?>"
							class="form-control calendarista-typography--caption1 calendarista_parsley_validated <?php echo esc_attr($formElement->className) ?>" 
							<?php echo $formElement->getValidationAttributes(); ?>
							placeholder="<?php echo $formElement->placeHolder ?>" rows="3"><?php echo $this->getFormElementValue($formElement->id, $index); ?></textarea>
					<?php elseif($formElement->elementType === Calendarista_ElementType::DROPDOWNLIST || $formElement->elementType === Calendarista_ElementType::MULTISELECT): ?>
						<select 
								id="calendarista_<?php echo $id ?>"
								name="<?php echo $id . ($formElement->elementType === Calendarista_ElementType::MULTISELECT ? '[]' : ''); ?>"
								<?php echo $formElement->getValidationAttributes(); ?>
								class="form-select calendarista-typography--caption1 calendarista_parsley_validated <?php echo esc_attr($formElement->className) ?>"
								<?php echo $formElement->elementType === Calendarista_ElementType::MULTISELECT ? 'multiple="multiple"' : ''; ?>>
								<?php if($formElement->defaultOptionItem):?>
								<option value=""><?php echo $formElement->defaultOptionItem; ?></option>
								<?php endif; ?>
								<?php foreach($formElement->options as $key=>$value):?>
									<option value="<?php echo esc_attr($value) ?>" 
										<?php echo $this->selectedList($formElement->id, $value, $formElement->defaultSelectedOptionItem, $index); ?>><?php echo esc_html($value) ?></option>
								<?php endforeach;?>
						</select>
					<?php endif; ?>
				<?php elseif(in_array($formElement->elementType, array(4, 5))): ?>
					<?php if($formElement->options):?>
						<label class="form-control-label calendarista-typography--caption1" for="calendarista_formelement_<?php echo $formElement->id ?>">
							<?php echo esc_html($formElement->label) ?>
						</label>
						<?php foreach($formElement->options as $key=>$value):?>
						 <div class="form-check">
							<label class="form-check-label">
								<input
									name="<?php echo $id ?>[]"  
									type="<?php echo $formElement->elementType === Calendarista_ElementType::CHECKBOX ? 'checkbox' : 'radio'; ?>"
									class="form-check-input calendarista_parsley_validated <?php echo esc_attr($formElement->className) ?>"
									value="<?php echo esc_attr($value) ?>"
									<?php echo $formElement->getValidationAttributes(); ?>
									<?php echo $this->checked($formElement->id, $value, $index); ?>
									<?php do_action('calendarista_form_element_attributes', $formElement->id, $value, $this->viewState); ?>/>
									<?php echo $value ?>
							</label>
						</div>
						<?php echo do_action('calendarista_form_element_message', $formElement->id, $value, $this->viewState); ?>
						<?php endforeach;?>
						<?php else: ?>
							 <div class="form-check">
								<label class="form-check-label">
									<input
										name="<?php echo $id ?>[]"  
										type="<?php echo $formElement->elementType === Calendarista_ElementType::CHECKBOX ? 'checkbox' : 'radio'; ?>"
										class="form-check-input calendarista_parsley_validated <?php echo esc_attr($formElement->className) ?>"
										value="<?php echo $value ?>"
										<?php echo $formElement->getValidationAttributes(); ?>
										<?php echo $this->checked($formElement->id, $value, $index); ?>/>
										<?php echo esc_html($formElement->label) ?>
								</label>
							</div>
					<?php endif; ?>
				<?php elseif($formElement->elementType === Calendarista_ElementType::PLAINTEXT): ?>
					<p 
						<?php if($formElement->className):?>
						class="calendarista-plaintext <?php echo esc_attr($formElement->className) ?>"
						<?php endif;?>>
						<?php echo wp_kses_post($formElement->content) ?>
					</p>
				<?php elseif($formElement->elementType === Calendarista_ElementType::TERMS): ?>
					<div class="form-check calendarista-row-single">
						<label class="form-check-label">
							<input
								type="checkbox"
								name="terms_<?php echo $formElement->id ?>"
								data-parsley-required="true"
								data-parsley-trigger="change"
								class="form-check-input calendarista_parsley_validated <?php echo $formElement->className ?>" />
								<?php echo wp_kses_post($formElement->content) ?>
						</label>
					</div>
				<?php endif;?>
				<?php if($formElement->lineSeparator):?>
				<hr />
				<?php endif; ?>
			</div>
		<?php endforeach; ?>	
	<?php 
	}
	public function render(){
	?>
	<?php if(!$this->customerTypeChangedPostback):?>
		<?php if($this->membershipRequired && !$this->manualBooking):?>
		<input type="hidden" 
			name="createUserValidator" 
			class="calendarista_parsley_validated" 
			data-parsley-required="true" 
			data-parsley-errors-messages-disabled="true"
			value="1" />
		<input type="hidden" 
			name="signOnValidator" 
			class="calendarista_parsley_validated" 
			data-parsley-required="true" 
			data-parsley-errors-messages-disabled="true"
			value="1" />
		<div class="form-group customer-type-container <?php echo $this->isUserLoggedIn() ? 'hide' : '' ?>">
			<label class="radio-inline">
				<input type="radio" 
				value="0" 
				name="customerType" 
				<?php echo in_array($this->customerType, array(-1, 0)) ? 'checked' : ''; ?>>
				<?php echo esc_html($this->stringResources['REGISTRATION_NEW_CUSTOMER']); ?>
			</label>
			<label class="radio-inline">
				<input type="radio" 
				value="1" 
				name="customerType" 
				<?php echo in_array($this->customerType, array(1)) ? 'checked' : ''; ?>>
				<?php echo esc_html($this->stringResources['REGISTRATION_RETURN_CUSTOMER']); ?>
			</label>
			<hr>
		</div>
		<?php endif; ?>
		<div class="col-xl-12 calendarista-custom-form">
			<?php $this->renderFields(); ?>
		</div>
		<?php do_action('calendarista_form_element_add_script_block', $this->uniqueId); ?>
		<script type="text/javascript">
		(function(){
			"use strict";
			function init(){
				var customForm = new Calendarista.customForm({
					'id': '<?php echo $this->uniqueId ?>'
					, 'projectId': <?php echo $this->projectId ?>
					, 'ajaxUrl': '<?php echo $this->ajaxUrl ?>'
					, 'membershipRequired': <?php echo $this->membershipRequired ? 'true' : 'false' ?>
				})
				, $nextButton;
				$nextButton = customForm.$root.find('button[name="next"]');
				$nextButton.on('click', function(e){
					if(Calendarista.wizard.isValid(customForm.$root)){
						customForm.unload();
					}
				});
			}
			<?php if($this->notAjaxRequest):?>
			
			if (window.addEventListener){
			  window.addEventListener('load', onload, false); 
			} else if (window.attachEvent){
			  window.attachEvent('onload', onload);
			}
			function onload(e){
				init();
			}
			<?php else: ?>
			init();
			<?php endif; ?>
		})();
		</script>
	<?php else: ?>
		<?php $this->renderFields(); ?>
	<?php endif; ?>
<?php
	}
	public function renderFields(){
	?>
		<input type="hidden" name="userId" value="<?php echo $this->userId; ?>"/>
		<?php if(in_array($this->customerType, array(-1, 0))): ?>
		<div class="form-group">
			<label class="form-control-label calendarista-typography--caption1" for="calendarista_name<?php echo $this->projectId; ?>">
				<?php echo esc_html($this->stringResources['REGISTER_NAME_LABEL']); ?>
			</label>
			<input type="text" 
					name="name"
					id="calendarista_name<?php echo $this->projectId; ?>"
					data-parsley-required="true" 
					class="form-control calendarista-typography--caption1 calendarista_parsley_validated" 
					size="20"
					value="<?php echo esc_attr($this->name) ?>"
					<?php echo $this->isUserLoggedIn() && $this->name ? 'readonly' : '' ?> />
		</div>
		<div class="form-group">
			<label class="form-control-label calendarista-typography--caption1" for="calendarista_email<?php echo $this->projectId; ?>">
				<?php echo esc_html($this->stringResources['REGISTER_EMAIL_LABEL']); ?>
			</label>
			<input type="text" 
					name="email"
					id="calendarista_email<?php echo $this->projectId; ?>"
					data-parsley-required="true" 
					data-parsley-type="email"
					data-parsley-maxlength="64"
					<?php echo do_action('calendarista_email_field_attributes'); ?>
					class="form-control calendarista-typography--caption1 calendarista_parsley_validated" 
					size="20"
					value="<?php echo esc_attr($this->email) ?>"
					<?php echo $this->isUserLoggedIn() ? 'readonly' : '' ?> />
			<div class="create-user-error parsley-error hide"><?php echo esc_html($this->stringResources['REGISTRATION_EMAIL_EXISTS']) ?></div>
		</div>
		<?php if($this->membershipRequired && (!$this->manualBooking && !$this->isUserLoggedIn())): ?>
			<div class="form-group">
				<label class="form-control-label calendarista-typography--caption1" for="calendarista_password<?php echo $this->projectId; ?>">
					<?php echo esc_html($this->stringResources['REGISTER_PASSWORD_LABEL']); ?>
				</label>
				<input type="password" 
						name="password" 
						id="calendarista_password<?php echo $this->projectId; ?>" 
						data-parsley-required="true" 
						<?php if($this->enableStrongPassword):?>
						data-parsley-pattern="^(?=.*[A-Z].*[A-Z])(?=.*[0-9].*[0-9]).{6}$"
						data-parsley-error-message="<?php echo esc_html($this->stringResources['REGISTER_PASSWORD_ERROR'])?>"
						<?php else: ?>
						data-parsley-minlength="6"
						<?php endif; ?>
						class="form-control calendarista-typography--caption1 calendarista_parsley_validated" 
						value="" 
						size="20" />
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if(!in_array($this->customerType, array(-1, 0))): ?>
		<div class="form-group">
			<label class="form-control-label calendarista-typography--caption1" for="calendarista_email<?php echo $this->projectId ?>"><?php echo esc_html($this->stringResources['LOGIN_EMAIL_LABEL']); ?></label>
			<input type="text" 
					name="email" 
					id="calendarista_email<?php echo $this->projectId ?>" 
					data-parsley-required="true" 
					data-parsley-type="email"
					<?php do_action('calendarista_email_field_attributes'); ?>
					class="form-control calendarista-typography--caption1 calendarista_parsley_validated" 
					size="20" />
		</div>
		<div class="form-group">
			<label class="form-control-label calendarista-typography--caption1" for="calendarista_password<?php echo $this->projectId ?>"><?php echo esc_html($this->stringResources['LOGIN_PASSWORD_LABEL']); ?></label>
			<input 
				type="password" 
				name="password" 
				id="calendarista_password<?php echo $this->projectId ?>"
				data-parsley-required="true" 				
				class="form-control calendarista-typography--caption1 calendarista_parsley_validated" 
				value="" 
				size="20" />
		</div>
		<div class="form-group">
			<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"  target="_blank" class="calendarista-lostpassword"><?php echo esc_html($this->stringResources['LOGIN_FORGOT_PASSWORD_LABEL']); ?></a>
		</div>
		<div class="signon-error parsley-error hide"><?php echo esc_html($this->stringResources['LOGIN_INCORRECT_CREDENTIALS']) ?></div>
	<?php endif; ?>
	<?php foreach($this->formTypes as $formType): ?>
		<?php if($formType === 1): ?>
		<?php $this->renderFormElements($formType);?>
		<?php else: ?>
			<?php for($i = 0; $i < $this->seats; $i++):?>
				<div class="form-group calendarista-row-single">
					<div class="card calendarista-guest-card">
						<div class="card-header">
							<?php echo sprintf($this->stringResources['GUEST_REQUIRED_INFO'], $i+1); ?>
						</div>
						<div class="card-body calendarista-guest-card-body">
							<?php $this->renderFormElements($formType, $i);?>
						</div>
					</div>
				</div>
			<?php endfor;?>
		<?php endif; ?>
	<?php endforeach; ?>
		<?php if($this->availability->guestNameRequired && ($this->availability->selectableSeats && $this->seats)):?>
		<div class="form-group">
			<label class="form-control-label calendarista-typography--caption1">
				<?php echo esc_html($this->stringResources['SEATS_CUSTOMER_NAME_LABEL']) ?>
			</label>
		</div>
		<?php for($i = 0; $i < $this->seats; $i++):?>
		<div class="input-group calendarista-row-single">
			<label for="formelement_<?php echo $this->availability->id ?>_seats_customer_name_<?php echo $i ?>" class="input-group-text">
				<?php echo $i+1; ?>
			</label>
			<input type="text"
					id="formelement_<?php echo $this->projectId ?>_seats_customer_name_<?php echo $i ?>"
					name="formelement_<?php echo $this->projectId ?>_seats_customer_name_<?php echo $i ?>"
					class="form-control calendarista-typography--caption1 calendarista_parsley_validated" 
					value="<?php echo esc_attr($this->getGuestName($i)) ?>"
					placeholder="<?php echo esc_html($this->stringResources['SEATS_CUSTOMER_NAME_PLACEHOLDER']) ?>"
					data-parsley-errors-container=".formelement_<?php echo $this->projectId ?>_seats_customer_name_<?php echo $i ?>_error"
					data-parsley-required="true" />
		</div>
		<div class="formelement_<?php echo $this->projectId ?>_seats_customer_name_<?php echo $i ?>_error"></div>
		<?php endfor; ?>
		<?php endif; ?>
	<?php do_action('calendarista_form_element_extra_charge_notification', $this->uniqueId); ?>
	<?php
	}
}