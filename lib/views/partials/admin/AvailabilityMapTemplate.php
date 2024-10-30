<?php
class Calendarista_AvailabilityMapTemplate extends Calendarista_ViewBase{
	public $availability;
	public $availabilities;
	public $selectedId = -1;
	function __construct( ){
		parent::__construct();
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if(!$generalSetting->googleMapsKey){
			$this->googleMapsAPIKeyNotice();
			return;
		}
		$this->requestUrl = admin_url() . 'admin.php?page=calendarista-index&calendarista-tab=7&projectId=' . $this->selectedProjectId;
		$availabilityId = isset($_POST['availability']) && !empty($_POST['availability']) ? (int)$_POST['availability'] : null;
		$availabilityRepo = new Calendarista_AvailabilityRepository();
		$this->availability = new Calendarista_Availability(array());
		$this->availabilities = new Calendarista_Availabilities();
		$availability = new Calendarista_Availability(array(
			'id'=>isset($_POST['id']) ? (int)$_POST['id'] : null,
			'regionAddress'=>isset($_POST['regionAddress']) ? sanitize_text_field($_POST['regionAddress']) : null,
			'regionLat'=>isset($_POST['regionLat']) ? sanitize_text_field($_POST['regionLat']) : null,
			'regionLng'=>isset($_POST['regionLng']) ? sanitize_text_field($_POST['regionLng']) : null,
			'regionMarkerIconUrl'=>isset($_POST['regionMarkerIconUrl']) ? sanitize_text_field($_POST['regionMarkerIconUrl']) : null,
			'regionMarkerIconWidth'=>isset($_POST['regionMarkerIconWidth']) ? (int)$_POST['regionMarkerIconWidth'] : null,
			'regionMarkerIconHeight'=>isset($_POST['regionMarkerIconHeight']) ? (int)$_POST['regionMarkerIconHeight'] : null,
			'regionInfoWindowIcon'=>isset($_POST['regionInfoWindowIcon']) ? sanitize_text_field($_POST['regionInfoWindowIcon']) : null,
			'regionInfoWindowDescription'=>isset($_POST['regionInfoWindowDescription']) ? sanitize_text_field($_POST['regionInfoWindowDescription']) : null,
			'styledMaps'=>isset($_POST['styledMaps']) ? sanitize_text_field($_POST['styledMaps']) : null,
			'showMapMarker'=>isset($_POST['showMapMarker']) ? (bool)$_POST['showMapMarker'] : null,
			'hideMapDisplay'=>isset($_POST['hideMapDisplay']) ? (bool)$_POST['hideMapDisplay'] : null
		));
		new Calendarista_AvailabilityMapController(
			$availability
			, array($this, 'updatedAvailabilityMap')
			, array($this, 'deleteAvailabilityMap')
		);
		if($this->selectedProjectId !== -1){
			$this->availabilities = $availabilityRepo->readAll($this->selectedProjectId);
		}
		if($availabilityId !== null){
			$this->availability = $availabilityRepo->read($availabilityId);
		}
		if(!$this->project){
			$this->project = $this->projectRepo->read($this->selectedProjectId);
		}
		$this->render();
	}
	public function updatedAvailabilityMap($id){
		$this->selectedId = $id;
		$this->updatedAvailabilityNotice();
	}
	public function deleteAvailabilityMap($result){
		if($result){
			$this->deletedAvailabilityMapNotice();
		}
	}
	public function updatedAvailabilityNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The availability map has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function deletedAvailabilityMapNotice() {
		?>
		<div class="calendarista-notice updated notice is-dismissible">
			<p><?php esc_html_e('The availability map has been reset.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function errorNotice($message) {
		?>
		<div class="calendarista-notice error notice is-dismissible">
			<p><?php echo sprintf(__('The operation failed unexpected with [%s]. Try again?', 'calendarista'), $message); ?></p>
		</div>
		<?php
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
	<div id="woald_creator">
		<div id="progressModal" title="<?php esc_html_e('Activity progress') ?>">
			<div id="progress-bar"></div>
			<p><span class="progress-report"></span></p>
		</div>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<p class="description">
						<?php esc_html_e('Display the area on the map', 'calendarista') ?>
					</p>
					<form id="form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="availability_map" />
						<input type="hidden" name="contextMenuType" value="0"/>
						<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>">
						<input type="hidden" name="id" value="<?php echo $this->availability->id ?>" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<div>
											<label for="availability">
												<?php esc_html_e('Availability', 'calendarista')?>
											</label>
										</div>
										<select name="availability" id="availability" data-parsley-required="true">
										<option value=""><?php esc_html_e('Select an availability', 'calendarista'); ?></option>
										<?php foreach($this->availabilities as $availability):?>
											<option value="<?php echo $availability->id; ?>" <?php echo $availability->id === $this->availability->id ? 'selected=selected' : '';?>><?php echo $availability->name; ?></option>
										<?php endforeach;?>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										<div>
											<label for="regionLatLng">
												<?php esc_html_e('Address', 'calendarista')?>
											</label>
										</div>
										<div>
											<input type="hidden" name="regionAddress" value="<?php echo  $this->availability->regionAddress ?>"/>
											<input type="hidden" id="regionLat" name="regionLat" value="<?php echo  $this->availability->regionLat ?>"/>
											<input type="hidden" id="regionLng" name="regionLng" value="<?php echo  $this->availability->regionLng ?>"/>
											<input type="text" 
												class="woald_parsley_validated"
												data-parsley-errors-container=".region-lat-lng-error-container"
												id="regionLatLng"
												name="regionAddress"
												value="<?php echo  esc_html($this->availability->regionAddress) ?>"/>
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
											<label for="theme">
												<?php esc_html_e('Styled Maps', 'calendarista') ?>
											</label>
										</div>
										<select
											name="styledMaps"
											id="styledMaps">
											<option value="" <?php echo $this->availability->styledMaps ? 'selected' : '' ?>><?php esc_html_e('Select a theme', 'calendarista') ?></option>
										</select>
										<p class="description"><?php esc_html_e('Styled maps allow you to customize the presentation of the standard Google base maps, changing the visual display of such elements as roads, parks, and built-up areas', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
									<input name="showMapMarker" 
											type="checkbox" <?php echo $this->availability->showMapMarker ? "checked" : ""?> /> 
										<?php esc_html_e('Display marker', 'calendarista')?>
									</td>
								</tr>
								<tr>
									<td>
									<input name="hideMapDisplay" 
											type="checkbox" <?php echo $this->availability->hideMapDisplay ? "checked" : ""?> /> 
										<?php esc_html_e('Hide map', 'calendarista')?>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<hr>
									</td>
								</tr>
							</tbody>
						</table>
						<table class="form-table availability-map-fields">
							<tbody>
								<tr>
									<td>
										<div>
											<label for="regionMarkerIconUrl">
												<?php esc_html_e('Icon url', 'calendarista'); ?>
											</label>
										</div>
											<input  type="hidden"  
													name="regionMarkerIconUrl"
													value="<?php echo $this->availability->regionMarkerIconUrl ?>"/>
											<div data-calendarista-preview-icon="regionMarkerIconUrl" class="preview-icon" 
												style="<?php echo $this->availability->regionMarkerIconUrl ?
																	sprintf('background-image: url(%s)', esc_url($this->availability->regionMarkerIconUrl)) : ''?>">
											</div>
											<button type="button" 
												class="button button-primary remove-image"
												data-calendarista-preview-icon="regionMarkerIconUrl"
												title="<?php __('Remove image', 'calendarista')?>"
												name="iconUrlRemove">
												<i class="fa fa-remove"></i>
											</button>
									</td>
								</tr>
								<tr>
									<td>
										<div>
											<label for="regionMarkerIconWidth">
												<?php esc_html_e('Icon width', 'calendarista'); ?>
											</label>
										</div>
										<input type="text" 
											class="woald_parsley_validated small-text"
											data-parsley-trigger="change"
											data-parsley-type="digits" 
											name="regionMarkerIconWidth"
											value="<?php echo $this->availability->regionMarkerIconWidth ?>"
											id="regionMarkerIconWidth"/>
											<p class="description"><?php esc_html_e('A value of 0 will size the icon automatically', 'calendarista'); ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div>
											<label for="regionMarkerIconHeight">
												<?php esc_html_e('Icon height', 'calendarista'); ?> 
											</label>
										</div>
										<input type="text" 
											class="woald_parsley_validated small-text"
											data-parsley-trigger="change"
											data-parsley-type="digits"
											value="<?php echo $this->availability->regionMarkerIconHeight ?>"
											name="regionMarkerIconHeight"
											id="regionMarkerIconHeight"/>
											<p class="description"><?php esc_html_e('A value of 0 will size the icon automatically', 'calendarista'); ?></p>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<hr>
									</td>
								</tr>
								<tr>
									<td>
										<div>
											<label for="regionInfoWindowIcon">
												<?php esc_html_e('Info window icon', 'calendarista'); ?>
											</label>
										</div>
										<input type="hidden" 
												name="regionInfoWindowIcon"
												value="<?php echo $this->availability->regionInfoWindowIcon ?>"/>
											<div data-calendarista-preview-icon="regionInfoWindowIcon" class="preview-icon" 
											style="<?php echo $this->availability->regionInfoWindowIcon ?
																	sprintf('background-image: url(%s)', $this->availability->regionInfoWindowIcon) : ''?>">
											</div>
											<button type="button" 
													class="button button-primary"
													data-calendarista-preview-icon="regionInfoWindowIcon"
													name="iconUrlRemove">
													<i class="fa fa-remove"></i>
											</button>
									</td>
								</tr>
								<tr>
									<td>
										<div>
											<label for="regionInfoWindowDescription">
												<?php esc_html_e('Info window description', 'calendarista'); ?>
											</label>
										</div>
										<textarea type="text" 
												class="large-text"
												name="regionInfoWindowDescription"
												rows="3"
												id="regionInfoWindowDescription"><?php echo esc_html($this->availability->regionInfoWindowDescription) ?></textarea>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<hr>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="submit" 
									name="calendarista_update" 
									id="calendarista_update" 
									class="button button-primary" 
									value="<?php esc_html_e('Save changes', 'calendarista') ?>"
									<?php echo !$this->availability->id ? 'disabled' : ''?>>
							<input type="submit" 
									name="calendarista_delete" 
									id="calendarista_delete" 
									class="button button-primary" 
									value="<?php esc_html_e('Reset', 'calendarista') ?>"
									<?php echo !$this->availability->regionAddress ? 'disabled' : ''?>>
						</p>
					</form>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right">
			<div id="widgets-right">
				<div class="widgets-holder-wrap">
					<div class="widgets-sortables">	
						<br>
						<div  class="woald-container">
							<div class="woald-map">
								<div class="woald-map-canvas"></div>
							</div>
						</div>
						<br class="clear">
					</div>
				</div>
			</div>
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
				this.requestUrl = options['requestUrl'];
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.$form = $('form[id="form1"]');
				this.$availability = $('select[name="availability"]');
				this.$displayMarker = $('input[name="showMapMarker"]');
				this.$fieldsContainer = $('.availability-map-fields');
				this.$fields = this.$fieldsContainer.find('input, textarea, select, button');
				this.$displayMarker.on('change', function(){
					context.fieldState();
				});
				this.$availability.on('change', function(e){
					var val = parseInt($(this).val(), 10);
					if(val !== -1){
						//parsley 1
						context.$form.off('submit.Parsley');
						//parsley 2
						context.$form.off('form:validate');
						context.$form.submit();
					}
				});
				new Woald.creator({
					'id': '#woald_creator'
					, 'selectedMapStyle': options['selectedMapStyle']
				});
				new Calendarista.imageSelector({'id': '#form1', 'previewImageUrl': options['previewImageUrl']});
				this.fieldState();
			};
			calendarista.availabilityMap.prototype.fieldState = function(){
				if(this.$displayMarker.is(':checked')){
					this.$fields.prop('disabled', false);
					this.$fieldsContainer.css({'display': 'block'});
				}else{
					this.$fields.prop('disabled', true);
					this.$fieldsContainer.css({'display': 'none'});
				}
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.availabilityMap({
				'requestUrl': '<?php echo $this->baseUrl ?>'
				, 'previewImageUrl': '<?php echo CALENDARISTA_PLUGINDIR ?>assets/img/no-preview-thumbnail.png'
				, 'selectedMapStyle': '<?php echo $this->availability->styledMaps ?>'
		});
		</script>
	<?php
	}
}