<?php
class Calendarista_MapAutocompleteRestrictTemplate extends Calendarista_ViewBase{
	public $map;
	function __construct( ){
		parent::__construct(false, false, 'calendarista-places');
		if($this->selectedProjectId !== -1){
			$mapRepo = new Calendarista_MapRepository();
			$this->map = $mapRepo->readByProject($this->selectedProjectId);
		}
		if(!isset($this->map)){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		$this->render();
	}
	public function render(){
	?>
	<div id="woald_creator">
		<div id="progressModal" title="<?php esc_html_e('Activity progress') ?>">
			<div id="progress-bar"></div>
			<p><span class="progress-report"></span></p>
		</div>
		<div class="wrap">
			<p class="description">
				<?php esc_html_e('Display the area on the map', 'calendarista') ?>
			</p>
			<form id="form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="tabName" value="map_restrict"/>
				<input type="hidden" name="contextMenuType" value="0"/>
				<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<tr>
							<td>
								<div>
									<label for="regionLatLng">
										<?php esc_html_e('Address', 'calendarista')?>
									</label>
								</div>
								<div>
									<input type="hidden" id="regionLat" name="restrictLat" value="<?php echo  esc_attr($this->map->restrictLat) ?>"/>
									<input type="hidden" id="regionLng" name="restrictLng" value="<?php echo  esc_attr($this->map->restrictLng) ?>"/>
									<input type="text" 
										class="woald_parsley_validated"
										data-parsley-errors-container=".region-lat-lng-error-container"
										data-parsley-required="true"
										id="regionLatLng"
										name="restrictAddress"
										data-parsley-required="true"
										value="<?php echo  esc_attr($this->map->restrictAddress) ?>"/>
											<button type="button" 
												class="button-primary"
												name="search">
												<i class="fa fa-search"></i>
											</button>
											<button type="button" 
												class="button-primary"
												name="mypos">
												<i class="fa fa-dot-circle"></i>
											</button>
								</div>
								<div class="region-lat-lng-error-container"></div>
								<p class="description">
									<?php esc_html_e('If this value is not provided, no map is displayed', 'calendarista') ?>
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label for="restrictRadius">
										<?php esc_html_e('Restrict Radius', 'calendarista') ?>
									</label>
								</div>
								<input
									name="restrictRadius"
									id="restrictRadius"
									data-parsley-required="true"
									data-parsley-type="digits"
									value="<?php echo esc_attr($this->map->restrictRadius) ?>">
								<p class="description"><?php esc_html_e('The radius of the area used for prediction biasing. The radius needs to be in meters', 'calendarista') ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<p>
					<input type="submit" 
							name="calendarista_update" 
							class="button button-primary" 
							value="<?php esc_html_e('Save changes', 'calendarista') ?>">
					<input type="submit" 
							name="calendarista_update" 
							class="button button-primary" 
							value="<?php esc_html_e('Reset', 'calendarista') ?>"
							form="form2"
							<?php echo !$this->map->restrictAddress ? 'disabled' : ''?>>
				</p>
			</form>
			<form id="form2" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="tabName" value="map_restrict"/>
				<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
				<!-- reset -->
				<input type="hidden" name="restrictLat" value=""/>
				<input type="hidden" name="restrictLng" value=""/>
				<input type="hidden" name="restrictAddress" value=""/>
				<input type="hidden" name="restrictRadius" value=""/>
			</form>
		</div>
	</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.availabilityMap = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.availabilityMap.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.requestUrl = options['requestUrl'];
				this.$form = $('form[id="form1"]');
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		</script>
	<?php
	}
}