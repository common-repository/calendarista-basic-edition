(function(window, document){
	"use strict";
	var Woald = Woald || {};
	/**
		@description The createDelegate function is useful when setting up an event handler to point 
		to an object method that must use the this pointer within its scope.
	*/
	Woald.createDelegate = function (instance, method) {
		return function () {
			return method.apply(instance, arguments);
		};
	}
	
	/**
		@description Allows us to retain the this context and optionally pass an arbitrary list of parameters.
	*/
	Woald.createCallback = function (method, context, params) {
		return function() {
			var l = arguments.length;
			if (l > 0) {
				var args = [];
				for (var i = 0; i < l; i++) {
					args[i] = arguments[i];
				}
				args[l] = params;
				return method.apply(context || this, args);
			}
			return method.call(context || this, params);
		}
	}
	if(!window['Woald']){
		window['Woald'] = Woald;
	}
}(window, document));
(function(window, document, google, Woald, $, accounting, wp){
	"use strict";
	Woald.gmaps = function(options){
		this.$window = $(window);
		this.options = options || {};
		this.ajaxUrl = wp.url;
		this.nonce = wp.nonce;
		this.id = 0;
		this.infoWindowIndex = 0;
		this.queryLimitTimeout = typeof(options['queryLimitTimeout']) === 'number' ? options['queryLimitTimeout'] : 200;
		this.traffic = typeof(options['traffic']) === 'boolean' ? options['traffic'] : false;
		this.avoidHighways = typeof(options['highway']) === 'boolean' ? options['highway']  : false;
		this.avoidTolls = typeof(options['toll']) === 'boolean' ? options['toll'] : false;
		this.enableDirection = typeof(options['enableDirection']) === 'boolean' ? options['enableDirection'] : true;
		this.enableDirectionButton = typeof(options['enableDirectionButton']) === 'boolean' ? options['enableDirectionButton'] : true;
		this.showDirectionStepsInline =  typeof(options['showDirectionStepsInline']) === 'boolean' ? options['showDirectionStepsInline'] : false;
		this.showInfoWindow = typeof(options['showInfoWindow']) === 'boolean' ? options['showInfoWindow'] : false;
		this.enableDirectionPanel = typeof(options['enableDirectionPanel']) === 'boolean' ? options['enableDirectionPanel'] : true;
		this.enableScrollWheel = typeof(options['enableScrollWheel']) === 'boolean' ? options['enableScrollWheel'] : true;
		this.displayMap = typeof(options['displayMap']) === 'boolean' ? options['displayMap'] : true;
		if(this.showDirectionStepsInline){
			this.enableDirectionPanel = false;
		}
		this.draggableMarker = typeof(options['draggableMarker']) === 'boolean' ? options['draggableMarker'] : true;
		this.selectedTravelMode = options['selectedTravelMode'] ? options['selectedTravelMode'] : 'driving';
		this.styles = options['styledMaps'] ? options['styledMaps'] : null;
		this.backgroundGradient = options['backgroundGradient'] ? options['backgroundGradient'] : null;
		this.enableCollapseButton =  typeof(options['enableCollapseButton']) === 'boolean' ? options['enableCollapseButton'] : true;
		this.enableWaypointButton =  typeof(options['enableWaypointButton']) === 'boolean' ? options['enableWaypointButton'] : true;
		this.enableDepartureField =  typeof(options['enableDepartureField']) === 'boolean' ? options['enableDepartureField'] : true;
		this.enableDestinationField =  typeof(options['enableDestinationField']) === 'boolean' ? options['enableDestinationField'] : true;
		this.departurePlaceholder = options['departurePlaceholder'] ? options['departurePlaceholder'] : 'Departure';
		this.destinationPlaceholder = options['destinationPlaceholder'] ? options['destinationPlaceholder'] : 'Destination';
		this.fromPlacesPreload = typeof(options['fromPlacesPreload']) === 'boolean' ? options['fromPlacesPreload'] : false;
		this.toPlacesPreload = typeof(options['toPlacesPreload']) === 'boolean' ? options['toPlacesPreload'] : false;
		this.panToZoom = typeof(options['panToZoom']) === 'number' ? options['panToZoom'] : 0;
		this.enableContextMenu = typeof(options['enableContextMenu']) === 'boolean' ? options['enableContextMenu'] : true;
		this.waypointsMax = typeof(options['waypointsMax']) === 'number' ? options['waypointsMax'] : 8;
		//google.maps.UnitSystem.IMPERIAL/*miles*/
		this.unit = options['unitType'] || google.maps.UnitSystem.METRIC;
		this.km = this.unit === google.maps.UnitSystem.METRIC;
		this.miles = !this.km;
		this.defer = typeof(options['defer']) === 'boolean' ? options['defer'] : false;
		this.optimizeWaypoints = options['optimizeWaypoints'] || false;
		this.mapHeight = options['mapHeight'] ? options['mapHeight'] : null;
		/*stopover: If true, indicates that this waypoint is a stop between the origin and destination. 
		This has the effect of splitting the route into two*/
		this.stopover = options['stopover'] ? options['stopover'] : true;
		this.waypointMax = typeof(options['waypointMax']) === 'number' ? options['waypointMax'] : 23;
		this.costUnitType = typeof(options['costUnitType']) === 'number' ? options['costUnitType'] : google.maps.UnitSystem.METRIC;
		this.fromPlaces = options['fromPlaces'] ? options['fromPlaces'] : [];
		this.toPlaces = options['toPlaces'] ? options['toPlaces'] : [];
		this.waypoints = options['waypoints'] ? options['waypoints'] : [];
		this.defaultFromSelectionCaption = options['defaultFromSelectionCaption'] ? options['defaultFromSelectionCaption'] : 'Select a place';
		this.defaultToSelectionCaption = options['defaultToSelectionCaption'] ? options['defaultToSelectionCaption'] : 'Select a place';
		this.departureContextMenuLabel = options['departureContextMenuLabel'] ? options['departureContextMenuLabel'] : 'Departure';
		this.destinationContextMenuLabel = options['destinationContextMenuLabel'] ? options['destinationContextMenuLabel'] :'Destination';
		this.waypointContextMenuLabel = options['waypointContextMenuLabel'] ? options['waypointContextMenuLabel'] :'Waypoint';
		this.directionEmptyError = options['directionEmptyError'] ? options['directionEmptyError'] : 'Provide both a departure and arrival address above to view direction data.';
		this.directionDataUnavailable = options['directionDataUnavailable'] ? options['directionDataUnavailable'] : 'No routes available from/to the selected location.';
		this.decimalPoint = options['decimalPoint'] ? options['decimalPoint'] : '.';
		this.thousandsSep = options['thousandsSep'] ? options['thousandsSep'] : ',';
		this.decimalPrecision = options['decimalPrecision'] ? options['decimalPrecision'] : 2;
		this.excludedComboPlaces = options['excludedComboPlaces'] ? options['excludedComboPlaces'] : [];
		this.conversion = this.getConversion();
		this.waypointMarkerIconUrl = options['waypointMarkerIconUrl'] ? options['waypointMarkerIconUrl'] : null;
		this.waypointMarkerIconHeight = options['waypointMarkerIconHeight'] ? options['waypointMarkerIconHeight'] : null;
		this.waypointMarkerIconWidth = options['waypointMarkerIconWidth'] ? options['waypointMarkerIconWidth'] : null;
		this.distanceLabelText = options['distanceLabelText'] ? options['distanceLabelText'] : 'Distance';
		this.durationLabelText = options['durationLabelText'] ? options['durationLabelText'] : 'Duration';
		this.milesLabelText = options['milesLabelText'] ? options['milesLabelText'] : 'miles';
		this.kmLabelText = options['kmLabelText'] ? options['kmLabelText'] : 'km';
		this.nearbySearchKeyword = options['nearbySearchKeyword'] ? options['nearbySearchKeyword'] : null;
		this.nearbySearchTypes = options['nearbySearchTypes'] ? options['nearbySearchTypes'] : ''/*eg: establishment*/;
		this.unitLabelText = this.km ? this.kmLabelText : this.milesLabelText;
		//13 is the default zoom level. when locating on map, zoom is determined automatically based on bounds.
		this.zoom = typeof(options['zoom']) === 'number' ? options['zoom'] : 13;
		this.enableDistance  = typeof(options['enableDistance']) === 'boolean' ? options['enableDistance'] : true;
		this.distanceUpdated = options['distanceUpdated'] ? options['distanceUpdated'] : null;
		this.directionUpdated = options['directionUpdated'] ? options['directionUpdated'] : null;
		this.mapLoaded = options['mapLoaded'] ? options['mapLoaded'] : null;
		this.fromDefaultPlace = options['fromDefaultPlace'] ? options['fromDefaultPlace'] : null;
		this.toDefaultPlace = options['toDefaultPlace'] ? options['toDefaultPlace'] : null;
		this.preloadLocations = typeof(options['preloadLocations']) === 'boolean' ? options['preloadLocations'] : true;
		this.fromAddress = options['fromAddress'];
		this.fromLat = options['fromLat'];
		this.fromLng = options['fromLng'];
		this.toAddress = options['toAddress'];
		this.toLat = options['toLat'];
		this.toLng = options['toLng'];
		this.restrictLat = options['restrictLat'];
		this.restrictLng = options['restrictLng'];
		this.restrictRadius = options['restrictRadius'] ? parseInt(options['restrictRadius'], 10) : null;
		
		this.costSummaryRequest = 'calendarista_cost_summary_request' + options['projectId'];
		this.resizeDelegate = Woald.createDelegate(this, this.resize);
		this.unloadDelegate = Woald.createDelegate(this, this.unloadHandler);
		this.searchFields = [];
		if(this.defer){
			this.initDelegate = Woald.createDelegate(this, this.initialize);
			this.$window.on('load', this.initDelegate);
		}
		else{
			this.initialize();
		}
		this.$window.on('resize', this.resizeDelegate);
		this.$window.on('unload', this.unloadDelegate);
	}
	Woald.gmaps.contextMenu = {
		'departure' : 0
		, 'destination' : 1
		, 'waypoint' : 2
	}
	Woald.gmaps.collapseDirection = {
		'inside': 0
		, 'out': 1
	}
	Woald.gmaps.action = {
		'onPageLoad': 0
		, 'onPageLoaded': 1
	}
	Woald.gmaps.prototype.initialize = function(){
		var options = this.options
			, $controls
			, $gallery
			, unit
			, sep
			, $contentBeforeContainer
			, $contentAfterContainer
			, $contentEndContainer
			, defaultLatLng
			, i
			, genFlag
			, circle
			, opt
			, context = this;
		this.defaultLocation = {
			'address': options['regionAddress'],
			'lat': options['regionLat'] ? options['regionLat'] : 40.7588908,
			'lng': options['regionLng'] ? options['regionLng'] : -73.98484159999998,
			'infoWindowIcon': options['regionInfoWindowIcon'],
			'infoWindowDescription': options['regionInfoWindowDescription'],
			'markerIcon': options['regionMarkerIconUrl'],
			'markerIconWidth': options['regionMarkerIconWidth'],
			'markerIconHeight': options['regionMarkerIconHeight'],
			'showMarker': typeof(options['showMapMarker']) === 'boolean' ? options['showMapMarker'] : false
		};
		if(this.restrictLat && this.restrictLng){
			this.defaultLocation['lat'] = this.restrictLat;
			this.defaultLocation['lng'] = this.restrictLng;
		}
		defaultLatLng = new google.maps.LatLng(this.defaultLocation['lat'] ? this.defaultLocation['lat'] : 40.7588908, this.defaultLocation['lng'] ? this.defaultLocation['lng'] : -73.98484159999998);
		this.rootId = options['id'];
		this.$root = $('#' + this.rootId);
		this.$spinner = $('#spinner_map_' + this.rootId);
		this.$mapElement = this.$root.find('.woald-map-canvas');
		this.$controls = this.$root.find('.woald-knobs');
		this.$gallery = this.$root.find('.woald-gallery');
		this.$travelModeList = this.$root.find('.woald-travelmode-list a');
		this.$travelModeHeading = this.$root.find('.woald-travelmode-heading');
		for(i = 0; i < this.$travelModeList.length; i++){
			this.travelModeItemClickDelegate = Woald.createDelegate(this, this.travelModeItemClick);
			$(this.$travelModeList[i]).on('click', this.travelModeItemClickDelegate);
		}
		if(this.enableDirection){
			this.directionsService = new google.maps.DirectionsService();
			this.directionsDisplay = new google.maps.DirectionsRenderer({ 'draggable': this.draggableMarker, 'suppressMarkers' : this.showDirectionStepsInline});
		}
		if(!this.displayMap){
			this.$root.find('.woald-map-placeholder').hide();
		}
		if(this.restrictLat && this.restrictLng){
			circle = new google.maps.Circle({'radius': this.restrictRadius, 'center': new google.maps.LatLng(this.restrictLat, this.restrictLng)});
			this.bounds = circle.getBounds();
		}
		opt = {
			center: defaultLatLng
			, zoom: this.zoom
			, styles: this.styles
			, mapTypeId: google.maps.MapTypeId.ROADMAP
			, panControl: false
			, zoomControl: false
			, mapTypeControl: false
			, scaleControl: false
			, streetViewControl: false
			, overviewMapControl: false
			, scrollwheel: this.enableScrollWheel
			, clickableIcons: false
		};
		if(this.bounds){
			opt['restriction'] = {
			  'latLngBounds': this.bounds
			  , 'strictBounds': true
			};
		}
		this.map = new google.maps.Map(this.$mapElement[0], opt);
		if(this.enableContextMenu){
			this.mapContextMenuDelegate = Woald.createDelegate(this, this.mapContextMenuHandler);
			google.maps.event.addListener(this.map, 'rightclick', this.mapContextMenuDelegate);
			this.contextMenuExitDelegate = Woald.createDelegate(this, this.contextMenuExitHandler);
			google.maps.event.addListener(this.map, 'click', this.contextMenuExitDelegate);
			$(window.document).on('click', this.contextMenuExitDelegate);
		}
		this.tilesLoadedDelegate = Woald.createDelegate(this, this.tilesLoadedHandler);
		google.maps.event.addListener(this.map, 'tilesloaded', this.tilesLoadedDelegate);
		
		this.initControls(options);
		$controls = this.$controls.detach();
		this.map.controls[google.maps.ControlPosition.TOP_LEFT].push($controls[0]);
		$controls.removeClass('hide');
		
		this.progressIndicator('show');
		this.browserSupportFlag =  new Boolean(false);
		if(this.enableDistance){
			this.$distanceContainer.removeClass('hide');
		}
		this.setBackgroundGradient();
		
		if(this.mapHeight){
			this.$mapElement.css('height', this.mapHeight + 'px');
		}
		genFlag = this.genDropdownLists();
		if(this.$fromLatLng.attr('type') === 'text' && !genFlag['from']){
			this.preloadPlaces(this.$fromLatLng, this.$departureField);
		}
		if(this.$toLatLng.attr('type') === 'text' && !genFlag['to']){
			this.preloadPlaces(this.$toLatLng, this.$destinationField);
		}
		this.autocompleteInit();
		this.$mapDiv = $(this.map.getDiv());
		if(this.enableContextMenu){
			this.$mapDiv.append(this.getContextMenu());
			this.$contextMenu = this.$root.find('.woald-gmaps-contextmenu');
			this.contextMenuItemClickDelegate = Woald.createDelegate(this, this.contextMenuItemClickHandler);
			this.$contextMenu.find('a').on('click', this.contextMenuItemClickDelegate);
		}
		this.$fromLatLng.attr('placeholder', this.departurePlaceholder);
		this.$toLatLng.attr('placeholder', this.destinationPlaceholder);
		this.maintainDepartureState();
		this.maintainDestinationState();
		this.initializeWaypoints();
		this.defaultLocationInit();
	}
	Woald.gmaps.prototype.setBackgroundGradient = function(){
		this.$photoBackground.addClass(this.backgroundGradient);
	}
	Woald.gmaps.prototype.travelModeItemClick = function(e){
		var $elem = $(e.currentTarget)
			, name = $elem.data('woaldDrivingmode');
		e.preventDefault();
		this.selectedTravelMode = name;
		this.$travelModeList.find('i').remove();
		this.$travelModeHeading.html($.trim($elem.text()));
		$elem.append('<i class="fa fa-ok-sign pull-right"></i>');
		this.showDirection();
	}
	Woald.gmaps.prototype.initControls = function(options){
		this.$fromLatLng = this.$root.find('input[name="fromLatLng"]');
		this.$toLatLng = this.$root.find('input[name="toLatLng"]');
		this.$options1Container = this.$root.find('.woald-options1-container');
		this.$options2Container = this.$root.find('.woald-options2-container');
		this.$highway = this.$root.find('input[name="highway"]');
		this.$toll = this.$root.find('input[name="toll"]');
		this.$traffic = this.$root.find('input[name="traffic"]');
		this.$reverse = this.$root.find('.woald-reverse');
		this.$direction = this.$root.find('.woald-direction');
		this.$measurementContainer = this.$root.find('.woald-measurement-container');
		this.$miles = this.$root.find('.woald-miles');
		this.$km = this.$root.find('.woald-km');
		this.$directionPanelContainer = this.$root.find('.woald-direction-panel-container');
		this.$directionPanel = this.$root.find('.woald-direction-panel');
		this.$directionPanelCloseButton = this.$directionPanel.find('button');
		this.$directionDataEmpty = this.$root.find('.woald-direction-empty');
		this.$myPosButton = this.$root.find('button[name="mypos"]');
		this.$departureGroup = this.$root.find('.woald-departure-group');
		this.$destinationGroup = this.$root.find('.woald-destination-group');
		this.$errorPanelContainer = this.$root.find('.woald-error-panel-container');
		this.$errorPanel = this.$root.find('.woald-error-panel');
		this.$errorPanelCloseButton = this.$errorPanel.find('button');
		this.$errorContainer = this.$root.find('.woald-error-container');
		this.posErrorMsg = options['posErrorMsg'] || 'An error occurred while trying to determine your position.';
		this.geolocationErrorMsg = options['geolocationErrorMsg'] || 'Your browser does not support geolocation. We have placed you somewhere.';
		this.$photoBackground = this.$root.find('.woald-photo-background');
		this.$zoomInButton = this.$root.find('.woald-zoom-in-button');
		this.$zoomOutButton = this.$root.find('.woald-zoom-out-button');
		this.$distancePlaceHolder = this.$root.find('.woald-distance-placeholder');
		this.$distanceContainer = this.$root.find('.woald-total-distance-container');
		//this.$totalDistance = this.$root.find('.woald-total-distance');
		this.$totalCost = this.$root.find('.woald-total-cost');
		this.$waypoints = this.$root.find('.woald-waypoints-placeholder');
		this.$waypointsAddButton = this.$root.find('button[name="add"]');
		this.$waypointsAddButtonContainer =  this.$root.find('.woald-waypoint-add-button-container');
		this.$departureFieldContainer = this.$root.find('.woald-departure-field-container');
		this.$destinationFieldContainer = this.$root.find('.woald-destination-field-container');
		this.$validators = this.$root.find('.woald_parsley_validated');
		this.$departureField = this.$root.find('input[name="departure"]');
		this.$destinationField = this.$root.find('input[name="destination"]');
		this.$distanceField = this.$root.find('input[name="distance"]');
		this.$durationField = this.$root.find('input[name="duration"]');
		this.$unitField = this.$root.find('input[name="unit"]');
		this.$waypointsField  = this.$root.find('input[name="waypoints"]');
		this.$contextMenuHint = this.$root.find('.woald-map-hint');
		this.$fromPlaceId = this.$root.find('input[name="fromPlaceId"]');
		this.$toPlaceId = this.$root.find('input[name="toPlaceId"]');
		if(this.$highway.length > 0){
			this.highwayClickDelegate = Woald.createDelegate(this, this.highwayClickHandler);
			this.$highway.on('change', this.highwayClickDelegate);
			if(this.avoidHighways){
				this.$highway.prop('checked', true);
			}
		}
		
		if(this.$toll.length > 0){
			this.tollClickDelegate = Woald.createDelegate(this, this.tollClickHandler);
			this.$toll.on('change', this.tollClickDelegate);
			if(this.avoidTolls){
				this.$toll.prop('checked', true);
			}
		}
		if(this.$traffic.length > 0){
			this.trafficClickDelegate = Woald.createDelegate(this, this.trafficClickHandler);
			this.$traffic.on('change', this.trafficClickDelegate);
			if(this.traffic){
				this.$traffic.prop('checked', true);
			}
		}
		if(this.enableDirection){
			if(this.$reverse.length > 0){
				this.reverseClickDelegate = Woald.createDelegate(this, this.reverseClickHandler);
				this.$reverse.on('click', this.reverseClickDelegate);
			}
			if(this.enableDirectionPanel && !this.showDirectionStepsInline){
				if(this.$direction.length > 0){
					this.directionClickDelegate = Woald.createDelegate(this, this.directionClickHandler);
					this.$direction.on('click', this.directionClickDelegate);
				}
			}
		}
		if(this.$miles.length > 0){
			this.milesClickDelegate = Woald.createDelegate(this, this.milesClickHandler);
			this.$miles.on('click', this.milesClickDelegate);
			if(this.miles){
				this.$miles.addClass('active');
			}
		}
		if(this.$km.length > 0){
			this.kmClickDelegate = Woald.createDelegate(this, this.kmClickHandler);
			this.$km.on('click', this.kmClickDelegate);
			if(this.km){
				this.$km.addClass('active');
			}
		}
		if(this.$myPosButton.length > 0){
			this.myPosClickDelegate = Woald.createDelegate(this, this.myPosClickHandler);
			this.$myPosButton.on('click', this.myPosClickDelegate);
		}
		
		if(this.$directionPanelCloseButton.length > 0){
			this.directionPanelCloseDelegate = Woald.createDelegate(this, this.directionPanelCloseHandler);
			this.$directionPanelCloseButton.on('click', this.directionPanelCloseDelegate);
		}
		if(this.$errorPanelCloseButton.length > 0){
			this.errorPanelCloseDelegate = Woald.createDelegate(this, this.errorPanelCloseHandler);
			this.$errorPanelCloseButton.on('click', this.errorPanelCloseDelegate);
		}
		if(this.$zoomInButton.length > 0){
			this.zoomInDelegate = Woald.createDelegate(this, this.zoomInHandler);
			this.$zoomInButton.on('click', this.zoomInDelegate);
		}
		if(this.$zoomOutButton.length > 0){
			this.zoomOutDelegate = Woald.createDelegate(this, this.zoomOutHandler);
			this.$zoomOutButton.on('click', this.zoomOutDelegate);
		}
		if(this.enableDirection && this.$directionPanel.length > 0){
			this.$directionPanelContainer.removeClass('hide');
			this.directionsDisplay.setMap(this.map);
			if(this.enableDirectionPanel){
				this.directionsDisplay.setPanel(this.$directionPanel[0]);
			}
			
			this.directionsChangedDelegate = Woald.createDelegate(this, this.directionsChangedHandler);
			google.maps.event.addListener(this.directionsDisplay, 'directions_changed', this.directionsChangedDelegate);
		}
		if(this.enableWaypointButton && this.$waypointsAddButton.length > 0){
			this.waypointsAddDelegate = Woald.createDelegate(this, this.waypointsAddHandler);
			this.$waypointsAddButton.on('click', this.waypointsAddDelegate);
		}
		this.autocompleteOnPasteDelegate = Woald.createDelegate(this, this.autocompleteOnPasteHandler);
		this.$fromLatLng.on('paste', this.autocompleteOnPasteDelegate);
		this.$toLatLng.on('paste', this.autocompleteOnPasteDelegate);
		if(this.enableDirectionPanel){
			this.directionPanelToggleDelegate = Woald.createDelegate(this, this.directionPanelToggleHandler);
			this.$directionPanel.on('shown.bs.collapse', this.directionPanelToggleDelegate);
			this.$directionPanel.on('hidden.bs.collapse', this.directionPanelToggleDelegate);
		}
	}
	Woald.gmaps.prototype.contextMenuItemClickHandler = function(e, waypointArgs){
		var $target = $(e.currentTarget)
			, $container = $target.parent()
			, menu = parseInt($target.attr('data-woald-menu'), 10)
			, waypointId = parseInt($target.attr('data-woald-waypoint-id'), 10)
			, $waypoint = this.$root.find('input[data-woald-waypoint-id="' + waypointId + '"]')
			, lat = parseFloat($container.attr('data-woald-lat'))
			, lng = parseFloat($container.attr('data-woald-lng'))
			, latLng = [lat, lng].join(',')
			, data;
		if($waypoint.length > 0){
			data = $waypoint.attr('data-woald-desc');
		}
		//ToDO: infowindow, add a caption to indicate departure/destination.
		switch(menu){
			case 0: /*Woald.gmaps.contextMenu.departure*/
				this.$fromLatLng.val('');
				this.geocodeRequest(latLng, {'infoWindow': this.infoWindowFrom, 'marker': this.fromMarker, 'elem': this.$fromLatLng, 'hiddenField': this.$departureField});
			break;
			case 1: /*Woald.gmaps.contextMenu.destination*/
				this.$toLatLng.val('');
				this.geocodeRequest(latLng, {'infoWindow': this.infoWindowTo, 'marker': this.toMarker, 'elem': this.$toLatLng, 'hiddenField': this.$destinationField});
			break;
			case 2: /*Woald.gmaps.contextMenu.waypoint*/
				$waypoint.val('');
				this.geocodeRequest(latLng, waypointArgs);
			break;
		}
		this.contextMenuExitHandler();
		return false;
	}
	Woald.gmaps.prototype.contextMenuExitHandler = function(e){
		if(!this.$contextMenu.hasClass('hide')){
			this.$contextMenu.addClass('hide');
		}
	}
	Woald.gmaps.prototype.mapContextMenuHandler = function(e){
		e.stop();
		var lat = e.latLng.lat()
			, lng = e.latLng.lng()
			, worldCoordinate = this.fromLatLngToPoint(e.latLng)
			, x = worldCoordinate['x']
			, y = worldCoordinate['y']
			, mapWidth = this.$mapDiv.width()
			, mapHeight = this.$mapDiv.height()
			, contextMenuWidth
			, contextMenuHeight
			, context = this;
		window.setTimeout(function(){
			context.$contextMenu.removeClass('hide');
			contextMenuWidth = context.$contextMenu.width();
			contextMenuHeight = context.$contextMenu.height();
			if((mapWidth - x ) < contextMenuWidth){
				x = Math.abs(x - contextMenuWidth);
			}
			if((mapHeight - y ) < contextMenuHeight){
				y = Math.abs(y - contextMenuHeight);
			}
			context.$contextMenu.attr('data-woald-lat', e.latLng.lat()).attr('data-woald-lng', e.latLng.lng());
			context.$contextMenu.css({'left': x + 'px', 'top': y + 'px'});
		}, 10);
		return false;
	}
	Woald.gmaps.prototype.fromLatLngToPoint = function(latLng) {
		var scale = Math.pow(2, this.map.getZoom())
			, nw = new google.maps.LatLng(
				 this.map.getBounds().getNorthEast().lat(),
				 this.map.getBounds().getSouthWest().lng()
			 )
			, worldCoordinateNW = this.map.getProjection().fromLatLngToPoint(nw)
			, worldCoordinate = this.map.getProjection().fromLatLngToPoint(latLng)
			, result = new google.maps.Point(
				 Math.floor((worldCoordinate.x - worldCoordinateNW.x) * scale),
				 Math.floor((worldCoordinate.y - worldCoordinateNW.y) * scale)
			 );
		return result;
	}
	Woald.gmaps.prototype.autocompleteOnPasteHandler = function(e){
		var $target = $(e.currentTarget);
		(function($elem, context){
			return setTimeout(function() {
				var val = $elem.val();
				$elem.blur();
				$elem.val(val);
				return $elem.focus();
			}, 1);
		})($target, this);
	}
	Woald.gmaps.prototype.genDropdownLists = function(options){
		var dropdownListFrom
			, dropdownListTo
			, params
			, place
			, result = {'from': false, 'to': false};
		
		if(this.fromPlaces.length > 1){
			this.$departureGroup.parent().removeClass('input-group');
			this.$departureGroup.addClass('hide');
			dropdownListFrom = this.getDropdownList(
				this.fromPlaces
				, 'fromLatLng'
				, this.defaultFromSelectionCaption
				, this.fromPlacesPreload
				, this.$departureField
			);
			this.fromLatLngChangeDelegate = Woald.createDelegate(this, this.fromLatLngChangedHandler);
			dropdownListFrom.on('change', this.fromLatLngChangeDelegate);
			this.$fromLatLng.replaceWith(dropdownListFrom);
			this.$fromLatLng = this.$root.find('select[name="fromLatLng"]');
		}else if(this.fromPlaces.length === 1){
			place = this.fromPlaces[0];
			result['from'] = true;
			this.preloadPlaces(this.$fromLatLng, this.$departureField, place, true);
			this.$fromLatLng.prop('readonly', true);
			this.fromPlacesPreload = true;
		}
		if(this.toPlaces.length > 1){
			this.$destinationGroup.parent().removeClass('input-group');
			this.$destinationGroup.addClass('hide');
			dropdownListTo = this.getDropdownList(
				this.toPlaces
				, 'toLatLng'
				, this.defaultToSelectionCaption
				, this.toPlacesPreload
				, this.$destinationField
			);
			this.toLatLngChangeDelegate = Woald.createDelegate(this, this.toLatLngChangedHandler);
			dropdownListTo.on('change', this.toLatLngChangeDelegate);
			this.$toLatLng.replaceWith(dropdownListTo);
			this.$toLatLng = this.$root.find('select[name="toLatLng"]');
		}else if(this.toPlaces.length === 1){
			place = this.toPlaces[0];
			result['to'] = true;
			this.preloadPlaces(this.$toLatLng, this.$destinationField, place, true);
			this.$toLatLng.prop('readonly', true);
			this.toPlacesPreload = true;
		}
		if((this.fromPlacesPreload || this.toPlacesPreload) && this.searchFields.length > 0){
			params = this.searchFields.splice(this.searchFields.length - 1, 1)[0];
			this.geocodeRequest(params[0], params[1]);
		}
		return result;
	}
	Woald.gmaps.prototype.preloadPlaces = function($elem, $hiddenField, place, preload){
		var infoWindow
			, marker
			, args
			, toggleInfoWindowDelegate
			, markerDragEndDelegate
			, address
			, markerParams = {}
			, markerIconUrl = place ? place['markerIcon'] : null
			, markerIconWidth = place ? place['markerIconWidth'] : null
			, markerIconHeight = place ? place['markerIconHeight'] : null
			, lat = place ? place['lat'] : null
			, lng = place ? place['lng'] : null;
		infoWindow = new google.maps.InfoWindow();
		if(!this.infoWindows){
			this.infoWindows = [];
		}
		this.infoWindows.push(infoWindow);
		marker = new google.maps.Marker({
			map: this.map
			, anchorPoint: new google.maps.Point(0, -29)
			, draggable: this.draggableMarker
			, animation: google.maps.Animation.DROP
		});
		if(markerIconWidth && markerIconHeight){
			markerParams['scaledSize'] = new google.maps.Size(markerIconWidth, markerIconHeight);
		}
		if(markerIconUrl){
			markerParams['url'] = markerIconUrl;
		}
		if(!$.isEmptyObject(markerParams)){
			marker.setIcon(markerParams);
		}
		if(!this.markers){
			this.markers = [];
		}
		this.markers.push(marker);
		toggleInfoWindowDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
			'infoWindow': infoWindow
			, 'marker': marker
		});
		google.maps.event.addListener(marker, 'click', toggleInfoWindowDelegate);
		args = {'infoWindow': infoWindow, 'marker': marker, 'elem': $elem, 'hiddenField': $hiddenField, 'preloading': true };
		markerDragEndDelegate = Woald.createCallback(this.dragEnd, this, args);
		google.maps.event.addListener(marker, 'dragend', markerDragEndDelegate);
		if(preload && (lat && lng)){
			address = [lat, lng].join(',');
			this.searchFields.push([address, args]);
		}
	}
	Woald.gmaps.prototype.fromLatLngChangedHandler = function(e){
		var $option = this.$fromLatLng.find('option:selected')
			, placeId = parseInt($option.attr('data-woald-place-id'), 10);
		this.filterLatLngList(placeId);
		this.resetMarkersByOptions(this.$fromLatLng.find('option'));
		this.requestByLatLng({
			'elem': $option
			, 'hiddenField': this.$departureField
			, 'latLng': this.$fromLatLng.val()
			, 'preload': this.fromPlacesPreload
			, 'index': parseInt($option.attr('data-woald-index'), 10)
			, 'showInfoWindow': true
		});
		this.$fromPlaceId.val(placeId);
	}
	Woald.gmaps.prototype.toLatLngChangedHandler = function(e){
		var $option = this.$toLatLng.find('option:selected')
			, placeId = parseInt($option.attr('data-woald-place-id'), 10);
		this.resetMarkersByOptions(this.$toLatLng.find('option'));
		this.requestByLatLng({
			'elem': $option
			, 'hiddenField': this.$destinationField
			, 'latLng': this.$toLatLng.val()
			, 'preload': this.toPlacesPreload
			, 'index': parseInt($option.attr('data-woald-index'), 10)
			, 'showInfoWindow': true
		});
		this.$toPlaceId.val(placeId);
	}
	Woald.gmaps.prototype.filterLatLngList = function(placeId){
		var i
			, j
			, id
			, pair
			, $options = this.$toLatLng.find('option')
			, $option
			, fromPlaceId
			, toPlaceId
			, selectedDestination = this.$toLatLng.val()
			, itemsToExclude = [];
		if(this.excludedComboPlaces.length === 0 || $options.length <= 1){
			return;
		}
		for(i = 0; i < this.excludedComboPlaces.length; i++){
			pair = this.excludedComboPlaces[i].split(',');
			fromPlaceId = parseInt(pair[0], 10);
			toPlaceId = parseInt(pair[1], 10);
			if(fromPlaceId === placeId){
				itemsToExclude.push(toPlaceId);
			}
		}
		if(itemsToExclude.length === 0){
			return;
		}
		$options.removeAttr('disabled');
		$options.removeAttr('selected');
		$options.removeAttr('hidden');
		for(j = 0; j < $options.length; j++){
			$option = $($options[j]);
			id = parseInt($option.attr('data-woald-place-id'), 10);
			if(itemsToExclude.indexOf(id) !== -1){
				$option.prop('disabled', true);
				$option.prop('hidden', true);
			}
		}
		if(selectedDestination){
			this.resetDirection();
		}
	}
	Woald.gmaps.prototype.requestByLatLng = function(args){
		var $elem = args['elem']
			, $hiddenField = args['hiddenField']
			, latLng = args['latLng']
			, preload = args['preload']
			, index = args['index']
			, showInfoWindow = args['showInfoWindow']
			, infoWindow = this.infoWindows[index]
			, marker = this.markers[index]
			, pos;
		if(!latLng){
			latLng = $elem.val();
		}
		if(!latLng){
			this.fitMarkers();
			return;
		}
		if(preload){
			pos = marker.getPosition();
			if(pos){
				this.map.panTo(pos);
				this.closeAllInfoWindows();
				infoWindow.open(this.map, marker);
				if(this.panToZoom){
					this.map.setZoom(this.panToZoom);
				}
				this.showDirection({'showError': false});
			}
			return;
		}
		this.geocodeRequest(latLng, {
			'infoWindow': infoWindow
			, 'marker': marker
			, 'elem': $elem
			, 'hiddenField': $hiddenField
			, 'showInfoWindow': showInfoWindow
		});
	};
	Woald.gmaps.prototype.maintainDepartureState = function(){
		var departureValue
			, index
			, placeId;
		if(!this.fromAddress && !this.toAddress){
			return;
		}
		if(this.fromAddress && this.$fromLatLng.length > 0){
			departureValue = [this.fromLat, this.fromLng].join(',');
			if(this.$fromLatLng[0].type !== 'text'){
				this.$fromLatLng.find('option[value="' + departureValue + '"]').prop('selected', true);
				index = parseInt(this.$fromLatLng.find('option:selected').attr('data-woald-index'), 10);
				placeId = parseInt(this.$fromLatLng.find('option:selected').attr('data-woald-place-id'), 10);
				this.filterLatLngList(placeId);
			}else{
				this.$fromLatLng.val(this.decodeSpecialCharacters(this.fromAddress));
				index = 0;
			}
			this.searchFields.push([departureValue, {
				'infoWindow': this.infoWindows[index]
				, 'marker': this.markers[index]
				, 'elem': this.$fromLatLng
				, 'hiddenField': this.$departureField
				, 'latLng': departureValue
				, 'preload': this.fromPlacesPreload
				, 'index': index
			}]);
		}
	}
	Woald.gmaps.prototype.maintainDestinationState = function(){
		var destinationValue
			, index;
		if(!this.fromAddress && !this.toAddress){
			return;
		}
		if(this.toAddress && this.$toLatLng.length > 0){
			destinationValue = [this.toLat, this.toLng].join(',');
			if(this.$toLatLng[0].type !== 'text'){
				this.$toLatLng.find('option[value="' + destinationValue + '"]').prop('selected', true);
				index = parseInt(this.$toLatLng.find('option:selected').attr('data-woald-index'), 10);
			}else{
				this.$toLatLng.val(this.decodeSpecialCharacters(this.toAddress));
				index = this.$fromLatLng.length > 0 ? 1 : 0;
			}
			this.searchFields.push([destinationValue, {
				'infoWindow': this.infoWindows[index]
				, 'marker': this.markers[index]
				, 'elem': this.$toLatLng
				, 'hiddenField': this.$destinationField
				, 'latLng': destinationValue
				, 'preload': this.toPlacesPreload
				, 'index': index
			}]);
		}
	}
	Woald.gmaps.prototype.getDropdownList = function(places, name, caption, preload, $hiddenField){
		var $dropdownList = $('<select name="' + name + '" class="form-select calendarista-typography--caption1 woald_parsley_validated" data-parsley-required="true"><option value="">' + caption + '</option></select>')
			, $option
			, place
			, value
			, text
			, i;
		for(i = 0; i < places.length; i++){
			place = places[i];
			value = [place['lat'], place['lng']].join(',');
			text = place['name'];
			$option = $('<option value="' + value + '" data-woald-place="' + this.encodedStringify(place) + '"' + 'data-woald-place-id="' + place['id'] + '"' + 'data-woald-index="' + this.infoWindowIndex + '">' + text + '</option>');
			if((name === 'fromLatLng' && value == this.selectedFromPlaceValue) || 
				(name === 'toLatLng' && value == this.selectedToPlaceValue)){
				$option.prop('selected', true);
			}
			$dropdownList.append($option);
			this.preloadPlaces(
				$option
				, $hiddenField
				, place
				, preload
			);
			this.infoWindowIndex++;
		}
		return $dropdownList;
	}
	Woald.gmaps.prototype.getConversion = function(){
		return this.km ? 1000 : 1609.344;
	}
	Woald.gmaps.prototype.setConversion = function(){
		this.conversion = this.getConversion();
		this.unitLabelText = this.km ? this.kmLabelText : this.milesLabelText;
		this.unit = this.km ? google.maps.UnitSystem.METRIC : google.maps.UnitSystem.IMPERIAL;
	}
	Woald.gmaps.prototype.isValid = function(){
		var isValid = true;
		this.$validators.each(function(){
			var $elem = $(this),
				result;
			if (!$elem.is(':visible') || $elem.is(':disabled') || $elem.attr('data-parsley-excluded')){
				return true;
			}
			result = $elem.parsley ? $elem.parsley().validate(true) : true;
			if(result !== null && (typeof(result) === 'object' && result.length > 0)){
				isValid = false;
			}
		});
		return isValid;
	}
	Woald.gmaps.prototype.highwayClickHandler = function(e){
		this.avoidHighways = this.$highway.is(':checked');
		this.showDirection();
	}
	
	Woald.gmaps.prototype.tollClickHandler = function(e){
		this.avoidTolls = this.$toll.is(':checked');
		this.showDirection();
	}
	Woald.gmaps.prototype.trafficClickHandler = function(e){
		this.traffic = this.$traffic.is(':checked');
		if(!this.trafficLayer){
			this.trafficLayer = new google.maps.TrafficLayer();
			this.trafficLayerMapChangedDelegate = Woald.createDelegate(this, this.trafficLayerMapChangedHandler);
			google.maps.event.addListener(this.trafficLayer, 'map_changed', this.trafficLayerMapChangedDelegate );
		}
		this.progressIndicator('show');
		if(this.traffic){
			this.trafficLayer.setMap(this.map);
		}else{
			this.trafficLayer.setMap(null);
		}
	}
	Woald.gmaps.prototype.trafficLayerMapChangedHandler = function(e){
		this.progressIndicator('hide');
	}
	Woald.gmaps.prototype.reverseClickHandler = function(e){
		var fromLatLng = this.$fromLatLng.val()
			, toLatLng = this.$toLatLng.val()
			, fromPostData = this.$fromLatLng.attr('data-woald-postdata')
			, toPostData = this.$toLatLng.attr('data-woald-postdata');
		this.$fromLatLng.val(toLatLng);
		this.$fromLatLng.attr('data-woald-postdata', toPostData);
		this.$toLatLng.val(fromLatLng);
		this.$toLatLng.attr('data-woald-postdata', fromPostData);
		this.showDirection();
	}
	Woald.gmaps.prototype.directionClickHandler = function(e){
		var status = 'show'
			, showDirectionPanel
			, fromLatLng = this.$fromLatLng.val()
			, toLatLng = this.$toLatLng.val();
		if(!this.enableDirectionPanel){
			return;
		}
		showDirectionPanel = !this.$direction.hasClass('active');
		if(!showDirectionPanel){
			status = 'hide';
			this.closeError();
		}else{
			if(!fromLatLng || !toLatLng){
				this.showError(this.directionEmptyError);
				return;
			}
		}
		this.$directionPanel.calendaristaCollapse(status);
	}
	Woald.gmaps.prototype.milesClickHandler = function(e){
		this.miles = !this.$miles.hasClass('active');
		this.km = !this.miles;
		this.setConversion();
		this.showDirection();
	}
	Woald.gmaps.prototype.kmClickHandler = function(e){
		this.km = !this.$km.hasClass('active');
		this.miles = !this.km;
		this.setConversion();
		this.showDirection();
	}
	Woald.gmaps.prototype.initSearch = function(){
		var result = this.searchFields.length > 0
			, params;
		if(result){
			params = this.searchFields.splice(this.searchFields.length - 1, 1)[0];
			this.geocodeRequest(params[0], params[1]);
		}
		return result;
	}
	Woald.gmaps.prototype.myPosClickHandler = function(e){
		this.$fromLatLng.val('');
		this.geoLocate({'infoWindow': this.infoWindowFrom, 'marker': this.fromMarker, 'elem': this.$fromLatLng, 'hiddenField': this.$departureField});
	}
	Woald.gmaps.prototype.directionPanelToggleHandler = function(e){
		e.stopPropagation();
		//not using yet.
	}
	Woald.gmaps.prototype.initializeWaypoints = function(){
		var i
			, waypoint
			, $textbox;
		for(i = 0; i < this.waypoints.length; i++){
			waypoint = this.waypoints[i];
			$textbox = this.waypointsAdd(Woald.gmaps.collapseDirection.inside, Woald.gmaps.action.onPageLoad, waypoint);
			$textbox.val(waypoint['address']);
		}
		this.updateWaypointsField();
		if(this.enableDirection){
			if(this.searchFields.length > 0){
				this.showDirection({'showError': false});
			}
			return;
		}
		this.initSearch();
	}
	Woald.gmaps.prototype.waypointsAdd = function(collapseDirection, action, postdata){
		var $buttons
			, $formGroups
			, id = ++this.id
			, $textbox
			, $waypointMenuItem
			, $waypointElements = this.$root.find('.woald-waypoint-group');;
		this.$waypoints.append(this.getWaypointsHtml(id, collapseDirection));
		if(this.enableContextMenu){
			this.$contextMenu.append(this.getWaypointContextMenu(id));
		}
		$textbox = this.$waypoints.find('input[data-woald-waypoint-id="' + id + '"]');
		$textbox.on('paste', this.autocompleteOnPasteDelegate);
		
		this.autocompleteWaypoint($textbox, id, action, postdata);
		
		$buttons = this.$waypoints.find('button[name="waypointremove"]');
		$formGroups = this.$waypoints.find('.form-group');
		$buttons.off();
		$formGroups.off();
		
		if(!this.waypointsRemoveDelegate){
			this.waypointsRemoveDelegate = Woald.createDelegate(this, this.waypointsRemoveHandler);
		}
		if(!this.collapseWaypointDelegate){
			this.collapseWaypointDelegate = Woald.createDelegate(this, this.collapseWaypointHandler);
		}
		$formGroups.on('hidden.bs.collapse', this.collapseWaypointDelegate);
		$buttons.on('click', this.waypointsRemoveDelegate);
		this.$waypoints.find('.collapse.out').calendaristaCollapse('show');
		
		if($formGroups.length >= this.waypointMax){
			this.$waypointsAddButton.prop('disabled', true);
		}else if($formGroups.length < this.waypointMax){
			this.$waypointsAddButton.removeAttr('disabled');
		}
		if($waypointElements.length >= this.waypointsMax){
			this.$waypointsAddButton.prop('disabled', true);
		}
		return $textbox;
	}
	Woald.gmaps.prototype.waypointsAddHandler = function(){
		this.waypointsAdd(Woald.gmaps.collapseDirection.out, Woald.gmaps.action.onPageLoaded);
	}
	Woald.gmaps.prototype.collapseWaypointHandler = function(e){
		e.stopPropagation();
		var $target = $(e.currentTarget)
			, $formGroup = $target.closest('.form-group')
			, $textbox = $formGroup.find('input[name="waypoint[]"]')
			, $formGroups
			, $buttonRemove = $target.find('button[name="waypointremove"]')
			, id = parseInt($buttonRemove.data('woaldId'), 10)
			, autocomplete = this.findObject(this.autocompletesWaypoint, id)
			, marker = this.findObject(this.markersWaypoint, id)
			, infoWindow = this.findObject(this.infoWindowsWaypoint, id)
			, $waypointContextMenuItem = this.$contextMenu ? this.$contextMenu.find('a[data-woald-waypoint-id="' + id + '"]') : null
			, $waypointElements
			, $contextMenuItems
			, i
			, $waypoint
			, $menuItem
			, $badge
			, $input
			, $button;
		--this.id;
		if(this.isOpen(infoWindow['result'])){
			infoWindow['result'].close(this.map, marker['result']);
		}
		marker['result'].setMap(null);
		google.maps.event.clearListeners(autocomplete['result'], 'place_changed');
		google.maps.event.clearListeners(marker['result'], 'click');
		$textbox.off();
		
		this.autocompletesWaypoint.splice(autocomplete['i'], 1);
		this.markersWaypoint.splice(marker['i'], 1);
		this.infoWindowsWaypoint.splice(infoWindow['i'], 1);
		
		$formGroup.off();
		$buttonRemove.off();
		$formGroup.remove();
		if($waypointContextMenuItem){
			$waypointContextMenuItem.remove();
		}

		$formGroups = this.$waypoints.find('.form-group');
		if($formGroups.length < this.waypointMax){
			this.$waypointsAddButton.removeAttr('disabled');
		}

		$waypointElements = this.$root.find('.woald-waypoint-group');
		for(i = 0; i < $waypointElements.length;i++){
			$waypoint = $($waypointElements[i]);
			$badge = $waypoint.find('.woald-waypoint-badge');
			//$input = $waypoint.find('input');
			//$button = $waypoint.find('button[data-woald-id]');
			$badge.html(i+1);
			//$input.attr('data-woald-waypoint-id', i+1);
			//$button.attr('data-woald-id', i+1);
		}
		if(this.enableContextMenu){
			$contextMenuItems = this.$root.find('.woald-gmaps-contextmenu a[data-woald-waypoint-id]');
			for(i = 0; i < $contextMenuItems.length;i++){
				$menuItem = $($contextMenuItems[i]);
				//$menuItem.attr('data-woald-waypoint-id', i+1);
				$badge = $menuItem.find('.woald-waypoint-badge');
				$badge.html(i+1);
			}
		}
		if($waypointElements.length < this.waypointsMax){
			this.$waypointsAddButton.removeAttr('disabled');
		}
		this.updateWaypointsField();
		this.showDirection({'showError': false});
	}
	Woald.gmaps.prototype.findObject = function(list, id){
		var result = null
			, i
			, listItem;
		for(i = 0; i < list.length; i++){
			listItem = list[i];
			if(listItem['id'] === id){
				result = listItem['obj'];
				break;
			}
		}
		return {'result': result, 'i': i};
	}
	Woald.gmaps.prototype.waypointsRemoveHandler = function(e){
		var $target = $(e.currentTarget)
			, $formGroup = $target.closest('.form-group');
		$formGroup.calendaristaCollapse('hide');
	}
	Woald.gmaps.prototype.zoomOutHandler = function(){
		var currentZoomLevel = this.map.getZoom();
		if(currentZoomLevel !== 0){
			this.map.setZoom(currentZoomLevel - 1);
		}	
	}
	 
	Woald.gmaps.prototype.zoomInHandler = function(){
		var currentZoomLevel = this.map.getZoom();
		if(currentZoomLevel !== 21){
			this.map.setZoom(currentZoomLevel + 1);
		}
	}
	Woald.gmaps.prototype.tilesLoadedHandler = function(){
		//google.maps.event.clearListeners(this.map, 'tilesloaded');
		//delete this.tilesLoadedDelegate;
		this.progressIndicator('hide');
		if(this.mapLoaded){
			this.mapLoaded(this.map);
		}
	}
	Woald.gmaps.prototype.progressIndicator = function(status){
		if(status === 'show'){
			this.$spinner.removeClass('calendarista-invisible');
			return;
		}
		this.$spinner.addClass('calendarista-invisible');
	}
	Woald.gmaps.prototype.getLocality = function(args){
		var i
			, item;
		for(i = 0; i < args.address_components.length; i++){
			item = args.address_components[i];
			if(item['types'] && item['types'][0] === 'locality'){
				return item['long_name'];
			}
		}
		return '';
	}
	Woald.gmaps.prototype.defaultLocationInit = function(){
		var markerParams = {}
			, args
			, toggleInfoWindowDelegate
			, markerDragEndDelegate
			, $elem
			, address;
		//optionally show marker and description? might want this option in the future
		if((!this.defaultLocation['lat'] || !this.defaultLocation['lng']) || this.searchFields.length > 0){
			return;
		}
		if(this.fromAddress && this.toAddress){
			//since we already have both places, means we are maintaining state, hence we do not care about loading the default location, exit
			return;
		}
		this.defaultLocationInfoWindow = new google.maps.InfoWindow();
		this.defaultLocationMarker = new google.maps.Marker({
			map: this.map
			, anchorPoint: new google.maps.Point(0, -29)
			, draggable: this.draggableMarker
			, animation: google.maps.Animation.DROP
		});
		if(this.defaultLocation['markerIconWidth'] && this.defaultLocation['markerIconHeight']){
			markerParams['scaledSize'] = new google.maps.Size(this.defaultLocation['markerIconWidth'], this.defaultLocation['markerIconHeight']);
		}
		if(this.defaultLocation['markerIcon']){
			markerParams['url'] = this.defaultLocation['markerIcon'];
		}
		if(!$.isEmptyObject(markerParams)){
			this.defaultLocationMarker.setIcon(markerParams);
		}
		toggleInfoWindowDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
			'infoWindow': this.defaultLocationInfoWindow
			, 'marker': this.defaultLocationMarker
		});
		google.maps.event.addListener(this.defaultLocationMarker, 'click', toggleInfoWindowDelegate);
		args = {'infoWindow': this.defaultLocationInfoWindow, 'marker': this.defaultLocationMarker, 'hiddenField': null, 'elem': null };
		markerDragEndDelegate = Woald.createCallback(this.dragEnd, this, args);
		google.maps.event.addListener(this.defaultLocationMarker, 'dragend', markerDragEndDelegate);
		$elem = $('<input>').attr({
			type: 'hidden',
			id: 'woald_default_location',
			name: 'woald_default_location'
		});
		$elem.appendTo(this.$root).attr('data-woald-place', this.encodedStringify(this.defaultLocation));
		address = [this.defaultLocation['lat'], this.defaultLocation['lng']].join(',');
		this.geocodeRequest(address, {'infoWindow': this.defaultLocationInfoWindow, 'marker': this.defaultLocationMarker, 'elem': $elem, 'hiddenField': $elem});
	}
	Woald.gmaps.prototype.autocompleteInit = function(){
		var autocompleteFromDelegate
			, autocompleteToDelegate
			, autocompleteFrom
			, autocompleteTo
			, fromArgs
			, toArgs
			, opt = {};
		if(this.bounds){
			opt['strictBounds'] = true;
			opt['bounds'] = this.bounds;
			//opt['types'] = ['address'];
		}
		if(!this.autocompletes){
			this.autocompletes = [];
		}
		if(this.$fromLatLng.attr('type') === 'text'){
			//we have a textbox, power with autocomplete
			this.infoWindowFrom = this.infoWindows[0]
			this.fromMarker = this.markers[0];
			fromArgs = {'infoWindow': this.infoWindowFrom, 'marker': this.fromMarker, 'hiddenField': this.$departureField, 'elem': this.$fromLatLng};
			autocompleteFrom = new google.maps.places.Autocomplete(this.$fromLatLng[0], opt);
			autocompleteFrom.bindTo('bounds', this.map);
			//ToDO: in the future, add a filter option so customers can filter results by type:
			//https://developers.google.com/places/supported_types#table3
			//1. geocode, 2. address, 3. establishment (put these filter options in places page).
			//autocompleteFrom.setTypes(['geocode']);
			this.autocompletes.push(autocompleteFrom);
			autocompleteFromDelegate = Woald.createCallback(this.autocomplete, this, [autocompleteFrom, fromArgs]);
			google.maps.event.addListener(autocompleteFrom, 'place_changed', autocompleteFromDelegate);
		}
		
		if(this.$toLatLng.attr('type') === 'text'){
			//using textbox, power with autocomplete
			this.infoWindowTo = this.infoWindows[this.infoWindows.length === 1 ? 0 : 1];
			this.toMarker = this.markers[this.markers.length === 1 ? 0 : 1];
			toArgs = {'infoWindow': this.infoWindowTo, 'marker': this.toMarker, 'hiddenField': this.$destinationField, 'elem': this.$toLatLng};
			autocompleteTo = new google.maps.places.Autocomplete(this.$toLatLng[0], opt);
			autocompleteTo.bindTo('bounds', this.map);
			//ToDO: in the future, add a filter option so customers can filter results by type:
			//https://developers.google.com/places/supported_types#table3
			//1. geocode, 2. address, 3. establishment (put these filter options in places page).
			//autocompleteFrom.setTypes(['geocode']);
			this.autocompletes.push(autocompleteTo);
			autocompleteToDelegate = Woald.createCallback(this.autocomplete, this, [autocompleteTo, toArgs]);
			google.maps.event.addListener(autocompleteTo, 'place_changed', autocompleteToDelegate);
		}
	}
	
	Woald.gmaps.prototype.autocompleteWaypoint = function($elem, id, action, postdata){
		var autocomplete
			, infoWindow
			, marker
			, args
			, toggleWaypointInfoWindowDelegate
			, autocompleteDelegate
			, waypointMarkerDragEndDelegate
			, contextMenuItemClicDelegate
			, $waypointMenuItem = this.$contextMenu ? this.$contextMenu.find('a[data-woald-waypoint-id="' + id + '"]') : null
			, data = postdata
			, address
			, markerParams = {}
			, opt = {};
		if(this.bounds){
			opt['strictBounds'] = true;
			opt['bounds'] = this.bounds;
			//opt['types'] = ['address'];
		}
		autocomplete = new google.maps.places.Autocomplete($elem[0], opt)
		autocomplete.bindTo('bounds', this.map);
		//ToDO: in the future, add a filter option so customers can filter results by type:
		//https://developers.google.com/places/supported_types#table3
		//1. geocode, 2. address, 3. establishment (put these filter options in places page).
		//autocompleteFrom.setTypes(['geocode']);
		if(!this.autocompletesWaypoint){
			this.autocompletesWaypoint = [];
		}
		this.autocompletesWaypoint.push({'id': id, 'obj': autocomplete});
		
		infoWindow = new google.maps.InfoWindow();
		if(!this.infoWindowsWaypoint){
			this.infoWindowsWaypoint = [];
		}
		this.infoWindowsWaypoint.push({'id': id, 'obj': infoWindow});
		
		marker = new google.maps.Marker({
			map: this.map
			, anchorPoint: new google.maps.Point(0, -29)
			, draggable: this.draggableMarker
			, animation: google.maps.Animation.DROP
		});
		if(this.waypointMarkerIconWidth && this.waypointMarkerIconHeight){
			markerParams['scaledSize'] = new google.maps.Size(this.waypointMarkerIconWidth, this.waypointMarkerIconHeight);
		}
		if(this.waypointMarkerIconUrl){
			markerParams['url'] = this.waypointMarkerIconUrl;
		}
		if(!$.isEmptyObject(markerParams)){
			marker.setIcon(markerParams);
		}

		if(!this.markersWaypoint){
			this.markersWaypoint = [];
		}
		this.markersWaypoint.push({'id': id, 'obj': marker});
		
		toggleWaypointInfoWindowDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
			'infoWindow': infoWindow
			, 'marker': marker
		});
		google.maps.event.addListener(marker, 'click', toggleWaypointInfoWindowDelegate);
		
		args = {'infoWindow': infoWindow, 'marker': marker, 'hiddenField': this.$waypointsField, 'elem': $elem};
		waypointMarkerDragEndDelegate = Woald.createCallback(this.dragEnd, this, args);
		autocompleteDelegate = Woald.createCallback(this.autocomplete, this, [autocomplete, args]);
		
		google.maps.event.addListener(marker, 'dragend', waypointMarkerDragEndDelegate);
		google.maps.event.addListener(autocomplete, 'place_changed', autocompleteDelegate);
		if(this.enableContextMenu && $waypointMenuItem){
			contextMenuItemClicDelegate = Woald.createCallback(this.contextMenuItemClickHandler, this, args);
			$waypointMenuItem.on('click', contextMenuItemClicDelegate);
		}
		if(this.preloadLocations && action === Woald.gmaps.action.onPageLoad){
			if(!data){
				data = $elem.attr('data-woald-postdata');
				if(data){
					data = this.JSONParse(data);
				}
			}
			if(data){
				address = [data['lat'], data['lng']].join(',');
				this.searchFields.push([address, args]);
			}
		}
	}
	Woald.gmaps.prototype.autocomplete = function(args){
		var autocompleteFromTo = args[0]
			, place = autocompleteFromTo.getPlace()
			, params = args[1];
		params['pos'] = [place.geometry.location.lat(), place.geometry.location.lng()].join(',');
		this.clearDefaultLocation();
		this.locate(place, params);
		this.showDirection({'showError': false});
	};
	Woald.gmaps.prototype.resetMarkers = function(){
		var i
			, len;
		for(i in this.markers){
			this.markers[i].setVisible(false);
		}
		for(i in this.infoWindows){
			this.infoWindows[i].close();
		}
		
		for(i in this.infoWindowsWaypoint){
			this.infoWindowsWaypoint[i]['obj'].close();
		}
		for(i in this.markersWaypoint){
			this.markersWaypoint[i]['obj'].setVisible(false);
		}
		if(this.directionsInfoWindows){
			len = this.directionsInfoWindows.length;
			for (i = 0; i < len; i++) {
				this.directionsInfoWindows[i].close();
				delete this.directionsInfoWindows[i];
			}
			this.directionsInfoWindows.length = 0;
		}
		this.clearDefaultLocation();
	};
	Woald.gmaps.prototype.resetMarkersByOptions = function($options){
		var i
			, j;
		for(i = 0; i < $options.length; i++){
			j = parseInt($($options[i]).attr('data-woald-index'), 10);
			if(!isNaN(j)){
				this.markers[j].setVisible(false);
				this.infoWindows[j].close();
			}
		}
		if(this.directionsInfoWindows){
			for (i = 0; i < this.directionsInfoWindows.length; i++) {
				this.directionsInfoWindows[i].close();
				delete this.directionsInfoWindows[i];
			}
			this.directionsInfoWindows.length = 0;
		}
	};
	Woald.gmaps.prototype.clearDefaultLocation = function(){
		var len
			, i;
		if(this.directionsMarkers){
			len = this.directionsMarkers.length;
			for (i = 0; i < len; i++) {
				this.directionsMarkers[i].setVisible(false);
				google.maps.event.clearListeners(this.directionsMarkers[i], 'click');
				delete this.directionsMarkers[i];
			}
			this.directionsMarkers.length = 0;
		}
		if(this.defaultLocationInfoWindow){
			this.defaultLocationInfoWindow.close();
			delete this.defaultLocationInfoWindow;
		}
		if(this.defaultLocationMarker){
			this.defaultLocationMarker.setVisible(false);
			google.maps.event.clearListeners(this.defaultLocationMarker, 'click');
			google.maps.event.clearListeners(this.defaultLocationMarker, 'dragend');
			delete this.defaultLocationMarker;
		}
	}
	Woald.gmaps.prototype.fitMarkers = function(){
		var bounds = new google.maps.LatLngBounds()
			, i
			, markers = []
			, marker
			, pos
			, count = 0
			, context = this;
		if(this.markers){
			markers = markers.concat(this.markers);
		}
		if(this.markersWaypoint){
			markers =  markers.concat(this.markersWaypoint);
		}
		if(this.directionsMarkers){
			markers = markers.concat(this.directionsMarkers);
		}
		for(i = 0;i < markers.length;i++) {
			marker = markers[i];
			if(marker['obj']){
				marker = marker['obj'];
			}
			pos = marker.getPosition();
			if(pos){
				++count;
				bounds.extend(pos);
			}
		}
		if(count > 1){
			this.map.fitBounds(bounds);
		}
	}
	Woald.gmaps.prototype.locate = function(place, args){
		var address = ''
			, infoWindow = args['infoWindow']
			, marker = args['marker']
			, $hiddenField = args['hiddenField']
			, $elem = args['elem']
			, data = $elem ? $elem.attr('data-woald-place') : null
			, desc = data ? this.JSONParse(data) : null
			, showMarker = true
			, name = $hiddenField ? $hiddenField.attr('name') : null
			, location = place.geometry.location
			, postData
			, content
			, title
			, icon
			, currentBaseUrl
			, description
			, pos = args['pos'] ? args['pos'].split(',') : null
			, lat = pos ? pos[0] : location.lat()
			, lng = pos ? pos[1] : location.lng()
			, showInfoWindow = this.showInfoWindow;
			//, showInfoWindow = typeof(args['showInfoWindow']) === 'boolean' ? args['showInfoWindow'] : this.showInfoWindow;
		this.map.setCenter(new google.maps.LatLng(lat,lng));
		if(desc && typeof(desc['showMarker']) === 'boolean'){
			showMarker = desc['showMarker'];
		}
		if(infoWindow){
			infoWindow.close();
		}
		if(marker){
			marker.setVisible(false);
		}
		if (!place.geometry) {
		  return;
		}
		if($elem && $elem.attr('type') !== 'text'){
			address = $elem[0].type === 'select-one' ? $elem.find(':selected').text() : $elem.text();
		}else if($elem){
			address = $elem.val();
		}
		postData = {'address': address, 'lat': lat, 'lng': lng};
		if($elem){
			$elem.attr('data-woald-postdata', this.encodedStringify(postData));
		}
		if(name === 'waypoints'){
			this.updateWaypointsField();
		}else if($hiddenField){
			$hiddenField.val(this.JSONStringify(postData));
		}
		if(marker){
			marker.setPosition(location);
			if(showMarker){
				marker.setVisible(true);
			}
		}
		if(infoWindow){
			title = place.name ? place.name : this.getLocality(place);
			if(desc && desc['infoWindowDescription']){
				icon = desc['infoWindowIcon'];
				description = desc['infoWindowDescription'];
				address = null;
				title = null;
			}
			content = this.getInfoWindowHtml(icon, title, description, address);
			if(content){
				infoWindow.setContent(content);
				if(showInfoWindow){
					infoWindow.open(this.map, marker);
				}
			}
		}
		this.fitMarkers();
		if(window[this.costSummaryRequest]){
			window[this.costSummaryRequest](this.getMapModel());
		}
	}
	Woald.gmaps.prototype.dragEnd = function(marker, args){
		var $elem = args['elem']
			, latLng = [marker['latLng']['lat'](), marker['latLng']['lng']()].join(',');
		if($elem){
			$elem.val('');
		}
		this.geocodeRequest(latLng, args);
	}
	Woald.gmaps.prototype.updateWaypointsField = function(){
		var waypoints = this.$waypoints.find('input')
			, list = []
			, i
			, postData;
		for(i = 0; i < waypoints.length;i++){
			postData = $(waypoints[i]).attr('data-woald-postdata');
			if(postData){
				list.push(decodeURIComponent(postData));
			}
		}
		if(list.length > 0){
			this.$waypointsField.val(this.JSONStringify(list));
		}
	}
	Woald.gmaps.prototype.showDirection = function(args){
		var request
			, context = this
			, fromLatLng = this.$fromLatLng.val()
			, toLatLng = this.$toLatLng.val();
		args = args || {'showError': true};
		if(!fromLatLng || !toLatLng){
			if(args['showError'] && this.enableDirection){
				this.showError(this.directionEmptyError);
			}
			return;
		}
		this.closeError();
		if(!this.enableDirection){
			this.distanceMatrix();
			return;
		}
		
		request = {
			'origin': fromLatLng
			, 'destination': toLatLng
			, 'travelMode': this.selectedTravelMode.toUpperCase()
			, 'avoidHighways': this.avoidHighways
			, 'avoidTolls': this.avoidTolls
			, 'unitSystem': this.unit
		};
		
		this.progressIndicator('show');
		request = this.getWaypoints(request);
		this.directionsService.route(request, function(response, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				context.directionsDisplay.setDirections(response);
				context.resetMarkers();
				if(context.showDirectionStepsInline){
					context.createDirectionsMarkers(response);
				}
				context.distanceMatrix();
				context.fitMarkers();
			}else{
				context.showError(context.directionDataUnavailable);
			}
			context.progressIndicator('hide');
			if(context.directionUpdated){
				context.directionUpdated(response, status);
			}
		});
		this.closeError();
	}
	Woald.gmaps.prototype.resetDirection = function(){
		if(this.enableDirection && this.directionsDisplay){
			this.resetMarkers();
			google.maps.event.clearListeners(this.directionsDisplay, 'directions_changed');
			this.directionsDisplay.setMap(null);
			this.directionsDisplay.setPanel(null);
			this.directionsDisplay = new google.maps.DirectionsRenderer({ 'draggable': this.draggableMarker, 'suppressMarkers' : this.showDirectionStepsInline});
			this.directionsDisplay.setMap(this.map);
			google.maps.event.addListener(this.directionsDisplay, 'directions_changed', this.directionsChangedDelegate);
			if(this.$directionPanel.length > 0){
				this.directionsDisplay.setPanel(this.$directionPanel[0]);
			}
			if(window[this.costSummaryRequest]){
				window[this.costSummaryRequest]([]);
			}
		}
	}
	Woald.gmaps.prototype.createDirectionsMarkers = function(response){
		var legs = response.routes[0].legs
			, firstLeg = legs[0]
			, lastLeg = legs.length > 1 ? legs[legs.length - 1] : legs[0]
			, i
			, j
			, k = 1;
		if(firstLeg.start_location){
			this.directionsAddMarker(firstLeg.start_location, firstLeg.start_address);
		}
		if(lastLeg.end_location){
			this.directionsAddMarker(lastLeg.end_location, lastLeg.end_address);
		}
		for (i = 0; i < legs.length; i++) {
			for(j = 0; j < legs[i].steps.length; j++){
				if(i === 0 && j === 0){
					continue;
				}
				if(i === (legs.length -1) && (j === (legs[i].steps.length - 1))){
					continue;
				}
				this.directionsAddMarker(legs[i].steps[j].start_point, legs[i].steps[j].instructions, this.waypointMarkerIconUrl, this.waypointMarkerIconWidth, this.waypointMarkerIconHeight);
			}
		}
	}
	Woald.gmaps.prototype.directionsAddMarker = function(position, content, directionMarkerIconUrl, directionMarkerIconWidth, directionMarkerIconHeight) {
		var infoWindow
			, marker
			, toggleInfoWindowDelegate
			, markerParams = {}; 
		
		infoWindow = new google.maps.InfoWindow();
		infoWindow.setContent(content);
		if(!this.directionsInfoWindows){
			this.directionsInfoWindows = [];
		}
		this.directionsInfoWindows.push(infoWindow);
		marker = new google.maps.Marker({
			'position': position
			, 'map': this.map
			, 'anchorPoint': new google.maps.Point(0, -29)
			, 'draggable': this.draggableMarker
			, 'animation': google.maps.Animation.DROP
		});
		if(directionMarkerIconWidth && directionMarkerIconHeight){
			markerParams['scaledSize'] = new google.maps.Size(directionMarkerIconWidth, directionMarkerIconHeight);
		}
		if(directionMarkerIconUrl){
			markerParams['url'] = directionMarkerIconUrl;
		}
		if(!$.isEmptyObject(markerParams)){
			marker.setIcon(markerParams);
		}
		if(!this.directionsMarkers){
			this.directionsMarkers = [];
		}
		this.directionsMarkers.push(marker);
		toggleInfoWindowDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
			'infoWindow': infoWindow
			, 'marker': marker
		});
		google.maps.event.addListener(marker, 'click', toggleInfoWindowDelegate);
	}
	Woald.gmaps.prototype.directionsChangedHandler = function(){
		var dir = this.directionsDisplay.getDirections()
			, startAddress = dir.routes[0]['legs'][0]['start_address']
			, startLocation = dir.routes[0]['legs'][0]['start_location']
			, startPostData = {'address': startAddress, 'lat': startLocation.lat(), 'lng': startLocation.lng()}
			, endAddress
			, endLocation
			, endPostData
			, leg
			, $waypoints = this.$waypoints.find('input')
			, waypoint
			, $waypoint
			, waypointAddress
			, waypointLocation
			, waypointPostData
			, legs = dir.routes[0]['legs']
			, len
			, i;
		len = legs.length;
		endAddress = legs[len - 1]['end_address'];
		endLocation = legs[len - 1]['end_location'];
		endPostData = {'address': endAddress, 'lat': endLocation.lat(), 'lng': endLocation.lng()};
		
		for(i = 1; i < len; i++){
			leg = legs[i];
			if($waypoints.length > 0){
				waypointAddress = leg['start_address'];
				waypointLocation = leg['start_location'];
				waypointPostData = {'address': waypointAddress, 'lat': waypointLocation.lat(), 'lng': waypointLocation.lng()};
				$waypoint = $($waypoints[i - 1]);
				$waypoint.attr('data-woald-postdata', this.encodedStringify(waypointPostData));
				$waypoint.val(waypointAddress);
			}
		}
		
		if(this.$fromLatLng.attr('type') === 'text'){
			this.$fromLatLng.val(startAddress);
			this.$fromLatLng.attr('data-woald-postdata', this.encodedStringify(startPostData));
			this.$departureField.val(this.JSONStringify(startPostData));
		}
		if(this.$toLatLng.attr('type') === 'text'){
			this.$toLatLng.val(endAddress);
			this.$toLatLng.attr('data-woald-postdata', this.encodedStringify(endPostData));
			this.$destinationField.val(this.JSONStringify(endPostData));
		}
		this.updateWaypointsField();
		this.distanceMatrix();
		if(!this.enableDistance && window[this.costSummaryRequest]){
			window[this.costSummaryRequest](this.getMapModel());
		}
	}
	Woald.gmaps.prototype.getWaypoints = function(request){
		var $waypoints = this.$waypoints.find('input')
			, waypoints = []
			, context = this
			, i;
			
		$.each($waypoints, function(i, value){
			var val = $.trim($(value).val());
			if(val){
				waypoints.push({'location': val, 'stopover': context.stopover});
			}
		});
		
		if(waypoints.length > 0){
			request['waypoints'] = waypoints;
			if(this.stopover && this.optimizeWaypoints){
				request['optimizeWaypoints'] = this.optimizeWaypoints;
			}
		}
		return request;
	}
	Woald.gmaps.prototype.distanceMatrix = function(){
		var fromLatLng = this.$fromLatLng.val()
			, toLatLng = this.$toLatLng.val()
			, request
			, result
			, i;
		
		if(!fromLatLng && !toLatLng){
			return;
		}
		if(!this.enableDistance){
			return;
		}
		this.progressIndicator('show');
		
		if(!this.distanceMatrixService){
			this.distanceMatrixService = new google.maps.DistanceMatrixService();
		}
		if(!this.distanceMatrixDelegate){
			this.distanceMatrixDelegate = Woald.createDelegate(this, this.distanceMatrixHandler);
		}
		request = {
			'origins': [fromLatLng]
			, 'destinations': [toLatLng]
			, 'travelMode': this.selectedTravelMode.toUpperCase()
			, 'unitSystem': this.unit
			, 'durationInTraffic': false
			, 'avoidHighways': this.avoidHighways
			, 'avoidTolls': this.avoidTolls
		};
		result = this.getWaypoints({});
		if(result['waypoints']){
			request['destinations'] = [];
			for(i in result['waypoints']){
				request['destinations'].push(result['waypoints'][i]['location']);
			}
			request['destinations'].push(toLatLng);
		}
		this.distanceMatrixService.getDistanceMatrix(request, this.distanceMatrixDelegate);
	}
	Woald.gmaps.prototype.distanceMatrixHandler = function(response, status){
		var origins
			, destinations
			, results
			, element
			, dist
			, totalDistance = 0
			, totalDuration = 0
			, totalDistanceAfterConversion
			, from
			, to
			, i
			, j
			, html;
		
		this.progressIndicator('hide');
		
		if (status !== google.maps.DistanceMatrixStatus.OK) {
			return;
		}
		origins = response.originAddresses;
		destinations = response.destinationAddresses;
		
		if(this.$distancePlaceHolder.length > 0){
			this.$distancePlaceHolder.empty();
			this.$distancePlaceHolder.removeClass('hide');
		}
		for (i = 0; i < origins.length; i++) {
			results = response.rows[i].elements;
			for (j = 0; j < results.length; j++) {
				element = results[j];
				totalDistance += element.distance.value;
				totalDuration += element.duration.value;
				if(this.$distancePlaceHolder.length > 0){
					from = origins[i];
					to = destinations[j];
					dist = accounting.formatNumber(element.distance.value/this.conversion, this.decimalPrecision, this.thousandsSep, this.decimalPoint);
					html = this.getDistanceHtml(dist + ' ' + this.unitLabelText, element.duration.text, from, to);
					this.$distancePlaceHolder.append(html); 
				}
			}
		}
		totalDistanceAfterConversion = accounting.formatNumber(totalDistance/this.conversion, this.decimalPrecision, this.thousandsSep, this.decimalPoint);
		this.$distanceField.val(totalDistanceAfterConversion);
		this.$durationField.val(totalDuration);
		if(window[this.costSummaryRequest]){
			window[this.costSummaryRequest](this.getMapModel());
		}
		if(this.distanceUpdated){
			this.distanceUpdated(totalDistance, totalDistanceAfterConversion);
		}
	}
	Woald.gmaps.prototype.getFriendlyDuration = function(value){
		var inputSeconds = parseInt(value, 10)
			, hours   = Math.floor(inputSeconds / 3600)
			, minutes = Math.floor((inputSeconds - (hours * 3600)) / 60)
			, seconds = inputSeconds - (hours * 3600) - (minutes * 60);

		if (minutes < 10) {
			minutes = "0"+minutes;
		}
		if (seconds < 10) {
			seconds = "0"+seconds;
		}
		return hours + ':' + minutes; // + ':' + seconds;
	}
	Woald.gmaps.prototype.getDistanceHtml = function(distance, duration, from, to){
		return '<div class="calendarista-typography--body2">'
					+ '<div>' + this.distanceLabelText 
						+ ' <span class="woald-item-total-distance">' + distance + '</span>'
					+ '</div>'
					+ '<div>'
						+ from + ' - ' + to + ' / <strong>' + this.durationLabelText + '</strong>: ' + duration
					+ '</div>'
				+ '</div>';
		
	}
	Woald.gmaps.prototype.getWaypointsHtml = function(id, direction){
		direction = direction === Woald.gmaps.collapseDirection.inside ? 'in' : 'out';
		return '<div class="form-group calendarista-row-single collapse ' + direction + ' woald-gmaps-field-shade woald-waypoint-group">'
					+ '<div class="input-group">'
						+ '<span class="input-group-text woald-waypoint-badge">' + id + '</span>'
						+ '<input type="text" class="form-control calendarista-typography--caption1" placeholder="Add a stop on the way" name="waypoint[]" data-woald-waypoint-id="' + id + '" />'
						+ '<button type="button" class="btn btn-outline-secondary calendarista-typography--button" name="waypointremove"  title="Remove this stop" data-woald-id="' + id + '">'
							+ '<i class="fa fa-minus"></i>'
						+ '</button>'
					+ '</div>'
				+ '</div>';
	}
	Woald.gmaps.prototype.getWaypointContextMenu = function(id){
		return '<a href="#" class="list-group-item list-group-item-action calendarista-typography--caption1" data-woald-menu="2" data-woald-waypoint-id="' + id + '"><span class="badge badge-dark woald-waypoint-badge">' + id + '</span> ' + this.waypointContextMenuLabel + '</a>';
	}
	Woald.gmaps.prototype.getContextMenu = function(){
		var result = [];
		if(this.enableDepartureField && this.$fromLatLng.attr('type') === 'text'){
			result.push('<a href="#" class="list-group-item list-group-item-action calendarista-typography--caption1" data-woald-menu="0"><i class="fas fa-map-marker"></i>&nbsp;' + this.departureContextMenuLabel + '</a>');
		}
		if(this.enableDestinationField && this.$toLatLng.attr('type') === 'text'){
			result.push('<a href="#" class="list-group-item list-group-item-action calendarista-typography--caption1" data-woald-menu="1"><i class="fas fa-map-marker"></i>&nbsp;' + this.destinationContextMenuLabel + '</a>');
		}
		if(result.length){
			return '<div class="list-group woald-gmaps-contextmenu hide">' + result.join('')  + '</div>';
		}
		if(this.$contextMenuHint.length){
			this.$contextMenuHint.addClass('hide');
		}
		return null;
	}
	Woald.gmaps.prototype.getInfoWindowHtml = function(icon, title, description1, description2){
		var content = ''; 
		if(icon){
			content = 	'<div class="col-xl-3 woald-thumbnail-col">'
							+ '<img src="' + icon + '" alt="" class="img-thumbnail">'
						+ '</div>'
						+ '<div class="col-xl-9 woald-info-col">';
		}
		if(description1 || description2){
			content +=	'<div class="woald-infowindow">';
		}
		if(description1){
			content += 	'<div class="woald-description">' + description1 + '</div>';
		}
		if(description2){
			content += 	'<div class="woald-address">'; 
			content += 		'<strong class="woald-title">' + title + '</strong><br>';
			content +=		description2;
			content +=	'</div>';
		}
		if(description1 || description2){
			content +=	'</div>';
		}
		if(icon){
			content += '</div>';
		}
		return content;
	}
	Woald.gmaps.prototype.directionPanelCloseHandler  = function(e){
		this.$direction.button('toggle');
		this.$directionPanel.calendaristaCollapse('hide');
	}
	Woald.gmaps.prototype.resize = function(){
		if(this.map && this.map.getCenter){
			var center = this.map.getCenter();
			google.maps.event.trigger(this.map, 'resize');
			this.map.setCenter(center);
		}
	}
	Woald.gmaps.prototype.closeAllInfoWindows = function(){
		var i
			, infoWindow
			, marker
			, flag;
		for(i in this.infoWindows){
			infoWindow = this.infoWindows[i];
			marker = this.markers[i];
			if(infoWindow && this.isOpen(infoWindow)){
				infoWindow.close(this.map, marker);
			}
		}
		if(this.defaultLocationMarker && this.defaultLocationInfoWindow){
			if(this.isOpen(this.defaultLocationInfoWindow)){
				this.defaultLocationInfoWindow.close(this.map, this.defaultLocationMarker);
			}
		}
	}
	Woald.gmaps.prototype.isOpen = function(infoWindow){
		var map = infoWindow.getMap();
		return (map !== null && typeof(map) !== 'undefined');
	}
	Woald.gmaps.prototype.toggleInfoWindowHandler = function(e, args){
		this.toggleInfoWindow(args);
	}
	Woald.gmaps.prototype.toggleInfoWindow = function(args){
		var infoWindow = args['infoWindow']
			, marker = args['marker']
			, toggle;
		if(!infoWindow){
			return;
		}
		toggle = this.isOpen(infoWindow);
		if(!toggle){
			infoWindow.open(this.map, marker);
		} else{
			infoWindow.close(this.map, marker);
		}
	}
	
	Woald.gmaps.prototype.geoLocate = function(args){
		// Try W3C Geolocation (Preferred)
		var options = {
			'enableHighAccuracy': false
			, 'timeout': 5000
			, 'maximumAge': 0
		};
		if(navigator.geolocation) {
			this.browserSupportFlag = true;
			this.getCurrentPositionDelegate = Woald.createCallback(this.getCurrentPosition, this,  args);
			this.handleNoGeoLocationDelegate = Woald.createDelegate(this, this.handleNoGeoLocation);
			navigator.geolocation.getCurrentPosition(this.getCurrentPositionDelegate, this.handleNoGeoLocationDelegate, null);
		}
		// Browser doesn't support Geolocation
		else {
			this.browserSupportFlag = false;
			this.handleNoGeolocation();
		}
		this.closeError();
	}
	
	Woald.gmaps.prototype.geocodeRequest = function(pos, args) {
		var location = pos.split(',')
			, lat
			, lng
			, latLng
			, param = {'address': pos};
		if(location){
			latLng = new google.maps.LatLng(location[0], location[1]);
			param = {'latLng': latLng};
		}
		args['pos'] = pos;
		this.geocode(param, args);
	}

	Woald.gmaps.prototype.getCurrentPosition = function(position, args) {
		var latLng = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
		this.geocode({'latLng': latLng}, args);
	}
	
	Woald.gmaps.prototype.geocode = function(request, args){
		var reverseGeocodeDelegate;
		if(!this.geocoder){
			this.geocoder = new google.maps.Geocoder();
		}
		if(!args['preloading']){
			this.progressIndicator('show');
		}
		reverseGeocodeDelegate = Woald.createCallback(this.reverseGeocodeHandler, this,  args);
		this.geocoder.geocode(request, reverseGeocodeDelegate);
	}
	Woald.gmaps.prototype.reverseGeocodeHandler = function(results, status, args){
		var $elem = args['elem']
			, $field = args['hiddenField'];
		if(!args['preloading']){
			this.progressIndicator('hide');
		}
		if (status === google.maps.GeocoderStatus.OK) {
			if($elem && $elem.val() === ''){
				$elem.val(results[0].formatted_address);
			}
			this.locate(results[0], args);
		}else
        {
            if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT)
            {
				(function(args, context){
					window.setTimeout(function() { 
						context.geocodeRequest(args['pos'], args); 
					}, context.queryLimitTimeout);
				})(args, this);
            }
        }
		if($field && ['departure', 'destination'].indexOf($field.attr('name')) !== -1){
			if(!this.enableDirection && window[this.costSummaryRequest]){
				window[this.costSummaryRequest](this.getMapModel());
			}
		}
		this.loadNextLocation();
	}
	Woald.gmaps.prototype.loadNextLocation = function(){
		if(!this.initSearch()){
			this.showDirection({'showError': false});
		}
	}
	Woald.gmaps.prototype.handleNoGeoLocation = function(e) {
		var initialLocation = new google.maps.LatLng(this.defaultLocation['lat'], this.defaultLocation['lng']);
		if (this.browserSupportFlag === true) {
			this.showError(this.posErrorMsg);
			window.console.log(e['message'] || "Geolocation service failed.");
		} else {
			this.showError(this.geolocationErrorMsg);
			window.console.log(e['message'] || "Your browser doesn't support geolocation.");
		}
		this.map.setCenter(initialLocation);
	}
	Woald.gmaps.prototype.showError = function(msg){
		if(!msg){
			return;
		}
		this.$errorContainer.html(msg);
		this.$errorPanelContainer.removeClass('hide');
		this.$errorPanel.calendaristaCollapse('show');
	}
	Woald.gmaps.prototype.closeError = function(){
		this.$errorPanel.calendaristaCollapse('hide');
	}
	Woald.gmaps.prototype.errorPanelCloseHandler  = function(e){
		this.closeError();
	}
	Woald.gmaps.prototype.unloadHandler = function () {
		this.unload();
	}
	Woald.gmaps.prototype.encodedStringify = function(value){
		return encodeURIComponent(this.JSONStringify(value));
	}
	Woald.gmaps.prototype.JSONParse = function(value){
		return window['JSON'].parse(decodeURIComponent(value));
	}
	Woald.gmaps.prototype.JSONStringify = function(value){
		return window['JSON'].stringify(value);
	}
	Woald.gmaps.prototype.getMapModel = function(){
		/*
			We do not want to show fromAddress and toAddress. 
			This is repetitive data as it's already reflected in the step fields this.$departureField
		*/
		var $waypoints = $('input[name="waypoints"]')
			, args = [
				{'name': 'fromPlaceId', 'value': this.$fromPlaceId.val()}
				, {'name': 'toPlaceId', 'value': this.$toPlaceId.val()}
				, {'name': 'distance', 'value': this.$distanceField.val()}
				, {'name': 'duration', 'value': this.$durationField.val()}
				, {'name': 'departure', 'value': this.$departureField.val()}
				, {'name': 'destination', 'value': this.$destinationField.val()}
				, {'name': 'fromLatLng', 'value': this.$fromLatLng.val()}
				, {'name': 'toLatLng', 'value': this.$toLatLng.val()}
				//, {'name': 'ignoreAddress', 'value': true }
				, {'name': 'unitType', 'value': this.$unitField.val() == 'km' ? 0 : 1}
				, {'name': 'unit', 'value': this.$unitField.val() }
			];
			if($waypoints.length > 0 && $waypoints.val()){
				args.push({'name': 'waypoints', 'value': $waypoints.val()});
			}
		return args;
	};
	Woald.gmaps.prototype.decodeSpecialCharacters = function(str){
		if(!str){
			return '';
		}
		return $('<div/>').html(str).text();
	};
	Woald.gmaps.prototype.unload = function(){
		var selector
			, elem
			, i;
		if(this.autocompletes){
			for(i in this.autocompletes){
				google.maps.event.clearListeners(this.autocompletes[i], 'place_changed');
			}
			this.autocompletes.length = 0;
		}
		if(this.markers){
			for(i in this.markers){
				google.maps.event.clearListeners(this.markers[i], 'click');
				google.maps.event.clearListeners(this.markers[i], 'dragend');
			}
			this.markers.length = 0;
		}
		if(this.autocompletesWaypoint){
			for(i in this.autocompletesWaypoint){
				google.maps.event.clearListeners(this.autocompletesWaypoint[i]['obj'], 'place_changed');
			}
			this.autocompletesWaypoint.length = 0;
		}
		if(this.markersWaypoint){
			for(i in this.markersWaypoint){
				google.maps.event.clearListeners(this.markersWaypoint[i]['obj'], 'click');
				google.maps.event.clearListeners(this.markersWaypoint[i]['obj'], 'dragend');
			}
			this.markersWaypoint.length = 0;
		}
		this.$travelModeList.off();
		if(this.enableContextMenu){
			google.maps.event.clearListeners(this.map, 'rightclick');
			google.maps.event.clearListeners(this.map, 'click');
			$(window.document).off('click', this.contextMenuExitDelegate);
		}
		if(this.tilesLoadedDelegate){
			google.maps.event.clearListeners(this.map, 'tilesloaded');
		}
		if(this.highwayClickDelegate){
			this.$highway.off('change', this.highwayClickDelegate);
		}
		if(this.tollClickDelegate){
			this.$toll.off('change', this.tollClickDelegate);
		}
		if(this.trafficClickDelegate){
			this.$traffic.off('change', this.trafficClickDelegate);
		}
		if(this.reverseClickDelegate){
			this.$reverse.off('change', this.reverseClickDelegate);
		}
		if(this.directionClickDelegate){
			this.$direction.off('change', this.directionClickDelegate);
		}
		if(this.milesClickDelegate){
			this.$miles.off('change', this.milesClickDelegate);
		}
		if(this.kmClickDelegate){
			this.$km.off('change', this.kmClickDelegate);
		}
		if(this.myPosClickDelegate){
			this.$myPosButton.off('change', this.myPosClickDelegate);
		}
		if(this.directionPanelCloseDelegate){
			this.$directionPanelCloseButton.off('change', this.directionPanelCloseDelegate);
		}
		if(this.errorPanelCloseDelegate){
			this.$errorPanelCloseButton.off('change', this.errorPanelCloseDelegate);
		}
		if(this.zoomInDelegate){
			this.$zoomInButton.off('change', this.zoomInDelegate);
		}
		if(this.directionsDisplay){
			google.maps.event.clearListeners(this.directionsDisplay, 'directions_changed');
		}
		if(this.waypointsAddDelegate){
			this.$waypointsAddButton.off('click', this.waypointsAddDelegate);
		}
		if(this.autocompleteOnPasteDelegate){
			this.$fromLatLng.off('paste', this.autocompleteOnPasteDelegate);
			this.$toLatLng.off('paste', this.autocompleteOnPasteDelegate);
		}
		if(this.directionPanelToggleDelegate){
			this.$directionPanel.off('shown.bs.collapse', this.directionPanelToggleDelegate);
			this.$directionPanel.off('hidden.bs.collapse', this.directionPanelToggleDelegate);
		}
		if(this.trafficLayerMapChangedDelegate){
			google.maps.event.clearListeners(this.trafficLayer, 'map_changed');
		}
		if(this.fromLatLngChangeDelegate){
			this.$fromLatLng.off('change', this.fromLatLngChangeDelegate);
		}
		if(this.toLatLngChangeDelegate){
			this.$toLatLng.on('change', this.toLatLngChangeDelegate);
		}
		if(this.$waypoionts && this.$waypoints.length > 0){
			this.$waypoints.find('input').off();
			this.$waypoints.find('button[name="waypointremove"]').off();
			this.$waypoints.find('.form-group').off();
		}
		if(this.$contextMenu && this.$contextMenu.length > 0){
			this.$contextMenu.find('a').off();
		}
		for(i = 0; i < this.placeChangedListeners; i++){
			google.maps.event.clearListeners(this.placeChangedListeners[i], 'place_changed');
		}
		this.clearDefaultLocation();
		if(this.initDelegate){
			this.$window.off('load', this.initDelegate);
		}
		if(this.resizeDelegate){
			this.$window.off('resize', this.resizeDelegate);
		}
		if(this.unloadDelegate){
			this.$window.off('unload', this.unloadDelegate);
		}
		if(this.distanceMatrixService){
			delete this.distanceMatrixService;
		}
		if(this.distanceMatrixDelegate){
			delete this.distanceMatrixDelegate;
		}
	}
}(window, document, google, Woald, window['jQuery'], window['accounting'], window['calendarista_wp_ajax']));
(function(window, Woald){
	"use strict";
	Woald.gmaps.prototype.getTemplate = function () {
		return '<div class="woald-container">'
				+ '<div class="woald-controls form-horizontal">'
					+ '<div class="woald-content collapse in">'
						+ '<div class="col-lg-12 woald-photo-background">'
							+ '<div class="woald-content-inner">'
								+ '<div class="form-group woald-options1-container">'
									+ '<div class="col-lg-12">'
										+ '<div class="btn-group woald-button-right-margin" data-toggle="buttons">'
										 + '<label class="btn btn-default woald-minimize-button" title="Minimize View">'
											+ '<input type="checkbox" name="minimize" data-parsley-excluded="true" checked><i class="fa fa-chevron-up"></i>'
										  + '</label>'
										  + '<label class="btn btn-primary woald-direction" title="Show direction">'
											+ '<input type="checkbox" name="direction" data-parsley-excluded="true"><i class="fa fa-road"></i>'
										  + '</label>'
										  + '<label class="btn btn-primary woald-reverse" title="Reverse direction">'
											+ '<input type="checkbox" name="reverse" data-parsley-excluded="true"><i class="fa fa-sort"></i>'
										  + '</label>'
										+ '</div>'
										+ '<div class="btn-group woald-button-right-margin woald-measurement-container" data-toggle="buttons">'
										  + '<label class="btn btn-default woald-miles">'
											+ '<input type="radio" name="measurement" value="miles" data-parsley-excluded="true" checked>Miles'
										  + '</label>'
										  + '<label class="btn btn-default woald-km">'
											+ '<input type="radio" name="measurement" data-parsley-excluded="true" value="km">KM'
										  + '</label>'
										+ '</div>'
										+ '<div class="btn-group woald-travelmode">'
										  + '<button type="button" class="btn btn-default woald-travelmode-heading" data-parsley-excluded="true"></button>'
										  + '<button type="button" class="btn btn-default" data-toggle="dropdown" aria-expanded="false" data-parsley-excluded="true">'
											+ '<span class="caret"></span>'
											+ '<span class="sr-only">Toggle Dropdown</span>'
										  + '</button>'
										  + '<ul class="dropdown-menu" role="menu">'
											+ '<li><a href="#" data-woald-drivingmode="driving"></a></li>'
											+ '<li><a href="#" data-woald-drivingmode="walking"></a></li>'
											+ '<li><a href="#" data-woald-drivingmode="bicycling"></a></li>'
											+ '<li><a href="#" data-woald-drivingmode="transit"></a></li>'
										  + '</ul>'
										+ '</div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="form-group form-inline woald-options2-container">'
									+ '<div class="col-lg-12">'
										 + '<div class="checkbox">'
											+ '<label class="woald-inline-checkbox">'
												+ '<input type="checkbox" name="highway" data-parsley-excluded="true" checked>Avoid Highways'
											+ '</label>'
										+ '</div>'
										+ '<div class="checkbox">'
										  + '<label class="woald-inline-checkbox">'
											+ '<input type="checkbox" name="toll" data-parsley-excluded="true"> Avoid Tolls'
										  + '</label>'
										+ '</div>'
										+ '<div class="checkbox">'
										  + '<label class="woald-inline-checkbox">'
											+ '<input type="checkbox" name="traffic" data-parsley-excluded="true">Show Traffic'
										  + '</label>'
										+ '</div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="form-group woald-progressbar collapse out">'
									+ '<div class="col-lg-12">'
										+ '<div class="progress progress-striped">'
											+ '<div class="progress-bar"></div>'
										+ '</div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="woald-content-before hide"></div>'
								+ '<div class="form-group woald-gmaps-field-shade woald-departure-field-container">'
									+ '<div class="col-lg-12">'
										+ '<div class="input-group">'
											+ '<input type="text" '
												+ 'class="woald_parsley_validated form-control" '
												+ 'placeholder="Departure" '
												+ 'data-parsley-trigger="change" '
												+ 'data-parsley-errors-container=".fromlatlng-error-container" '
												+ 'name="fromLatLng" />'
												+ '<span class="input-group-btn woald-departure-group">'
												+ '<button type="button" '
													+ 'class="btn btn-default" '
													+ 'name="search">'
													+ '<i class="fa fa-search"></i>'
												+ '</button>'
												+ '<button type="button" '
													+ 'class="btn btn-primary" '
													+ 'title="Find my position" '
													+ 'name="mypos">'
													+ '<i class="fa fa-record"></i>'
												+ '</button>'
											+ '</span>'
										+ '</div>'
										+ '<div class="fromlatlng-error-container"></div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="woald-waypoints-placeholder"></div>'
								+ '<div class="form-group woald-gmaps-field-shade woald-destination-field-container">'
									+ '<div class="col-lg-12">'
										+ '<div class="input-group">'
											+ '<input type="text" '
												+ 'class="woald_parsley_validated form-control" '
												+ 'placeholder="Destination" '
												+ 'data-parsley-trigger="change" '
												+ 'data-parsley-errors-container=".tolatlng-error-container" '
												+ 'name="toLatLng">'
												+ '<span class="input-group-btn woald-destination-group">'
													+ '<button type="button" '
														+ 'class="btn btn-default" '
														+ 'name="search"> '
														+ '<i class="fa fa-search"></i>'
													+ '</button>'
												+ '</span>'
											+ '</div>'
											+ '<div class="tolatlng-error-container"></div>'
										+ '</div>'
								+ '</div>'
								+ '<div class="form-group woald-waypoint-add-button-container">'
									+ '<div class="col-lg-12">'
										+ '<div class="pull-right">'
											+ '<button type="button" '
												+ 'class="btn btn-primary woald-button-right-margin" '
												+ 'name="add" title="Add way point">'
												+ '<i class="fa fa-plus"></i>'
											+ '</button>'
											+ '<div class="clearfix"></div>'
										+ '</div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="woald-content-after hide"></div>'
								+ '<div class="form-group woald-distance-placeholder hide"></div>'
								+ '<div class="form-group woald-distance-and-cost hide">'
									+ '<div class="col-lg-12">'
										+ '<h3 class="form-control-static">'
											+ '<span class="woald-totals"><span class="woald-total-distance"></span> <span class="woald-total-cost" title="Total cost"><span></span>'
										+ '</h3>'
									+ '</div>'
									+ '<div class="clearfix"></div>'
								+ '</div>'
								+ '<div class="form-group woald-submit-button">'
									+ '<div class="col-lg-12">'
										+ '<button type="submit" name="submitButton" class="btn btn-primary pull-right">Submit</button>'
										+ '<div class="clearfix"></div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="woald-content-end hide"></div>'
								+ '<div class="form-group woald-error-panel-container hide">'
									+ '<div class="col-lg-12">'
										+ '<div class="woald-error-panel collapse out">'
											+ '<div class="alert alert-danger" role="alert">'
												+ '<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'
												+ '<span class="woald-error-container"></span>'
											+ '</div>'
										+ '</div>'
									+ '</div>'
								+ '</div>'
								+ '<div class="form-group woald-direction-panel-container hide">'
									+ '<div class="col-lg-12">'
										+ '<div class="woald-direction-panel collapse out">'
										+ '</div>'
									+ '</div>'
								+ '</div>'
							+ '</div>'
						+ '</div>'
						+ '<div class="clearfix"></div>'
					+ '</div>'
					+ '<div class="clearfix"></div>'
					+ '<div class="btn-group-vertical woald-knobs">'
						+ '<button type="button" class="btn btn-default woald-minimize-button" title="Minimize View"><i class="fa fa-chevron-up"></i></button>'
						+ '<button type="button" class="btn btn-default woald-zoom-in-button" title="Zoom in"><i class="fa fa-plus"></i></button>'
						+ '<button type="button" class="btn btn-default woald-zoom-out-button" title="Zoom out"><i class="fa fa-minus"></i></button>'
					+ '</div>'
					+ '<div class="clearfix"></div>'
				+ '</div>'
				+ '<div class="woald-map">'
					+ '<div class="woald-map-canvas"/>'
				+ '</div>'
			+ '</div>';
	}
}(window, Woald));
