<?php
class Calendarista_DestinationTemplate extends Calendarista_ViewBase{
	public $map;
	public $createNew = true;
	function __construct(){
		parent::__construct(false, false, 'calendarista-places');
		if($this->selectedProjectId !== -1){
			$mapRepo = new Calendarista_MapRepository();
			$this->map = $mapRepo->readByProject($this->selectedProjectId);
			if(isset($this->map) && isset($this->map->id)){
				$this->createNew = false;
			}
		}
		if(!isset($this->map)){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		$this->render();
	}
	public function render(){
	?>
		<div class="wrap">
			<form id="formDestination" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="tabName" value="destination_settings"/>
				<input type="hidden" name="contextMenuType" value="2"/>
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
				<input type="hidden" name="calendarista_update"/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<?php if($this->map->costMode !== Calendarista_CostMode::NONE): ?>
				<input type="hidden" name="enableDestinationField" value="<?php echo $this->map->enableDestinationField ?>">
				<?php endif; ?>
				<table class="form-table">
					<tbody>
						<?php if($this->map->costMode === Calendarista_CostMode::NONE): ?>
						<tr>
							<td>
							  <label>
								<input type="checkbox" name="enableDestinationField" <?php echo $this->map->enableDestinationField ? 'checked' : '' ?>
									><?php esc_html_e('Enable destination field', 'calendarista') ?>
							  </label>
							</td>
						</tr>
						<?php endif; ?>
						<?php if($this->map->enableDestinationField):?>
						<tr>
							<td>
								<p>
									<button type="button"
										class="button button-primary"
										name="addDestination" title="Add a new destination location">
										<i class="fa fa-plus"></i>
										<?php esc_html_e('Add new', 'calendarista') ?>
									</button>
									<span class="description"><?php esc_html_e('Or right click on the map and select add new location', 'calendarista') ?></span>
								</p>
								<br>
								<p class="description"><?php esc_html_e('Click [add new] to create a selection list of predefined location', 'calendarista') ?></p>
							</td>
						</tr>
						<?php /*
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="toPlacesPreload" <?php echo $this->map->toPlacesPreload ? 'checked' : '' ?>><?php esc_html_e('Preload all locations on map', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						*/?>
						<?php endif; ?>
					</tbody>
				</table>
				<?php /*
				<p class="submit">
					<?php if(!$this->createNew):?>
					<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>"
						<?php echo $this->selectedProjectId === -1 ? 'disabled' : ''?>>
					<?php else:?>
					<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Create map', 'calendarista') ?>" 
						<?php echo $this->selectedProjectId === -1 ? 'disabled' : ''?>>
					<?php endif;?>
				</p>*/?>
			</form>
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.destination = function(options){
				this.init(options);
			};
			calendarista.destination.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.$form = $('#formDestination');
				this.$enableDestinationField = $('input[name="enableDestinationField"]');
				this.$enableDestinationField.on('change', function(){
					context.$form.submit();
				});
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.destination({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
	<?php
	}
}