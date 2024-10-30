<?php
class Calendarista_BookingSearchTmpl extends Calendarista_TemplateBase{
	public $projects = array();
	public $projectInclusionList = array();
	public $projectId;
	public $includeService;
	public $includeTime;
	public $resultPageUrl;
	public $searchResultInline;
	public $excludeEndDate;
	public $excludeEndDateTime;
	public $tags;
	public function __construct($projects = array(), $includeService = false, $includeTime = false, $excludeEndDate = false, $excludeEndDateTime = false, $filterAttr = array(), $resultPage = null){
		parent::__construct();
		$repo = new Calendarista_ProjectRepository();
		if(in_array(-1, $projects)){
			$projects = array();
		}
		$this->projectInclusionList = $projects;
		$this->projects = $repo->readAll($projects);
		$this->includeService = $includeService;
		$this->includeTime = $includeTime;
		$this->excludeEndDate = $excludeEndDate;
		$this->excludeEndDateTime = $excludeEndDateTime;
		$this->resultPageUrl = $this->getResultPageUrl($resultPage);
		$this->uniqueId = uniqid();
		$tagsRepo = new Calendarista_TagsRepository();
		$this->tags = $tagsRepo->readByTagId($filterAttr);
		$repo = new Calendarista_GeneralSettingsRepository();
		$this->generalSetting = $repo->read();
		$this->searchResultInline = isset($_GET['search-result-inline']) ? true : false;
		if($this->searchResultInline){
			$GLOBALS['hook_suffix'] = 'calendarista_search_list';
		}
		$this->render();
	}
	
