<?php
class Calendarista_DirectionGeneralSettingTemplate extends Calendarista_ViewBase{
	public $createNew = true;
	public $map;
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
			<p class="description">
				<?php esc_html_e('General settings', 'calendarista') ?>
			</p>
			<form id="form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="tabName" value="general_settings"/>
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<?php /*<tr>
							<td>
								<div>
									<label for="queryLimitTimeout">
										<?php esc_html_e('Geolocate query limit', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
											class="woald_parsley_validated regular-text"
											name="queryLimitTimeout"
											value="<?php echo $this->map->queryLimitTimeout ?>"
											data-parsley-trigger="change"
											data-parsley-type="digits" 
											id="queryLimitTimeout"/>
									<p class="description">Timeout value in milliseconds</p>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableDirectionButton" <?php echo $this->map->enableDirectionButton ? 'checked' : '' ?>><?php esc_html_e('Display direction option', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label for="zoom">
										<?php esc_html_e('Zoom', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
										class="woald_parsley_validated regular-text"
										data-parsley-trigger="change"
										data-parsley-type="digits" 
										data-parsley-max="19"
										value="<?php echo $this->map->zoom ?>"
										name="zoom"
										id="zoom"/>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label for="panToZoom">
										<?php esc_html_e('Pan to zoom', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
										class="woald_parsley_validated regular-text"
										data-parsley-trigger="change"
										data-parsley-type="digits" 
										data-parsley-max="19"
										value="<?php echo $this->map->panToZoom ?>"
										name="panToZoom"
										id="panToZoom"/>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<p class="description"><?php esc_html_e('Allows the user to select a travel distance and journey duration between multiple origins and destinations', 'calendarista') ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="driving" <?php echo $this->map->driving ? 'checked' : '' ?>>
								<input type="text" name="labelDriving" value="<?php esc_html_e('Driving', 'calendarista') ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="walking" <?php echo $this->map->walking ? 'checked' : '' ?>>
								<input type="text" name="labelWalking" value="<?php esc_html_e('Walking', 'calendarista') ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="bicycling" <?php echo $this->map->bicycling ? 'checked' : '' ?>>
								<input type="text" name="labelBicycling" value="<?php esc_html_e('Bicycling', 'calendarista') ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="transit" <?php echo $this->map->transit ? 'checked' : '' ?>>
								<input type="text" name="labelTransit" value="<?php esc_html_e('Transit', 'calendarista') ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label>
										<?php esc_html_e('Default Travel mode', 'calendarista') ?>
									</label>
								</div>
								<div>
									<select name="defaultTravelMode">
										<option value="driving"  <?php echo $this->map->defaultTravelMode === 'driving' ? 'selected' : '' ?>><?php esc_html_e('Driving', 'calendarista') ?></option>
										<option value="walking" <?php echo $this->map->defaultTravelMode === 'walking' ? 'selected' : '' ?>><?php esc_html_e('Walking', 'calendarista') ?></option>
										<option value="bicycling" <?php echo $this->map->defaultTravelMode === 'bicycling' ? 'selected' : '' ?>><?php esc_html_e('Bicycling', 'calendarista') ?></option>
										<option value="transit" <?php echo $this->map->defaultTravelMode === 'transit' ? 'selected' : '' ?>><?php esc_html_e('Transit', 'calendarista') ?></option>
									</select>
								</div>
							</td>
						</tr>
						*/?>
						<tr>
							<td>
								<div>
									<label for="theme">
										<?php esc_html_e('Styled Maps', 'calendarista') ?>
									</label>
								</div>
								<div>
									<select
										name="styledMaps"
										id="styledMaps">
										<option value="" <?php echo $this->map->styledMaps ? 'selected' : '' ?>><?php esc_html_e('Select a theme', 'calendarista') ?></option>
									</select>
									<p class="description"><?php esc_html_e('Styled maps allow you to customize the presentation of the standard Google base maps, changing the visual display of such elements as roads, parks, and built-up areas', 'calendarista') ?></p>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label class="col-xl-4 form-control-label" for="mapHeight">
										<?php esc_html_e('Map Height', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
										placeholder="100"
										class="woald_parsley_validated"
										data-parsley-errors-container=".map-height-error-container"
										data-parsley-trigger="change"
										data-parsley-type="digits"
										value="<?php echo $this->map->mapHeight ?>"
										name="mapHeight"
										id="mapHeight"/>
										<div class="map-height-error-container"></div>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label class="col-xl-4 form-control-label" for="unitType">
										<?php esc_html_e('Unit', 'calendarista') ?>
									</label>
								</div>
								<div>
								<select
									name="unitType"
									id="unitType">
									<option value="0" <?php echo $this->map->unitType === 0 ? 'selected' : '' ?>><?php esc_html_e('KM', 'calendarista') ?></option>
									<option value="1" <?php echo $this->map->unitType === 1 ? 'selected' : '' ?>><?php esc_html_e('MILE', 'calendarista') ?></option>
								  </select>
								</div>
							</td>
						</tr>
						<?php if($this->map->enableDestinationField): ?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableDirection"  <?php echo $this->map->enableDirection ? 'checked' : '' ?>><?php esc_html_e('Enable direction', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<?php endif; ?>
						<?php if($this->map->enableDestinationField && !in_array($this->map->costMode, array(Calendarista_CostMode::DEPARTURE_AND_DESTINATION, Calendarista_CostMode::DEPARTURE_ONLY))): ?>
						<tr>
							<td>
								<label>
									<input type="checkbox" name="highway" <?php echo $this->map->highway ? 'checked' : '' ?>><?php esc_html_e('Avoid Highway', 'calendarista') ?>
								</label>
							</td>
						</tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="toll" <?php echo $this->map->toll ? 'checked' : '' ?>><?php esc_html_e('Avoid Toll', 'calendarista') ?> 
								  </label>
							</td>
						</tr>
						<tr>
							<td>
							  <label>
								<input type="checkbox" name="traffic" <?php echo $this->map->traffic ? 'checked' : '' ?>><?php esc_html_e('Show Traffic', 'calendarista') ?>
							  </label>
							</td>
						</tr>
						<tr><th><hr></th></tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableHighway" <?php echo $this->map->enableHighway ? 'checked' : '' ?>><?php esc_html_e('Display highway option', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableTolls" <?php echo $this->map->enableTolls ? 'checked' : '' ?>><?php esc_html_e('Display tolls option', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableTraffic" <?php echo $this->map->enableTraffic ? 'checked' : '' ?>><?php esc_html_e('Display traffic option', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<?php endif; ?>
						<?php if($this->map->costMode !== Calendarista_CostMode::DISTANCE): ?>
						<?php if($this->map->enableDestinationField):?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableDistance" <?php echo $this->map->enableDistance ? 'checked' : '' ?>><?php esc_html_e('Enable distance', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<?php endif; ?>
						<?php else: ?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="_enableDistance" disabled checked><?php esc_html_e('Enable distance', 'calendarista') ?>
									<input type="hidden" name="enableDistance" value="1">
								  </label>
							</td>
						</tr>
						<?php endif;?>
						<?php if($this->map->enableDestinationField):?>
						<tr>
							<td>
							  <label>
								<input type="checkbox" name="enableDistanceInfo" <?php echo $this->map->enableDistanceInfo ? 'checked' : '' ?>><?php esc_html_e('Display distance details by location', 'calendarista') ?>
							  </label>
							</td>
						</tr>
						<?php endif; ?>
						<?php if(!in_array($this->map->costMode, array(Calendarista_CostMode::DEPARTURE_AND_DESTINATION, Calendarista_CostMode::DEPARTURE_ONLY))): ?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableFindMyPosition" <?php echo $this->map->enableFindMyPosition ? 'checked' : '' ?>><?php esc_html_e('Display a find my position button', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<?php endif; ?>
						<?php if($this->map->enableDestinationField):?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="showDirectionStepsInline" <?php echo $this->map->showDirectionStepsInline ? 'checked' : '' ?>><?php esc_html_e('Show directions inline on map', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="showInfoWindow" <?php echo $this->map->showInfoWindow ? 'checked' : '' ?>><?php esc_html_e('Show info window', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableScrollWheel" <?php echo $this->map->enableScrollWheel ? 'checked' : '' ?>><?php esc_html_e('Enable scroll wheel', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<?php if(!in_array($this->map->costMode, array(Calendarista_CostMode::DEPARTURE_AND_DESTINATION, Calendarista_CostMode::DEPARTURE_ONLY))): ?>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="draggableMarker" <?php echo $this->map->draggableMarker ? 'checked' : '' ?>><?php esc_html_e('Draggable marker', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableContextMenu" <?php echo $this->map->enableContextMenu ? 'checked' : '' ?>><?php esc_html_e('Enable Context Menu (Right click on map)', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label for="departureContextMenuLabel">
										<?php esc_html_e('Departure context menu label', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
											class="regular-text"
											placeholder="<?php esc_html_e('Departure', 'calendarista') ?>"
											name="departureContextMenuLabel"
											id="departureContextMenuLabel"
											value="<?php echo $this->map->departureContextMenuLabel ?>"/>
								</div>
							</td>
						</tr>
						<?php if($this->map->enableDestinationField):?>
						<tr>
							<td>
								<div>
									<label for="destinationContextMenuLabel">
										<?php esc_html_e('Destination context menu label', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
											class="regular-text"
											placeholder="<?php esc_html_e('Destination', 'calendarista') ?>"
											name="destinationContextMenuLabel"
											id="destinationContextMenuLabel"
											value="<?php echo $this->map->destinationContextMenuLabel ?>"/>
								</div>
							</td>
						</tr>
						<?php if($this->map->enableWaypointButton): ?>
						<tr>
							<td>
								<div>
									<label for="waypointContextMenuLabel">
										<?php esc_html_e('Waypoint context menu label', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
											class="regular-text"
											placeholder="<?php esc_html_e('Waypoint', 'calendarista') ?>"
											name="waypointContextMenuLabel"
											id="waypointContextMenuLabel"
											value="<?php echo $this->map->waypointContextMenuLabel ?>"/>
								</div>
							</td>
						</tr>
						<?php endif; ?>
						<?php endif; ?>
						<?php endif; ?>
						<tr>
							<td>
								  <label>
									<input name="displayMap" type="hidden" value="0">
									<input type="checkbox" name="displayMap"  <?php echo $this->map->displayMap ? 'checked' : '' ?>><?php esc_html_e('Display Map', 'calendarista') ?>
								  </label>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<?php if(!$this->createNew):?>
					<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>"
						<?php echo $this->selectedProjectId === -1 ? 'disabled' : ''?>>
					<?php else:?>
					<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Create map', 'calendarista') ?>" 
						<?php echo $this->selectedProjectId === -1 ? 'disabled' : ''?>>
					<?php endif;?>
				</p>
			</form>
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.directionGeneralSetting = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.directionGeneralSetting.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.requestUrl = options['requestUrl'];
				this.baseUrl = options['baseUrl'];
				this.$enableDirection = $('input[name="enableDirection"]');
				this.$showDirectionStepsInline = $('input[name="showDirectionStepsInline"]');
				this.$enableDistance = $('input[name="enableDistance"]');
				this.$enableDistanceInfo = $('input[name="enableDistanceInfo"]');
				this.$enableDirection.on('change', function(){
					if(context.$showDirectionStepsInline.length > 0 && context.$showDirectionStepsInline[0].checked){
						context.$showDirectionStepsInline.prop('checked', false);
					}
				});
				this.$showDirectionStepsInline.on('change', function(){
					if(this.checked){
						context.$enableDirection.prop('checked', true);
					}
				});
				this.$enableDistance.on('change', function(){
					if(context.$enableDistanceInfo.length > 0 && context.$enableDistanceInfo[0].checked){
						context.$enableDistanceInfo.prop('checked', false);
					}
				});
				this.$enableDistanceInfo.on('change', function(){
					if(this.checked && !context.$enableDistance.is(':disabled')){
						context.$enableDistance.prop('checked', true);
					}
				});
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.directionGeneralSetting({
				'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
				, 'baseUrl': '<?php echo $this->baseUrl ?>'
		});
		</script>
	<?php
	}
}