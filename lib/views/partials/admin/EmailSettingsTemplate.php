<?php
class Calendarista_EmailSettingsTemplate extends Calendarista_ViewBase{
	public $templates;
	public $emailSetting;
	public $generalSetting;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		new Calendarista_EmailSettingsController(
			array($this, 'emailSettingNotification')
			, array($this, 'deletedSetting')
		);
		$repo = new Calendarista_GeneralSettingsRepository();
		$this->generalSetting = $repo->read();
		$this->templates = Calendarista_EmailTemplateHelper::getTemplates();
		$this->emailType = isset($_POST['emailType']) ? (int)$_POST['emailType'] : 1;
		$repo = new Calendarista_EmailSettingRepository($this->emailType);
		$this->emailSetting = $repo->read();
		if(!$this->emailSetting){
			$this->emailSetting = Calendarista_EmailTemplateHelper::getTemplate($this->emailType);
		}
		$this->render();
	}
	public function emailSettingNotification($result, $errorMessage) {
		if($errorMessage):
		?>
		<div class="wrap">
			<div class="calendarista-notice error notice is-dismissible">
				<p><?php echo sprintf(__('An error has occurred: %s. The changes made were not applied.', 'calendarista'), $errorMessage) ?></p>
			</div>
			<hr>
		</div>
		<?php
		else:
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The changes were applied successfully.', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
		endif;
	}
	public function render(){
	?>
		<div class="wrap">
			<form action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_emailsettings">
				<input type="hidden" name="id" value="<?php echo $this->emailSetting->id ?>"/>
				<input type="hidden" name="name" value="<?php echo esc_attr($this->emailSetting->name) ?>"/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="emailSenderName">
									<?php esc_html_e('Sender E-mail Name', 'calendarista')?>
								</label>
							</th>
							<td>
								<input type="text" 
										class="regular-text calendarista_parsley_validated"  
										data-parsley-maxlength="256"
										data-parsley-trigger="change" 
										id="emailSenderName"
										name="emailSenderName" 
										value="<?php echo esc_attr($this->generalSetting->emailSenderName) ?>"/> 
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="senderEmail">
									<?php esc_html_e('Sender E-mail', 'calendarista')?>
								</label>
							</th>
							<td>
								<input type="text" 
										class="regular-text calendarista_parsley_validated"  
										data-parsley-maxlength="256"
										data-parsley-trigger="change" 
										data-parsley-type="email"
										id="senderEmail"
										name="senderEmail" 
										value="<?php echo esc_attr($this->generalSetting->senderEmail) ?>"/> 
								<p class="description"><?php esc_html_e('Displayed on all emails as the sender', 'calendarista')?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="adminNotificationEmail">
									<?php esc_html_e('Admin notification E-mail', 'calendarista')?>
								</label>
							</th>
							<td>
								<input type="text" 
										class="regular-text calendarista_parsley_validated"  
										data-parsley-maxlength="256"
										data-parsley-trigger="change" 
										data-parsley-type="email"
										id="adminNotificationEmail"
										name="adminNotificationEmail" 
										value="<?php echo esc_attr($this->generalSetting->adminNotificationEmail) ?>"/> 
								<p class="description"><?php esc_html_e('The email address to which to send admin notifications. For best results, do not use the same email used in sender email field above.', 'calendarista')?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="utf8EncodeEmailSubject">
									<?php esc_html_e('UTF-8 Encode email subject', 'calendarista')?>
								</label>
							</th>
							<td>
								<input name="utf8EncodeEmailSubject" type="hidden" value="0">
								<input type="checkbox"  
									id="utf8EncodeEmailSubject"
									name="utf8EncodeEmailSubject" 
								<?php echo $this->generalSetting->utf8EncodeEmailSubject ? "checked" : ""?> /> <?php esc_html_e('If you notice strange characters in the email subject, check this box to correct.', 'calendarista')?>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="calendarista-borderless-accordion">
					<div id="smtp_settings">
						<h3><?php esc_html_e('SMTP settings', 'calendarista') ?></h3>
						<table class="form-table">
							<tbody>
								<tr>
									<td colspan="2">
										<p class="description">
											<?php esc_html_e('All fields below are required, including sender email name and sender email fields above for smtp settings to take effect.', 'calendarista')?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="smtpHostName">
											<?php esc_html_e('Server', 'calendarista')?>
										</label>
									</th>
									<td>
										<input type="text" 
												class="regular-text calendarista_parsley_validated"
												data-parsley-trigger="change"
												id="smtpHostName"
												name="smtpHostName" 
												value="<?php echo esc_attr($this->generalSetting->smtpHostName) ?>"/> 
										<p class="description"><?php esc_html_e('The hostname of the mail server eg: smtp.example.com', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="smtpUserName">
											<?php esc_html_e('Username', 'calendarista')?>
										</label>
									</th>
									<td>
										<input type="text" 
												class="regular-text calendarista_parsley_validated"
												data-parsley-trigger="change"
												id="smtpUserName"
												name="smtpUserName" 
												value="<?php echo esc_attr($this->generalSetting->smtpUserName) ?>"/> 
											<p class="description"><?php esc_html_e('Username to use for SMTP authentication eg: user@example.com', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="smtpPassword">
											<?php esc_html_e('Password', 'calendarista')?>
										</label>
									</th>
									<td>
										<input type="password" 
												class="regular-text calendarista_parsley_validated"
												data-parsley-trigger="change" 
												id="smtpPassword"
												name="smtpPassword" 
												value="<?php echo esc_attr($this->generalSetting->smtpPassword) ?>"/> 
										<p class="description"><?php esc_html_e('Password to use for SMTP authentication', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="smtpPortNumber">
											<?php esc_html_e('Port Number', 'calendarista')?>
										</label>
									</th>
									<td>
										<input type="text" 
												class="regular-text calendarista_parsley_validated" 
												data-parsley-trigger="change" 
												id="smtpPortNumber"
												name="smtpPortNumber" 
												data-parsley-type="digits"
												value="<?php echo $this->generalSetting->smtpPortNumber ? esc_attr($this->generalSetting->smtpPortNumber) : '' ?>"/> 
										<p class="description"><?php esc_html_e('SMTP port number - likely to be 25, 465 or 587', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="smtpAuthenticate">
											<?php esc_html_e('Smtp Authenticate', 'calendarista')?>
										</label>
									</th>
									<td>
										<select 
												class="regular-text calendarista_parsley_validated"
												id="smtpAuthenticate"
												name="smtpAuthenticate">
											<option value="0" <?php echo !$this->generalSetting->smtpAuthenticate ? 'selected' : '' ?>><?php esc_html_e('false', 'calendarista') ?></option>
											<option value="1" <?php echo $this->generalSetting->smtpAuthenticate ? 'selected' : '' ?>><?php esc_html_e('true', 'calendarista') ?></option> 
										</select> 
										<p class="description"><?php esc_html_e('Use SMTP authentication?', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="smtpSecure">
											<?php esc_html_e('Protection', 'calendarista')?>
										</label>
									</th>
									<td>
										<select 
												class="regular-text calendarista_parsley_validated"
												id="smtpSecure"
												name="smtpSecure">
											<option value="" <?php echo $this->generalSetting->smtpSecure == '' ? 'selected' : '' ?>><?php esc_html_e('None', 'calendarista') ?></option>
											<option value="ssl" <?php echo $this->generalSetting->smtpSecure == 'ssl' ? 'selected' : '' ?>>SSL</option>
											<option value="tls" <?php echo $this->generalSetting->smtpSecure == 'tls' ? 'selected' : '' ?>>TLS</option> 
										</select> 
										<p class="description"><?php esc_html_e('Encryption system to use - ssl or tls', 'calendarista')?></p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<table class="form-table">
					<tbody>
						<tr>
								<th scope="row"><label for="emailTemplateHeaderImage"><?php esc_html_e('Your company logo', 'calendarista') ?></label></th>
								<td>
									<input id="emailTemplateHeaderImage" 
										name="emailTemplateHeaderImage" 
										type="hidden" 
										value="<?php echo esc_url($this->generalSetting->emailTemplateHeaderImage) ?>" />
									<div  class="preview-thumbnail" 
										style="<?php echo $this->generalSetting->emailTemplateHeaderImage ?
															sprintf('background-image: url(%s)', esc_url($this->generalSetting->emailTemplateHeaderImage)) : ''?>">
									</div>
									<button 
										id="remove_preview"
										type="button" 
										class="button button-primary remove-image" 
										title="<?php __('Remove image', 'calendarista')?>">&times;</button>
									<p class="description"><?php esc_html_e('A 200x50 image to display in the email header area', 'calendarista')?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="emailTemplateHeaderTitle"><?php esc_html_e('Title', 'calendarista') ?></label></th>
								<td>
									<input id="emailTemplateHeaderTitle" 
										name="emailTemplateHeaderTitle" 
										type="text" 
										class="regular-text" 
										value="<?php echo esc_attr($this->generalSetting->emailTemplateHeaderTitle) ?>" />
										<p class="description"><?php esc_html_e('The title to display in the email header area', 'calendarista') ?></p>
								</td>
							</tr>
					</tbody>
				</table>
				<div class="calendarista-borderless-accordion">
					<div id="email_color_settings">
						<h3><?php esc_html_e('Color settings', 'calendarista') ?></h3>
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<input id="emailTemplateHeaderBackground" 
											name="emailTemplateHeaderBackground" 
											type="text" 
											class="regular-text" 
											placeholder="<?php echo esc_attr($this->generalSetting->emailTemplateHeaderBackground) ?>" 
											value="<?php echo esc_attr($this->generalSetting->emailTemplateHeaderBackground) ?>" />
											<p class="description"><?php esc_html_e('Your emails header area background color', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<input id="emailTemplateHeaderColor" 
											name="emailTemplateHeaderColor" 
											type="text" 
											class="regular-text" 
											placeholder="<?php echo esc_attr($this->generalSetting->emailTemplateHeaderColor) ?>" 
											value="<?php echo esc_attr($this->generalSetting->emailTemplateHeaderColor) ?>" />
											<p class="description"><?php esc_html_e('Your emails header area font color', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<input id="emailTemplateBodyColor" 
											name="emailTemplateBodyColor" 
											type="text" 
											class="regular-text" 
											placeholder="<?php echo esc_attr($this->generalSetting->emailTemplateBodyColor) ?>" 
											value="<?php echo esc_attr($this->generalSetting->emailTemplateBodyColor) ?>" />
											<p class="description"><?php esc_html_e('Your emails body area font color', 'calendarista') ?></p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<p class="submit">
					<button class="button button-primary" name="calendarista_updatesetting"><?php esc_html_e('Save', 'calendarista') ?></button>
				</p>
				<hr>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="emailType">
									<?php esc_html_e('Template', 'calendarista')?>
								</label>
							</th>
							<td>
								<select id="emailType" name="emailType" onchange="form.submit()">
									<?php foreach($this->templates as $template):?>
									<option value="<?php echo $template['emailType']?>" <?php echo $this->emailSetting->emailType == $template['emailType'] ? 'selected=selected' : '' ?>><?php echo $template['subject'] ?></option>
									<?php endforeach;?>
								</select>
								<?php if((int)$this->emailSetting->emailType === Calendarista_EmailType::MASTER_TEMPLATE):?>
								<p class="description">
									<?php esc_html_e('The master template gives structure and styling and is meant for developers.', 'calendarista')?>
								</p>
								<?php endif; ?>
							</td>
						</tr>
						<?php if($this->emailSetting->emailType !== Calendarista_EmailType::MASTER_TEMPLATE):?>
						<tr>
							<th scope="row">
								<label for="subject">
									<?php esc_html_e('Subject', 'calendarista')?>
								</label>
							</th>
							<td>
								<input type="text" 
										class="regular-text calendarista_parsley_validated"  
										data-parsley-maxlength="256"
										data-parsley-trigger="change" 
										id="subject"
										name="subject" 
										value="<?php echo esc_attr($this->emailSetting->subject) ?>"/> 
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<th scope="row"></th>
							<td>
								<?php wp_editor(wp_kses_post($this->emailSetting->content), 'content', $settings = array('media_buttons'=>false, 'tinymce'=>false)); ?> 
								<p class="description"><strong><?php esc_html_e('Note!', 'calendarista') ?></strong>&nbsp;<?php esc_html_e('When editing pay particular attention to how tokens are enclosed within 2 braces {{token}} or 3 braces {{{token}}}.', 'calendarista') ?></p>
							</td>
						</tr>
						<?php if($this->emailSetting->emailType !== Calendarista_EmailType::MASTER_TEMPLATE):?>
						<tr>
							<th scope="row"></th>
							<td>
								<div class="calendarista-borderless-accordion">
									<div id="email_tokens">
										<h3><?php esc_html_e('+ Tokens (Click to expand)', 'calendarista') ?></h3>
										<div>
											<p class="description">
												<?php esc_html_e('Use any of the tokens below to include in your emails. Note that some tokens contains 3 curly braces instead of the usual 2', 'calendarista')?>
											</p>
											<ul>
												<li>{{invoice_id}}</li>
												<li>{{customer_name}}</li>
												<li>{{customer_email}}</li>
												<li>{{service_name}}</li>
												<li>{{availability_name}}</li>
												<li>{{service_provider_name}}</li>
												<li>{{start_datetime}}</li>
												<li>{{start_date}}</li>
												<li>{{start_time}}</li>
												<li>{{end_datetime}}</li>
												<li>{{end_date}}</li>
												<li>{{end_time}}</li>
												<li>{{from_address}}</li>
												<li><strong>{{{</strong>stops<strong>}}}</strong></li>
												<li>{{to_address}}</li>
												<li>{{map_link}}</li>
												<li>{{distance}}</li>
												<li>{{duration}}</li>
												<li><strong>{{{</strong>optionals<strong>}}}</strong></li>
												<li><strong>{{{</strong>optionalsWithCost<strong>}}}</strong></li>
												<li><strong>{{{</strong>custom_form_fields<strong>}}}</strong></li>
												<li>{{booked_seats_count}}</li>
												<li><strong>{{{</strong>dynamic_fields<strong>}}}</strong></li>
												<li>{{total_cost_value}}</li>
												<li>{{total_amount_paid}}</li>
												<li>{{deposit}}</li>
												<li>{{deposit_amount}}</li>
												<li>{{balance_amount}}</li>
												<li>{{tax_rate}}</li>
												<li>{{tax}}</li>
												<li>{{site_name}}</li>
												<li>{{cancel_page_url}}</li>
												<li>{{gdpr_page_url}}</li>
												<li>{{payment_date}}</li>
												<li>{{payment_operator}}</li>
												<li>{{{add_to_ical_link}}}</li>
												<li>{{{add_to_outlook_link}}}</li>
												<li>{{{add_to_google_link}}}</li>
												<li>{{appointment_management_url}}</li>
												<li>{{coupon_code}}</li>
												<li>{{coupon_discount}}</li>
												<li>{{total_amount_before_tax}}</li>
												<li>{{{upfront_payment_total}}}</li>
											</ul>
										</div>
									</div>
								</div>
								<div class="calendarista-borderless-accordion">
									<div id="control_statements">
										<h3><?php esc_html_e('+ Control statements (Click to expand)', 'calendarista') ?></h3>
										<div>
											<p class="description">
												<?php esc_html_e('Along with tokens, you can use the following control statements', 'calendarista')?>
											</p>
											<ul>
												<li>
												{{#if_has_end_date}}
												<br>
												{{/if_has_end_date}}
												</li>
												<li>
												{{#if_has_from_address}}
												<br>
												{{/if_has_from_address}}
												</li>
												<li>
												{{#if_has_waypoints}}
												<br>
												{{/if_has_waypoints}}
												</li>
												<li>
												{{#if_has_map_link}}
												<br>
												{{/if_has_map_link}}
												</li>
												<li>
												{{#if_has_to_address}}
												<br>
												{{/if_has_to_address}}
												</li>
												<li>
												{{#if_has_distance}}
												<br>
												{{/if_has_distance}}
												</li>
												<li>
												{{#if_has_duration}}
												<br>
												{{/if_has_duration}}
												</li>
												<li>
												{{#if_has_optionals}}
												<br>
												{{/if_has_optionals}}
												</li>
												<li>
												{{#if_has_custom_form_fields}}
												<br>
												{{/if_has_custom_form_fields}}
												</li>
												<li>
												{{#if_has_cost}}
												<br>
												{{/if_has_cost}}
												</li>
												<li>
												{{#if_has_return_trip}}
												<br>
												{{/if_has_return_trip}}
												</li>
												<li>
												{{#if_cancel_booking_enabled}}
												<br>
												{{/if_cancel_booking_enabled}}
												</li>
												<li>
												{{#if_gdpr_enabled}}
												<br>
												{{/if_gdpr_enabled}}
												</li>
												<li>
												{{#if_has_group_booking}}
												<br>
												{{/if_has_group_booking}}
												</li>
												<li>
												{{#if_has_deposit}}
												<br>
												{{/if_has_deposit}}
												</li>
												<li>
												{{#if_has_balance}}
												<br>
												{{/if_has_balance}}
												</li>
												<li>
												{{#if_has_tax}}
												<br>
												{{/if_has_tax}}
												</li>
												<li>
												{{#if_has_payment_date}}
												<br>
												{{/if_has_payment_date}}
												</li>
												<li>
												{{#if_has_dynamic_fields}}
												<br>
												{{/if_has_dynamic_fields}}
												</li>
												<li>
												{{#if_service_id_123}}
												<br><?php echo ('Note: replace 123 with the service id') ?><br>
												{{/if_service_id_123}}
												</li>
												<li>
												{{#if_availability_id_123}}
												<br><?php echo ('Note: replace 123 with the availability id') ?><br>
												{{/if_availability_id_123}}
												</li>
												<li>
												{{#if_has_coupon_discount}}
												<br>
												{{/if_has_coupon_discount}}
												</li>
												<li>
												{{#if_paid_upfront_full_amount}}
												<br>
												{{/if_paid_upfront_full_amount}}
												</li>
											</ul>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<p class="submit">
					<button class="button button-primary" 
							name="calendarista_update" 
							value="<?php echo $this->emailSetting->id?>">
							<?php esc_html_e('Save', 'calendarista') ?>
					</button>
					<?php if(isset($this->emailSetting->id)):?>
					<button class="button button-primary" 
							name="calendarista_delete" 
							value="<?php echo $this->emailSetting->id?>"
							<?php echo $this->emailSetting->id === -1 ? 'disabled=disabled' : '' ?>>
							<?php esc_html_e('Reset', 'calendarista') ?>
					</button>
					<?php endif; ?>
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
				calendarista.emailSettings = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.emailSettings.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					$('#smtp_settings').accordion({
						collapsible: true
						, active: false
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
					$('#email_color_settings').accordion({
						collapsible: true
						, active: false
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
					$('#email_tokens').accordion({
						collapsible: true
						, active: false
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
					$('#control_statements').accordion({
						collapsible: true
						, active: false
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
					this.galleryWindow = window['wp'].media({
						'title': 'Select an icon'
						, 'library': {'type': 'image'}
						, 'multiple': false
						, button: {'text': 'Select'}
					});
					this.$previewThumbnail = $('.preview-thumbnail');
					this.$emailTemplateHeaderImage = $('input[name="emailTemplateHeaderImage"]');
					this.$removePreviewButton = $('#remove_preview');
					this.$emailTemplateHeaderBackground = $('input[name="emailTemplateHeaderBackground"]');
					this.$emailTemplateHeaderColor = $('input[name="emailTemplateHeaderColor"]');
					this.$emailTemplateBodyColor = $('input[name="emailTemplateBodyColor"]');
					this.$previewThumbnail['bind']('click', function(e){
						e.preventDefault();
						context.galleryWindow.open();
					});
					this.$removePreviewButton.on('click', function(e){
						e.stopPropagation();
						var url = context.previewImageUrl;
						context.$previewThumbnail.css('background-image', 'url(' + url + ')');
						context.$emailTemplateHeaderImage.val('');
					});
					this.galleryWindow.on('select', function(){
						var userSelection = context.galleryWindow.state().get('selection').first().toJSON();
						context.imagePickerSelectionChanged(userSelection.url);
					});
					this.$emailTemplateHeaderBackground.wpColorPicker();
					this.$emailTemplateHeaderColor.wpColorPicker();
					this.$emailTemplateBodyColor.wpColorPicker();
				};
				calendarista.emailSettings.prototype.imagePickerSelectionChanged = function(url){
				   this.$previewThumbnail.css('background-image', 'url(' + url + ')');
				   this.$emailTemplateHeaderImage.val(url);
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.emailSettings({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}