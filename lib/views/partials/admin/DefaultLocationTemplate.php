<?php
class Calendarista_DefaultLocationTemplate extends Calendarista_ViewBase{
	public $createNew = true;
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
				<?php esc_html_e('This option allows you to display a default area on the map when it first loads', 'calendarista') ?>
			</p>
			<form id="form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="tabName" value="region_settings"/>
				<input type="hidden" name="contextMenuType" value="0"/>
				<input type="hidden" name="controller" value="map" />
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
									<input type="hidden" id="regionLat" name="regionLat" value="<?php echo  esc_attr($this->map->regionLat) ?>"/>
									<input type="hidden" id="regionLng" name="regionLng" value="<?php echo  esc_attr($this->map->regionLng) ?>"/>
									<input type="text" 
										class="woald_parsley_validated"
										data-parsley-errors-container=".region-lat-lng-error-container"
										id="regionLatLng"
										name="regionAddress"
										value="<?php echo  esc_attr($this->map->regionAddress) ?>"/>
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
									<?php esc_html_e('The area to display on the map when it first loads', 'calendarista')?>
								</p>
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
	<?php
	}
}