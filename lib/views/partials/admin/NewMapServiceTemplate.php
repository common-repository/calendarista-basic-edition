<?php
class Calendarista_NewMapServiceTemplate extends Calendarista_ViewBase{
	public $project;
	public $projectsToExclude = array();
	public $maps;
	function __construct(){
		parent::__construct(false, true, 'calendarista-places');
		$mapRepo = new Calendarista_MapRepository();
		$this->maps = $mapRepo->readAll();
		if(count($this->maps) > 0){
			foreach($this->maps as $map){
				array_push($this->projectsToExclude, $map['projectId']);
			}
		}
		if($this->selectedProjectId !== -1){
			$this->map = $mapRepo->readByProject($this->selectedProjectId);
		}
		if(!isset($this->map)){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		$this->project = $this->getProject();
		$this->render();
	}
	public function selectedCostMode($val, $checked = 'checked'){
		return isset($this->map->id) && $this->map->costMode === $val ? $checked : 'disabled';
	}
	public function render(){
	?>
		<form id="form1" data-parsley-validate action="<?php echo esc_url($this->baseUrl) ?>" method="post">
			<input type="hidden" name="tabName" value="new_place"/>
			<input type="hidden" name="controller" value="map" />
			<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
			<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
			<input type="hidden" name="enableDistance" value="<?php echo $this->map->enableDistance ?>">
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<?php if($this->selectedProjectId !== -1):?>
							<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>">
							<?php else: ?>
							<?php $this->renderProjectSelectList(true, __('Select a service', 'calendarista'), true, true, array(), $this->projectsToExclude) ?>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td>
							 <label>
								<input type="radio" name="costMode" value="0" <?php echo esc_attr($this->selectedCostMode(0)) ?>>
								<?php esc_html_e('Cost does not apply', 'calendarista') ?>
							  </label>
						</td>
					</tr>
					<tr>
						<td>
							  <label>
								<input type="radio" name="costMode" value="1" <?php echo esc_attr($this->selectedCostMode(1)) ?>>
								<?php esc_html_e('Cost depends on predefined departure and destination combo', 'calendarista') ?>
							  </label>
						</td>
					</tr>
					<tr>
						<td>
							  <label>
								<input type="radio" name="costMode" value="2"  <?php echo esc_attr($this->selectedCostMode(2)) ?>>
								<?php esc_html_e('Cost depends on predefined departure', 'calendarista') ?>
							  </label>
						</td>
					</tr>
					<tr>
						<td>
							<label>
								<input type="radio" name="costMode" value="3"  <?php echo esc_attr($this->selectedCostMode(3)) ?>>
								<?php esc_html_e('Charge', 'calendarista'); ?>
								<input type="text" 
									class="woald_parsley_validated small-text"
									placeholder="0.00"
									data-parsley-errors-container=".unit-cost-error-container"
									data-parsley-trigger="change"
									data-parsley-min="0"
									data-parsley-required="false"
									value="<?php echo $this->map->unitCost ?>"
									data-parsley-pattern="^\d+(\.\d{1,2})?$" 
									name="unitCost"
									id="unitCost"
									<?php echo esc_attr($this->selectedCostMode(3)) ?>/>
								&nbsp;<?php esc_html_e('per km/mile', 'calendarista') ?>.
						  </label>
						  <span class="minimum-unit"><?php esc_html_e('When distance is', 'calendarista') ?></span>
									<input type="text" 
										class="woald_parsley_validated small-text"
										placeholder="0"
										data-parsley-errors-container=".minimum-unit-value-error-container"
										data-parsley-trigger="change"
										data-parsley-min="0"
										data-parsley-required="false"
										value="<?php echo $this->map->minimumUnitValue ?>"
										data-parsley-pattern="^\d+(\.\d{1,2})?$" 
										name="minimumUnitValue"
										id="minimumUnitValue"
										<?php echo esc_attr($this->selectedCostMode(3)) ?>/>
								<span class="minimum-unit"><?php esc_html_e('km/miles, apply fixed cost of', 'calendarista') ?></span>
								<input type="text" 
									class="woald_parsley_validated small-text"
									placeholder="0.00"
									data-parsley-errors-container=".minimum-unit-cost-error-container"
									data-parsley-trigger="change"
									data-parsley-min="0"
									data-parsley-required="false"
									value="<?php echo $this->map->minimumUnitCost ?>"
									data-parsley-pattern="^\d+(\.\d{1,2})?$" 
									name="minimumUnitCost"
									id="minimumUnitCost"
									<?php echo esc_attr($this->selectedCostMode(3)) ?>/>
							<div class="unit-value-error-container"></div>
							<div class="minimum-unit-value-error-container"></div>
							<div class="minimum-unit-cost-error-container"></div>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<?php if($this->selectedProjectId !== -1):?>
				<a href="<?php echo esc_url(admin_url() . 'admin.php?page=calendarista-places') ?>" class="button button-primary">
					 <?php esc_html_e('New', 'calendarista') ?>
				</a>
				<button type="submit" name="calendarista_update" class="button button-primary">
					 <?php esc_html_e('Update', 'calendarista') ?>
				</button>
				<?php else: ?>
				<button type="submit" name="calendarista_create" class="button button-primary" disabled>
					 <?php esc_html_e('Create new', 'calendarista') ?>
				</button>
				<?php endif; ?>
			</p>
		</form>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.newMapService = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.newMapService.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.projectId = options['projectId'];
				this.requestUrl = options['requestUrl'];
				this.baseUrl = options['baseUrl'];
				this.$createServiceLocationModalForm =  $('#create-new-service-location-modal form');
				//cost none
				this.$option1 = $('input[name="costMode"][value="0"]');
				//cost by departure and destination & departure only
				this.$option2 = $('input[name="costMode"][value="1"],input[name="costMode"][value="2"]');
				//cost by distance
				this.$option3 = $('input[name="costMode"][value="3"]');
				this.$enableDistance = $('input[name="enableDistance"]');
				this.$unitCost = $('input[name="unitCost"]');
				this.$minimumUnitValue = $('input[name="minimumUnitValue"]');
				this.$minimumUnitCost = $('input[name="minimumUnitCost"]');
				this.$createButton = $('button[name="calendarista_create"]');
				this.$projects = $('select[name="projectId"]');
				this.$projects.on('change', function(e){
					var paymentsMode = parseInt($(this).find('option:selected').attr('data-calendarista-payments-mode'), 10);
					context.$enableDistance.val(0);
					context.$unitCost.val('');
					context.$minimumUnitValue.val('');
					context.$minimumUnitCost.val('');
					context.$unitCost.prop('disabled', true);
					context.$minimumUnitValue.prop('disabled', true);
					context.$minimumUnitCost.prop('disabled', true);
					switch(paymentsMode){
						case -1:
							context.$option1.prop('checked', true).prop('disabled', false);
							context.$option2.removeProp('checked').prop('disabled', true);
							context.$option3.removeProp('checked').prop('disabled', true);
						break;
						case 0:
						case 1:
						case 2:
						case 3:
							context.$option1.removeProp('checked').prop('disabled', false);
							context.$option2.prop('checked', true).prop('disabled', false);
							context.$option3.removeProp('checked').prop('disabled', false);
						break;
					}
					context.createButtonState();
				});
				$('input[name="costMode"]').on('change', function(){
					var option = parseInt($(this).val(), 10);
					context.$enableDistance.val(0);
					context.$unitCost.val('');
					context.$minimumUnitValue.val('');
					context.$minimumUnitCost.val('');
					context.$unitCost.prop('disabled', true);
					context.$minimumUnitValue.prop('disabled', true);
					context.$minimumUnitCost.prop('disabled', true);
					switch (option){
						case 3:
							context.$enableDistance.val(1);
							context.$unitCost.prop('disabled', false);
							context.$minimumUnitValue.prop('disabled', false);
							context.$minimumUnitCost.prop('disabled', false);
						break;
					}
				});
				this.createButtonState();
			};
			calendarista.newMapService.prototype.createButtonState = function(){
				if(this.$projects.val() !== ''){
					this.$createButton.prop('disabled', false);
				}else{
					this.$createButton.prop('disabled', true);
				}
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.newMapService({
				'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
				, 'baseUrl': '<?php echo $this->baseUrl ?>'
		});
		</script>
	<?php
	}
}