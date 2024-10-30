<?php
class Calendarista_ManageSales extends Calendarista_ViewBase{
	public $salesList;
	public $orderId = null;
	public $fromDate;
	public $toDate;
	public $allSalesLink;
	public $availabilities;
	public $availabilityId;
	public $email;
	public $customerName;
	public $invoiceId;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-sales', Calendarista_PermissionHelper::staffMemberProjects());
		$this->fromDate = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : null;
		$this->toDate = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : null;
		$this->orderId = isset($_GET['orderId']) ? (int)$_GET['orderId'] : null;
		$this->invoiceId = isset($_GET['invoiceId']) ? sanitize_text_field($_GET['invoiceId']) : null;
		$this->customerName = isset($_GET['customerName']) ? sanitize_text_field($_GET['customerName']) : null;
		$this->email = isset($_GET['email']) ? sanitize_email($_GET['email']) : null;
		$this->availabilityId = isset($_GET['availabilityId']) ? (int)$_GET['availabilityId'] : null;
		$this->salesList = new Calendarista_SalesList();
		$this->salesList->bind();
		if(isset($_GET['command']) && $_GET['command'] === 'delete'){
			$this->orderDelete();
		}
		$this->allSalesLink = sprintf('<a href="%s">%s</a>', $this->requestUrl, __('here', 'calendarista'));
		if($this->selectedProjectId !== -1){
			$staffMemberAvailabilities = Calendarista_PermissionHelper::staffMemberAvailabilities();
			$repo = new Calendarista_AvailabilityRepository();
			$this->availabilities = $repo->readAll($this->selectedProjectId, $staffMemberAvailabilities);
		}
		$this->render();
	}
	
	public function render(){
	?>
		<div class="wrap">
			<div class="column-pane calendarista-borderless-accordion">
				<div id="searchfilter">
					<h3><?php esc_html_e('Search filter', 'calendarista') ?></h3>
					<div>
						<form action="<?php echo esc_url($this->requestUrl) ?>" method="get">
							<input type="hidden" name="page" value="calendarista-sales">
							<input type="hidden" name="controller" value="calendarista_sales">
							<div class="searchfilter">
								<p class="description"><?php esc_html_e('All fields are optional', 'calendarista') ?></p>
								<p>
								<label for="from"><?php esc_html_e('Find sales made on', 'calendarista') ?></label>
								<input 
									type="text" 
									id="from" 
									name="from" 
									class="medium-text enable-readonly-input" 
									readonly
									value="<?php echo  esc_html($this->fromDate) ?>">
								<label for="to"><?php esc_html_e('or between', 'calendarista') ?></label>
								<input 
									type="text" 
									id="to" 
									name="to" 
									class="medium-text enable-readonly-input" 
									readonly
									value="<?php echo  esc_html($this->toDate) ?>">
								</p>
								<p>
									<?php $this->renderProjectSelectList(true, __('All services', 'calendarista')) ?>
									&nbsp;<?php esc_html_e('and', 'calendarista') ?>&nbsp;
									<select name="availabilityId">
										<option value="-1"><?php esc_html_e('All availabilities', 'calendarista') ?></option>
										<?php if($this->availabilities):?>
											<?php foreach($this->availabilities as $availability):?>
												<option value="<?php echo esc_attr($availability->id) ?>" <?php echo $availability->id === $this->availabilityId ? 'selected' : ''?>><?php echo esc_html($availability->name) ?></option>
											<?php endforeach;?>
										<?php endif;?>
									</select>
									<span id="spinner_get_availability" class="calendarista-spinner calendarista-invisible">
										&nbsp;<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading...', 'calendarista') ?>
									</span>
								</p>
								<p>
								<input type="text" 
										id="customerName" 
										name="customerName" 
										class="medium-text"
										value="<?php echo esc_html($this->customerName) ?>"
										placeholder="<?php esc_html_e('Customer name', 'calendarista') ?>" />
								<?php esc_html_e('or by', 'calendarista') ?>
								<input type="text" 
										id="email" 
										name="email" 
										class="medium-text"
										value="<?php echo esc_html($this->email) ?>"
										placeholder="<?php esc_html_e('Customer email', 'calendarista') ?>"
										data-parsley-errors-container="#email-error-container"
										data-parsley-type="email" 
										data-parsley-trigger="change" />
								<?php esc_html_e('or by', 'calendarista') ?>
								<input type="text" 
										id="invoiceId" 
										name="invoiceId"
										value="<?php echo esc_html($this->invoiceId) ?>"
										class="medium-text"
										placeholder="<?php esc_html_e('ID', 'calendarista') ?>"/>
								</p>
								<p>
								<button type="button" class="button button-primary" id="salesFilterButton">
									<i class="fa fa-filter"></i>
									<?php esc_html_e('Apply', 'calendarista') ?>
								</button>
								&nbsp;
								<button type="button" id="filterResetButton" class="button button-primary">
									<?php esc_html_e('Reset', 'calendarista') ?>
								</button>
								<br class="clear">
								</p>
							</div>
							<div id="email-error-container"></div>
						</form>
					</div>
				</div>
			</div>
			<?php if($this->orderId):?>
			<div class="calendarista error notice is-dismissible">
				<p><?php echo sprintf(__('Currently viewing single sale item. To view all sales, click %s or use search filter above.', 'calendarista'), $this->allSalesLink) ?></p>
			</div>
			<?php endif; ?>
		</div>
		<div class="wrap">
			<div>
			<span id="spinner_update_sales_list" class="calendarista-spinner calendarista-invisible">
				<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
			</span>
			</div>
			<div id="calendarista_sales_list" class="table-responsive">
				<h1><?php echo sprintf(__('Total Amount: %s', 'calendarista'), Calendarista_MoneyHelper::toShortCurrency($this->salesList->sum)) ?></h1>
				<?php $this->salesList->printVariables() ?>
				<?php $this->salesList->display();?>
			</div>
		</div>
		<div class="sale-details-modal calendarista" 
				title="<?php esc_html_e('Sale details', 'calendarista') ?>">
			<div class="container-fluid">
				<div class="sale_details_placeholder"></div>
			</div>
		</div>
		<div class="read-appointment-modal calendarista" 
				title="<?php esc_html_e('Appointment details', 'calendarista') ?>">
			<div class="container-fluid">
				<div class="read_appointment_placeholder"></div>
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
				calendarista.sales = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.sales.prototype.init = function(options){
					var context = this;
					this.requestUrl = options['requestUrl'];
					this.actionSaleDetails = 'calendarista_sale_details';
					this.actionGetAvailabilities = 'calendarista_get_availabilities';
					this.actionWizard = 'calendarista_wizard';
					this.actionReadAppointment = 'calendarista_read_appointment';
					this.actionGetSalesList = 'calendarista_get_sales_list';
					this.$saleDetailsPlaceHolder = $('.sale_details_placeholder');
					this.$saleDetailsButton = $('button[name="details"]');
					this.$searchFilterProjectList = $('.searchfilter select[name="projectId"]');
					this.$availabilityList = $('.searchfilter select[name="availabilityId"]');
					this.$readAppointmentPlaceHolder = $('.read_appointment_placeholder');
					this.$viewAppointmentButtons = $('button[name="viewAppointment"]');
					this.$customerName = $('input[name="customerName"]');
					this.$email = $('input[name="email"]');
					this.$salesFilterButton = $('#salesFilterButton');
					this.$filterResetButton = $('#filterResetButton');
					this.$invoiceId = $('input[name="invoiceId"]');
					this.$salesList = $('#calendarista_sales_list');
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.ajax1 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'sale_details'});
					this.ajax2 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'get_availability'});
					this.ajax3 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'update_sales_list'});
					this.readAppointmentAjax = new Calendarista.ajax({'ajaxUrl':  this.ajaxUrl, 'id': 'read_appointment'});
					this.dateClearDelegate = calendarista.createDelegate(this, this.dateClear);
					this.saleDetailButtonClickDelegate = calendarista.createDelegate(this, this.saleDetailButtonClick);
					this.viewAppointmentButtonClickDelegate = calendarista.createDelegate(this, this.viewAppointmentButtonClick);
					this.$fromDate = $('input[name="from"]');
					this.$toDate = $('input[name="to"]');
					this.$salesFilterButton.on('click', function(e){
						context.salesListRequest(true);
					});
					this.$filterResetButton.on('click', function(e){
						context.$fromDate.val('');
						context.$toDate.val('');
						context.$searchFilterProjectList[0].selectedIndex = 0;
						context.$availabilityList.find('option').remove().end().append('<option value=""><?php echo $this->decodeString(__("Select an availability", "calendarista")) ?></option>');
						context.$availabilityList[0].selectedIndex = 0;
						$('input[name="customerName"]').val('');
					    $('input[name="email"]').val('');
						$('input[name="invoiceId"]').val('');
						context.salesListRequest(true);
					});
					this.datepickerOptions = {
						'changeMonth': true
						, 'dateFormat': 'yy-mm-dd'
						, 'changeYear': true
						, 'showButtonPanel': true
						, 'closeText': 'Clear'
						, 'onClose': this.dateClearDelegate
						, 'minDate': new Date(1999, 1, 1)
					};
					this.$fromDate.datepicker(this.datepickerOptions).on('change', function() {
						context.$toDate.datepicker('option', 'minDate', context.getDate(this));
					});
					this.$toDate.datepicker(this.datepickerOptions).on('change', function() {
						context.$fromDate.datepicker('option', 'maxDate', context.getDate(this));
					});
					$('#searchfilter').accordion({
						collapsible: true
						<?php if($this->orderId):?>
						, active: false
						<?php endif; ?>
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
					this.$saleDetailsButton.on('click', this.saleDetailButtonClickDelegate);
					this.$saleDetailsModalDialog = $('.sale-details-modal').dialog({
						autoOpen: false
						, height: '480'
						, width: '640'
						, modal: true
						, resizable: false
						, dialogClass: 'calendarista-dialog'
						, closeOnEscape: false
						, open: function(event, ui) {
							$('.ui-dialog-titlebar-close', ui.dialog | ui).hide();
						}
						, create: function() {
							var spinner = '<div id="spinner_sale_details" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
								spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">&nbsp;';
								spinner += '</div>';
							$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
							$(this).closest('div.ui-dialog').find('.ui-dialog-titlebar-close').on('click', function(e) {
								e.preventDefault();
								$('#spinner_sale_details').removeClass('calendarista-invisible');
								context.salesListRequest();
								return false;
							});
						}
						, buttons: [
							{
								'text': '<?php echo $this->decodeString(__("Request Payment", "calendarista")) ?>'
								, 'name': 'requestPayment'
								, 'click':  function(){
									var model = [
										{ 'name': 'requestPayment', 'value': true}
										, { 'name': 'orderId', 'value': context.orderId}
										, { 'name': 'projectId', 'value': context.projectId}
										, { 'name': 'controller', 'value': 'calendarista_sales'}
										, { 'name': 'action', 'value': context.actionSaleDetails }
										, { 'name': 'calendarista_nonce', 'value': context.nonce }
									];
									context.ajax1.request(context, context.saleResponse, $.param(model));
									return false;
								}
							}
							, {
								'text': '<?php echo $this->decodeString(__("Confirm Payment", "calendarista")) ?>'
								, 'name': 'confirmPayment'
								, 'click':  function(){
									var $dialog
										, content;
									content = '<p title="<?php echo $this->decodeString(__('Confirm Payment', 'calendarista')) ?>">'
									content += '<input type="checkbox" name="paymentRecievedNotification" /><?php echo $this->decodeString(__("Payment received notification", "calendarista")) ?>';
									content += '<br>';
									content += '<input type="checkbox" name="confirmBookingNotification" /><?php echo $this->decodeString(__("Appointment confirmation notification", "calendarista")) ?>';
									content += '</p>';
									$dialog = $(content).dialog({
										buttons: {
											'OK': function() {
												var $paymentRecievedNotification = $dialog.dialog('widget').find('input[name="paymentRecievedNotification"]')
													, $confirmBookingNotification = $dialog.dialog('widget').find('input[name="confirmBookingNotification"]')
													, model = [
														{ 'name': 'confirmPayment', 'value': true}
														, { 'name': 'orderId', 'value': context.orderId}
														, { 'name': 'projectId', 'value': context.projectId}
														, { 'name': 'paymentReceivedNotification', 'value': $paymentRecievedNotification.is(':checked') ? 1 : 0}
														, { 'name': 'confirmBookingNotification', 'value': $confirmBookingNotification.is(':checked') ? 1 : 0}
														, { 'name': 'controller', 'value': 'calendarista_sales'}
														, { 'name': 'action', 'value': context.actionSaleDetails }
														, { 'name': 'calendarista_nonce', 'value': context.nonce }
													];
												context.ajax1.request(context, context.saleResponse, $.param(model));
												context.paymentButtonState(1);
												$dialog.dialog('close');
											}
											, 'Cancel':  function() {
												$dialog.dialog('close');
											}
										}
									});
									return false;
								}
							}
							, {
								'text': '<?php echo $this->decodeString(__("Delete", "calendarista")) ?>'
								, 'name': 'delete'
								, 'click':  function(){
									var $dialog
										, content;
									content = '<p title="<?php echo $this->decodeString(__("Delete this sale?", "calendarista")) ?>">'
									content += '<input type="checkbox" name="bookingCancelledNotification" /><?php echo $this->decodeString(__("Appointment cancelled notification", "calendarista"))?>';
									content += '</p>';
									$dialog = $(content).dialog({
										dialogClass: 'calendarista-dialog'
										, buttons: {
											'OK': function() {
												var $bookingCancelledNotification = $dialog.dialog('widget').find('input[name="bookingCancelledNotification"]')
													, model = [
														{ 'name': 'delete', 'value': true}
														, { 'name': 'orderId', 'value': context.orderId}
														, { 'name': 'projectId', 'value': context.projectId}
														, { 'name': 'bookingCancelledNotification', 'value': $bookingCancelledNotification.is(':checked') ? 1 : 0}
														, { 'name': 'controller', 'value': 'calendarista_sales'}
														, { 'name': 'action', 'value': context.actionSaleDetails }
														, { 'name': 'calendarista_nonce', 'value': context.nonce }
													];
												$('#spinner_sale_details').removeClass('calendarista-invisible');
												context.disableButtons();
												context.ajax1.request(context, context.deleteSaleResponse, $.param(model));
												$dialog.dialog('close');
											}
											, 'Cancel':  function() {
												$dialog.dialog('close');
											}
										}
									});
								}
							}
							, {
								'text': '<?php echo $this->decodeString(__("Close", "calendarista")) ?>'
								, 'click':  function(){
									$('#spinner_sale_details').removeClass('calendarista-invisible');
									context.$saleDetailsModalDialog.dialog('close');
									context.salesListRequest();
								}
							}
						]
					});
					this.$searchFilterProjectList.on('change', function(e){
						var val = parseInt($(this).val(), 10)
							, model = [
								{ 'name': 'projectId', 'value': val}
								, { 'name': 'action', 'value': context.actionGetAvailabilities }
								, { 'name': 'calendarista_nonce', 'value': context.nonce }
							];
						context.ajax2.request(context, context.availabilitiesResponse, $.param(model));
					});
					this.$viewAppointmentButtons.on('click', this.viewAppointmentButtonClickDelegate);
					this.$readAppointmentModalDialog = $('.read-appointment-modal').dialog({
						autoOpen: false
						, height: '480'
						, width: '640'
						, modal: true
						, resizable: false
						, dialogClass: 'calendarista-dialog'
						, create: function() {
							var spinner = '<div id="spinner_read_appointment" style="margin-right: 10px" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
								spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">';
								spinner += '</div>';
							$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
							$(this).closest('div.ui-dialog').find('.ui-dialog-titlebar-close').on('click', function(e) {
							   e.preventDefault();
							});
						}
						, buttons: [
							{
								'text': '<?php echo $this->decodeString(__("Close", "calendarista")) ?>'
								, 'click':  function(){
									context.$readAppointmentModalDialog.dialog('close');
									context.$readAppointmentPlaceHolder.empty();
								}
							}
						]
					});
				};
				calendarista.sales.prototype.dateClear = function(e){
					if (!$(window.event.srcElement).hasClass('ui-datepicker-close')){
						return;
					}
					this.$fromDate.datepicker('setDate', null);
					this.$toDate.datepicker('setDate', null);
					this.$fromDate.datepicker('option', 'maxDate', null);
					this.$toDate.datepicker('option', 'minDate', null);
				};
				calendarista.sales.prototype.availabilitiesResponse = function(result){
					this.$availabilityList[0].length = 0;
					this.$availabilityList.append(result);
				};
				calendarista.sales.prototype.deleteSaleResponse = function(result){
					this.$saleDetailsPlaceHolder.replaceWith('<div class="sale_details_placeholder">' + result + '</div>');
					this.$saleDetailsPlaceHolder = $('.sale_details_placeholder');
					this.salesListRequest();
				};
				calendarista.sales.prototype.saleResponse = function(result){
					this.$saleDetailsPlaceHolder.replaceWith('<div class="sale_details_placeholder">' + result + '</div>');
					this.$saleDetailsPlaceHolder = $('.sale_details_placeholder');
				};
				calendarista.sales.prototype.paymentButtonState = function(status){
					var $requestPaymentButton = this.$saleDetailsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="requestPayment"]')
						, $confirmPaymentButton = this.$saleDetailsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="confirmPayment"]')
						, $deleteButton = this.$saleDetailsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="delete"]');
					$requestPaymentButton.prop('disabled', false).removeClass('ui-state-disabled');
					$confirmPaymentButton.prop('disabled', false).removeClass('ui-state-disabled');
					$deleteButton.prop('disabled', false).removeClass('ui-state-disabled');
					if(status){
						$requestPaymentButton.prop('disabled', true).addClass('ui-state-disabled');
						$confirmPaymentButton.prop('disabled', true).addClass('ui-state-disabled');
					}
				};
				calendarista.sales.prototype.disableButtons = function(){
					var $requestPaymentButton = this.$saleDetailsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="requestPayment"]')
						, $deleteButton = this.$saleDetailsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="delete"]')
						, $confirmPaymentButton = this.$saleDetailsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="confirmPayment"]');
					$requestPaymentButton.prop('disabled', true).addClass('ui-state-disabled');
					$deleteButton.prop('disabled', true).addClass('ui-state-disabled');
					$confirmPaymentButton.prop('disabled', true).addClass('ui-state-disabled');
				};
				calendarista.sales.prototype.readAppointmentResponse = function(result){
					this.$readAppointmentPlaceHolder.replaceWith('<div class="read_appointment_placeholder">' + result + '</div>');
					this.$readAppointmentPlaceHolder = $('.read_appointment_placeholder');
				};
				calendarista.sales.prototype.getDate = function(element){
					var date;
					try {
						date = $.datepicker.parseDate('yy-mm-dd', element.value);
					}catch(error){
						date = null;
					}
					return date;
				};
				calendarista.sales.prototype.saleDetailButtonClick = function(e){
					var $target = $(e.currentTarget)
						, orderId = parseInt($target.val(), 10)
						, paymentStatus = parseInt($target.attr('data-calendarista-payment-status'), 10)
						, projectId = parseInt($target.attr('data-calendarista-project-id'), 10)
						, model = [
							{ 'name': 'orderId', 'value': orderId}
							, { 'name': 'action', 'value': this.actionSaleDetails }
							, { 'name': 'calendarista_nonce', 'value': this.nonce }
						];
					e.preventDefault();
					this.orderId = orderId;
					this.projectId = projectId;
					this.$saleDetailsModalDialog.dialog('open');
					this.paymentButtonState(paymentStatus);
					this.ajax1.request(this, this.saleResponse, $.param(model));
					return false;
				};
				calendarista.sales.prototype.viewAppointmentButtonClick = function(e){
					var $target = $(e.currentTarget)
						, projectId =  parseInt($target.attr('data-calendarista-project-id'), 10)
						, availabilityId = parseInt($target.attr('data-calendarista-availability-id'), 10)
						, orderId = parseInt($target.val(), 10)
						, model = [
							{ 'name': 'projectId', 'value': projectId }
							, { 'name': 'availabilityId', 'value': availabilityId }
							, { 'name': 'appointment', 'value': 1 }
							, { 'name': 'orderId', 'value': orderId }
							, { 'name': 'salesInfoRequest', 'value': 1 }
							, { 'name': 'action', 'value': this.actionReadAppointment }
							, { 'name': 'calendarista_nonce', 'value': this.nonce }
						];
					this.eventData = {'projectId': projectId , 'availabilityId': availabilityId, 'orderId': orderId };
					this.$readAppointmentModalDialog.dialog('widget').find('.ui-dialog-buttonset').addClass('calendarista_' + this.eventData['projectId']);
					this.$readAppointmentModalDialog.dialog('open');
					this.readAppointmentAjax.request(this, this.readAppointmentResponse, $.param(model));
					return false;
				};
				calendarista.sales.prototype.salesListRequest = function(cleanUrl, values){
				var paged = $('input[name="paged"]').val()
					, orderby = $('input[name="orderby"]').val()
					, order = $('input[name="order"]').val()
					, from = $('input[name="from"]').val()
					, to = $('input[name="to"]').val()
					, projectId = this.$searchFilterProjectList.val()
					, availabilityId = $('select[name="availabilityId"]').val()
					, customerName = $('input[name="customerName" ]').val()
					, email = $('input[name="email"]').val()
					, invoiceId = $('input[name="invoiceId"]').val()
					, url = window.location.pathname + window.location.search
					, model = [
						{ 'name': 'projectId', 'value': projectId }
						, { 'name': 'availabilityId', 'value': availabilityId }
						, { 'name': 'current_url', 'value': url }
						, { 'name': 'customerName', 'value': customerName }
						, { 'name': 'email', 'value': email }
						, { 'name': 'invoiceId', 'value': invoiceId }
						, { 'name': 'from', 'value': from }
						, { 'name': 'to', 'value': to }
						, { 'name': 'action', 'value': this.actionGetSalesList }
						, { 'name': 'calendarista_nonce', 'value': this.nonce }
					];
				if(!cleanUrl){
					model.push({ 'name': 'orderby', 'value': orderby } , { 'name': 'order', 'value': order });
					if(!values){
						model.push({ 'name': 'paged', 'value': paged });
					}
				}
				if(values){
					model = model.concat(values);
				}
				window.history.replaceState({}, document.title, window.location.href);
				this.ajax3.request(this, this.salesListResponse, $.param(model));
			};
			calendarista.sales.prototype.salesListResponse = function(result){
				var context = this;
				this.$saleDetailsButton.off();
				this.$viewAppointmentButtons.off();
				this.$salesList.replaceWith('<div id="calendarista_sales_list">' + result + '</div>');
				this.$salesList = $('#calendarista_sales_list');
				this.$saleDetailsButton = $('button[name="details"]');
				this.$saleDetailsButton.on('click', this.saleDetailButtonClickDelegate);
				this.$viewAppointmentButtons = $('button[name="viewAppointment"]');
				this.$viewAppointmentButtons.on('click', this.viewAppointmentButtonClickDelegate);
				this.pagerButtonDelegates();
			};
			calendarista.sales.prototype.pagerButtonDelegates = function(){
				var context = this;
				this.$nextPage = $('a[class="next-page"]');
				this.$lastPage = $('a[class="last-page"]');
				this.$prevPage = $('a[class="prev-page"]');
				this.$firstPage = $('a[class="first-page"]');
				this.$nextPage.on('click', function(e){
					context.gotoPage(e);
				});
				this.$lastPage.on('click', function(e){
					context.gotoPage(e);
				});
				this.$prevPage.on('click', function(e){
					context.gotoPage(e);
				});
				this.$firstPage.on('click', function(e){
					context.gotoPage(e);
				});
			};
			calendarista.sales.prototype.gotoPage = function(e){
				var pagedValue = this.getUrlParameter('paged', $(e.currentTarget).prop('href'))
					, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
				this.$nextPage.off();
				this.$lastPage.off();
				this.$prevPage.off();
				this.$firstPage.off();
				this.salesListRequest(false, model);
				e.preventDefault();
				return false;
			};
			calendarista.sales.prototype.removeURLParameter = function(parameter) {
				 var url = window.location.href;
				//prefer to use l.search if you have a location/link object
				var urlparts= url.split('?');   
				if (urlparts.length>=2) {

					var prefix= encodeURIComponent(parameter)+'=';
					var pars= urlparts[1].split(/[&;]/g);

					//reverse iteration as may be destructive
					for (var i= pars.length; i-- > 0;) {    
						//idiom for string.startsWith
						if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
							pars.splice(i, 1);
						}
					}

					url= urlparts[0]+'?'+pars.join('&');
				}
				window.history.replaceState({}, document.title, url);
			};
			calendarista.sales.prototype.getUrlParameter = function(param, url) {
				var regex, results;
				param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
				regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
				results = regex.exec(url);
				return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.sales({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
		<?php
	}
}