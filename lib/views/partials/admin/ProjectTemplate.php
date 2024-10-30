<?php
class Calendarista_ProjectTemplate extends Calendarista_ViewBase{
	public $project;
	public $createNew;
	public $calendarMode;
	public $pages;
	function __construct( ){
		parent::__construct(false, false);
		$this->calendarMode = Calendarista_CalendarMode::toArray();
		$projectRepo = new Calendarista_ProjectRepository();
		$this->project = new Calendarista_Project($this->parseArgs('project'));
		new Calendarista_ProjectController(
			$this->project
			, array($this, 'newProject')
			, array($this, 'sortOrder')
			, array($this, 'beforeDelete')
			, array($this, 'updated')
		);
		if($this->selectedProjectId !== -1){
			$project = $projectRepo->read($this->selectedProjectId);
			if($project){
				$this->project = $project;
			}else{
				$this->selectedProjectId = -1;
			}
		}
		$this->createNew = $this->selectedProjectId === -1 ? true : false;
		$this->readAllProjects();
		 if(array_key_exists('duplicated', $_GET)){
			$this->duplicated(true);
		} else if(array_key_exists('deleted', $_GET)){
			$this->deleted(true);
		}
		
		$this->pages = $this->getPages();
		$this->render();
	}
	public static function sort_cpts_by_label($post1, $post2){
		return strcasecmp(
			$post1->post_title,
			$post2->post_title
		);
	}
	public static function getPages(){
		$result = array();
		try{
			$pages = get_pages();
			foreach($pages as $page){
				$localPage = get_post_meta($page->ID, CALENDARISTA_META_KEY_NAME, true);
				if($localPage != ''){
					continue;
				}
				array_push($result, array('name'=>$page->post_title, 'id'=>$page->ID));
			}
		}catch(Exception $e){
			Calendarista_ErrorLogHelper::insert($e->getMessage());
		}
		return $result;
	}
	protected function getProjectId(){
		if(isset($_POST['controller']) && $_POST['controller'] === 'project'){
			return isset($_POST['id']) && trim($_POST['id']) ? (int)$_POST['id'] : -1;
		}
		return -1;
	}
	public function getProjectTitle($project){
		return Calendarista_StringResourceHelper::decodeString($project->name);
	}
	public function getProjectName($project){
		return Calendarista_StringResourceHelper::decodeString($project->name);
	}
	public function newProject($project){
		$this->selectedProjectId = -1;
		$this->project = new Calendarista_Project(array());
		$this->newProjectNotice();
	}
	public function sortOrder($result){
		if($result){
			$this->sortOrderNotice();
		}
	}
	public function updated($project, $result){
		$this->project = $project;
		$this->selectedProjectId = $project->id;
		if($result){
			$this->updatedNotice();
		}
	}
	public function beforeDelete($projects){
		$this->beforeDeleteNotice($projects);
	}
	public function deleted($result){
		if($result){
			$this->deletedNotice();
		}
	}
	public function duplicated(){
		$this->duplicateNotice();
	}
	public function updatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The service has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function sortOrderNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The services sort order has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function deletedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The service(s) have been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function duplicateNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The service has been duplicated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function newProjectNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('Create a new service.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function beforeDeleteNotice($projects){
		?>
		<div class="index updated notice is-dismissible">
			<form id="calendarista_form1" data-parsley-validate action="<?php echo esc_url($this->baseUrl) . '&projectId=-1' ?>" method="post">
				<input type="hidden" name="controller" value="project" />
				<input type="hidden" name="id" value="<?php echo $this->project->id ?>" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<?php foreach($projects as $project):?>
				<input type="hidden" name="projects[]" value="<?php echo esc_attr($project) ?>">
				<?php endforeach;?>
				<p>
					<span class="before-delete-notice"><?php esc_html_e('You are about to delete one or more services. Remember, this will delete all data relating to the service, even bookings made on this service will be lost. This cannot be undone. Apply changes?', 'calendarista'); ?> </span>
					<br>
					<br>
					<input type="submit" name="calendarista_delete" class="button button-primary" value="<?php esc_html_e('Apply', 'calendarista')?>" />
					<input type="submit" name="cancel" class="button button-primary" value="<?php esc_html_e('Cancel', 'calendarista')?>" />
					<br class="clear">
				</p>
			</form>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<form id="service_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-excluded="[disabled=disabled]">
						<input type="hidden" name="controller" value="project" />
						<input type="hidden" name="id" value="<?php echo $this->project->id ?>" />
						<input type="hidden" name="orderIndex" value="<?php echo $this->project->orderIndex ?>" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<?php if(!$this->createNew): ?>
								<tr>
									<td>
										<label title="<?php esc_html_e('Service ID', 'calendarista') ?>" class="calendarista-rounded-border">
											<?php echo sprintf('#%s', $this->project->id) ?>
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="single-short-code"><?php esc_html_e('Short code', 'calendarista') ?></label></div>
										<input readonly class="regular-text" value='[calendarista-booking id="<?php echo $this->selectedProjectId ?>"]'/>
										<?php echo do_action('calendarista_service_info', $this->selectedProjectId); ?>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<div><label for="name"><?php esc_html_e('Name', 'calendarista') ?></label></div>
										<input id="name" name="name" type="text" 
											class="regular-text" 
											data-parsley-required="true" 
											data-parsley-pattern="^[^<>'`\u0022]+$"
											value="<?php echo esc_attr(Calendarista_StringResourceHelper::decodeString($this->project->name)) ?>" />
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="calendarMode"><?php esc_html_e('Mode (12 ways to book)', 'calendarista')?></label></div>
										<select id="calendarMode" name="calendarMode" <?php echo !$this->createNew ? 'disabled=disabled' : '' ?>>
											<?php foreach($this->calendarMode as $mode):?>
											<option value="<?php echo $mode['key']?>" 
												<?php echo $this->project->calendarMode === $mode['key'] ? "selected" : ""?>><?php echo $mode['value'] ?></option>
										   <?php endforeach;?>
										</select>
										<?php if(!$this->createNew): ?>
										<p class="description"><?php esc_html_e('Calendar mode can only be selected on new services', 'calendarista') ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td>
									<input name="enableCoupons" 
											type="checkbox" <?php echo $this->project->enableCoupons ? "checked" : ""?> /> 
										<?php esc_html_e('Enable coupons', 'calendarista')?>
									</td>
								</tr>
								<tr>
									<td>
										<input name="membershipRequired" 
											type="checkbox" <?php echo $this->project->membershipRequired ? "checked" : ""?> /> 
										<?php esc_html_e('Require login or registration before booking', 'calendarista')?>
									</td>
								</tr>
								<tr>
									<td>
										<input name="optionalByService" 
											type="checkbox" <?php echo $this->project->optionalByService ? "checked" : ""?> /> 
										<?php esc_html_e('Optional cost applies by service', 'calendarista')?>
									</td>
								</tr>
								<?php 
								/*<tr>
									<td>
										<input name="enableStrongPassword" 
											type="checkbox" <?php echo $this->project->enableStrongPassword ? "checked" : ""?> /> 
										<?php esc_html_e('If membership enabled, use strong password', 'calendarista')?>
									</td>
								</tr>*/
								?>
							<?php if(count($this->pages) > 0): ?>
							<tr>
								<td>
									<div><label for="searchPage"><?php esc_html_e('Search page result', 'calendarista')?></label></div>
									<select name="searchPage">
										<option value=""><?php esc_html_e('None', 'calendarista') ?></option>
										<?php foreach($this->pages as $page): ?>
										<option value="<?php echo esc_attr($page['id'])?>" <?php echo $this->project->searchPage === $page['id'] ? 'selected' : '' ?>><?php echo esc_html($page['name']) ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e('The page where the service short-code is placed. It will be used in the search result.', 'calendarista') ?></p>
								</td>
							</tr>
							<?php endif; ?>
								<tr>
									<td>
										<div><label for="previewUrl"><?php esc_html_e('Cover Image URL', 'calendarista') ?></label></div>
										<input name="previewUrl" type="hidden" 
											value="<?php echo $this->project->previewUrl ?>" />
										<div data-calendarista-preview-icon="previewUrl" class="preview-icon" 
											style="<?php echo $this->project->previewUrl ?
																sprintf('background-image: url(%s)', esc_url($this->project->previewUrl)) : ''?>">
										</div>
										<button
											type="button"
											name="iconUrlRemove"
											data-calendarista-preview-icon="previewUrl"
											class="button button-primary remove-image" 
											title="<?php __('Remove image', 'calendarista')?>">
											<i class="fa fa-remove"></i>
										</button>
										<p class="description"><?php esc_html_e('A cover image to display in the wizard when this service is active or as a thumbnail when multiple service selection is enabled. Note: As a thumbnail, a good size to use is between 200 - 300 pixels.', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="previewImageHeight"><?php esc_html_e('Cover Image Url Height', 'calendarista') ?></label></div>
										<input id="previewImageHeight" name="previewImageHeight" type="text" 
											class="small-text"   
											placeholder="0"
											data-parsley-type="digits"
											value="<?php echo $this->project->previewImageHeight ?>" />px
											<p class="description"><?php esc_html_e('When the value is 0, the original height of the image is used.', 'calendarista')?></p>
									</td>
								</tr>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SINGLE_DAY_EVENT)): ?>
								<tr>
									<td>
										<div><label for="repeatPageSize"><?php esc_html_e('Repeat page size', 'calendarista') ?></label></div>
										<input id="repeatPageSize" 
											name="repeatPageSize" 
											type="text" 
											class="small-text"
											data-parsley-trigger="change focusout"
											data-parsley-type="digits"
											value="<?php echo $this->project->repeatPageSize ?>" />
									<p class="description"><?php esc_html_e('If you enable repetition, the repeated dates are listed in the booking summary. Setting a page size allows you to keep the list compact with a show more button.', 'calendarista'); ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<div>
											<label for=""><?php esc_html_e('Payments', 'calendarista')?></label>
										</div>
										<ul>
											<li>
												<label for="">
													<input name="paymentsMode" 
														value="-1"
														type="radio" <?php echo $this->project->paymentsMode === -1 ? "checked" : ""?> /> 
														<?php esc_html_e('None', 'calendarista')?>
												</label>
											</li>
											<li>
												<label for="">
													<input name="paymentsMode" 
														value="0"
														type="radio" <?php echo $this->project->paymentsMode === 0 ? "checked" : ""?> /> 
														<?php esc_html_e('Collect payment offline', 'calendarista')?>
												</label>
											</li>
											<li>
												<label for="">
														<input name="paymentsMode" 
															value="1"
															type="radio" <?php echo $this->project->paymentsMode === 1 ? "checked" : ""?> /> 
															<?php esc_html_e('Enable online payments', 'calendarista')?>
												</label>
											</li>
											<li>
												<label for="">
													<input name="paymentsMode" 
														value="2"
														type="radio" <?php echo $this->project->paymentsMode === 2 ? "checked" : ""?> /> 
														<?php esc_html_e('Enable online payments and offline mode', 'calendarista')?>
												</label>
											</li>
										</ul>
									</td>
								</tr>
							</body>
						</table>
						<p class="submit">
							<?php if(!$this->createNew):?>
							<input type="submit" name="calendarista_new" id="calendarista_new" class="button" value="<?php esc_html_e('New', 'calendarista') ?>">
							<input type="submit" name="calendarista_beforedelete" id="calendarista_beforedelete" class="button" value="<?php esc_html_e('Delete', 'calendarista') ?>">
							<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>">
							<?php else:?>
							<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Create new', 'calendarista') ?>">
							<?php endif;?>
						</p>
					</form>
				</div>
			</div>
		</div>
		<?php if($this->projects->count() > 0):?>
		<div class="widget-liquid-right calendarista-widgets-right">
			<div id="widgets-right">
				<div class="single-sidebar">
					<div class="widgets-holder-wrap">
						<div class="widgets-sortables ui-droppable ui-sortable">	
							<?php if(!$this->createNew): ?>
								<div class="sidebar-name">
									<h3><?php esc_html_e('Duplicate service', 'calendarista') ?></h3>
								</div>
								<div class="sidebar-description">
									<p class="description">
										<?php esc_html_e('Duplicates the currently selected service', 'calendarista')?>
									</p>
								</div>
								<form id="duplicate_form" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
									<input type="hidden" name="controller" value="project" />
									<input type="hidden" name="id" value="<?php echo $this->project->id ?>" />
									<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
									<div>
									<input id="duplicateProjectName" 
											name="duplicateProjectName" 
											data-parsley-required="true" 
											data-parsley-pattern="^[^<>'`\u0022]+$"
											type="text" 
											value="<?php echo sprintf('%s (%s)', esc_attr(Calendarista_StringResourceHelper::decodeString($this->project->name)), __('clone', 'calendarista')) ?>"
											class="regular-text" />
									</div>
									<br>
									<div>
										<input type="submit" name="calendarista_duplicate" id="submit" class="button button-primary" value="<?php esc_html_e('Create clone', 'calendarista') ?>">
									</div>
								</form>
							<?php endif; ?>
							<form id="calendarista_form2" action="<?php echo esc_url($this->requestUrl) ?>" method="post">
								<input type="hidden" name="controller" value="project" />
								<input type="hidden" name="sortOrder" />
								<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
								<div class="widgets-sortables ui-droppable ui-sortable">	
									<div class="sidebar-name">
										<h3><?php esc_html_e('Services', 'calendarista') ?></h3>
									</div>
									<div class="sidebar-description">
										<p class="description">
											<?php esc_html_e('List of services below. Drag and drop header to rearrange the order.', 'calendarista')?>
										</p>
									</div>
									<div class="accordion-container project-items">
										<ul class="outer-border">
										<?php foreach($this->projects as $project):?>
											<li class="control-section accordion-section">
												<h3 class="accordion-section-title <?php echo $this->selectedProjectId === $project->id ? 'calendarista-accordion-selected' : '' ?>" tabindex="0">
													<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
													<input id="checkbox_<?php echo $project->id ?>" title="#<?php echo $project->id ?>" type="checkbox" name="projects[]" value="<?php echo $project->id ?>"> 
													<span title="<?php echo esc_attr($this->getProjectTitle($project)) ?>">
														<?php echo esc_html($this->getProjectName($project)) ?>
													</span> 
													<a type="submit" class="edit-linkbutton alignright" href="<?php echo esc_url($this->baseUrl) . '&projectId=' . $project->id; ?>">
														[<?php esc_html_e('Edit', 'calendarista') ?>]
													</a>
												</h3>
											</li>
									   <?php endforeach;?>
										</ul>
										<p class="alignright">
											<input type="submit" name="calendarista_beforedelete" id="calendarista_beforedelete" class="button button-primary" value="<?php esc_html_e('Delete', 'calendarista') ?>" disabled>
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
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		<div class="clear"></div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.projects = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
					$('.accordion-container ul').accordion({
					  collapsible: true
					  , active: null
					});
				});
			};
			calendarista.projects.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.requestUrl = options['requestUrl'];
				this.baseUrl = options['baseUrl'];
				this.$serviceForm = $('#service_form');
				this.$duplicateForm = $('#duplicate_form');
				this.$createNewButton = $('input[name="calendarista_new"]');
				this.$createDuplicateButton = $('input[name="calendarista_duplicate"]');
				this.$availableColor = $('input[name="availableColor"]');
				this.$unavailableColor = $('input[name="unavailableColor"]');
				this.$currentDayColor = $('input[name="currentDayColor"]');
				this.$selectedDayRangeColor = $('input[name="selectedDayRangeColor"]');
				this.$halfDayRangeColor = $('input[name="halfDayRangeColor"]');
				this.$selectedDayColor = $('input[name="selectedDayColor"]');
				this.$rangeUnavailableDayColor = $('input[name="rangeUnavailableDayColor"]');
				this.$calendarMode = $('select[name="calendarMode"]');
				this.$halfDayRangeColorContainer = $('.half-day-range-color');
				this.$selectedDayRangeColorContainer = $('.selected-day-range-color');
				this.$selectedDayColorContainer = $('.selected-day-color');
				this.$rangeUnavailableDayColorContainer = $('.range-unavailable-day-color');
				this.$projectItemInputFields = $('.project-items input[type="checkbox"], .project-items button[type="submit"], .project-items a');
				this.$projectListItems = $('.accordion-container.project-items ul>li');
				this.$sortOrder = $('input[name="sortOrder"]');
				this.$sortOrderButton = $('input[name="calendarista_sortorder"]');
				this.$deleteProjectsButton = $('.project-items input[name="calendarista_beforedelete"]');
				this.$projectCheckboxes = $('.project-items input[type="checkbox"]');
				this.$paymentModes = $('input[name="paymentsMode"]');
				this.$wooProductId = $('select[name="wooProductId"]');
				this.$form2 = $('#calendarista_form2');
				this.$editServiceButton = $('.edit-linkbutton');
				this.$editServiceButton.on('keydown', function(e){
					if (e.keyCode == 13) {
						context.$form2.submit();
					}
				});
				new Calendarista.imageSelector({'id': '#service_form', 'previewImageUrl': options['previewImageUrl']});
				this.$paymentModes.on('change', function(){
					var val = parseInt($(this).val(), 10);
					context.$wooProductId.parsley().reset();
					if(!context.$wooProductId.is(':disabled')){
						context.$wooProductId.prop('disabled', true);
					}
					if(val === 3/*woocommerce*/){
						context.$wooProductId.prop('disabled', false);
					}
				});
				$('.accordion-container.project-items ul').accordion({
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
				this.$projectItemInputFields.on('click', function(e){
					e.stopPropagation();
				});
				this.$projectCheckboxes.on('change', function(e){
					var hasChecked = context.$projectCheckboxes.is(':checked');
					if(hasChecked){
						context.$deleteProjectsButton.prop('disabled', false);
					}else{
						context.$deleteProjectsButton.prop('disabled', true);
					}
				});
				this.$calendarMode.on('change', function(e){
					context.calendarModeChanged();
				});
				this.$createNewButton.on('click', function(e){
					context.$serviceForm.prop('action', context.baseUrl);
				});
				this.$createDuplicateButton.on('click', function(e){
					context.$duplicateForm.prop('action', context.baseUrl);
				});
				this.$availableColor.wpColorPicker();
				this.$unavailableColor.wpColorPicker();
				this.$currentDayColor.wpColorPicker();
				this.$selectedDayRangeColor.wpColorPicker();
				this.$halfDayRangeColor.wpColorPicker();
				this.$selectedDayColor.wpColorPicker();
				this.$rangeUnavailableDayColor.wpColorPicker();
				this.calendarModeChanged();
			};
			calendarista.projects.prototype.calendarModeChanged = function(){
				var selectedIndex = parseInt(this.$calendarMode.val(), 10);
				this.$selectedDayRangeColorContainer.addClass('hide');
				this.$halfDayRangeColorContainer.addClass('hide');
				this.$selectedDayRangeColor.prop('disabled', true);
				this.$halfDayRangeColor.prop('disabled', true);
				this.$selectedDayColorContainer.addClass('hide');
				this.$selectedDayColor.prop('disabled', true);
				this.$rangeUnavailableDayColorContainer.addClass('hide');
				this.$rangeUnavailableDayColor.prop('disabled', true);
				switch(selectedIndex){
					case 3:
					case 4:
					this.$selectedDayRangeColorContainer.removeClass('hide');
					this.$selectedDayRangeColor.prop('disabled', false);
					if(selectedIndex === 4){
						this.$rangeUnavailableDayColorContainer.removeClass('hide');
						this.$rangeUnavailableDayColor.prop('disabled', false);
					}
					break;
					case 5:
					this.$selectedDayRangeColorContainer.removeClass('hide');
					this.$halfDayRangeColorContainer.removeClass('hide');
					this.$selectedDayRangeColor.prop('disabled', false);
					this.$halfDayRangeColor.prop('disabled', false);
					break;
					default:
					this.$selectedDayColorContainer.removeClass('hide');
					this.$selectedDayColor.prop('disabled', false);
				}
			};
			calendarista.projects.prototype.updateSortOrder = function(){
				var sortOrder = this.getSortOrder(this.$projectListItems, 'input[name="projects[]"]');
				this.$sortOrder.val(sortOrder.join(','));
				this.$sortOrderButton.prop('disabled', false);
			};
			calendarista.projects.prototype.getSortOrder = function($sortItems, selector){
				var i
					, sortOrder = []
					, $item;
				for(i = 0; i < $sortItems.length; i++){
					$item = $($sortItems[i]);
					sortOrder.push($item.find(selector).val() + ':' + $item.index());
				}
				return sortOrder;
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.projects({
			<?php echo $this->requestUrl ?>'
			, 'baseUrl': '<?php echo esc_url($this->baseUrl) . '&projectId=-1' ?>'
		});
		</script>
		<?php
	}
}