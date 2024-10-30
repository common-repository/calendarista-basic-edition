<?php
class Calendarista_DepartureTemplate extends Calendarista_ViewBase{
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
		if(isset($_GET['newmapservice'])){
			$this->newMapServiceCreatedNotice();
		}
		$this->render();
	}
	public function newMapServiceCreatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The map service has been created, now setup your departure properties for the service or leave as is and explore clicking the tabs above.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="wrap">
			<form id="formDeparture" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="tabName" value="departure_settings"/>
				<input type="hidden" name="contextMenuType" value="1"/>
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<tr>
							<td>
								<div>
									<button type="button"
										class="button button-primary"
										name="addDeparture" title="Add a new departure location">
										<i class="fa fa-plus"></i>
										<?php esc_html_e('Add new', 'calendarista') ?>
									</button>
									<span class="description"><?php esc_html_e('(Or right click on map and select add new location)', 'calendarista') ?></span>
								</div>
								<br>
								<p class="description"><?php esc_html_e('Click [add new] to create a selection list of predefined location', 'calendarista') ?></p>
							</td>
						</tr>
						<?php /*
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="fromPlacesPreload" <?php echo $this->map->fromPlacesPreload ? 'checked' : '' ?>><?php esc_html_e('Preload all locations on map', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						*/?>
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
			calendarista.departure = function(options){
				this.init(options);
			};
			calendarista.departure.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.departure({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
	<?php
	}
}