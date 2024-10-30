<?php
class Calendarista_CouponsTemplate extends Calendarista_ViewBase{
	public $couponsList;
	public $currentUrl;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-coupons');
		$this->deleteCouponController();
		$this->couponsList = new Calendarista_CouponsList();
		$this->couponsList->bind();
		$this->currentUrl = esc_url(add_query_arg(array(
			'page'=>'calendarista-settings',
			'calendarista-tab'=>3,
		), 'admin.php'));
		$this->render();
	}
	public function deleteCouponController(){
		if (!(array_key_exists('deleteAllCoupon', $_POST))){
			return;
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$couponRepository = new Calendarista_CouponRepository();
		$result = $couponRepository->deleteAll();
		$this->couponsDeletedNotification($result);
	}
	function couponsDeletedNotification($result){
		?>
		<div class="settings error notice is-dismissible">
			<p><?php echo sprintf(__('%d coupons have been deleted successfully', 'calendarista'), $result); ?></p>
		</div>
		<?php
	}
	function emptyNotification(){
		?>
		<div class="settings error notice is-dismissible">
			<p><?php esc_html_e('There are currently no coupons in the system', 'calendarista'); ?></p>
		</div>
		<?php
	}
	
	public function render(){
	?>
		<div class="wrap" id="coupons_filter">
			<div class="column-pane">
				<form data-parsley-validate="">
					<input type="hidden" name="page" value="calendarista-settings">
					<input type="hidden" name="calendarista-tab" value="3">
					<table>
					<tr>
					<td>
					<?php $this->renderProjectSelectList(true, __('Select a services', 'calendarista')) ?>
					</td>
					<td>
					<select 
								id="couponType" 
								name="couponType">
								<option value="" selected=selected><?php esc_html_e('Any coupon type', 'calendarista') ?></option>
								<option value="0">Regular</option>
								<option value="1">Super</option>
							</select>
					</td>
					<td>
					| <input type="text" id="searchByCode" name="searchByCode" placeholder="Coupon code" />
					</td>
					<td>
					| <?php esc_html_e('Discount', 'calendarista') ?>&nbsp;: <input type="text" 
									id="discount" 
									name="discount" 
									class="small-text calendarista_parsley_validated" 
									data-parsley-pattern="^\d+(\.\d{1,2})?$"
									data-parsley-min="0.1"
									data-parsley-max="100"
									data-parsley-trigger="change" >
					</td>
					</tr>
					</table>
					<p>
					<button type="button" id="filterButton" class="button button-primary">
						<?php esc_html_e('Filter results', 'calendarista') ?>
					</button>
					&nbsp;
					<button type="button" id="filterResetButton" class="button button-primary">
						<?php esc_html_e('Reset', 'calendarista') ?>
					</button>
					</p>
				</form>
				<div id="spinner_staff_filter" class="calendarista-spinner calendarista-invisible">
					<br>
					<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading availabilities...', 'calendarista') ?>
				</div>
			</div>
		</div>
		<div class="email-coupon-modal calendarista" 
				title="<?php esc_html_e('Email coupon', 'calendarista') ?>">
				<div class="email_coupon_placeholder"></div>
		</div>
		<div class="create-coupon-modal calendarista" 
				title="<?php esc_html_e('Coupons', 'calendarista') ?>">
				<div class="create_coupon_placeholder"></div>
		</div>
		<div class="wrap">
			<div class="column-pane">
				<p>
					<button type="button" name="createCoupon" class="button button-primary">
						<?php esc_html_e('New Coupon', 'calendarista') ?>
					</button>
					&nbsp;&nbsp;
					<span id="spinner_update_coupons_list" class="calendarista-spinner calendarista-invisible">
						<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">
					</span>
				</p>
				<div id="calendarista_coupons_list" class="table-responsive">
				<?php $this->couponsList->printVariables() ?>
				<?php $this->couponsList->display(); ?>
				</div>
				<form id="calendarista_form" data-parsley-validate action="<?php echo esc_url($this->currentUrl) ?>" method="post">
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
					<p>
						<button type="submit" name="deleteAllCoupon" class="button button-primary">
							<?php esc_html_e('Delete all Coupons', 'calendarista') ?>
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
				calendarista.coupons = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.coupons.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.dateTimepickerOptions = {
						'showHour': false
						, 'showMinute': false
						, 'showTime': false
						, 'alwaysSetTime': false
						, 'dateFormat': 'yy-mm-dd'
						, 'minDate': 0
					};
					this.requestUrl = options['requestUrl'];
					this.actionCreateCoupon = 'calendarista_create_coupon';
					this.actionSendEmailCoupon = 'calendarista_email_coupon';
					this.actionGetCouponsList = 'calendarista_get_coupons_list';
					this.$sendEmailPlaceHolder = $('.email_coupon_placeholder');
					this.$createCouponPlaceHolder = $('.create_coupon_placeholder');
					this.ajax = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'coupons'});
					this.ajax2 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'email_coupons'});
					this.ajax3 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'update_coupons_list'});
					this.$filter = $('#coupons_filter');
					this.$searchFilterProjectList = this.$filter.find('select[name="projectId"]');
					this.$filterButton = this.$filter.find('#filterButton');
					this.$filterResetButton = this.$filter.find('#filterResetButton');
					this.$createCouponButtons = $('button[name="createCoupon"]');
					this.$editCouponButtons = $('button[name="editCoupon"]');
					this.$emailCouponButtons = $('button[name="emailCoupon"]');
					this.$couponsList = $('#calendarista_coupons_list');
					this.$couponType = this.$filter.find('select[name="couponType"]');
					this.$searchByCode = this.$filter.find('input[name="searchByCode"]');
					this.$discount = this.$filter.find('input[name="discount"]');
					this.$createCouponButtons.on('click', function(e){
						var model = [
							{ 'name': 'action', 'value': context.actionCreateCoupon }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }];
						context.$createCouponModalDialog.dialog('open');
						context.createEditCouponButtonText(0);
						context.$createCouponModalDialog.dialog('widget').find('#spinner_coupons').removeClass('calendarista-invisible');
						context.ajax.request(context, context.couponResponse, $.param(model));
					});
					this.editCouponDelegate = calendarista.createDelegate(this, this.editCoupon);
					this.emailCouponDelegate = calendarista.createDelegate(this, this.emailCoupon);
					this.$editCouponButtons.on('click', this.editCouponDelegate);
					this.$emailCouponButtons.on('click', this.emailCouponDelegate);
					this.$filterButton.on('click', function(e){
						context.couponsListRequest(true);
					});
					this.$filterResetButton.on('click', function(e){
						context.$searchFilterProjectList[0].selectedIndex = 0;
						context.$couponType[0].selectedIndex = 0;
						context.$searchByCode.val('');
						context.$discount.val('');
						context.couponsListRequest(true);
					});
					this.$emailCouponModalDialog = $('.email-coupon-modal').dialog({
						autoOpen: false
						, height: '280'
						, width: '640'
						, modal: true
						, resizable: false
						, dialogClass: 'calendarista-dialog'
						, closeOnEscape: false
						, open: function(event, ui) {
							$('.ui-dialog-titlebar-close', ui.dialog | ui).hide();
						}
						, create: function() {
							var spinner = '<div id="spinner_email_coupons" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
								spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">&nbsp;';
								spinner += '</div>';
							$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
						}
						, buttons: [
							{
								'text': 'Send Email'
								, 'name': 'submit'
								, 'click':  function(e){
									var $form = context.$emailCouponModalDialog.dialog('widget').find('#form1')
										, $id = $form.find('input[name="id"]')
										, model = [
											{ 'name': 'id', 'value':  parseInt($id.val(), 10) }
											, { 'name': 'name', 'value': context.$emailCouponModalDialog.dialog('widget').find('input[name="name"]').val()}
											, { 'name': 'email', 'value': context.$emailCouponModalDialog.dialog('widget').find('input[name="email"]').val()}
											, { 'name': 'sendEmail', 'value': 'true'}
											, { 'name': 'action', 'value': context.actionSendEmailCoupon }
											, { 'name': 'calendarista_nonce', 'value': context.nonce }
										];
									e.preventDefault();
									if(!Calendarista.wizard.isValid($form)){
										return false;
									}
									context.ajax2.request(context, context.emailCouponSubmitResponse, $.param(model));
								}
							}
							, {
								'text': 'Close'
								, 'click':  function(){
									$('#spinner_email_coupons').removeClass('calendarista-invisible');
									context.$emailCouponModalDialog.dialog('close');
									context.couponsListRequest();
								}
							}
						]
					});
					this.$createCouponModalDialog = $('.create-coupon-modal').dialog({
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
							var spinner = '<div id="spinner_coupons" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
								spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">&nbsp;';
								spinner += '</div>';
							$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
						}
						, buttons: [
							{
								'text': 'Create'
								, 'name': 'create'
								, 'click':  function(e){
									var $target = $(e.currentTarget)
										, $form = context.$createCouponModalDialog.dialog('widget').find('#form1')
										, editMode = parseInt($target.val(), 10) === 1
										, model = $form.serializeArray();
									e.preventDefault();
									if(!Calendarista.wizard.isValid($form)){
										return false;
									}
									model.push({ 'name': 'controller', 'value': 'calendarista_coupons'});
									model.push({ 'name': editMode ? 'calendarista_update' : 'calendarista_create', 'value': 'true'});
									model.push({ 'name': 'action', 'value': context.actionCreateCoupon });
									model.push({ 'name': 'calendarista_nonce', 'value': context.nonce });
									if(!editMode){
										context.createEditCouponButtonText(2);
									}
									context.ajax.request(context, context.couponResponse, $.param(model));
								}
							}
							, {
								'text': 'Delete'
								, 'name': 'delete'
								, 'click':  function(){
									var $id = context.$createCouponModalDialog.dialog('widget').find('input[name="id"]')
										, model = [
											{ 'name': 'id', 'value': parseInt($id.val(), 10)}
											, { 'name': 'controller', 'value': 'calendarista_coupons'}
											, { 'name': 'calendarista_delete', 'value': 'true'}
											, { 'name': 'action', 'value': context.actionCreateCoupon }
											, { 'name': 'calendarista_nonce', 'value': context.nonce }
										];
									context.createEditCouponButtonText(2);
									context.ajax.request(context, context.couponResponse, $.param(model));
								}
							}
							, {
								'text': 'Close'
								, 'click':  function(){
									$('#spinner_coupons').removeClass('calendarista-invisible');
									context.$createCouponModalDialog.dialog('close');
									context.couponsListRequest();
								}
							}
						]
					});
				};
				calendarista.coupons.prototype.editCoupon = function(e){
					var $id = $(e.currentTarget)
						, model = [
							{ 'name': 'id', 'value':  parseInt($id.val(), 10) }
							, { 'name': 'action', 'value': this.actionCreateCoupon }
							, { 'name': 'calendarista_nonce', 'value': this.nonce }
						];
					this.$createCouponModalDialog.dialog('open');
					this.createEditCouponButtonText(1);
					$('#spinner_coupons').removeClass('calendarista-invisible');
					this.ajax.request(this, this.couponResponse, $.param(model));
				};
				calendarista.coupons.prototype.emailCoupon = function(e){
					var $id = $(e.currentTarget)
						, $submitButton
						, model = [
							{ 'name': 'id', 'value':  parseInt($id.val(), 10) }
							, { 'name': 'action', 'value': this.actionSendEmailCoupon }
							, { 'name': 'calendarista_nonce', 'value': this.nonce }
						];
					this.$emailCouponModalDialog.dialog('open');
					$submitButton = this.$emailCouponModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="submit"]');
					$submitButton.prop('disabled', false).removeClass('ui-state-disabled');
					$('#spinner_email_coupons').removeClass('calendarista-invisible');
					this.ajax2.request(this, this.emailCouponResponse, $.param(model));
				};
				calendarista.coupons.prototype.emailCouponResponse = function(result){
					this.$sendEmailPlaceHolder.replaceWith('<div class="email_coupon_placeholder">' + result + '</div>');
					this.$sendEmailPlaceHolder = $('.email_coupon_placeholder');
				};
				calendarista.coupons.prototype.emailCouponSubmitResponse = function(result){
					var $submitButton;
					this.emailCouponResponse(result);
					$submitButton = this.$emailCouponModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="submit"]');
					$submitButton.prop('disabled', true).addClass('ui-state-disabled');
				};
				calendarista.coupons.prototype.couponResponse = function(result){
					this.$createCouponPlaceHolder.replaceWith('<div class="create_coupon_placeholder">' + result + '</div>');
					this.$createCouponPlaceHolder = $('.create_coupon_placeholder');
					this.$createCouponPlaceHolder.find('input[name="expirationDate"]').datetimepicker(this.dateTimepickerOptions);
				};
				calendarista.coupons.prototype.createEditCouponButtonText = function(status){
					var $createButton = this.$createCouponModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="create"]')
						, $deleteButton = this.$createCouponModalDialog.dialog('widget').find('.ui-dialog-buttonset').find('button[name="delete"]');
					$createButton.button('option', 'label', '<?php echo $this->decodeString(__('Create', 'calendarista')) ?>');
					$createButton.prop('disabled', false).removeClass('ui-state-disabled');
					$deleteButton.prop('disabled', true).addClass('ui-state-disabled');
					$createButton.val(status);
					if(status == 1){
						$createButton.button('option', 'label', '<?php echo $this->decodeString(__('Update', 'calendarista')) ?>');
						$deleteButton.prop('disabled', false).removeClass('ui-state-disabled');
					}
					if(status === 2){
						$createButton.prop('disabled', true).addClass('ui-state-disabled');
					}
				};
				calendarista.coupons.prototype.couponsListRequest = function(cleanUrl, values){
				var paged = $('input[name="paged"]').val()
					, orderby = $('input[name="orderby"]').val()
					, order = $('input[name="order"]').val()
					, projectId = this.$searchFilterProjectList.val()
					, searchByCode = this.$searchByCode.val()
					, couponType = this.$couponType.val()
					, discount = this.$discount.val()
					, url = window.location.pathname + window.location.search
					, model = [
						{ 'name': 'projectId', 'value': projectId }
						, { 'name': 'current_url', 'value': url }
						, { 'name': 'searchByCode', 'value': searchByCode }
						, { 'name': 'couponType', 'value': couponType }
						, { 'name': 'discount', 'value': discount }
						, { 'name': 'action', 'value': this.actionGetCouponsList }
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
				this.ajax3.request(this, this.couponsListResponse, $.param(model));
			};
			calendarista.coupons.prototype.couponsListResponse = function(result){
				var context = this;
				this.$editCouponButtons.off();
				this.$emailCouponButtons.off();
				this.$couponsList.replaceWith('<div id="calendarista_coupons_list">' + result + '</div>');
				this.$couponsList = $('#calendarista_coupons_list');
				this.$editCouponButtons = $('button[name="editCoupon"]');
				this.$editCouponButtons.on('click', this.editCouponDelegate);
				this.$emailCouponButtons = $('button[name="emailCoupon"]');
				this.$emailCouponButtons.on('click', this.emailCouponDelegate);
				this.pagerButtonDelegates();
			};
			calendarista.coupons.prototype.pagerButtonDelegates = function(){
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
			calendarista.coupons.prototype.gotoPage = function(e){
				var pagedValue = this.getUrlParameter('paged', $(e.currentTarget).prop('href'))
					, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
				this.$nextPage.off();
				this.$lastPage.off();
				this.$prevPage.off();
				this.$firstPage.off();
				this.couponsListRequest(false, model);
				e.preventDefault();
				return false;
			};
			calendarista.coupons.prototype.removeURLParameter = function(parameter) {
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
			calendarista.coupons.prototype.getUrlParameter = function(param, url) {
				var regex, results;
				param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
				regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
				results = regex.exec(url);
				return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.coupons({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
	<?php
	}
}