<?php
class Calendarista_PlacesTemplate extends Calendarista_ViewBase{
	public $places;
	function __construct($placeType){
		parent::__construct(false, false, 'calendarista-places');
		if($this->selectedProjectId !== -1){
			$mapRepo = new Calendarista_MapRepository();
			$map = $mapRepo->readByProject($this->selectedProjectId);
			$placeRepo = new Calendarista_PlaceRepository();
			$this->places = $placeRepo->readAll($map->id, $placeType);
			if(isset($this->places) && $this->places->count() !== 0){
				if($placeType === Calendarista_PlaceType::DESTINATION && !$map->enableDestinationField){
					return;
				}
				$this->render();
			}
		}
	}
	public function render(){
	?>
		<input type="hidden" name="controller" value="place" />
		<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
		<input type="hidden" name="sortOrder" />
		<div class="column-borders">
			<div class="clear"></div>
			<div class="accordion-container place-items">
				<p class="description">
					<?php esc_html_e('Drag and drop to rearrange locations.', 'calendarista')?>
				</p>
				<ul class="outer-border">
					<?php foreach($this->places as $place):?>
						<li class="control-section accordion-section">
							<h3 class="accordion-section-title" tabindex="0">
								<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
								<input id="checkbox_<?php echo $place->id ?>" type="checkbox" name="places[]" value="<?php echo $place->id ?>"> 
								<?php echo $place->name ?> 
								<button type="button" name="editPlace" class="edit-linkbutton alignright" value="<?php echo $place->id ?>">
									[<?php esc_html_e('Edit', 'calendarista') ?>]
								</button>
							</h3>
						</li>
				   <?php endforeach;?>
				</ul>
				<p class="alignright">
					<input type="submit" name="calendarista_delete_places" id="calendarista_delete_places" class="button button-primary" value="<?php esc_html_e('Delete', 'calendarista') ?>" disabled>
					<input type="submit" 
							name="calendarista_sortorder" 
							id="calendarista_sortorder" 
							class="button button-primary sort-button" 
							title="<?php esc_html_e('Save sort order', 'calendarista')?>" 
							value="<?php esc_html_e('Save order', 'calendarista') ?>" disabled>
				</p>
				<br class="clear">
			</div>
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.places = function(options){
				this.init(options);
			};
			calendarista.places.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.places({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
		<?php
	}
}