<?php
class Calendarista_PlaceTemplate extends Calendarista_ViewBase{
	public $place;
	public $map;
	function __construct(){
		parent::__construct(false, false, 'calendarista-places');
		$this->place = new Calendarista_Place(array());
		if ((array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'place')){
			if(isset($_POST['action']) && $_POST['action'] === 'calendarista_edit_place'){
				new Calendarista_PlaceController(
					array($this, 'sortOrderNotice')
					, array($this, 'createdNotification')
					, array($this, 'updatedNotification')
					, array($this, 'deletedNotification')
				);
			}
			if(isset($_POST['placeId'])){
				$placeRepo = new Calendarista_PlaceRepository();
				$this->place = $placeRepo->read((int)$_POST['placeId']);
			}else{
				$this->place = new Calendarista_Place(array(
					'projectId'=>isset($_POST['projectId']) ? (int)$_POST['projectId'] : null,
					'orderIndex'=>isset($_POST['orderIndex']) ? (int)$_POST['orderIndex'] : null,
					'mapId'=>isset($_POST['mapId']) ? (int)$_POST['mapId'] : null,
					'placeType'=>isset($_POST['placeType']) ? (int)$_POST['placeType'] : null,
					'lat'=>isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : null,
					'lng'=>isset($_POST['lng']) ? sanitize_text_field($_POST['lng']) : null,
					'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
					'markerIcon'=>isset($_POST['markerIcon']) ? sanitize_text_field($_POST['markerIcon']) : null,
					'markerIconWidth'=>isset($_POST['markerIconWidth']) ? (int)$_POST['markerIconWidth'] : null,
					'markerIconHeight'=>isset($_POST['markerIconWidth']) ? (int)$_POST['markerIconWidth'] : null,
					'infoWindowTitle'=>isset($_POST['infoWindowTitle']) ? sanitize_text_field($_POST['infoWindowTitle']) : null,
					'infoWindowIcon'=>isset($_POST['infoWindowIcon']) ? sanitize_text_field($_POST['infoWindowIcon']) : null,
					'infoWindowDescription'=>isset($_POST['infoWindowDescription']) ? sanitize_text_field($_POST['infoWindowDescription']) : null,
					'cost'=>isset($_POST['cost']) ? (double)$_POST['cost'] : null,
					'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null,
				));
			}
		}
		$mapRepo = new Calendarista_MapRepository();
		$this->map = $mapRepo->readByProject($this->selectedProjectId);
		if(!isset($this->map)){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		$this->render();
	}
	public function render(){
	?>
		<input type="hidden" name="controller" value="place"/>
		<input type="hidden" name="id" value="<?php echo $this->place->id?>"/>
		<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId?>"/>
		<input type="hidden" name="mapId" value="<?php echo $this->map->id?>"/>
		<input type="hidden" name="orderIndex" value="<?php echo $this->place->orderIndex?>"/>
		<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
		<table class="form-table place-editor-form">
			<tbody>
				<tr>
					<th scope="row">
						<label for="lat">
							<?php esc_html_e('Latitude', 'calendarista')?>
						</label>
					</th>
					<td>
						<input type="text" 
							class="woald_parsley_validated medium-text"
							data-parsley-trigger="change"
							data-parsley-range="[-90,90]"
							data-parsley-required="true"
							name="lat"
							value="<?php echo $this->place->lat?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lng">
							<?php esc_html_e('Longitude', 'calendarista')?>
						</label>
					</th>
					<td>
						<input type="text" 
							class="woald_parsley_validated medium-text"
							data-parsley-trigger="change"
							data-parsley-range="[-180,180]"
							data-parsley-required="true"
							name="lng"
							value="<?php echo $this->place->lng?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="name">
							<?php esc_html_e('Name', 'calendarista') ?>
						</label>
					</th>
					<td>
						<input type="text" 
								class="woald_parsley_validated regular-text"
								data-parsley-trigger="change"
								data-parsley-required="true"
								name="name"
								value="<?php echo esc_attr($this->place->name) ?>"/>
					</td>
				</tr>
				<?php if($this->map->costMode === Calendarista_CostMode::DEPARTURE_ONLY):?>
				<tr>
					<th scope="row"><label for="cost"><?php esc_html_e('Cost', 'calendarista') ?></label></th>
					<td>
						<input id="cost" 
							name="cost" 
							type="text" 
							class="small-text" 
							data-parsley-trigger="change focusout"
							data-parsley-min="0"
							data-parsley-pattern="^\d+(\.\d{1,2})?$"
							placeholder="0.00" 
							value="<?php echo $this->emptyStringIfZero($this->place->cost) ?>" />
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<th scope="row">
						<label for="markerIcon">
							<?php esc_html_e('Marker Icon url', 'calendarista') ?>
						</label>
					</th>
					<td>
						<input type="hidden" 
								name="markerIcon"
								value="<?php echo $this->place->markerIcon ?>"/>
						<div data-calendarista-preview-icon="markerIcon" class="preview-icon" 
								style="<?php echo $this->place->markerIcon ?
														sprintf('background-image: url(%s)', esc_url($this->place->markerIcon)) : ''?>">
						</div>
						<button type="button" 
							class="button button-primary"
							data-calendarista-preview-icon="markerIcon"
							name="iconUrlRemove">
							<i class="fa fa-remove"></i>
						</button>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="markerIconWidth">
							<?php esc_html_e('Marker icon width', 'calendarista') ?>
						</label>
					</th>
					<td>
						<input type="text" 
							class="woald_parsley_validated small-text"
							data-parsley-trigger="change"
							data-parsley-type="digits"
							name="markerIconWidth"
							value="<?php echo $this->place->markerIconWidth?>"/>
						<p class="description"><?php esc_html_e('A value of 0 will size the icon automatically', 'calendarista') ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="markerIconHeight">
							<?php esc_html_e('Marker icon height', 'calendarista') ?>
						</label>
					</th>
					<td>
						<input type="text" 
							class="woald_parsley_validated small-text"
							data-parsley-trigger="change"
							data-parsley-type="digits" 
							name="markerIconHeight"
							value="<?php echo $this->place->markerIconHeight?>"/>
						<p class="description"><?php esc_html_e('A value of 0 will size the icon automatically', 'calendarista') ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="infoWindowTitle">
							<?php esc_html_e('Info window title', 'calendarista') ?>
						</label>
					</th>
					<td>
						<input type="text" 
								class="regular-text"
								name="infoWindowTitle"
								value="<?php echo esc_attr($this->place->infoWindowTitle) ?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="infoWindowIcon">
							<?php esc_html_e('Info window icon', 'calendarista') ?>
						</label>
					</th>
					<td>
						<input  type="hidden" 
								name="infoWindowIcon"
								value="<?php echo $this->place->infoWindowIcon ?>"/>
						<div data-calendarista-preview-icon="infoWindowIcon" class="preview-icon" 
								style="<?php echo $this->place->infoWindowIcon ?
														esc_url(sprintf('background-image: url(%s)', $this->place->infoWindowIcon)) : ''?>">
						</div>
						<button type="button" 
							class="button button-primary"
							data-calendarista-preview-icon="infoWindowIcon"
							name="iconUrlRemove">
							<i class="fa fa-remove"></i>
						</button>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="infoWindowDescription">
							<?php esc_html_e('Info window description', 'calendarista') ?>
						</label>
					</th>
					<td>
						<textarea type="text" 
								class="large-text"
								rows="3"
								name="infoWindowDescription"><?php echo esc_textarea($this->place->infoWindowDescription) ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.place = function(options){
				this.init(options);
			};
			calendarista.place.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.place({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
		<?php
	}
}