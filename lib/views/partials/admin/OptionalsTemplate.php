<?php
class Calendarista_OptionalsTemplate extends Calendarista_ViewBase{
	public $optional;
	public $optionals;
	public $optionalGroup;
	public $optionalGroups;
	public $selectedGroupId;
	public $selectedOptionalId;
	public $selectedIndex = 'null';
	public $displayModes;
	public $createNewGroup;
	public $createNewOptional;
	function __construct( ){
		parent::__construct();
		$this->selectedGroupId = $this->getGroupId();
		$this->selectedOptionalId = $this->getOptionalId();
		$this->displayModes = Calendarista_OptionalDisplayMode::toArray();
		$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
		$this->optionalGroup = new Calendarista_OptionalGroup($this->parseArgs('optional_group'));
		$optionalRepo = new Calendarista_OptionalRepository();
		$this->optional = new Calendarista_Optional($this->parseArgs('optional'));
		new Calendarista_OptionalGroupController(
			$this->optionalGroup
			, array($this, 'newGroup')
			, array($this, 'groupSortOrder')
			, array($this, 'groupCreated')
			, array($this, 'groupUpdated')
			, array($this, 'groupDeleted')
		);
		new Calendarista_OptionalController(
			$this->optional
			, array($this, 'newOptional')
			, array($this, 'optionalSortOrder')
			, array($this, 'optionalCreated')
			, array($this, 'optionalUpdated')
			, array($this, 'optionalDeleted')
		);
		$this->optionals = new Calendarista_Optionals();
		$this->optionalGroups = new Calendarista_OptionalGroups();
		if($this->selectedProjectId !== -1){
			$this->optionals = $optionalRepo->readAll($this->selectedProjectId);
			$this->optionalGroups = $optionalGroupRepo->readAll($this->selectedProjectId);
		}
		if($this->selectedOptionalId !== -1){
			$optional = $optionalRepo->read($this->selectedOptionalId);
			if($optional){
				$this->optional = $optional;
				$this->selectedGroupId = $optional->groupId;
			}
		}
		if($this->selectedGroupId !== -1){
			$optionalGroup = $optionalGroupRepo->read($this->selectedGroupId);
			if($optionalGroup){
				$this->optionalGroup = $optionalGroup;
			}
			$this->selectedIndex = $this->getIndex();
		}
		$this->createNewOptional = $this->selectedOptionalId === -1 ? true : false;
		$this->createNewGroup = $this->selectedGroupId === -1 ? true : false;
		if(!$this->project){
			$this->project = $this->projectRepo->read($this->selectedProjectId);
		}
		$this->render();
	}
	protected function getIndex(){
		if($this->selectedGroupId !== -1){
			for($i = 0; $i < $this->optionalGroups->count(); $i++){
				$optionalGroup = $this->optionalGroups->item($i);
				if($this->selectedGroupId === $optionalGroup->id){
					return $i;
				}
			}
		}
		return 'null';
	}
	protected function getGroupId(){
		if(isset($_POST['controller']) && $_POST['controller'] === 'optional_group'){
			return isset($_POST['id']) && trim($_POST['id']) ? (int)$_POST['id'] : -1;
		}
		return -1;
	}
	protected function getOptionalId(){
		if(isset($_POST['controller']) && $_POST['controller'] === 'optional'){
			return isset($_POST['id']) && trim($_POST['id']) ? (int)$_POST['id'] : -1;
		}
		return -1;
	}
	public function groupHasOptionals($groupId){
		foreach($this->optionals as $optional){
			if($optional->groupId === $groupId){
				return true;
			}
		}
		return false;
	}
	public function selectedDisplayMode($displayMode){
		return $this->optionalGroup->displayMode === $displayMode ? 'selected' : '';
	}
	public function newOptional($optional){
		$this->selectedGroupId = $optional->groupId;
		$this->selectedOptionalId = -1;
		$this->optional = new Calendarista_Optional(array());
		$this->newOptionalNotice();
	}
	public function optionalSortOrder($result){
		if($result){
			$this->optionalSortOrderNotice();
		}
	}
	public function optionalCreated($optional, $result){
		$this->optional = $optional;
		$this->selectedGroupId = $optional->groupId;
		$this->selectedOptionalId = $optional->id;
		if($result){
			$this->optionalCreatedNotice();
		}
	}
	public function optionalUpdated($optional, $result){
		$this->optional = $optional;
		$this->selectedGroupId = $optional->groupId;
		$this->selectedOptionalId = $optional->id;
		if($result){
			$this->optionalUpdatedNotice();
		}
	}
	public function optionalDeleted($result){
		if($result){
			$this->optionalDeletedNotice();
		}
	}
	public function optionalUpdatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The optional has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function optionalSortOrderNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The optionals sort order has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function optionalCreatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The optional has been created.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function optionalDeletedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The optional has been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function newOptionalNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('Create a new optional.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function newGroup(){
		$this->selectedGroupId = -1;
		$this->optionalGroup = new Calendarista_OptionalGroup(array());
		$this->newGroupNotice();
	}
	public function groupSortOrder($result){
		if($result){
			$this->groupSortOrderNotice();
		}
	}
	public function groupCreated($newId){
		if($newId){
			$this->optionalGroup->id = $newId;
			$this->selectedGroupId = $newId;
			$this->groupCreatedNotice();
		}
	}
	public function groupUpdated($optionalGroup, $result){
		$this->selectedGroupId = $optionalGroup->id;
		if($result){
			$this->groupUpdatedNotice();
		}
	}
	public function groupDeleted($result){
		$this->optionalGroup = new Calendarista_OptionalGroup(array());
		$this->selectedGroupId = -1;
		if($result){
			$this->groupDeletedNotice();
		}
	}
	public function groupUpdatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The group has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function groupSortOrderNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The groups sort order has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function groupCreatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The group has been created.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function groupDeletedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The group has been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function newGroupNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('Create a new group to contain your optional items.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function errorNotice($message) {
		?>
		<div class="index error notice">
			<p><?php echo sprintf(__('The operation failed unexpected with [%s]. Try again?', 'calendarista'), $message); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<?php if(!$this->optionalGroups->count()):?>
					<p class="description"><?php esc_html_e('Hint: 1) Create a group, 2) Add one or more optional items to the group', 'calendarista') ?></p>
					<?php endif; ?>
					<?php if($this->selectedGroupId !== -1):?>
					<p class="description"><?php esc_html_e('Add new optional item to selected group', 'calendarista') ?></p>
					<form id="calendarista_form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="optional" />
						<input type="hidden" name="id" value="<?php echo $this->optional->id ?>" />
						<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
						<input type="hidden" name="groupId" value="<?php echo $this->selectedGroupId ?>" />
						<input type="hidden" name="orderIndex" value="<?php echo $this->optional->orderIndex ?>" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<div><label for="name"><?php esc_html_e('Name', 'calendarista') ?></label></div>
										<input id="name" 
											name="name" 
											type="text" 
											class="regular-text" 
											data-parsley-required="true" 
											data-parsley-pattern="^[^<>'`\u0022]+$" 
											placeholder="<?php esc_html_e('Item name', 'calendarista')?>" 
											value="<?php echo esc_attr(Calendarista_StringResourceHelper::decodeString($this->optional->name)) ?>" />
									</td>
								</tr>
								<?php if($this->project->paymentsMode !== -1):?>
								<tr>
									<td>
										<div><label for="cost"><?php esc_html_e('Cost', 'calendarista') ?></label></div>
										<input id="cost" 
											name="cost" 
											type="text" 
											class="regular-text" 
											data-parsley-trigger="change focusout"
											data-parsley-pattern="^-?\d+(\.\d{1,2})?$"
											placeholder="0.00" 
											value="<?php echo $this->optional->cost ?>" />
										<p class="description"><?php esc_html_e('Negative values supported', 'calendarista') ?></p>
									</td>
								</tr>
								<?php endif; ?>
								<?php if($this->optionalGroup->displayMode === Calendarista_OptionalDisplayMode::INCREMENTAL_INPUT): ?>
								<tr>
									<td>
										<div><label for="minIncrement"><?php esc_html_e('Minimum decrements', 'calendarista') ?></label></div>
										<input id="minIncrement" 
											name="minIncrement" 
											type="text" 
											class="small-text" 
											data-parsley-type="digits" 
											placeholder="0" 
											value="<?php echo $this->optional->minIncrement ?>" />
										<p class="description"><?php esc_html_e('The minimum that can be decremented.', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="maxIncrement"><?php esc_html_e('Maximum increments', 'calendarista') ?></label></div>
										<input id="maxIncrement" 
											name="maxIncrement" 
											type="text" 
											class="small-text" 
											data-parsley-type="digits" 
											placeholder="0" 
											value="<?php echo $this->optional->maxIncrement ?>" />
										<p class="description"><?php esc_html_e('The maximum that can be incremented.', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="description"><?php esc_html_e('Description', 'calendarista') ?></label></div>
										<textarea type="text" 
												class="large-text"
												name="description"
												rows="3"
												id="description"><?php echo Calendarista_StringResourceHelper::decodeString($this->optional->description) ?></textarea>
										<p class="description"><?php esc_html_e('A description to display in the wizard and search result', 'calendarista')?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="thumbnailUrl"><?php esc_html_e('Thumbnail Image', 'calendarista') ?></label></div>
										<input name="thumbnailUrl" type="hidden" 
											value="<?php echo esc_url($this->optional->thumbnailUrl) ?>" />
										<div data-calendarista-preview-icon="thumbnailUrl" class="preview-icon" 
											style="<?php echo $this->optional->thumbnailUrl ?
																sprintf('background-image: url(%s)', esc_url($this->optional->thumbnailUrl)) : ''?>">
										</div>
										<button
											type="button"
											name="iconUrlRemove"
											data-calendarista-preview-icon="thumbnailUrl"
											class="button button-primary remove-image" 
											title="<?php __('Remove image', 'calendarista')?>">
											<i class="fa fa-remove"></i>
										</button>
										<p class="description"><?php esc_html_e('A thumbnail image to display in the search result. Hint, a good size to use: 100x100px', 'calendarista')?></p>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<div><label for="quantity"><?php esc_html_e('Quantity', 'calendarista') ?></label></div>
										<input id="quantity" 
											name="quantity" 
											type="text" 
											class="small-text" 
											data-parsley-type="digits" 
											placeholder="0" 
											value="<?php echo $this->optional->quantity ?>" />
										<p class="description"><?php esc_html_e('Leave default value of 0 if item is not in limited quantity', 'calendarista') ?></p>
									</td>
								</tr>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)): ?>
								<tr>
									<td>
											<div><label for="limitMode"><?php esc_html_e('Limit mode', 'calendarista') ?></label></div>
											<input id="limitMode" 
												name="limitMode" 
												type="radio" 
												value="0"
												<?php echo $this->optional->limitMode === 0 ? 'checked' : '' ?> />
											<?php esc_html_e('Limit by full day', 'calendarista') ?>
											<input id="limitMode" 
												name="limitMode" 
												type="radio" 
												value="1"
												<?php echo $this->optional->limitMode === 1 ? 'checked' : '' ?> />
											<?php esc_html_e('Limit by time slot', 'calendarista') ?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_RETURN)):?>
								<tr>
									<td>
										<input id="doubleCostIfReturn" 
											name="doubleCostIfReturn" 
											type="checkbox" 
											<?php echo $this->optional->doubleCostIfReturn ? 'checked=checked' : '' ?> />
										<?php esc_html_e('Double the cost if return is set', 'calendarista') ?>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<p class="submit">
							<?php if(!$this->createNewOptional):?>
							<input type="submit" name="calendarista_new" id="calendarista_new" class="button" value="<?php esc_html_e('New', 'calendarista') ?>">
							<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button" value="<?php esc_html_e('Delete', 'calendarista') ?>">
							<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>">
							<?php else:?>
							<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Add new optional', 'calendarista') ?>">
							<?php endif;?>
						</p>
					</form>
					<hr>
					<?php endif;?>
					<form id="calendarista_optional_group" name="calendarista_optional_group" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="optional_group" />
						<input type="hidden" name="id" value="<?php echo $this->selectedGroupId ?>" />
						<input type="hidden" name="orderIndex" value="<?php echo $this->optionalGroup->orderIndex ?>" />
						<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
								<?php if($this->selectedProjectId !== -1):?>
								<tr>
									<td>
										<div><label for="name"><?php esc_html_e('Group name', 'calendarista') ?></label></div>
										<input id="name" 
											name="name" 
											type="text" 
											class="regular-text" 
											data-parsley-required="true" 
											data-parsley-pattern="^[^<>'`\u0022]+$" 
											placeholder="<?php esc_html_e('Item name', 'calendarista')?>"  
											value="<?php echo esc_attr(Calendarista_StringResourceHelper::decodeString($this->optionalGroup->name)) ?>" />
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="displayType"><?php esc_html_e('Display mode', 'calendarista')?></label></div>
										<select id="displayMode" name="displayMode">
											<?php foreach($this->displayModes as $id=>$mode):?>
											<option value="<?php echo esc_attr($id) ?>" 
												<?php echo esc_attr($this->selectedDisplayMode($id)) ?>>
												<?php echo esc_html($mode) ?>
											</option>
										   <?php endforeach;?>
										</select>
									</td>
								</tr>
								<tr class="minimum-row">
									<td>
										<div><label><?php esc_html_e('Min selection', 'calendarista') ?></label></div>
										<input
											name="minRequired" 
											type="text" 
											class="small-text" 
											data-parsley-type="digits" 
											placeholder="0" 
											value="<?php echo $this->optionalGroup->minRequired ?>" />
										<p class="description"><?php esc_html_e('Minimum selections required.', 'calendarista') ?></p>
									</td>
								</tr>
								<tr class="max-row">
									<td>
										<div><label><?php esc_html_e('Max selection', 'calendarista') ?></label></div>
										<input
											name="maxSelection" 
											type="text" 
											class="small-text" 
											data-parsley-type="digits" 
											placeholder="0" 
											value="<?php echo $this->optionalGroup->maxSelection ?>" />
										<p class="description"><?php esc_html_e('Max items selection.', 'calendarista') ?></p>
									</td>
								</tr>
								<tr class="required-row">
									<td>
										<input 
											name="minRequired" 
											type="checkbox" 
											<?php echo $this->optionalGroup->minRequired > 0 ? 'checked' : '' ?>
											value="1" /><?php esc_html_e('Required', 'calendarista') ?>
									</td>
								</tr>
								<?php if($this->project->paymentsMode !== -1):?>
								<tr>
									<td>
										<label for="multiplyNone">
											<input id="multiplyNone" value="0" name="multiply" type="radio" <?php echo !$this->optionalGroup->multiply ? 'checked' : ''?>>
											<?php esc_html_e('Add cost to total', 'calendarista')?>
										</label>
										<br>
										<label for="multiplyByDay">
											<input id="multiplyByDay" value="1" name="multiply" type="radio" <?php echo $this->optionalGroup->multiply === 1 ? 'checked' : ''?>>
											<?php esc_html_e('Multiply cost by each selected day/timeslot and add to total', 'calendarista')?>
										</label>
										<br>
										<label for="multiplyBySeat">
											<input id="multiplyBySeat" value="2" name="multiply" type="radio" <?php echo $this->optionalGroup->multiply === 2 ? 'checked' : ''?>>
											<?php esc_html_e('Multiply cost by each selected seat and add to total', 'calendarista')?>
										</label>
										<br>
										<label for="multiplyByDayBySeat">
											<input id="multiplyByDayBySeat" value="3" name="multiply" type="radio" <?php echo $this->optionalGroup->multiply === 3 ? 'checked' : ''?>>
											<?php esc_html_e('Multiply cost by each selected day/timeslot/seat and add to total', 'calendarista')?>
										</label>
									</td>
								</tr>
								<?php endif; ?>
								<?php endif; ?>
							</tbody>
						</table>
						<?php if($this->selectedProjectId !== -1):?>
						<p class="submit">
							<?php if(!$this->createNewGroup):?>
							<input type="submit" name="calendarista_new" id="calendarista_new" class="button" value="<?php esc_html_e('New', 'calendarista') ?>">
							<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button" value="<?php esc_html_e('Delete', 'calendarista') ?>">
							<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>">
							<?php else:?>
							<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Add new group', 'calendarista') ?>">
							<?php endif;?>
						</p>
						<?php endif;?>
					</form>
				</div>
			</div>
		</div>
		<div class="widget-liquid-right calendarista-widgets-right">
			<div id="widgets-right">
				<div class="single-sidebar">
					<div class="sidebars-column-1">
						<div class="widgets-holder-wrap">
							<div class="widgets-sortables ui-droppable ui-sortable">	
								<div class="sidebar-name">
									<h3><?php esc_html_e('Optionals', 'calendarista') ?></h3>
								</div>
								<div class="sidebar-description">
									<p class="description">
										<?php esc_html_e('List of optional groups below. Drag and drop to rearrange the order. Click a group to edit the group or add new optionals to the group.', 'calendarista')?>
									</p>
								</div>
								<?php if($this->optionalGroups->count() > 0):?>
									<div class="column-borders">
										<div class="clear"></div>
										<div class="accordion-container group-items">
											<ul class="outer-border">
											<?php foreach($this->optionalGroups as $group):?>
												<li class="control-section accordion-section">
													<h3 class="accordion-section-title <?php echo $this->selectedGroupId === $group->id ? 'calendarista-accordion-selected' : '' ?>" tabindex="0">
														<form id="calendarista_form2" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
															<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
															<span title="<?php echo strlen($group->name) > 20 ? esc_attr(Calendarista_StringResourceHelper::decodeString($group->name)) : '' ?>">
																<?php echo esc_html($this->trimString(Calendarista_StringResourceHelper::decodeString($group->name))) ?>
															</span>
															<input type="hidden" name="controller" value="optional_group" />
															<input type="hidden" name="id" value="<?php echo $group->id ?>" />
															<input type="hidden" name="groupId" value="<?php echo $group->id ?>" />
															<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
															<button type="submit" class="edit-linkbutton alignright" name="calendarista_edit" title="Edit group" value="<?php echo $group->id; ?>">
																[<?php esc_html_e('Edit', 'calendarista') ?>]
															</button>
															<button type="submit" class="edit-linkbutton alignright" name="calendarista_edit" title="Add new item to group" value="<?php echo $group->id; ?>">
																[<?php esc_html_e('Add', 'calendarista') ?>]
															</button>
														</form>
														<div class="clear"></div>
													</h3>
													<div class="accordion-section-content optional-items">
														<div class="inside">
															<?php if($this->groupHasOptionals($group->id)):?>
															<form id="calendarista_form3" action="<?php echo esc_url($this->requestUrl) ?>" method="post">
																<input type="hidden" name="controller" value="optional" />
																<input type="hidden" name="optionalSortOrder" />
																<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
																<div class="column-borders">
																	<div class="clear"></div>
																	<div class="accordion-container">
																		<ul class="outer-border">
																			<?php foreach($this->optionals as $optional):?>
																			<?php if($optional->groupId !== $group->id){continue;}?>
																			<li class="control-section accordion-section optional-items">
																				<h3 class="accordion-section-title <?php echo $this->selectedOptionalId === $optional->id ? 'calendarista-accordion-selected' : '' ?>" tabindex="0">
																					<i class="calendarista-drag-handle fa fa-align-justify"></i>&nbsp;
																					<input id="checkbox_<?php echo $optional->id ?>" type="checkbox" name="optionals[]" value="<?php echo $optional->id ?>"> 
																					<span title="<?php echo strlen($optional->name) > 15 ? esc_attr($optional->name) : '' ?>">
																						<?php echo esc_html($this->trimString($optional->name, 15)) ?>
																					</span>
																					<?php if($this->project->paymentsMode !== -1):?>
																					<small>
																					- <?php echo number_format($optional->cost, 2, '.', '') ?>
																					</small>
																					<?php endif; ?>
																					<button type="submit" class="edit-linkbutton alignright" name="id" value="<?php echo $optional->id; ?>">
																						[<?php esc_html_e('Edit', 'calendarista') ?>]
																					</button>
																					<div class="clear"></div>
																				</h3>
																			</li>
																			<?php endforeach;?>
																		</ul>
																	</div>
																</div>
																<p class="alignright">
																	<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button button-primary" value="<?php esc_html_e('Delete', 'calendarista') ?>" disabled>
																	<input type="submit" 
																			name="calendarista_sortorder" 
																			id="calendarista_sortorder" 
																			class="button button-primary optional-sort-button" 
																			title="<?php esc_html_e('Save optionals sort order', 'calendarista')?>" 
																			value="<?php esc_html_e('Save order', 'calendarista') ?>" disabled>
																	<br class="clear">
																</p>
															</form>
															<?php else:?>
															<div class="empty-records">
																<p>
																	<?php esc_html_e('You have not added any optional elements to this group yet.', 'calendarista')?>
																</p>
															</div>
															<?php endif; ?>
														</div>
													</div>
												</li>
										   <?php endforeach;?>
											</ul>
											<p class="submit">
												<form id="calendarista_form4" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
													<input type="hidden" name="controller" value="optional_group" />
													<input type="hidden" name="groupSortOrder" />
													<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
													<input type="submit" name="calendarista_sortorder" id="calendarista_sortorder" class="button button-primary group-sort-button alignright" title="<?php esc_html_e('Save groups sort order', 'calendarista')?>" value="<?php esc_html_e('Save order', 'calendarista') ?>" disabled>
												</form>
											<br class="clear">
											</p>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
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
			calendarista.optional = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
					$('.accordion-container.group-items>ul').accordion({
					  collapsible: true
					   , active: <?php echo $this->selectedIndex ?>
					}).sortable({
						axis: 'y'
						, handle: '.calendarista-drag-handle'
						, stop: function( event, ui ) {
							var $this = $(this);
							context.updateGroupSortOrder();
						  // IE doesn't register the blur when sorting
						  // so trigger focusout handlers to remove .ui-state-focus
						  ui.item.children('h3').triggerHandler('focusout');
						  // Refresh accordion to handle new order
						  $this.accordion('refresh');
						  $this.accordion({active: ui.item.index()});
						}
					 });
					$('.accordion-section-content.optional-items ul').accordion({
					  collapsible: false
					   , active: null
					}).sortable({
						axis: 'y'
						, handle: '.calendarista-drag-handle'
						, stop: function( event, ui ) {
							var $this = $(this)
								, $listItems = ui.item.parent().find('li');
							context.updateOptionalSortOrder($listItems);
						  // IE doesn't register the blur when sorting
						  // so trigger focusout handlers to remove .ui-state-focus
						  ui.item.children('h3').triggerHandler('focusout');
						  // Refresh accordion to handle new order
						  $this.accordion('refresh');
						  $this.accordion({active: ui.item.index()});
						}
					 });
				});
			};
			calendarista.optional.prototype.init = function(options){
				var context = this
					, displayModechangeHandlerDelegate = calendarista.createDelegate(this, this.displayModeChanged);
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.requestUrl = options['requestUrl'];
				this.$displayModeDropdownList = $('#displayMode');
				this.$requiredRow = $('.required-row');
				this.$minimumRow = $('.minimum-row');
				this.$maxRow = $('.max-row');
				this.$minRequired = $('.minimum-row input[name="minRequired"]');
				this.$maxSelection = $('.max-row input[name="maxSelection"]');
				this.$required = $('.required-row input[name="minRequired"]');
				this.$groupSortItems = $('div.group-items>ul>li');
				this.$optionalSortItems = $('.optional-items li');
				this.$groupSortOrder = $('input[name="groupSortOrder"]');
				this.$groupSortOrderButton = $('.group-sort-button');
				this.$optionalSortOrder = $('input[name="optionalSortOrder"]');
				this.$optionalSortOrderButton = $('.optional-sort-button');
				this.$optionalCheckboxes = $('.optional-items input[type="checkbox"]');
				this.$deleteOptionalsButton = $('.optional-items input[name="calendarista_delete"]');
				this.$optionalInputFields = $('.group-items input[type="checkbox"], .group-items button[type="submit"]');
				this.$optionalInputFields.on('click', function(e){
					e.stopPropagation();
				});
				this.$optionalCheckboxes.on('change', function(e){
					var hasChecked = context.$optionalCheckboxes.is(':checked');
					if(hasChecked){
						context.$deleteOptionalsButton.prop('disabled', false);
					}else{
						context.$deleteOptionalsButton.prop('disabled', true);
					}
				});
				this.$displayModeDropdownList.on('change', displayModechangeHandlerDelegate);
				this.displayModeChanged();
				new Calendarista.imageSelector({'id': '#calendarista_form1', 'previewImageUrl': options['previewImageUrl']});
			};
			calendarista.optional.prototype.displayModeChanged = function(){
				var selection = parseInt(this.$displayModeDropdownList.val(), 10);
				switch(selection){
					case 1:
					case 2:
					case 4:
					this.$minimumRow.addClass('hide');
					this.$minRequired.prop('disabled', true);
					this.$maxRow.addClass('hide');
					this.$maxSelection.prop('disabled', true);
					break;
					default:
					this.$minimumRow.removeClass('hide');
					this.$minRequired.prop('disabled', false);
					this.$maxRow.removeClass('hide');
					this.$maxSelection.prop('disabled', false);
					break;
				}
				switch(selection){
					case 0:
					case 3:
					this.$requiredRow.addClass('hide');
					this.$required.prop('disabled', true);
					break;
					default:
					this.$requiredRow.removeClass('hide');
					this.$required.prop('disabled', false);
					break;
				}
			};
			calendarista.optional.prototype.updateGroupSortOrder = function(){
				var sortOrder = this.getSortOrder(this.$groupSortItems, 'input[name="groupId"]');
				this.$groupSortOrder.val(sortOrder.join(','));
				this.$groupSortOrderButton.prop('disabled', false);
			};
			calendarista.optional.prototype.updateOptionalSortOrder = function($listItems){
				var sortOrder = this.getSortOrder($listItems, 'button[name="id"]');
				this.$optionalSortOrder.val(sortOrder.join(','));
				this.$optionalSortOrderButton.prop('disabled', false);
			};
			calendarista.optional.prototype.getSortOrder = function($sortItems, selector){
				var i
					, sortOrder = []
					, $item;
				for(i = 0; i < $sortItems.length; i++){
					$item = $($sortItems[i]);
					sortOrder.push($item.find(selector).val() + ':' + $item.index());
				}
				return sortOrder;
			}
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.optional({
				<?php echo $this->requestUrl ?>'
		});
		</script>
		<?php
	}
}