<?php
class Calendarista_PricingSchemeTemplate extends Calendarista_ViewBase{
	public $pricingScheme;
	public $selectedPriceSchemeId;
	public $availabilityId;
	public $seasonId;
	function __construct($availabilityId, $seasonId = null){
		$this->availabilityId = $availabilityId;
		$this->seasonId = $seasonId;
		parent::__construct();
		new Calendarista_PricingSchemeController(
			array($this, 'createdPriceScheme')
			, array($this, 'updatedPriceScheme')
			, array($this, 'deletedPriceScheme')
			, array($this, 'autogenPriceScheme')
		);
		$repo = new Calendarista_PricingSchemeRepository();
		if($this->seasonId){
			$this->pricingScheme = $repo->readBySeasonId($seasonId);
		}else{
			$this->pricingScheme = $repo->readByAvailabilityId($availabilityId, true);
		}
		if(!$this->pricingScheme){
			$this->pricingScheme = array();
		}
		array_push($this->pricingScheme, array('id'=>0, 'days'=>0, 'cost'=>0));
		$this->requestUrl .= '&availabilityId=' . $availabilityId;
		$this->render();
	}
	public function autogenPriceScheme($duplicate){
		if(count($duplicate) > 0):
		?>
		<div class="index error notice is-dismissible">
			<p><?php echo sprintf(__('The price scheme with the following number of days (%s) already exists. All other days were created.', 'calendarista'), implode(',', $duplicate)); ?></p>
		</div>
		<?php
		else:
		?>
		<div class="index updated notice is-dismissible calendarista-notice">
			<p><?php esc_html_e('The price scheme has been generated.', 'calendarista'); ?></p>
		</div>
		<?php 
		endif;
	}
	public function duplicatePriceSchemeNotice(){
		?>
		<div class="index error notice is-dismissible">
			<p><?php esc_html_e('The price scheme with the same number of days already exists, aborted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function createdPriceScheme($id, $duplicate){
		$this->selectedPriceSchemeId = $id;
		if($duplicate){
			$this->duplicatePriceSchemeNotice();
			return;
		}
		?>
		<div class="index updated notice is-dismissible calendarista-notice">
			<p><?php esc_html_e('The price scheme has been created.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function updatedPriceScheme($id, $duplicate){
		$this->selectedPriceSchemeId = $id;
		if($duplicate){
			$this->duplicatePriceSchemeNotice();
			return;
		}
		?>
		<div class="index updated notice is-dismissible calendarista-notice">
			<p><?php esc_html_e('The price scheme has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function deletedPriceScheme(){
		?>
		<div class="index updated notice is-dismissible calendarista-notice">
			<p><?php esc_html_e('The price scheme has been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
			<input type="hidden" name="controller" value="pricing_scheme" />
			<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>"/>
			<input type="hidden" name="availabilityId" value="<?php echo $this->availabilityId ?>"/>
			<input type="hidden" name="seasonId" value="<?php echo $this->seasonId ?>"/>
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<div class="calendarista-borderless-accordion">
				<div id="calendar_pricingscheme_autogen">
					<h3><?php esc_html_e('Autogen', 'calendarista') ?></h3>
					<div>
						<label for="autogen_days_from"><?php esc_html_e('Day range', 'calendarista') ?></label>
						<input id="autogen_days_from" 
							name="autogen_days_from" 
							type="text" 
							class="small-text" 
							data-parsley-type="digits"
							data-parsley-min="2"
							data-parsley-errors-container="#autogen_error_container"
							placeholder="0" />
						-
						<input id="autogen_days_to" 
							name="autogen_days_to" 
							type="text" 
							class="small-text" 
							data-parsley-type="digits"
							data-parsley-min="2"
							data-parsley-errors-container="#autogen_error_container"
							placeholder="0" />
						<br>
						<br>
						<label for="cost"><?php esc_html_e('cost', 'calendarista') ?></label>
						<input id="autogen_cost" 
							name="autogen_cost" 
							type="text" 
							class="small-text" 
							data-parsley-trigger="change focusout"
							data-parsley-min="0"
							data-parsley-pattern="^\d+(\.\d{1,2})?$"
							data-parsley-errors-container="#autogen_error_container"
							placeholder="0.00" />
						<button type="submit" name="calendarista_autogen_create" class="button button-primary">
							<?php esc_html_e('Generate', 'calendarista') ?>
						</button>
						<div id="autogen_error_container"></div>
						<p class="description"><?php esc_html_e('Auto generate to apply the same cost to multiple days.', 'calendarista') ?></p>
					</div>
				</div>
			</div>
			<hr>
			<table class="widefat">
				<thead>
					<th><input type="checkbox" name="del_all_prices" value="pricingscheme-container">
					<th><?php esc_html_e('Days', 'calendarista') ?></th>
					<th><?php esc_html_e('Cost', 'calendarista') ?></th>
					<th></th>
				</thead>
				<tbody class="pricingscheme-container">
				<?php for($i = 0; $i < count($this->pricingScheme);$i++): ?>
					<tr>
					<td>
						<input type="checkbox" name="pricingSchemes[]" value="<?php echo $this->pricingScheme[$i]['id'] ?>" 
							 <?php echo !$this->pricingScheme[$i]['id'] ? 'disabled' : '' ?>>
					</td>
					<td>
						<input id="days_<?php echo $this->pricingScheme[$i]['id'] ?>" 
							name="days_<?php echo $this->pricingScheme[$i]['id'] ?>" 
							type="text" 
							class="small-text" 
							data-parsley-trigger="change focusout"
							placeholder="0"
							data-parsley-type="digits"
							data-parsley-min="2"
							data-parsley-errors-container="#pricing_error_container"
							value="<?php echo $this->emptyStringIfZero($this->pricingScheme[$i]['days']) ?>" />
					</td>
					<td>
						<input id="cost_<?php echo $this->pricingScheme[$i]['id'] ?>" 
							name="cost_<?php echo $this->pricingScheme[$i]['id'] ?>" 
							type="text" 
							class="small-text" 
							data-parsley-trigger="change focusout"_<?php echo $i ?>
							data-parsley-min="0"
							data-parsley-pattern="^\d+(\.\d{1,2})?$"
							data-parsley-errors-container="#pricing_error_container"
							placeholder="0.00"
							value="<?php echo $this->emptyStringIfZero($this->pricingScheme[$i]['cost']) ?>" />
					</td>
					<td>
						<button type="submit" name="calendarista_<?php echo $this->pricingScheme[$i]['id'] ? 'update' : 'create' ?>" class="button button-primary" 
						value="<?php echo $this->pricingScheme[$i]['id'] ?>">
						<?php echo $this->pricingScheme[$i]['id'] ? __('Update', 'calendarista') : __('Add', 'calendarista') ?>
						</button>
					</td>
					<?php endfor; ?>
				</tbody>
			</table>
			<div id="pricing_error_container"></div>
			<p>
			<button type="submit" name="calendarista_delete" class="button button-primary calendarista-delete-priceschemes" disabled>
			<?php esc_html_e('Delete', 'calendarista') ?>
			</button>
			</p>
		</form>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.pricingScheme = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.pricingScheme.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$deleteAllPrices = $('input[name="del_all_prices"]');
					this.$priceSchemeCheckboxes = $('.pricingscheme-container input[type="checkbox"]:not(:disabled)');
					this.$priceSchemeDeleteButton = $('.calendarista-delete-priceschemes');
					this.$priceSchemeCheckboxes.on('change', function(e){
						context.deletePriceSchemeButtonState();
					});
					this.$deleteAllPrices.on('change', function(e){
						var $target = $(this)
							, checked = $target.is(':checked')
							, $container = $('.' + $target.val())
							, $checkboxes = $container.find('input[type="checkbox"]:not(:disabled)');
						if(checked){
							$checkboxes.prop('checked', true);
						}else{
							$checkboxes.prop('checked', false);
						}
						context.deletePriceSchemeButtonState();
					});
					 $('#calendar_pricingscheme_autogen').accordion({
						collapsible: true
						, active: false
						, heightStyle: 'content'
						, autoHeight: false
						, clearStyle: true
					});
				};
				calendarista.pricingScheme.prototype.deletePriceSchemeButtonState = function(){
					var hasChecked = this.$priceSchemeCheckboxes.is(':checked');
					if(hasChecked){
						this.$priceSchemeDeleteButton.prop('disabled', false);
					}else{
						this.$priceSchemeDeleteButton.prop('disabled', true);
					}
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.pricingScheme({<?php echo $this->requestUrl ?>'});
		</script>
		<?php
	}
}