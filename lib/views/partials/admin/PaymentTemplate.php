<?php
class Calendarista_PaymentTemplate extends Calendarista_ViewBase{
	public $paypal;
	public $paymentOperators;
	public $currencies;
	public $unsupportedCurrencies;
	public $paypalSupportedCurrencies;
	public $generalSetting;
	public $generalSettingsRepository;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		$this->currencyController(
			array($this, 'createdSetting')
			, array($this, 'updatedSetting')
		);
		new Calendarista_PayPalController(
			array($this, 'createdSetting')
			, array($this, 'updatedSetting')
			, array($this, 'deletedSetting')
		);
		$this->unsupportedCurrencies = array('KWD'/*Kuwaiti dinar*/);
		if(in_array($this->generalSetting->currency, $this->unsupportedCurrencies)){
			$this->disablePaymentOperators();
		}
		$this->paypal = Calendarista_PayPalHelper::getSettings();
		if(!$this->paypal){
			$this->paypal = new Calendarista_PayPalSetting(array());
		}
		$this->sortOrderController();
		$this->paymentOperators = $this->getPaymentOperators();
		$this->currencies = $this->getCurrencies();
		$this->paypalSupportedCurrencies = Calendarista_PaypalHelper::getCurrencies();
		$this->render();
	}
	public function sortOrderController(){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_payment_operators_sortorder')){
				return;
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$sortOrder = explode(',', $_POST['sortOrder']);
		$repo = new Calendarista_PaymentSettingRepository();
		foreach($sortOrder as $order){
			$pair = explode(':', $order);
			$id = (int)$pair[0];
			$sortIndex = (int)$pair[1];
			if($id === $this->paypal->id){
				$this->paypal->orderIndex = $sortIndex;
				$repo->update($this->paypal);
			}
		}
		$this->sortOrderUpdatedNotification();
	}
	public function disablePaymentOperators(){
		$repo = new Calendarista_PaymentSettingRepository();
		$result = $repo->readAll();
		$paymentOperators = array();
		foreach($result as $r){
			if($r['enabled']){
				$r['enabled'] = false;
				$repo->update($r);
			}
		}
	}
	public function getPaymentOperators(){
		$repo = new Calendarista_PaymentSettingRepository();
		$result = $repo->readAll();
		$paymentOperators = array();
		foreach($result as $r){
			if($r['enabled']){
				array_push($paymentOperators, $r);
			}
		}
		usort($paymentOperators, array($this, 'sortByIndex'));
		return $paymentOperators;
	}
	public function sortByIndex($a, $b){
		return ((int)$a['orderIndex'] <=> (int)$b['orderIndex']);
	}
	public function unsupportedCurrencyNotification(){
		if(!in_array($this->generalSetting->currency, $this->unsupportedCurrencies)){
			return;
		}
		?>
		<div class="wrap">
			<div class="settings error notice is-dismissible">
				<p><?php esc_html_e('The selected currency is not supported hence the payment operators below are disabled.', 'calendarista') ?></p>
			</div>
		</div>
		<?php
	}
	public function sortOrderUpdatedNotification() {
		?>
		<div class="wrap">
			<div class="settings updated notice is-dismissible">
				<p><?php esc_html_e('The sort order has been updated', 'calendarista') ?></p>
			</div>
		</div>
		<?php
	}
	public function supportsPaypal($currency){
		if(array_key_exists($currency, $this->paypalSupportedCurrencies)){
			return ' (' . __('Paypal Supported', 'calendarista') . ')';
		}
		return null;
	}
	public function getCurrencies(){
		return array(
			'USD'=>'United States Dollar'
			, 'EUR'=>'Euro'
			, 'GBP'=>'British Pound'
			, 'AUD'=>'Australian Dollar'
			, ''=>'---'
			, 'AED'=>'United Arab Emirates Dirham'
			, 'AFN'=>'Afghan Afghani'
			, 'ALL'=>'Albanian Lek'
			, 'AMD'=>'Armenian Dram'
			, 'ANG'=>'Netherlands Antillean Gulden'
			, 'AOA'=>'Angolan Kwanza'
			, 'ARS'=>'Argentine Peso'
			, 'AWG'=>'Aruban Florin'
			, 'AZN'=>'Azerbaijani Manat'
			, 'BAM'=>'Bosnia &amp; Herzegovina Convertible Mark'
			, 'BBD'=>'Barbadian Dollar'
			, 'BDT'=>'Bangladeshi Taka'
			, 'BGN'=>'Bulgarian Lev'
			, 'BIF'=>'Burundian Franc'
			, 'BMD'=>'Bermudian Dollar'
			, 'BND'=>'Brunei Dollar'
			, 'BOB'=>'Bolivian Boliviano'
			, 'BRL'=>'Brazilian Real'
			, 'BSD'=>'Bahamian Dollar'
			, 'BWP'=>'Botswana Pula'
			, 'BZD'=>'Belize Dollar'
			, 'CAD'=>'Canadian Dollar'
			, 'CDF'=>'Congolese Franc'
			, 'CHF'=>'Swiss Franc'
			, 'CLP'=>'Chilean Peso'
			, 'CNY'=>'Chinese Renminbi Yuan'
			, 'COP'=>'Colombian Peso'
			, 'CRC'=>'Costa Rican Colón'
			, 'CVE'=>'Cape Verdean Escudo'
			, 'CZK'=>'Czech Koruna'
			, 'DJF'=>'Djiboutian Franc'
			, 'DKK'=>'Danish Krone'
			, 'DOP'=>'Dominican Peso'
			, 'DZD'=>'Algerian Dinar'
			, 'EGP'=>'Egyptian Pound'
			, 'ETB'=>'Ethiopian Birr'
			, 'FJD'=>'Fijian Dollar'
			, 'FKP'=>'Falkland Islands Pound'
			, 'GEL'=>'Georgian Lari'
			, 'GIP'=>'Gibraltar Pound'
			, 'GMD'=>'Gambian Dalasi'
			, 'GNF'=>'Guinean Franc'
			, 'GTQ'=>'Guatemalan Quetzal'
			, 'GYD'=>'Guyanese Dollar'
			, 'HKD'=>'Hong Kong Dollar'
			, 'HNL'=>'Honduran Lempira'
			, 'HRK'=>'Croatian Kuna'
			, 'HTG'=>'Haitian Gourde'
			, 'HUF'=>'Hungarian Forint'
			, 'IDR'=>'Indonesian Rupiah'
			, 'ILS'=>'Israeli New Sheqel'
			, 'INR'=>'Indian Rupee'
			, 'ISK'=>'Icelandic Króna'
			, 'JMD'=>'Jamaican Dollar'
			, 'JPY'=>'Japanese Yen'
			, 'KES'=>'Kenyan Shilling'
			, 'KGS'=>'Kyrgyzstani Som'
			, 'KHR'=>'Cambodian Riel'
			, 'KMF'=>'Comorian Franc'
			, 'KRW'=>'South Korean Won'
			, 'KYD'=>'Cayman Islands Dollar'
			, 'KZT'=>'Kazakhstani Tenge'
			, 'LAK'=>'Lao Kip'
			, 'LBP'=>'Lebanese Pound'
			, 'LKR'=>'Sri Lankan Rupee'
			, 'LRD'=>'Liberian Dollar'
			, 'LSL'=>'Lesotho Loti'
			, 'MAD'=>'Moroccan Dirham'
			, 'MDL'=>'Moldovan Leu'
			, 'MGA'=>'Malagasy Ariary'
			, 'MKD'=>'Macedonian Denar'
			, 'MMK'=>'Myanmar Kyat'
			, 'MNT'=>'Mongolian Tögrög'
			, 'MOP'=>'Macanese Pataca'
			, 'MRO'=>'Mauritanian Ouguiya'
			, 'MUR'=>'Mauritian Rupee'
			, 'MVR'=>'Maldivian Rufiyaa'
			, 'MWK'=>'Malawian Kwacha'
			, 'MXN'=>'Mexican Peso'
			, 'MYR'=>'Malaysian Ringgit'
			, 'MZN'=>'Mozambican Metical'
			, 'NAD'=>'Namibian Dollar'
			, 'NGN'=>'Nigerian Naira'
			, 'NIO'=>'Nicaraguan Córdoba'
			, 'NOK'=>'Norwegian Krone'
			, 'NPR'=>'Nepalese Rupee'
			, 'NZD'=>'New Zealand Dollar'
			, 'OMR'=>'Omani Rial'
			, 'PAB'=>'Panamanian Balboa'
			, 'PEN'=>'Peruvian Nuevo Sol'
			, 'PGK'=>'Papua New Guinean Kina'
			, 'PHP'=>'Philippine Peso'
			, 'PKR'=>'Pakistani Rupee'
			, 'PLN'=>'Polish Złoty'
			, 'PYG'=>'Paraguayan Guaraní'
			, 'QAR'=>'Qatari Riyal'
			, 'RON'=>'Romanian Leu'
			, 'RSD'=>'Serbian Dinar'
			, 'RUB'=>'Russian Ruble'
			, 'RWF'=>'Rwandan Franc'
			, 'SAR'=>'Saudi Riyal'
			, 'SBD'=>'Solomon Islands Dollar'
			, 'SCR'=>'Seychellois Rupee'
			, 'SEK'=>'Swedish Krona'
			, 'SGD'=>'Singapore Dollar'
			, 'SHP'=>'Saint Helenian Pound'
			, 'TJS'=>'Tajikistani Somoni'
			, 'SLL'=>'Sierra Leonean Leone'
			, 'SOS'=>'Somali Shilling'
			, 'SRD'=>'Surinamese Dollar'
			, 'STD'=>'São Tomé and Príncipe Dobra'
			, 'SVC'=>'Salvadoran Colón'
			, 'SZL'=>'Swazi Lilangeni'
			, 'THB'=>'Thai Baht'
			, 'TOP'=>'Tongan Paʻanga'
			, 'TRY'=>'Turkish Lira'
			, 'TTD'=>'Trinidad and Tobago Dollar'
			, 'TWD'=>'New Taiwan Dollar'
			, 'TZS'=>'Tanzanian Shilling'
			, 'UAH'=>'Ukrainian Hryvnia'
			, 'UGX'=>'Ugandan Shilling'
			, 'UYU'=>'Uruguayan Peso'
			, 'UZS'=>'Uzbekistani Som'
			, 'VND'=>'Vietnamese Đồng'
			, 'VUV'=>'Vanuatu Vatu'
			, 'WST'=>'Samoan Tala'
			, 'XAF'=>'Central African Cfa Franc'
			, 'XCD'=>'East Caribbean Dollar'
			, 'XOF'=>'West African Cfa Franc'
			, 'XPF'=>'Cfp Franc'
			, 'YER'=>'Yemeni Rial'
			, 'ZAR'=>'South African Rand'
			, 'ZMW'=>'Zambian Kwacha'
			, 'KWD'=>'Kuwaiti dinar'
		);
	}
	public function currencyController($createCallback = null, $updateCallback = null){
		$this->generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		$this->generalSetting = $this->generalSettingsRepository->read();
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_currency')){
				return;
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->generalSetting->currency = sanitize_text_field($_POST['currency']);
		$this->generalSetting->currencySymbolPlacement = (int)$_POST['currencySymbolPlacement'];
		$this->generalSetting->thousandSep = $_POST['thousandSep'];
		$this->generalSetting->decimalPoint = $_POST['decimalPoint'];
		if (array_key_exists('calendarista_create', $_POST)){
			$this->createCurrencySetting($createCallback);
		}else if(array_key_exists('calendarista_update', $_POST)){
			$this->updateCurrencySetting($updateCallback);
		}
	}
	public function createCurrencySetting($callback){
		$result = $this->generalSettingsRepository->insert($this->generalSetting);
		call_user_func_array($callback, array($result));
	}
	public function updateCurrencySetting($callback){
		$result = $this->generalSettingsRepository->update($this->generalSetting);
		call_user_func_array($callback, array($result));
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<p class="description"><?php esc_html_e('Payment general setting', 'calendarista') ?></p>
					<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="calendarista_currency"/>
						<input type="hidden" name="id" value="<?php echo $this->generalSetting->id ?>"/>
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="currency"><?php esc_html_e('Currency', 'calendarista')?></label>
									</th>
									<td>
										<select id="currency" name="currency">
										<?php foreach($this->currencies as $key=>$value): ?>
											<option value="<?php echo $key ?>" 
											<?php selected($this->generalSetting->currency, $key) ?> 
											<?php echo !$key ? 'disabled' : '' ?>>
												<?php echo sprintf('%s%s', esc_html($value), $this->supportsPaypal($key)) ?>
											</option>
										<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="currencySymbolPlacement"><?php esc_html_e('Currency Symbol placement', 'calendarista')?></label>
									</th>
									<td>
										<input type="radio" name="currencySymbolPlacement" value="-1" <?php echo $this->generalSetting->currencySymbolPlacement === -1 ? 'checked': ''?>><?php esc_html_e('Automatic', 'calendarista'); ?>
										<input type="radio" name="currencySymbolPlacement" value="0" <?php echo $this->generalSetting->currencySymbolPlacement === 0 ? 'checked': ''?>><?php esc_html_e('Left', 'calendarista'); ?>
										<input type="radio" name="currencySymbolPlacement" value="1" <?php echo $this->generalSetting->currencySymbolPlacement === 1 ? 'checked': ''?>><?php esc_html_e('Right', 'calendarista'); ?>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="thousandSep"><?php esc_html_e('Thousands Separator', 'calendarista')?></label>
									</th>
									<td>
										<select name="thousandSep" id="thousandSep">
											<option value="." <?php echo $this->generalSetting->thousandSep == '.' ? 'selected' : '' ?>>.</option>
											<option value="," <?php echo $this->generalSetting->thousandSep == ',' ? 'selected' : '' ?>>,</option>
											<option value="" <?php echo $this->generalSetting->thousandSep == '' ? 'selected' : '' ?>><?php esc_html_e('None', 'calendarista') ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="thousandSep"><?php esc_html_e('Decimal Point', 'calendarista')?></label>
									</th>
									<td>
										<select name="decimalPoint" id="decimalPoint">
											<option value="." <?php echo $this->generalSetting->decimalPoint == '.' ? 'selected' : '' ?>>.</option>
											<option value="," <?php echo $this->generalSetting->decimalPoint == ',' ? 'selected' : '' ?>>,</option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<button class="button button-primary" 
								<?php if($this->generalSetting->id === -1) :?>
								 name="calendarista_create"
								<?php else:?>
								name="calendarista_update" 
								<?php endif;?>
								value="<?php echo $this->generalSetting->id?>">
								<?php esc_html_e('Save', 'calendarista') ?>
							</button>
						</p>
					</form>
					<hr>
					<?php $this->unsupportedCurrencyNotification(); ?>
					<p class="description">PayPal</p>
					<p><img src="<?php echo CALENDARISTA_PLUGINDIR . 'assets/img/PP_logo_h_98x25.png'?>"></p>
					<form id="paypal" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="calendarista_paypal"/>
						<input type="hidden" name="id" value="<?php echo $this->paypal->id ?>"/>
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"></th>
									<td>
										<input id="enabled" name="enabled" type="checkbox" 
																<?php echo $this->paypal->enabled ? 'checked' : ''; ?> />
																	<?php esc_html_e('Enable', 'calendarista')?>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="businessEmail"><?php esc_html_e('Email', 'calendarista')?></label>
									</th>
									<td>
										<input type="text" 
											class="regular-text" 
											id="businessEmail" 
											name="businessEmail" 
											data-parsley-type="email"
											data-parsley-required="true"
											value="<?php echo esc_attr($this->paypal->businessEmail) ?>"/>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="title"><?php esc_html_e('Title', 'calendarista')?></label>
									</th>
									<td>
										<input type="text" 
											class="regular-text" 
											id="title" 
											name="title" 
											data-parsley-required="true"
											value="<?php echo esc_attr($this->paypal->title) ?>"/>
									<p class="description"><?php esc_html_e('The payment selection', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"></th>
									<td>
										<input id="useSandbox" name="useSandbox" type="checkbox" 
																<?php echo $this->paypal->useSandbox ? 'checked' : ''; ?> />
																	<?php esc_html_e('Enable sandbox', 'calendarista')?>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="imageUrl"><?php esc_html_e('Image', 'calendarista') ?></label></th>
									<td>
										<input name="imageUrl" type="hidden" 
											value="<?php echo $this->paypal->imageUrl ?>" />
										<div data-calendarista-preview-icon="imageUrl" class="preview-icon" 
											style="<?php echo $this->paypal->imageUrl ?
																sprintf('background-image: url(%s)', esc_url($this->paypal->imageUrl)) : ''?>">
										</div>
										<button
											type="button"
											name="iconUrlRemove"
											data-calendarista-preview-icon="imageUrl"
											class="button button-primary remove-image" 
											title="<?php __('Remove image', 'calendarista')?>">
											<i class="fa fa-remove"></i>
										</button>
										<p class="description"><?php esc_html_e('An image to display when PayPal payment method is active', 'calendarista')?></p>
									</td>
								</tr>
							</body>
						</table>
						<p class="submit">
						<?php if($this->paypal->id === -1) :?>
							<button class="button button-primary" name="calendarista_create"><?php esc_html_e('Save', 'calendarista') ?></button>
						<?php else:?>
							<button class="button button-primary" 
									name="calendarista_update" 
									value="<?php echo $this->paypal->id?>">
									<?php esc_html_e('Save', 'calendarista') ?>
							</button>
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
								<div class="sidebar-name">
									<h3><?php esc_html_e('Payment operators', 'calendarista') ?></h3>
								</div>
								<div class="sidebar-description">
									<p class="description">
										<?php esc_html_e('List of enabled payment operators below. Drag and drop header to rearrange the order.', 'calendarista')?>
									</p>
								</div>
								<?php if(count($this->paymentOperators) > 0):?>
								<form id="calendarista_form" action="<?php echo esc_url($this->requestUrl) ?>" method="post">
									<input type="hidden" name="controller" value="calendarista_payment_operators_sortorder" />
									<input type="hidden" name="sortOrder" />
									<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
									<div class="column-borders">
										<div class="clear"></div>
										<div class="accordion-container payment-operators">
											<ul class="outer-border">
											<?php foreach($this->paymentOperators as $operator):?>
												<?php if($operator['paymentOperator'] == 2/*twocheckout*/){
															//discontinued operator
															continue;
														}
												?>
												<li class="control-section accordion-section">
													<h3 class="accordion-section-title" tabindex="0">
														<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
														<input type="hidden" name="paymentOperators[]" value="<?php echo $operator['id'] ?>"> 
														<?php echo $operator['title'] ?>
													</h3>
												</li>
										   <?php endforeach;?>
											</ul>
											<p class="alignright">
												<input type="submit" 
														name="calendarista_sortorder" 
														id="calendarista_sortorder" 
														class="button button-primary sort-button" 
														value="<?php esc_html_e('Save sort order', 'calendarista') ?>" disabled>
											</p>
											<br class="clear">
										</div>
									</div>
								</form>
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
				calendarista.payment = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.payment.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$paymentOperators = $('.accordion-container.payment-operators ul>li');
					this.$sortOrder = $('input[name="sortOrder"]');
					this.$sortOrderButton = $('input[name="calendarista_sortorder"]');
					//paypal
					this.$paypalForm = $('#paypal');
					this.$paypalEnableCheckbox = this.$paypalForm.find('input[name="enabled"]');
					this.$paypalInputs = this.$paypalForm.find('input[type="text"], input[type="checkbox"]').not('input[name="enabled"]');
					this.$paypalEnableCheckbox.on('change', function(){
						context.paypalTestFields();
					});
					this.paypalTestFields();
					
					$('.accordion-container.payment-operators ul').accordion({
					  collapsible: false
					   , active: null
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
					new Calendarista.imageSelector({'id': '#paypal', 'previewImageUrl': options['previewImageUrl']});
				};
				calendarista.payment.prototype.updateSortOrder = function(){
					var sortOrder = this.getSortOrder(this.$paymentOperators, 'input[name="paymentOperators[]"]');
					this.$sortOrder.val(sortOrder.join(','));
					this.$sortOrderButton.prop('disabled', false);
				};
				calendarista.payment.prototype.getSortOrder = function($sortItems, selector){
				var i
					, sortOrder = []
					, $item;
				for(i = 0; i < $sortItems.length; i++){
					$item = $($sortItems[i]);
					sortOrder.push($item.find(selector).val() + ':' + $item.index());
				}
				return sortOrder;
			};
			calendarista.payment.prototype.paypalTestFields = function(){
				if(this.$paypalEnableCheckbox.is(':checked')){
					this.$paypalInputs.prop('disabled', false);
					return;
				}
				this.$paypalInputs.prop('disabled', true);
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.payment({
			<?php echo $this->requestUrl ?>'
			, 'previewImageUrl': '<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/no-preview-thumbnail.png'
		});
		</script>
		<?php
	}
}