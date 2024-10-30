<?php
class Calendarista_GeneralSettingsTemplate extends Calendarista_ViewBase{
	public $setting;
	public $weekDays;
	public $shortDateFormats;
	public $pages;
	public $demoRequest;
	public $themes;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		new Calendarista_GeneralSettingsController(
			array($this, 'createdSetting')
			, array($this, 'updatedSetting')
			, array($this, 'deletedSetting')
		);
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		$this->setting = $generalSettingsRepository->read();
		$this->weekDays = $this->getWeekDays();
		$this->shortDateFormats = array(
			'dd/mm/yy'
			, 'dd.mm.yy'
			, 'd. m. yy'
			, 'yy-mm-dd'
			, 'd.m.yy'
			, 'dd-mm-yy'
			, 'yy/mm/dd'
			, 'mm/dd/yy'
			, 'd M, y'
			, 'DD, d MM, yy'
		);
		$this->timeFormats = Calendarista_TimeFormat::toArray();
		$this->pages = self::getPages();
		$this->demoRequest = apply_filters('calendarista_demo_request', null);
		$this->themes = Calendarista_StyleHelper::getThemes();
		$this->render();
	}
	public function upperCaseWords($value){
		return ucwords(join(' ', explode('_', $value)));
	}
	public static function getPages(){
		$result = array();
		try{
			$pages = get_pages();
			foreach($pages as $page){
				$localPage = get_post_meta($page->ID, CALENDARISTA_META_KEY_NAME, true);
				if($localPage != ''){
					continue;
				}
				array_push($result, array('name'=>$page->post_title, 'id'=>$page->ID));
			}
		}catch(Exception $e){
			Calendarista_ErrorLogHelper::insert($e->getMessage());
		}
		return $result;
	}
	protected function getThemes($themes, $themeRoot){
		//if using a child theme then the theme has to be defined there 
		//and not in the parent theme
		if(file_exists($themeRoot)){
			$children = glob($themeRoot . '*' , GLOB_ONLYDIR);
			foreach($children as $child){
				$name = basename($child);
				$themes[$name] = $name;
			}
		}
		return $themes;
	}
	
	protected function getWeekDays(){
		return array(
			__('Sunday', 'calendarista')
			, __('Monday', 'calendarista')
			, __('Tuesday', 'calendarista')
			, __('Wednesday', 'calendarista')
			, __('Thursday', 'calendarista')
			, __('Friday', 'calendarista')
			, __('Saturday', 'calendarista')
			, __('Default', 'calendarista')
		);
	}
	public function render(){
	?>
		<div class="wrap">
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_generalsettings"/>
				<input type="hidden" name="id" value="<?php echo $this->setting->id ?>"/>
				<input type="hidden" name="currency" value="<?php echo $this->setting->currency ?>"/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Auto approve booking', 'calendarista')?></label>
							</th>
							<td>
								<input name="autoApproveBooking" type="hidden" value="0">
								<input name="autoApproveBooking" type="checkbox" 
										<?php echo $this->setting->autoApproveBooking ? "checked" : ""?> /> 
									<?php esc_html_e('Approve and confirm booking always', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Auto approve after payment', 'calendarista')?></label>
							</th>
							<td>
								<input name="autoConfirmOrderAfterPayment" type="hidden" value="0">
								<input name="autoConfirmOrderAfterPayment" type="checkbox" 
								<?php echo $this->setting->autoConfirmOrderAfterPayment ? "checked" : ""?> /> 
									<?php esc_html_e('Approve and confirm booking only after payment', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Payment required notification', 'calendarista')?></label>
							</th>
							<td>
								<input name="autoInvoiceNotification" type="hidden" value="0">
								<input name="autoInvoiceNotification" 
									type="checkbox" <?php echo $this->setting->autoInvoiceNotification ? "checked" : ""?> /> 
									<?php esc_html_e('Send payment required notification automatically after booking', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Notify admin', 'calendarista')?></label>
							</th>
							<td>
							<input name="autoNotifyAdminNewBooking" type="hidden" value="0">
							<input name="autoNotifyAdminNewBooking" 
									type="checkbox" <?php echo $this->setting->autoNotifyAdminNewBooking ? "checked" : ""?> /> 
									<?php esc_html_e('Send new booking notification to admin or staff', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Notify customer', 'calendarista')?></label>
							</th>
							<td>
								<input name="notifyBookingReceivedSuccessfully" type="hidden" value="0">
								<input name="notifyBookingReceivedSuccessfully" 
									type="checkbox" <?php echo $this->setting->notifyBookingReceivedSuccessfully ? "checked" : ""?> /> 
									<?php esc_html_e('Notify customer that the booking was received successfully', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Out of stock notification', 'calendarista')?></label>
							</th>
							<td>
								<input name="outOfStockNotification" type="hidden" value="0">
								<input name="outOfStockNotification" 
									type="checkbox" <?php echo $this->setting->outOfStockNotification ? "checked" : ""?> /> 
									<?php esc_html_e('Notify customer about a race condition that the appointment has already been booked by someone else during the booking phase.', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Booking confirmation', 'calendarista')?></label>
							</th>
							<td>
								<input name="notifyBookingConfirmation" type="hidden" value="0">
								<input name="notifyBookingConfirmation" 
									type="checkbox" <?php echo $this->setting->notifyBookingConfirmation ? "checked" : ""?> /> 
									<?php esc_html_e('Notify customer that their booking is confirmed', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Enable booking cancellation', 'calendarista')?></label>
							</th>
							<td>
							<input name="enableUserCancelBooking" type="hidden" value="0">
							<input name="enableUserCancelBooking" 
								type="checkbox" <?php echo $this->setting->enableUserCancelBooking ? "checked" : ""?> /> 
									<?php esc_html_e('Allow customer to cancel their appointment within', 'calendarista')?>
							<input name="cancellationPolicy" type="hidden" value="0">
							<input id="cancellationPolicy" name="cancellationPolicy" 
								class="small-text" 
								data-parsley-type="digits"
								value="<?php echo $this->setting->cancellationPolicy ?>"/>
							<?php esc_html_e('mins of booking.', 'calendarista') ?>
							<p class="description">
							<?php esc_html_e('A value of 0 mins means customer can cancel at anytime.', 'calendarista') ?>
							</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Enable cancellation alert', 'calendarista')?></label>
							</th>
							<td>
							<input name="enableCancelBookingAlert" type="hidden" value="0">
							<input id="enableCancelBookingAlert" name="enableCancelBookingAlert" 
								type="checkbox" <?php echo $this->setting->enableCancelBookingAlert ? "checked" : ""?> /> 
									<?php esc_html_e('Send a cancel notification to admin when a customer cancels their booking.', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Enable cancellation notification', 'calendarista')?></label>
							</th>
							<td>
							<input name="customerBookingCancelNotification" type="hidden" value="0">
							<input id="customerBookingCancelNotification" name="customerBookingCancelNotification" 
								type="checkbox" <?php echo $this->setting->customerBookingCancelNotification ? "checked" : ""?> /> 
									<?php esc_html_e('Send a cancel notification to a customer when their booking has been cancelled by staff.', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Enable cancel notification upon delete', 'calendarista')?></label>
							</th>
							<td>
							<input name="enableCancelNotificationOnDelete" type="hidden" value="0">
							<input id="enableCancelNotificationOnDelete" name="enableCancelNotificationOnDelete" 
								type="checkbox" <?php echo $this->setting->enableCancelNotificationOnDelete ? "checked" : ""?> /> 
									<?php esc_html_e('Send a cancel notification to customer when admin or staff delete an unconfirmed booking.', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Notify Booking Changed', 'calendarista')?></label>
							</th>
							<td>
								<input name="notifyBookingHasChanged" type="hidden" value="0">
								<input name="notifyBookingHasChanged" 
									type="checkbox" <?php echo $this->setting->notifyBookingHasChanged ? "checked" : ""?> /> 
									<?php esc_html_e('After editing an appointment, notify customer that the booking has changed', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Enable mobile initial scale', 'calendarista')?></label>
							</th>
							<td>
							<input name="enableMobileInitialScale" type="hidden" value="0">
							<input name="enableMobileInitialScale" 
									type="checkbox" <?php echo $this->setting->enableMobileInitialScale ? "checked" : ""?> /> 
									<?php esc_html_e('Enable responsive features', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Display steps in mobile view', 'calendarista')?></label>
							</th>
							<td>
							<input name="displayStepsMobileView" type="hidden" value="0">
							<input id="displayStepsMobileView" name="displayStepsMobileView" 
								type="checkbox" <?php echo $this->setting->displayStepsMobileView ? "checked" : ""?> /> 
									<?php esc_html_e('A dropdownlist is displayed to enable navigating between steps in mobile view.', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="shorthandDateFormat"><?php esc_html_e('Date formats', 'calendarista')?></label></th>
							<td>
								<select name="shorthandDateFormat" 
									id="shorthandDateFormat">
									<?php foreach($this->shortDateFormats as $shortDateFormat):?>
									<option value="<?php echo esc_attr($shortDateFormat) ?>" <?php echo $this->setting->shorthandDateFormat == $shortDateFormat ? 'selected' : '' ?>><?php echo esc_html($shortDateFormat) ?></option>
									<?php endforeach;?>
								</select>
								<p class="description"><?php esc_html_e('The date format to show in the front-end datepicker field', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="timeFormat"><?php esc_html_e('Time formats', 'calendarista')?></label></th>
							<td>
								<select name="timeFormat" 
									id="timeFormat">
									<?php foreach($this->timeFormats as $key=>$value):?>
									<option value="<?php echo esc_attr($value) ?>" <?php echo $this->setting->timeFormat == $value ? 'selected' : '' ?>><?php echo esc_html($key)?></option>
									<?php endforeach;?>
								</select>
								<p class="description"><?php esc_html_e('The time format to show in the front-end time field', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e('First day of week', 'calendarista')?></label></th>
							<td>
								<select
										id="firstDayOfWeek"
										name="firstDayOfWeek"> 
										<?php foreach($this->weekDays as $key=>$value):?>
											<option value="<?php echo $key ?>" <?php echo $this->setting->firstDayOfWeek == $key ? 'selected' : '' ?>><?php echo $value ?></option>
										<?php endforeach;?>
								</select>
							</td>
						</tr>
						<?php if(count($this->pages) > 0): ?>
						<tr>
							<th scope="row"><label for="confirmUrl"><?php esc_html_e('Booking confirmation url', 'calendarista')?></label></th>
							<td>
								<select name="confirmUrl">
									<option value=""><?php esc_html_e('Same page', 'calendarista') ?></option>
									<?php foreach($this->pages as $page): ?>
									<option value="<?php echo esc_attr($page['id']) ?>" <?php echo $this->setting->confirmUrl === $page['id'] ? 'selected' : '' ?>><?php echo esc_html($this->trimString($page['name'], 32)) ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e('You may use the following short-code on your confirmation page: [calendarista-confirmation]', 'calendarista') ?></p>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<th scope="row"><label for="tax"><?php esc_html_e('Tax', 'calendarista')?></label></th>
							<td>
							<input type="text" 
								class="small-text"  
								data-parsley-pattern="^\d+(\.\d{1,2})?$"
								data-parsley-min="0"
								data-parsley-trigger="change" 
								id="tax"
								name="tax" value="<?php echo esc_attr($this->setting->tax) ?>"/>%
								<label>
									<input type="radio" name="taxMode" value="0" <?php echo $this->setting->taxMode === 0 ? 'checked' : '' ?>>
									<?php esc_html_e('Exclusive', 'calendarista') ?>
								</label>
								<label>
									<input type="radio" name="taxMode" value="1" <?php echo $this->setting->taxMode === 1 ? 'checked' : '' ?>>
									<?php esc_html_e('Inclusive', 'calendarista') ?>
								</label>
							</td>
						</tr>
						<?php if(!$this->demoRequest): ?>
						<tr>
							<th scope="row"><label for="googleMapsKey"><?php esc_html_e('Google Maps API Key', 'calendarista') ?></label></th>
							<td>
								<input id="googleMapsKey" 
									name="googleMapsKey" 
									type="text" 
									class="regular-text" 
									value="<?php echo esc_attr($this->setting->googleMapsKey) ?>" />
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<th scope="row"><label><?php esc_html_e('Translation plugins', 'calendarista') ?></label></th>
							<td>
								<input
									name="translationEngine" 
									type="radio" 
									value="0" 
									<?php echo $this->setting->translationEngine === 0 ? 'checked' : '' ?>/>
									<?php esc_html_e('None', 'calendarista'); ?>
								<input
									name="translationEngine" 
									type="radio" 
									value="3" 
									<?php echo $this->setting->translationEngine === 3 ? 'checked' : '' ?>/>
									<?php esc_html_e('Manual translations (poEdit or other)', 'calendarista'); ?>
								<p class="description"><strong><?php esc_html_e('Note', 'calendarista') ?>:</strong> <?php esc_html_e('Dynamic string translation such as labels and content found in custom form field, optional extras and email notifications require either WPML or Polylang.', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="prefix"><?php esc_html_e('Invoice prefix', 'calendarista') ?></label></th>
							<td>
								<input id="prefix" 
									name="prefix" 
									type="text" 
									class="small-text" 
									data-parsley-required="true"
									data-parsley-type="alphanum"
									value="<?php echo esc_attr($this->setting->prefix) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="approvedColor"><?php esc_html_e('Approved appointment', 'calendarista') ?></label></th>
							<td>
								<input id="approvedColor" 
									name="approvedColor" 
									type="text" 
									class="regular-text" 
									placeholder="#000" 
									value="<?php echo esc_attr($this->setting->approvedColor) ?>" />
								<p class="description"><?php esc_html_e('An approved appointment will have this color', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="pendingApprovalColor"><?php esc_html_e('Pending approval appointment', 'calendarista') ?></label></th>
							<td>
								<input id="pendingApprovalColor" 
									name="pendingApprovalColor" 
									type="text" 
									class="regular-text" 
									placeholder="#000" 
									value="<?php echo esc_attr($this->setting->pendingApprovalColor) ?>" />
								<p class="description"><?php esc_html_e('A pending approval appointment will have this color', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="cancelledColor"><?php esc_html_e('Cancelled appointment', 'calendarista') ?></label></th>
							<td>
								<input id="cancelledColor" 
									name="cancelledColor" 
									type="text" 
									class="regular-text" 
									placeholder="#000" 
									value="<?php echo esc_attr($this->setting->cancelledColor) ?>" />
								<p class="description"><?php esc_html_e('A cancelled appointment will have this color', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="searchFilterFindButtonLabel"><?php esc_html_e('Search filter Find button label', 'calendarista') ?></label></th>
							<td>
								<input id="searchFilterFindButtonLabel" 
									name="searchFilterFindButtonLabel" 
									type="text" 
									class="regular-text" 
									value="<?php echo esc_attr($this->setting->searchFilterFindButtonLabel) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="searchFilterSelectButtonLabel"><?php esc_html_e('Search filter Select button label', 'calendarista') ?></label></th>
							<td>
								<input id="searchFilterSelectButtonLabel" 
									name="searchFilterSelectButtonLabel" 
									type="text" 
									class="regular-text" 
									value="<?php echo esc_attr($this->setting->searchFilterSelectButtonLabel) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="searchFilterSoldOutLabel"><?php esc_html_e('Search filter Sold Out label', 'calendarista') ?></label></th>
							<td>
								<input id="searchFilterSoldOutLabel" 
									name="searchFilterSoldOutLabel" 
									type="text" 
									class="regular-text" 
									value="<?php echo esc_attr($this->setting->searchFilterSoldOutLabel) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="searchFilterAlternateDateLabel"><?php esc_html_e('Search filter alternate date found label', 'calendarista') ?></label></th>
							<td>
								<input id="searchFilterAlternateDateLabel" 
									name="searchFilterAlternateDateLabel" 
									type="text" 
									class="regular-text" 
									value="<?php echo esc_attr($this->setting->searchFilterAlternateDateLabel) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Allow alternate dates in result', 'calendarista')?></label>
							</th>
							<td>
								<input name="searchIncludeAlternateDates" type="hidden" value="0">
								<input name="searchIncludeAlternateDates" 
									type="checkbox" <?php echo $this->setting->searchIncludeAlternateDates ? "checked" : ""?> /> 
									<?php esc_html_e('Search result will include availability if alternate dates are found', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Allow sold out dates in result', 'calendarista')?></label>
							</th>
							<td>
								<input name="searchIncludeSoldoutDates" type="hidden" value="0">
								<input name="searchIncludeSoldoutDates" 
									type="checkbox" <?php echo $this->setting->searchIncludeSoldoutDates ? "checked" : ""?> /> 
									<?php esc_html_e('Search result will include availability with sold out message, if no dates are found', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="searchFilterTheme"><?php esc_html_e('Search filter theme', 'calendarista') ?></label>
							</th>
							<td>
								<select
									id="searchFilterTheme" 
									name="searchFilterTheme">
										<?php foreach($this->themes as $key=>$value):?>
											<option value="<?php echo esc_attr($key) ?>" style="background-color: <?php echo esc_attr($value) ?>;" <?php echo $this->setting->searchFilterTheme === $key ? 'selected' : null?>><?php echo esc_html($this->upperCaseWords($key)) ?></option>
										<?php endforeach; ?>
									</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="fontSize"><?php esc_html_e('Font Size', 'calendarista') ?></label></th>
							<td>
								<input id="fontSize" 
									name="fontSize" 
									type="text" 
									class="small-text" 
									data-parsley-pattern="^\d+(\.\d{1,2})?$"
									value="<?php echo esc_attr($this->setting->fontSize) ?>" />em
								<p class="description"><?php esc_html_e('The font-size affects the booking form. The default value is around 0.75em. Increase or decrease as desired.', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Add to calendar links', 'calendarista')?></label>
							</th>
							<td>
							<input name="displayAddToCalendarOption" type="hidden" value="0">
							<input id="displayAddToCalendarOption" name="displayAddToCalendarOption" 
								type="checkbox" <?php echo $this->setting->displayAddToCalendarOption ? "checked" : ""?> /> 
									<?php esc_html_e('Display add to Calendar links after the booking, in the confirmation message', 'calendarista')?>
							</td>
						</tr>
					</body>
				</table>
				<p class="submit">
				<?php if($this->setting->id === -1) :?>
					<button class="button button-primary" name="calendarista_create"><?php esc_html_e('Save', 'calendarista') ?></button>
				<?php else:?>
					<button class="button button-primary" 
							name="calendarista_update" 
							value="<?php echo $this->setting->id?>">
							<?php esc_html_e('Save', 'calendarista') ?>
					</button>
					<button class="button button-primary" 
							name="calendarista_delete" 
							value="<?php echo $this->setting->id?>">
							<?php esc_html_e('Reset', 'calendarista') ?>
					</button>
				<?php endif;?>
				</p>
			</form>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.generalSettings = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.generalSettings.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.$autoApproveBooking = $('input[name="autoApproveBooking"]');
					this.$autoConfirmOrderAfterPayment = $('input[name="autoConfirmOrderAfterPayment"]');
					this.$shortDateFormats = $('select[name="shorthandDateFormat"] option');
					this.$enableCancel = $('input[name="enableUserCancelBooking"]');
					this.$cancellationPolicy = $('#cancellationPolicy');
					this.$enableCancelBookingAlert = $('#enableCancelBookingAlert');
					this.$approvedColor = $('input[name="approvedColor"]');
					this.$pendingApprovalColor = $('input[name="pendingApprovalColor"]');
					this.$cancelledColor = $('input[name="cancelledColor"]');
					this.requestUrl = options['requestUrl'];
					if(this.$autoApproveBooking.is(':checked')){
						this.$autoConfirmOrderAfterPayment.prop('checked', false);
						this.$autoConfirmOrderAfterPayment.prop('disabled', true);
					}
					this.$autoApproveBooking.on('change', function(){
						if(this.checked){
							context.$autoConfirmOrderAfterPayment.prop('checked', false);
							context.$autoConfirmOrderAfterPayment.prop('disabled', true);
						}else{
							context.$autoConfirmOrderAfterPayment.prop('disabled', false);
						}
					});
					this.$enableCancel.on('change', function(){
						if(!this.checked){
							context.$cancellationPolicy.val('0');
							context.$cancellationPolicy.prop('disabled', true);
							context.$enableCancelBookingAlert.prop('checked', false);
							context.$enableCancelBookingAlert.prop('disabled', true);
							return;
						}
						context.$cancellationPolicy.prop('disabled', false);
						context.$enableCancelBookingAlert.prop('disabled', false);
					});
					this.setDateFormats();
					$('#appointment_color').accordion({
						collapsible: true
						, active: false
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
					this.$approvedColor.wpColorPicker();
					this.$pendingApprovalColor.wpColorPicker();
					this.$cancelledColor.wpColorPicker();
				};
				calendarista.generalSettings.prototype.setDateFormats = function(){
					var i
						, dateFormat
						, $option;
					for(i = 0; i < this.$shortDateFormats.length; i++){
						$option = $(this.$shortDateFormats[i]);
						dateFormat = jQuery.datepicker.formatDate($option.val(), new Date());
						$option.text(dateFormat + ' - ' + $option.val());
					}
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.generalSettings({<?php echo $this->requestUrl ?>'});
		</script>
	<?php
	}
}