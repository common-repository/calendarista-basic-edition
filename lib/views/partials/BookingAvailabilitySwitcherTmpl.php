<?php
class Calendarista_BookingAvailabilitySwitcherTmpl extends Calendarista_TemplateBase{
	public $availabilities = array();
	public $availability;
	public $availabilityId;
	public $searchResultAvailabilityId;
	public function __construct($availabilities, $searchResultAvailabilityId = null){
		parent::__construct();
		$this->availabilities = $availabilities;
		$this->searchResultAvailabilityId = $searchResultAvailabilityId;
		$this->availability = new Calendarista_Availability(array());
		$this->availabilityId = (int)$this->getPostValue('availabilityId');
		if(!$this->availabilityId){
			$this->availabilityId = (int)$this->getViewStateValue('availabilityId');
		}
		if(!$this->availabilityId){
			$this->availabilityId = $this->searchResultAvailabilityId;
		}
		if(count($this->availabilities) > 0){
			if(!$this->availabilityId){
				$this->availabilityId = $this->availabilities[0]->id;
			}
			$this->render();
		}
	}
	public function selectedValue($id){
		return $this->availabilityId === $id ? 'selected=selected' : '';
	}
	public function checkedValue($id){
		return $this->availabilityId === $id ? 'checked' : '';
	}
	
	public function render(){
	?>
	<div class="availability_placeholder">
		<?php if(count($this->availabilities) > 1):?>
		<input type="hidden" name="oldAvailabilityId" />
		<input type="hidden" 
			name="availabilityId" 
			class="calendarista_parsley_validated" 
			data-parsley-required="true" 
			data-parsley-errors-messages-disabled="true" 
			value="<?php echo $this->availabilityId ?>" />
		<div class="col-xl-12">
			<div class="form-group">
				<?php if($this->availabilityThumbnailView): ?>
				<label class="form-control-label calendarista-typography--caption1" for="availability_<?php echo $this->projectId; ?>">
					<?php echo esc_html($this->stringResources['BOOKING_AVAILABILITY_SELECTION_LABEL']) ?>
				</label>
				<div class="container calendarista-availability-card-container">
					<div class="row align-items-center calendarista-card-row">
						<?php foreach($this->availabilities as $availability):?>
							<div class="col calendarista-availability-card-col">
								<div class="card calendarista-availability-card">
									<?php if($availability->imageUrl): ?>
									<img src="<?php echo esc_url($availability->imageUrl) ?>" class="card-img-top" alt="<?php echo esc_attr($availability->name) ?>">
									<?php endif; ?>
									<div class="card-header calendarista-availability-card-header calendarista-typography--subtitle1">
										<div class="calendarista-typography--subtitle1 calendarista-availability-card-title form-check form-check-inline">
											<label for="availability_<?php echo $availability->id?>"><?php echo esc_html($availability->name) ?></label>
										</div>
									</div>
									<div class="card-body calendarista-availability-card-body">
										<?php if($availability->description): ?>
										<p class="calendarista-typography--subtitle4 calendarista-availability-card-description"><?php echo esc_html($availability->description) ?></p>
										<?php endif; ?>
										<div class="d-grid gap-2 col-6 mx-auto">
											<input class="btn-check" type="radio" name="availability"
												id="availability_<?php echo $availability->id?>"  value="<?php echo $availability->id ?>"  
											<?php echo $this->checkedValue($availability->id); ?> disabled  autocomplete="off">
											<label class="btn btn-outline-primary" for="availability_<?php echo $availability->id?>"><?php esc_html_e('Select', 'calendarista') ?></label>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php else: ?>
					<label class="form-control-label calendarista-typography--caption1" for="availability_<?php echo $this->projectId; ?>">
						<?php echo esc_html($this->stringResources['BOOKING_AVAILABILITY_SELECTION_LABEL']) ?>
					</label>
					<select
						id="availability_<?php echo $this->projectId; ?>"
						name="availability" 
						class="form-select calendarista-typography--caption1" disabled>
						<?php foreach($this->availabilities as $availability):?>
						<option value="<?php echo $availability->id ?>" <?php echo $this->selectedValue($availability->id); ?>>
							<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($availability->name)) ?>
						</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
		</div>
		<?php else: ?>
		<input type="hidden" 
			name="availabilityId" 
			value="<?php echo $this->availabilityId ?>" />
		<?php endif; ?>
	</div>
<?php
	}
}