	public function getResultPageUrl($resultPage){
		if(!$resultPage){
			return null;
		}
		$post = get_post($resultPage);
		if($post){
			return get_page_link($resultPage);
		}
		return null;
	}
	public function formatTime($val){
		//24hour format
		return date('H:i', strtotime($val));
	}
	public function render(){
	?>
	<div class="calendarista calendarista-typography calendarista-calendar-search" id="search_<?php echo $this->uniqueId ?>">
		<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
			<div class="col-xl-12">
				<div class="container-fluid">
					<div class="row calendarista-search-row">
						<?php if($this->includeService && ($this->projects && $this->projects->count() > 0)):?>
						<div class="col-sm">
							<select
								id="search_project_<?php echo $this->uniqueId ?>"
								name="projects" 
								class="form-select calendarista-typography--caption1">
								<option value=""><?php esc_html_e('Any', 'calendarista') ?></option>
								<?php foreach($this->projects as $project):?>
								<?php if(count($this->projectInclusionList) > 0 && !in_array($project->id, $this->projectInclusionList)){
									continue;
								}?>
								<option value="<?php echo $project->id ?>">
									<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($project->name)) ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php endif; ?>
						<div class="col-sm">
							<div class="input-group">
								<input type="text" 
									id="search_start_date_<?php echo $this->uniqueId ?>" 
									class="calendarista-search-start-date form-control <?php echo $this->includeTime ? 'col-sm-6' : '' ?> calendarista-typography--caption1 calendarista-readonly-field calendarista_parsley_validated" 
									readonly
									data-calendarista-loading="<?php echo esc_html($this->stringResources['CALENDAR_LOADING']) ?>">
								<?php if($this->includeTime):?>
								<select
									id="search_timeslot_start_<?php echo $this->uniqueId ?>"
									name="timeslotStart" 
									class="form-select col-sm-6 calendarista-typography--caption1">
									<?php foreach($this->generalSetting->searchTimeslots as $timeslot):?>
									<option value="<?php echo $this->formatTime($timeslot['value']) ?>">
										<?php echo Calendarista_TimeslotHelper::toLocalFormat($timeslot['text']) ?>
									</option>
									<?php endforeach; ?>
								</select>
								<?php endif; ?>
							</div>
						</div>
						<?php if(!$this->excludeEndDateTime): ?>
						<div class="col-sm">
							<div class="input-group">
								<?php if(!$this->excludeEndDate): ?>
								<input type="text" 
									id="search_end_date_<?php echo $this->uniqueId ?>" 
									class="calendarista-search-end-date form-control <?php echo $this->includeTime ? 'col-sm-6' : '' ?> calendarista-typography--caption1 calendarista-readonly-field calendarista_parsley_validated" 
									readonly
									data-calendarista-loading="<?php echo esc_html($this->stringResources['CALENDAR_LOADING']) ?>">
								<?php endif; ?>
								<?php if($this->includeTime):?>
								<select
									id="search_timeslot_end_<?php echo $this->uniqueId ?>"
									name="timeslotEnd" 
									class="form-select <?php echo !$this->excludeEndDate ? 'col-sm-6' : 'col-sm-12' ?> calendarista-typography--caption1">
									<?php foreach($this->generalSetting->searchTimeslots as $timeslot):?>
									<option value="<?php echo $this->formatTime($timeslot['value']) ?>">
										<?php echo Calendarista_TimeslotHelper::toLocalFormat($timeslot['text']) ?>
									</option>
									<?php endforeach; ?>
								</select>
								<?php endif; ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php if($this->tags && count($this->tags) > 0): ?>
			<div class="col-xl-12">
				<div class="container-fluid">
					<div class="row calendarista-search-row">
						<div class="form-check-inline">
							<?php foreach($this->tags as $tag): ?>
								<label class="form-check-label" for="<?php echo sprintf('%s_%s', $this->uniqueId, $tag->id) ?>">
									<input type="checkbox" class="form-check-input" id="<?php echo sprintf('%s_%s', $this->uniqueId, $tag->id) ?>" value="<?php echo $tag->id ?>" name="tags">
									<?php echo esc_html($tag->name) ?>
								</label>&nbsp;&nbsp;
							<?php endforeach;?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
			<div class="col-xl-12">
				<div class="container-fluid">
					<div class="row calendarista-search-row">
						<div class="col calendarista-align-right">
							<?php if($this->resultPageUrl):?>
							<a href="#" class="btn btn-primary calendarista-typography--button" id="search_button_<?php echo $this->uniqueId ?>" target="__blank"><?php echo esc_html($this->generalSetting->searchFilterFindButtonLabel) ?></a>
							<?php else: ?>
							<button class="btn btn-primary calendarista-typography--button" id="search_button_<?php echo $this->uniqueId ?>" type="button"><?php echo esc_html($this->generalSetting->searchFilterFindButtonLabel) ?></button>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-12">
				<div class="container">
					<div class="row">
						<div class="col">
							<div class="calendarista-search-result">
								<div class="calendarista">
									<div id="spinner_search_callback_<?php echo $this->uniqueId ?>" class="calendarista-spinner spinner-grow spinner-grow-sm text-dark calendarista-invisible m-2" role="status">
										<span class="sr-only"><?php echo esc_html($this->stringResources['AJAX_SPINNER'])?></span>
									</div>
									<div id="search_result_<?php echo $this->uniqueId ?>" class="calendarista-search-result">
										<?php if($this->searchResultInline): ?>
											<?php new Calendarista_BookingSearchResultTmpl(); ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<script type="text/javascript">
	(function(){
		"use strict";
		function init(){
			new Calendarista.search({
				'id': '<?php echo $this->uniqueId?>'
				, 'action': 'calendarista_wizard'
				, 'projectList': 'search_project_<?php echo $this->uniqueId ?>'
				, 'projectListInclusion': '<?php echo implode(',', $this->projectInclusionList) ?>'
				, 'searchStartDate': 'search_start_date_<?php echo $this->uniqueId ?>'
				, 'searchStartTime': 'search_timeslot_start_<?php echo $this->uniqueId ?>'
				, 'searchEndDate': 'search_end_date_<?php echo $this->uniqueId ?>'
				, 'searchEndTime': 'search_timeslot_end_<?php echo $this->uniqueId ?>'
				, 'searchButton': 'search_button_<?php echo $this->uniqueId ?>'
				, 'spinnerId': 'search_callback_<?php echo $this->uniqueId ?>'
				, 'dateFormat': '<?php echo $this->generalSetting->shorthandDateFormat ?>'
				, 'firstDayOfWeek': <?php echo $this->generalSetting->firstDayOfWeek ?>
				, 'resultPageUrl': '<?php echo esc_url($this->resultPageUrl) ?>'
				, 'clearLabel': '<?php echo $this->decodeString($this->stringResources["CALENDAR_CLEAR_DATE"]) ?>'
			});
		}
		<?php if($this->notAjaxRequest):?>
		if (window.addEventListener){
		  window.addEventListener('load', onload, false); 
		} else if (window.attachEvent){
		  window.attachEvent('onload', onload);
		}
		function onload(e){
			init();
		}
		<?php else: ?>
		init();
		<?php endif; ?>
	})();
	</script>
<?php
	}
}