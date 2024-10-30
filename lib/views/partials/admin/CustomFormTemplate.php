<?php
class Calendarista_CustomFormTemplate extends Calendarista_ViewBase{
	public $formElement;
	public $formElements;
	public $elementTypes;
	public $selectedId;
	public $selectedIndex = 'null';
	public $createNew;
	public $elementSelected;
	public $countryList;
	public $termsIndex = 0;
	function __construct( ){
		parent::__construct();
		$this->selectedId = $this->getFormElementId();
		$this->elementTypes = Calendarista_ElementType::toArray();
		$formElementRepo = new Calendarista_FormElementRepository();
		$this->formElement = new Calendarista_FormElement($this->parseArgs('customform'));
		new Calendarista_CustomFormElementController(
			$this->formElement
			, array($this, 'newForm')
			, array($this, 'sortOrder')
			, array($this, 'created')
			, array($this, 'updated')
			, array($this, 'deleted')
		);
		$this->formElements = new Calendarista_FormElements();
		if($this->selectedProjectId !== -1){
			$this->formElements = $formElementRepo->readAll($this->selectedProjectId);
		}
		if($this->selectedId !== -1 && $this->selectedId !== null){
			$this->formElement = $formElementRepo->read($this->selectedId);
			$this->selectedIndex = $this->getIndex();
		}
		if(!isset($this->formElement->elementType)){
			$this->formElement->elementType = Calendarista_ElementType::TEXTBOX;
		}
		$this->createNew = $this->selectedId === -1 ? true : false;
		$this->countryList = $this->phoneNumberCountryList();
		$this->getProject();
		$this->render();
	}
	public function phoneNumberCountryList(){
		return array('AC','AD','AE','AF','AG','AI','AL','AM','AO','AR','AS','AT','AU','AW','AX','AZ','BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS','BT','BW','BY','BZ','CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EE','EG','EH','ER','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GP','GQ','GR','GT','GU','GW','GY','HK','HN','HR','HT','HU','ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MF','MG','MH','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH','PK','PL','PM','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL','SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ','TA','TC','TD','TG','TH','TJ','TK','TL','TM','TN','TO','TR','TT','TV','TW','TZ','UA','UG','US','UY','UZ','VA','VC','VE','VG','VI','VN','VU','WF','WS','YE','YT','ZA','ZM','ZW');
	}
	protected function getFormElementId(){
		if(isset($_POST['controller']) && $_POST['controller'] === 'customform'){
			return isset($_POST['id']) && trim($_POST['id']) ? (int)$_POST['id'] : -1;
		}
		return -1;
	}
	protected function getIndex(){
		for($i = 0; $i < $this->formElements->count(); $i++){
			$formElement = $this->formElements->item($i);
			if($formElement->id === $this->selectedId){
				return $i;
			}
		}
		return 'null';
	}
	public function newForm($formElement){
		$this->selectedId = -1;
		$this->formElement = new Calendarista_FormElement(array('elementType'=>0));
		$this->newFormNotice();
	}
	public function sortOrder($result){
		if($result){
			$this->sortOrderNotice();
		}
	}
	public function created($formElement, $result){
		$this->selectedId = $formElement->id;
		if($result){
			$this->createdNotice();
		}
	}
	public function updated($formElement, $result){
		$this->selectedId = $formElement->id;
		if($result){
			$this->updatedNotice();
		}
	}
	public function deleted($result){
		$this->selectedId = -1;
		$this->formElement = new Calendarista_FormElement(array('elementType'=>0));
		if($result){
			$this->deletedNotice();
		}
	}
	public function updatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The form field has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function sortOrderNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The form fields sort order has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function createdNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The form field has been created.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function deletedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The form field(s) have been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function newFormNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('Create a new form field.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function errorNotice($message) {
		?>
		<div class="error">
			<p><?php echo sprintf(__('The operation failed unexpected with [%s]. Try again?', 'calendarista'), $message); ?></p>
		</div>
		<?php
	}
	public function getOptions(){
		return $this->formElement->options ? implode(',', $this->formElement->options) : '';
	}
	public function getEmailValidation(){
		return isset($this->formElement->validation['email']) && $this->formElement->validation['email'];
	}
	public function getUrlValidation(){
		return isset($this->formElement->validation['url']) && $this->formElement->validation['url'];
	}
	public function getRequiredValidation(){
		return isset($this->formElement->validation['required']) && $this->formElement->validation['required'];
	}
	public function getDigitsValidation(){
		return isset($this->formElement->validation['digits']) && $this->formElement->validation['digits'];
	}
	public function getNumberValidation(){
		return isset($this->formElement->validation['number']) && $this->formElement->validation['number'];
	}
	public function getAlphaNumValidation(){
		return isset($this->formElement->validation['alphanum']) && $this->formElement->validation['alphanum'];
	}
	public function getMinLengthValidation(){
		return isset($this->formElement->validation['minLength']) && $this->formElement->validation['minLength'] ? 
			$this->formElement->validation['minLength'] : 0;
	}
	public function getMaxLengthValidation(){
		return isset($this->formElement->validation['maxLength']) && $this->formElement->validation['maxLength'] ? 
			$this->formElement->validation['maxLength'] : 0;
	}
	public function getMinValidation(){
		return isset($this->formElement->validation['min']) && $this->formElement->validation['min'] ? 
			$this->formElement->validation['min'] : 0;
	}
	public function getMaxValidation(){
		return isset($this->formElement->validation['max']) && $this->formElement->validation['max'] ? 
			$this->formElement->validation['max'] : 0;
	}
	public function getRegexValidation(){
		return isset($this->formElement->validation['regex']) && $this->formElement->validation['regex'] ? 
			$this->formElement->validation['regex'] : '';
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<p class="description"><?php esc_html_e('Please note, there is no need to add a name and email field. These are already included by the system', 'calendarista') ?></p>
					<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="customform" />
						<input type="hidden" name="id" value="<?php echo $this->formElement->id ?>" />
						<input type="hidden" name="orderIndex" value="<?php echo $this->formElement->orderIndex ?>" />
						<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<div><label for="elementType"><?php esc_html_e('Element type', 'calendarista') ?></label></div>
										<select id="elementType" name="elementType" <?php echo !$this->createNew ? 'disabled' : ''; ?>>
											<?php foreach($this->elementTypes as $key=>$value):?>
											<option value="<?php echo esc_attr($key)?>" 
												<?php echo $this->formElement->elementType === $key ? "selected" : ""?>><?php echo esc_html($value) ?></option>
										   <?php endforeach;?>
										</select>
									</td>
								</tr>
								<?php if($this->formElement->elementType === Calendarista_ElementType::PHONE):?>
								<tr>
									<td>
										<div><label for="country"><?php esc_html_e('Country code', 'calendarista') ?></label></div>
										<select id="country" name="country">
										<?php foreach($this->countryList as $countryCode): ?>
											<option value="<?php echo esc_attr($countryCode) ?>" <?php echo $this->formElement->country === $countryCode ? 'selected' : '' ?>><?php echo esc_html($countryCode) ?></option>
										<?php endforeach; ?>
										</select>
										<p class="description"><?php esc_html_e('As you type formatting and validation will be applied to the selected country code.', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if($this->formElement->elementType === Calendarista_ElementType::TEXTBOX):?>
								<tr>
									<td>
										<p>
											<input id="phoneNumberField" 
												name="phoneNumberField" 
												type="checkbox" 
												<?php echo $this->formElement->phoneNumberField ? 'checked' : '' ?> />
												<?php esc_html_e('This is a phone number field', 'calendarista') ?>
										</p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->formElement->elementType, array(0, 1, 2, 3, 4, 5, 8)) && 
									in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_GROUP_BOOKING)):?>
								<tr>
									<td>
										<label for="guestField">
											<input id="guestField" name="guestField" type="checkbox" <?php echo $this->formElement->guestField ? 'checked' : ''?>>
											<?php esc_html_e('This is a guest field', 'calendarista')?>
										</label>
										<p class="description"><?php esc_html_e('When checked, this field is repeated for each seat selected by the user. Group booking must be enabled on the availability.', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->formElement->elementType, array(0, 1, 2, 3, 4, 5, 8))):?>
								<tr>
									<td>
										<div><label for="label"><?php esc_html_e('Label', 'calendarista') ?></label></div>
										<input id="label" name="label" 
											type="text" 
											class="regular-text" 
											data-parsley-required="true" 
											value="<?php echo esc_attr($this->formElement->label) ?>" />
										<?php if(in_array($this->formElement->elementType, array(4, 5))):?>
										<p class="description"><?php esc_html_e('All the options will be listed under this label', 'calendarista') ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->formElement->elementType, array(Calendarista_ElementType::PLAINTEXT, Calendarista_ElementType::TERMS))):?>
								<tr>
									<td>
										<div><label for="content"><?php esc_html_e('Content', 'calendarista') ?></label></div>
										<textarea name="content" 
											id="content" 
											data-parsley-required="true" 
											class="large-text code" rows="3"><?php echo $this->formElement->content ?></textarea>
										<?php if($this->formElement->elementType === Calendarista_ElementType::TERMS):?>
										<p class="description"><?php esc_html_e('Insert a line with links pointing to your Terms and conditions.', 'calendarista') ?></p>
										<?php endif;?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->formElement->elementType, array(0, 1, 2, 3, 4, 5, 8))):?>
								<tr>
									<td>
										<label for="required">
											<input id="required" name="required" type="checkbox" <?php echo $this->getRequiredValidation() ? 'checked' : ''?>>
											<?php esc_html_e('This field is required', 'calendarista')?>
										</label>
										<?php if(in_array($this->formElement->elementType, array(4, 5))):?>
										<p class="description"><?php esc_html_e('Ensures atleast one option is checked or validation fails', 'calendarista') ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<table class="form-table">
							<tbody>
								<?php if (in_array($this->formElement->elementType, array(
																	Calendarista_ElementType::DROPDOWNLIST, Calendarista_ElementType::MULTISELECT
																	, Calendarista_ElementType::CHECKBOX , Calendarista_ElementType::RADIOBUTTON))):?>
								<tr>
									<td>
										<div><label for="options"><?php esc_html_e('Options', 'calendarista') ?></label></div>
										<textarea name="options" id="options" class="large-text code" rows="3" data-parsley-required="true"><?php echo $this->getOptions(); ?></textarea>
										<p class="description" id="options-description"><?php esc_html_e('Provide a comma separated list eg: apple,grape,banana', 'calendarista')?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if($this->formElement->elementType === Calendarista_ElementType::DROPDOWNLIST):?>
								<tr>
									<td>
										<div><label for="value"><?php esc_html_e('First item', 'calendarista') ?></label></div>
										<input type="text" 
												id="defaultOptionItem"
												name="defaultOptionItem" 
												class="regular-text"
												value="<?php echo esc_attr($this->formElement->defaultOptionItem) ?>" />
										<p class="description" id="value-description"><?php esc_html_e('The first item in the list. Eg: Select an item', 'calendarista')?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if (in_array($this->formElement->elementType, array(Calendarista_ElementType::DROPDOWNLIST, Calendarista_ElementType::MULTISELECT))):?>
								<tr>
									<td>
										<div><label for="value"><?php esc_html_e('Default', 'calendarista') ?></label></div>
										<input type="text" 
												id="defaultSelectedOptionItem"
												name="defaultSelectedOptionItem" 
												class="regular-text"
												value="<?php echo esc_attr($this->formElement->defaultSelectedOptionItem) ?>" />
										<p class="description" id="value-description"><?php esc_html_e('An option to select by default eg: banana', 'calendarista')?></p>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<div class="calendarista-borderless-accordion">
							<div id="advanced_options">
								<h3><?php esc_html_e('Advanced', 'calendarista') ?></h3>
								<table class="form-table">
									<tbody>
										<?php if(in_array($this->formElement->elementType, array(0, 1, 8))):?>
										<tr>
											<td>
												<div><label for="placeHolder"><?php esc_html_e('Place holder', 'calendarista') ?></label></div>
												<input id="placeHolder" name="placeHolder" 
													type="text" 
													class="regular-text" 
													value="<?php echo esc_attr($this->formElement->placeHolder) ?>" />
											</td>
										</tr>
										<?php endif; ?>
										<tr>
											<td>
												<div><label for="className"><?php esc_html_e('CSS class', 'calendarista') ?></label></div>
												<input type="text"
												class="regular-text" 
												id="className"
												name="className"
												value="<?php echo esc_attr($this->formElement->className) ?>" />
											</td>
										</tr>
										<tr>
											<td>
												<fieldset>
													<legend class="screen-reader-text"><span><?php esc_html_e('Line separator', 'calendarista')?></span></legend>
													<label for="lineSeparator">
														<input id="lineSeparator" name="lineSeparator" type="checkbox" <?php echo  $this->formElement->lineSeparator ? 'checked' : ''?>>
														<?php esc_html_e('Show a separator after this field', 'calendarista')?></label>
												</fieldset>
											</td>
										</tr>
										<?php if ($this->formElement->elementType === Calendarista_ElementType::TEXTBOX):?>
										<tr>
											<td>
												<label for="url">
													<input id="url" name="url" type="checkbox" <?php echo $this->getUrlValidation() ? 'checked' : ''?>>
													<?php esc_html_e('Match a valid URL', 'calendarista')?>
												</label>
											</td>
										</tr>
										<?php endif; ?>
										<?php if ( $this->formElement->elementType === Calendarista_ElementType::TEXTBOX ||
														 $this->formElement->elementType === Calendarista_ElementType::TEXTAREA):?>
										<?php if ( $this->formElement->elementType === Calendarista_ElementType::TEXTBOX):?>
										<tr>
											<td>
												<div><label for="entryType"><?php esc_html_e('Allowed value', 'calendarista') ?></label></div>
												<select id="entryType" name="entryType">
													<option value="none"><?php esc_html_e('Any value', 'calendarista') ?></option>
													<option value="digits" <?php echo $this->getDigitsValidation() ? ' selected' : ''?>><?php esc_html_e('Allow only digits from 0-9', 'calendarista') ?></option>
													<option value="number" <?php echo $this->getNumberValidation() ? ' selected' : ''?>><?php esc_html_e('Allow only numbers', 'calendarista') ?></option>
													<option value="alphanum" <?php echo $this->getAlphaNumValidation() ? ' selected' : ''?>><?php esc_html_e('Allow only alphanumeric string', 'calendarista') ?></option>
												</select>
											</td>
										</tr>
										<?php endif; ?>
										<?php if (!$this->getDigitsValidation() && !$this->getNumberValidation()):?>
										<tr>
											<td>
												<input id="minLength" name="minLength" 
														class="small-text"
														data-parsley-trigger="change focusout" 
														data-parsley-type="digits" 
														type="text" 
														value="<?php echo $this->getMinLengthValidation() ?>" />&nbsp;<?php esc_html_e('Min length', 'calendarista') ?>
												<p class="description" id="min-description"><?php esc_html_e('Validates the length of a string is as long as the given limit', 'calendarista')?></p>
											</td>
										</tr>
										<tr>
											<td>
												<input id="maxLength" name="maxLength" 
														class="small-text"
														data-parsley-trigger="change focusout" 
														data-parsley-type="digits" 
														type="text" 
														value="<?php echo $this->getMaxLengthValidation() ?>" />&nbsp;<?php esc_html_e('Max length', 'calendarista') ?>
												<p class="description" id="min-description"><?php esc_html_e('Validates the length of a string is not larger than the given limit', 'calendarista')?></p>
											</td>
										</tr>
										<?php endif; ?>
										<?php if ($this->formElement->elementType === Calendarista_ElementType::TEXTBOX && ($this->getDigitsValidation() || $this->getNumberValidation())):?>
										<tr>
											<td>
												<input id="min" name="min" 
														class="small-text"
														data-parsley-trigger="change focusout" 
														data-parsley-type="<?php echo $this->getNumberValidation() ? 'number' : 'digits'?>" 
														type="text" 
														value="<?php echo $this->getMinValidation() ?>" />&nbsp;<?php esc_html_e('Min', 'calendarista') ?>
												<p class="description" id="min-description"><?php esc_html_e('Validates given number is greater than this min number', 'calendarista')?></p>
											</td>
										</tr>
										<tr>
											<td>
												<input id="max" name="max" 
														class="small-text"
														data-parsley-trigger="change focusout" 
														data-parsley-type="<?php echo $this->getNumberValidation() ? 'number' : 'digits'?>" 
														type="text" 
														value="<?php echo $this->getMaxValidation() ?>" />&nbsp;<?php esc_html_e('Max', 'calendarista') ?>
												<p class="description" id="min-description"><?php esc_html_e('Validates given number is less than this max number', 'calendarista')?></p>
											</td>
										</tr>
										<?php endif; ?>
										<tr>
											<td>
												<div><label for="regex"><?php esc_html_e('Regex', 'calendarista') ?></label></div>
												<textarea id="regex" name="regex" 
														class="large-text code"  
														rows="5"><?php echo $this->getRegexValidation() ?></textarea>
											</td>
										</tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
						<p class="submit">
							<?php if(!$this->createNew):?>
							<input type="submit" name="calendarista_new" id="calendarista_new" class="button button-primary" value="<?php esc_html_e('New', 'calendarista') ?>">
							<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button" value="<?php esc_html_e('Delete', 'calendarista') ?>">
							<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>"
							<?php else:?>
							<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Create', 'calendarista') ?>">
							<?php endif;?>
						</p>
					</form>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right calendarista-widgets-right">
			<div id="widgets-right">
				<div class="single-sidebar">
					<div class="sidebars-column-1">
						<div class="widgets-holder-wrap">
							<div class="widgets-sortables ui-droppable ui-sortable">
								<?php if($this->formElements->count() > 0):?>
								<div class="sidebar-name">
									<h3><?php esc_html_e('Preview', 'calendarista') ?></h3>
								</div>
								<div class="sidebar-description">
									<p class="description">
										<?php esc_html_e('A rough preview of form fields. Actual form displayed in front-end use bootstrap and are styled differently.', 'calendarista')?>
									</p>
								</div>
								<table class="form-table">
									<tbody>
										<?php foreach($this->formElements as $formElement):?>
											<?php if(in_array($formElement->elementType, array(0, 1, 2, 3, 8))): ?>
												<tr>
													<td>
														<div>
															<strong>
																<label for="<?php echo $formElement->id ?>"><?php echo esc_html($formElement->label) ?></label>
															</strong>
														</div>
														<?php echo do_action('calendarista_form_element_info', $formElement->id); ?>
														<div>
														<?php if($formElement->elementType === Calendarista_ElementType::TEXTBOX ||
																	$formElement->elementType === Calendarista_ElementType::PHONE): ?>
															<input type="text"
																	class="<?php echo esc_attr($formElement->className) ?>" 
																	placeholder="<?php echo esc_attr($formElement->placeHolder) ?>"/>
														<?php elseif($formElement->elementType === Calendarista_ElementType::TEXTAREA): ?>
															<textarea class="large-text code <?php echo $formElement->className ?>" placeholder="<?php echo $formElement->placeHolder ?>" rows="3"></textarea>
														<?php elseif($formElement->elementType === Calendarista_ElementType::DROPDOWNLIST || $formElement->elementType === Calendarista_ElementType::MULTISELECT): ?>
															<select 
																	class="<?php echo $formElement->className ?>"
																	<?php echo $formElement->elementType === Calendarista_ElementType::MULTISELECT ? 'multiple="multiple"' : '' ?>>
																	<?php if($formElement->defaultOptionItem):?>
																	<option value=""><?php echo $formElement->defaultOptionItem; ?></option>
																	<?php endif; ?>
																	<?php foreach($formElement->options as $key=>$value):?>
																		<option value="<?php echo esc_attr($value) ?>" <?php echo $formElement->defaultSelectedOptionItem === $value ? 'selected="selected"' : '' ?>><?php echo esc_html($value) ?></option>
																	<?php endforeach;?>
															</select>
														<?php endif; ?>
														</div>
													</td>
												</tr>
											<?php elseif(in_array($formElement->elementType, array(4, 5))): ?>
												<tr>
													<td>
														<?php if($formElement->options):?>
															<div>
																<strong>
																	<label for="<?php echo $formElement->id ?>"><?php echo esc_html($formElement->label) ?></label>
																</strong>
															</div>
															<?php echo do_action('calendarista_form_element_info', $formElement->id); ?>
															<?php foreach($formElement->options as $key=>$value):?>
															<div>
																<label for="formelement_<?php echo $formElement->id ?>">
																	<input id="formelement_<?php echo $formElement->id ?>" 
																		name="formelement_<?php echo $formElement->id ?>"   
																		type="<?php echo $formElement->elementType === Calendarista_ElementType::CHECKBOX ? 'checkbox' : 'radio' ?>"
																		class="<?php echo esc_attr($formElement->className) ?>"/>
																			<?php echo $value ?>
																</label>
															</div>
															<?php endforeach;?>
														<?php else: ?>
														<div>
																<label for="formelement_<?php echo $formElement->id ?>">
																	<input id="formelement_<?php echo $formElement->id ?>" 
																		name="formelement_<?php echo $formElement->id ?>"   
																		type="<?php echo $formElement->elementType === Calendarista_ElementType::CHECKBOX ? 'checkbox' : 'radio' ?>"
																		class="<?php echo esc_attr($formElement->className) ?>"/>
																			<?php echo esc_html($formElement->label) ?>
																</label>
															</div>
															<?php echo do_action('calendarista_form_element_info', $formElement->id); ?>
														<?php endif; ?>
													</td>
												</tr>
											<?php elseif($formElement->elementType === Calendarista_ElementType::PLAINTEXT): ?>
												<tr>
													<td>
														<?php echo do_action('calendarista_form_element_info', $formElement->id); ?>
														<p <?php if($formElement->className):?>
														class="<?php echo esc_attr($formElement->className) ?>"
														<?php endif;?>>
														<?php echo wp_kses_post($formElement->content) ?>
														</p>
													</td>
												</tr>
											<?php elseif($formElement->elementType === Calendarista_ElementType::TERMS): ?>
												<tr>
													<td>
														<?php echo do_action('calendarista_form_element_info', $formElement->id); ?>
														<input id="formelement_<?php echo $formElement->id ?>"
														type="checkbox"
														<?php if($formElement->className):?>
														class="<?php echo esc_attr($formElement->className) ?>"
														<?php endif;?> />
														<?php echo wp_kses_post($formElement->content) ?>
													</td>
												</tr>
											<?php endif;?>
											<?php if($formElement->lineSeparator):?>
											<tr>
												<td>
												<hr />
												</td>
											</tr>
											<?php endif; ?>
										<?php endforeach;?>
									</tbody>
								</table>
								<?php endif; ?>
								<div class="sidebar-name">
									<h3><?php esc_html_e('Custom form fields', 'calendarista') ?></h3>
								</div>
								<div class="sidebar-description">
									<p class="description">
										<?php esc_html_e('List of custom form fields below. Drag and drop to rearrange the order.', 'calendarista')?>
									</p>
								</div>
								<?php if($this->formElements->count() > 0):?>
								<form id="calendarista_form_list" action="<?php echo esc_url($this->requestUrl) ?>" method="post">
									<input type="hidden" name="controller" value="customform" />
									<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
									<input type="hidden" name="sortOrder" />
									<div class="column-borders">
										<div class="clear"></div>
										<div class="accordion-container">
											<ul class="outer-border">
												<?php foreach($this->formElements as $formElement):?>
												<li class="control-section accordion-section">
													<h3 class="accordion-section-title <?php echo $this->selectedId === $formElement->id ? 'calendarista-accordion-selected' : '' ?>" tabindex="0">
														<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
														<input id="checkbox_<?php echo $formElement->id ?>" type="checkbox" name="formelements[]" value="<?php echo $formElement->id ?>"> 
														<span title="<?php echo strlen($formElement->label) > 15 ? esc_attr($formElement->label) : '' ?>">
														<?php
															switch($formElement->elementType){
																case 0:
																case 1:
																case 2:
																case 3:
																case 4:
																case 5:
																echo $formElement->label ? esc_html($this->trimString($formElement->label, 15)) . ' - ' : '';
																break;
															}
														?>
														</span>
														<?php echo $this->elementTypes[$formElement->elementType]; ?>
														<?php echo $formElement->elementType === 7/*terms*/ ? '&nbsp;#' . ++$this->termsIndex : '' ?>
														<button type="submit" class="edit-linkbutton alignright" name="id" value="<?php echo $formElement->id; ?>">
															[<?php esc_html_e('Edit', 'calendarista') ?>]
														</button>
														<br class="clear">
													</h3>
												</li>
												<?php endforeach;?>
											</ul>
											<p class="alignright">
												<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button button-primary" value="<?php esc_html_e('Delete', 'calendarista') ?>" disabled>
												<input type="submit" name="calendarista_sortorder" id="calendarista_sortorder" class="button button-primary optional-sort-button" value="<?php esc_html_e('Save order', 'calendarista') ?>" disabled>
											</p>
											<br class="clear">
										</div>
									</div>
								</form>
								<?php else:?>
								<hr>
								<div class="empty-records">
									<p>
										<?php esc_html_e('Empty. No custom form fields found.', 'calendarista')?>
									</p>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.customForm = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
					$('.accordion-container ul').accordion({
						  collapsible: true
						  , active: <?php echo $this->selectedIndex ?>
					}).sortable({
						axis: 'y'
						, handle: '.calendarista-drag-handle'
						, stop: function( event, ui ) {
							var $this = $(this);
							context.updateSortOrder();
						  // IE doesn't register the blur when sorting
						  // so trigger focusout handlers to remove .ui-state-focus
						  ui.item.children('h3').triggerHandler('focusout');
						  // Refresh accordion to handle new order
						  $this.accordion('refresh');
						  $this.accordion({active: ui.item.index()});
						}
					 });
				});
			};
			calendarista.customForm.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.requestUrl = options['requestUrl'];
				this.$form = $('#calendarista_form');
				this.$sortItems = $('.accordion-container').find('li');
				this.$elementTypeList = $('#elementType');
				this.$entryTypeList = $('#entryType');
				this.$email = $('#email');
				this.$sortOrder = $('input[name="sortOrder"]');
				this.$updateSortOrderButton = $('input[name="calendarista_sortorder"]');
				this.$customFormFieldCheckboxes = $('#calendarista_form_list input[type="checkbox"]');
				this.$deleteCustomFormFieldsButton = $('#calendarista_form_list input[name="calendarista_delete"]');
				this.$customFormInputFields = $('.control-section.accordion-section input[type="checkbox"], .control-section.accordion-section button[type="submit"]');
				this.submitFormDelegate = calendarista.createDelegate(this, this.submitForm);
				this.$customFormInputFields.on('click', function(e){
					e.stopPropagation();
				});
				this.$customFormFieldCheckboxes.on('change', function(e){
					var hasChecked = context.$customFormFieldCheckboxes.is(':checked');
					if(hasChecked){
						context.$deleteCustomFormFieldsButton.prop('disabled', false);
					}else{
						context.$deleteCustomFormFieldsButton.prop('disabled', true);
					}
				});
				this.$email.on('change', this.submitFormDelegate);
				this.$entryTypeList.on('change', this.submitFormDelegate);
				this.$elementTypeList.on('change', this.submitFormDelegate);
				$('#advanced_options').accordion({
					collapsible: true
					, active: false
					, heightStyle: 'content'
					, autoHeight: false
					, clearStyle: true
				});
			};
			calendarista.customForm.prototype.submitForm = function(e){
				this.$form[0].submit();
			};
			calendarista.customForm.prototype.updateSortOrder = function(){
				var i
					, sortOrder = [];
				for(i = 0; i < this.$sortItems.length; i++){
					$item = $(this.$sortItems[i]);
					sortOrder.push($item.find('button[name="id"]').val() + ':' + $item.index());
				}
				this.$sortOrder.val(sortOrder.join(','));
				this.$updateSortOrderButton.prop('disabled', false);
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.customForm({
				<?php echo $this->requestUrl ?>'
		});
		</script>
		<?php
	}
}