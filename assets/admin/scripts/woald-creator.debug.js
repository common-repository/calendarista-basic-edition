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
(function(window, document, google, Woald, $, accounting){
	"use strict";
	Woald.creator = function(options){
		this.options = options || {};
		this.searchFields = [];
		this.zoom = options['zoom'] ? options['zoom'] : 2;
		this.previewImageUrl = options['previewImageUrl'];
		this.selectedMapStyle = options['selectedMapStyle'];
		this.loadingNearbyMesssage = 'Loading nearby locations';
		this.loadingMapTilesMessage = 'Loading Map Tiles';
		this.loadingGeocodeMessage = 'Requesting geocode data';
		this.addressContextMenuLabel = 'Address';
		this.departureContextMenuLabel = 'Departure';
		this.destinationContextMenuLabel = 'Destination';
		this.waypointContextMenuLabel = 'Waypoint';
		this.newPlaceContextMenuLabel = 'Add new place';
		this.resizeDelegate = Woald.createDelegate(this, this.resize);
		this.unloadDelegate = Woald.createDelegate(this, this.unloadHandler);
		google.maps.event.addDomListener(window, 'resize', this.resizeDelegate );
		google.maps.event.addDomListener(window, 'unload', this.unloadDelegate );
		this.initialize();
	}
	
	Woald.creator.placeType = {
		'departure': 0
		, 'destination': 1
	};
	Woald.creator.contextMenu = {
		'initaialLocation': 0
		, 'departure' : 1
		, 'destination' : 2
		, 'waypoint' : 3
		, 'addNewPlace': 4
	}
	Woald.creator.prototype.progressBarShow = function(report){
		if(report){
			this.modalProgressReport.html(report);
		}
		this.modalProgress.dialog('open').is(':visible');
		//this.modalProgressBar.addClass('woald-stretch');
	}
	
	Woald.creator.prototype.progressBarHide = function(){
		//this.modalProgressBar.removeClass('woald-stretch');
		this.modalProgress.dialog('close').is(':visible');
	}
	
	Woald.creator.prototype.initialize = function(){
		var options = this.options
			, lat = options['lat'] ? options['lat'] : 0
			, lng = options['lng'] ? options['lng'] : 0
			, zoom = lat && lng ? 13 : this.zoom
			, nowhere = new google.maps.LatLng(lat, lng);
		this.location = nowhere;
		this.$root = $(options['id']);
		this.$mapElement = this.$root.find('.woald-map-canvas');
		if(this.$mapElement.length > 0){
			this.map = new google.maps.Map(this.$mapElement[0], 
			{
				center: this.location
				, zoom: zoom
				, mapTypeId: google.maps.MapTypeId.ROADMAP
				, clickableIcons: false
			});
			
			this.mapContextMenuDelegate = Woald.createDelegate(this, this.mapContextMenuHandler);
			google.maps.event.addListener(this.map, 'rightclick', this.mapContextMenuDelegate);
			
			this.contextMenuExitDelegate = Woald.createDelegate(this, this.contextMenuExitHandler);
			google.maps.event.addListener(this.map, 'click', this.contextMenuExitDelegate);
			$(window.document).on('click', this.contextMenuExitDelegate);
			
			this.tilesLoadedDelegate = Woald.createDelegate(this, this.tilesLoadedHandler);
			google.maps.event.addDomListener(this.map, 'tilesloaded', this.tilesLoadedDelegate);
		}
		
		this.initControls(options);
		this.browserSupportFlag =  new Boolean(false);
		if(this.map){
			this.progressBarShow(this.loadingMapTilesMessage);
			this.autocompleteInit();
			this.$mapDiv = $(this.map.getDiv());
			this.$mapDiv.append(this.getContextMenu());
			this.$contextMenu = this.$root.find('.woald-creator-contextmenu');
			this.$contextMenu.menu();
			this.contextMenuItemClickDelegate = Woald.createDelegate(this, this.contextMenuItemClickHandler);
			this.$contextMenu.find('a').on('click', this.contextMenuItemClickDelegate);
		}
		this.initializeWaypoints();
	}
	Woald.creator.prototype.initControls = function(options){
		var context = this;
		this.$mapWidth = this.$root.find('input[name="mapWidth"]');
		this.$mapHeight = this.$root.find('input[name="mapHeight"]');
		this.$mapWidthUnit = this.$root.find('select[name="mapWidthUnit"]');
		this.$mapHeightUnit = this.$root.find('select[name="mapHeightUnit"]');
		this.$regionLat = this.$root.find('#regionLat');
		this.$regionLng = this.$root.find('#regionLng');
		this.$regionAddress = this.$root.find('input[name="regionAddress"]');
		this.$regionLatLng = this.$root.find('#regionLatLng');
		this.$restrictRadius = this.$root.find('#restrictRadius');
		this.$regionMarkerIconUrl = this.$root.find('input[name="regionMarkerIconUrl"]');
		this.$regionMarkerIconHeight = this.$root.find('input[name="regionMarkerIconHeight"]');
		this.$regionMarkerIconWidth = this.$root.find('input[name="regionMarkerIconWidth"]');
		this.$regionInfoWindowIcon = this.$root.find('input[name="regionInfoWindowIcon"]');
		this.$regionInfoWindowDescription = this.$root.find('textarea[name="regionInfoWindowDescription"]');
		this.$regionShowMarker = this.$root.find('input[name="regionShowMarker"]');
		this.$fromLat = this.$root.find('input[name="fromLat"]');
		this.$fromLng = this.$root.find('input[name="fromLng"]');
		this.$contextMenuType = this.$root.find('input[name="contextMenuType"]');
		this.$nearbyDeparturePlaces = this.$root.find('button[name="nearbyDeparturePlaces"]');
		this.$departurePlacesList = this.$root.find('select[name="departurePlacesList"]');
		this.$departurePlacesPreview = this.$root.find('.departure-places-preview');
		this.$addDeparture = this.$root.find('button[name="addDeparture"]');
		this.$clearDepartureItems = this.$root.find('button[name="clearDepartureItems"]');
		this.$toAddress = this.$root.find('input[name="toAddress"]');
		this.$toLatLng = this.$root.find('#toLatLng');
		this.$toInfoWindowIcon = this.$root.find('input[name="toInfoWindowIcon"]');
		this.$toInfoWindowDescription = this.$root.find('textarea[name="toInfoWindowDescription"]');
		this.$destinationListAttributes = this.$root.find('#destinationListAttributes');
		this.$nearbyDestinationPlaces = this.$root.find('button[name="nearbyDestinationPlaces"]');
		this.$destinationPlacesList = this.$root.find('select[name="destinationPlacesList"]');
		this.$destinationLocationsGroup = this.$root.find('#destinationLocationsGroup');
		this.$addDestination = this.$root.find('button[name="addDestination"]');
		this.$destinationPlacesPreview = this.$root.find('.destination-places-preview');
		this.$fromPlacesPreload = this.$root.find('input[name="fromPlacesPreload"]');
		this.$toPlacesPreload = this.$root.find('input[name="toPlacesPreload"]');
		this.$enableContextMenu = this.$root.find('input[name="enableContextMenu"]');
		this.$optimizeWayPoints = this.$root.find('input[name="optimizeWayPoints"]');
		this.$draggableMarker = this.$root.find('input[name="draggableMarker"]');
		this.$enableDirection = this.$root.find('input[name="enableDirection"]');
		this.$enableDirectionButton = this.$root.find('input[name="enableDirectionButton"]');
		this.$placeModal = $('#placeModal');
		this.$placeType = this.$placeModal.find('input[name="placeType"]');
		this.$placeId = this.$placeModal.find('input[name="placeId"]');
		this.$placeLat = this.$placeModal.find('input[name="lat"]');
		this.$placeLng = this.$placeModal.find('input[name="lng"]');
		this.$placeName = this.$placeModal.find('input[name="name"]');
		this.$placeCost = this.$placeModal.find('input[name="cost"]');
		this.$placeIcon = this.$placeModal.find('input[name="markerIcon"]');
		this.$placeIconWidth = this.$placeModal.find('input[name="markerIconWidth"]');
		this.$placeIconHeight = this.$placeModal.find('input[name="markerIconHeight"]');
		this.$placeInfoWindowIcon = this.$placeModal.find('input[name="infoWindowIcon"]');
		this.$placeInfoWindowDescription = this.$placeModal.find('textarea[name="infoWindowDescription"]');
		this.$styledMaps = this.$root.find('select[name="styledMaps"]');
		this.$backgroundGradient = this.$root.find('select[name="backgroundGradient"]');
		this.$highway = this.$root.find('input[name="highway"]');
		this.$toll = this.$root.find('input[name="toll"]');
		this.$traffic = this.$root.find('input[name="traffic"]');
		this.$zoom = this.$root.find('input[name="zoom"]');
		this.$panToZoom = this.$root.find('input[name="panToZoom"]');
		this.$formCollapsed = this.$root.find('input[name="formCollapsed"]');
		this.$showTotalDistance = this.$root.find('input[name="showTotalDistance"]');
		this.$costPerUnit = this.$root.find('input[name="costPerUnit"]');
		this.$costUnitType = this.$root.find('select[name="costUnitType"]');
		this.$currencySymbol = this.$root.find('input[name="currencySymbol"]');
		this.$currencySymbolPosition = this.$root.find('select[name="currencySymbolPosition"]');
		this.$currency = this.$root.find('input[name="currency"]');
		this.$decimalPoint = this.$root.find('input[name="decimalPoint"]');
		this.$thousandsSep = this.$root.find('input[name="thousandsSep"]');
		this.$enableRegionPhotos = this.$root.find('input[name="enableRegionPhotos"]');
		this.$photoMaxWidth = this.$root.find('input[name="photoMaxWidth"]');
		this.$photoMaxHeight = this.$root.find('input[name="photoMaxHeight"]');
		this.$photoBackgroundSize = this.$root.find('input[name="photoBackgroundSize"]');
		this.$photoBackgroundRepeat = this.$root.find('select[name="photoBackgroundRepeat"]');
		this.$photoNearbySearchKeyword = this.$root.find('input[name="photoNearbySearchKeyword"]');
		this.$photoNearbySearchTypes = this.$root.find('select[name="photoNearbySearchTypes"]');
		this.$photoDelay = this.$root.find('input[name="photoDelay"]');
		this.$paramsBlock = this.$root.find('.params-block');
		this.$searchButton = this.$root.find('button[name="search"]');
		this.$myPosButton = this.$root.find('button[name="mypos"]');
		this.$preview = this.$root.find('#preview');
		this.$waypointFieldNames = this.$root.find('input[name="waypointFieldNames"]');
		this.$waypoints = this.$root.find('.woald-waypoints-placeholder tbody');
		this.$waypointsAddButton = this.$root.find('button[name="addwaypoint"]');
		this.$tabs = this.$root.find('.woald-tab a[data-toggle="tab"]');
		this.$enableDistanceMeasurement = this.$root.find('input[name="enableDistanceMeasurement"]');
		this.$enableHighway = this.$root.find('input[name="enableHighway"]');
		this.$enableTolls = this.$root.find('input[name="enableTolls"]');
		this.$enableTraffic = this.$root.find('input[name="enableTraffic"]');
		this.$enableCollapseButton = this.$root.find('input[name="enableCollapseButton"]');
		this.$enableWaypointButton = this.$root.find('input[name="enableWaypointButton"]');
		this.$enableDestinationField = this.$root.find('input[name="enableDestinationField"]');
		this.$enableDepartureField = this.$root.find('input[name="enableDepartureField"]');
		this.$enableSubmitButton = this.$root.find('input[name="enableSubmitButton"]');
		this.$enableScrollWheel = this.$root.find('input[name="enableScrollWheel"]');
		this.$submitButtonText = this.$root.find('input[name="submitButtonText"]');
		this.$driving = this.$root.find('input[name="driving"]');
		this.$labelDriving = this.$root.find('input[name="labelDriving"]');
		this.$walking = this.$root.find('input[name="walking"]');
		this.$labelWalking = this.$root.find('input[name="labelWalking"]');
		this.$bicycling = this.$root.find('input[name="bicycling"]');
		this.$labelBicycling = this.$root.find('input[name="labelBicycling"]');
		this.$transit = this.$root.find('input[name="transit"]');
		this.$labelTransit = this.$root.find('input[name="labeltransit"]');
		this.$defaultTravelMode = this.$root.find('select[name="defaultTravelMode"]');
		this.$fromContextMenuLabel = this.$root.find('input[name="fromContextMenuLabel"]');
		this.$toContextMenuLabel = this.$root.find('input[name="toContextMenuLabel"]');
		this.$waypointContextMenuLabel = this.$root.find('input[name="waypointContextMenuLabel"]');
		this.$directionEmptyError = this.$root.find('input[name="directionEmptyError"]');
		this.$directionDataUnavailable = this.$root.find('input[name="directionDataUnavailable"]');
		this.$queryLimitTimeout = this.$root.find('input[name="queryLimitTimeout"]');
		this.posErrorMsg = options['posErrorMsg'] ? options['posErrorMsg'] : 'An error occurred while trying to determine your position.';
		this.geolocationErrorMsg = options['geolocationErrorMsg'] ? options['geolocationErrorMsg'] : 'Your browser does not support geolocation. We have placed you somewhere.';
		this.directionEmptyError = options['directionEmptyError'] ? options['directionEmptyError'] : '<strong>Heads up!</strong> Provide both a departure and arrival address above to view direction data.';
		this.directionDataUnavailable = options['directionDataUnavailable'] ? options['directionDataUnavailable'] : '<strong>Heads up!</strong> Direction data unavailable. Probably chosen departure and destination are not connected by roads, too far perhaps?.';
		this.$waypointMarkerIconUrl = this.$root.find('input[name="waypointMarkerIconUrl"]');
		this.$waypointMarkerIconHeight = this.$root.find('input[name="waypointMarkerIconHeight"]');
		this.$waypointMarkerIconWidth = this.$root.find('input[name="waypointMarkerIconWidth"]');
		this.$showDirectionStepsInline = this.$root.find('input[name="showDirectionStepsInline"]');
		this.$contentBeforeSelector = this.$root.find('input[name="contentBeforeSelector"]');
		this.$contentAfterSelector = this.$root.find('input[name="contentAfterSelector"]');
		this.$contentEndSelector = this.$root.find('input[name="contentEndSelector"]');
		this.$destinationRequired = this.$root.find('input[name="destinationRequired"]');
		this.$departureRequired = this.$root.find('input[name="departureRequired"]');
		this.$validators = this.$root.find('.woald_parsley_validated');
		this.$placesValidators = this.$root.find('input[data-parsley-group="block_place"]');
		this.modalProgress = $('#progressModal').dialog({
					autoOpen: false
					, height: 'auto'
					, width: 'auto'
					, modal: true
					, dialogClass: 'calendarista-dialog'
		});
		this.modalProgressBar = this.modalProgress.find('#progress-bar');
		this.modalProgressBar.progressbar({
		  value: false
		});
		this.modalProgressReport = this.modalProgress.find('.progress-report');
		if(this.$waypointsAddButton.length > 0){
			this.waypointsAddDelegate = Woald.createDelegate(this, this.waypointsAddHandler);
			this.$waypointsAddButton.on('click', this.waypointsAddDelegate);
		}
		if(this.$searchButton.length > 0){
			this.searchClickDelegate = Woald.createDelegate(this, this.searchClickHandler);
			this.$searchButton.on('click', this.searchClickDelegate);
		}
		if(this.$myPosButton.length > 0){
			this.myPosClickDelegate = Woald.createDelegate(this, this.myPosClickHandler);
			google.maps.event.addDomListener(this.$myPosButton[0], 'click', this.myPosClickDelegate);
		}
		
		this.fillStyledMaps();
		this.$styledMaps.on('change', function(e){
			context.setMapStyle();
		});
		if(this.map && (this.$styledMaps.length > 0 && this.$styledMaps[0].selectedIndex > 0)){
			this.setMapStyle();
		}
		
		this.autocompleteOnPasteDelegate = Woald.createDelegate(this, this.autocompleteOnPasteHandler);
		this.$regionLatLng.on('paste', this.autocompleteOnPasteDelegate);

		this.tabToggleDelegate = Woald.createDelegate(this, this.tabToggleHandler);
		this.$tabs.on('shown.bs.tab', this.tabToggleDelegate);
		
		this.tabClickDelegate = Woald.createDelegate(this, this.tabClickHandler);
		this.$tabs.on('click', this.tabClickDelegate);
		
		this.addDestinationClickDelegate = Woald.createDelegate(this, this.addDestinationClickHandler);
		this.addDepartureClickDelegate = Woald.createDelegate(this, this.addDepartureClickHandler);
		
		this.$addDeparture.on('click', this.addDepartureClickDelegate);
		this.$addDestination.on('click', this.addDestinationClickDelegate);
		
		this.$formPlaceModal = this.$root.find('#formPlaceModal');
		this.$placeModalDialog = this.$placeModal.dialog({
			autoOpen: false
			, height: '350'
			, width: 'auto'
			, modal: true
			, dialogClass: 'calendarista-dialog'
			, buttons: [
				{
					'class': 'block_place'
					, 'text': 'Add'
					, 'click': function(){
						context.$formPlaceModal[0].submit();
					}
				}
				, {
					'text': 'Cancel'
					, 'click':  function(){
						context.$placeModalDialog.dialog('close');
					}
				}
			]
		});
		this.addBoundryByRadiusDelegate = Woald.createDelegate(this, this.addBoundryByRadius);
		this.$restrictRadius.on('change', this.addBoundryByRadiusDelegate);
	};
	Woald.creator.prototype.addBoundryByRadius = function(){
		var radius = this.$restrictRadius.length > 0 ? parseInt(this.$restrictRadius.val(), 10) : null
			, circle;
		if(!radius || !this.currentLocationMarker){
			return;
		}
		if(this.circle){
			this.circle.setMap(null);
		}
		this.circle = new google.maps.Circle({
		  map: this.map
		  , radius: radius
		  , fillColor: '#AA0000'
		});
		this.circle.bindTo('center', this.currentLocationMarker, 'position');
		this.map.fitBounds(this.circle.getBounds());
	};
	Woald.creator.prototype.setMapStyle = function(){
		var val = this.$styledMaps.val()
				, styles = null
				, name = this.$styledMaps[0].options[this.$styledMaps[0].selectedIndex].text
				, styledMap;
			if(val){
				styles = window['JSON'].parse(val);
			}
			styledMap = new google.maps.StyledMapType(styles,{'name': name});
			this.map.mapTypes.set('map_style', styledMap);
			this.map.setMapTypeId('map_style');
	};
	Woald.creator.prototype.fillStyledMaps = function(){
		var style
			, i
			, defaultSelected = false
			, selected = false
			, stringValue;
		for(i in Woald.creator.styles){
			style = Woald.creator.styles[i];
			stringValue = window['JSON'].stringify(style['value']);
			if(stringValue.indexOf(this.selectedMapStyle) !== -1){
				selected = true;
			}
			this.$styledMaps.append(new Option(style['name'], stringValue, defaultSelected, selected));
			selected = false;
		}
	}
	Woald.creator.prototype.contextMenuItemClickHandler = function(e, waypointArgs){
		var $target = $(e.currentTarget)
			, $container = $target.parent().parent()
			, menu = parseInt($target.attr('data-woald-menu'), 10)
			, waypointId = parseInt($target.attr('data-woald-waypoint-id'), 10)
			, $waypoint = this.$root.find('input[data-woald-waypoint-id="' + waypointId + '"]')
			, lat = parseFloat($container.attr('data-woald-lat'))
			, lng = parseFloat($container.attr('data-woald-lng'))
			, latLng = [lat, lng].join(',');
		this.resetMarkers();
		//ToDO: infowindow, add a caption to indicate departure/destination.
		switch(menu){
			case 0: /*Woald.creator.contextMenu.initaialLocation*/
				//this.$regionLatLng.val('');
				this.geocodeRequest(latLng, {'infoWindow': this.infoWindowRegion, 'marker': this.regionMarker, 'elem': this.$regionLatLng});
			break;
			case 3: /*Woald.creator.contextMenu.waypoint*/
				//$waypoint.val('');
				this.geocodeRequest(latLng, waypointArgs);
			break;
			case 4: /*Woald.creator.contextMenu.addNewPlace*/
				this.resetPlaceFields();
				(function(latLng, infoWindowPlaceItem, placeItemMarker, context){
					window.setTimeout(function() { 
						context.geocodeRequest(latLng, {'infoWindow': infoWindowPlaceItem, 'marker': placeItemMarker, 'newPlace': true});
					}, 200);
				})(latLng, this.infoWindowPlaceItem, this.placeItemMarker, this);
					
			
			break;
		}
		this.contextMenuExitHandler();
		return false;
	}
	Woald.creator.prototype.contextMenuExitHandler = function(e){
		if(!this.$contextMenu.hasClass('hide')){
			this.$contextMenu.addClass('hide');
		}
	}
	Woald.creator.prototype.mapContextMenuHandler = function(e){
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
		if(this.$contextMenu.find('li').length === 0){
			return false;
		}
		window.setTimeout(function(){
			context.$contextMenu.removeClass('hide');
			contextMenuWidth = context.$contextMenu.width();
			contextMenuHeight = context.$contextMenu.height();
			if((mapWidth - x ) < contextMenuWidth){
				x = x - contextMenuWidth;
			}
			if((mapHeight - y ) < contextMenuHeight){
				y = y - contextMenuHeight;
			}
			context.$contextMenu.attr('data-woald-lat', e.latLng.lat()).attr('data-woald-lng', e.latLng.lng());
			context.$contextMenu.css({'left': x + 'px', 'top': y + 'px'});
		}, 10);
		return false;
	}
	Woald.creator.prototype.fromLatLngToPoint = function(latLng) {
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
	Woald.creator.prototype.addDestinationClickHandler = function(e){
		this.$placeType.val(Woald.creator.placeType.destination);
		this.resetPlaceFields();
		this.$placeModalDialog.dialog('open');
	}
	Woald.creator.prototype.addDepartureClickHandler = function(e){
		this.$placeType.val(Woald.creator.placeType.departure);
		this.resetPlaceFields();
		this.$placeModalDialog.dialog('open');
	}
	Woald.creator.prototype.resetPlaceFields = function(){
		this.$placeId.val('');
		this.$placeLat.val('');
		this.$placeLng.val('');
		this.$placeName.val('');
		this.$placeCost.val('');
		this.$placeIcon.val('');
		this.$placeIconWidth.val('');
		this.$placeIconHeight.val('');
	}
	Woald.creator.prototype.autocompleteOnPasteHandler = function(e){
		var $target = $(e.currentTarget);
		(function($elem){
			return setTimeout(function() {
				var val = $elem.val();
				$elem.blur();
				$elem.val(val);
				return $elem.focus();
			}, 1);
		})($target);
	}
	Woald.creator.prototype.tabClickHandler = function(e){
		var $target = $(e.target);
		if(!this.isValid()){
			return false;
		}
		$target.tab('show');
	}
	Woald.creator.prototype.tabToggleHandler = function(e){
		this.gen();
		switch(e.target.hash){
			case '#preview':
				if(this.gmaps){
					this.gmaps.unload();
				}
				this.gmaps = new Woald.gmaps(this.args);
			break;
			default:
				window.console.log('activating creator');
			break;
		}
	}
	Woald.creator.prototype.isValid = function($validators){
		var isValid = true;
		$validators = $validators || this.$validators;
		$validators.each(function(){
			var $elem = $(this)
				, result;
			if (!$elem.is(':visible') || $elem.is(':disabled') || $elem.attr('data-parsley-excluded')){
				return true;
			}
			result = $elem.parsley().validate(true);
			if(result !== null && (typeof(result) === 'object' && result.length > 0)){
				isValid = false;
			}
		});
		return isValid;
	}
	
	Woald.creator.prototype.searchClickHandler = function(e){
		this.search();
	}
	Woald.creator.prototype.search = function(){
		var regionAddress = $.trim(this.$regionLatLng.val())
			, params;

		this.searchFields.length = 0;
		
		if(regionAddress){
			this.searchFields.push([regionAddress, {'infoWindow': this.infoWindowRegion, 'marker': this.regionMarker, 'elem': this.$regionLatLng}]);
		}
		if(this.searchFields.length > 0){
			params = this.searchFields.splice(this.searchFields.length - 1, 1)[0];
			this.geocodeRequest(params[0], params[1]);
		}
	}
	Woald.creator.prototype.myPosClickHandler = function(e){
		this.$regionLatLng.val('');
		this.geoLocate({'infoWindow': this.infoWindowRegion, 'marker': this.regionMarker, 'elem': this.$regionLatLng});
	}
	Woald.creator.prototype.tilesLoadedHandler = function(){
		//google.maps.event.clearListeners(this.map, 'tilesloaded');
		//delete this.tilesLoadedDelegate;
		this.progressBarHide();
		if(this.mapLoaded){
			this.mapLoaded(this.map);
		}
	}
	Woald.creator.prototype.getLocality = function(args){
		var i
			, item
			, route = '';
		for(i = 0; i < args.address_components.length; i++){
			item = args.address_components[i];
			if(item['types'] && item['types'][0] === 'route'){
				route = item['long_name'];
			}
			if(item['types'] && item['types'][0] === 'locality'){
				return item['long_name'];
			}
		}
		return route;
	}
	Woald.creator.prototype.getFriendlyName = function(args){
		var i
			, j
			, item
			, address = [(args.address_components[0] && args.address_components[0].short_name || '')
							, (args.address_components[1] && args.address_components[1].short_name || '')
						].join(' ');
			if(address){
				address += ', ';
			}
			address += (args.address_components[2] && args.address_components[2].short_name || '');
		for(i = 0; i < args.address_components.length; i++){
			item = args.address_components[i];
			if(item['types']){
				for(j = 0; j < item['types'].length; j++){
					if(item['types'][j] === 'administrative_area_level_2'){
						address += address ? ', ' + item['short_name'] : item['short_name'];
						return address;
					}
				}
			}
		}
		return address;
	}
	Woald.creator.prototype.autocompleteInit = function(){
		var toggleInfoWindowRegionDelegate
			, toggleinfoWindowPlaceItemDelegate
			, autocompleteRegionDelegate
			, autocompleteRegion;
		
		if(!this.autocompletes){
			this.autocompletes = [];
		}
		if(!this.infoWindows){
			this.infoWindows = [];
		}
		if(!this.markers){
			this.markers = [];
		}
		if(this.$regionLatLng.length > 0){
			autocompleteRegion = new google.maps.places.Autocomplete(this.$regionLatLng[0]);
			autocompleteRegion.bindTo('bounds', this.map);
			autocompleteRegion.setTypes(['geocode']);
			this.autocompletes.push(autocompleteRegion);
			this.infoWindowRegion = new google.maps.InfoWindow();
			this.infoWindows.push(this.infoWindowRegion);
			
			this.regionMarker = new google.maps.Marker({
				map: this.map
				, anchorPoint: new google.maps.Point(0, -29)
			});
			this.markers.push(this.regionMarker);
			toggleInfoWindowRegionDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
				'infoWindow': this.infoWindowRegion
				, 'marker': this.regionMarker
			});
			google.maps.event.addListener(this.regionMarker, 'click', toggleInfoWindowRegionDelegate);
			autocompleteRegionDelegate = Woald.createCallback(this.autocomplete, this, [autocompleteRegion, this.infoWindowRegion, this.regionMarker, this.$regionLatLng]);
			google.maps.event.addListener(autocompleteRegion, 'place_changed', autocompleteRegionDelegate);
			if(this.$regionLatLng.val()){
				this.geocodeRequest(this.$regionLatLng.val(), {'infoWindow': this.infoWindowRegion, 'marker': this.regionMarker, 'elem': this.$regionLatLng})
			}
		}
		this.infoWindowPlaceItem = new google.maps.InfoWindow();
		this.infoWindows.push(this.infoWindowPlaceItem);
		this.placeItemMarker = new google.maps.Marker({
			map: this.map
			, anchorPoint: new google.maps.Point(0, -29)
		});
		this.markers.push(this.placeItemMarker);
		toggleinfoWindowPlaceItemDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
			'infoWindow': this.infoWindowPlaceItem
			, 'marker': this.placeItemMarker
		});
		google.maps.event.addListener(this.placeItemMarker, 'click', toggleinfoWindowPlaceItemDelegate);
	}

	Woald.creator.prototype.autocomplete = function(args){
		var autocompleteFromTo = args[0]
			, infoWindow = args[1]
			, marker = args[2]
			, $elem = args[3]
			, place = autocompleteFromTo.getPlace();
		this.locate(place, infoWindow, marker, $elem, null);
	}
	Woald.creator.prototype.resetMarkers = function(){
		var i;
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
	}
	Woald.creator.prototype.fitMarkers = function(){
		var bounds = new google.maps.LatLngBounds()
			, i
			, markers = []
			, marker
			, pos
			, zoom = 13
			, count = 0;
		markers = markers.concat(this.markers);
		if(this.markersWaypoint){
			markers =  markers.concat(this.markersWaypoint);
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
		this.map.fitBounds(bounds);
		if(count > 1){
			zoom = this.getZoomLevel(bounds, {height: this.$mapElement.height(), width: this.$mapElement.width() });
		}
		this.map.setZoom(zoom);
	}
	Woald.creator.prototype.getZoomLevel = function(bounds, mapDim) {
			var WORLD_DIM = { height: 256, width: 256 }
				, ZOOM_MAX = 21
				, ne = bounds.getNorthEast()
				, sw = bounds.getSouthWest()
				, latFraction = (this.latRad(ne.lat()) - this.latRad(sw.lat())) / Math.PI
				, lngDiff = ne.lng() - sw.lng()
				, lngFraction = ((lngDiff < 0) ? (lngDiff + 360) : lngDiff) / 360
				, latZoom = this.zoomLevel(mapDim.height, WORLD_DIM.height, latFraction)
				, lngZoom = this.zoomLevel(mapDim.width, WORLD_DIM.width, lngFraction)
			return Math.min(latZoom, lngZoom, ZOOM_MAX);
	}
	Woald.creator.prototype.latRad = function(lat) {
		var sin = Math.sin(lat * Math.PI / 180)
			, radX2 = Math.log((1 + sin) / (1 - sin)) / 2;
		return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
	}
	Woald.creator.prototype.zoomLevel = function(mapPx, worldPx, fraction) {
		return Math.floor(Math.log(mapPx / worldPx / fraction) / Math.LN2);
	}
	Woald.creator.prototype.locate = function(place, infoWindow, marker, $elem, newPlace){
		var address = ''
			, locality
			, location = place.geometry.location
			, postData
			, content
			, title
			, icon
			, label
			, friendlyName;
		
		infoWindow.close();
		marker.setVisible(false);
		this.currentLocationMarker = marker;
		if (!place.geometry) {
		  return;
		}

		if (place.geometry.viewport) {
		  this.map.fitBounds(place.geometry.viewport);
		} else {
		  this.map.setCenter(location);
		  this.map.setZoom(13);
		}
		if(place.formatted_address){
			address = place.formatted_address;
		} else if (place.address_components) {
		  address = [
			(place.address_components[0] && place.address_components[0].short_name || ''),
			(place.address_components[1] && place.address_components[1].short_name || ''),
			(place.address_components[2] && place.address_components[2].short_name || '')
		  ].join(' ');
		}
		locality = place.name ? place.name : this.getLocality(place);
		friendlyName = this.getFriendlyName(place);
		postData = {'address': address
			, 'lat': location.lat()
			, 'lng': location.lng()
			, 'markerIcon': place['icon']
		};
		if(locality){
			postData['name'] = locality;
		}
		content = '<div><strong>' + locality + '</strong><br>' + address + '</div>';
		if($elem){
			if($elem.prop('id') === 'regionLatLng'){
				this.$regionLatLng.val(postData['address']);
				this.$regionAddress.val(postData['address']);
				this.$regionLat.val(postData['lat']);
				this.$regionLng.val(postData['lng']);
				this.$regionInfoWindowDescription.val(content);
			}
			if($elem.prop('name') === 'waypoint'){
				$elem.val(postData['address']);
				$('input[name="' + $elem.attr('data-woald-address') + '"]').val(postData['address']);
				$('input[name="' + $elem.attr('data-woald-lat') + '"]').val(postData['lat']);
				$('input[name="' + $elem.attr('data-woald-lng') + '"]').val(postData['lng']);
			}
		}
		marker.setPosition(place.geometry.location);
		marker.setVisible(true);
		infoWindow.setContent(content);
		infoWindow.open(this.map, marker);
		this.fitMarkers();
		if(newPlace){
			this.$placeLat.val(postData['lat']);
			this.$placeLng.val(postData['lng']);
			this.$placeName.val(friendlyName);
			this.$placeInfoWindowDescription.val(content);
			this.$placeModalDialog.dialog('open');
		}
		this.addBoundryByRadius();
	}
	
	Woald.creator.prototype.resize = function(){
		if(this.map && this.map.getCenter){
			var center = this.map.getCenter();
			google.maps.event.trigger(this.map, 'resize');
			this.map.setCenter(center);
		}
	}
	
	Woald.creator.prototype.isOpen = function(infoWindow){
		var map = infoWindow ? infoWindow.getMap() : null;
		return (map !== null && typeof(map) !== 'undefined');
	}
	
	Woald.creator.prototype.toggleInfoWindowHandler = function(e, args){
		this.toggleInfoWindow(args);
	}

	Woald.creator.prototype.toggleInfoWindow = function(args){
		var infoWindow = args['infoWindow']
			, marker = args['marker']
			, toggle;
		toggle = this.isOpen(infoWindow);
		if(!toggle){
			infoWindow.open(this.map, marker);
		} else{
			infoWindow.close(this.map, marker);
		}
	}
	
	Woald.creator.prototype.geoLocate = function(args){
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
	}
	
	Woald.creator.prototype.geocodeRequest = function(pos, args) {
		var values = pos.split(',')
			, lat
			, lng
			, latLng;
		if(values.length > 1){
			lat = parseFloat(values[0]);
			lng = parseFloat(values[1]);
			if(!isNaN(lat) && !isNaN(lng)){
				latLng = new google.maps.LatLng(lat, lng);
				this.geocode({'latLng': latLng}, args);
				return;
			}
		}
		this.geocode({'address': pos}, args);
	}
	
	Woald.creator.prototype.getCurrentPosition = function(position, args) {
		var latLng = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
		this.geocode({'latLng': latLng}, args);
	}
	
	Woald.creator.prototype.geocode = function(request, args){
		var reverseGeocodeDelegate;
		if(!this.geocoder){
			this.geocoder = new google.maps.Geocoder();
		}
		this.progressBarShow(this.loadingGeocodeMessage);
		reverseGeocodeDelegate = Woald.createCallback(this.reverseGeocodeHandler, this,  args);
		this.geocoder.geocode(request, reverseGeocodeDelegate);
	}
	
	Woald.creator.prototype.reverseGeocodeHandler = function(results, status, args){
		var params
			, $elem = args['elem']
			, newPlace = args['newPlace'];
		this.progressBarHide();
		if (status === google.maps.GeocoderStatus.OK) {
			/*if($elem && !$elem.val()){
				$elem.val(results[0].formatted_address);
			}*/
			this.locate(results[0], args['infoWindow'], args['marker'], $elem, newPlace);
		}
		if(this.searchFields.length > 0){
			params = this.searchFields.splice(this.searchFields.length - 1, 1)[0];
			this.geocodeRequest(params[0], params[1]);
		}
	}
	
	Woald.creator.prototype.handleNoGeoLocation = function(e) {
		var initialLocation = this.location;
		if (this.browserSupportFlag === true) {
			window.console.log(this.posErrorMsg);
			window.console.log(e['message'] || "Geolocation service failed.");
		} else {
			window.console.log(this.geolocationErrorMsg);
			window.console.log(e['message'] || "Your browser doesn't support geolocation.");
		}
		this.map.setCenter(initialLocation);
	}
	Woald.creator.prototype.initializeWaypoints = function(){
		var context = this
			, $formGroups = this.$waypoints.find('tr');
		this.wayPointId = $formGroups.length;
		$formGroups.each(function(){
			var $formGroup = $(this)
				, $elem = $formGroup.find('input[name="waypoint"]')
				, id = parseInt($formGroup.find('button[name="waypointremove"]').attr('data-woald-id'), 10);
			context.waypointsAdd($elem, id);
		});
		this.setWaypointFields();
	}
	Woald.creator.prototype.setWaypointFields = function(){
		var $formGroups = this.$waypoints.find('tr')
			, $fields
			, subResult = []
			, result = [];
		$formGroups.each(function(){
			$fields = $(this).find('input[type="hidden"]');
			$fields.each(function(){
				subResult.push(this.name);
			});
			if(subResult.length > 0){
				result.push(subResult.join(','));
				subResult.length = 0;
			}
		});
		this.$waypointFieldNames.val(result.join(';'));
	}
	Woald.creator.prototype.waypointsAdd = function($textbox, id){
		var $buttons
			, $formGroups;
		if(!$textbox){
			id = ++this.wayPointId;
			this.$waypoints.append(this.getWaypointsHtml(id));
			$textbox = this.$waypoints.find('input[data-woald-id="' + id + '"]')
		}
		$textbox.on('paste', this.autocompleteOnPasteDelegate);
		this.$contextMenu.append(this.getWaypointContextMenu(id));
		this.$contextMenu.menu('refresh');
		this.autocompleteWaypoint($textbox, id);
		$buttons = this.$waypoints.find('button[name="waypointremove"]');
		$formGroups = this.$waypoints.find('tr');
		$buttons.off();
		$formGroups.off();
		if(!this.waypointsRemoveDelegate){
			this.waypointsRemoveDelegate = Woald.createDelegate(this, this.waypointsRemoveHandler);
		}
		$buttons.on('click', this.waypointsRemoveDelegate);
		this.setWaypointFields();
	}
	Woald.creator.prototype.waypointsAddHandler = function(){
		if(this.$waypoints.find('input[name="waypoint"]').length < 23){
			this.waypointsAdd();
		}
	}
	Woald.creator.prototype.findObject = function(list, id){
		var result = null
			, index;
		$.each(list, function(i, value){
			if(value['id'] === id){
				result = value['obj'];
				index = i;
				return false;
			}
		});
		return {'result': result, 'i': index};
	}
	Woald.creator.prototype.waypointsRemoveHandler = function(e){
		var $target = $(e.currentTarget)
			, $formGroup = $target.closest('tr')
			, $textbox = $formGroup.find('input[name="waypoint"]')
			, $buttonSearch = $formGroup.find('button[name="waypointsearch"]')
			, $buttonRemove = $formGroup.find('button[name="waypointremove"]')
			, id = parseInt($buttonRemove.data('woaldId'), 10)
			, autocomplete = this.findObject(this.autocompletesWaypoint, id)
			, marker = this.findObject(this.markersWaypoint, id)
			, infoWindow = this.findObject(this.infoWindowsWaypoint, id)
			, $waypointContextMenuItem = this.$contextMenu.find('a[data-woald-waypoint-id="' + id + '"]').parent();
		
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
		$buttonSearch.off();
		$formGroup.remove();
		$waypointContextMenuItem.remove();
		this.$contextMenu.menu('refresh');
		this.setWaypointFields();
	}

	Woald.creator.prototype.autocompleteWaypoint = function($elem, id){
		var autocomplete = new google.maps.places.Autocomplete($elem[0])
			, infoWindow
			, marker
			, toggleWaypointInfoWindowDelegate
			, autocompleteDelegate
			, autocompleteSearchClickDelegate
			, contextMenuItemClickDelegate
			, $searchButton = $elem.parent().find('button[name="waypointsearch"]')
			, $waypointMenuItem = this.$contextMenu.find('a[data-woald-waypoint-id="' + id + '"]');
			
		autocomplete.bindTo('bounds', this.map);
		autocomplete.setTypes(['geocode']);
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
		});

		if(!this.markersWaypoint){
			this.markersWaypoint = [];
		}
		this.markersWaypoint.push({'id': id, 'obj': marker});
		
		toggleWaypointInfoWindowDelegate = Woald.createCallback(this.toggleInfoWindowHandler, this,  {
			'infoWindow': infoWindow
			, 'marker': marker
		});
		google.maps.event.addListener(marker, 'click', toggleWaypointInfoWindowDelegate);
		autocompleteDelegate = Woald.createCallback(this.autocomplete, this, [autocomplete, infoWindow, marker, $elem]);
		autocompleteSearchClickDelegate = Woald.createCallback(this.autocompleteSearchWaypointHandler, this, [autocomplete, infoWindow, marker, $elem]);
		contextMenuItemClickDelegate = Woald.createCallback(this.contextMenuItemClickHandler, this, {'infoWindow': infoWindow, 'marker': marker, 'elem': $elem});
		
		google.maps.event.addListener(autocomplete, 'place_changed', autocompleteDelegate);
		$searchButton.on('click', autocompleteSearchClickDelegate);
		$waypointMenuItem.on('click', contextMenuItemClickDelegate);
	}
	
	Woald.creator.prototype.autocompleteSearchWaypointHandler = function(e, args){
			var autocompleteFromTo = args[0]
			, infoWindow = args[1]
			, marker = args[2]
			, $elem = args[3]
			, address = $elem.val();
		if(address){
			this.geocodeRequest(address, {'infoWindow': infoWindow, 'marker': marker, 'elem': $elem});
		}
	}
	
	Woald.creator.prototype.getWaypointsHtml = function(id){
		return '<tr>'
				+'<td>'	
					+ '#' + id + '&nbsp;'
					+ '<input type="hidden" name="waypointAddress' + id + '"/>'
					+ '<input type="hidden" name="waypointLat' + id + '"/>'
					+ '<input type="hidden" name="waypointLng' + id + '"/>'
					+ '<input type="text" data-parsley-errors-container=".waypoint' + id + '-error-container" class="form-control" placeholder="Add a stop on the way" name="waypoint" data-woald-id="' + id + '"'
						+ 'data-woald-address="waypointAddress' + id + '"'
						+ 'data-woald-lat="waypointLat' + id + '"'
						+ 'data-woald-lng="waypointLng' + id + '"/>'
						+'<button type="button" class="button button-primary button-search" name="waypointsearch">'
							+'<i class="fa fa-search"></i>'
						+'</button>'
						+'<button type="button" title="Remove this stop" class="button button-primary" name="waypointremove" data-woald-id="' + id + '">'
							+'<i class="fa fa-minus"></i>'
						+'</button>'
						+'<div class="waypoint' + id + '-error-container"></div>'
					+'</td>'
				+'</tr>';
	}
	Woald.creator.prototype.getWaypointContextMenu = function(id){
		return '<li><a href="#" data-woald-menu="3" data-woald-waypoint-id="' + id + '"><span class="badge">#' + id + ' - </span>' + this.waypointContextMenuLabel + '</a></li>';
	}
	Woald.creator.prototype.getContextMenu = function(){
		var result =  '<ul class="woald-creator-contextmenu hide">'
			, contextMenuType = parseInt(this.$contextMenuType.val(), 10);
		if(contextMenuType === 0){
			result += '<li><a href="#" data-woald-menu="0">' + this.addressContextMenuLabel + '</a></li>';
		}
		if([1,2].indexOf(contextMenuType) !== -1){
			result += '<li><a href="#" data-woald-menu="4">' + this.newPlaceContextMenuLabel + '</a></li>';
		}
		result += '</ul>';
		return result;
	}
	
	Woald.creator.prototype.unloadHandler = function () {
		var selector
			, elem
			, i;
		google.maps.event.clearListeners(window, 'load');
		google.maps.event.clearListeners(window, 'unload');

		if(this.autocompletes){
			for(i in this.autocompletes){
				google.maps.event.clearListeners(this.autocompletes[i], 'place_changed');
			}
			this.autocompletes.length = 0;
		}
		if(this.markers){
			for(i in this.markers){
				google.maps.event.clearListeners(this.markers[i], 'click');
			}
			this.markers.length = 0;
		}
		delete this.geocodeCallbackDelegate;
		delete this.getCurrentPositionDelegate;
		delete this.handleNoGeoLocationDelegate;
		delete this.unloadDelegate;
	}
}(window, document, google, Woald, window['jQuery'], window['accounting']));
