<?php
class Calendarista_AvailabilityDayTemplate extends Calendarista_ViewBase{
	public $availabilityDayList;
	public $projectId;
	public $availabilityId;
	function __construct($projectId, $availabilityId){
		parent::__construct(false, true);
		$this->projectId = $projectId;
		$this->availabilityId = $availabilityId;
		$this->availabilityDayList = new Calendarista_AvailabilityDayList($this->availabilityId);
		$this->availabilityDayList->bind();
		$this->render();
	}
	public function deletedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The search attribute(s) have been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
	<div id="availability_day_container">
		<div class="wrap">
			<form action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-excluded="[disabled=disabled]">
				<input type="hidden" name="controller" value="availability_day" />
				<input type="hidden" name="projectId" value="<?php echo $this->projectId ?>" />
				<input type="hidden" name="availabilityId" value="<?php echo $this->availabilityId ?>" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<tr>
							<td>
								<label for="name"><?php esc_html_e('Date', 'calendarista') ?></label>
									<input id="individualDay" 
										name="individualDay" 
										type="text" 
										class="medium-text enable-readonly-input" 
										data-parsley-required="true"
										readonly />
										<input type="button" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Add', 'calendarista') ?>">
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<div class="wrap">
			<form action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="availability_day" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<div>
					<span id="spinner_get_availability_day_list" class="calendarista-spinner calendarista-invisible">
						<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
					</span>
				</div>
				<div id="calendarista_availability_day_list"  class="table-responsive">
					<?php $this->availabilityDayList->display(); ?>
				</div>
				<p>
					<button type="button" name="calendarista_delete" class="button button-primary" disabled>
						<?php esc_html_e('Delete', 'calendarista') ?>
					</button>
				</p>
			</form>
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
			calendarista.availabilityDay = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.availabilityDay.prototype.init = function(options){
				var context = this;
				this.dateTimepickerOptions = {
					'showHour': false
					, 'showMinute': false
					, 'showTime': false
					, 'alwaysSetTime': false
					, 'dateFormat': 'yy-mm-dd'
					, 'minDate': 0
				};
				this.actionCreateAvailabilityDay = 'create_availability_day';
				this.actionGetAvailabilityDayList = 'get_availability_day_list';
				this.actionDeleteAvailabilityDay = 'delete_availability_day';
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.ajax1 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'get_availability_day_list'});
				this.$root = $('#availability_day_container');
				this.$dayTextbox = this.$root.find('input[name="individualDay"]');
				this.$dayCheckboxes = this.$root.find('input[name="individualDay[]"]');
				this.$deleteButton = this.$root.find('button[name="calendarista_delete"]');
				this.$dayAllCheck = this.$root.find('input[name="selectall"]');
				this.$availabilityDayList = $('#calendarista_availability_day_list');
				this.$addButton = this.$root.find('input[name="calendarista_create"]');
				this.callbackDelegate =  calendarista.createDelegate(this, this.callback);
				this.listPager = new Calendarista.listPager({'id': '#calendarista_availability_day_list', 'callback': this.callbackDelegate});
				this.checkedAllDelegate = calendarista.createDelegate(this, this.checkedAll);
				this.$dayCheckboxes.on('change', this.checkedAllDelegate);
				this.dayCheckAllDelegate = calendarista.createDelegate(this, this.dayCheckall);
				this.$dayAllCheck.on('change', this.dayCheckAllDelegate);
				this.$dayTextbox.datetimepicker(this.dateTimepickerOptions);
				this.$dayTextbox.on('change', function(e){
					var d = context.$dayTextbox.val();
				});
				this.addNewDateDelegate = calendarista.createDelegate(this, this.addNewDate);
				this.$addButton.on('click', this.addNewDateDelegate);
				this.deleteDateDelegate = calendarista.createDelegate(this, this.deleteDate);
				this.$deleteButton.on('click', this.deleteDateDelegate);
				this.listPager.pagerButtonDelegates();
			};
			calendarista.availabilityDay.prototype.addNewDate = function(e){
				var individualDay = this.$dayTextbox.val()
					, url = window.location.pathname + window.location.search
					, model = [
					{ 'name': 'projectId', 'value':  <?php echo $this->projectId ?> }
					, { 'name': 'availabilityId', 'value':  <?php echo $this->availabilityId ?> }
					, { 'name': 'individualDay', 'value':  individualDay }
					, { 'name': 'controller', 'value': 'availability_day' }
					, { 'name': 'current_url', 'value': url }
					, { 'name': 'action', 'value': this.actionCreateAvailabilityDay }
					, { 'name': 'calendarista_nonce', 'value': this.nonce }];
				this.ajax1.request(this, this.availabilityDayListResponse, $.param(model));
			};
			calendarista.availabilityDay.prototype.deleteDate = function(e){
				var id = $('input[name="individualDay[]"]:checked').map( function () {
							return $(this).val();
						}).get().join()
					, url = window.location.pathname + window.location.search
					, model = [
					{ 'name': 'projectId', 'value':  <?php echo $this->projectId ?> }
					, { 'name': 'availabilityId', 'value':  <?php echo $this->availabilityId ?> }
					, { 'name': 'id', 'value':  id }
					, { 'name': 'controller', 'value': 'availability_day' }
					, { 'name': 'current_url', 'value': url }
					, { 'name': 'action', 'value': this.actionDeleteAvailabilityDay }
					, { 'name': 'calendarista_nonce', 'value': this.nonce }];
				this.ajax1.request(this, this.availabilityDayListResponse, $.param(model));
			};
			calendarista.availabilityDay.prototype.checkedAll = function(){
				var hasChecked = this.$dayCheckboxes.is(':checked');
				if(hasChecked){
					this.$deleteButton.prop('disabled', false);
				}else{
					this.$deleteButton.prop('disabled', true);
				}
			};
			calendarista.availabilityDay.prototype.dayCheckall = function(e){
				var target = e.currentTarget;
				if(target.checked){
					this.$dayCheckboxes.prop('checked', true);
				}else{
					this.$dayCheckboxes.prop('checked', false);
				}
				this.checkedAll();
			};
			calendarista.availabilityDay.prototype.callback = function(values){
				this.availabilityDayListRequest(false, values);
			};
			calendarista.availabilityDay.prototype.availabilityDayListRequest = function(cleanUrl, values){
				var paged = this.$availabilityDayList.find('input[name="paged"]').val()
					, orderby = this.$availabilityDayList.find('input[name="orderby"]').val()
					, order = this.$availabilityDayList.find('input[name="order"]').val()
					, availabilityId = <?php echo $this->availabilityId ?>
					, url = window.location.pathname + window.location.search
					, model = [
						{ 'name': 'availabilityId', 'value': availabilityId }
						, { 'name': 'current_url', 'value': url }
						, { 'name': 'action', 'value': this.actionGetAvailabilityDayList }
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
				this.ajax1.request(this, this.availabilityDayListResponse, $.param(model));
			};
			calendarista.availabilityDay.prototype.availabilityDayListResponse = function(result){
				var context = this;
				this.$dayAllCheck.off();
				this.$availabilityDayList.replaceWith('<div id="calendarista_availability_day_list">' + result + '</div>');
				this.$availabilityDayList = this.$root.find('#calendarista_availability_day_list');
				this.$dayAllCheck = this.$root.find('input[name="selectall"]');
				this.$dayAllCheck.on('change', this.dayCheckAllDelegate);
				this.$dayCheckboxes = this.$root.find('input[name="individualDay[]"]');
				this.$dayCheckboxes.on('change', this.checkedAllDelegate);
				this.listPager.pagerButtonDelegates();
			};
		window['calendarista'] = calendarista;
	})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.availabilityDay({
			<?php echo $this->requestUrl ?>'
			, 'id': '#availability_day_container'
		});
	</script>
	<?php
	}
}