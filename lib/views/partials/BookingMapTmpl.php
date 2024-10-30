<?php
class Calendarista_BookingMapTmpl extends Calendarista_TemplateBase{
	public $map;
	public $mapOptions;
	public $departure;
	public $destination;
	public $enableOptions1;
	public $enableOptions2;
	public function __construct($stateBag = null){
		parent::__construct($stateBag);
		$mapRepo = new Calendarista_MapRepository();
		$this->map = $mapRepo->readByProject($this->projectId);
		if(!$this->map){
			return;
		}
		$this->mapOptions = $this->map->toArray();
		$this->mapOptions['id'] = $this->uniqueId;
		$this->setDeparture();
		$this->setDestination();
		$this->setWaypoints();
		if($this->map->enableDepartureField || $this->map->enableDestinationField){
			$fromPlaces = array();
			$toPlaces = array();
			$placeRepo = new Calendarista_PlaceRepository();
			$places = $placeRepo->readAll($this->map->id);
			foreach($places as $place){
				if($place->placeType === Calendarista_PlaceType::DEPARTURE){
					array_push($fromPlaces, $place->toArray());
				}else{
					array_push($toPlaces, $place->toArray());
				}
			}
			if($this->map->enableDepartureField){
				$this->mapOptions['fromPlaces'] = $fromPlaces;
			}
			if($this->map->enableDestinationField){
				$this->mapOptions['toPlaces'] = $toPlaces;
			}
		}
		$this->mapOptions['directionEmptyError'] = $this->stringResources['DIRECTION_EMPTY_ERROR'];
		$this->mapOptions['directionDataUnavailable'] = $this->stringResources['NO_DIRECTION_ERROR'];
		$this->mapOptions['departurePlaceholder'] = $this->stringResources['MAP_DEPARTURE_PLACEHOLDER'];
		$this->mapOptions['destinationPlaceholder'] = $this->stringResources['MAP_DESTINATION_PLACEHOLDER'];
		$this->mapOptions['defaultFromSelectionCaption'] = $this->stringResources['MAP_DEPARTURE_DEFAULT_LIST_ITEM'];
		$this->mapOptions['defaultToSelectionCaption'] = $this->stringResources['MAP_DESTINATION_DEFAULT_LIST_ITEM'];
		$this->mapOptions['decimalPrecision'] = Calendarista_MoneyHelper::getCurrencyPrecision();
		$this->mapOptions['excludedComboPlaces'] = $this->getExcludedComboPlaces();
		if($this->map->enableTravelMode() || $this->map->enableDirectionButton || $this->map->enableTraffic){
			$this->enableOptions1 = true;
		}
		if($this->map->enableHighway || $this->map->enableTolls || $this->map->enableTraffic){
			$this->enableOptions2 = true;
		}
		$this->render();
	}
	public function getExcludedComboPlaces(){
		$repo = new Calendarista_PlaceAggregateCostRepository();
		$aggregates = $repo->readAll($this->map->id);
		$result = array();
		foreach($aggregates as $aggregate){
			if(!(int)$aggregate->exclude){
				continue;
			}
			array_push($result, sprintf('%d,%d', (int)$aggregate->departurePlaceId, (int)$aggregate->destinationPlaceId));
		}
		return $result;
	}
	public function setDeparture(){
		$address = $this->getViewStateValue('fromAddress');
		$lat = $this->getViewStateValue('fromLat');
		$lng = $this->getViewStateValue('fromLng');
		if($address){
			$address = htmlspecialchars($address, ENT_QUOTES);
			$result = array('address'=>$address, 'lat'=>$lat, 'lng'=>$lng);
			$this->mapOptions['fromAddress'] = $address;
			$this->mapOptions['fromLat'] = $lat;
			$this->mapOptions['fromLng'] = $lng;
			$this->departure = wp_json_encode($result);
		}
		return null;
	}
	public function setDestination(){
		$address = $this->getViewStateValue('toAddress');
		$lat = $this->getViewStateValue('toLat');
		$lng = $this->getViewStateValue('toLng');
		if($address){
			$address = htmlspecialchars($address, ENT_QUOTES);
			$result = array('address'=>$address, 'lat'=>$lat , 'lng'=>$lng);
			$this->mapOptions['toAddress'] = $address;
			$this->mapOptions['toLat'] = $lat;
			$this->mapOptions['toLng'] = $lng;
			$this->destination = wp_json_encode($result);
		}
		return null;
	}
	public function setWaypoints(){
		if($this->map->enableDepartureField && $this->map->enableDestinationField){
			$waypoints = $this->getViewStateValue('waypoints');
			if($waypoints && is_array($waypoints)){
				$result = null;
				foreach($waypoints as $w){
					$waypoint = (array)$w;
					if(!$result){
						$result = array();
					}
					array_push($result, array(
						'address'=>htmlspecialchars($waypoint['address'], ENT_QUOTES)
						, 'lat'=>$waypoint['lat']
						, 'lng'=>$waypoint['lng']
					));
				}
				$this->mapOptions['waypoints'] = $result;
			}else{
				$waypointRepo = new Calendarista_WaypointRepository();
				$result = $waypointRepo->readAll($this->map->id);
				if($result && $result->count() > 0){
					$this->mapOptions['waypoints'] = $result->toArray();
				}
			}
		}
	}
	public function render(){
	?>
	<input type="hidden" name="departure" value='<?php echo esc_attr($this->departure) ?>'/>
	<input type="hidden" name="destination" value='<?php echo esc_attr($this->destination) ?>'/>
	<input type="hidden" name="distance" value="<?php echo $this->getViewStateValue('distance'); ?>"/>
	<input type="hidden" name="duration" value="<?php echo $this->getViewStateValue('duration'); ?>"/>
	<input type="hidden" name="unit" value="<?php echo (int)$this->map->unitType === 0 ? 'km' : 'miles'; ?>"/>
	<input type="hidden" name="waypoints" value="<?php echo isset($this->mapOptions['waypoints']) ? wp_json_encode($this->mapOptions['waypoints']) : '' ?>"/>
	<input type="hidden" name="fromPlaceId" value="<?php echo $this->getViewStateValue('fromPlaceId'); ?>"/>
	<input type="hidden" name="toPlaceId" value="<?php echo $this->getViewStateValue('toPlaceId'); ?>"/>
	<div class="woald-container">
		<div class="woald-controls">
			<div class="woald-content">
				<div class="col-xl-12 woald-photo-background">
					<div class="woald-content-inner">
						<?php if($this->enableOptions1):?>
						<div class="form-group woald-options1-container">
							<?php if($this->map->enableDirectionButton):?>
							<div class="btn-group woald-button-right-margin" data-toggle="buttons">
							  <label class="calendarista-typography--caption1 woald-direction" title="Show direction">
								<input type="checkbox" name="direction" data-parsley-excluded="true"><i class="fa fa-road"></i>
							  </label>
							  <label class="calendarista-typography--caption1woald-reverse" title="Reverse direction">
								<input type="checkbox" name="reverse" data-parsley-excluded="true"><i class="fa fa-sort"></i>
							  </label>
							</div>
							<?php endif; ?>
							<?php if($this->map->enableTravelMode()):?>
							<div class="btn-group woald-travelmode">
							  <button type="button" class="btn btn-outline-secondary calendarista-typography--button woald-travelmode-heading" data-parsley-excluded="true"><?php echo $this->map->getDefaultTravelModeLabel(); ?></button>
							  <button type="button" class="btn btn-outline-secondary calendarista-typography--button" data-toggle="dropdown" aria-expanded="false" data-parsley-excluded="true">
								<span class="caret"></span>
								<span class="sr-only">Toggle Dropdown</span>
							  </button>
							  <ul class="dropdown-menu woald-travelmode-list" role="menu">
								<?php foreach($this->map->travelModeList as $travelMode):?>
								<li>
									<a href="#" data-woald-drivingmode="<?php echo $travelMode['name']?>">
										<?php echo $travelMode['label']?>
										<?php if($this->map->defaultTravelMode === $travelMode['name']):?>
										<i class="fa fa-ok-sign calendarista-align-right"></i>
										<?php endif; ?>
									</a>
								</li>
								<?php endforeach; ?>
							  </ul>
							</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<?php if($this->enableOptions2):?>
						<div class="form-group form-inline woald-options2-container">
							<?php if($this->map->enableHighway):?>
							 <div class="checkbox">
								<label class="woald-inline-checkbox calendarista-typography--caption1">
									<input type="checkbox" name="highway" data-parsley-excluded="true"><?php esc_html_e('Avoid Highways', 'calendarista')?>
								</label>
							</div>
							<?php endif; ?>
							<?php if($this->map->enableTolls):?>
							<div class="checkbox">
							  <label class="woald-inline-checkbox calendarista-typography--caption1">
								<input type="checkbox" name="toll" data-parsley-excluded="true"><?php esc_html_e('Avoid Tolls', 'calendarista')?>
							  </label>
							</div>
							<?php endif; ?>
							<?php if($this->map->enableTraffic):?>
							<div class="checkbox">
							  <label class="woald-inline-checkbox calendarista-typography--caption1">
								<input type="checkbox" name="traffic" data-parsley-excluded="true"><?php esc_html_e('Show Traffic', 'calendarista')?>
							  </label>
							</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<?php if($this->map->enableDepartureField): ?>
						<div class="form-group woald-departure-field-container">
							<label class="form-control-label calendarista-typography--caption1">
								<?php echo esc_html($this->stringResources['MAP_DEPARTURE_LABEL']); ?>
							</label>
							<div class="<?php echo $this->map->enableFindMyPosition ? 'input-group' : '' ?>">
								<input type="text" 
									class="woald_parsley_validated form-control calendarista-typography--caption1" 
									data-parsley-trigger="change" 
									data-parsley-errors-container=".fromlatlng-error-container" 
									data-parsley-required="true"
									name="fromLatLng" />
								<?php if($this->map->enableFindMyPosition):?>
									<button type="button" 
										class="btn btn-outline-secondary calendarista-typography--button" 
										title="Find my position" 
										name="mypos">
										<i class="fa fa-dot-circle"></i>
									</button>
								<?php endif; ?>
							</div>
							<div class="fromlatlng-error-container calendarista-typography--caption1"></div>
						</div>
						<?php endif; ?>
						<div class="woald-waypoints-placeholder"></div>
						<?php if($this->map->enableDestinationField):?>
						<div class="form-group woald-destination-field-container">
							<label class="form-control-label calendarista-typography--caption1">
								<?php echo esc_html($this->stringResources['MAP_DESTINATION_LABEL']); ?>
							</label>
							<input type="text" 
								class="woald_parsley_validated form-control calendarista-typography--caption1" 
								data-parsley-trigger="change" 
								data-parsley-errors-container=".tolatlng-error-container" 
								data-parsley-required="true"
								name="toLatLng">
							<div class="tolatlng-error-container calendarista-typography--caption1"></div>
						</div>
						<?php endif; ?>
						<?php if($this->map->enableWaypointButton):?>
						<div class="form-group  calendarista-row-single woald-waypoint-add-button-container">
							<div class="calendarista-align-right">
								<button type="button" 
									class="btn btn-outline-secondary calendarista-typography--button woald-button-right-margin" 
									name="add">
									<?php echo esc_html($this->stringResources['MAP_ADD_WAYPOINT']); ?>
								</button>
							</div>
							<div class="clearfix"></div>
						</div>
						<?php endif;?>
						<?php if($this->map->enableDistanceInfo):?>
						<div class="form-group woald-distance-placeholder"></div>
						<?php endif;?>
						<div class="form-group calendarista-row-single calendarista-typography--caption1 woald-total-distance-container hide">
								<div class="form-control-static calendarista-align-right">
									<span class="woald-total-distance"></span>
								</div>
							<div class="clearfix"></div>
						</div>
						<div class="form-group calendarista-row-single woald-error-panel-container hide">
								<div class="woald-error-panel collapse out">
									<div class="alert alert-danger calendarista-typography--caption1" role="alert">
										<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
										<span class="woald-error-container calendarista-typography--caption1"></span>
									</div>
								</div>
						</div>
						<div class="form-group calendarista-row-single woald-direction-panel-container hide">
								<div class="woald-direction-panel collapse out">
								</div>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="clearfix"></div>
			<div class="btn-group woald-knobs">
				<button type="button" class="btn btn-outline-secondary calendarista-typography--button woald-zoom-in-button" title="Zoom in"><i class="fa fa-plus"></i></button>
				<button type="button" class="btn btn-outline-secondary calendarista-typography--button woald-zoom-out-button" title="Zoom out"><i class="fa fa-minus"></i></button>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="woald-map-placeholder">
			<div class="col-xl-12 calendarista-row-single">
				<?php if($this->map->enableContextMenu):?>
				<div class="woald-map-hint form-text text-muted"><?php echo esc_html($this->stringResources['MAP_HINT']) ?></div>
				<?php endif; ?>
				<div class="woald-map">
					<div class="woald-map-canvas"></div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="col-xl-12 calendarista-row-single">
			<div id="spinner_map_<?php echo $this->uniqueId ?>" class="spinner-border text-primary calendarista-invisible" role="status">
			  <span class="sr-only"><?php echo esc_html($this->stringResources['MAP_LOADING'])?></span>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	(function(){
		"use strict";
		function init(){
			var gmaps = new Woald.gmaps(<?php echo wp_json_encode($this->mapOptions); ?>)
				, $nextButton;
			$nextButton = gmaps.$root.find('button[name="next"]');
			$nextButton.on('click', function(e){
				if(!Calendarista.wizard.isValid(gmaps.$root)){
					e.preventDefault();
					return false;
				}
				gmaps.unload();
			});
		}
		<?php if($this->notAjaxRequest):?>
		
		if (window.addEventListener){
		  window.addEventListener('load', onload, false); 
		} else if (window.attachEvent){
		  window.attachEvent('onload', onload);
		}
		function onload(e){
			init();
		}
		<?php else: ?>
		init();
		<?php endif; ?>
		
	})();
	</script>
<?php
	}
}