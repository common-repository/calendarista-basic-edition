<?php
class Calendarista_WaypointTemplate extends Calendarista_ViewBase{
	public $waypoints;
	public $map;
	public $waypointIndex = 0;
	public $createNew = true;
	function __construct(){
		parent::__construct(false, false, 'calendarista-places');
		new Calendarista_WaypointController(
			array($this, 'createdNotification')
			, array($this, 'updatedNotification')
		);
		if($this->selectedProjectId !== -1){
			$mapRepo = new Calendarista_MapRepository();
			$this->map = $mapRepo->readByProject($this->selectedProjectId);
			if(isset($this->map) && isset($this->map->id)){
				$this->createNew = false;
			}
			$waypointRepo = new Calendarista_WaypointRepository();
			$this->waypoints = $waypointRepo->readAll($this->map->id);
		}
		if(!isset($this->map)){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		if(!$this->waypoints){
			$this->waypoints = new Calendarista_Waypoints();
		}
		$this->render();
	}
	public function render(){
	?>
		<div class="wrap">
			<p class="description">
				<?php esc_html_e('Allow customer to create waypoints en route to their destination', 'calendarista') ?>
			</p>
			<form id="form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="tabName" value="waypoint_settings"/>
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="secondary_controller" value="waypoint" />
				<input type="hidden" name="id" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
				<input type="hidden" name="mapId" value="<?php echo $this->map->id ?>" />
				<input type="hidden" name="waypointFieldNames" value=""/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<p class="description"><?php esc_html_e('The number of waypoints a user can add between departure and destination. Your customers will be able to add up to 23 waypoints, this is a restriction set by google.', 'calendarista'); ?></p>
				<table class="form-table">
					<tbody>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="enableWaypointButton" <?php echo $this->map->enableWaypointButton ? 'checked' : '' ?>><?php esc_html_e('Enable adding one or more waypoints', 'calendarista') ?>
								  </label>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label>
										<?php esc_html_e('Icon URL', 'calendarista') ?> 
									</label>
								</div>
								<div>
									<input type="hidden"  
											name="waypointMarkerIconUrl"
											value="<?php echo $this->map->waypointMarkerIconUrl ?>"/>
									<div data-calendarista-preview-icon="waypointMarkerIconUrl" class="preview-icon" 
									style="<?php echo $this->map->waypointMarkerIconUrl ?
															sprintf('background-image: url(%s)', esc_url($this->map->waypointMarkerIconUrl)) : ''?>">
									</div>
									<button type="button" 
										class="button button-primary"
										data-calendarista-preview-icon="waypointMarkerIconUrl"
										name="iconUrlRemove">
										<i class="fa fa-remove"></i>
									</button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label for="waypointMarkerIconWidth">
										<?php esc_html_e('Icon width', 'calendarista') ?> 
									</label>
								</div>
								<div>
									<input type="text" 
										class="woald_parsley_validated small-text"
										data-parsley-trigger="change"
										data-parsley-type="digits"
										value="<?php echo $this->map->waypointMarkerIconWidth ?>"
										name="waypointMarkerIconWidth"
										id="waypointMarkerIconWidth"/>
										<p class="description"><?php esc_html_e('A value of 0 will size the icon automatically', 'calendarista') ?></p>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<label for="waypointMarkerIconHeight">
										<?php esc_html_e('Icon height', 'calendarista') ?>
									</label>
								</div>
								<div>
									<input type="text" 
										class="woald_parsley_validated small-text"
										data-parsley-trigger="change"
										data-parsley-type="digits"
										value="<?php echo $this->map->waypointMarkerIconHeight ?>"
										name="waypointMarkerIconHeight"
										id="waypointMarkerIconHeight"/>
										<p class="description"><?php esc_html_e('A value of 0 will size the icon automatically', 'calendarista') ?></p>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<?php /*
				<table class="form-table woald-waypoints-placeholder">
					<tbody>
						<?php foreach($this->waypoints as $waypoint):?>
						<tr>
							<td>
								#<?php echo ++$this->waypointIndex ?>&nbsp;
								<input type="hidden" name="waypointAddress<?php echo $this->waypointIndex ?>" value="<?php echo $waypoint->address ?>" />
								<input type="hidden" name="waypointLat<?php echo $this->waypointIndex ?>" value="<?php echo $waypoint->lat ?>" />
								<input type="hidden" name="waypointLng<?php echo $this->waypointIndex ?>" value="<?php echo $waypoint->lng ?>" />
								<input type="text" 
									data-parsley-errors-container=".waypoint<?php echo $this->waypointIndex ?>-error-container" 
									name="waypoint"
									value="<?php echo $waypoint->address ?>"
									data-woald-address="waypointAddress<?php echo $this->waypointIndex ?>" 
									data-woald-lat="waypointLat<?php echo $this->waypointIndex ?>" 
									data-woald-lng="waypointLng<?php echo $this->waypointIndex ?>"  />
								<button type="button"  class="button button-primary" name="waypointsearch">
									<i class="fa fa-search"></i>
								</button>
								<button type="button" title="Remove this stop" class="button button-primary" name="waypointremove" data-woald-id="<?php echo $this->waypointIndex ?>">
									<i class="fa fa-minus"></i>
								</button>
								<div class="waypoint<?php echo $this->waypointIndex ?>-error-container"></div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<table class="form-table">
					<tbody>
						<tr>
							<td>
									<button type="button"
										class="button button-primary"
										name="addwaypoint" title="<?php esc_html_e('Add stop', 'calendarista') ?>">
										<i class="fa fa-plus"></i>
									</button>
								<p class="description"><?php esc_html_e('By using the add button above, one or more waypoint fields will be added under the departure field. Fill the fields to display them with prefilled values. Hint: You can also right click on the map to prefill the fields.', 'calendarista') ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				*/?>
				<?php /*
				<table class="form-table">
					<tbody>
						<tr>
							<td>
								  <label>
									<input type="checkbox" name="optimizeWayPoints" <?php echo $this->map->optimizeWayPoints ? 'checked' : ''?>>Optimize stops
								  </label>
							</td>
						</tr>
					</tbody>
				</table>
				*/?>
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
	<?php
	}
}