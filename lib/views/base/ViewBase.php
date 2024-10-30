<?php
class Calendarista_ViewBase
{
	public $projects;
	public $project;
	public $selectedProjectId;
	public $selectedProjectName;
	public $projectRepo;
	public $identifier;
	public $selectedTab;
	public $requestUrl;
	public $baseUrl;
	private $projectsFilter;
	public function __construct($projectNotice = true, $loadProjects = false, $page = 'calendarista-index', $projectsFilter = false){
		$this->identifier = 'calendarista-tab';
		$this->selectedTab = isset($_GET[$this->identifier]) ? (int)$_GET[$this->identifier] : 0;
		$this->selectedProjectId = isset($_REQUEST['projectId']) ? (int)$_REQUEST['projectId'] : -1;
		$this->projectsFilter = $projectsFilter;
		$this->baseUrl = admin_url() . 'admin.php?page=' . $page;
        if($this->selectedTab !== null){
			$this->baseUrl .= '&calendarista-tab=' . $this->selectedTab;
		}
		$this->requestUrl = $this->baseUrl . '&projectId=' . $this->selectedProjectId;
		$this->projects = new Calendarista_Projects();
		$this->projectRepo = new Calendarista_ProjectRepository();
		if($loadProjects){
			$this->readAllProjects();
		}
		if($this->selectedProjectId === -1 && $projectNotice){
			$this->projectRequiredNotice();
		}
	}
	public function getProject(){
		if(!$this->project){
			$projectRepo = new Calendarista_ProjectRepository();
			$this->project = $projectRepo->read($this->selectedProjectId);
		}
		return $this->project;
	}
	public function readAllProjects(){
		$this->projects->clear();
		$projects = $this->projectRepo->readAll($this->projectsFilter);
		foreach($projects as $project){
			if($this->selectedProjectId === $project->id){
				$this->selectedProjectName = $project->name;
				$this->project = $project;
			}
			$this->projects->add($project);
		}
	}
	public function trimString($val, $maxSize = 20, $continuation = false){
		if(strlen($val) <= $maxSize){
			return $val;
		}
		$name = mb_substr($val, 0, $maxSize, 'utf-8');
		if($continuation){
			$name .= '..';
		}
		return $name;
	}
	public function parseArgs($value){
		if(isset($_POST['controller']) && $_POST['controller'] === $value){
			return $_POST;
		}
		return array();
	}
	public function getPostValue($key, $default = null){
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}
	public function emptyStringIfZero($val){
		if(!$val){
			return '';
		}
		return $val;
	}
	public function sanitize($value){
		return stripslashes($value);
	}
	public function projectRequiredNotice() {
		?>
		<div class="error">
			<p><?php esc_html_e('You must select a service', 'calendarista'); ?></p>
		</div>
		<?php
	}
	function createdSetting($result){
		if($result){
			$this->createdSettingNotification();
		}
	}
	function updatedSetting($result){
		if($result){
			$this->updatedSettingNotification();
		}
	}
	function deletedSetting($result){
		if($result){
			$this->deletedSettingNotification();
		}
	}
	public function createdSettingNotification() {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The setting(s) have been created', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
	}
	public function updatedSettingNotification() {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The setting(s) have been updated', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
	}
	public function deletedSettingNotification() {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The setting(s) have been reset to factory', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
	}
	public function createdNotification() {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('Created successfully', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
	}
	public function updatedNotification() {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The update has completed successfully', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
	}
	public function deletedNotification() {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The deletion has completed successfully', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
	}
	public function renderProjectSelectList($plain = false, $defaultCaption = null, $required = false, $disableErrorMessage = false, $calendarModeExclusion = array(), $projectsToExclude = array(), $displayReminder = false){
		if(!$plain){
	?>
		<div class="projects-modal hide" title="<?php esc_html_e('Services', 'calendarista') ?>">
			<div>
	<?php }?>
				<select id="projectId" name="projectId"
					<?php if($required):?>
					data-parsley-required="true"
					data-parsley-trigger="change" 
					class="calendarista_parsley_validated"
					<?php endif;?>
					<?php if($disableErrorMessage):?>
					data-parsley-errors-messages-disabled="true"
					<?php endif;?>>
					<?php if($defaultCaption):?>
					<option value="<?php echo $required ? '' : -1 ?>"><?php echo $defaultCaption ?></option>
					<?php endif; ?>
					<?php foreach($this->projects as $project):?>
					<?php if(in_array($project->id, $projectsToExclude)){continue;}?>
					<?php if(in_array($project->calendarMode, $calendarModeExclusion)){continue;}?>
					<option value="<?php echo $project->id?>"  data-calendarista-payments-mode="<?php echo $project->paymentsMode ?>" 
						<?php echo $this->selectedProjectId === $project->id ? 'selected' : ''?>><?php echo $this->getProjectName($displayReminder, $project) ?></option>
				   <?php endforeach;?>
				</select>
	<?php if(!$plain){ ?>
			</div>
		</div>
	<?php
		}
	}
	private function getProjectName($displayReminder, $project){
		if($displayReminder && $project->reminder > 0){
			return sprintf('%s - (%d %s)', $project->name, $project->reminder, __('minutes', 'calendarista'));
		}
		return $project->name;
	}
	public function decodeString($args){
		return Calendarista_StringResourceHelper::decodeString($args);
	}
}
?>