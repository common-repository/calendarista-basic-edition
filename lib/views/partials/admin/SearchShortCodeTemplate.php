<?php
class Calendarista_SearchShortCodeTemplate extends Calendarista_ViewBase{
	public $url;
	public $tagList;
	public $pages;
	public $searchTimeslots;
	public $timeslotsCreated;
	function __construct(){
		parent::__construct(false, true);
		new Calendarista_AutogenTimeslotsController(
			null
			, null
			, null
			, null
			, array($this, 'searchTimeslotsCreated')
		);
		$this->tagList = new Calendarista_TagByAvailabilityList();
		$this->tagList->bind();
		$this->pages = Calendarista_GeneralSettingsTemplate::getPages();
		$repo = new Calendarista_GeneralSettingsRepository();
		$generalSetting = $repo->read();
		$this->searchTimeslots = $generalSetting->searchTimeslots;
		$this->render();
	}
	public function searchTimeslotsCreated($result){
	?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p>
				<?php esc_html_e('A fresh set of timeslots have been generated.', 'calendarista'); ?>
			</p>
		</div>
	<?php 
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<div class="column-pane">
						<div id="shortcode_gen"> 
							<h3><?php esc_html_e('Services', 'calendarista') ?></h3>
							<p class="description"><?php esc_html_e('All services are queried and included in the search result if no item is checked below', 'calendarista') ?></p>
							<?php if($this->projects->count() > 0): ?>
							<div style="overflow: auto; height: 200px; width: 100%">
								<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post"> 
									<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
									<table class="wp-list-table calendarista wp-list-table widefat fixed striped">
										<thead></thead>
										<tbody>
										<?php foreach($this->projects as $project):?>
										<tr>
											<td>
												<div><input type="checkbox" name="projects" value="<?php echo $project->id ?>">&nbsp;<?php echo esc_html($project->name) ?></div>
											</td>
										</tr>
										<?php endforeach;?>
										</tbody>
									</table>
								</form>
							</div>
							<?php else: ?>
							<p class="description"><?php esc_html_e('Please create at least one service first', 'calendarista') ?></p>
							<?php endif; ?>
						</div>
					</div>
					<div class="column-pane">
						<div id="tags">
							<h3><?php esc_html_e('Search attributes', 'calendarista') ?></h3>
							<p class="description"><?php esc_html_e('Search attributes to display on the search form', 'calendarista') ?></p>
							<?php if($this->tagList->count > 0): ?>
							<div>
								<span id="spinner_update_tag_list" class="calendarista-spinner calendarista-invisible">
									<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
								</span>
							</div>
							<div id="calendarista_tag_list"  class="table-responsive">
								<?php $this->tagList->printVariables() ?>
								<?php $this->tagList->display(); ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="column-pane">
						<p class="description">
							<?php esc_html_e('If your search has "include time selection", the slots below will be used. Auto generate slots to customize.', 'calendarista') ?>
						</p>
						<div>
							<button type="button" name="autogenTimeslots" 
							class="button button-primary"><?php esc_html_e('Autogenerate Timeslots', 'calendarista') ?></button>
						</div>
						<div class="autogen-timeslots-modal calendarista" 
							title="<?php esc_html_e('Autogenerate timeslots', 'calendarista') ?>">
							<div class="autogen_timeslots_placeholder"></div>
							<div id="spinner_timeslots" class="calendarista-spinner calendarista-invisible">
								<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading dialog...', 'calendarista') ?>
							</div>
						</div>
						<hr>
						<table class="widefat">
							<caption><strong><?php esc_html_e('Search page time slots', 'calendarista') ;?></strong></caption>
							<tbody>
								<?php foreach($this->searchTimeslots as $slot): ?>
								<tr>
									<td><?php echo esc_html($slot['text']) ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right">
			<div id="widgets-right">
				<div class="wrap">
					<div class="column-pane">
						<div id="shortcode">
							<p>
								<input type="checkbox" name="includeService"><?php esc_html_e('Include service selection', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="includeTime"><?php esc_html_e('Include time selection', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="excludeEndDate"><?php esc_html_e('Exclude end date selection', 'calendarista') ?>
							</p>
							<p>
								<input type="checkbox" name="excludeEndDateTime"><?php esc_html_e('Exclude both end date and time selection', 'calendarista') ?>
							</p>
							<?php if(count($this->pages) > 0): ?>
							<p>
								<select name="target">
									<option value=""><?php esc_html_e('Display results on the same page', 'calendarista') ?></option>
									<?php foreach($this->pages as $page): ?>
									<option value="<?php echo $page['id'] ?>"><?php echo esc_html($this->trimString($page['name'], 32)) ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<?php endif; ?>
							<h3><?php esc_html_e('Result', 'calendarista') ?></h3>
							<div>
								<p class="description"><?php esc_html_e('The short-code to use on your search page.', 'calendarista') ?></p>
								<textarea id="shortcode_search" style="width: 100%" rows="5" readonly></textarea>
								<p class="description"><?php esc_html_e('If the results are to be displayed on a separate page, insert the following short-code as well.', 'calendarista') ?></p>
								<textarea id="shortcode_result" style="width: 100%" rows="5" readonly></textarea>
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
				calendarista.search = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.search.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.actionGetTagList = 'calendarista_get_tag_list';
					this.actionAutogenSearchTimeslot = 'calendarista_autogen_search_timeslots';
					this.$projectList = $('input[name="projects"]');
					this.$output1 = $('#shortcode_search');
					this.$output2 = $('#shortcode_result');
					this.$includeService = $('input[name="includeService"]');
					this.$includeTime = $('input[name="includeTime"]');
					this.$excludeEndDate = $('input[name="excludeEndDate"]');
					this.$excludeEndDateTime = $('input[name="excludeEndDateTime"]');
					this.$targetPage = $('select[name="target"]');
					this.$tagCheckboxes = $('input[name="tags[]"]');
					this.$tagAllCheck = $('input[name="selectall"]');
					this.$autogenTimeslotsPlaceHolder = $('.autogen_timeslots_placeholder');
					this.$autogenTimeslotsButton = $('button[name="autogenTimeslots"]');
					this.targetPageChangedDelegate = calendarista.createDelegate(this, this.targetPageChanged);
					this.$targetPage.on('change', this.targetPageChangedDelegate);
					this.checkedAllDelegate = calendarista.createDelegate(this, this.checkedAll);
					this.$tagCheckboxes.on('change', this.checkedAllDelegate);
					this.tagCheckAllDelegate = calendarista.createDelegate(this, this.tagsCheckall);
					this.$tagAllCheck.on('change', this.tagCheckAllDelegate);
					this.$tagList = $('#calendarista_tag_list');
					this.ajax1 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'update_tag_list'});
					this.ajax2 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'timeslots'});
					this.pagerButtonDelegates();
					this.$projectList.on('change', function(){
						context.shortcodeOutput1();
						context.shortcodeOutput2();
					});
					this.$includeService.on('change', function(){
						context.shortcodeOutput1();
						context.shortcodeOutput2();
					});
					this.$includeTime.on('change', function(){
						context.shortcodeOutput1();
						context.shortcodeOutput2();
					});
					this.$excludeEndDate.on('change', function(){
						context.shortcodeOutput1();
						context.shortcodeOutput2();
					});
					this.$excludeEndDateTime.on('change', function(){
						context.shortcodeOutput1();
						context.shortcodeOutput2();
					});
					this.$autogenTimeslotsButton.on('click', function(e){
						var model = [{ 'name': 'action', 'value': context.actionAutogenSearchTimeslot }
									, { 'name': 'calendarista_nonce', 'value': context.nonce }];
						context.$autogenTimeslotsModalDialog.dialog('open');
						context.ajax2.request(context, context.autogenTimeslotResponse, $.param(model));
					});
					this.$autogenTimeslotsModalDialog = $('.autogen-timeslots-modal').dialog({
						autoOpen: false
						, height: '480'
						, width: '640'
						, modal: true
						, resizable: false
						, dialogClass: 'calendarista-dialog'
						, buttons: [
							{
								'text': 'Create'
								, 'name': 'create'
								, 'click':  function(e){
									var $target = $(e.currentTarget)
										, $form = context.$autogenTimeslotsModalDialog.dialog('widget').find('form');
									if(!Calendarista.wizard.isValid($form)){
										e.preventDefault();
										return false;
									}
									$dialog = $('<p title="<?php echo $this->decodeString(__('Auto generate timeslots', 'calendarista')) ?>"><?php echo $this->decodeString(__('If there are existing timeslots, these will be replaced. Are you sure?', 'calendarista')); ?></p>').dialog({
										dialogClass: 'calendarista-dialog'
										, buttons: {
											'Yes': function() {
												$form.append('<input type="hidden" name="calendarista_create_for_search" />');
												$form.submit();
											}
											, 'Cancel':  function() {
												$dialog.dialog('close');
											}
										}
									});
								}
							}
							, {
								'text': 'Close'
								, 'click':  function(){
									context.$autogenTimeslotsModalDialog.dialog('close');
								}
							}
						]
					});
					this.shortcodeOutput1();
					this.shortcodeOutput2();
				};
				calendarista.search.prototype.autogenTimeslotResponse = function(result){
					var $day;
					this.$autogenTimeslotsPlaceHolder.replaceWith('<div class="autogen_timeslots_placeholder">' + result + '</div>');
					this.$autogenTimeslotsPlaceHolder = $('.autogen_timeslots_placeholder');
					$day = this.$autogenTimeslotsPlaceHolder.find('input[name="day"]');
					$day.datepicker('destroy');
					$day.removeClass('hasDatepicker').removeProp('id');
					$day.datetimepicker(this.dateTimepickerOptions);
					this.initializeTimepickerFields(this.$autogenTimeslotsPlaceHolder);
				};
				calendarista.search.prototype.initializeTimepickerFields = function($root){
					var context = this;
					this.$timeslotTextbox = $root.find('input[name="timeslot"]');
					this.$startIntervalTextbox = $root.find('input[name="startInterval"]');
					this.$timeSplitTextbox = $root.find('input[name="timeSplit"]');
					this.$endTimeTextbox = $root.find('input[name="endTime"]');
					this.$timeslotTextbox.timepicker({'timeFormat': 'h:mm tt'});
					this.$startIntervalTextbox.timepicker({'hour': 0});
					this.$endTimeTextbox.timepicker({'hour': 0});
					this.$timeSplitTextbox.timepicker();
				};
				calendarista.search.prototype.targetPageChanged = function(e){
					this.shortcodeOutput1();
					this.shortcodeOutput2();
				};
				calendarista.search.prototype.shortcodeOutput1 = function(){
					var projectListCheckedValues = $('input[name="projects"]:checked').map(function () {
							return this.value;
						}).get()
						, tags = this.getTags()
						, output = '[calendarista-search';
					//reset
					this.$output1.val('');
					if(projectListCheckedValues.length > 0 && projectListCheckedValues.indexOf('-1')){
						output += ' id="' + projectListCheckedValues.join(',') + '"';
					}
					if(this.$includeService.is(':checked')){
						output += ' service="true"';
					}
					if(this.$includeTime.is(':checked')){
						output += ' time="true"';
					}
					if(this.$excludeEndDate.is(':checked')){
						output += ' exclude-end-date="true"';
					}
					if(this.$excludeEndDateTime.is(':checked')){
						output += ' exclude-end-date-time="true"';
					}
					if(tags.length > 0){
						output += ' filter-attr="' + tags.join(',') + '"';
					}
					if(this.$targetPage.val()){
						output += ' result-page="' + this.$targetPage.val() + '"';
					}
					if(output){
						output += ']';
					}
					this.$output1.val(output);
					return projectListCheckedValues;
				};
				calendarista.search.prototype.shortcodeOutput2 = function(){
					var projectListCheckedValues = $('input[name="projects"]:checked').map(function () {
							return this.value;
						}).get()
						, tags = this.getTags()
						, output = '[calendarista-search-result';
					//reset
					this.$output2.val('');
					if(projectListCheckedValues.length > 0 && projectListCheckedValues.indexOf('-1')){
						output += ' id="' + projectListCheckedValues.join(',') + '"';
					}
					if(this.$includeService.is(':checked')){
						output += ' service="true"';
					}
					if(this.$includeTime.is(':checked')){
						output += ' time="true"';
					}
					if(this.$excludeEndDate.is(':checked')){
						output += ' exclude-end-date="true"';
					}
					if(this.$excludeEndDateTime.is(':checked')){
						output += ' exclude-end-date-time="true"';
					}
					if(tags.length > 0){
						output += ' filter-attr="' + tags.join(',') + '"';
					}
					if(output){
						output += ']';
					}
					this.$output2.val(output);
					return projectListCheckedValues;
				};
				calendarista.search.prototype.getTags = function(){
					var currentSelection = $('input[name="tags[]"]:checked').map(function(){
							return this.value;
					}).get()
					, i
					, j;
					if(!this.tags){
						this.tags = currentSelection;
						return this.tags;
					}
					for(i = 0; i < currentSelection.length; i++){
						for(j = 0; j < this.tags.length; j++){
							if(currentSelection[i] == this.tags[j]){
								currentSelection.splice(i, 1);
							}
						}
					}
					this.tags = this.tags.concat(currentSelection);
					return this.tags;
				};
				calendarista.search.prototype.checkedAll = function(e){
					var $elem
						, i
						, currentSelection = $('input[name="tags[]"]:checked').map(function(){
							return this.value;
						}).get()
						, context = this;
					if(e){
						$elem = $(e.currentTarget);
						if(!$elem.is(':checked') && this.tags){
							i = this.tags.indexOf($elem.val());
							if(i !== -1){
								this.tags.splice(i, 1);
							}
						}
					}else if((this.tags && this.tags.length > 0) && currentSelection.length === 0){
						$.each(this.$tagCheckboxes, function(i, val){
							if(context.tags.indexOf(val.value) !== -1){
								context.tags.splice(context.tags.indexOf(val.value), 1);
							}
						});
					}
					this.shortcodeOutput1();
				};
				calendarista.search.prototype.tagsCheckall = function(e){
					var target = e.currentTarget;
					if(target.checked){
						this.$tagCheckboxes.prop('checked', true);
					}else{
						this.$tagCheckboxes.prop('checked', false);
					}
					this.checkedAll(null);
				};
				calendarista.search.prototype.tagListRequest = function(cleanUrl, values){
				var paged = $('input[name="paged"]').val()
					, orderby = $('input[name="orderby"]').val()
					, order = $('input[name="order"]').val()
					, url = window.location.pathname + window.location.search
					, model = [
						{ 'name': 'current_url', 'value': url }
						, { 'name': 'action', 'value': this.actionGetTagList }
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
				this.ajax1.request(this, this.tagListResponse, $.param(model));
			};
			calendarista.search.prototype.tagListResponse = function(result){
				var context = this;
				this.$tagAllCheck.off();
				this.$tagList.replaceWith('<div id="calendarista_tag_list">' + result + '</div>');
				this.$tagList = $('#calendarista_tag_list');
				this.$tagAllCheck = $('input[name="selectall"]');
				this.$tagAllCheck.on('change', this.tagCheckAllDelegate);
				this.$tagCheckboxes = $('input[name="tags[]"]');
				this.$tagCheckboxes.on('change', this.checkedAllDelegate);
				if(this.tags && this.tags.length > 0){
					$.each(this.tags, function(i1, val1){
						$.each(context.$tagCheckboxes, function(i2, val2){
							if(val1 == val2.value){
								$(val2).prop('checked', true);
							}
						});
					});
				}
				this.pagerButtonDelegates();
			};
			calendarista.search.prototype.pagerButtonDelegates = function(){
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
			calendarista.search.prototype.gotoPage = function(e){
				var pagedValue = this.getUrlParameter('paged', $(e.currentTarget).prop('href'))
					, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
				this.$nextPage.off();
				this.$lastPage.off();
				this.$prevPage.off();
				this.$firstPage.off();
				this.tagListRequest(false, model);
				e.preventDefault();
				return false;
			};
			calendarista.search.prototype.removeURLParameter = function(parameter) {
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
			calendarista.search.prototype.getUrlParameter = function(param, url) {
				var regex, results;
				param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
				regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
				results = regex.exec(url);
				return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.search({
			'requestUrl': '<?php echo $_SERVER["REQUEST_URI"] ?>'
		});
		</script>
		<?php
	}
}