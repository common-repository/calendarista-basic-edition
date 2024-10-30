<?php
class Calendarista_ManageAppointments extends Calendarista_ViewBase{
	public $generalSetting;
	public $url;
	public $appointmentList;
	public $fromDate;
	public $toDate;
	public $locale = 'en';
	function __construct( ){
		parent::__construct(false, true, 'calendarista-appointments', Calendarista_PermissionHelper::staffMemberProjects());
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->fromDate = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : null;
		$this->toDate = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : null;
		$this->url = admin_url() . 'admin.php?page=calendarista-appointments';
		
		$this->selectedTab = !isset($_GET['calendarista-tab']) ? $this->generalSetting->appointmentTabOrder : $this->selectedTab;
		if($this->selectedTab === 1){
			$this->appointmentList = new Calendarista_AppointmentList();
			$this->appointmentList->bind();
		}
		$locale = apply_filters('plugin_locale',  get_locale(), 'calendarista');
		if($locale){
			$loc = explode('_', $locale);
			$this->locale = strtolower($loc[0]);
		}
		$this->saveTabOrderIndex();
		$this->render();
	}
	
	public function saveTabOrderIndex(){
		if(!isset($_GET['calendarista-tab']) || $this->selectedTab === 2/*public calendar tab*/){
			return;
		}
		$repo = new Calendarista_GeneralSettingsRepository();
		$this->generalSetting->appointmentTabOrder = $this->selectedTab;
		$repo->update($this->generalSetting);
	}
	public function orderByAppointmentListSelectedValue($value){
		if($this->generalSetting->appointmentListOrder == $value){
			return 'selected';
		}
		return null;
	}
	public function render(){
	?>
	<?php if(in_array($this->selectedTab, array(0,1))): ?>
	<div class="wrap">
		<div class="column-pane">
			<p class="description"><?php esc_html_e('Create a new appointment below or click an available appointment in the calendar to edit an existing appointment', 'calendarista') ?></p>
			<form id="create_appointment_form" data-parsley-validate="">
				<label for="projectId"><?php esc_html_e('Service', 'calendarista') ?></label>
				<?php $this->renderProjectSelectList(true, __('Select a service', 'calendarista'), true, true) ?>
				&nbsp;
				<button type="button" name="newAppointment" class="button button-primary">
					<?php esc_html_e('Create Appointment', 'calendarista') ?>
				</button>
			</form>
		</div>
		<div class="column-pane">
			<?php if($this->selectedTab === 1): ?>
			<p class="description"><?php esc_html_e('All fields are optional', 'calendarista') ?></p>
			<p>
				<label for="from"><?php esc_html_e('Find reservation made on', 'calendarista') ?></label>
				<input 
					type="text" 
					id="from" 
					name="from" 
					class="medium-text enable-readonly-input" 
					readonly
					value="<?php echo  $this->fromDate ?>">
				<label for="to"><?php esc_html_e('or between', 'calendarista') ?></label>
				<input 
					type="text" 
					id="to" 
					name="to" 
					class="medium-text enable-readonly-input" 
					readonly
					value="<?php echo  $this->toDate ?>">
			</p>
			<?php endif; ?>
			<p class="searchfilter">
				<?php $this->renderProjectSelectList(true, __('All services', 'calendarista')) ?>
				&nbsp;<?php esc_html_e('and', 'calendarista') ?>&nbsp;
				<select name="availabilityId">
					<option value="-1"><?php esc_html_e('All availabilities', 'calendarista') ?></option>
				</select>
				<span id="spinner_callback" class="calendarista-spinner calendarista-invisible">
					<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
				</span>
				<br class="clear">
			</p>
			<?php if($this->selectedTab === 1): ?>
			<p>
				<input type="text" 
						id="customerName" 
						name="customerName" 
						class="medium-text"
						placeholder="<?php esc_html_e('Customer name', 'calendarista') ?>" />
				<?php esc_html_e('or by', 'calendarista') ?>
				<input type="text" 
						id="email" 
						name="email" 
						class="medium-text"
						placeholder="<?php esc_html_e('Customer email', 'calendarista') ?>"
						data-parsley-errors-container="#email-error-container"
						data-parsley-type="email" 
						data-parsley-trigger="change" />
				<?php esc_html_e('or by', 'calendarista') ?>
				<input type="text" 
						id="invoiceId" 
						name="invoiceId"
						class="medium-text"
						placeholder="<?php esc_html_e('ID', 'calendarista') ?>"/>
			</p>
			<p>
			  <?php esc_html_e('Where the appointment status is', 'calendarista') ?>
			  <input type="radio" name="status" value="" checked><?php esc_html_e('Any', 'calendarista') ?>
			  <input type="radio" name="status" value="0"><?php esc_html_e('Pending', 'calendarista') ?>
			  <input type="radio" name="status" value="1"><?php esc_html_e('Approved', 'calendarista') ?>
			  <input type="radio" name="status" value="2"><?php esc_html_e('Cancelled', 'calendarista') ?>
			</p>
			<?php endif; ?>
			<p>
				<input type="radio" name="syncDataFilter" value="0">
				<?php esc_html_e('Imported, Exported and Regular bookings', 'calendarista') ?>
				<input type="radio" name="syncDataFilter" value="1" checked>
				<?php esc_html_e('Regular bookings made through Calendarista', 'calendarista') ?>
				<input type="radio" name="syncDataFilter" value="2">
				<?php esc_html_e('Only imported bookings', 'calendarista') ?>
			</p>
		</div>
	</div>
	<?php endif; ?>
	<?php if($this->selectedTab === 0): ?>
	<p>
		<i class="calendarista-more-info fa fa-address-card fa-lg" style="color: <?php echo esc_html($this->generalSetting->approvedColor) ?>"></i>
		&nbsp;<?php esc_html_e('Approved', 'calendarista') ?>&nbsp;&nbsp;
		<i class="calendarista-more-info fa fa-address-card fa-lg" style="color: <?php echo esc_html($this->generalSetting->pendingApprovalColor) ?>">
		</i>&nbsp;<?php esc_html_e('Pending approval', 'calendarista') ?>&nbsp;&nbsp;
		<i class="calendarista-more-info fa fa-address-card fa-lg" style="color: <?php echo esc_html($this->generalSetting->cancelledColor) ?>"></i>
		&nbsp;<?php esc_html_e('Cancelled', 'calendarista') ?>
	</p>
	<?php endif; ?>
	<div style="margin: 10px 20px 0 2px;">
		<div class="column-pane">
			<h2 class="calendarista nav-tab-wrapper">
				<a class="nav-tab <?php echo $this->selectedTab === 0 ? 'nav-tab-active' : '' ?>" 
					href="<?php echo $this->url  . '&calendarista-tab=0' ?>" data-calendarista-tabindex="0"><?php esc_html_e('Calendar view', 'calendarista') ?>
					&nbsp;<i class="fa fa-calendar" aria-hidden="true"></i>
				</a>
				<a class="nav-tab <?php echo $this->selectedTab === 1 ? 'nav-tab-active' : '' ?>" 
					href="<?php echo $this->url . '&calendarista-tab=1' ?>" data-calendarista-tabindex="1"><?php esc_html_e('List view', 'calendarista') ?>
					&nbsp;<i class="fa fa-list" aria-hidden="true"></i>
				</a>
				<a class="nav-tab <?php echo $this->selectedTab === 2 ? 'nav-tab-active' : '' ?>" 
					href="<?php echo $this->url . '&calendarista-tab=2' ?>" data-calendarista-tabindex="1"><?php esc_html_e('Public Calendar', 'calendarista') ?>
					&nbsp;<i class="fa fa-share-alt" aria-hidden="true"></i>
				</a>
			</h2>
			<?php if($this->selectedTab === 0):?>
				<div id="calendarista_calendar" class="calendarista-admin-fullcalendar"></div>
			<?php elseif($this->selectedTab === 1): ?>
				<div>
				<span id="spinner_update_appointment_list" class="calendarista-spinner calendarista-invisible">
					<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
				</span>
				</div>
				<div>
					<?php esc_html_e('Order results by', 'calendarista') ?>:&nbsp;
					<select id="orderBy" name="orderBy">
						<option value="0" <?php echo $this->orderByAppointmentListSelectedValue(0) ?>><?php esc_html_e('Appointment start date ASC', 'calendarista') ?></option>
						<option value="1" <?php echo $this->orderByAppointmentListSelectedValue(1) ?>><?php esc_html_e('Appointment start date DESC', 'calendarista') ?></option>
						<option value="2" <?php echo $this->orderByAppointmentListSelectedValue(2) ?>><?php esc_html_e('Order date ASC', 'calendarista') ?></option>
						<option value="3" <?php echo $this->orderByAppointmentListSelectedValue(3) ?>><?php esc_html_e('Order date DESC', 'calendarista') ?></option>
					</select>
				</div>
				<div id="calendarista_appointment_list" class="table-responsive">
					<?php $this->appointmentList->printVariables() ?>
					<?php $this->appointmentList->display() ?>
				</div>
			<?php elseif($this->selectedTab === 2): ?>
				<?php new Calendarista_CalendarViewShortcodeTmpl(); ?>
				<br class="clear">
			<?php endif; ?>
		</div>
	</div>
	<div class="create-appointments-modal calendarista" 
			title="<?php esc_html_e('Create appointment', 'calendarista') ?>">
		<div class="container-fluid">
			<div class="create_appointment_placeholder"></div>
		</div>
	</div>
	<div class="read-appointment-modal calendarista" 
			title="<?php esc_html_e('Appointment details', 'calendarista') ?>">
		<div class="container-fluid">
			<div class="read_appointment_placeholder"></div>
		</div>
	</div>
	<div class="edit-appointments-modal calendarista" 
			title="<?php esc_html_e('Edit appointment', 'calendarista') ?>">
		<div class="container-fluid">
			<div class="edit_appointment_placeholder"></div>
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
			calendarista.appointments = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.appointments.prototype.init = function(options){
				var context = this;
				this.requestUrl = options['requestUrl'];
				this.selectedTabIndex = options['selectedTabIndex'];
				this.url = options['url'];
				this.actionWizard = 'calendarista_wizard';
				this.actionReadAppointment = 'calendarista_read_appointment';
				this.actionEditAppointment = 'calendarista_edit_appointment';
				this.actionUpdateAppointment = 'calendarista_update_appointment';
				this.actionDeleteAppointment = 'calendarista_delete_appointment';
				this.actionConfirmAppointment = 'calendarista_confirm_appointment';
				this.actionDeleteSyncAppointment = 'calendarista_delete_sync_appointment';
				this.actionGetAvailabilities = 'calendarista_get_availabilities';
				this.actionGetAppointmentList = 'calendarista_get_appointment_list';
				this.filterChangedDelegate = calendarista.createDelegate(this, this.filterChanged);
				this.$editAppointmentPlaceHolder = $('.edit_appointment_placeholder');
				this.$readAppointmentPlaceHolder = $('.read_appointment_placeholder');
				this.$createAppointmentForm = $('#create_appointment_form');
				this.$projectList = $('.searchfilter select[name="projectId"]');
				this.$availabilityList = $('.searchfilter select[name="availabilityId"]');
				this.$createAppointmentProjectList = this.$createAppointmentForm.find('select[name="projectId"]');
				this.$createAppointmentButton = this.$createAppointmentForm.find('button[name="newAppointment"]');
				this.$createAppointmentPlaceHolder = $('.create_appointment_placeholder');
				this.$availabilityDate = $('input[name="availabilityDate"]');
				this.$syncDataFilter = $('input[name="syncDataFilter"]');
				this.$returnDate = $('input[name="returnDate"]');
				this.$appointmentList = $('#calendarista_appointment_list');
				this.$editAppointmentListItem = $('.edit-appointment-list-item');
				this.$editAppointmentListItem.on('click', function(e){
					context.editAppointmentListItemClick(e);
				});
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.editAppointmentAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'edit_appointment'});
				this.readAppointmentAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'read_appointment'});
				this.createAppointmentAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'create_appointment'});
				this.deleteSyncAppointmentAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'delete_sync_appointment'});
				this.appointmentListAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'update_appointment_list'});
				this.fullCalendarAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'callback'});
				this.$customerName = $('input[name="customerName"]');
				this.$customerName.on('change', this.filterChangedDelegate);
				this.$status = $('input[name="status"]');
				this.$status.on('change', this.filterChangedDelegate);
				this.$email = $('input[name="email"]');
				this.$email.on('change', this.filterChangedDelegate);
				this.$invoiceId = $('input[name="invoiceId"]');
				this.$invoiceId.on('change', this.filterChangedDelegate);
				this.$orderBy = $('#orderBy');
				this.$orderBy.on('change', function(e){
					context.appointmentListRequest(true);
				});
				this.$syncDataFilter.on('change', this.filterChangedDelegate);
				this.$spinner = $('#spinner_callback');
				this.fc = new FullCalendar.Calendar($('#calendarista_calendar')[0], {
					plugins: ['dayGrid', 'timeGrid', 'list'],
					header: {
						left: 'prev, next, today'
						, right: 'dayGridMonth,timeGridWeek,timeGridDay'
						, center: 'title'
					},
					locale: '<?php echo $this->locale ?>',
					defaultView: context.mobileCheck() ? 'listWeek' : 'dayGridMonth',
					editable: false,
					eventLimit: true, // allow "more" link when too many events
					windowResize: function(view){
						if (context.mobileCheck()) {
							context.fc.changeView('listWeek');
							context.fc.setOption('header', {right: ''});
						} else {
							context.fc.changeView('dayGridMonth');
							context.fc.setOption('header', {right: 'dayGridMonth,timeGridWeek,timeGridDay'});
						}
					},
					eventSources: [{
						url: this.ajaxUrl
						, method: 'POST'
						, extraParams: function() {
							var result = {
								'action': 'calendarista_appointments_feed'
								, 'projectId': context.$projectList.val()
								, 'availabilityId': context.$availabilityList.val()
								, 'syncDataFilter': context.$syncDataFilter.filter(':checked').val()
								, 'calendarista_nonce': context.nonce
							};
							return result;
						}
						, success: function(){
						}
						, failure: function() {
							window.console.log('there was an error while fetching events!');
						}
					}],
					eventTimeFormat: {hour: 'numeric', minute: '2-digit'},
					eventRender: function(info) {
						var $element1 = $(info.el).find('.fc-title')
							, $element2 = $(info.el).find('.fc-list-item-title')
							, eventData = info.event.extendedProps
							, view = info.view
							, $target;
						if(eventData.headingfield){
							if(view.viewSpec.type === 'listWeek'){
								$element2.html(eventData.rawTitle);
							}else{
								$element1.html(info.event.title + '<br class="calendarista-more">' + eventData.description);
							}
						}
						$target = $element1.find('.calendarista-more-info');
						$target.webuiPopover({ width: '300px', height: 'auto', title: eventData.rawTitle, content: eventData.rawDescription, closeable: true, trigger: 'manual'});
						$target.on('click', (function (info, $target) {
							return function(e){
								e.stopPropagation();
								$target.webuiPopover('show');
							}
						})(info, $target));
					}, 
					eventClick: function(info) {
						var eventData = info.event.extendedProps
							, status = eventData.status
							, model = [
								{ 'name': 'projectId', 'value': eventData.projectId }
								, { 'name': 'availabilityId', 'value': eventData.availabilityId}
								, { 'name': 'appointment', 'value': 1}
								, { 'name': 'orderId', 'value': eventData.orderId}
								, { 'name': 'bookedAvailabilityId', 'value': eventData.bookedAvailabilityId}
								, { 'name': 'action', 'value': context.actionReadAppointment }
								, { 'name': 'calendarista_nonce', 'value': context.nonce }
							];
						if(eventData.synched){
							context.showDeleteSyncAppointmentDialog(eventData);
							return false;
						}
						context.eventData = eventData;
						context.modalDialogButtons(context.$readAppointmentModalDialog, status);
						context.$readAppointmentModalDialog.dialog('widget').find('.ui-dialog-buttonset').addClass('calendarista_' + eventData['projectId']);
						context.$readAppointmentModalDialog.dialog('open');
						context.readAppointmentAjax.request(context, context.readAppointmentResponse, $.param(model));
						return false;
					},
					eventPositioned: function(info){
						var $el = $(context.fc.el)
							, $titleContainer = $el.find('.fc-toolbar.fc-header-toolbar .fc-center')
							, $title = $titleContainer.find('h2');
						if(context.$datePickerDummy){
							return;
						}
						$titleContainer.append('<div class="fc-clear"></div>');
						$titleContainer.append('<input type="text" id="datePickerDummy" class="calendarista-dummy-datepicker"/>');
						context.$datePickerDummy = $('#datePickerDummy').datepicker({
							dateFormat: 'yy-mm-dd',
							changeMonth: true,
							changeYear: true,
							showButtonPanel: true,
							onClose: function(dateText, inst) {
								var view;
								if(!dateText){
									return;
								}
								context.fc.gotoDate(dateText);
								//view = context.fc.fullCalendar('getView');
							}
						});
						$title.on('click', function(){
							context.$datePickerDummy.datepicker('show');
						});
					},
					loading: function( isLoading, view ) {
						if(isLoading) {
							context.showSpinner();
						} else {
							context.hideSpinner();
						}
					}
				});
				if(this.fc.el){
					this.fc.render();
				}
				this.$createAppointmentButton.on('click', function(e){
					var result
						, projectId = parseInt(context.$createAppointmentProjectList.val(), 10)
						, $bookedAvailabilityId = $('input[name="bookedAvailabilityId"]')
						, bookedAvailabilityId = $bookedAvailabilityId.length > 0 ? parseInt($bookedAvailabilityId.val(), 10) : null
						, model = [
							{ 'name': 'projectId', 'value': projectId }
							, { 'name': 'bookedAvailabilityId', 'value': bookedAvailabilityId }
							, { 'name': 'appointment', 'value': 1}
							, { 'name': 'editMode', 'value': 0 }
							, { 'name': 'action', 'value': context.actionWizard }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }
						];
						result = context.$createAppointmentProjectList.parsley ? context.$createAppointmentProjectList.parsley().validate() : true;
						if(result !== null && (typeof(result) === 'object' && result.length > 0)){
							return false;
						}
						context.$createAppointmentsModalDialog.dialog('widget').find('.ui-dialog-buttonset').addClass('calendarista_' + projectId);
						context.$createAppointmentsModalDialog.dialog('open');
						context.createAppointmentAjax.request(context, context.createAppointmentResponse, $.param(model));
						return false;
				});
				this.$editAppointmentsModalDialog = $('.edit-appointments-modal').dialog({
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
						var spinner = '<div id="spinner_edit_appointment" style="margin-right: 10px" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
							spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">';
							spinner += '</div>';
						$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
					}
					, buttons: [
						{
							'text': '<?php echo $this->decodeString(__('Update', 'calendarista')) ?>'
							, 'name': 'updateAppointment'
							, 'click':  function(){
								var model
									, $form = context.$editAppointmentsModalDialog.find('form');
								if(!context.isValid($form)){
									return false;
								}
								context.$editAppointmentsModalDialog.find('input[name="controller"]').val('calendarista_appointments');
								model = $form.serialize();
								model += '&calendarista_nonce=' + context.nonce + '&appointment=1&editMode=1&calendarista_update=1&action=' + context.actionUpdateAppointment;
								context.editAppointmentAjax.request(context, context.editAppointmentResponse, model);
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__('Delete', 'calendarista')) ?>'
							, 'name': 'deleteAppointment'
							, 'click':  function(){
								var $dialog
									, model = [
									{ 'name': 'appointment', 'value': 1}
									, { 'name': 'orderId', 'value': context.eventData.orderId}
									, { 'name': 'bookedAvailabilityId', 'value': context.eventData.bookedAvailabilityId}
									, { 'name': 'controller', 'value': 'calendarista_appointments'}
									, { 'name': 'action', 'value': context.actionDeleteAppointment }
									, { 'name': 'calendarista_nonce', 'value': context.nonce }
								];
								$dialog = $('<p title="<?php echo $this->decodeString(__('Delete appointment', 'calendarista')) ?>"><?php echo $this->decodeString(__('You are about to delete this appointment. Are you sure?', 'calendarista'));?></p>').dialog({
									dialogClass: 'calendarista-dialog'
									, buttons: {
										'Yes': function() {
											context.editAppointmentAjax.request(context, context.deleteAppointmentResponse, $.param(model));
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
							'text': '<?php echo $this->decodeString(__('Confirm Appointment', 'calendarista')) ?>'
							, 'name': 'status'
							, 'click':  function(e){
								var $dialog
								, $target = $(e.currentTarget)
								, status = parseInt($target.val(), 10)
								, model = [
									{ 'name': 'projectId', 'value': context.eventData.projectId }
									, { 'name': 'availabilityId', 'value': context.eventData.availabilityId}
									, { 'name': 'appointment', 'value': 1}
									, { 'name': 'updateAppointmentStatus', 'value': true}
									, { 'name': 'status', 'value': status}
									, { 'name': 'orderId', 'value': context.eventData.orderId}
									, { 'name': 'bookedAvailabilityId', 'value': context.eventData.bookedAvailabilityId}
									, { 'name': 'editMode', 'value': 1 }
									, { 'name': 'controller', 'value': 'calendarista_appointments'}
									, { 'name': 'action', 'value': context.actionWizard }
									, { 'name': 'calendarista_nonce', 'value': context.nonce }
								];
								if(status === 2){
									$dialog = $('<p title="<?php echo $this->decodeString(__('Cancel appointment', 'calendarista')) ?>"><?php echo $this->decodeString(__('A cancelled appointment cannot be edited or approved again. Are you sure?', 'calendarista'));?></p>').dialog({
										dialogClass: 'calendarista-dialog'
										, buttons: {
											'Yes': function() {
												context.modalDialogButtons(context.$editAppointmentsModalDialog, status);
												context.editAppointmentAjax.request(context, context.editAppointmentResponse, $.param(model));
												$dialog.dialog('close');
											}
											, 'Cancel':  function() {
												$dialog.dialog('close');
											}
										}
									});
								}else{
									context.modalDialogButtons(context.$editAppointmentsModalDialog, status);
									context.editAppointmentAjax.request(context, context.editAppointmentResponse, $.param(model));
								}
								return false;
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__('Exit', 'calendarista')) ?>'
							, 'name': 'dispose'
							, 'click':  function(){
								context.$editAppointmentsModalDialog.dialog('close');
								if(context.fc.el){
									context.fc.refetchEvents();
								}else{
									context.appointmentListRequest();
								}
								context.$editAppointmentPlaceHolder.empty();
							}
						}
					]
				});
				this.$createAppointmentsModalDialog = $('.create-appointments-modal').dialog({
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
						var spinner = '<div id="spinner_create_appointment" class="calendarista-spinner ui-widget ui-button calendarista-invisible">'
							, $closeButton = $(this).closest('div.ui-dialog').find('.ui-dialog-titlebar-close');
							spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">';
							spinner += '</div>';
						$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
					}
					, buttons: [
						{
							'text': '<?php echo $this->decodeString(__("Previous", "calendarista")) ?>'
							, 'name': 'prev'
							, 'click':  function(){
								return false;
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__("Next", "calendarista")) ?>'
							, 'name': 'next'
							, 'click':  function(){
								return false;
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__("Create", "calendarista")) ?>'
							, 'name': 'booknow'
							, 'click':  function(){
								var $form = context.$createAppointmentsModalDialog.find('form')
									, model = $form.serialize();
								if(!context.isValid($form)){
									return false;
								}
								model += '&calendarista_nonce=' + context.nonce + '&booknow=1&action=' + context.actionWizard;
								context.createAppointmentAjax.request(context, context.completedAppointmentResponse, model);
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__("Exit", "calendarista")) ?>'
							, 'name': 'dispose'
							, 'click':  function(){
								context.$createAppointmentsModalDialog.dialog('close');
								if(context.fc.el){
									context.fc.refetchEvents();
								}else{
									context.appointmentListRequest();
								}
								context.$createAppointmentPlaceHolder.empty();
							}
						}
					]
				});
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
						   context.fc.refetchEvents();
						   context.$editAppointmentPlaceHolder.empty();
					    });
					}
					, buttons: [
						{
							'text': '<?php echo $this->decodeString(__('Edit', 'calendarista')) ?>'
							, 'name': 'editAppointment'
							, 'click':  function(){
								var eventData = context.eventData
									, status = eventData.status
									, model = [
										{ 'name': 'projectId', 'value': eventData.projectId }
										, { 'name': 'availabilityId', 'value': eventData.availabilityId}
										, { 'name': 'orderId', 'value': eventData.orderId}
										, { 'name': 'editMode', 'value': 1 }
										, { 'name': 'appointment', 'value': 1}
										, { 'name': 'bookedAvailabilityId', 'value': eventData.bookedAvailabilityId}
										, { 'name': 'action', 'value': context.actionEditAppointment }
										, { 'name': 'calendarista_nonce', 'value': context.nonce }
									];
								if(!eventData.orderId){
									return false;
								}
								//close readonly dialog
								context.$readAppointmentModalDialog.dialog('close');
								context.$readAppointmentPlaceHolder.empty();
								
								context.eventData = eventData;
								context.modalDialogButtons(context.$editAppointmentsModalDialog, status);
								context.$editAppointmentsModalDialog.dialog('widget').find('.ui-dialog-buttonset').addClass('calendarista_' + eventData.projectId);
								context.$editAppointmentsModalDialog.dialog('open');
								
								context.editAppointmentAjax.request(context, context.editAppointmentResponse, $.param(model));
								return false;
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__('Delete', 'calendarista')) ?>'
							, 'name': 'deleteAppointment'
							, 'click':  function(){
								var $dialog
									, model = [
									{ 'name': 'appointment', 'value': 1}
									, { 'name': 'orderId', 'value': context.eventData.orderId}
									, { 'name': 'bookedAvailabilityId', 'value': context.eventData.bookedAvailabilityId}
									, { 'name': 'controller', 'value': 'calendarista_appointments'}
									, { 'name': 'action', 'value': context.actionDeleteAppointment }
									, { 'name': 'calendarista_nonce', 'value': context.nonce }
								];
								$dialog = $('<p title="<?php echo $this->decodeString(__('Delete appointment', 'calendarista')) ?>"><?php echo $this->decodeString(__('You are about to delete this appointment. Are you sure?', 'calendarista'));?></p>').dialog({
									dialogClass: 'calendarista-dialog'
									, buttons: {
										'Yes': function() {
											context.readAppointmentAjax.request(context, context.deleteAppointmentResponse, $.param(model));
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
							'text': '<?php echo $this->decodeString(__('Confirm Appointment', 'calendarista')) ?>'
							, 'name': 'status'
							, 'click':  function(e){
								var $dialog
								, $target = $(e.currentTarget)
								, status = parseInt($target.val(), 10)
								, model = [
									{ 'name': 'projectId', 'value': context.eventData.projectId }
									, { 'name': 'availabilityId', 'value': context.eventData.availabilityId}
									, { 'name': 'appointment', 'value': 1}
									, { 'name': 'updateAppointmentStatus', 'value': true}
									, { 'name': 'status', 'value': status}
									, { 'name': 'orderId', 'value': context.eventData.orderId}
									, { 'name': 'bookedAvailabilityId', 'value': context.eventData.bookedAvailabilityId}
									, { 'name': 'editMode', 'value': 1 }
									, { 'name': 'controller', 'value': 'calendarista_appointments'}
									, { 'name': 'action', 'value': context.actionConfirmAppointment }
									, { 'name': 'calendarista_nonce', 'value': context.nonce }
								];
								if(status === 2){
									$dialog = $('<p title="<?php echo $this->decodeString(__('Cancel appointment', 'calendarista')) ?>"><?php echo $this->decodeString(__('A cancelled appointment cannot be edited or approved again. Are you sure?', 'calendarista'));?></p>').dialog({
										dialogClass: 'calendarista-dialog'
										, buttons: {
											'Yes': function() {
												context.eventData.status = status;
												context.modalDialogButtons(context.$readAppointmentModalDialog, status);
												context.readAppointmentAjax.request(context, context.readAppointmentResponse, $.param(model));
												$dialog.dialog('close');
											}
											, 'Cancel':  function() {
												$dialog.dialog('close');
											}
										}
									});
								}else{
									context.modalDialogButtons(context.$readAppointmentModalDialog, status);
									context.readAppointmentAjax.request(context, context.readAppointmentResponse, $.param(model));
								}
								return false;
							}
						}
						, {
							'text': '<?php echo $this->decodeString(__('Close', 'calendarista')) ?>'
							, 'click':  function(){
								context.$readAppointmentModalDialog.dialog('close');
								if(context.fc.el){
									context.fc.refetchEvents();
								}else{
									context.appointmentListRequest();
								}
								context.$readAppointmentPlaceHolder.empty();
							}
						}
					]
				});
				this.$projectList.on('change', function(e){
					var val = parseInt($(this).val(), 10)
						, model = [
							{ 'name': 'projectId', 'value': val}
							, { 'name': 'action', 'value': context.actionGetAvailabilities }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }
						];
					context.$availabilityList[0].selectedIndex = 0;
					context.fullCalendarAjax.request(context, context.availabilitiesResponse, $.param(model));
					if(context.fc.el){
						context.fc.refetchEvents();
					}else{
						context.removeURLParameter('paged');
						context.removeURLParameter('order');
						context.removeURLParameter('orderby');
						context.appointmentListRequest(true);
					}
				});
				this.$availabilityList.on('change', function(e){
					if(context.fc.el){
						context.fc.refetchEvents();
					}else{
						context.removeURLParameter('paged');
						context.removeURLParameter('order');
						context.removeURLParameter('orderby');
						context.appointmentListRequest(true);
					}
				});
				this.dateClearDelegate = calendarista.createDelegate(this, this.dateClear);
				this.datepickerOptions = {
					'changeMonth': true
					, 'dateFormat': 'yy-mm-dd'
					, 'changeYear': true
					, 'showButtonPanel': true
					, 'closeText': 'Clear'
					, 'onClose': this.dateClearDelegate
					, 'minDate': new Date(1999, 1, 1)
				};
				this.$fromDate = $('input[name="from"]');
				this.$toDate = $('input[name="to"]');
				this.$fromDate.datepicker(this.datepickerOptions).on('change', function() {
					context.$toDate.datepicker('option', 'minDate', context.getDate(this));
					context.removeURLParameter('paged');
					context.removeURLParameter('order');
					context.removeURLParameter('orderby');
					context.appointmentListRequest(true);
				});
				this.$toDate.datepicker(this.datepickerOptions).on('change', function() {
					context.$fromDate.datepicker('option', 'maxDate', context.getDate(this));
					context.removeURLParameter('paged');
					context.removeURLParameter('order');
					context.removeURLParameter('orderby');
					context.appointmentListRequest(true);
				});
				this.pagerButtonDelegates();
			};
			calendarista.appointments.prototype.filterChanged = function(){
				if(this.fc.el){
						this.fc.refetchEvents();
					}else{
						this.removeURLParameter('paged');
						this.removeURLParameter('order');
						this.removeURLParameter('orderby');
						this.appointmentListRequest(true);
					}
			};
			calendarista.appointments.prototype.pagerButtonDelegates = function(){
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
			calendarista.appointments.prototype.gotoPage = function(e){
				var pagedValue = this.getUrlParameter('paged', $(e.currentTarget).prop('href'))
					, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
				this.$nextPage.off();
				this.$lastPage.off();
				this.$prevPage.off();
				this.$firstPage.off();
				this.appointmentListRequest(false, model);
				e.preventDefault();
				return false;
			};
			calendarista.appointments.prototype.showSpinner = function(){
				this.$spinner.removeClass('calendarista-invisible');
			};
			calendarista.appointments.prototype.hideSpinner = function(){
				this.$spinner.addClass('calendarista-invisible');
			};
			calendarista.appointments.prototype.availabilitiesResponse = function(result){
				this.$availabilityList[0].length = 0;
				this.$availabilityList.append(result);
			};
			calendarista.appointments.prototype.deleteAppointmentResponse = function(result){
				if(this.fc.el){
					this.fc.refetchEvents();
				}else{
					this.appointmentListRequest();
				}
				this.$readAppointmentModalDialog.dialog('close');
				this.$editAppointmentsModalDialog.dialog('close');
				this.$readAppointmentPlaceHolder.empty();
				this.$editAppointmentPlaceHolder.empty();
			};
			calendarista.appointments.prototype.readAppointmentResponse = function(result){
				this.$readAppointmentPlaceHolder.replaceWith('<div class="read_appointment_placeholder">' + result + '</div>');
				this.$readAppointmentPlaceHolder = $('.read_appointment_placeholder');
			};
			calendarista.appointments.prototype.editAppointmentResponse = function(result){
				this.$editAppointmentPlaceHolder.replaceWith('<div class="edit_appointment_placeholder">' + result + '</div>');
				this.$editAppointmentPlaceHolder = $('.edit_appointment_placeholder');
				if(this.fc.el){
					this.fc.refetchEvents();
				}
			};
			calendarista.appointments.prototype.createAppointmentResponse = function(result){
				this.$createAppointmentPlaceHolder.replaceWith('<div class="create_appointment_placeholder">' + result + '</div>');
				this.$createAppointmentPlaceHolder = $('.create_appointment_placeholder');
				var $nextButton = this.$createAppointmentsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="next"]')
					, $availableDate = this.$createAppointmentPlaceHolder.find('input[name="availableDate"]');
				if(!$availableDate.val()){
					$nextButton.prop('disabled', true).addClass('ui-state-disabled');
				}
			};
			calendarista.appointments.prototype.completedAppointmentResponse = function(result){
				if(this.fc.el){
					this.fc.refetchEvents();
				}
				this.$createAppointmentPlaceHolder.replaceWith('<div class="create_appointment_placeholder">' + result + '</div>');
				this.$createAppointmentPlaceHolder = $('.create_appointment_placeholder');
				var prevButton = this.$createAppointmentsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="prev"]')
					, createButton = this.$createAppointmentsModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="booknow"]');
				prevButton.prop('disabled', true).addClass('ui-state-disabled');
				createButton.prop('disabled', true).addClass('ui-state-disabled');
			};
			calendarista.appointments.prototype.appointmentListRequest = function(cleanUrl, values){
				var paged = $('input[name="paged"]').val()
					, order = $('input[name="order"]').val()
					, projectId = this.$projectList.val()
					, availabilityId = this.$availabilityList.val()
					, syncDataFilter = this.$syncDataFilter.filter(':checked').val()
					, url = window.location.pathname + window.location.search
					, statusValue = this.$status.filter(':checked').val()
					, model = [
						{ 'name': 'projectId', 'value': projectId }
						, { 'name': 'availabilityId', 'value': availabilityId }
						, { 'name': 'syncDataFilter', 'value': syncDataFilter }
						, { 'name': 'current_url', 'value': url }
						, { 'name': 'start', 'value': this.$fromDate.val() }
						, { 'name': 'end', 'value': this.$toDate.val() }
						, { 'name': 'orderby', 'value': this.$orderBy.val() }
						, { 'name': 'customerName', 'value': this.$customerName.val() }
						, { 'name': 'email', 'value': this.$email.val() }
						, { 'name': 'invoiceId', 'value': this.$invoiceId.val() }
						, { 'name': 'status', 'value': statusValue }
						, { 'name': 'action', 'value': this.actionGetAppointmentList }
						, { 'name': 'calendarista_nonce', 'value': this.nonce }
					];
				if(!cleanUrl){
					if(!values){
						model.push({ 'name': 'paged', 'value': paged });
					}
				}
				if(values){
					model = model.concat(values);
				}
				window.history.replaceState({}, document.title, window.location.href);
				this.appointmentListAjax.request(this, this.appointmentListResponse, $.param(model));
			};
			calendarista.appointments.prototype.appointmentListResponse = function(result){
				var context = this;
				this.$editAppointmentListItem.off();
				this.$appointmentList.replaceWith('<div id="calendarista_appointment_list">' + result + '</div>');
				this.$appointmentList = $('#calendarista_appointment_list');
				this.$editAppointmentListItem = $('.edit-appointment-list-item');
				this.$editAppointmentListItem.on('click', function(e){
					context.editAppointmentListItemClick(e);
				});
				this.pagerButtonDelegates();
			};
			calendarista.appointments.prototype.showDeleteSyncAppointmentDialog = function(eventData){
				var context = this
					, model = [
						{ 'name': 'synchedBookingId', 'value': eventData.synchedBookingId }
						, { 'name': 'deleteSyncAppointment', 'value': true}
						, { 'name': 'controller', 'value': 'calendarista_appointments'}
						, { 'name': 'action', 'value': context.actionDeleteSyncAppointment }
						, { 'name': 'calendarista_nonce', 'value': context.nonce }
					];
				this.$deleteSyncAppointmentDialog = $('<p title="' + eventData.rawTitle + '">' + eventData.rawDescription + '</p>').dialog({
					dialogClass: 'calendarista-dialog'
					, create: function() {
						var spinner = '<div id="spinner_delete_sync_appointment" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
							spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">';
							spinner += '</div>';
						$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
					    $(this).closest('div.ui-dialog').find('.ui-dialog-titlebar-close').on('click', function(e) {
						   e.preventDefault();
						   context.fc.refetchEvents();
					    });
					}
					, buttons: {
						'Delete': function() {
							context.deleteSyncAppointmentAjax.request(context, context.deleteSyncAppointmentResponse, $.param(model));
						}
						, 'Cancel':  function() {
							context.$deleteSyncAppointmentDialog.dialog('close');
						}
					}
				});
			};
			calendarista.appointments.prototype.deleteSyncAppointmentResponse = function(result){
				if(this.fc.el){
					this.fc.refetchEvents();
				}else{
					this.appointmentListRequest();
				}
				this.$deleteSyncAppointmentDialog.dialog('close');
			};
			calendarista.appointments.prototype.modalDialogButtons = function($dialog, status){
				var $buttonStatus = $dialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="status"]')
					, $buttonUpdate = $dialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="updateAppointment"]')
					, $buttonEdit = $dialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="editAppointment"]');
				$buttonStatus.button('option', 'label', '<?php echo $this->decodeString(__('Confirm Appointment', 'calendarista'))?>');
				$buttonStatus.prop('disabled', false).removeClass('ui-state-disabled');
				$buttonUpdate.prop('disabled', false).removeClass('ui-state-disabled');
				$buttonEdit.prop('disabled', false).removeClass('ui-state-disabled');
				switch(status){
					case 0:
						$buttonStatus.prop('value', 1);
					break;
					case 1:
						$buttonStatus.button('option', 'label', '<?php echo $this->decodeString(__('Cancel Appointment', 'calendarista'))?>');
						$buttonStatus.prop('value', 2);
					break;
					case 2:
						$buttonStatus.prop('disabled', true).addClass('ui-state-disabled');
						$buttonUpdate.prop('disabled', true).addClass('ui-state-disabled');
						$buttonEdit.prop('disabled', true).addClass('ui-state-disabled');
					break;
				}
			};
			calendarista.appointments.prototype.editAppointmentListItemClick = function(e) {
				var $target = $(e.currentTarget)
					, bookedAvailabilityId = parseInt($target.attr('data-calendarista-id'), 10)
					, projectId = parseInt($target.attr('data-calendarista-project-id'), 10)
					, availabilityId = parseInt($target.attr('data-calendarista-availability-id'), 10)
					, orderId = parseInt($target.attr('data-calendarista-order-id'), 10)
					, status = parseInt($target.attr('data-calendarista-status'), 10)
					, synchedBookingId = $target.attr('data-calendarista-synched-booking-id')
					, synched = parseInt($target.attr('data-calendarista-synched'), 10)
					, rawTitle = $target.attr('data-calendarista-raw-title')
					, rawDescription = atob($target.attr('data-calendarista-raw-description'))
					, eventData = {
						'projectId': projectId
						, 'availabilityId': availabilityId
						, 'orderId': orderId
						, 'status': status
						, 'synchedBookingId': synchedBookingId
						, 'synched': synched ? true : false
						, 'rawTitle': rawTitle
						, 'rawDescription': rawDescription
						, 'bookedAvailabilityId': bookedAvailabilityId
					} 
					, model = [
						{ 'name': 'projectId', 'value': eventData.projectId }
						, { 'name': 'availabilityId', 'value': eventData.availabilityId}
						, { 'name': 'appointment', 'value': 1}
						, { 'name': 'orderId', 'value': eventData.orderId}
						, { 'name': 'bookedAvailabilityId', 'value': bookedAvailabilityId}
						, { 'name': 'action', 'value': this.actionReadAppointment }
						, { 'name': 'calendarista_nonce', 'value': this.nonce }
					];
				if(eventData.synched){
					this.showDeleteSyncAppointmentDialog(eventData);
					return false;
				}
				this.eventData = eventData;
				this.modalDialogButtons(this.$readAppointmentModalDialog, status);
				this.$readAppointmentModalDialog.dialog('widget').find('.ui-dialog-buttonset').addClass('calendarista_' + eventData.projectId);
				this.$readAppointmentModalDialog.dialog('open');
				this.readAppointmentAjax.request(this, this.readAppointmentResponse, $.param(model));
				return false;
			};
			calendarista.appointments.prototype.isValid = function($form){
				var  $multiDateSelection = $form.find('input[name="multiDateSelection"]')
					, $bookingDaysMinimum = $form.find('input[name="bookingDaysMinimum"]')
					, bookingDaysMinimum = $bookingDaysMinimum.length > 0 ? parseInt($bookingDaysMinimum.val(), 10) : 0
					, multiDatesVal = $multiDateSelection.val()
					, multiDates = multiDatesVal ? multiDatesVal.split(';') : []
					, sel = (multiDates.length > 0 && (!bookingDaysMinimum || multiDates.length >= bookingDaysMinimum)) ? '.calendarista-dynamicfield.calendarista_parsley_validated' : null;
				if(Calendarista.wizard.isValid($form, sel)){
					return true;
				}
				return false;
			};
			calendarista.appointments.prototype.removeURLParameter = function(parameter) {
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
			calendarista.appointments.prototype.getUrlParameter = function(param, url) {
				var regex, results;
				param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
				regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
				results = regex.exec(url);
				return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
			};
			calendarista.appointments.prototype.dateClear = function(e){
				if (!$(window.event.srcElement).hasClass('ui-datepicker-close')){
					return;
				}
				this.$fromDate.datepicker('setDate', null);
				this.$toDate.datepicker('setDate', null);
				this.$fromDate.datepicker('option', 'maxDate', null);
				this.$toDate.datepicker('option', 'minDate', null);
				this.removeURLParameter('paged');
				this.removeURLParameter('order');
				this.removeURLParameter('orderby');
				this.appointmentListRequest(true);
			};
			calendarista.appointments.prototype.getDate = function(element){
				var date;
				try {
					date = $.datepicker.parseDate('yy-mm-dd', element.value);
				}catch(error){
					date = null;
				}
				return date;
			};
			calendarista.appointments.prototype.mobileCheck = function() {
				if (window.innerWidth >= 768 ) {
					return false;
				} else {
					return true;
				}
			};
		window['calendarista'] = calendarista;
	})(window['jQuery'], window['calendarista_wp_ajax']);
	new calendarista.appointments({
		'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		, 'url': '<?php echo $this->url ?>'
		, 'selectedTabIndex': <?php echo esc_html($this->selectedTab) ?>});
	</script>
		<?php
	}
}