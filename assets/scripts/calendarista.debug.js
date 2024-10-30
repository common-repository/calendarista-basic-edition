(function(window){
	"use strict";
	var Calendarista = window['Calendarista'] || {};
	/**
		@description The createDelegate function is useful when setting up an event handler to point 
		to an object method that must use the this pointer within its scope.
	*/
	Calendarista.createDelegate = function (instance, method) {
		return function () {
			return method.apply(instance, arguments);
		};
	}
	
	/**
		@description Allows us to retain the this context and optionally pass an arbitrary list of parameters.
	*/
	Calendarista.createCallback = function (method, context, params) {
		return function() {
			var l = arguments.length;
			if (l > 0) {
				var args = [];
				for (var i = 0; i < l; i++) {
					args[i] = arguments[i];
				}
				args[l] = params;
				return method.apply(context || this, args);
			}
			return method.call(context || this, params);
		}
	}
	if(!window['Calendarista']){
		window['Calendarista'] = Calendarista;
	}
}(window));
/**
	 @license Copyright @ 2018 Alessandro Zifiglio. All rights reserved. https://www.calendarista.com
*/
(function(window){
	"use strict";
	var Calendarista = window['Calendarista'] || function(){};
	Calendarista.Cookie = function(){};
	Calendarista.Cookie.create =  function(name,value,days) {
		var date
			, expires;
		if (days) {
			date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			expires = "; expires="+date.toGMTString();
		}
		else {
			expires = "";
		}
		document.cookie = name+"="+value+expires+"; path=/";
	};
	Calendarista.Cookie.read = function(name) {
		var nameEQ = name + "="
			, ca = document.cookie.split(';')
			, c
			, i;
		for(i=0;i < ca.length;i++) {
			c = ca[i];
			while (c.charAt(0)==' '){ 
				c = c.substring(1,c.length);
			}
			if (c.indexOf(nameEQ) == 0){ 
				return c.substring(nameEQ.length,c.length);
			}
		}
		return null;
	};
	Calendarista.Cookie.erase = function(name) {
		Calendarista.Cookie.create(name,"",-1);
	};
	Calendarista.Cookie.destroy = function( ) {};
	if(!window['Calendarista']){
		window['Calendarista'] = Calendarista;
	}
})(window);
(function($, Calendarista){
	"use strict";
	Calendarista.ajax = function(options){
		this.init(options);
		this.destroyDelegate = Calendarista.createDelegate(this, this.destroy);
		$(window).on('unload', this.destroyDelegate);
	};
	Calendarista.ajax.prototype.init = function(options){
		var context = this
			, $footer;
		this.options = options;
		this.id = options['id'];
		this.ajaxUrl = options['ajaxUrl'];
	};
	Calendarista.ajax.prototype.navButtonState = function(arg){
		(function(context, options, enable, id){
			var $footer
				, appointment
				, $nextButton
				, $prevButton
				, flipped = false;
			if(typeof(options['appointment']) !== 'undefined'){
				appointment = options['appointment'];
				$footer = $('#' + id);
				if([0,1].indexOf(appointment) !== -1){
					$footer = $('.ui-dialog-buttonset.' + id);
				}
				$nextButton = $footer.find('button[name="next"]');
				$prevButton = $footer.find('button[name="prev"]');
				if($nextButton.length > 0){
					if(context.flag1 || (!enable && $nextButton[0].hasAttribute('disabled'))){
						//button is already disabled, bail out.
						context.flag1 = !context.flag1;
						return;
					}
					if(enable){
						$nextButton.prop('disabled', false).removeClass('ui-state-disabled');
					}else{
						$nextButton.prop('disabled', true).addClass('ui-state-disabled');
					}
				}
			}
		})(this, this.options, arg, this.id);
	};
	Calendarista.ajax.prototype.request = function(callbackContext, callback, params){
		var context = this;
		(function(callbackContext, callback, params,  context){
			context.showSpinner();
			context.navButtonState(false);
			$.post(context.ajaxUrl
				, params
				, function(response){
					callback.call(callbackContext, response);
					context.hideSpinner();
					context.navButtonState(true);
				}
			);
		})(callbackContext, callback, params, this);
	};
	Calendarista.ajax.prototype.showSpinner = function(){
		var $spinner = $('#spinner_' + this.id);
		$spinner.removeClass('calendarista-invisible');
	};
	Calendarista.ajax.prototype.hideSpinner = function(){
		var $spinner = $('#spinner_' + this.id);
		$spinner.addClass('calendarista-invisible');
	};
	Calendarista.ajax.prototype.destroy = function(){
		var $elems, i;
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	}
	
})(window['jQuery'], window['Calendarista']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.wizard = function(options){
		this.init(options);
		this.destroyDelegate = Calendarista.createDelegate(this, this.destroy);
		$(window).on('unload', this.destroyDelegate);
	};
	Calendarista.wizard.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.wizardAction = options['wizardAction'];
		this.ajaxUrl = wp.url;
		this.requestUrl = options['requestUrl'];
		this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': this.id});
		this.prevIndex = options['prevIndex'];
		this.nextIndex = options['nextIndex'];
		this.stepCounter = options['stepCounter'];
		this.appointment = options['appointment'];
		this.selectedStepName = options['selectedStepName'];
		this.selectedStepIndex = options['selectedStepIndex'];
		this.invoiceId = options['invoiceId'];
		this.editMode = typeof(options['editMode']) !== 'undefined' ? options['editMode'] : 0;
		this.steps = options['steps'];
		this.externalDialog = options['externalDialog'];
		this.$root = $('#' + this.id);
		this.$footer = this.$root;
		if(this.externalDialog){
			this.$footer = $('.ui-dialog-buttonset.' + this.id);
		}
		this.initAjaxElements();
	};
	Calendarista.wizard.prototype.initAjaxElements = function(){
		var i
			, $tabItem
			, $option
			, index
			, context = this;
		this.$form = this.$root.find('form');
		this.$prevButton = this.$footer.find('button[name="prev"]');
		this.$nextButton = this.$footer.find('button[name="next"]');
		this.$bookNowButton = this.$footer.find('button[name="booknow"]');
		this.$disposeButton = this.$footer.find('button[name="dispose"]');
		this.$tabItemLinks = this.$root.find('.nav-item a');
		this.$navTabs = this.$root.find('.nav.nav-tabs');
		this.$dropdownList = this.$root.find('#dropdown_' + this.id);
		this.$dropdownListOptions = this.$dropdownList.find('option');
		this.prevButtonClickDelegate = Calendarista.createDelegate(this, this.prevButtonClick);
		this.$prevButton.on('click', this.prevButtonClickDelegate);
		this.nextButtonClickDelegate = Calendarista.createDelegate(this, this.nextButtonClick);
		this.$nextButton.on('click', this.nextButtonClickDelegate);
		this.disposeButtonClickDelegate = Calendarista.createDelegate(this, this.closeDialogClick);
		this.$disposeButton.on('click', this.disposeButtonClickDelegate);
		this.windowResizeDelegate = Calendarista.createDelegate(this, this.windowResize);
		$(window).on('resize', this.windowResizeDelegate);
		this.$tabItemLinks.on('click', function(e){
			var $target = $(e.currentTarget);
			e.preventDefault();
			if(!$target.prop('disabled')){
				context.request(parseInt($target.attr('data-calendarista-index'), 10));
			}
			return false;
		});
		this.$dropdownList.on('change', function(e){
			var $target = $(e.currentTarget).find(':selected');
			context.request(parseInt($target.attr('data-calendarista-index'), 10));
		});
		this.createAppointment();
		for(i = 0; i < this.$tabItemLinks.length; i++){
			$tabItem = $(this.$tabItemLinks[i]);
			index = parseInt($tabItem.attr('data-calendarista-index'), 10);
			if(index === this.selectedStepIndex || (this.appointment !== 1 && index > this.selectedStepIndex)){
				$tabItem.prop('disabled', true);
				$tabItem.addClass('calendarista-disabled disabled');
			}
		}
		for(i = 0; i < this.$dropdownListOptions.length; i++){
			$option = $(this.$dropdownListOptions[i]);
			index = parseInt($option.attr('data-calendarista-index'), 10);
			if(index === this.selectedStepIndex || (!this.editMode && index > this.selectedStepIndex)){
				$option.prop('disabled', true);
				$option.addClass('calendarista-disabled disabled');
			}
		}
		this.mobileCheck();
	};
	Calendarista.wizard.prototype.windowResize = function(){
		this.mobileCheck();
	};
	Calendarista.wizard.prototype.mobileCheck = function() {
		if (this.appointment !== 1 && window.innerWidth >= 768 ) {
			this.$navTabs.removeClass('hide');
			this.$dropdownList.addClass('hide');
		} else {
			this.$dropdownList.removeClass('hide');
			this.$navTabs.addClass('hide');
		}
	};
	Calendarista.wizard.prototype.createAppointment = function(){
		if(this.$prevButton.length > 0){
			this.$prevButton.prop('disabled', true).addClass('ui-state-disabled');
			this.$prevButton.val(this.prevIndex);
			if(this.prevIndex > 0){
				this.$prevButton.prop('disabled', false).removeClass('ui-state-disabled');
			}
		}
		if(this.$nextButton.length > 0){
			this.$nextButton.prop('disabled', true).addClass('ui-state-disabled');
			this.$nextButton.val(this.nextIndex);
		}
		if(this.$bookNowButton.length > 0){
			this.$bookNowButton.prop('disabled', true).addClass('ui-state-disabled');
		}
		if(this.nextIndex <= this.stepCounter){
			if (this.$nextButton.length > 0 && !this.$nextButton[0].hasAttribute('data-calendarista-closed')){
				this.$nextButton.prop('disabled', false).removeClass('ui-state-disabled');
			}
		}else if(this.$bookNowButton.length > 0 && this.nextIndex === (this.stepCounter+1)){
			this.$bookNowButton.prop('disabled', false).removeClass('ui-state-disabled');
		}
	};
	Calendarista.wizard.prototype.navButtonClick = function(e){
		e.preventDefault();
		this.request(parseInt($(e.target).val(), 10));
	};
	Calendarista.wizard.prototype.prevButtonClick = function(e){
		e.preventDefault();
		this.request(parseInt(this.$prevButton.val(), 10));
	};
	Calendarista.wizard.isValid = function($root, sel){
		var isValid = true
			, $validators;
		if(!sel){
			sel = '.calendarista_parsley_validated, .woald_parsley_validated';
		}
		$validators	= $root.find(sel);
		$validators.each(function(){
			var $elem = $(this),
				result;
			if ($elem.prop('type') !== 'hidden' && (!$elem.is(':visible') || $elem.is(':disabled') || $elem.attr('data-parsley-excluded'))){
				return true;
			}
			$elem.parsley().reset();
			result = $elem.parsley ? $elem.parsley().validate() : true;
			if(result !== null && (typeof(result) === 'object' && result.length > 0)){
				isValid = false;
			}
		});
		return isValid;
	};
	Calendarista.wizard.prototype.nextButtonClick = function(e){
		e.preventDefault();
		//If multiple dates are selected (multi_date mode) then we skip validation on calendar field.
		var  $multiDateSelection = this.$root.find('input[name="multiDateSelection"]')
			, $bookingDaysMinimum = this.$root.find('input[name="bookingDaysMinimum"]')
			, bookingDaysMinimum = $bookingDaysMinimum.length > 0 ? parseInt($bookingDaysMinimum.val(), 10) : 0
			, multiDatesVal = $multiDateSelection.val()
			, multiDates = multiDatesVal ? multiDatesVal.split(';') : []
			, sel = (multiDates.length > 0 && (!bookingDaysMinimum || multiDates.length >= bookingDaysMinimum)) ? '.calendarista-dynamicfield.calendarista_parsley_validated' : null;
		if(Calendarista.wizard.isValid(this.$root, sel)){
			this.request(parseInt(this.$nextButton.val(), 10));
		}else{
			this.scrollTop();
		}
	};
	Calendarista.wizard.prototype.closeDialogClick = function(e){
		e.preventDefault();
		this.destroy();
	};
	Calendarista.wizard.prototype.request = function(selectedStep){
		var model = this.$form.serializeArray();
		model.push({ 'name': 'selectedStep', 'value': selectedStep });
		model.push({ 'name': 'editMode', 'value': this.editMode });
		model.push({ 'name': 'action', 'value': this.wizardAction });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		this.ajax.request(this, this.response, $.param(model));
	};
	Calendarista.wizard.prototype.response = function(result){
		this.destroy();
		this.$root = this.$root.replaceWith(result);
		this.scrollTop();
	};
	Calendarista.wizard.prototype.scrollTop = function(){
		var $elem1 = $('#navbar_' + this.id)
			, $elem2 = $('#dropdown_' + this.id)
			, $elem = !$elem1.hasClass('hide')  ? $elem1 : $elem2;
		if($elem.length > 0){
			$elem[0].scrollIntoView({ 'block': 'center', 'behaviour': 'smooth' });
		}
	};
	Calendarista.wizard.prototype.destroy = function(){
		var $elems, i;
		if(this.$prevButton){
			this.$prevButton.off();
		}
		if(this.$nextButton){
			this.$nextButton.off();
		}
		if(this.$tabItemLinks){
			this.$tabItemLinks.off();
		}
		if(this.$dropdownList){
			this.$dropdownList.off();
		}
		if(this.$disposeButton){
			this.$disposeButton.off('click', this.disposeButtonClickDelegate);
		}
		delete this.$bookNowButton;
		delete this.bookNowButtonClickDelegate;
		delete this.prevButtonClickDelegate;
		delete this.$prevButton;
		delete this.nextButtonClickDelegate;
		delete this.$nextButton;
		delete this.$tabItemLinks;
		delete this.$footer;
		delete this.$disposeButton;
		delete this.disposeButtonClickDelegate;
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
	
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
(function($, Calendarista, jstz, wp){
	"use strict";
	Calendarista.calendar = function(options){
		var datepicker;
		if((!$.fn.bootstrapDP && $.fn.datepicker) && $.fn.datepicker.noConflict){
			datepicker = $.fn.datepicker.noConflict();
			$.fn.bootstrapDP = datepicker;
		}
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.calendar.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.$root = $('#' + this.id);
		this.actionWizard = 'calendarista_wizard';
		this.actionMonthChange = 'calendarista_calendar_month_change';
		this.actionStartDaySelected ='calendarista_calendar_start_day_selected';
		this.actionEndDaySelected = 'calendarista_calendar_end_day_selected';
		this.actionSeats = 'calendarista_seats';
		this.actionDynamicFields = 'calendarista_dynamic_fields';
		this.actionCostSummary = 'calendarista_cost_summary';
		this.actionBookMore = 'calendarista_bookmore';
		this.actionRepeat = 'calendarista_repeat';
		this.timeslotCalendarModes = [
			1/*SINGLE_DAY_AND_TIME*/
			, 2/*SINGLE_DAY_AND_TIME_RANGE*/
			, 4/*MULTI_DATE_AND_TIME_RANGE*/
			, 8/*ROUND_TRIP_WITH_TIME*/
			, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/
			, 12/*MULTI_DATE_AND_TIME*/
		];
		this.calendarMode = options['calendarMode'];
		this.ajaxUrl = wp.url;
		this.projectId = options['projectId'];
		this.dateFormat = options['dateFormat'] ? options['dateFormat'] : 'DD, d MM, yy';
		this.thumbnails = options['thumbnails'];
		this.appointment = options['appointment'];
		this.bookingDaysMinimum = options['bookingDaysMinimum'];
		this.bookingDaysMaximum = options['bookingDaysMaximum'];
		this.dayCountMode = options['dayCountMode'];
		this.minTime = options['minTime'];
		this.maxTime = options['maxTime'];
		this.returnSameDay = options['returnSameDay'];
		this.returnOptional = options['returnOptional'];
		this.firstDayOfWeek = options['firstDayOfWeek'];
		this.searchResultStartTime = options['searchResultStartTime'];
		this.searchResultEndTime = options['searchResultEndTime'];
		this.timeDisplayMode = options['timeDisplayMode'];
		this.enableMultipleBooking = options['enableMultipleBooking'];
		this.clearLabel = options['clearLabel'];
		this.seasons = options['seasons'];
		this.repeatPageSize = options['repeatPageSize'];
		
		this.startCurrentMonthExclusions = [];
		this.startBookedOutDays = [];
		this.startHalfDays = {'start': [], 'end': []};
		this.startCheckinWeekdayList = [];
		this.startCheckoutWeekdayList = [];
		this.startBookedAvailabilityList = [];
		this.holidays = [];
		this._startBookedAvailabilityList = [];
		
		this.serverDateFormat = 'yy-mm-dd';
		this.endCurrentMonthExclusions = [];
		this.endBookedOutDays = [];
		this.endHalfDays = {'start': [], 'end': []};
		this.endCheckinWeekdayList = [];
		this.endCheckoutWeekdayList = [];
		this.endBookedAvailabilityList = [];
		this._endBookedAvailabilityList = [];
		
		this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': this.id, 'appointment': this.appointment});
		this.$form = this.$root.find('form');
		this.$viewState = this.$root.find('input[name="__viewstate"]');
		this.$cart = this.$root.find('input[name="calendarista_cart"]');
		this.$startDate = this.$root.find('.calendarista-start-date');
		this.$endDate = this.$root.find('.calendarista-end-date');
		this.$startDateField = this.$root.find('input[name="availableDate"]');
		this.$endDateField = this.$root.find('input[name="endDate"]');
		this.$clientStartDateField = this.$root.find('input[name="clientAvailableDate"]');
		this.$clientEndDateField = this.$root.find('input[name="clientEndDate"]');
		this.$timezone = this.$root.find('input[name="timezone"]');
		this.$dateRangeError = this.$root.find('.calendarista-date-range-error');
		this.$timeslotsError = this.$root.find('.calendarista-timeslots-error');
		this.$timeslotsReturnError = this.$root.find('.calendarista-timeslots-return-error');
		this.$minTimeError = this.$root.find('input[name="minTime"]');
		this.$maxTimeError = this.$root.find('input[name="maxTime"]');
		this.$startTimeslotPlaceHolder = this.$root.find('.calendarista-start-timeslot-placeholder');
		this.$endTimeslotPlaceHolder = this.$root.find('.calendarista-end-timeslot-placeholder');
		this.$seatsPlaceholder = this.$root.find('.calendarista-seats-placeholder');
		this.$bookMorePlaceholder = this.$root.find('.calendarista-bookmore-placeholder');
		this.$repeatPlaceholder = this.$root.find('.calendarista-repeat-appointment-placeholder');
		this.$seatsMaximum = this.$root.find('input[name="seatsMaximum"]');
		this.$dynamicFieldsPlaceholder = this.$root.find('.calendarista-dynamicfield-placeholder');
		this.$dynamicFieldsCount = this.$root.find('input[name="dynamicFieldsCount"]');
		this.$calendarLegend = this.$root.find('.calendarista-calendar-legend' + this.projectId);
		this.$costSummaryPlaceholder = this.$root.find('.calendarista-cost-summary-placeholder');
		this.$orderId = this.$root.find('input[name="orderId"]');
		this.$projectList = this.$root.find('input[name="projectList"]');
		this.$projectsListbox = this.$root.find('select[name="projects"]');
		this.$projectsRadioList = this.$root.find('input[name="projects"][type="radio"]');
		this.$projectId = this.$root.find('input[name="projectId"]');
		this.$projectsListbox.on('change', Calendarista.createDelegate(this, this.projectsChanged));
		this.$projectsRadioList.on('change', Calendarista.createDelegate(this, this.projectsChanged));
		this.$availabilityField = this.$root.find('input[name="availabilityId"]');
		this.$oldAvailabilityField = this.$root.find('input[name="oldAvailabilityId"]');
		this.availabilityId = parseInt(this.$availabilityField.val(), 10);
		this.$availabilityListbox = this.$root.find('select[name="availability"]');
		this.$availabilityRadioList = this.$root.find('input[name="availability"][type="radio"]');
		this.$availabilityListbox.on('change', Calendarista.createDelegate(this, this.availabilityChanged));
		this.$availabilityRadioList.on('change', Calendarista.createDelegate(this, this.availabilityChanged));
		this.$packageListbox = this.$root.find('select[name="package"]');
		this.$packageListbox.on('change', Calendarista.createDelegate(this, this.packageChanged));
		this.$packageDurationPlaceholder = this.$root.find('#calendarista_package_duration_' + this.projectId);
		this.$wizardSectionBlock = this.$root.find('.calendarista-wizard-section-block');
		this.$wizardSectionThumbnail = this.$root.find('.calendarista-wizard-section-block-thumb');
		this.$availabilityPreviewUrlField = this.$root.find('input[name="availabilityPreviewUrl"]');
		this.$serviceThumbnailViewField = this.$root.find('input[name="serviceThumbnailView"]');
		this.$availabilityThumbnailViewField = this.$root.find('input[name="availabilityThumbnailView"]');
		this.$minDate = this.$root.find('input[name="minDate"]');
		this.$seats = this.$root.find('input[name="seats"]');
		this.$multiDateSelection = this.$root.find('input[name="multiDateSelection"]');
		this.$liveAriaRegion = this.$root.find('#calendarista_liveregion');
		this.ariaLogDelegate = Calendarista.createDelegate(this, this.ariaLog);
		if([6/*PACKAGE*/].indexOf(this.calendarMode) !== -1){
			if(this.$packageListbox.length === 0){
				//this is a single package, show summary immediately
				this.$startDateField.val(this.$root.find('input[name="__availableDate"]').val());
				this.$endDateField.val(this.$root.find('input[name="__endDate"]').val());
				this.seatsRequest();
			}else if (this.$packageListbox[0].selectedIndex > 0){
				this.packageSelected();
			}
		}else{
			this.createCalendars();
		}
		if(this.$multiDateSelection.val()){
			this.$startDateField.val('');
			this.$endDateField.val('');
			this.costSummaryRequest(true/*queryDynamicFields*/);
		}
		window['calendarista_cost_summary_request' + this.projectId] = Calendarista.createDelegate(this, this.mapCostSummaryRequest);
		this.callbackTimeslotSelected = 'calendarista_timeslot_selected' + this.availabilityId;
		this.applyAvailabilityThumbnail(this.availabilityId);
		this.timezone = jstz.determine().name();
		this.$timezone.val(this.timezone);
		if(this.$projectsListbox.length > 0){
			this.$projectsListbox.removeAttr('disabled');
		}
		if(this.$projectsRadioList.length > 0){
			this.$projectsRadioList.removeAttr('disabled');
		}
		if(this.$availabilityListbox.length > 0){
			this.$availabilityListbox.removeAttr('disabled');
		}
		if(this.$availabilityRadioList.length > 0){
			this.$availabilityRadioList.removeAttr('disabled');
		}
	};
	Calendarista.calendar.prototype.requestCurrentMonthCalendar = function(){
		var today = new Date()
			, month = today.getMonth() + 1
			, year = today.getFullYear();
		this.calendarRequestByMonth(year, month, 0/*startDate*/);
		if(this.$endDate.length > 0){
			this.requestEndDate = month;
		}
		this.datepickerReset();
	};
	Calendarista.calendar.prototype.packageChanged = function(){
		var model = []
			, availabilityId = parseInt(this.$packageListbox.val(), 10);
		model.push({ 'name': 'projectId', 'value': this.projectId });
		model.push({ 'name': 'availabilityId', 'value': availabilityId });
		model.push({ 'name': 'appointment', 'value': this.appointment });
		model.push({ 'name': 'projectList', 'value': this.$projectList.val() });
		model.push({ 'name': 'enableMultipleBooking', 'value': this.enableMultipleBooking ? 1 : 0 });
		model.push({ 'name': 'calendarista_cart', 'value': this.$cart.val() });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		if(this.appointment && this.$orderId.length > 0){
			model.push({ 'name': 'orderId', 'value': this.$orderId.val() });
		}
		model.push({ 'name': 'action', 'value': this.actionWizard });
		this.ajax.request(this, this.wizardResponse, $.param(model));
	};
	Calendarista.calendar.prototype.packageSelected = function(){
		var $option = this.$packageListbox.find(':selected')
			, startDate = $option.attr('data-calendarista-startdate')
			, endDate = $option.attr('data-calendarista-enddate')
			, model
			, context = this;
		this.availabilityId = parseInt(this.$packageListbox.val(), 10);
		this.applyAvailabilityThumbnail(this.availabilityId);
		if(!this.availabilityId){
			this.datepickerReset();
			return;
		}
		this.$availabilityField.val(this.availabilityId);
		this.$startDateField.val(startDate);
		this.$endDateField.val(endDate);
		window.setTimeout(function(){
			context.seatsRequest();
		}, 1);
	};
	Calendarista.calendar.prototype.applyAvailabilityThumbnail = function(availabilityId){
		var i
			, thumbnail
			, result;
		if(!this.thumbnails.length){
			return;
		}
		if(availabilityId){
			for(i = 0; i < this.thumbnails.length; i++){
				thumbnail = this.thumbnails[i];
				if(thumbnail['id'] === availabilityId){
					result = thumbnail['url'];
					break;
				}
			}
		}
		//if an availability has no thumbnail, then we remove the thumbnail.
		this.$wizardSectionBlock.addClass('calendarista-wizard-section-no-thumbnail');
		this.$wizardSectionBlock.removeClass('calendarista-wizard-section-thumbnail');
		this.$wizardSectionThumbnail.css('display', 'none');
		this.$availabilityPreviewUrlField.val(result);
		if(result){
			this.$wizardSectionBlock.addClass('calendarista-wizard-section-thumbnail');
			this.$wizardSectionBlock.removeClass('calendarista-wizard-section-no-thumbnail');
			this.$wizardSectionThumbnail.css({'display': 'block', 'background-image': 'url("' + result + '")'});
		}
	};
	Calendarista.calendar.prototype.projectsChanged = function(el){
		var selectedVal = $(el.currentTarget).val()
			, projectId = parseInt(selectedVal, 10)
			, $bookedAvailabilityId = this.$root.find('input[name="bookedAvailabilityId"]')
			, bookedAvailabilityId = $bookedAvailabilityId.length > 0 ? parseInt($bookedAvailabilityId.val(), 10) : null
			, model = [
			{ 'name': 'projectId', 'value': projectId }
			, { 'name': 'projectList', 'value': this.$projectList.val() }
			, { 'name': 'bookedAvailabilityId', 'value': bookedAvailabilityId }
			, { 'name': 'appointment', 'value': this.appointment}
			, { 'name': 'enableMultipleBooking', 'value': this.enableMultipleBooking ? 1 : 0 }
			, { 'name': 'serviceThumbnailView', 'value': this.$serviceThumbnailViewField.val() }
			, { 'name': 'availabilityThumbnailView', 'value': this.$availabilityThumbnailViewField.val() }
			, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
			, { 'name': 'action', 'value': this.actionWizard }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		if(this.appointment && this.$orderId.length > 0){
			model.push({ 'name': 'orderId', 'value': this.$orderId.val() });
		}
		this.ajax.request(this, this.wizardResponse, $.param(model));
	};
	Calendarista.calendar.prototype.wizardResponse = function(result){
		this.unload();
		this.$root.replaceWith(result);
	};
	Calendarista.calendar.prototype.availabilityChanged = function(el){
		var model = []
			, $bookedAvailabilityId = this.$root.find('input[name="bookedAvailabilityId"]')
			, bookedAvailabilityId = $bookedAvailabilityId.length > 0 ? parseInt($bookedAvailabilityId.val(), 10) : null
			, selectedVal = $(el.currentTarget).val()
			, availabilityId = parseInt(selectedVal, 10);
		model.push({ 'name': 'projectId', 'value': this.projectId });
		model.push({ 'name': 'availabilityId', 'value': availabilityId });
		model.push({ 'name': 'bookedAvailabilityId', 'value': bookedAvailabilityId });
		model.push({ 'name': 'appointment', 'value': this.appointment });
		model.push({ 'name': 'projectList', 'value': this.$projectList.val() });
		model.push({ 'name': 'serviceThumbnailView', 'value': this.$serviceThumbnailViewField.val() });
		model.push({ 'name': 'availabilityThumbnailView', 'value': this.$availabilityThumbnailViewField.val() });
		model.push({ 'name': 'enableMultipleBooking', 'value': this.enableMultipleBooking ? 1 : 0 });
		model.push({ 'name': 'calendarista_cart', 'value': this.$cart.val() });
		model.push({ 'name': 'action', 'value': this.actionWizard });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		if(this.appointment && this.$orderId.length > 0){
			model.push({ 'name': 'orderId', 'value': this.$orderId.val() });
		}
		this.ajax.request(this, this.wizardResponse, $.param(model));
	};
	Calendarista.calendar.prototype.createCalendars = function(){
		var today = this.parseDate(this.$minDate.val())
			, editAppointment = this.appointment === 1
			, minDate = editAppointment ? null : today
			, args
			, context = this;
		this.startDateBeforeShowDayDelegate = Calendarista.createDelegate(this, this.startDateBeforeShowDay);
		this.endDateBeforeShowDayDelegate = Calendarista.createDelegate(this, this.endDateBeforeShowDay);
		this.onChangeMonthYearStartDateDelegate = Calendarista.createDelegate(this, this.onChangeMonthYearStartDate);
		this.onChangeMonthYearEndDateDelegate = Calendarista.createDelegate(this, this.onChangeMonthYearEndDate);
		this.onStartDateSelectDelegate = Calendarista.createDelegate(this, this.onStartDateSelect);
		this.onEndDateSelectDelegate = Calendarista.createDelegate(this, this.onEndDateSelect);
		this.onStartDateCloseDelegate = Calendarista.createDelegate(this, this.onStartDateClose);
		this.onEndDateCloseDelegate = Calendarista.createDelegate(this, this.onEndDateClose);
		this.$startDate.datepicker({
			'dateFormat': this.dateFormat
			, 'defaultDate': today
			, 'minDate': minDate
			, 'beforeShowDay': this.startDateBeforeShowDayDelegate
			, 'onChangeMonthYear': this.onChangeMonthYearStartDateDelegate
			, 'onSelect': this.onStartDateSelectDelegate
			, 'onClose': this.onStartDateCloseDelegate
			, 'showButtonPanel': true
            , 'closeText': this.clearLabel
			, 'firstDay': this.firstDayOfWeek
			//, 'showOtherMonths': true
		}).on('keydown', this.ariaLogDelegate);
		this.$endDate.datepicker({
			'dateFormat': this.dateFormat
			, 'defaultDate': today
			, 'minDate': minDate
			, 'beforeShowDay': this.endDateBeforeShowDayDelegate
			, 'onChangeMonthYear': this.onChangeMonthYearEndDateDelegate
			, 'onSelect': this.onEndDateSelectDelegate
			, 'onClose': this.onEndDateCloseDelegate
			, 'showButtonPanel': true
            , 'closeText': this.clearLabel
			, 'firstDay': this.firstDayOfWeek
			//, 'showOtherMonths': true
		}).on('keydown', this.ariaLogDelegate);
		this.$datepickerElement = $('#ui-datepicker-div');
		if(this.availabilityId){
			if(this.$startDate.length > 0){
				this.$startDate.prop('disabled', true);
				this._startDateVal = this.$clientStartDateField.val();
				this.$startDate.val(this.$startDate.attr('data-calendarista-loading'));
			}
			if(this.$clientStartDateField.val()){
				today = this.parseDate(this.$clientStartDateField.val());
			}
			if(this.$endDate.length > 0){
				this.$endDate.prop('disabled', true);
				this._endDateVal = this.$clientEndDateField.val();
				this.$endDate.val(this.$endDate.attr('data-calendarista-loading'));
				this.requestEndDate = today;
			}
			this.calendarRequestByMonth(today.getFullYear(), today.getMonth() + 1, 0/*startDate*/);
		}
	};
	Calendarista.calendar.prototype.ariaLog = function(e){
		var result;
		if (e.keyCode !== 13) {
			result  = ' ' + $('.ui-state-hover').html() + 
				' ' + $('.ui-datepicker-month').html() + 
				' ' + $('.ui-datepicker-year').html();
			this.$liveAriaRegion.html(result);
		}
	};
	Calendarista.calendar.prototype.getCalendarModelByMonth = function(year, month){
		var ymd = year + '-' + this.pad(month) + '-01'
			, model = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'availabilityId', 'value': this.availabilityId}
				, { 'name': 'appointment', 'value': this.appointment}
				, { 'name': 'changeMonthYear', 'value': ymd }
				, { 'name': 'clientTime', 'value': this.toTime(new Date()) }
				, { 'name': 'timezone', 'value': this.timezone }
				, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
				, { 'name': 'action', 'value': this.actionMonthChange }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		return model;
	};
	Calendarista.calendar.prototype.calendarRequestByMonth = function(year, month, requestBy){
		var model = this.getCalendarModelByMonth(year, month, requestBy);
		model.push({ 'name': 'requestBy', 'value': requestBy });
		if(requestBy === 1/*endDate*/){
			this.$startDate.prop('disabled', true);
		}
		this.ajax.request(this, this.calendarResponseByMonth, $.param(model));
	};
	Calendarista.calendar.prototype.calendarResponseByMonth = function(result){
		var data = window['JSON'].parse(result);
		this.monthYearResponse(result);
		if(this.$startDate.length > 0 && data.requestBy === 0/*start date*/){
			this.$startDate.val(this._startDateVal);
			if(this.$clientStartDateField.val()){
				//we are in edit mode (back-end appointments page)
				this.startDateSelected = this.$clientStartDateField.val();
				this.setStartDate(this.startDateSelected);
				this.onStartDateSelect(this.startDateSelected);
				//setDate calls calendars beforeShowDay again, so re-enable endDate:
				this.$endDate.prop('disabled', false);
				if([0/*SINGLE_DAY*/].indexOf(this.calendarMode) !== -1){
					this.repeatRequest();
				}
			}
			this.$startDate.prop('disabled', false);
		}
		if(this.$endDate.length > 0 && data.requestBy === 1/*end date*/){
			this.$endDate.prop('disabled', false);
			this.$endDate.val(this._endDateVal);
			if(this.$clientEndDateField.val()){
				//we are in edit mode (back-end appointments page)
				this.endDateSelected = this.$clientEndDateField.val();
				this.setEndDate(this.endDateSelected);
				this.onEndDateSelect(this.endDateSelected);
				//setDate calls calendars beforeShowDay again, so re-enable startDate:
			}else if(!this._startDateVal && !this._endDateVal){
				this.$endDate.prop('disabled', true);
			}
			this.$startDate.prop('disabled', false);
		}
		if(this.requestEndDate){
			this.calendarRequestByMonth(this.requestEndDate.getFullYear(), this.requestEndDate.getMonth() + 1, 1/*endDate*/);
			this.requestEndDate = null;
		}
	};
	Calendarista.calendar.prototype.createUniqueCalendar = function(){
		var className1 = 'calendarista-calendar-' + this.projectId
			, className2 = ' calendarista-flat calendarista-borderless'
			, attrName = 'data-calendarista-classname';
		if(!this.$datepickerElement){
			return;
		}
		this.$calendarLegend.removeClass('hide');
		this.$calendarLegend.appendTo(this.$datepickerElement);
		if(this.$datepickerElement.hasClass(className1)){
			return;
		}
		this.$datepickerElement.removeClass(this.$datepickerElement.prop(attrName) + className2);
		this.$datepickerElement.removeProp(attrName);
		this.$datepickerElement.prop(attrName, className1);
		this.$datepickerElement.addClass(className1 + className2);
		this.$datepickerElement.attr('translate', 'no');
		this.$datepickerElement.addClass('notranslate');
		this.$datepickerElement.addClass('calendarista-datepicker');
	};
	Calendarista.calendar.prototype.resetUniqueCalendar = function(){
		//method is deprecated.
		/*(function(projectId,  context){
			window.setTimeout(function(){
				var className = 'calendarista-calendar-' + projectId + ' calendarista-flat calendarista-borderless'
					, attrName = 'data-calendarista-classname';
				context.$datepickerElement.removeClass(className);
				context.$datepickerElement.removeProp(attrName);
				context.$calendarLegend.addClass('hide');
				context.$calendarLegend.appendTo(context.$root);
			}, 1000);
		})(this.projectId, this);*/
	};
	Calendarista.calendar.prototype.onStartDateClose = function(selectedDate, args){
		var context = this
			, result
			, event = window.event || arguments.callee.caller.caller.caller.arguments[0]
			, target = event.currentTarget || event.delegateTarget
			, lastSelectedDate
			, currentDate = new Date()
			, minDate = this.$minDate.val();
		if(selectedDate){
			currentDate = this.parseDate(selectedDate);
		}else if(minDate){
			currentDate = this.parseDate(minDate);
		}
		if(selectedDate){
			this.$startDate.parsley().reset();
		}
		this.$startDate.prop('disabled', false);
		this.resetUniqueCalendar();
		this.$dateRangeError.addClass('hide');
		if(this.$endDate.length > 0){
			if(selectedDate){
				this.$endDate.prop('disabled', false);
			}
		}
		if (target && $(target).hasClass('ui-datepicker-close')) {
			this.datepickerReset();
			return;
		}
		if(this.startDateSelected !== selectedDate){
			this.startDateSelected = selectedDate;
			if(this.endDateSelected){
				result = this.validateDateRange(selectedDate, this.endDateSelected, 0/*startDate checkin calendar*/);
				if(!result){
					this.datepickerReset();
					this.$dateRangeError.removeClass('hide');
					return;
				}
				this.setEndDate(null);
				this.endDateSelected = null;
				this.$endDateField.val('');
				this.$seatsPlaceholder.html('');
				this.$bookMorePlaceholder.html('');
				this.$repeatPlaceholder.html('');
				this.$dynamicFieldsPlaceholder.html('');
				this.costSummaryReset();
				if(this.$endTimeslotPlaceHolder){
					this.$endTimeListbox = null;
					this.$endTimeslotPlaceHolder.empty();
				}
				this.adjustStartTime();
				this.$startDate.datepicker('option', 'maxDate', null);
			}else{
				this.timeReset();
			}
			if((this.$endDate && this.$endDate.length > 0) && selectedDate){
				this.$endDate.datepicker('option', 'minDate',  this.parseDate(selectedDate));
			}
			if([0/*SINGLE_DAY*/].indexOf(this.calendarMode) !== -1 && selectedDate){
				this.repeatRequest();
			}
		}
		this.calendarRequestByMonth(currentDate.getFullYear(), currentDate.getMonth() + 1, 0/*startDate*/);
	};
	Calendarista.calendar.prototype.onEndDateClose = function(selectedDate, args){
		var context = this
			, result
			, event = window.event || arguments.callee.caller.caller.caller.arguments[0]
			, target = event.currentTarget || event.delegateTarget
			, lastSelectedDate
			, currentDate = new Date()
			, minDate = this.$minDate.val();
		if(selectedDate){
			currentDate = this.parseDate(selectedDate);
		}else if(this.startDateSelected){
			currentDate = this.parseDate(this.startDateSelected)
		}else if(minDate){
			currentDate = this.parseDate(minDate);
		}
		if(selectedDate){
			this.$endDate.parsley().reset();
		}
		this.$endDate.prop('disabled', false);
		this.resetUniqueCalendar();
		this.$dateRangeError.addClass('hide');
		if (target && $(target).hasClass('ui-datepicker-close')) {
			this.datepickerReset();
			return;
		}
		if(this.endDateSelected !== selectedDate && selectedDate){
			if(this.startDateSelected){
				result = this.validateDateRange(this.startDateSelected, selectedDate, 1/*endDate checkout calendar*/);
				if(!result){
					this.datepickerReset();
					this.$dateRangeError.removeClass('hide');
					return;
				}
			}
			if(this.startDateSelected === this.endDateSelected && this.endDateSelected !== selectedDate){
				this.timeReset();
			}
			this.endDateSelected = selectedDate;
			if(selectedDate){
				this.$startDate.datepicker('option', 'maxDate', this.parseDate(selectedDate));
			}
		}
		this.calendarRequestByMonth(currentDate.getFullYear(), currentDate.getMonth() + 1, 1/*endDate*/);
	};
	Calendarista.calendar.prototype.datepickerReset = function(emptyPlaceHolder){
		var context = this
			, today = this.parseDate(this.$minDate.val())
			, clearSummary = typeof emptyPlaceHolder === 'undefined' ? true : emptyPlaceHolder;
		this.setStartDate(null);
		this.setEndDate(null);
		this.$startDate.datepicker('option', 'minDate', today);
		this.$startDate.datepicker('option', 'maxDate', null);
		this.$endDate.datepicker('option', 'minDate', new Date());
		if(this.$startTimeslotPlaceHolder){
			this.$startTimeslotPlaceHolder.empty();
		}
		if(this.$endTimeslotPlaceHolder){
			this.$endTimeslotPlaceHolder.empty();
		}
		this.$startDateField.val('');
		this.$endDateField.val('');
		this.startDateSelected = null;
		this.endDateSelected = null;
		this.$seatsPlaceholder.html('');
		this.$bookMorePlaceholder.html('');
		this.$repeatPlaceholder.html('');
		this.$dynamicFieldsPlaceholder.html('');
		if(clearSummary){
			this.$costSummaryPlaceholder.html('');
		}
		if(this.$startDate.length){
			this.$startDate.blur();
		}
		if(this.$endDate.length){
			this.$endDate.blur();
		}
		this._startBookedAvailabilityList.length = 0;
		this._endBookedAvailabilityList.length = 0;
		this._startDateVal = null;
		this._endDateVal = null;
		this.$clientStartDateField.val('');
		this.$clientEndDateField.val('');
		this.calendarRequestByMonth(today.getFullYear(), today.getMonth() + 1, 0/*startDate*/);
		if(this.$endDate.length > 0){
			this.requestEndDate = today;
		}
	};
	Calendarista.calendar.prototype.validateDateRange = function(x, y, requestBy){
		if(!x || !y){
			return false;
		}
		var startDate = x instanceof Date ? x : this.parseDate(x)
			, endDate = y instanceof Date ? y : this.parseDate(y)
			, ymdStart = this.toYMD(startDate)
			, ymdEnd = this.toYMD(endDate)
			, _halfDays = requestBy === 0 ? this.startHalfDays : this.endHalfDays
			, bookedAvailabilityList = requestBy === 0 ? this.startBookedAvailabilityList.concat([]) : this.endBookedAvailabilityList.concat([])
			, _bookedAvailabilityList = requestBy === 0 ? this._startBookedAvailabilityList : this._endBookedAvailabilityList
			, halfDayStart = _halfDays ? _halfDays['start'] : []
			, halfDayEnd = _halfDays ? _halfDays['end'] : []
			, halfDays = halfDayStart.concat(halfDayEnd)
			, bookedOutDays = requestBy === 0 ? this.startBookedOutDays : this.endBookedOutDays
			, len = 0
			, result = true
			, weekday
			, bookingDaysMinimum = this.bookingDaysMinimum
			, bookingDaysMaximum = this.bookingDaysMaximum
			, bookedDate
			, selectedDays
			, i
			, index
			, item
			, season
			, seasonStartDate
			, seasonEndDate
			, excludedDay;
		if(bookedOutDays.length === 0 && this.startBookedOutDays.length > 0){
			bookedOutDays = this.startBookedOutDays;
		}
		if(this.startCurrentMonthExclusions.length > 0){
			for(i = 0; i < this.startCurrentMonthExclusions.length; i++){
				excludedDay = this.startCurrentMonthExclusions[i];
				if(bookedOutDays.indexOf(excludedDay) === -1){
					bookedOutDays.push(excludedDay);
				}
			}
		}
		if(this.endCurrentMonthExclusions.length > 0){
			for(i = 0; i < this.endCurrentMonthExclusions.length; i++){
				excludedDay = this.endCurrentMonthExclusions[i];
				if(bookedOutDays.indexOf(excludedDay) === -1){
					bookedOutDays.push(excludedDay);
				}
			}
		}
		if(this.seasons && this.seasons.length > 0){
			for(i = 0; i < this.seasons.length; i++){
				season = this.seasons[i];
				seasonStartDate = this.parseDateByFormat(this.serverDateFormat, season['startDate']);
				seasonEndDate = this.parseDateByFormat(this.serverDateFormat, season['endDate']);
				if((startDate >= seasonStartDate && startDate <= seasonEndDate) || (endDate >= seasonStartDate && endDate <= seasonEndDate)){
					bookingDaysMinimum = parseInt(season['bookingDaysMinimum'], 10);
					bookingDaysMaximum = parseInt(season['bookingDaysMaximum'], 10);
					break;
				}
			}
		}
		if(_bookedAvailabilityList.length > 0){
			for(i = 0; i < _bookedAvailabilityList.length; i++){
				item = _bookedAvailabilityList[i];
				if(bookedAvailabilityList.indexOf(item) === -1){
					bookedAvailabilityList.push(item);
				}
			}
		}
		for(i = 0; i < this.holidays.length; i++){
			item = this.holidays[i];
			index = bookedOutDays.indexOf(item);
			if(index !== -1){
				bookedOutDays.splice(index, 1);
			}
			index = bookedAvailabilityList.indexOf(item);
			if(index !== -1){
				bookedAvailabilityList.splice(index, 1);
			}
		}
		if([4/*MULTI_DATE_AND_TIME_RANGE*/].indexOf(this.calendarMode) !== -1 && bookedAvailabilityList.length > 0){
			selectedDays = this.dayDiff(startDate, endDate);
			startDate.setHours(0,0,0,0);
			endDate.setHours(0,0,0,0);
			for(i = 0; i < bookedAvailabilityList.length; i++){
				bookedDate = new Date(bookedAvailabilityList[i]);
				bookedDate.setHours(0,0,0,0);
				if(selectedDays <= 1){
					//same day booking is fine.
					continue;
				}
				if(bookedDate > startDate && bookedDate < endDate){
					//date within range has one or more timeslots booked, hence invalid range.
					return false;
				}
			}
		}
		if([7/*ROUND_TRIP*/, 8/*ROUND_TRIP_WITH_TIME*/].indexOf(this.calendarMode) !== -1){
			return true;
		}
		if(!x){
			return false;
		}
		if([5/*changeover days*/].indexOf(this.calendarMode) !== -1 && ymdStart === ymdEnd){
			//checkin and checkout cannot be on the same day
			result = false;
		}
		for (startDate = this.parseDate(x); startDate <= endDate; startDate.setDate(startDate.getDate() + 1)) {
			weekday = startDate.getDay();
			ymdStart = this.toYMD(startDate);
			
			++len;
			if(bookedOutDays.indexOf(ymdStart) !== -1 || ((len > 1 && ymdStart != ymdEnd) && halfDays.indexOf(ymdStart) !== -1)){
				result = false;
				break;
			}
		}
		if(this.calendarMode === 5){
			if(bookingDaysMinimum){
				bookingDaysMinimum += 1;
			}
			if(bookingDaysMaximum){
				bookingDaysMaximum += 1;
			}
		}
		if(this.dayCountMode === 1/*difference*/){
			if(len === 1){
				return false;
			}
			++bookingDaysMinimum;
			if(bookingDaysMaximum > 0){
				++bookingDaysMaximum;
			}
		}
		if((bookingDaysMinimum && len < bookingDaysMinimum) ||
			(bookingDaysMaximum && len > bookingDaysMaximum)){
			return false;
		}
		return result;
	};
	Calendarista.calendar.prototype.beforeShowDay = function(theDate, selectedDate, requestBy){
		var weekday = theDate.getDay()
			, ymd = this.toYMD(theDate)
			, today = new Date()
			, result = true
			, className = ''
			, startDateSelected = this.startDateSelected ? this.parseDate(this.startDateSelected) : null
			, endDateSelected = this.endDateSelected ? this.parseDate(this.endDateSelected) : null
			, halfDayStart = requestBy === 0 ? this.startHalfDays['start'] : this.endHalfDays['start']
			, halfDayEnd = requestBy === 0 ? this.endHalfDays['end'] : this.endHalfDays['end']
			, currentMonthExclusions = requestBy === 0 ? this.startCurrentMonthExclusions : this.endCurrentMonthExclusions
			, checkoutWeekdayList = requestBy === 0 ? this.startCheckoutWeekdayList : this.endCheckoutWeekdayList
			, checkinWeekdayList = requestBy === 0 ? this.endCheckinWeekdayList : this.endCheckinWeekdayList
			, bookedAvailabilityList = requestBy === 0 ? this.startBookedAvailabilityList : this.endBookedAvailabilityList
			, bookedOutDays = requestBy === 0 ? this.startBookedOutDays : this.endBookedOutDays
			, i;
		if(weekday === 0){
			//in php we use ISO-8601 numerical format for weekdays 1-7
			weekday = 7;
		}
		if(bookedOutDays.length > 0){
			for(i = 0; i < bookedOutDays.length; i++){
				if(currentMonthExclusions.indexOf(bookedOutDays[i]) === -1){
					currentMonthExclusions.push(bookedOutDays[i]);
				}
			}
		}
		// CalendarMode:
		//--------------
		//3. MULTI_DATE_RANGE
		//4. MULTI_DATE_AND_TIME_RANGE 
		//5. MULTI_DATE_RANGE_WITH_PARTIAL_DAY_CHARGE
		today.setHours(0,0,0,0);
		this.createUniqueCalendar();
		if(requestBy === 1/*enddate*/ && [3/*MULTI_DATE_RANGE*/,4/*MULTI_DATE_AND_TIME_RANGE*/,5/*CHANGEOVER*/].indexOf(this.calendarMode) !== -1){
			if(!this.validateDateRange(this.startDateSelected, theDate, 1/*enddate checkin calendar*/)){
				return [false, 'calendarista-unavailable'];
			}
		}
		if(halfDayStart.indexOf(ymd) !== -1 && requestBy === 0){
			return [false, 'calendarista-unavailable calendarista-halfday-start'];
		}else if(halfDayEnd.indexOf(ymd) !== -1 && requestBy === 1){
			return [false, 'calendarista-unavailable calendarista-halfday-end'];
		}
		if (currentMonthExclusions.indexOf(ymd) !== -1){
			result = false;
			className = 'calendarista-unavailable';
		} else if(requestBy === 0/*startDate checkin*/ && checkoutWeekdayList.indexOf(weekday) !== -1 ||
				requestBy === 1/*endDate checkout*/ && checkinWeekdayList.indexOf(weekday) !== -1){
			result = false;
			className = 'calendarista-selectedday-range';
		}else{
			if(halfDayStart.indexOf(ymd) !== -1 ||
				([3, 4, 5, 7].indexOf(this.calendarMode) !== -1 && 
				(startDateSelected && theDate.getTime() == startDateSelected.getTime()))){
				if(this.calendarMode !== 7/*ROUND_TRIP*/){
					className = 'calendarista-selectedday-range';
					if(this.calendarMode === 5){
						className += ' calendarista-halfday-start';
					}
				}
				if([3, 7].indexOf(this.calendarMode) !== -1 && requestBy === 1){
					//exclude muti date range if mindays is 0 or 1, in this case we allow booking a single day.
					if(!(requestBy === 1/*endDate checkout*/ && ([0, 1].indexOf(this.bookingDaysMinimum) !== -1 && this.calendarMode === 3/*MULTI_DATE_RANGE*/))){
						//we disable start date
						return [false, className];
					}
				}
			}else if(halfDayEnd.indexOf(ymd) !== -1 ||
				(this.calendarMode === 5 && 
				(endDateSelected && theDate.getTime() == endDateSelected.getTime()))){
				className = 'calendarista-selectedday-range calendarista-halfday-end';
			}
			if(ymd === selectedDate){
				className += ' calendarista-selectedday';
			}else if(theDate.getTime() == today.getTime()){
				className += ' calendarista-current';
			}
			if(!result || (theDate < today)){
				className = 'calendarista-unavailable';
				result = false;
			}else if(!className){
				className = 'calendarista-available';
			}
			if((startDateSelected && endDateSelected) && [3, 4, 5].indexOf(this.calendarMode) !== -1){
				if(theDate.getTime() >= startDateSelected.getTime() && theDate.getTime() <= endDateSelected.getTime()){
					className += ' calendarista-selectedday-range';
				}
			}
		}
		if([4/*MULTI_DATE_AND_TIME_RANGE*/].indexOf(this.calendarMode) !== -1 && bookedAvailabilityList.indexOf(ymd) !== - 1){
			className += ' calendarista-range-unavailable';
		}
		//
		return [result, className];
	};
	Calendarista.calendar.prototype.startDateBeforeShowDay = function(date){
		var selectedDate = this.startDateSelected ? this.toYMD(this.parseDate(this.startDateSelected)) : null;
		return this.beforeShowDay(date, selectedDate, 0/*startDate*/);
	};
	Calendarista.calendar.prototype.endDateBeforeShowDay = function(date){
		var selectedDate = this.endDateSelected ? this.toYMD(this.parseDate(this.endDateSelected)) : null;
		if(this.$startDate.length > 0 && !this.$startDate.is(':disabled')){
			this.$startDate.prop('disabled', true);
		}
		if(!selectedDate){
			selectedDate = this.startDateSelected ? this.toYMD(this.parseDate(this.startDateSelected)) : null;
		}
		return this.beforeShowDay(date, selectedDate, 1/*endDate*/);
	};
	Calendarista.calendar.prototype.onChangeMonthYearStartDate = function(year, month, inst){
		if(typeof(inst['lastVal']) == 'undefined'/* || this.appointment === 1*/){
			return;
		}
		var model = this.getCalendarModelByMonth(year, month);
		model.push({ 'name': 'requestBy', 'value': 0/*startDate*/ });
		this.calendarStateToggle();
		this.ajax.request(this, this.monthYearStartDateResponse, $.param(model));
	};
	Calendarista.calendar.prototype.monthYearStartDateResponse = function(result){
		this.monthYearResponse(result);
	};
	Calendarista.calendar.prototype.onChangeMonthYearEndDate = function(year, month, inst){
		/*if(this.appointment === 1){
			return;
		}*/
		var model = this.getCalendarModelByMonth(year, month);
		model.push({ 'name': 'requestBy', 'value': 1/*endDate*/ });
		this.calendarStateToggle();
		this.ajax.request(this, this.monthYearEndDateResponse, $.param(model));
	};
	Calendarista.calendar.prototype.monthYearEndDateResponse = function(result){
		this.monthYearResponse(result);
		this.costSummaryRequest(true/*queryDynamicFields*/);
	};
	Calendarista.calendar.prototype.calendarStateToggle = function(){
		var context = this;
		window.setTimeout(function(){
			$('.ui-datepicker-calendar a').parent().addClass('ui-datepicker-unselectable ui-state-disabled calendarista-unavailable');
			$('.ui-datepicker-prev').addClass('ui-state-disabled').off();
			$('.ui-datepicker-next').addClass('ui-state-disabled').off();
		}, 10);
	};
	Calendarista.calendar.prototype.monthYearResponse = function(result){
		var data = window['JSON'].parse(result);
		if(data['requestBy'] == 0/*startDate*/){
			this.startDateMonthYearResponse(data);
			this.$startDate.datepicker('refresh');
		}else if(data['requestBy'] == 1/*endDate*/){
			this.endDateMonthYearResponse(data);
			this.$endDate.datepicker('refresh');
		}
	};
	Calendarista.calendar.prototype.startDateMonthYearResponse = function(data){
		var i
			, item;
		this.startCurrentMonthExclusions.length = 0;
		this.startHalfDays['start'].length = 0;
		this.startHalfDays['end'].length = 0;
		this.startCheckinWeekdayList.length = 0;
		this.startCheckoutWeekdayList.length = 0;
		this.startBookedAvailabilityList.length = 0;
		this.startBookedOutDays.length = 0;
		this.holidays.length = 0;
		if(data['exclusions']){
			this.startCurrentMonthExclusions = data['exclusions'];
		}
		if(data['holidays']){
			this.holidays = data['holidays'];
		}
		if(this.appointment === 1){
			return;
		}
		if(data['bookedOutDays']){
			this.startBookedOutDays = data['bookedOutDays'];
		}
		if(data['halfDays']){
			this.startHalfDays['start'] = data['halfDays']['start'];
			this.startHalfDays['end'] = data['halfDays']['end'];
		}
		if(data['checkinWeekdayList']){
			this.startCheckinWeekdayList = data['checkinWeekdayList'];
		}
		if(data['checkoutWeekdayList']){
			this.startCheckoutWeekdayList = data['checkoutWeekdayList'];
		}
		if(data['bookedAvailabilityList']){
			this.startBookedAvailabilityList = data['bookedAvailabilityList'];
			for(i =  0; i < this.startBookedAvailabilityList.length; i++){
				item = this.startBookedAvailabilityList[i];
				if(this._startBookedAvailabilityList.indexOf(item) === -1){
					this._startBookedAvailabilityList.push(item);
				}
			}
		}
	};
	Calendarista.calendar.prototype.endDateMonthYearResponse = function(data){
		var i
			, item;
		this.endCurrentMonthExclusions.length = 0;
		this.endHalfDays['start'].length = 0;
		this.endHalfDays['end'].length = 0;
		this.endCheckinWeekdayList.length = 0;
		this.endCheckoutWeekdayList.length = 0;
		this.endBookedAvailabilityList.length = 0;
		this.endBookedOutDays.length = 0;
		/*if(this.appointment === 1){
			return;
		}*/
		if(data['exclusions']){
			this.endCurrentMonthExclusions = data['exclusions'];
		}
		if(data['bookedOutDays']){
			for(i =  0; i < data['bookedOutDays'].length; i++){
				item = data['bookedOutDays'][i];
				if(this.endBookedOutDays.indexOf(item) === -1){
					this.endBookedOutDays.push(item);
				}
			}
		}
		if(data['halfDays']){
			this.endHalfDays['start'] = data['halfDays']['start'];
			this.endHalfDays['end'] = data['halfDays']['end'];
		}
		if(data['checkinWeekdayList']){
			this.endCheckinWeekdayList = data['checkinWeekdayList'];
		}
		if(data['checkoutWeekdayList']){
			this.endCheckoutWeekdayList = data['checkoutWeekdayList'];
		}
		if(data['bookedAvailabilityList']){
			this.endBookedAvailabilityList = data['bookedAvailabilityList'];
			for(i =  0; i < this.endBookedAvailabilityList.length; i++){
				item = this.endBookedAvailabilityList[i];
				if(this._endBookedAvailabilityList.indexOf(item) === -1){
					this._endBookedAvailabilityList.push(item);
				}
			}
		}
	};
	Calendarista.calendar.prototype.onStartDateSelect = function(dateText) {
		var now = new Date()
			, currentTime = this.toTime(now)
			, selectedDate = this.parseDate(dateText)
			, ymd = this.toYMD(selectedDate)
			, endDate = [0,/*SINGLE_DAY*/ 1,/*SINGLE_DAY_AND_TIME*/ 2,/*SINGLE_DAY_AND_TIME_RANGE*/ 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/, 11/*MULTI_DATE*/, 12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1 ? ymd : (this.$endDateField ? this.$endDateField.val() : ymd)
			, selectedStartTime = this.$startTimeListbox ? this.$startTimeListbox.val() : -1
			, model1
			, $deals = this.$root.find('a.calendarista-timeslot-deals .calendarista-timeslot-deals-selected time')
			, $repeatAppointment = this.$root.find('input[name="repeatAppointment"]');
			if($deals.length > 0){
				selectedStartTime = parseInt($deals.attr('data-calendarista-value'), 10);
			}
			model1 = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'availabilityId', 'value': this.availabilityId}
				, { 'name': 'selectedDate', 'value': ymd }
				, { 'name': 'selectedStartTime', 'value': selectedStartTime }
				, { 'name': 'sameDay', 'value': false }
				, { 'name': 'clientTime', 'value': currentTime }
				, { 'name': 'searchResultStartTime', 'value': this.searchResultStartTime }
				, { 'name': 'timezone', 'value': this.timezone }
				, { 'name': 'calendarMode', 'value': this.calendarMode }
				, { 'name': '__viewstate', 'value': this.$viewState.val() }
				, { 'name': 'appointment', 'value': this.appointment }
				, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
				, { 'name': 'action', 'value': this.actionStartDaySelected }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
			];
			
		if($repeatAppointment.length > 0){
			$repeatAppointment.prop('checked', false);
			this.resetRepeat = true;
		}
		this.$startDate.parsley().reset();
		this._startDateVal = dateText;
		this.$clientStartDateField.val('');
		
		if([11/*MULTI_DATE*/].indexOf(this.calendarMode) !== -1){
			this.insertMultiDate(ymd);
		}
		if(endDate == ymd && this.searchResultEndTime){
			model1.push({ 'name': 'searchResultEndTime', 'value': this.searchResultEndTime });
		}
		if(this.endDateSelected && !this.validateDateRange(dateText, this.endDateSelected, 0/*startDate checkin calendar*/)){
			return;
		}
		this.currentTime = currentTime;
		this.$startDateField.val(ymd);
		if(this.$endDate.length === 0){
			this.$endDateField.val(ymd);
		}
		if(this.timeslotCalendarModes.indexOf(this.calendarMode) !== -1){
			this.ajax.request(this, this.startDayResponse, $.param(model1));
			if(this.calendarMode === 2/*SINGLE_DAY_AND_TIME_RANGE*/){
				this.$endDateField.val(ymd);
			}
		}
		if([0/*SINGLE_DAY*/, 11/*MULTI_DATE*/].indexOf(this.calendarMode) !== -1 || 
		([7/*ROUND_TRIP*/].indexOf(this.calendarMode) !== -1 && this.returnOptional)){
			this.seatsRequest();
		}
	};
	Calendarista.calendar.prototype.startDayResponse = function(result){
		var onStartTimeSelectedDelegate
			, timeResetClickDelegate
			, timeslotsRequired
			, $deals
			, $startTime
			, ymd = this.$startDateField.val()
			, endDate = [0,/*SINGLE_DAY*/ 1,/*SINGLE_DAY_AND_TIME*/ 2,/*SINGLE_DAY_AND_TIME_RANGE*/ 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/, 11/*MULTI_DATE*/, 12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1 ? ymd : (this.$endDateField ? this.$endDateField.val() : ymd)
			, selectedEndTime = this.$endTimeListbox ? this.$endTimeListbox.val() : -1
			, model1 = [
			{ 'name': 'projectId', 'value': this.projectId }
			, { 'name': 'availabilityId', 'value': this.availabilityId}
			, { 'name': 'selectedDate', 'value': ymd }
			, { 'name': 'selectedEndTime', 'value': selectedEndTime }
			, { 'name': 'sameDay', 'value': endDate == ymd }
			, { 'name': 'searchResultEndTime', 'value': this.searchResultEndTime }
			, { 'name': 'clientTime', 'value': this.currentTime}
			, { 'name': 'timezone', 'value': this.timezone }
			, { 'name': 'calendarMode', 'value': this.calendarMode }
			, { 'name': '__viewstate', 'value': this.$viewState.val() }
			, { 'name': 'appointment', 'value': this.appointment }
			, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
			, { 'name': 'action', 'value': this.actionEndDaySelected }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		if(this.$startTimeListbox){
			this.$startTimeListbox.off();
		}
		if(this.$startTimeDeals){
			this.$startTimeDeals.off();
		}
		if(this.$startTimeReset){
			this.$startTimeReset.off();
		}
		this.$startTimeslotPlaceHolder.removeClass('hide');
		this.$startTimeslotPlaceHolder.replaceWith(result);
		this.$startTimeslotPlaceHolder = this.$root.find('.calendarista-start-timeslot-placeholder');
		if(this.timeDisplayMode === 1/*Deals*/ && [1/*SINGLE_DAY_AND_TIME*/, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/, 12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1){
			this.$startTimeDeals = this.$root.find('a.calendarista-timeslot-deals');
			$deals = this.$root.find('a.calendarista-timeslot-deals .calendarista-timeslot-deals-selected time');
			this.onStartTimeDealClickedDelegate = Calendarista.createDelegate(this, this.onStartTimeDealClicked);
			this.$startTimeDeals.on('click', this.onStartTimeDealClickedDelegate);
			$startTime = this.$root.find('input[name="startTime"]');
		}
		this.$startTimeListbox = this.$root.find('select[name="startTime"]');
		if(this.$startTimeListbox.length === 0){
			this.$startTimeListbox = this.$root.find('select[name="startTime[]"]');
		}
		this.$startTimeReset = this.$root.find('.calendarista-starttime-reset');
		onStartTimeSelectedDelegate = Calendarista.createDelegate(this, this.onStartTimeSelected);
		this.$startTimeListbox.on('change', onStartTimeSelectedDelegate);
		timeResetClickDelegate = Calendarista.createDelegate(this, this.timeResetClick);
		this.$startTimeReset.on('click', timeResetClickDelegate);
		if(!this.validateTimeslots(this.$startTimeListbox, 0/*starttime*/)){
			this.setStartDate(null);
			this.$startDateField.val('');
			this._startDateVal = null;
		}
		if(this.$startTimeListbox.is('[multiple]')){
			this.disableOutOfStockListItems(this.$startTimeListbox);
		}
		if((this.$startTimeListbox.length > 0 && this.$startTimeListbox[0].selectedIndex > 0) || ($startTime && ($startTime.length > 0 && $startTime.val()))){
			if([1/*SINGLE_DAY_AND_TIME*/
				, 2/*SINGLE_DAY_AND_TIME_RANGE*/
				, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/].indexOf(this.calendarMode) !== -1){
				this.repeatRequest();
			}
		}
		if([8/*ROUND_TRIP_WITH_TIME*/].indexOf(this.calendarMode) !== -1 && this.returnSameDay){
			this.requestEndTimeslots();
		}
		if(this.timeslotCalendarModes.indexOf(this.calendarMode) !== -1){
			if(this.calendarMode === 2/*SINGLE_DAY_AND_TIME_RANGE*/){
				this.ajax.request(this, this.endDayResponse, $.param(model1));
			}
		}
		if([0/*SINGLE_DAY*/
			, 4/*MULTI_DATE_AND_TIME_RANGE*/
			, 11/*MULTI_DATE*/].indexOf(this.calendarMode) !== -1){
			this.costSummaryRequest(true/*queryDynamicFields*/);
		}else if([1/*SINGLE_DAY_AND_TIME*/
				/*, 2 SINGLE_DAY_AND_TIME_RANGE*/
				, 8/*ROUND_TRIP_WITH_TIME*/
				, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/
				, 12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1){
			if(this.$startTimeListbox.length > 0 && this.$startTimeListbox[0].selectedIndex > 0){
				//we are reloading the view, so repopulate seats
				this.seatsRequest({ 'name': 'startTime', 'value': parseInt(this.$startTimeListbox.find('option:selected').val(), 10)});
				return;
			}else if($deals && $deals.length > 0){
				this.seatsRequest({ 'name': 'startTime', 'value': parseInt($deals.attr('data-calendarista-value'), 10)});
				return;
			}
			this.seatsResponse('');
		}
	};
	Calendarista.calendar.prototype.seatsRequest = function(args){
		var  model = [
			{ 'name': 'projectId', 'value': this.projectId }
			, { 'name': 'availabilityId', 'value': this.availabilityId}
			, { 'name': 'startDate', 'value': this.$startDateField.val() }
			, { 'name': 'endDate', 'value': this.$endDateField.val() }
			, { 'name': '__viewstate', 'value': this.$viewState.val() }
			, { 'name': 'appointment', 'value': this.appointment }
			, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
			, { 'name': 'action', 'value': this.actionSeats }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		if(isNaN(this.projectId) || isNaN(this.availabilityId)){
			return false;
		}
		if(args){
			model = model.concat(args);
		}
		if(this.$availabilities && this.$availabilities.length > 0){
			this.$availabilities.prop('checked', false);
		}
		this.ajax.request(this, this.seatsResponse, $.param(model));
	};
	Calendarista.calendar.prototype.seatsResponse = function($result){
		if(this.$seats && this.$seats.length > 0){
			this.$seats.off();
		}
		this.$seatsPlaceholder.html($result);
		this.$seats = this.$root.find('select[name="seats"]');
		this.$seatsMaximum = this.$root.find('input[name="seatsMaximum"]');
		if(this.$seats.length === 0){
			this.$seats = this.$root.find('input[name="seats"]');
		}
		this.bookMoreRequest();
		this.$seats.on('change', Calendarista.createDelegate(this, this.seatSelectionChanged));
		if([
				0/*SINGLE_DAY*/, 1/*SINGLE_DAY_AND_TIME*/
				, 2/*SINGLE_DAY_AND_TIME_RANGE*/
				, 3/*MULTI_DATE_RANGE*/
				, 4/*MULTI_DATE_AND_TIME_RANGE*/
				, 5/*MULTI_DATE_RANGE_WITH_PARTIAL_DAY_CHARGE*/
				, 6/*PACKAGE*/
				, 7/*ROUND_TRIP*/
				, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/
				, 11/*MULTI_DATE*/
				, 12/*MULTI_DATE_AND_TIME*/
				, 8/*ROUND_TRIP_WITH_TIME*/
			].indexOf(this.calendarMode) !== -1){
			this.costSummaryRequest(true/*queryDynamicFields*/);
			return;
		}
	};
	Calendarista.calendar.prototype.dynamicFieldsRequest = function(){
		var  seats = this.$seats ? this.$seats.val() : 0
			, seatsMax = this.$seatsMaximum.length > 0 ? parseInt(this.$seatsMaximum.val(), 10) : 0
			, model = [
			{ 'name': 'projectId', 'value': this.projectId }
			, { 'name': 'availabilityId', 'value': this.availabilityId}
			, { 'name': 'seats', 'value': seats }
			, { 'name': 'seatsMax', 'value': seatsMax }
			, { 'name': '__viewstate', 'value': this.$viewState.val() }
			, { 'name': 'appointment', 'value': this.appointment }
			, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
			, { 'name': 'action', 'value': this.actionDynamicFields }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		]
		, dynamicFieldsCount = this.$dynamicFieldsCount.val();
		if(dynamicFieldsCount && parseInt(dynamicFieldsCount, 10) > 0){
			this.ajax.request(this, this.dynamicFieldsResponse, $.param(model));
			return true;
		}
		return false;
	};
	Calendarista.calendar.prototype.dynamicFieldsResponse = function($result){
		if(this.$dynamicFields){
			this.$dynamicFields.off();
		}
		this.$dynamicFieldsPlaceholder.html($result);
		this.$dynamicFields = this.$root.find('.calendarista-dynamicfield');
		this.$dynamicFields.on('change', Calendarista.createDelegate(this, this.dynamicFieldChange));
		this.costSummaryRequest();
	};
	Calendarista.calendar.prototype.dynamicFieldChange = function(e){
		var $currentTarget = $(e.currentTarget)
			, currentTargetId = $currentTarget.prop('id')
			, limitBySeat = parseInt($currentTarget.attr('data-calendarista-limit'), 10)
			, seatsMax = (this.$seatsMaximum.length > 0 && limitBySeat) ? parseInt(this.$seatsMaximum.val(), 10) : 0
			, selectedSeat = 0
			, selectedValue
			, i
			, j
			, $field
			, $options
			, $option
			, val;
		if(this.$seats.length > 0 && this.$seats[0].type === 'select-one'){
			seatsMax = parseInt(this.$seats.val(), 10);
		}
		if(this.$dynamicFields.length > 1){
			for(i = 0; i < this.$dynamicFields.length; i++){
				$field = $(this.$dynamicFields[i]);
				val = parseInt($field.val(), 10);
				if(val){
					selectedSeat += val;
				}
			}
		}
		if(seatsMax){
			for(i = 0; i < this.$dynamicFields.length; i++){
				$field = $(this.$dynamicFields[i]);
				if($field.prop('id') == currentTargetId){
					continue;
				}
				selectedValue = $field.val() ? parseInt($field.val(), 10) : 0;
				$options = $field.find('option');
				for(j = 0; j < $options.length; j++){
					$option = $($options[j]);
					val = $option.val() ? parseInt($option.val(), 10) : 0;
					if(val > selectedValue && ((seatsMax - selectedSeat) < val)){
						this.disableOption($option);
					}else if($option.is(':disabled')){
						$option.prop('disabled', false);
						$option.html($option.attr('data-calendarista-value'));
					}
				}
			}
		}
		this.costSummaryRequest();
	};
	Calendarista.calendar.prototype.bookMoreRequest = function(args){
		var model
			, seats;
		if(this.$bookMorePlaceholder.length === 0){
			return;
		}
		seats = this.$seats ? this.$seats.val() : 1;
		if(!seats){
			seats = 1;
		}
		model = [
			{ 'name': 'projectId', 'value': this.projectId }
			, { 'name': 'availabilityId', 'value': this.availabilityId}
			, { 'name': 'seats', 'value': seats }
			, { 'name': 'startDate', 'value': this.$startDateField.val() }
			, { 'name': 'endDate', 'value': this.$endDateField.val() }
			, { 'name': '__viewstate', 'value': this.$viewState.val() }
			, { 'name': 'appointment', 'value': this.appointment }
			, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
			, { 'name': 'action', 'value': this.actionBookMore }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		if(!args){
			if((this.$startTimeListbox && this.$startTimeListbox.length > 0) && this.$startTimeListbox.find('option:selected').val()){
				model.push({ 'name': 'startTime', 'value': parseInt(this.$startTimeListbox.find('option:selected').val(), 10)});
			}
			if((this.$endTimeListbox && this.$endTimeListbox.length > 0) && this.$endTimeListbox.find('option:selected').val()){
				model.push({ 'name': 'endTime', 'value': parseInt(this.$endTimeListbox.find('option:selected').val(), 10)});
			}
		}
		if(isNaN(this.projectId) || isNaN(this.availabilityId)){
			return false;
		}
		if(args){
			model = model.concat(args);
		}
		this.ajax.request(this, this.bookMoreResponse, $.param(model));	
	};
	Calendarista.calendar.prototype.bookMoreResponse = function($result){
		if(this.$availabilities && this.$availabilities.length > 0){
			this.$availabilities.off();
		}
		this.$bookMorePlaceholder.html($result);
		this.$availabilities = this.$root.find('input[name="availabilities[]"]');
		this.$availabilities.on('change', Calendarista.createDelegate(this, this.bookMoreSelectionChanged));
		this.costSummaryRequest(true/*queryDynamicFields*/);
	};
	Calendarista.calendar.prototype.bookMoreSelectionChanged = function(){
		this.costSummaryRequest(true/*queryDynamicFields*/);
	};
	Calendarista.calendar.prototype.repeatRequest = function(args){
		var model;
		if([0/*SINGLE_DAY*/ 
			, 1/*SINGLE_DAY_AND_TIME*/
			, 2/*SINGLE_DAY_AND_TIME_RANGE*/
			, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/].indexOf(this.calendarMode) === -1){
			return;
		}
		if(this.$repeatPlaceholder.length === 0){
			return;
		}
		model = [
			{ 'name': 'projectId', 'value': this.projectId }
			, { 'name': 'availabilityId', 'value': this.availabilityId}
			, { 'name': 'availableDate', 'value': this.$startDateField.val() }
			, { 'name': '__viewstate', 'value': this.$viewState.val() }
			, { 'name': 'appointment', 'value': this.appointment }
			, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
			, { 'name': 'action', 'value': this.actionRepeat }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		if(this.resetRepeat){
			model.push({'name': 'resetRepeat', 'value':  1});
		}
		if(isNaN(this.projectId) || isNaN(this.availabilityId)){
			return false;
		}
		if(args){
			model = model.concat(args);
		}
		this.ajax.request(this, this.repeatResponse, $.param(model));	
	};
	Calendarista.calendar.prototype.repeatResponse = function($result){
		var $viewState;
		this.$repeatPlaceholder.html($result);
		$viewState = this.$root.find('input[name="___viewstate"]');
		if($viewState.length > 0){
			this.$viewState.val($viewState.val());
		}
	};
	Calendarista.calendar.prototype.getRepeatAppointmentModel = function(){
		var  model = []
		, $repeat = this.$root.find('input[name="repeatAppointment"]')
		, $repeatFrequencySelectList = this.$root.find('select[name="repeatFrequency"]')
		, $repeatIntervalSelectList = this.$root.find('select[name="repeatInterval"]')
		, $terminateAfterOccurrenceSelectList = this.$root.find('select[name="terminateAfterOccurrence"]')
		, $repeatWeekdayCheckboxList = this.$root.find('input[name="repeatWeekdayList[]"]:checked')
		, $repeatFrequencyInput = this.$root.find('input[name="repeatFrequency"]')
		, $repeatIntervalInput = this.$root.find('input[name="repeatInterval"]')
		, $terminateAfterOccurrenceInput = this.$root.find('input[name="terminateAfterOccurrence"]')
		, repeatAppointment = $repeat.is(':checked')
		, $repeatAppointmentDates = this.$root.find('input[name="repeatAppointmentDates[]"]')
		, repeatInterval = $repeatIntervalSelectList.length > 0 ? $repeatIntervalSelectList.val() : $repeatIntervalInput.val()
		, repeatFrequency = $repeatFrequencySelectList.length > 0 ? $repeatFrequencySelectList.val() : $repeatFrequencyInput.val()
		, terminateAfterOccurrence = $terminateAfterOccurrenceSelectList.length > 0 ? $terminateAfterOccurrenceSelectList.val() : $terminateAfterOccurrenceInput.val()
		, repeatWeekdayList = [];
		
		if(repeatAppointment){
			if($repeatWeekdayCheckboxList.length > 0){
				repeatWeekdayList = $repeatWeekdayCheckboxList.map(function(){
				  return $(this).val();
				}).get();
			}
			model = [
				{ 'name': 'repeatAppointment', 'value': 1 }
				, { 'name': 'repeatInterval', 'value': repeatInterval }
				, { 'name': 'repeatFrequency', 'value': repeatFrequency}
				, { 'name': 'terminateAfterOccurrence', 'value': terminateAfterOccurrence }
			];
			if(repeatWeekdayList.length > 0){
				model = model.concat([
					{ 'name': 'repeatWeekdayList', 'value': repeatWeekdayList }
				]);
			}
		}
		return model;
	};
	Calendarista.calendar.prototype.getCostSummaryModel = function(){
		var $root =  $('#' + this.id)
			, $postbackStep = $root.find('input[name="postbackStep"]')
			, startTime = this.$startTimeListbox ? this.$startTimeListbox.val() : ''
			, startDate = this.$startDateField ? this.$startDateField.val() : ''
			, endTime = this.$endTimeListbox ? this.$endTimeListbox.val() : ''
			, endDate = this.$endDateField ? this.$endDateField.val() : ''
			, seats = this.$seats ? this.$seats.val() : ''
			, multiDateSelection = this.$multiDateSelection.val()
			, model
			, i
			, $deals = this.$root.find('a.calendarista-timeslot-deals .calendarista-timeslot-deals-selected time')
			, $availability
			, availabilities = []
			, repeatAppointmentModel;
			
		if($deals.length > 0){
			startTime = parseInt($deals.attr('data-calendarista-value'), 10);
		}
		switch(this.calendarMode){
			case 1:/*SINGLE_DAY_AND_TIME*/
			case 8:/*ROUND_TRIP_WITH_TIME*/
			if(!startTime){
				this.costSummaryReset();
				return false;
			}
			break;
			case 2:/*SINGLE_DAY_AND_TIME_RANGE*/
			case 4:/*MULTI_DATE_AND_TIME_RANGE*/
			if(!startTime || !endTime){
				this.costSummaryReset();
				return false;
			}
			break;
			case 3:/*MULTI_DATE_RANGE*/
			case 5:/*CHANGEOVER*/
			if(!startDate || !endDate){
				this.costSummaryReset();
				return false;
			}
			break;
		}
		if(startTime instanceof Array){
			if(startTime.length > 1){
				//got just a single day, but multiple timeslots
				endTime = startTime[startTime.length - 1];
				endDate = startDate;
			}
			startTime = startTime[0];
		}
		if(this.$availabilities && this.$availabilities.length > 0){
			for(i = 0; i < this.$availabilities.length;i++){
				$availability = $(this.$availabilities[i]);
				if($availability.is(':checked')){
					availabilities.push(parseInt($availability.val(), 10));
				}
			}
		}
		model = [
			{ 'name': 'projectId', 'value': this.projectId }
			, { 'name': 'availabilityId', 'value': this.availabilityId}
			, { 'name': 'appointment', 'value': this.appointment}
			, { 'name': 'availableDate', 'value': startDate }
			, { 'name': 'multiDateSelection', 'value': multiDateSelection }
			, { 'name': 'startTime', 'value': startTime }
			, { 'name': 'endDate', 'value': endDate }
			, { 'name': 'endTime', 'value': endTime }
			, { 'name': 'seats', 'value': seats }
			, { 'name': 'calendarMode', 'value': this.calendarMode }
			, { 'name': 'timezone', 'value': this.timezone }
			, { 'name': 'availabilities', 'value': availabilities.join(',') }
			, { 'name': 'postbackStep', 'value': $postbackStep.val() }
			, { 'name': '__viewstate', 'value': this.$viewState.val() }
			, { 'name': 'action', 'value': this.actionCostSummary }
			, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		if(this.$dynamicFields && this.$dynamicFields.length > 0){
			for(i = 0; i < this.$dynamicFields.length;i++){
				model.push({'name': this.$dynamicFields[i].name, 'value': this.$dynamicFields[i].value});
			}
		}
		repeatAppointmentModel = this.getRepeatAppointmentModel();
		if(repeatAppointmentModel.length > 0){
			model = model.concat(repeatAppointmentModel);
		}
		if(this.resetRepeat){
			model.push({'name': 'resetRepeat', 'value':  1});
		}
		return model;
	};
	Calendarista.calendar.prototype.mapCostSummaryRequest = function(args){
		var model = this.getCostSummaryModel()
			, result;
		if(!model){
			return;
		}
		result = model.concat(args);
		//edge case, calling into this method from external source, DOM has been refreshed
		this.$costSummaryPlaceholder = $('#' + this.id).find('.calendarista-cost-summary-placeholder');
		//artificial delay, because this method is usually called after a previous callback.
		(function(model,  context){
			window.setTimeout(function(){
				context.ajax.request(context, context.costSummaryResponse, $.param(model));
			}, 10);
		})(result, this);
	};
	Calendarista.calendar.prototype.seatSelectionChanged = function(){
		if(this.$bookMorePlaceholder.length === 0){
			this.costSummaryRequest(true/*queryDynamicFields*/);
			return;
		}
		(function(context){
			window.setTimeout(function(){
				context.bookMoreRequest();
			}, 10);
		})(this);
	};
	Calendarista.calendar.prototype.costSummaryRequest = function(queryDynamicFields, args){
		var model = this.getCostSummaryModel()
			, $multiDateButtons = this.$root.find('.calendarista-multi-date-btn')
			, $repeatDateButtons = this.$root.find('.calendarista-repeat-date-btn');
		if(!model){
			return false;
		}
		if(args){
			model = model.concat(args);
		}
		if(queryDynamicFields === true && this.dynamicFieldsRequest()){
			return false;
		}
		$multiDateButtons.off();
		$repeatDateButtons.off();
		//artificial delay, because this method is usually called after a previous callback.
		(function(model,  context){
			window.setTimeout(function(){
					context.ajax.request(context, context.costSummaryResponse, $.param(model));
			}, 10);
		})(model, this);
	};
	Calendarista.calendar.prototype.costSummaryResponse = function(result){
		var $multiDateButtons
			, $repeatDateButtons
			, $repeatShowMoreButton;
		this.$costSummaryPlaceholder.html(result);
		$multiDateButtons = this.$costSummaryPlaceholder.find('.calendarista-multi-date-btn');
		if($multiDateButtons.length > 0){
			this.multiDateButtonClickDelegate = Calendarista.createDelegate(this, this.removeDateClickHandler);
			$multiDateButtons.on('click', this.multiDateButtonClickDelegate);
		}
		$repeatDateButtons = this.$costSummaryPlaceholder.find('.calendarista-repeat-date-btn');
		if($repeatDateButtons.length > 0){
			this.repeatDateButtonClickDelegate = Calendarista.createDelegate(this, this.removeRepeatDateClickHandler);
			$repeatDateButtons.on('click', this.repeatDateButtonClickDelegate);
		}
		$repeatShowMoreButton = this.$costSummaryPlaceholder.find('.calendarista-repeat-show-more-btn');
		if($repeatShowMoreButton.length > 0){
			this.repeatShowMoreButtonClickDelegate = Calendarista.createDelegate(this, this.repeatShowMoreClickHandler);
			$repeatShowMoreButton.on('click', this.repeatShowMoreButtonClickDelegate);
		}
		this.resetRepeat = false;
	};
	Calendarista.calendar.prototype.costSummaryReset = function(){
		this.$costSummaryPlaceholder.html('');
	};
	Calendarista.calendar.prototype.validateTimeslots = function($listbox, $slotType){
		var $options = $listbox.find('option:not([disabled])')
			, multiTimeslotSelection = this.$startTimeListbox && this.$startTimeListbox.is('[multiple]')
			, startTimeDeals = this.$startTimeDeals && this.$startTimeDeals.length > 0
			, $timeslotsError = $slotType && this.returnSameDay ? this.$timeslotsReturnError : this.$timeslotsError;
		if($listbox.length === 0 && !startTimeDeals){
			//invalid, show error
			$timeslotsError.removeClass('hide');
			return false;
		}
		if(startTimeDeals){
			$timeslotsError.addClass('hide');
			return true;
		}
		this.adjustStartTime();
		this.adjustEndTime();
		if($listbox.prop('type') === 'hidden' || (!multiTimeslotSelection && $options.length === 1)){
			//invalid, show error
			$timeslotsError.removeClass('hide');
			return false;
		}
		$timeslotsError.addClass('hide');
		return true;
	};
	Calendarista.calendar.prototype.validateTimeRange = function(){
		var  $startTime = this.$startTimeListbox ? this.$startTimeListbox.find(':selected') : null
			, $endTime = this.$endTimeListbox ? this.$endTimeListbox.find(':selected') : null
			, startHour
			, startMinute
			, endHour
			, endMinute
			, startDateTime
			, endDateTime
			, millis
			, minutes;
		if(!this.minTime && !this.maxTime){
			return true;
		}
		if(!$startTime || !$endTime){
			return true;
		}
		startHour = parseInt($startTime.attr('data-calendarista-hour'), 10);
		startMinute = parseInt($startTime.attr('data-calendarista-minute'), 10);
		
		endHour = parseInt($endTime.attr('data-calendarista-hour'), 10);
		endMinute = parseInt($endTime.attr('data-calendarista-minute'), 10);
		
		if(!isNaN(startHour) && !isNaN(startMinute)){
			startDateTime = new Date();
			startDateTime.setHours(startHour, startMinute, 0);
		}
		if(!isNaN(endHour) && !isNaN(endMinute)){
			endDateTime = new Date();
			endDateTime.setHours(endHour, endMinute, 0);
		}
		if(startDateTime && endDateTime){
			if(endDateTime <= startDateTime){
				endDateTime.setDate(endDateTime.getDate() + 1);
			}
			millis = endDateTime - startDateTime;
			minutes = Math.floor(millis / 60000);
			if(this.minTime){
				if(minutes < this.minTime){
					this.$minTimeError.val('');
					this.$minTimeError.parsley().validate();
					return false;
				}else{
					this.$minTimeError.val('-1');
					this.$minTimeError.parsley().reset();
				}
			}
			if(this.maxTime){
				if(minutes > this.maxTime){
					this.$maxTimeError.val('');
					this.$maxTimeError.parsley().validate();
					return false;
				}else{
					this.$maxTimeError.val('-1');
					this.$maxTimeError.parsley().reset();
				}
			}
		}else{
			this.$minTimeError.val('-1');
			this.$minTimeError.parsley().reset();
			this.$maxTimeError.val('-1');
			this.$maxTimeError.parsley().reset();
		}
		return true;
	};
	Calendarista.calendar.prototype.disableOutOfStockListItems = function($listbox){
		var i
			, $options
			, $option;
		if(!$listbox.is('[multiple]')){
			return;
		}
		$options = $listbox.find('option');
		for(i = 0; i < $options.length; i++){
			$option = $($options[i]);
			if($option[0].hasAttribute('data-calendarista-outofstock')){
				this.disableOption($option);
			}
		}
	}
	Calendarista.calendar.prototype.onStartTimeSelected = function(e){
		var $options
			, $option
			, selectedOptions
			, i
			, j = 0
			, maxTimeslots
			, model
			, outOfStock
			, tm
			, flag = false
			, callbackParams = []
			, $selectedEndTime
			, selectedEndTimeValue
			, $repeatAppointment = this.$root.find('input[name="repeatAppointment"]');;
		if(this.$endTimeListbox){
			$selectedEndTime = this.$endTimeListbox.find('option:selected');
			selectedEndTimeValue = $selectedEndTime.val();
		}
		this.adjustStartTime();
		this.adjustEndTime();
		this.validateTimeRange();
		if([1/*SINGLE_DAY_AND_TIME*/
			, 2/*SINGLE_DAY_AND_TIME_RANGE*/
			, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/, 12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1){
			if(this.$startTimeListbox.is('[multiple]')){
				maxTimeslots = parseInt(this.$startTimeListbox.attr('data-calendarista-max-timeslots'), 10);
				$options = this.$startTimeListbox.find('option');
				selectedOptions = this.$startTimeListbox.find('option:selected').map(function(){ return this.index; }).get();
				for(i = 0; i < $options.length; i++){
					$option = $($options[i]);
					$option.prop('selected', false);
					outOfStock = $option[0].hasAttribute('data-calendarista-outofstock');
					if(outOfStock){
						this.disableOption($option);
					}
					//check bounds btw first selection and last selection
					if(i >= selectedOptions[0] && i <= selectedOptions[selectedOptions.length - 1]){
						if(flag || ((maxTimeslots && j >= maxTimeslots) || outOfStock)){
							if(!flag){
								flag = outOfStock;
							}
							continue;
						}
						$option.prop('selected', true);
						callbackParams.push($option[0]);
						++j;
					}
				}
				if(window[this.callbackTimeslotSelected]){
					window[this.callbackTimeslotSelected](callbackParams);
				}
			}
			if([1/*SINGLE_DAY_AND_TIME*/
				, 2/*SINGLE_DAY_AND_TIME_RANGE*/
				, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/].indexOf(this.calendarMode) !== -1){
				if($repeatAppointment.length > 0){
					$repeatAppointment.prop('checked', false);
					this.resetRepeat = true;
				}
				this.repeatRequest();
			}
			if([1/*SINGLE_DAY_AND_TIME*/, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/, 12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1){
				tm = parseInt(this.$startTimeListbox.find('option:selected').val(), 10);
				this.insertMultiDateTime(tm);
				this.seatsRequest([{ 'name': 'startTime', 'value': tm}]);
			}else{
				this.costSummaryRequest(true/*queryDynamicFields*/);
			}
			
		}else if([4/*MULTI_DATE_AND_TIME_RANGE*/, 8/*ROUND_TRIP_WITH_TIME*/].indexOf(this.calendarMode) !== -1){
			if(selectedEndTimeValue || this.returnOptional){
				this.seatsRequest([{ 'name': 'startTime', 'value': parseInt(this.$startTimeListbox.find('option:selected').val(), 10)}]);
				return;
			}
			if(this.$bookMorePlaceholder.length > 0 && selectedEndTimeValue){
				this.bookMoreRequest();
				return;
			}
			//if we already have an endtime selected and now we're selecting the starttime, show summary-placeholder
			this.costSummaryRequest(true/*queryDynamicFields*/);
		}
	};
	Calendarista.calendar.prototype.onStartTimeDealClicked = function(e){
		e.preventDefault();
		var $currentTarget = $(e.currentTarget).find('time')
			, tm = parseInt($currentTarget.attr('data-calendarista-value'), 10)
			, $startTime = this.$root.find('input[name="startTime"]')
			, $repeatAppointment = this.$root.find('input[name="repeatAppointment"]');;
			$startTime.val(tm);
			this.$startTimeDeals.find('.calendarista-timeslot-deals-tile').removeClass('calendarista-timeslot-deals-selected');
			$(e.currentTarget).find('.calendarista-timeslot-deals-tile').addClass('calendarista-timeslot-deals-selected');
			$startTime.parsley().reset();
			this.insertMultiDateTime(tm);
			if(window[this.callbackTimeslotSelected]){
				window[this.callbackTimeslotSelected]([$startTime[0]]);
			}
			if($repeatAppointment.length > 0){
				$repeatAppointment.prop('checked', false);
				this.resetRepeat = true;
			}
			this.repeatRequest();
			this.seatsRequest([{ 'name': 'startTime', 'value': tm}]);
	};
	Calendarista.calendar.prototype.insertMultiDate = function(ymd){
		var multiDateSelection = this.$multiDateSelection.val()
			, bookingDaysMinimum = this.bookingDaysMinimum
			, bookingDaysMaximum = this.bookingDaysMaximum;
		if([11/*MULTI_DATE*/].indexOf(this.calendarMode) !== -1 && ymd){
			multiDateSelection = multiDateSelection ? multiDateSelection.split(';') : [];
			if((!bookingDaysMaximum || multiDateSelection.length < bookingDaysMaximum) && multiDateSelection.indexOf(ymd) === -1){
				multiDateSelection.push(ymd);
			}
			multiDateSelection = multiDateSelection.join(';');
			this.$multiDateSelection.val(multiDateSelection);
			this.datepickerReset(false);
		}
		return this.$multiDateSelection.val();
	};
	Calendarista.calendar.prototype.insertMultiDateTime = function(tm){
		var multiDateSelection = this.$multiDateSelection.val()
			, ymd = this.$startDateField.val()
			, ymdtm = ymd + ':' + tm
			, index
			, bookingDaysMinimum = this.bookingDaysMinimum
			, bookingDaysMaximum = this.bookingDaysMaximum;
		if([12/*MULTI_DATE_AND_TIME*/].indexOf(this.calendarMode) !== -1 && tm){
			multiDateSelection = multiDateSelection ? multiDateSelection.split(';') : [];
			if(!bookingDaysMaximum || multiDateSelection.length < bookingDaysMaximum){
				index = this.inArray(multiDateSelection, ymd);
				if(index !== -1 && multiDateSelection.indexOf(ymdtm) === -1){
					//multiDateSelection.splice(index, 1);
					multiDateSelection.splice(index, 1, ymdtm);
				} else if(multiDateSelection.indexOf(ymdtm) === -1){
					multiDateSelection.push(ymdtm);
				}
				multiDateSelection = multiDateSelection.join(';');
				this.$multiDateSelection.val(multiDateSelection);
				this.datepickerReset(false);
			}
		}
		return this.$multiDateSelection.val();
	};
	Calendarista.calendar.prototype.inArray = function(arr, val){
		var i;
		if(arr){
			for (i = 0; i < arr.length; i++){
				if(arr[i].indexOf(val) !== -1){
					return i;
				}
			}
		}
		return -1;
	};
	Calendarista.calendar.prototype.removeDateClickHandler = function(e){
		var $currentTarget = $(e.currentTarget)
			, value = $currentTarget.attr('data-calendarista-value')
			, multiDateSelection = this.$multiDateSelection.val()
			, i;
		 if(multiDateSelection){
			multiDateSelection = multiDateSelection.split(';');
			i = multiDateSelection.indexOf(value);
			if(i !== -1){
				multiDateSelection.splice(i, 1);
			}
			this.$multiDateSelection.val(multiDateSelection.join(';'));
			this.datepickerReset(false);
			this.costSummaryRequest(true/*queryDynamicFields*/);
		 }
	};
	Calendarista.calendar.prototype.removeRepeatDateClickHandler = function(e){
		var $currentTarget = $(e.currentTarget)
			, value = $currentTarget.attr('data-calendarista-value')
			, $repeatDateContainer = $('#' + value)
			, repeatAppointmentDates
			, model = []
			, i;
		 if($repeatDateContainer){
			$repeatDateContainer.remove();
			repeatAppointmentDates = this.$root.find('input[name="repeatAppointmentDates[]"]').map(function(){
			  return $(this).val();
			}).get();
			if(repeatAppointmentDates.length > 0){
				for(i = 0; i < repeatAppointmentDates.length; i++){
					model.push({ 'name': 'repeatAppointmentDates[]', 'value': repeatAppointmentDates[i] });
				}
			}
			this.costSummaryRequest(false/*queryDynamicFields*/, model);
		 }
	};
	Calendarista.calendar.prototype.repeatShowMoreClickHandler = function(e){
		var $currentTarget = $(e.currentTarget)
			, value = $currentTarget.attr('data-calendarista-value')
			, $repeatShowMoreContainer = $('#' + value)
			, $repeatDates = this.$costSummaryPlaceholder.find('.calendarista-repeat-date-badge.hide')
			, i;
		 if($repeatDates.length > 0){
			for(i = 0; i <= $repeatDates.length; i++){
				if(i >= this.repeatPageSize){
					break;
				}
				$($repeatDates[i]).removeClass('hide');
			}
		 }
		 $repeatDates = this.$costSummaryPlaceholder.find('.calendarista-repeat-date-badge.hide');
		 if($repeatDates.length === 0 && $repeatShowMoreContainer.length > 0){
			 $repeatShowMoreContainer.hide();
		 }
	};
	Calendarista.calendar.prototype.adjustEndTime = function(){
		if(this.endDateSelected && (this.startDateSelected !== this.endDateSelected && this.calendarMode !== 2/*SINGLE_DAY_AND_TIME_RANGE*/)){
			return;
		}
		var i
			, selectedIndex = (this.$startTimeListbox && this.$startTimeListbox.length > 0) ? this.$startTimeListbox[0].selectedIndex : 0
			, $endTimeOptions = (this.$endTimeListbox && this.$endTimeListbox.length > 0) ? this.$endTimeListbox.find('option') : null
			, $option;
		if($endTimeOptions){
			$endTimeOptions.prop('disabled', false);
			for(i = 0; i < $endTimeOptions.length; i++){
				$option = $($endTimeOptions[i]);
				if (i > 0 && i < selectedIndex /*|| $option[0].hasAttribute('data-calendarista-outofstock')*/){
					this.disableOption($option);
				}else{
					$option.html($option.attr('data-calendarista-time'));
				}
			}
		}
		if(this.$endTimeListbox){
			this.disableTimeResetButtons(this.$endTimeListbox);
		}
		this.filterOutOfStockSlots();
	};
	Calendarista.calendar.prototype.filterOutOfStockSlots = function(){
		var i
			, $option1
			, $startTimeOptions = (this.$startTimeListbox && this.$startTimeListbox.length > 0) ? this.$startTimeListbox.find('option') : null
			, $endTimeOptions = (this.$endTimeListbox && this.$endTimeListbox.length > 0) ? this.$endTimeListbox.find('option') : null
			, startTimeSelectedIndex = (this.$startTimeListbox && this.$startTimeListbox.length > 0) ? this.$startTimeListbox[0].selectedIndex : 0
			, selectedStartTimeOption = startTimeSelectedIndex ? this.$startTimeListbox[0].options[startTimeSelectedIndex] : null
			, selectedStartTime = selectedStartTimeOption ? $(selectedStartTimeOption).attr('data-calendarista-24h') : null
			, endTimeSelectedIndex = (this.$endTimeListbox && this.$endTimeListbox.length > 0) ? this.$endTimeListbox[0].selectedIndex : 0
			, roundTrip = false
			, $outOfStockOptions = $startTimeOptions ? $startTimeOptions.filter('[data-calendarista-outofstock]') : null
			, firstOutOfStockIndex = -1
			, lastOutOfStockIndex = -1
			, sameDay = this.startDateSelected == this.endDateSelected || !this.endDateSelected
			, adaptive = true
			, flag;
		if([1/*SINGLE_DAY_AND_TIME*/
			/*, 8 ROUND_TRIP_WITH_TIME*/
			, 9/*SINGLE_DAY_AND_TIME_WITH_PADDING*/].indexOf(this.calendarMode) !== -1 && !this.$startTimeListbox.is('[multiple]')){
			adaptive = false;
		}
		if($startTimeOptions){
			if(adaptive && ((!sameDay && [2/*SINGLE_DAY_AND_TIME_RANGE*/, 4/*MULTI_DATE_AND_TIME_RANGE*/].indexOf(this.calendarMode) !== -1) && ($outOfStockOptions && $outOfStockOptions.length > 0))){
				firstOutOfStockIndex = sameDay ? $outOfStockOptions[0].index :  $outOfStockOptions[$outOfStockOptions.length - 1].index;
			}
			for(i = 0; i < $startTimeOptions.length; i++){
				$option1 = $($startTimeOptions[i]);
				if(i > 0 && (firstOutOfStockIndex !== -1 && i < firstOutOfStockIndex)){
					this.disableOption($option1);
					continue;
				}
				if($option1[0].hasAttribute('data-calendarista-outofstock')){
					this.disableOption($option1);
				}
			}
		}
		if($endTimeOptions){
			if(adaptive){
				if(!sameDay || $outOfStockOptions && $outOfStockOptions.length === 0){
					$outOfStockOptions = $endTimeOptions ? $endTimeOptions.filter('[data-calendarista-outofstock]') : null;
				}
				if($outOfStockOptions && $outOfStockOptions.length > 0){
					lastOutOfStockIndex = sameDay ? $outOfStockOptions[$outOfStockOptions.length - 1].index : $outOfStockOptions[0].index;
				}
			}
			if([8/*ROUND_TRIP_WITH_TIME*/].indexOf(this.calendarMode) !== -1){
				for(i = 0; i < $endTimeOptions.length; i++){
					$option1 = $($endTimeOptions[i]);
					if(sameDay && (selectedStartTime && $option1[0].hasAttribute('data-calendarista-return-trip'))){
						if(selectedStartTime >= $option1.attr('data-calendarista-24h')){
							this.disableOption($option1);
						}
					}
					if($option1[0].hasAttribute('data-calendarista-outofstock')){
						this.disableOption($option1);
					}
				}
				//data-calendarista-time
			}
			else{
				for(i = 0; i < $endTimeOptions.length; i++){
					$option1 = $($endTimeOptions[i]);
					if(lastOutOfStockIndex === -1){
						continue;
					}
					if(startTimeSelectedIndex <= lastOutOfStockIndex &&  i >= lastOutOfStockIndex){
						this.disableOption($option1);
					}else if((!sameDay && $outOfStockOptions.length > 0) && i >= $outOfStockOptions[$outOfStockOptions.length - 1].index){
						//different days, but start time has out of stock, so fullfill
						this.disableOption($option1);
					}
				}
			
				if((sameDay && lastOutOfStockIndex !== -1) && startTimeSelectedIndex < lastOutOfStockIndex){
					for(i = startTimeSelectedIndex; i < lastOutOfStockIndex; i++){
						$option1 = $($endTimeOptions[i]);
						if(!flag && $option1[0].hasAttribute('data-calendarista-outofstock')){
							flag = true;
							if(startTimeSelectedIndex !== 0){
								//a start is selected, so fullfill
								continue;
							}
						}
						if(flag){
							this.disableOption($option1);
						}
					}
				}
			}
		}
	}
	Calendarista.calendar.prototype.disableOption = function($option){
		if(!$option.is(':disabled')){
			$option.prop('disabled', true);
			$option.prop('selected', false);
			$option.html($option.html().replace(/\d/g, function (digit) { return digit + "\u0336" }));
		}
	};
	Calendarista.calendar.prototype.onEndDateSelect = function(dateText) {
		var context = this
			, now = new Date()
			, currentTime = this.toTime(now)
			, ymd = this.toYMD(this.parseDate(dateText))
			, startDate = this.$startDateField ? this.$startDateField.val() : ''
			, selectedStartTime = this.$startTimeListbox ? this.$startTimeListbox.val() : -1
			, selectedEndTime = this.$endTimeListbox ? this.$endTimeListbox.val() : -1
			, model1 = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'availabilityId', 'value': this.availabilityId}
				, { 'name': 'selectedDate', 'value': startDate }
				, { 'name': 'selectedStartTime', 'value': selectedStartTime }
				, { 'name': 'sameDay', 'value': false }
				, { 'name': 'clientTime', 'value': currentTime }
				, { 'name': 'searchResultStartTime', 'value': this.searchResultStartTime }
				, { 'name': 'timezone', 'value': this.timezone }
				, { 'name': 'calendarMode', 'value': this.calendarMode }
				, { 'name': '__viewstate', 'value': this.$viewState.val() }
				, { 'name': 'appointment', 'value': this.appointment }
				, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
				, { 'name': 'action', 'value': this.actionStartDaySelected }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
			]
			, model2 = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'availabilityId', 'value': this.availabilityId}
				, { 'name': 'appointment', 'value': this.appointment}
				, { 'name': 'selectedDate', 'value': ymd }
				, { 'name': 'selectedEndTime', 'value': selectedEndTime }
				, { 'name': 'sameDay', 'value': startDate == ymd }
				, { 'name': 'searchResultEndTime', 'value': this.searchResultEndTime }
				, { 'name': 'clientTime', 'value': this.currentTime}
				, { 'name': 'calendarMode', 'value': this.calendarMode }
				, { 'name': 'timezone', 'value': this.timezone }
				, { 'name': '__viewstate', 'value': this.$viewState.val() }
				, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
				, { 'name': 'action', 'value': this.actionEndDaySelected }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		this.$endDate.parsley().reset();
		this._endDateVal = dateText;
		this.$clientEndDateField.val('');
		if(!this.endDateSelected && !this.validateDateRange(this.startDateSelected, dateText, 1/*endDate checkout calendar*/)){
			this.datepickerReset();
			return;
		}
		this.$endDateField.val(ymd);
		if([4/*MULTI_DATE_AND_TIME_RANGE*/, 8/*ROUND_TRIP_WITH_TIME*/].indexOf(this.calendarMode) !== -1){
			//artificial delay, because the request is being made after a previous callback.
			(function(model,  context){
				window.setTimeout(function(){
						context.ajax.request(context, context.endDayResponse, $.param(model));
				}, 10);
			})(model2, context);
		} else if([3/*MULTI_DATE_RANGE*/, 7/*ROUND_TRIP*/, 5/*MULTI_DATE_RANGE_WITH_PARTIAL_DAY_CHARGE*/].indexOf(this.calendarMode) !== -1){
			this.seatsRequest();
		}
	};
	Calendarista.calendar.prototype.requestEndTimeslots = function() {
		var context = this
			, now = new Date()
			, currentTime = this.toTime(now)
			, startDate = this.$startDateField ? this.$startDateField.val() : ''
			, selectedEndTime = this.$endTimeListbox ? this.$endTimeListbox.val() : -1
			, model2 = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'availabilityId', 'value': this.availabilityId}
				, { 'name': 'appointment', 'value': this.appointment}
				, { 'name': 'selectedDate', 'value': startDate }
				, { 'name': 'selectedEndTime', 'value': selectedEndTime }
				, { 'name': 'sameDay', 'value': true }
				, { 'name': 'searchResultEndTime', 'value': this.searchResultEndTime }
				, { 'name': 'clientTime', 'value': this.currentTime}
				, { 'name': 'calendarMode', 'value': this.calendarMode }
				, { 'name': 'timezone', 'value': this.timezone }
				, { 'name': '__viewstate', 'value': this.$viewState.val() }
				, { 'name': 'calendarista_cart', 'value': this.$cart.val() }
				, { 'name': 'action', 'value': this.actionEndDaySelected }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
		];
		this._endDateVal = startDate;
		this.$clientEndDateField.val('');
		this.$endDateField.val(startDate);
		(function(model,  context){
			window.setTimeout(function(){
					context.ajax.request(context, context.endDayResponse, $.param(model));
			}, 10);
		})(model2, context);
	};
	Calendarista.calendar.prototype.endDayResponse = function(result){
		var onEndTimeSelectedDelegate
			, timeResetClickDelegate;
		if(this.$endTimeListbox){
			this.$endTimeListbox.off();
		}
		if(this.$endTimeReset){
			this.$endTimeReset.off();
		}
		this.$endTimeslotPlaceHolder.removeClass('hide');
		this.$endTimeslotPlaceHolder.replaceWith(result);
		this.$endTimeslotPlaceHolder = this.$root.find('.calendarista-end-timeslot-placeholder');
		this.$endTimeListbox = this.$root.find('select[name="endTime"]');
		if(this.$endTimeListbox.length === 0){
			this.$endTimeListbox = this.$root.find('select[name="endTime[]"]');
		}
		this.$endTimeReset = this.$root.find('.calendarista-endtime-reset');
		onEndTimeSelectedDelegate = Calendarista.createDelegate(this, this.onEndTimeSelected);
		this.$endTimeListbox.on('change', onEndTimeSelectedDelegate);
		timeResetClickDelegate = Calendarista.createDelegate(this, this.timeResetClick);
		this.$endTimeReset.on('click', timeResetClickDelegate);
		if(!this.validateTimeslots(this.$endTimeListbox, 1/*endtime*/)){
			this.setEndDate(null);
			this.$endDateField.val('');
			this._endDateVal = null;
			if(this.returnSameDay && !this.returnOptional){
				this.setStartDate(null);
				this.$startDateField.val('');
				this._startDateVal = null;
			}
		}
		if(([2/*SINGLE_DAY_AND_TIME_RANGE*/, 4/*MULTI_DATE_AND_TIME_RANGE*/, 8/*ROUND_TRIP_WITH_TIME*/]).indexOf(this.calendarMode) !== -1){
			//it's probably an edit, show summary-placeholder
			if(this.$endTimeListbox.length > 0 && this.$endTimeListbox[0].selectedIndex > 0){
				//we are reloading the view, so repopulate seats
				this.seatsRequest(
					[{ 'name': 'startTime', 'value': parseInt(this.$startTimeListbox.find('option:selected').val(), 10)}
					, { 'name': 'endTime', 'value': parseInt(this.$endTimeListbox.find('option:selected').val(), 10)}]
				);
				return;
			}
		}
		if(this.calendarMode === 2/*SINGLE_DAY_AND_TIME_RANGE*/){
			this.repeatRequest();
		}
	};
	Calendarista.calendar.prototype.onEndTimeSelected = function(e){
		this.adjustStartTime();
		this.validateTimeRange();
		if(window[this.callbackTimeslotSelected]){
			window[this.callbackTimeslotSelected]([this.$startTimeListbox.find('option:selected')[0], this.$endTimeListbox.find('option:selected')[0]]);
		}
		if([2/*SINGLE_DAY_AND_TIME_RANGE*/, 4/*MULTI_DATE_AND_TIME_RANGE*/, 8/*ROUND_TRIP_WITH_TIME*/].indexOf(this.calendarMode) !== -1){
			this.seatsRequest(
				[{ 'name': 'startTime', 'value': parseInt(this.$startTimeListbox.find('option:selected').val(), 10)}
				, { 'name': 'endTime', 'value': parseInt(this.$endTimeListbox.find('option:selected').val(), 10)}]
			);
		}else{
			this.costSummaryRequest(true/*queryDynamicFields*/);
		}
		if([2/*SINGLE_DAY_AND_TIME_RANGE*/].indexOf(this.calendarMode) !== -1){
			this.repeatRequest();
		}
	};
	Calendarista.calendar.prototype.adjustStartTime = function(){
		if(this.startDateSelected !== this.endDateSelected && this.calendarMode !== 2/*SINGLE_DAY_AND_TIME_RANGE*/){
			this.filterOutOfStockSlots();
			return;
		}
		var i
			, selectedIndex = (this.$endTimeListbox && this.$endTimeListbox.length > 0) ? this.$endTimeListbox[0].selectedIndex : 0
			, $startTimeOptions = (this.$startTimeListbox && this.$startTimeListbox.length > 0) ? this.$startTimeListbox.find('option') : null
			, $option;
		if($startTimeOptions){
			$startTimeOptions.prop('disabled', false);
			for(i = 0; i < $startTimeOptions.length; i++){
				$option = $($startTimeOptions[i]);
				if (i > selectedIndex && selectedIndex !== 0){
					this.disableOption($option);
				}else{
					$option.html($option.attr('data-calendarista-time'));
				}
			}
		}
		if(this.$startTimeListbox && this.$startTimeListbox.length > 0){
			this.disableTimeResetButtons(this.$startTimeListbox);
		}
		this.filterOutOfStockSlots();
	};
	Calendarista.calendar.prototype.timeResetClick = function(e){
		e.preventDefault();
		this.timeReset();
	};
	Calendarista.calendar.prototype.timeReset = function(){
		var $startTimeOptions = this.$startTimeListbox ? this.$startTimeListbox.find('option') : null
			, $endTimeOptions = this.$endTimeListbox ? this.$endTimeListbox.find('option') : null;
		if(!$startTimeOptions){
			return;
		}
		$startTimeOptions.prop('disabled', false);
		if(this.$startTimeListbox.length > 0){
			this.$startTimeListbox[0].selectedIndex = 0;
		}
		this.resetTimeslot($startTimeOptions);
		if($endTimeOptions){
			$endTimeOptions.prop('disabled', false);
			this.resetTimeslot($endTimeOptions);
			if(this.$endTimeListbox.length > 0){
				this.$endTimeListbox[0].selectedIndex = 0;
			}
		}
		this.$startTimeReset.addClass('calendarista-not-active');
		this.$startTimeReset.prop('disabled', true);
		if(this.$endTimeReset){
			this.$endTimeReset.addClass('calendarista-not-active');
			this.$endTimeReset.prop('disabled', true);
		}
		this.filterOutOfStockSlots();
	};
	Calendarista.calendar.prototype.resetTimeslot = function($options){
		if(!$options){
			return;
		}
		var i
			, $option;
		for(i = 1; i < $options.length; i++){
			$option = $($options[i]);
			$option.html($option.attr('data-calendarista-time'));
			if($option[0].hasAttribute('data-calendarista-outofstock')){
				this.disableOption($option);
			}
		}
	};
	Calendarista.calendar.prototype.disableTimeResetButtons = function($listbox){
		var enable = $listbox.find('option[disabled]').length > 0;
		if(enable){
			if(this.$startTimeReset){
				this.$startTimeReset.removeClass('calendarista-not-active');
				this.$startTimeReset.prop('disabled', false);
			}
			if(this.$endTimeReset){
				this.$endTimeReset.removeClass('calendarista-not-active');
				this.$endTimeReset.prop('disabled', false);
			}
		}else{
			if(this.$startTimeReset){
				this.$startTimeReset.addClass('calendarista-not-active');
				this.$startTimeReset.prop('disabled', true);
			}
			if(this.$endTimeReset){
				this.$endTimeReset.addClass('calendarista-not-active');
				this.$endTimeReset.prop('disabled', true);
			}
		}
	};
	Calendarista.calendar.prototype.dayDiff = function(date1, date2){
		var timeDiff;
		if((date1 instanceof Date && !isNaN(date1.valueOf())) &&
			(date2 instanceof Date && !isNaN(date2.valueOf()))){
			timeDiff = Math.abs(date2.getTime() - date1.getTime());
			return Math.ceil(timeDiff / (1000 * 3600 * 24));
		}
		return 0;
	}
	Calendarista.calendar.prototype.toYMD = function(date){
		var yyyy
			, mm
			, dd;
		if(date instanceof Date){
			yyyy = date.getFullYear().toString();
			mm = (date.getMonth()+1).toString();
			dd  = date.getDate().toString();
			return yyyy + '-' + this.pad(mm) + '-' + this.pad(dd);
		}
		return '';
	};
	Calendarista.calendar.prototype.toTime = function(date){
		var hours = date.getHours()
			, minutes = this.pad(date.getMinutes())
			, ampm = hours >= 12 ? 'pm' : 'am';
		hours = hours % 12;
		hours = hours ? hours : 12; // the hour '0' should be '12'
		return hours + ':' + minutes + ' ' + ampm;
	};
	Calendarista.calendar.prototype.parseDate = function(date){
		return $.datepicker.parseDate(this.dateFormat, date);
	};
	Calendarista.calendar.prototype.parseDateByFormat = function(format, date){
		return $.datepicker.parseDate(format, date);
	};
	Calendarista.calendar.prototype.pad = function(n){
		return ('0' + n).slice(-2);
	};
	Calendarista.calendar.prototype.setStartDate = function(date){
		var value = null;
		if(date){
			value = this.parseDate(date);
		}
		if(this.$startDate.length > 0){
			$.datepicker._setDate($.datepicker._getInst(this.$startDate[0]),  value, true);
		}
	};
	Calendarista.calendar.prototype.setEndDate = function(date){
		var value = null;
		if(date){
			value = this.parseDate(date);
		}
		if(this.$endDate.length > 0){
			$.datepicker._setDate($.datepicker._getInst(this.$endDate[0]),  value, true);
		}
	};
	Calendarista.calendar.prototype.unload = function(){
		if(this.$startTimeListbox){
			this.$startTimeListbox.off();
		}
		if(this.$endTimeListbox){
			this.$endTimeListbox.off();
		}
		if(this.$startTimeReset){
			this.$startTimeReset.off();
		}
		if(this.$endTimeReset){
			this.$endTimeReset.off();
		}
		if(this.$startDate.data("datepicker") != null){
			this.$startDate.datepicker('destroy');
		}
		if(this.$endDate.data("datepicker") != null){
			this.$endDate.datepicker('destroy');
		}
		if(this.$projectsListbox.length){
			this.$projectsListbox.off();
		}
		if(this.$availabilityListbox.length){
			this.$availabilityListbox.off();
		}
		if(this._startBookedAvailabilityList.length){
			this._startBookedAvailabilityList.length = 0;
		}
		if(this._endBookedAvailabilityList.length){
			this._endBookedAvailabilityList.length = 0;
		}
		if(this.$packageListbox.length){
			this.$packageListbox.off();
		}
		if(this.$seats){
			this.$seats.off();
		}
		if(this.$dynamicFields && this.$dynamicFields.length){
			this.$dynamicFields.off();
		}
		if(this.ajax){
			this.ajax.destroy();
		}
		if(this.unloadDelegate){
			$(window).off('unload', this.unloadDelegate);
			delete this.unloadDelegate;
		}
	};
	
})(window['jQuery'], window['Calendarista'], window['jstz'], window['calendarista_wp_ajax']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.optionals = function(options){
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.optionals.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.projectId = options['projectId'];
		this.$root = $('#' + this.id);
		this.actionCostSummary = 'calendarista_cost_summary';
		this.ajaxUrl = wp.url;
		this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': this.id});
		this.$costSummaryPlaceholder = this.$root.find('.calendarista-cost-summary-placeholder');
		this.$postbackStep = this.$root.find('input[name="postbackStep"]');
		this.$viewstate = this.$root.find('input[name="__viewstate"]');
		this.$optionals = this.$root.find('.calendarista-optional');
		this.$incrementButtonsLeftMinus = this.$root.find('.calendarista-increment-left-minus');
		this.$incrementButtonsRightPlus = this.$root.find('.calendarista-increment-right-plus');
		this.$incrementButtonsLeftMinus.on('click', Calendarista.createDelegate(this, this.incrementLeftMinusClicked));
		this.$incrementButtonsRightPlus.on('click', Calendarista.createDelegate(this, this.incrementRightPlusClicked));
		this.$optionals.on('change', Calendarista.createDelegate(this, this.optionalChanged));
		window.Parsley
		  .addValidator('groupRequired', {
			requirementType: 'string',
			validateNumber: function(value, requirement) {
				var $inputs = $(requirement)
					, i;
				for(i = 0; i < $inputs.length; i++){
					if(parseInt($inputs[i].value, 10) > 0){
						return true;
					}
				}
				return false;
			}
		});
		this.costSummary();
	};
	Calendarista.optionals.prototype.incrementLeftMinusClicked = function(e){
		e.preventDefault();
		var $currentTarget = $(e.currentTarget)
			, $inputField = $('#' + $currentTarget.attr('data-calendarista-input'))
			, min = parseInt($inputField.attr('data-calendarista-min'), 10)
			, val = parseInt($inputField.val(), 10);
		if(val > 0 && val > min){
			$inputField.val(val - 1);
			this.costSummary();
		}
		Calendarista.wizard.isValid(this.$root);
	};
	Calendarista.optionals.prototype.incrementRightPlusClicked = function(e){
		e.preventDefault();
		var $currentTarget = $(e.currentTarget)
			, $inputField = $('#' + $currentTarget.attr('data-calendarista-input'))
			, max = parseInt($inputField.attr('data-calendarista-max'), 10)
			, val = parseInt($inputField.val(), 10);
		if(val < max){
			$inputField.val(val + 1);
			this.costSummary();
		}
		Calendarista.wizard.isValid(this.$root);
	};
	Calendarista.optionals.prototype.optionalChanged = function(e){
		this.costSummary();
	};
	Calendarista.optionals.prototype.costSummary = function(){
		var i
			, optionals = this.$root.find('.calendarista-optional:checked, .calendarista-optional option:selected').map(function() {
				return this.value;
			}).get().join(',')
			, optionalIncremental = this.$root.find('input.calendarista-incremental-input')
			,  model = [{'name': 'optionals', 'value': optionals}
				, {'name': 'action', 'value': this.actionCostSummary}
				, {'name': 'projectId', 'value': this.projectId}
				, {'name': 'postbackStep', 'value': this.$postbackStep.val()}
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
				, {'name': '__viewstate', 'value': this.$viewstate.val()}];
			for(i = 0; i < optionalIncremental.length; i++){
				model.push({'name': optionalIncremental[i].id, 'value': optionalIncremental[i].value});
			}
		this.ajax.request(this, this.costSummaryResponse, $.param(model));
	};
	Calendarista.optionals.prototype.costSummaryResponse = function(result){
		this.$costSummaryPlaceholder.html(result);
	};
	Calendarista.optionals.prototype.unload = function(){
		if(this.$optionals.length){
			this.$optionals.off();
		}
		if(this.$incrementButtonsLeftMinus.length > 0){
			this.$incrementButtonsLeftMinus.off();
		}
		if(this.$incrementButtonsRightPlus.length > 0){
			this.$incrementButtonsRightPlus.off();
		}
		if(this.unloadDelegate){
			$(window).off('unload', this.unloadDelegate);
			delete this.unloadDelegate;
		}
	};
	
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.repeatAppointment = function(options){
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.repeatAppointment.prototype.init = function(options){
		var context = this
			, repeatFrequency;
		this.id = options['id'];
		this.$root = $('#calendarista_' + this.id);
		this.ajaxUrl = wp.url;
		this.everyDaySummary =  options['everyDaySummary'];
		this.everyWeekOn =  options['everyWeekOn'];
		this.everyMonth = options['everyMonth'];
		this.everyYear = options['everyYear'];
		this.everyDayOfTheWeek = options['everyDayOfTheWeek'];
		this.occurrenceTimes = options['occurrenceTimes'];
		this.until = options['until'];
		this.justOnce = options['justOnce'];
		this.su =  options['su'];
		this.mo =  options['mo'];
		this.tu =  options['tu'];
		this.we =  options['we'];
		this.th =  options['th'];
		this.fr =  options['fr'];
		this.sa =  options['sa'];
		this.availability = options['availabilityId'];
		this.$repeatModeSummary = this.$root.find('#repeatModeSummary');
		this.$repeat = this.$root.find('input[name="repeatAppointment"]');
		this.$repeatOptions = this.$root.find('#calendarista_' + this.id + '_repeat_options');
		this.$repeatFrequencySelectList = this.$root.find('select[name="repeatFrequency"]');
		this.$repeatIntervalSelectList = this.$root.find('select[name="repeatInterval"]');
		this.$terminateAfterOccurrenceSelectList = this.$root.find('select[name="terminateAfterOccurrence"]');
		this.$repeatIntervalRow = this.$root.find('#calendarista_' + this.id + '_repeatIntervalRow');
		this.$repeatWeekdayListRow = this.$root.find('#calendarista_' + this.id + '_repeatWeekdayListRow');
		this.$repeatWeekdayCheckboxList = this.$repeatWeekdayListRow.find('input[type="checkbox"]');
		this.$repeatFrequencyInput = this.$root.find('input[name="repeatFrequency"]');
		this.$repeatIntervalInput = this.$root.find('input[name="repeatInterval"]');
		this.$terminateAfterOccurrenceInput = this.$root.find('input[name="terminateAfterOccurrence"]');
		this.$summary = this.$root.find('#calendarista_' + this.id + '_repeat_summary'); 
		this.$startDateField = this.$root.find('input[name="availableDate"]');
		this.costSummaryRequest = 'calendarista_cost_summary_request' + options['id'];
		this.actionGetRepeatDates = 'calendarista_get_repeat_dates';
		this.repeatChangedDelegate = Calendarista.createDelegate(this, this.repeatChanged);
		this.$repeat.on('change', this.repeatChangedDelegate);
		this.$repeatWeekdayCheckboxList.on('change', function(e){
			context.summaryRequest(e);
		});
		this.$repeatIntervalSelectList.on('change', function(e){
			context.summaryRequest(e);
		});
		this.$terminateAfterOccurrenceSelectList.on('change', function(e){
			context.summaryRequest(e);
		});
		this.repeatFrequencyChangedDelegate = Calendarista.createDelegate(this, this.repeatFrequencyChanged);
		this.$repeatFrequencySelectList.on('change', this.repeatFrequencyChangedDelegate);
		repeatFrequency = parseInt(this.$repeatFrequencySelectList.val(), 10);
		this.$repeatWeekdayListRow.hide();
		this.$repeatWeekdayCheckboxList.prop('disabled', true);
		if(repeatFrequency === 5){
			//Weekly
			this.$repeatWeekdayListRow.show();
			this.$repeatWeekdayCheckboxList.prop('disabled', false);
		}
		this.repeatChanged();
	};
	Calendarista.repeatAppointment.prototype.repeatChanged = function(e){
		var repeatAppointment = this.$repeat.is(':checked')
			, changed = e ? true : false;
		if(repeatAppointment){
			this.$repeatOptions.show();
		}else{
			this.$repeatOptions.hide();
		}
		this.summaryRequest(changed);
	};
	Calendarista.repeatAppointment.prototype.summaryRequest = function(changed){
		var model = [];
		if(window[this.costSummaryRequest]){
			if(changed){
				model = [
					{ 'name': 'repeatAppointmentChanged', 'value': 1 }
					, {'name': 'resetRepeat', 'value':  1}
					, { 'name': 'calendarista_nonce', 'value': wp.nonce }
				];
			}
			window[this.costSummaryRequest](model);
		}
	};
	Calendarista.repeatAppointment.prototype.getRepeatDatesResponse = function($result){
		this.$summary.html($result);
	};
	Calendarista.repeatAppointment.prototype.mutuallyExclusive = function($a, selector){
		$('input[name="' + selector + '"]:checked').each(function(){
			var $b = $(this);
			if($a.val() === $b.val()){
				$b.prop('checked', false);
			}
		});
	};
	Calendarista.repeatAppointment.prototype.repeatFrequencyChanged = function(e){
		var value = parseInt(this.$repeatFrequencySelectList.val(), 10)
			, summary = ''
			, interval = parseInt(this.$repeatIntervalSelectList.val(), 10);
		this.$repeatWeekdayListRow.hide();
		this.$repeatWeekdayCheckboxList.prop('disabled', true);
		switch(value){
			case 1:
			//daily
			summary = this.everyDaySummary.replace('%s', interval);
			break;
			case 5:
			//Weekly
			this.$repeatWeekdayListRow.show();
			this.$repeatWeekdayCheckboxList.prop('disabled', false);
			summary = this.getSelectedWeekdaySummary();
			break;
			case 6:
			//Monthly
			summary = this.everyMonth.replace('%s', interval);
			break;
			case 7:
			//Yearly
			summary = this.everyYear.replace('%s', interval);
			break;
		}
		this.setSummary();
		this.summaryRequest(e);
	};
	Calendarista.repeatAppointment.prototype.getSelectedWeekdaySummary = function(){
		var i
			, val
			, values = []
			, weekdayName
			, $checkedList = this.$repeatWeekdayListRow.find('input:checked')
			, interval = parseInt(this.$repeatIntervalSelectList.val(), 10);;
		for(i = 0; i < $checkedList.length; i++){
			val = parseInt($($checkedList[i]).val(), 10);
			switch(val){
				case 7:
				weekdayName = this.su;
				break;
				case 1:
				weekdayName = this.mo;
				break;
				case 2:
				weekdayName = this.tu;
				break;
				case 3:
				weekdayName = this.we;
				break;
				case 4:
				weekdayName = this.th;
				break;
				case 5:
				weekdayName = this.fr;
				break;
				case 6:
				weekdayName = this.sa;
				break;
			}
			values.push(weekdayName);
		}
		if(values.length === 7){
			return this.everyDayOfTheWeek.replace('%s', interval);
		}
		return this.everyWeekOn.replace('%s', interval).replace('%s', values.join(','));
	};
	Calendarista.repeatAppointment.prototype.setSummary = function(){
		var value = parseInt(this.$repeatFrequencySelectList.val(), 10)
			, summary = ''
			, interval = parseInt(this.$repeatIntervalSelectList.val(), 10)
			, occurrence = parseInt(this.$terminateAfterOccurrenceSelectList.val(), 10);
		switch(value){
			case 1:
			//daily
			summary = this.everyDaySummary.replace('%s', interval);
			break;
			case 5:
			//Weekly
			summary = this.getSelectedWeekdaySummary();
			break;
			case 6:
			//Monthly
			summary = this.everyMonth.replace('%s', interval);
			break;
			case 7:
			//Yearly
			summary = this.everyYear.replace('%s', interval);
			break;
		}
		if(!isNaN(occurrence) && occurrence !== 0){
			 if(occurrence === 1){
				 summary = this.justOnce;
			 }
			else{
				summary += ', ' + this.occurrenceTimes.replace('%s', this.$terminateAfterOccurrenceSelectList.val());
			}
		}
		this.$summary.html(summary);
	};
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.checkout = function(options){
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.checkout.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.projectId = options['projectId'];
		this.ajaxUrl = wp.url;
		this.$root = $('#' + this.id);
		this.actionCostSummary = 'calendarista_cost_summary';
		this.actionValidateCoupon = 'calendarista_coupon_validator';
		this.wooCommerceAction = 'calendarista_woocommerce_submit';
		this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': this.id});
		this.$bookNowButton = this.$root.find('button[name="booknow"]');
		this.$costSummaryPlaceholder = this.$root.find('.calendarista-cost-summary-placeholder');
		this.$stagingId = this.$root.find('input[name="stagingId"]');
		this.$viewstate = this.$root.find('input[name="__viewstate"]');
		this.$paymentType = this.$root.find('input[name="paymentType"]');
		this.creditCardSection = this.$root.find('#collapse_creditcard_' + this.projectId);
		this.$coupon = this.$root.find('input[name="coupon"]');
		this.couponMinimumAmountError = this.$coupon.attr('data-calendarista-coupon-minimum-amount-error');
		this.couponInvalidError = this.$coupon.attr('data-calendarista-coupon-invalid-error');
		this.$couponButton = this.$root.find('button[name="couponButton"]');
		this.$couponResetButton = this.$root.find('button[name="couponResetButton"]');
		this.$paymentOperatorForms = this.$root.find('form[data-calendarista-payment-operator]');
		this.$panels = this.$root.find('.calendarista-collapsable');
		if(this.$coupon.length > 0){
			this.$coupon.parsley().on('field:success', Calendarista.createDelegate(this, this.removeCouponError));
		}
		this.$couponButton.on('click', Calendarista.createDelegate(this, this.couponButtonClicked));
		this.$couponResetButton.on('click', Calendarista.createDelegate(this, this.resetCouponClicked));
		this.$paymentType.on('change', Calendarista.createDelegate(this, this.paymentTypeChanged));
	};
	Calendarista.checkout.prototype.resetCouponClicked = function(e){
		var $paymentMethod;
		if(this.$coupon.val()){
			this.$coupon.val('');
		}
		this.resetTotal();
		this.costSummaryRequest();
		this.removeCouponError();
		$paymentMethod = this.$root.find('input[name="paymentMethod"]');
		if($paymentMethod.is(':disabled')){
			$paymentMethod.prop('disabled', false);
			$($paymentMethod[0]).prop('checked', true);
			this.paymentLabelStatus($paymentMethod, 'show');
		}
		//this.paymentMethodChanged($($paymentMethod[0]));
	};
	Calendarista.checkout.prototype.removeCouponError = function(){
		this.$coupon.parsley().removeError('couponerror', {updateClass: true});
	};
	Calendarista.checkout.prototype.couponButtonClicked = function(e){
		var coupon = this.$coupon.val()
			, result;
		this.removeCouponError();
		result = this.$coupon.parsley ? this.$coupon.parsley().validate() : true;
		if(result !== null && (typeof(result) === 'object' && result.length > 0)){
			this.costSummaryRequest();
			return;
		}
		this.couponValidator();
	};
	Calendarista.checkout.prototype.couponValidator = function(){
		var model = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'coupon', 'value': this.$coupon.val() }
				, { 'name': 'action', 'value': this.actionValidateCoupon }
				, { 'name': 'total', 'value': this.$root.find('input[name="totalAmountBeforeDiscount"]').val() }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
			];
		this.ajax.request(this, this.validationResponse, $.param(model));
	};
	Calendarista.checkout.prototype.validationResponse = function(result){
		var response = window['JSON'].parse(result)
			, $paymentMethod = this.$root.find('input[name="paymentMethod"]');
		if(!response['isValid'] && response['orderMinimum']){
			this.$coupon.parsley().addError('couponerror', {message: this.couponMinimumAmountError.replace('%s', response['orderMinimum']), updateClass: true});
		}else if(!response['isValid']){
			this.$coupon.parsley().addError('couponerror', {message: this.couponInvalidError, updateClass: true});
		}
		if(response['isValid'] && response['fullDiscount']){
			$paymentMethod.prop('checked', false);
			$paymentMethod.prop('disabled', true);
			this.$panels.calendaristaCollapse('hide');
			if($paymentMethod.length === 1){
				this.paymentLabelStatus($paymentMethod, 'hide');
			}
		}else{
			this.paymentLabelStatus($paymentMethod, 'show');
		}
		this.updateTotal(response);
		this.costSummaryRequest();
	};
	Calendarista.checkout.prototype.paymentLabelStatus = function($paymentMethodItems, action){
		var i
			, $paymentMethod
			, $label; 
		for (i = 0; i < $paymentMethodItems.length;i++){
			$paymentMethod = $($paymentMethodItems[i]);
			this.paymentMethodChanged($paymentMethod, action);
			$label = this.$root.find('label[for="' + $paymentMethod.prop('id') + '"]');
			if(action === 'hide'){
				$label.addClass('hide');
			}else if($label.hasClass('hide')){
				$label.removeClass('hide');
			}
		}
	};
	Calendarista.checkout.prototype.paymentMethodChanged = function($paymentMethod, action){
		var hasInlineForm = $paymentMethod.attr('data-calendarista-inline-form')
			, $form = this.$root.find($paymentMethod.val())
			, $panel = this.$root.find($form.attr('data-calendarista-operator-panel'));
		if($panel.hasClass('in') && action === 'hide'){
			$panel.calendaristaCollapse('hide');
			return;
		}
		if(!$panel.hasClass('in') && ($paymentMethod.prop('checked') && hasInlineForm)){
			$panel.calendaristaCollapse('show');
		}
	}
	Calendarista.checkout.prototype.updateTotal = function(response){
		var i
			, $form
			, fieldName
			, operatorName
			, total;
		for(i = 0; i < this.$paymentOperatorForms.length; i++){
			$form = $(this.$paymentOperatorForms[i]);
			fieldName = $form.attr('data-calendarista-total-name');
			operatorName = $form.attr('data-calendarista-payment-operator');
			total = (operatorName === 'stripe' && response['totalCents']) ? response['totalCents'] : response['total'];
			$form.find('input[name="' + fieldName + '"]').val(total);
		}
	};
	Calendarista.checkout.prototype.resetTotal = function(){
		var i
			, $form
			, fieldName
			, total;
		for(i = 0; i < this.$paymentOperatorForms.length; i++){
			$form = $(this.$paymentOperatorForms[i]);
			fieldName = $form.attr('data-calendarista-total-name');
			total = $form.attr('data-calendarista-original-total');
			$form.find('input[name="' + fieldName + '"]').val(total);
		}
	};
	Calendarista.checkout.prototype.costSummaryRequest = function(){
		var model = [
				{ 'name': 'coupon', 'value': this.$coupon.val() }
				, { 'name': 'postbackStep', 'value': 'checkout' }
				, { 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'stagingId', 'value': this.$stagingId.val() }
				, { 'name': '__viewstate', 'value': this.$viewstate.val() }
				, { 'name': 'action', 'value': this.actionCostSummary }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
			];
		this.ajax.request(this, this.costSummaryResponse, $.param(model));
	};
	Calendarista.checkout.prototype.costSummaryResponse = function(result){
		this.$costSummaryPlaceholder.html(result);
	};
	Calendarista.checkout.prototype.costSummaryReset = function(){
		this.$costSummaryPlaceholder.html('');
	};
	Calendarista.checkout.prototype.wooCommerceSubmit = function($form){
		var model = $form.serializeArray();
		model.push({ 'name': 'action', 'value': this.wooCommerceAction });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		this.ajax.request(this, this.wooCommerceResponse, $.param(model));
	};
	Calendarista.checkout.prototype.wooCommerceResponse = function(result){
		var response
			, checkoutMode;
		if(result){
			response = window['JSON'].parse(result);
		}
		if(response && response['result']){
			checkoutMode = parseInt(response['checkoutMode'], 10);
			window.location.href = checkoutMode === 0 ? response['checkoutUrl'] : response['cartUrl'] ;
		}else{
			this.$bookNowButton.prop('disabled', false).removeClass('ui-state-disabled');
		}
	};
	Calendarista.checkout.prototype.paymentTypeChanged = function(e){
		var paymentMode = parseInt($(e.target).val(), 10);
		if(paymentMode !== 1/*creditcard*/){
			this.creditCardSection.calendaristaCollapse('hide');
		}
	};
	Calendarista.checkout.prototype.unload = function(){
		if(this.unloadDelegate){
			$(window).off('unload', this.unloadDelegate);
			delete this.unloadDelegate;
		}
		this.$couponButton.off();
		this.$paymentType.off();
	};
	
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.fullcalendar = function(options){
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.fullcalendar.prototype.init = function(options){
		var context = this;
		this.fullcalendarId = options['fullcalendarId'];
		this.actionPublicFeed = 'calendarista_appointments_public_feed';
		this.ajaxUrl = wp.url;
		this.$spinner = $('#' + options['spinnerId']);
		this.projectList = options['projectList'];
		this.view = options['view'];
		this.formElementList = options['formElementList'];
		this.status = options['status'];
		this.includeNameField = options['includeNameField'];
		this.includeEmailField = options['includeEmailField'];
		this.includeAvailabilityNameField = options['includeAvailabilityNameField'];
		this.includeSeats = options['includeSeats'];
		this.firstDayOfWeek = options['firstDayOfWeek'];
		this.locale = options['locale'];
		this.defaultView = 'dayGridMonth';
		this.mobileView = 'listWeek';
		if(['week', 'list'].indexOf(this.view) !== -1){
			this.defaultView = 'listWeek';
		}else if(this.view === 'day'){
			this.defaultView = 'dayGrid';
			this.mobileView = 'dayGrid';
		}else if(['month', 'calendar'].indexOf(this.view) !== -1){
			this.defaultView = 'dayGridMonth';
		}
		this.fc = new window['FullCalendar'].Calendar($('#' + this.fullcalendarId)[0], {
			plugins: ['dayGrid', 'list'],
			defaultView: context.mobileCheck() ? context.mobileView : context.defaultView,
			locale: context.locale,
			firstDay: context.firstDayOfWeek,
			themeSystem: '',
			header: {
				left: ''
				, center: 'title'
			},
			titleFormat:{
				year: 'numeric',
				month: 'short',
				day: 'numeric' 
		   },
			views: {
				dayGridMonth: { 
					eventTimeFormat: {
						hour: '2-digit',
						minute: '2-digit',
						hour12: false
					}
				}
			},
			windowResize: function(view){
				if (context.mobileCheck()) {
					if(context.defaultView === 'listWeek' || context.defaultView === 'dayGridMonth'){
						context.fc.changeView('listWeek');
					}
				} else {
					context.fc.changeView(context.defaultView);
				}
			},
			editable: false,
			eventLimit: true, // allow "more" link when too many events
			//ToDO: we need to pass projectid list & formid list
			eventSources: [{
				url: context.ajaxUrl
				, method: 'POST'
				, extraParams: function() {
					var result = {
						'action': context.actionPublicFeed
						, 'projectList': context.projectList
						, 'formElementList': context.formElementList
						, 'includeNameField': context.includeNameField
						, 'includeEmailField': context.includeEmailField
						, 'includeAvailabilityNameField': context.includeAvailabilityNameField
						, 'includeSeats': context.includeSeats
						, 'availabilityId': -1
						, 'syncDataFilter': 1
						, 'status': context.status
						, 'calendarista_nonce': wp.nonce
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
						$element1.html(info.event.title);
					}
				}
			}, 
			eventClick: function(info) {
			},
			eventPositioned: function(info){
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
	};
	Calendarista.fullcalendar.prototype.mobileCheck = function() {
		if(this.defaultView == 'listWeek'){
			//force list always, because we want a list period.
			return true;
		}
		if (window.innerWidth >= 768 ) {
			return false;
		} else {
			return true;
		}
	};
	Calendarista.fullcalendar.prototype.showSpinner = function(){
		this.$spinner.removeClass('calendarista-invisible');
	};
	Calendarista.fullcalendar.prototype.hideSpinner = function(){
		this.$spinner.addClass('calendarista-invisible');
	};
	Calendarista.fullcalendar.prototype.getDate = function(element){
		var date;
		try {
			date = $.datepicker.parseDate('yy-mm-dd', element.value);
		}catch(error){
			date = null;
		}
		return date;
	};
	Calendarista.fullcalendar.prototype.unload = function(){
		if(this.unloadDelegate){
			$(window).off('unload', this.unloadDelegate);
			delete this.unloadDelegate;
		}
	};
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
(function($, Calendarista, jstz, wp){
	"use strict";
	Calendarista.search = function(options){
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.search.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.actionSearch = 'calendarista_search';
		this.$root = $('#search_' + this.id);
		this.$searchResultPlaceHolder = this.$root.find('#search_result_' + this.id);
		this.ajaxUrl = wp.url;
		this.spinnerId = options['spinnerId'];
		this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': this.spinnerId});
		this.$projectList = $('#' + options['projectList']);
		this.projectListInclusion = options['projectListInclusion'];
		this.$searchStartDate = $('#' + options['searchStartDate']);
		this.$searchStartTime = $('#' + options['searchStartTime']);
		this.$searchEndDate = $('#' + options['searchEndDate']);
		this.$searchEndTime = $('#' + options['searchEndTime']);
		this.$searchButton = $('#' + options['searchButton']);
		this.dateFormat = options['dateFormat'] ? options['dateFormat'] : 'DD, d MM, yy';
		this.firstDayOfWeek = options['firstDayOfWeek'];
		this.resultPageUrl = options['resultPageUrl'];
		this.clearLabel = options['clearLabel'];
		this.onSearchButtonClickDelegate = Calendarista.createDelegate(this, this.onSearchButtonClick);
		this.$searchButton.on('click', this.onSearchButtonClickDelegate);
		this.createCalendars();
		this.pagerButtonDelegates();
	};
	Calendarista.search.prototype.onSearchButtonClick = function(e){
		this.searchRequest([]);
	};
	Calendarista.search.prototype.searchRequest = function(model){
		var  projectId = this.$projectList.val() ? parseInt(this.$projectList.val(), 10) : null
			, startDate = this.$searchStartDate.val() ? this.toYMD(this.parseDate(this.$searchStartDate.val())) : null
			, startTime = this.$searchStartTime.length > 0 ? this.$searchStartTime.val() : null
			, endDate = this.$searchEndDate.val() ? this.toYMD(this.parseDate(this.$searchEndDate.val())) : null
			, endTime = this.$searchEndTime.length > 0 ? this.$searchEndTime.val() : null
			, projectList = ((this.$projectList.length === 0 || !this.$projectList.val()) && this.projectListInclusion) ? this.projectListInclusion : null
			, $tags = this.$root.find('input[name="tags"]:checked')
			, tags = []
			, redirectUrl
			, timezone = jstz.determine().name();
		$.each($tags, function(){
			tags.push($(this).val());
		});
		model.push({ 'name': 'projectId', 'value': projectId });
		model.push({ 'name': 'projectList', 'value': projectList });
		model.push({ 'name': 'fromDate', 'value': startDate });
		model.push({ 'name': 'fromTime', 'value': startTime });
		model.push({ 'name': 'toDate', 'value': endDate });
		model.push({ 'name': 'toTime', 'value': endTime });
		model.push({ 'name': 'tags', 'value': tags });
		model.push({ 'name': 'timezone', 'value': timezone });
		model.push({ 'name': 'clientTime', 'value': this.toTime(new Date()) });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		if(this.resultPageUrl){
			model.push({ 'name': 'search-result-inline', 'value': true });
			if(this.resultPageUrl.indexOf('?') === -1){
				this.resultPageUrl += '?';
			}
			redirectUrl = this.resultPageUrl + $.param(model); 
			this.$searchButton.prop('href', redirectUrl);
			return;
		}
		model.push({ 'name': 'action', 'value': this.actionSearch });
		this.ajax.request(this, this.searchResponse, $.param(model));
	};
	Calendarista.search.prototype.searchResponse = function(result){
		if(this.$nextPage){
			this.$nextPage.off();
		}
		if(this.$lastPage){
			this.$lastPage.off();
		}
		if(this.$prevPage){
			this.$prevPage.off();
		}
		if(this.$firstPage){
			this.$firstPage.off();
		}
		this.$searchResultPlaceHolder.html(result);
		this.pagerButtonDelegates();
	};
	Calendarista.search.prototype.createCalendars = function(){
		var today = new Date()
			, context = this;
		this.onStartDateSelectDelegate = Calendarista.createDelegate(this, this.onStartDateSelect);
		this.onEndDateSelectDelegate = Calendarista.createDelegate(this, this.onEndDateSelect);
		this.onStartDateCloseDelegate = Calendarista.createDelegate(this, this.onStartDateClose);
		this.onEndDateCloseDelegate = Calendarista.createDelegate(this, this.onEndDateClose);
		this.$searchStartDate.datepicker({
			'dateFormat': this.dateFormat
			, 'firstDay': this.firstDayOfWeek
			, 'defaultDate': today
			, 'minDate': today
			, 'onSelect': this.onStartDateSelectDelegate
			, 'onClose': this.onStartDateCloseDelegate
			, 'showButtonPanel': true
            , 'closeText': this.clearLabel
		});
		this.$searchEndDate.datepicker({
			'dateFormat': this.dateFormat
			, 'firstDay': this.firstDayOfWeek
			, 'defaultDate': today
			, 'minDate': today
			, 'onSelect': this.onEndDateSelectDelegate
			, 'onClose': this.onEndDateCloseDelegate
			, 'showButtonPanel': true
            , 'closeText': this.clearLabel
		});
		this.$searchStartDate.datepicker().datepicker("setDate", today.setDate(today.getDate()));
		//this.$searchEndDate.datepicker().datepicker("setDate", today.setDate(today.getDate() + 6));
		this.$datepickerElement = $('#ui-datepicker-div');
		this.createUniqueCalendar();
	};
	Calendarista.search.prototype.onStartDateSelect = function(dateText) {
		
	};
	Calendarista.search.prototype.onEndDateSelect = function(dateText) {
		
	};
	Calendarista.search.prototype.onStartDateClose = function(selectedDate, args){
		var context = this
			, event = window.event || arguments.callee.caller.caller.caller.arguments[0]
			, target = event.currentTarget || event.delegateTarget
			, currentDate = new Date();
		if(selectedDate){
			currentDate = this.parseDate(selectedDate);
		}
		if(selectedDate){
			this.$searchStartDate.parsley().reset();
		}
		if (target && $(target).hasClass('ui-datepicker-close')) {
			this.datepickerReset();
			return;
		}
		if(this.startDateSelected !== selectedDate){
			this.startDateSelected = selectedDate;
			if(this.$searchEndDate && this.$searchEndDate.length > 0){
				this.$searchEndDate.datepicker('option', 'minDate',  this.parseDate(selectedDate));
			}
		}
	};
	Calendarista.search.prototype.onEndDateClose = function(selectedDate, args){
		var context = this
			, result
			, event = window.event || arguments.callee.caller.caller.caller.arguments[0]
			, target = event.currentTarget || event.delegateTarget
			, lastSelectedDate
			, currentDate = new Date();
		if(selectedDate){
			currentDate = this.parseDate(selectedDate);
		}
		if(selectedDate){
			this.$searchEndDate.parsley().reset();
		}
		if (target && $(target).hasClass('ui-datepicker-close')) {
			this.datepickerReset();
			return;
		}
		if(this.endDateSelected !== selectedDate){
			this.endDateSelected = selectedDate;
			this.$searchStartDate.datepicker('option', 'maxDate', this.parseDate(selectedDate));
		}
	};
	Calendarista.search.prototype.pagerButtonDelegates = function(){
		var context = this;
		this.$nextPage = this.$searchResultPlaceHolder.find('a.calendarista-next-page');
		this.$lastPage = this.$searchResultPlaceHolder.find('a.calendarista-last-page');
		this.$prevPage = this.$searchResultPlaceHolder.find('a.calendarista-prev-page');
		this.$firstPage = this.$searchResultPlaceHolder.find('a.calendarista-first-page');
		this.$nextPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
		this.$lastPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
		this.$prevPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
		this.$firstPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
	};
	Calendarista.search.prototype.gotoPage = function(e){
		var pagedValue = parseInt($(e.currentTarget).attr('data-calendarista-paged'), 10)
			, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
		this.$nextPage.off();
		this.$lastPage.off();
		this.$prevPage.off();
		this.$firstPage.off();
		this.searchRequest(model);
	};
	Calendarista.search.prototype.datepickerReset = function(){
		var context = this
			, today = new Date();
		this.$searchStartDate.datepicker('setDate', null);
		if(this.$searchEndDate.length > 0){
			this.$searchEndDate.datepicker('setDate', null);
		}
		this.$searchStartDate.datepicker('option', 'minDate', today);
		this.$searchStartDate.datepicker('option', 'maxDate', null);
		this.$searchEndDate.datepicker('option', 'minDate', today);
		this.startDateSelected = null;
		this.endDateSelected = null;
	};
	Calendarista.search.prototype.createUniqueCalendar = function(){
		var className1 = 'calendarista-calendar-search'
			, className2 = ' calendarista-flat calendarista-borderless'
			, attrName = 'data-calendarista-classname';
		if(this.$datepickerElement.hasClass(className1)){
			return;
		}
		this.$datepickerElement.removeClass(this.$datepickerElement.attr(attrName) + className2);
		this.$datepickerElement.removeAttr(attrName);
		this.$datepickerElement.attr(attrName, className1);
		this.$datepickerElement.addClass(className1 + className2);
		this.$datepickerElement.addClass('calendarista-datepicker');
	};
	Calendarista.search.prototype.parseDate = function(date){
		return $.datepicker.parseDate(this.dateFormat, date);
	};
	Calendarista.search.prototype.pad = function(n){
		return ('0' + n).slice(-2);
	};
	Calendarista.search.prototype.toYMD = function(date){
		var yyyy = date.getFullYear().toString()
			, mm = (date.getMonth()+1).toString()
			, dd  = date.getDate().toString();
		return yyyy + '-' + this.pad(mm) + '-' + this.pad(dd);
	};
	Calendarista.search.prototype.toTime = function(date){
		var hours = date.getHours()
			, minutes = this.pad(date.getMinutes())
			, ampm = hours >= 12 ? 'pm' : 'am';
		hours = hours % 12;
		hours = hours ? hours : 12; // the hour '0' should be '12'
		return hours + ':' + minutes + ' ' + ampm;
	};
	Calendarista.search.prototype.unload = function(){
		if(this.unloadDelegate){
			$(window).off('unload', this.unloadDelegate);
			delete this.unloadDelegate;
		}
	};
})(window['jQuery'], window['Calendarista'], window['jstz'], window['calendarista_wp_ajax']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.customForm = function(options){
		this.init(options);
		this.unloadDelegate = Calendarista.createDelegate(this, this.unload);
		$(window).on('unload', this.unloadDelegate);
	};
	Calendarista.customForm.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.projectId = options['projectId'];
		this.actionCreateUser = 'calendarista_create_user';
		this.actionSignOn = 'calendarista_signon';
		this.actionCustomTypeChanged = 'calendarista_customer_type_changed';
		this.ajaxUrl = wp.url;
		this.membershipRequired = options['membershipRequired'];
		this.$root = $('#' + this.id);
		this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': this.id});
		this.$viewState = this.$root.find('input[name="__viewstate"]');
		this.$customerTypeContainer = this.$root.find('.customer-type-container');
		this.$customerType = this.$root.find('input[name="customerType"]');
		this.$phoneNumberFields = this.$root.find('.calendarista-phone');
		this.$email = this.$root.find('input[name="email"]');
		this.$name = this.$root.find('input[name="name"]');
		this.$password = this.$root.find('input[name="password"]');
		this.$createUserValidator = this.$root.find('input[name="createUserValidator"]');
		this.$signOnValidator = this.$root.find('input[name="signOnValidator"]');
		this.$nextButton = this.$root.find('button[name="next"]');
		this.$form = this.$root.find('form');
		this.$customerType.on('change', Calendarista.createDelegate(this, this.customerTypeChanged));
		this.nextButtonClickDelegate = Calendarista.createDelegate(this, this.nextButtonClicked);
		this.$nextButton.on('click', this.nextButtonClickDelegate);
		this.phoneNumberKeyUpDelegate = Calendarista.createDelegate(this, this.phoneNumberKeyUp);
		this.$phoneNumberFields.on('keyup', this.phoneNumberKeyUpDelegate);
		this.$container = this.$root.find('.calendarista-custom-form');
		if(!window.Parsley.hasValidator('calendaristaPhone')){
			window.Parsley.addValidator('calendaristaPhone', {
				validateString: function(value, countryCode, parsleyInstance) {
					if(!window['libphonenumber']){
						return true;
					}
					return window['libphonenumber'].isValidNumber(value, countryCode);
				}
			});
		}
	};
	Calendarista.customForm.prototype.phoneNumberKeyUp = function(e){
		var $currentTarget = $(e.currentTarget)
			, countryCode = $currentTarget.attr('data-parsley-calendarista-phone')
			, val = $currentTarget.val()
			, result;
		if (window['libphonenumber'] && ((e.keyCode != 46 && e.keyCode != 8) || 
				window['libphonenumber'].isValidNumber(val, countryCode))) {
			result = new window['libphonenumber'].AsYouType(countryCode).input(val);
			$currentTarget.val(result);
		}
	};
	Calendarista.customForm.prototype.nextButtonClicked = function(e){
		var model
			, customerType = parseInt(this.$customerType.filter(':checked').val(), 10);
		if(!Calendarista.wizard.isValid(this.$root)){
			return;
		}
		if(this.membershipRequired){
			model = [
				{ 'name': 'projectId', 'value': this.projectId }
				, { 'name': 'name', 'value': this.$name.val() }
				, { 'name': 'email', 'value': this.$email.val() }
				, { 'name': 'password', 'value': this.$password.val() }
				, { 'name': 'calendarista_nonce', 'value': wp.nonce }
			];
			if(customerType === 0 && !this.$customerTypeContainer.hasClass('hide')){
				this.$createUserValidator.val('');
				model.push({ 'name': 'action', 'value': this.actionCreateUser });
				this.ajax.request(this, this.createUserResponse, $.param(model));
			}else if(customerType === 1){
				this.$signOnValidator.val('');
				model.push({ 'name': 'action', 'value': this.actionSignOn });
				this.ajax.request(this, this.signOnResponse, $.param(model));
			}
		}
		this.unload();
	};
	Calendarista.customForm.prototype.createUserResponse = function(result){
		var $createUserError = this.$root.find('.create-user-error')
			, data;
		if(result === 'null'){
			Calendarista.wizard.isValid(this.$root);//force validation
			$createUserError.removeClass('hide');
		}
		this.$createUserValidator.val(1);
		if(result !== 'null'){
			data = window['JSON'].parse(result);
			window['calendarista_wp_ajax']['nonce'] = data.nonce;
			$createUserError.addClass('hide');
			this.$customerTypeContainer.addClass('hide');
			this.$nextButton.click();
		}
	};
	Calendarista.customForm.prototype.signOnResponse = function(result){
		var $signOnError = this.$root.find('.signon-error')
			, data;
		if(result === 'null'){
			Calendarista.wizard.isValid(this.$root);//force validation
			$signOnError.removeClass('hide');
		}
		this.$signOnValidator.val(1);
		if(result !== 'null'){
			data = window['JSON'].parse(result);
			window['calendarista_wp_ajax']['nonce'] = data.nonce;
			$signOnError.addClass('hide');
			this.$customerType.filter('[value="0"]').prop('checked', true);
			this.customerTypeRequest();
			this.$customerTypeContainer.addClass('hide');
		}
	};
	Calendarista.customForm.prototype.customerTypeChanged = function(e){
		this.customerTypeRequest();
	};
	Calendarista.customForm.prototype.customerTypeRequest = function(){
		var value = parseInt(this.$customerType.filter(':checked').val(), 10)
			, model = this.$form.serializeArray();
		model.push({ 'name': 'customerType', 'value': value });
		model.push({ 'name': 'projectId', 'value': this.projectId});
		model.push({ 'name': 'selectedStep', 'value': 3/*form*/ });
		model.push({ 'name': '__viewstate', 'value': this.$viewState.val() });
		model.push({ 'name': 'action', 'value': this.actionCustomTypeChanged });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		this.ajax.request(this, this.customerTypeChangedResponse, $.param(model));
	};
	Calendarista.customForm.prototype.customerTypeChangedResponse = function(result){
		this.$container.html(result);
		this.$email = this.$root.find('input[name="email"]');
		this.$name = this.$root.find('input[name="name"]');
		this.$password = this.$root.find('input[name="password"]');
	};
	Calendarista.customForm.prototype.unload = function(){
		if(this.unloadDelegate){
			$(window).off('unload', this.unloadDelegate);
			delete this.unloadDelegate;
		}
		this.$customerType.off();
		this.$root.off('click', 'button[name="next"]', this.nextButtonClickDelegate);
	};
	
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
(function($, Calendarista, wp){
	"use strict";
	Calendarista.userprofile = function(options){
		this.init(options);
		this.destroyDelegate = Calendarista.createDelegate(this, this.destroy);
		$(window).on('unload', this.destroyDelegate);
	};
	Calendarista.userprofile.prototype.init = function(options){
		var context = this;
		this.id = options['id'];
		this.userprofileAction = 'calendarista_user_profile';
		this.actionEditAppointment = 'calendarista_user_edit_appointment';
		this.actionUpdateAppointment = 'calendarista_user_update_appointment';
		this.ajaxUrl = wp.url;
		this.enableEdit = options['enableEdit'];
		this.editPolicy = options['editPolicy'];
		this.pluginDir = options['pluginDir'];
		this.userProfileAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'userprofile'});
		this.editAppointmentAjax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'edit_appointment'});
		this.$root = $('#' + this.id);
		this.$container = this.$root.find('.calendarista-userprofile-placeholder');
		this.$editAppointmentPlaceHolder = this.$root.find('#calendarista-edit-appointment-placeholder');
		this.initAjaxElements();
		this.pagerButtonDelegates();
		if(!this.$editAppointmentsModalDialog && this.enableEdit){
			this.createEditDialog();
		}
	};
	Calendarista.userprofile.prototype.initAjaxElements = function(){
		var i
			, $tabItem
			, $option
			, index
			, context = this;
		this.$form = this.$root.find('form');
		this.$tabItemLinks = this.$root.find('.nav-item a');
		this.selectedIndex = parseInt(this.$root.find('.nav-item a.nav-link.active').attr('data-calendarista-index'), 10);
		this.$navTabs = this.$root.find('.nav.nav-tabs');
		this.$dropdownList = this.$root.find('#dropdown_' + this.id);
		this.$dropdownListOptions = this.$dropdownList.find('option');
		this.windowResizeDelegate = Calendarista.createDelegate(this, this.windowResize);
		$(window).on('resize', this.windowResizeDelegate);
		this.$tabItemLinks.on('click', function(e){
			var $target = $(e.currentTarget);
			e.preventDefault();
			context.activeTab($target);
			context.pagedValue = null;
			context.selectedIndex = parseInt($target.attr('data-calendarista-index'), 10);
			if(!$target.prop('disabled')){
				context.request(context.selectedIndex);
			}
			return false;
		});
		this.$dropdownList.on('change', function(e){
			var $target = $(e.currentTarget).find(':selected');
			context.selectedIndex = parseInt($target.attr('data-calendarista-index'), 10);
			context.request(context.selectedIndex);
		});
		this.mobileCheck();
		this.request(this.selectedIndex);
	};
	Calendarista.userprofile.prototype.activeTab = function($target){
		var i
			, $tabItem;
		for(i = 0; i < this.$tabItemLinks.length; i++){
			$tabItem = $(this.$tabItemLinks[i]);
			$tabItem.removeClass('active');
		}
		$target.addClass('active');
	};
	Calendarista.userprofile.prototype.windowResize = function(){
		this.mobileCheck();
	};
	Calendarista.userprofile.prototype.mobileCheck = function() {
		if (this.appointment !== 1 && window.innerWidth >= 768 ) {
			this.$navTabs.removeClass('hide');
			this.$dropdownList.addClass('hide');
		} else {
			this.$dropdownList.removeClass('hide');
			this.$navTabs.addClass('hide');
		}
	};
	Calendarista.userprofile.isValid = function($root, sel){
		var isValid = true
			, $validators;
		if(!sel){
			sel = '.calendarista_parsley_validated, .woald_parsley_validated';
		}
		$validators	= $root.find(sel);
		$validators.each(function(){
			var $elem = $(this),
				result;
			if ($elem.prop('type') !== 'hidden' && (!$elem.is(':visible') || $elem.is(':disabled') || $elem.attr('data-parsley-excluded'))){
				return true;
			}
			$elem.parsley().reset();
			result = $elem.parsley ? $elem.parsley().validate() : true;
			if(result !== null && (typeof(result) === 'object' && result.length > 0)){
				isValid = false;
			}
		});
		return isValid;
	};
	Calendarista.userprofile.prototype.request = function(selectedIndex, params){
		var model = this.$form.serializeArray();
		model.push({ 'name': 'selectedIndex', 'value': selectedIndex });
		model.push({ 'name': 'action', 'value': this.userprofileAction });
		model.push({ 'name': 'enableEdit', 'value': this.enableEdit });
		model.push({ 'name': 'editPolicy', 'value': this.editPolicy });
		model.push({ 'name': 'calendarista_nonce', 'value': wp.nonce });
		if(this.pagedValue){
			model.push({ 'name': 'paged', 'value': this.pagedValue });
		}
		if(params){
			model = model.concat(params);
		}
		this.userProfileAjax.request(this, this.response, $.param(model));
	};
	Calendarista.userprofile.prototype.response = function(result){
		var context = this;
		this.$viewmoreButtons = this.$container.find('.calendarista-viewmore');
		if(this.$viewmoreButtons.length > 0){
			this.$viewmoreButtons.off();
		}
		this.$cancelButtons = this.$container.find('button[name="bookedAvailabilityId"]');
		this.$editButtons = this.$container.find('button[name="editAppointment"]');
		if(this.$cancelButtons.length > 0){
			this.$cancelButtons.off();
		}
		if(this.$nextPage){
			this.$nextPage.off();
		}
		if(this.$lastPage){
			this.$lastPage.off();
		}
		if(this.$prevPage){
			this.$prevPage.off();
		}
		if(this.$firstPage){
			this.$firstPage.off();
		}
		this.$container.replaceWith(result);
		this.$container = this.$root.find('.calendarista-userprofile-placeholder');
		this.$viewmoreButtons = this.$container.find('.calendarista-viewmore');
		this.$cancelButtons = this.$container.find('button[name="bookedAvailabilityId"]');
		this.$editButtons = this.$container.find('button[name="editAppointment"]');
		this.$viewmoreButtons.on('click', function(e){
			var $elem = $(this)
				, query = $elem.attr('data-bs-target')
				, $collapsePanel = $(query)
				, $i = $elem.find('i');
			if($i.hasClass('fa-plus')){
				$i.removeClass('fa-plus');
				$i.addClass('fa-minus');
			}else{
				$i.removeClass('fa-minus');
				$i.addClass('fa-plus');
			}
			$collapsePanel.calendaristaCollapse('toggle');
		});
		this.$cancelButtons.on('click', function(e){
			var id = parseInt($(this).val(), 10)
				, orderId = parseInt($('#order_id_' + id).val(), 10)
				, model = [
					{ 'name': 'bookedAvailabilityId', 'value': id }
					, { 'name': 'orderId', 'value': orderId }
				];
			context.request(context.selectedIndex, model);
		});
		this.$editButtons.on('click', function(e){
			var $elem = $(this)
				, orderId = parseInt($elem.val(), 10)
				, projectId = parseInt($elem.attr('data-calendarista-project-id'), 10)
				, availabilityId = parseInt($elem.attr('data-calendarista-availability-id'), 10)
				, bookedAvailabilityId = parseInt($elem.attr('data-calendarista-booked-availability-id'), 10)
				, model = [
					{ 'name': 'projectId', 'value': projectId }
					, { 'name': 'availabilityId', 'value': availabilityId}
					, { 'name': 'orderId', 'value': orderId}
					, { 'name': 'editMode', 'value': 0 }
					, { 'name': 'appointment', 'value': 1}
					, { 'name': 'bookedAvailabilityId', 'value': bookedAvailabilityId}
					, { 'name': 'action', 'value': context.actionEditAppointment }
					, { 'name': 'calendarista_nonce', 'value': wp.nonce }
				];
			context.$editAppointmentsModalDialog.dialog('widget').find('.ui-dialog-buttonset').addClass('calendarista_' + projectId);
			context.$editAppointmentsModalDialog.dialog('open');
			context.editAppointmentAjax.request(context, context.editAppointmentResponse, $.param(model));
		});
		this.pagerButtonDelegates();
		this.scrollTop();
	};
	Calendarista.userprofile.prototype.editAppointmentResponse = function(result){
		this.$editAppointmentPlaceHolder.replaceWith('<div id="calendarista-edit-appointment-placeholder">' + result + '</div>');
		this.$editAppointmentPlaceHolder = $('#calendarista-edit-appointment-placeholder');
	};
	Calendarista.userprofile.prototype.updateAppointmentResponse = function(result){
		this.$editAppointmentsModalDialog.dialog('close');
		this.$editAppointmentPlaceHolder.empty();
		this.request(this.selectedIndex);
	};
	Calendarista.userprofile.prototype.createEditDialog = function(){
		var context = this;
		this.$editAppointmentPlaceHolder = this.$root.find('#calendarista-edit-appointment-placeholder');
		this.$editAppointmentsModalDialog = this.$root.find('#calendarista-edit-appointments-modal').dialog({
			autoOpen: false
			, height: '480'
			, width: '640'
			, modal: true
			, resizable: false
			, dialogClass: 'calendarista-dialog calendarista-user-edit-appointment-dialog'
			, closeOnEscape: false
			, open: function(event, ui) {
				$('.ui-dialog-titlebar-close', ui.dialog | ui).hide();
			}
			, create: function() {
				var spinner = '<span id="spinner_edit_appointment" style="margin-right: 10px" class="calendarista-spinner calendarista-invisible">';
					spinner += '<img src="' + context.pluginDir + 'assets/img/transparent.gif">';
					spinner += '</span>';
				$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
			}
			, buttons: [
				{
					'text': 'Update'
					, 'name': 'updateAppointment'
					, 'click':  function(){
						var model
							, $form = context.$editAppointmentsModalDialog.find('form');
						if(!Calendarista.wizard.isValid($form)){
							return false;
						}
						context.$editAppointmentsModalDialog.find('input[name="controller"]').val('calendarista_appointments');
						model = $form.serialize();
						model += '&calendarista_nonce=' + wp.nonce + '&appointment=1&editMode=1&calendarista_update=1&action=' + context.actionUpdateAppointment;
						context.editAppointmentAjax.request(context, context.updateAppointmentResponse, model);
					}
				}
				, {
					'text': 'Exit'
					, 'name': 'dispose'
					, 'click':  function(){
						context.$editAppointmentsModalDialog.dialog('close');
						context.$editAppointmentPlaceHolder.empty();
						context.request(context.selectedIndex);
					}
				}
			]
		});
	};
	Calendarista.userprofile.prototype.pagerButtonDelegates = function(){
		var context = this;
		this.$nextPage = this.$container.find('a.calendarista-next-page');
		this.$lastPage = this.$container.find('a.calendarista-last-page');
		this.$prevPage = this.$container.find('a.calendarista-prev-page');
		this.$firstPage = this.$container.find('a.calendarista-first-page');
		this.$nextPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
		this.$lastPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
		this.$prevPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
		this.$firstPage.on('click', function(e){
			e.preventDefault();
			context.gotoPage(e);
			return false;
		});
	};
	Calendarista.userprofile.prototype.gotoPage = function(e){
		this.pagedValue = parseInt($(e.currentTarget).attr('data-calendarista-paged'), 10);
		this.$nextPage.off();
		this.$lastPage.off();
		this.$prevPage.off();
		this.$firstPage.off();
		this.request(this.selectedIndex);
	};
	Calendarista.userprofile.prototype.scrollTop = function(){
		var $elem1 = $('#navbar_' + this.id)
			, $elem2 = $('#dropdown_' + this.id)
			, $elem = !$elem1.hasClass('hide')  ? $elem1 : $elem2;
		if($elem.length > 0){
			$elem[0].scrollIntoView({ 'block': 'center', 'behaviour': 'smooth' });
		}
	};
	Calendarista.userprofile.prototype.destroy = function(){
		var $elems, i;
		if(this.$tabItemLinks){
			this.$tabItemLinks.off();
		}
		if(this.$dropdownList){
			this.$dropdownList.off();
		}
		if(this.$viewmoreButtons.length > 0){
			this.$viewmoreButtons.off();
		}
		if(this.$cancelButtons.length > 0){
			this.$cancelButtons.off();
		}
		if(this.$editButtons.length > 0){
			this.$editButtons.off();
		}
		delete this.$tabItemLinks;
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
	
})(window['jQuery'], window['Calendarista'], window['calendarista_wp_ajax']);
