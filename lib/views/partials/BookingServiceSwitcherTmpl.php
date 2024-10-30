<?php
class Calendarista_BookingServiceSwitcherTmpl extends Calendarista_TemplateBase{
	public $projects = array();
	public $projectId;
	public function __construct($projects){
		parent::__construct();
		if(count($projects) <= 1){
			return;
		}
		$repo = new Calendarista_ProjectRepository();
		$this->projects = $repo->readAll($projects);
		$this->render();
	}
	public function selectedValue($id){
		return $this->projectId === $id ? 'selected=selected' : '';
	}
	public function checkedValue($id){
		return $this->projectId === $id ? 'checked' : '';
	}
	public function render(){
	?>
	<?php if($this->projects && $this->projects->count() > 0):?>
	<div class="col-xl-12">
		<div class="form-group">
			<?php if($this->serviceThumbnailView): ?>
				<label class="form-control-label calendarista-typography--caption1">
					<?php echo esc_html($this->stringResources['BOOKING_SERVICE_SELECTION_LABEL']) ?>
				</label>
				<input type="hidden" name="oldProjectId" value="<?php echo $this->projectId ?>" />
				<div class="container calendarista-project-card-container">
					<div class="row align-items-center calendarista-card-row">
						<?php foreach($this->projects as $project):?>
							<div class="col calendarista-project-card-col">
								<div class="card calendarista-project-card">
									<?php if($project->previewUrl): ?>
									<img src="<?php echo esc_url($project->previewUrl) ?>" class="card-img-top" alt="<?php echo esc_attr($project->name) ?>">
									<?php endif; ?>
									<div class="card-header calendarista-project-card-header">
										<div class="calendarista-typography--subtitle1 calendarista-project-card-title text-center text-wrap">
											<?php echo esc_html($project->name) ?>
										</div>
									</div>
									<div class="card-body calendarista-project-card-body">
										<div class="d-grid gap-2 col-6 mx-auto">
											<input class="btn-check" type="radio" name="projects" 
											id="project_<?php echo $project->id?>"  value="<?php echo $project->id ?>"  
											<?php echo $this->checkedValue($project->id); ?> disabled>
											<label class="btn btn-outline-primary" for="project_<?php echo $project->id?>">
												<?php esc_html_e('Select', 'calendarista') ?>
											</label>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php else: ?>
				<label class="form-control-label calendarista-typography--caption1">
					<?php echo esc_html($this->stringResources['BOOKING_SERVICE_SELECTION_LABEL']) ?>
				</label>
				<input type="hidden" name="oldProjectId" value="<?php echo $this->projectId ?>" />
				<select
					name="projects" 
					class="form-select calendarista-typography--caption1" disabled>
					<?php foreach($this->projects as $project):?>
					<option value="<?php echo $project->id ?>" <?php echo $this->selectedValue($project->id); ?>>
						<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($project->name)) ?>
					</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
<?php
	}
}