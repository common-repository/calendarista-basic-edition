<?php
class Calendarista_ManagePlaces extends Calendarista_ViewBase{
	public $map;
	public $placeType;
	public $selectedMapId;
	public $tabs;
	public $createNew;
	public $project;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-places');
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!$generalSetting->googleMapsKey){
			$this->googleMapsAPIKeyNotice();
			return;
		}
		$this->placeType = null;
		if($this->selectedTab === 1){
			$this->placeType = Calendarista_PlaceType::DEPARTURE;
		}else if($this->selectedTab === 2){
			$this->placeType = Calendarista_PlaceType::DESTINATION;
		}
		$this->project = $this->getProject();
		new Calendarista_PlaceController(
			array($this, 'sortOrderNotice')
			, array($this, 'createdNotification')
			, array($this, 'updatedNotification')
			, array($this, 'deletedNotification')
		);
		new Calendarista_MapController(
			array($this, 'updatedNotification')
		);
		$mapRepo = new Calendarista_MapRepository();
		$this->map = $mapRepo->readByProject($this->selectedProjectId);
		if(!$this->map){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		$this->tabs = $this->getTabs();
		$this->render();
	}
	public function sortOrderNotice() {
		if($result){
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The sort order has been updated.', 'calendarista'); ?></p>
			</div>
		</div>
		<?php
		}
	}
	public function getTabs(){
		$url = admin_url() . 'admin.php?page=calendarista-places&projectId=' . $this->selectedProjectId;
		$result = array();
		$result[0] = array('url'=>$url, 'label'=>__('Start', 'calendarista'), 'active'=>false);
		$result[1] = array('url'=>$url . '&calendarista-tab=1', 'label'=>__('Departure', 'calendarista'), 'active'=>false);
		if($this->map->costMode !== Calendarista_CostMode::DEPARTURE_ONLY){
			$result[2] = array('url'=>$url . '&calendarista-tab=2', 'label'=>__('Destination', 'calendarista'), 'active'=>false);
		}
		if(!in_array($this->map->costMode, array(
			Calendarista_CostMode::DEPARTURE_ONLY
			, Calendarista_CostMode::DEPARTURE_AND_DESTINATION)) && $this->map->enableDestinationField){
			$result[3] = array('url'=>$url . '&calendarista-tab=3', 'label'=>__('Waypoints', 'calendarista'), 'active'=>false);
		}
		$result[4] = array('url'=>$url . '&calendarista-tab=4', 'label'=>__('Initial location', 'calendarista'), 'active'=>false);
		$result[5] = array('url'=>$url . '&calendarista-tab=5', 'label'=>__('General', 'calendarista'), 'active'=>false);
		if(in_array($this->map->costMode, array(null, Calendarista_CostMode::DEPARTURE_AND_DESTINATION), true)){
			$result[6] = array('url'=>$url . '&calendarista-tab=6', 'label'=>__('Aggregate cost', 'calendarista'), 'active'=>false);
		}
		$result[7] = array('url'=>$url . '&calendarista-tab=7', 'label'=>__('Restrict', 'calendarista'), 'active'=>false);
		if($this->selectedTab !== null && count($result) >= $this->selectedTab){
			$result[$this->selectedTab]['active'] = true;
		}else{
			$result[0]['active'] = true;
		}
		return $result;
	}
	protected function tabState($tabIndex){
		return ($tabIndex && $this->selectedProjectId === -1) ? 'nav-tab-disabled' : '';
	}
	public function googleMapsAPIKeyNotice() {
		$settingsUrl = admin_url() . 'admin.php?page=calendarista-settings';
		$link = sprintf('<a href="%s">%s</a>', $settingsUrl, __('settings', 'calendarista'));
		?>
		<div class="calendarista-notice error notice is-dismissible">
			<p><?php echo sprintf(__('You have not added your google maps API key yet. Please head on to the %s page and add one first.', 'calendarista'), $link); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<h2 class="calendarista nav-tab-wrapper">
			<?php foreach($this->tabs as $index=>$tab):?>
			<?php if(!isset($tab)){continue;}?>
			<a data-calendarista-tabindex="<?php echo esc_attr($index) ?>" class="nav-tab <?php echo $tab['active'] ? 'nav-tab-active' : '' ?> <?php echo esc_attr($this->tabState($index)) ?>" href="<?php echo esc_url($tab['url']) ?>"><?php echo esc_html($tab['label']) ?></a>
			<?php endforeach;?>
		</h2>
		<div id="woald_creator">
			<div id="progressModal" title="<?php esc_html_e('Activity progress') ?>">
				<div id="progress-bar"></div>
				<p><span class="progress-report"></span></p>
			</div>
			<div class="widget-liquid-left">
				<div id="widgets-left">
					<?php if($this->project):?>
					<div class="index updated notice">
						<p><?php echo sprintf(__('The map you are editing applies to [%s] service.', 'calendarista'),  esc_html($this->project->name)) ?></p>
					</div>
					<?php else: ?>
						<p class="description"><?php esc_html_e('Select a map from the list in the right pane to edit its properties.', 'calendarista') ?></p>
					<?php endif; ?>
					<?php 
						switch($this->selectedTab){
						case 1:
							new Calendarista_DepartureTemplate(); 
						break; 
						case 2:
							new Calendarista_DestinationTemplate();
						break;
						case 3: 
							new Calendarista_WaypointTemplate();
						break; 
						case 4:
							new Calendarista_DefaultLocationTemplate();
						break; 
						case 5:
							new Calendarista_DirectionGeneralSettingTemplate();
						break;
						case 6:
							new Calendarista_PlaceAggregateCostTemplate();
						break;
						case 7:
							new Calendarista_MapAutocompleteRestrictTemplate();
						break;
						default:
						new Calendarista_NewMapServiceTemplate();
					 }
					 ?>
				</div>
			</div>
			<?php switch($this->selectedTab):
				case 0:
			?>
			<div class="widget-liquid-right calendarista-widgets-right">
				<div id="widgets-right">
					<div class="single-sidebar">
						<div class="sidebars-column-1">
							<div class="widgets-holder-wrap">
								<div class="widgets-sortables ui-droppable ui-sortable">
								<?php new Calendarista_MapServicesTemplate() ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
				break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
			?>
			<div class="widget-liquid-right">
				<div id="widgets-right">
					<p class="description"><?php esc_html_e('Double click on desired area to zoom in. Right click on desired location to add area directly from map.', 'calendarista') ?></p>
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables">	
							<br>
							<div  class="woald-container">
								<div class="woald-map">
									<div class="woald-map-canvas"></div>
								</div>
							</div>
							<div id="placeModal" title="<?php esc_html_e('Add a new place', 'calendarista') ?>">
								<form id="formPlaceModal" action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-validate>
									<input type="hidden" name="calendarista_create" value="1"/>
									<input type="hidden" name="placeType" value="<?php echo $this->placeType ?>"/>
									<?php new Calendarista_PlaceTemplate() ?>
								</form>
							</div>
							<div id="editPlaceModal" title="<?php esc_html_e('Edit a place', 'calendarista') ?>">
								<form id="formEditPlaceModal" action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-validate>
									<input type="hidden" name="placeType" value="<?php echo $this->placeType ?>"/>
									<div class="edit_place_placeholder"></div>
								</form>
							</div>
							<br class="clear">
							<?php if(in_array($this->selectedTab, array(1,2))):?>
							<form action="<?php echo esc_url($this->requestUrl) ?>" method="post">
								<div id="spinner_places" class="calendarista-spinner calendarista-invisible">
									<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif"><?php esc_html_e('Loading...', 'calendarista') ?>
								</div>
								<div class="get_places_placeholder">
									<?php new Calendarista_PlacesTemplate($this->placeType); ?>
								</div>
							</form>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php break;
				case 7: ?>
			<div class="widget-liquid-right">
				<div id="widgets-right">
					<p class="description"><?php esc_html_e('Double click on desired area to zoom in. Right click on desired location to add area directly from map.', 'calendarista') ?></p>
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables">	
							<br>
							<div  class="woald-container">
								<div class="woald-map">
									<div class="woald-map-canvas"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php break;
			endswitch; ?>
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.maps = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
					context.initPlaces();
				});
			};
			calendarista.maps.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.projectId = options['projectId'];
				this.requestUrl = options['requestUrl'];
				this.baseUrl = options['baseUrl'];
				this.placeType = options['placeType'];
				this.selectedTab = options['selectedTab'];
				this.actionEditPlace = 'calendarista_edit_place';
				this.actionGetPlaces = 'calendarista_get_places';
				this.$nightStartTimeTextbox = $('input[name="nightStartTime"]');
				this.$nightEndTimeTextbox = $('input[name="nightEndTime"]');
				this.$nightStartTimeTextbox.timepicker({'timeFormat': 'hh:mm tt'});
				this.$nightEndTimeTextbox.timepicker({'timeFormat': 'hh:mm tt'});
				this.$editPlacePlaceHolder = $('.edit_place_placeholder');
				this.$placesPlaceHolder = $('.places_placeholder');
				this.$enableDepartureField = $('input[name="enableDestinationField"]');
				this.ajax1 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'place'});
				this.ajax2 = new Calendarista.ajax({'ajaxUrl': this.ajaxUrl, 'id': 'places'});
				$('.calendarista.nav-tab-wrapper a').on('click', function(e){
					var currentTab = parseInt($(this).attr('data-calendarista-tabindex'), 10);
					if(context.projectId === -1 && currentTab){
						return false;
					}
				});
				new Woald.creator({
					'id': '#woald_creator'
					, 'selectedMapStyle': options['selectedMapStyle']
					, 'lat': options['lat']
					, 'lng': options['lng']
				});
				new Calendarista.imageSelector({'id': '#placeModal', 'previewImageUrl': options['previewImageUrl']});
				this.$editPlaceModalDialog = $('#editPlaceModal').dialog({
					autoOpen: false
					, height: '480'
					, width: '640'
					, modal: true
					, resizable: false
					, dialogClass: 'calendarista-dialog'
					, create: function() {
						var spinner = '<div id="spinner_place" class="calendarista-spinner ui-widget ui-button calendarista-invisible">';
						spinner += '<img src="<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/transparent.gif">&nbsp;';
						spinner += '</div>';
						$(this).dialog('widget').find('.ui-dialog-buttonset').prepend(spinner);
						$(this).closest('div.ui-dialog').find('.ui-dialog-titlebar-close').on('click', function(e) {
						   e.preventDefault();
						   context.refreshPlaces();
						});
					}
					, buttons: [
						{
							'text': 'Update'
							, 'name': 'update'
							, 'click':  function(e){
								var $target = $(e.currentTarget)
									, $form = context.$editPlaceModalDialog.dialog('widget').find('#formEditPlaceModal')
									, model = $form.serializeArray();
								if(!Calendarista.wizard.isValid($form)){
									e.preventDefault();
									return false;
								}
								model.push({ 'name': 'calendarista_update', 'value': 1 });
								model.push({ 'name': 'action', 'value': context.actionEditPlace });
								model.push({ 'name': 'calendarista_nonce', 'value': context.nonce });
								context.ajax1.request(context, context.placeEditResponse, $.param(model));
							}
						}
						, {
							'text': 'Delete'
							, 'name': 'delete'
							, 'click':  function(){
								var $id = context.$editPlaceModalDialog.dialog('widget').find('input[name="id"]')
									, model = [
										{ 'name': 'id', 'value': parseInt($id.val(), 10)}
										, { 'name': 'controller', 'value': 'place'}
										, { 'name': 'calendarista_delete', 'value': 'true'}
										, { 'name': 'action', 'value': context.actionEditPlace }
										, { 'name': 'calendarista_nonce', 'value': context.nonce }
									]
								context.ajax1.request(context, context.placeDeleteResponse, $.param(model));
							}
						}
						, {
							'text': 'Close'
							, 'click':  function(){
								context.$editPlaceModalDialog.dialog('close');
								context.refreshPlaces();
							}
						}
					]
				});
			};
			calendarista.maps.prototype.updateQueryString = function(name, str, value){
				var re
					, delimiter
					, result;
				if(str.indexOf(name) === -1){
					return str + '&projectId=' + value;
				}
				re = new RegExp("[\\?&]" + name + "=([^&#]*)");
				delimiter = re.exec(str)[0].charAt(0);
				result = str.replace(re, delimiter + name + "=" + value);
				return result;
			};
			calendarista.maps.prototype.placeEditResponse = function(result){
				this.$editPlacePlaceHolder.replaceWith('<div class="edit_place_placeholder">' + result + '</div>');
				this.$editPlacePlaceHolder = $('.edit_place_placeholder');
				new Calendarista.imageSelector({'id': '#editPlaceModal', 'previewImageUrl': this.previewImageUrl});
				this.refreshPlaces();
			};
			calendarista.maps.prototype.placeDeleteResponse = function(result){
				this.refreshPlaces();
				this.$editPlaceModalDialog.dialog('close');
			};
			calendarista.maps.prototype.updateSortOrder = function(){
				var sortOrder = this.getSortOrder(this.$placeListItems, 'input[name="places[]"]');
				this.$sortOrder.val(sortOrder.join(','));
				this.$sortOrderButton.prop('disabled', false);
			};
			calendarista.maps.prototype.getSortOrder = function($sortItems, selector){
				var i
					, sortOrder = []
					, $item;
				for(i = 0; i < $sortItems.length; i++){
					$item = $($sortItems[i]);
					sortOrder.push($item.find(selector).val() + ':' + $item.index());
				}
				return sortOrder;
			};
			calendarista.maps.prototype.refreshPlaces = function(){
				var model = [
					{ 'name': 'placeType', 'value': this.placeType }
					, { 'name': 'projectId', 'value': this.projectId }
					, { 'name': 'action', 'value': this.actionGetPlaces }
					, { 'name': 'calendarista_nonce', 'value': this.nonce }
				];
				this.ajax2.request(this, this.getPlacesResponse, $.param(model));
			};
			calendarista.maps.prototype.getPlacesResponse = function(result){
				this.$getPlacesPlaceHolder.replaceWith('<div class="get_places_placeholder">' + result + '</div>');
				this.$getPlacesPlaceHolder = $('.get_places_placeholder');
				this.initPlaces();
			};
			calendarista.maps.prototype.initPlaces = function(){
				var context = this;
				this.$getPlacesPlaceHolder = $('.get_places_placeholder');
				this.$deletePlacesButton = $('.place-items input[name="calendarista_delete_places"]');
				this.$deletePlaces = $('.place-items input[type="checkbox"]');
				this.$placeListItems = $('.accordion-container.place-items ul>li');
				this.$editPlaceButtons = this.$placeListItems.find('button[name="editPlace"]');
				this.$sortOrder = $('input[name="sortOrder"]');
				this.$sortOrderButton = $('input[name="calendarista_sortorder"]');
				this.$deletePlaces.on('click', function(e){
					e.stopPropagation();
					var hasChecked = context.$deletePlaces.is(':checked');
					if(hasChecked){
						context.$deletePlacesButton.prop('disabled', false);
					}else{
						context.$deletePlacesButton.prop('disabled', true);
					}
				});
				this.$editPlaceButtons.on('click', function(e){
					e.stopPropagation();
					var id = parseInt($(this).val(), 10)
						, model = [
							{ 'name': 'placeId', 'value':  id }
							, { 'name': 'controller', 'value':  'place' }
							, { 'name': 'projectId', 'value':  context.projectId }
							, { 'name': 'action', 'value': context.actionEditPlace }
							, { 'name': 'calendarista_nonce', 'value': context.nonce }
						];
					context.$editPlaceModalDialog.dialog('open');
					context.$editPlacePlaceHolder.html('');
					context.ajax1.request(context, context.placeEditResponse, $.param(model));
				});
				this.$getPlacesPlaceHolder.find('.accordion-container ul').accordion({
					collapsible: false
					, active: null
				}).sortable({
					axis: 'y'
					, handle: '.calendarista-drag-handle'
					, stop: function( event, ui ) {
						var $this = $(this);
						context.updateSortOrder();
					  // IE doesn't register the blur when sorting
					  // so trigger focusout handlers to remove .ui-state-focus
					  ui.item.children('h3').triggerHandler('focusout');
					  // Refresh accordion to handle new order
					  $this.accordion('refresh');
					  $this.accordion({active: ui.item.index()});
					}
				});
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.maps({
				'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
				, 'baseUrl': '<?php echo $this->baseUrl ?>'
				, 'previewImageUrl': '<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/no-preview-thumbnail.png'
				, 'selectedMapStyle': '<?php echo $this->map->styledMaps ?>'
				, 'projectId': <?php echo $this->selectedProjectId ?>
				, 'placeType': <?php echo isset($this->placeType) ? $this->placeType : -1 ?>
				, 'selectedTab': <?php echo $this->selectedTab ?>
				, 'lat': <?php echo isset($_POST['lat']) ? $_POST['lat'] : 0 ?>
				, 'lng': <?php echo isset($_POST['lng']) ? $_POST['lng'] : 0 ?>
		});
		</script>
	<?php
	}
}