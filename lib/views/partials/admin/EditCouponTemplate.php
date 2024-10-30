<?php
class Calendarista_EditCouponTemplate extends Calendarista_ViewBase{
	public $id;
	public $coupon;
	public $partialUpdate = false;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-coupons');
		$this->id = isset($_POST['id']) ? (int)$_POST['id'] : null;
		$coupon = new Calendarista_Coupon(array(
			'discount'=>isset($_POST['discount']) ? (double)$_POST['discount'] : null,
			'orderMinimum'=>isset($_POST['orderMinimum']) ? (double)$_POST['orderMinimum'] : null,
			'expirationDate'=>isset($_POST['expirationDate']) ? sanitize_text_field($_POST['expirationDate']) : null,
			'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
			'projectName'=>isset($_POST['projectName']) ? sanitize_text_field($_POST['projectName']) : null,
			'code'=>isset($_POST['code']) ? sanitize_text_field($_POST['code']) : null,
			'emailedTo'=>isset($_POST['emailedTo']) ? sanitize_text_field($_POST['emailedTo']) : null,
			'discountMode'=>isset($_POST['discountMode']) ? (int)$_POST['discountMode'] : null,
			'couponType'=>isset($_POST['couponType']) ? (int)$_POST['couponType'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null,
		));
		$coupon->projectName = $this->selectedProjectName;
		new Calendarista_CouponsController(
			$coupon
			, array($this, 'create')
			, array($this, 'update')
			, array($this, 'delete')
		);
		if($this->id !== null){
			$couponRepository = new Calendarista_CouponRepository();
			$this->coupon = $couponRepository->read($this->id);
			if($this->coupon){
				$this->selectedProjectId = $this->coupon->projectId;
			}
		}
		if(!$this->coupon){
			$oneMonthLater = new Calendarista_DateTime();
			$oneMonthLater->modify('+30 day');
			$this->coupon = new Calendarista_Coupon(array('expirationDate'=>$oneMonthLater->format(CALENDARISTA_DATEFORMAT)));
		}
		if(!$this->partialUpdate){
			$this->render();
		}
	}
	function create($total, $result){
		if($result){
			$this->partialUpdate = true;
			$this->createNotification($total);
			return;
		}
		$this->createFailNotification();
	}
	function update($id, $result){
		if($result){
			$this->updateNotification($id);
			return;
		}
		$this->updateFailNotification();
	}
	function delete($id, $result){
		if($result){
			$this->partialUpdate = true;
			$this->deleteNotification($id);
		}
	}
	function createNotification($total){
		?>
		<div class="updated notice is-dismissible">
			<p><?php echo sprintf(__('#%d coupon(s) created', 'calendarista'), $total); ?></p>
		</div>
		<?php
	}
	public function updateNotification($id) {
		?>
		<div class="updated notice is-dismissible">
			<p><?php echo sprintf(__('The coupon has been updated', 'calendarista'), $id); ?></p>
		</div>
		<?php
	}
	public function deleteNotification($id) {
		?>
		<div class="updated notice is-dismissible">
			<p><?php echo sprintf(__('The coupon has been deleted', 'calendarista'), $id); ?></p>
		</div>
		<?php
	}
	public function createFailNotification() {
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e('Coupon creation failed. Ensure the coupon code does not already exist.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function updateFailNotification() {
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e('The update failed.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<form id="form1" data-parsley-validate method="post">
			<input type="hidden" name="id" value="<?php echo $this->id; ?>">
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="projectId">
								<?php esc_html_e('Service', 'calendarista') ?>
							</label>
						</th>
						<td>
							<?php $this->renderProjectSelectList(true, __('All services', 'calendarista')) ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="expirationDate"><?php esc_html_e('Expiration Date', 'calendarista')?></label>
						</th>
						<td>
							<input type="text" 
								id="expirationDate" 
								name="expirationDate" 
								class="regular-text enable-readonly-input" 
								readonly
								value="<?php echo $this->coupon->expirationDate->format(CALENDARISTA_DATEFORMAT) ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="code"><?php esc_html_e('Coupon code', 'calendarista')?></label>
						</th>
						<td>
						  <input type="text" 
								id="code" 
								name="code" 
								class="regular-text calendarista_parsley_validated"
								data-parsley-required="true"
								data-parsley-trigger="change" 
								value="<?php echo $this->coupon->code ? $this->coupon->code : sha1(uniqid(wp_rand(), true))?>">
							<p class="description"><?php esc_html_e('Edit to customize eg: 10%OFF but must be unique', 'calendarista') ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="couponType"><?php esc_html_e('Coupon Type', 'calendarista')?></label>
						</th>
						<td>
						  <select 
								id="couponType" 
								name="couponType">
								<option value="0" <?php echo $this->coupon->couponType === 0 ? "selected" : "" ?>>Regular</option>
								<option value="1" <?php echo $this->coupon->couponType === 1 ? "selected" : "" ?>>Super</option>
							</select>
							<p class="description"><?php esc_html_e('A regular coupon is limited to one time use only', 'calendarista') ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="discount"><?php esc_html_e('Discount', 'calendarista')?></label></th>
						<td>
							<label>
								 <input type="radio"  
														name="discountMode" 
														value="0"
														<?php echo !$this->coupon->discountMode ? 'checked' : '' ?>>
								<?php esc_html_e('Percentage', 'calendarista') ?>
								</label>
							<label>
								 <input type="radio"  
														name="discountMode" 
														value="1"
														<?php echo $this->coupon->discountMode ? 'checked' : '' ?>>
								<?php esc_html_e('Fixed', 'calendarista') ?>
								</label>
							  <input type="text" 
									id="discount" 
									name="discount" 
									class="small-text calendarista_parsley_validated" 
									data-parsley-required="true"
									data-parsley-pattern="^\d+(\.\d{1,2})?$"
									data-parsley-min="0.1"
									data-parsley-max="100"
									data-parsley-trigger="change" 
									value="<?php echo Calendarista_MoneyHelper::toDouble($this->coupon->discount) ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="orderMinimum"><?php esc_html_e('Apply coupon if exceeds', 'calendarista')?></label>
						</th>
						<td>
						  <input type="text" 
								id="orderMinimum" 
								name="orderMinimum" 
								class="regular-text calendarista_parsley_validated" 
								data-parsley-type="number"
								data-parsley-min="0.00"
								data-parsley-trigger="change" 
								value="<?php echo $this->coupon->orderMinimum ?>">
							<p class="description"><?php esc_html_e('Insert amount above', 'calendarista') ?></p>
						</td>
					</tr>
					<?php if($this->id === null): ?>
					<tr id="coupon_count_container">
						<th scope="row">
							<label for="couponsCount">
								<?php esc_html_e('No. of coupons', 'calendarista')?>
							</label>
						</th>
						<td>
						  <input type="text" 
								id="couponsCount" 
								name="couponsCount" 
								class="regular-text calendarista_parsley_validated" 
								data-parsley-type="digits"
								data-parsley-min="1"
								data-parsley-trigger="change"
								value="1">
							<p class="description"><?php esc_html_e('A value greater than 1 will autogenerate coupon code', 'calendarista') ?></p>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</form>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.coupon = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.coupon.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$discountMode = $('input[name="discountMode"]');
					this.$discount = $('input[name="discount"]');
					this.$discountMode.on('change', function(){
						var selectedDiscountMode = parseInt(context.$discountMode.filter(':checked').val(), 10);
						if(selectedDiscountMode === 1){
							context.$discount.removeAttr('data-parsley-max');
							return;
						}
						context.$discount.attr('data-parsley-max', 100);
					});
					this.$couponType = $('select[name="couponType"]');
					this.$couponCountContainer = $('#coupon_count_container');
					this.$couponType.on('change', function(){
						var selection = parseInt($(this).val(), 10);
						context.$couponCountContainer.removeClass('hide');
						if(selection === 1){
							//super coupon
							context.$couponCountContainer.addClass('hide');
						}
					});
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.coupon({
			<?php echo $this->requestUrl ?>'
		});
		</script>
		<?php
	}
}