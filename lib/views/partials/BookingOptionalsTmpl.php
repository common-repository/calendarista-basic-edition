<?php
class Calendarista_BookingOptionalsTmpl extends Calendarista_TemplateBase{
	public $optionalGroups;
	public $optionals;
	public $optionalStateBag;
	public $costHelper;
	public $optionalsHelper;
	public $optionalRepo;
	public function __construct($stateBag = null){
		parent::__construct($stateBag);
		$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
		$this->optionalRepo = new Calendarista_OptionalRepository();
		$this->optionalGroups = $optionalGroupRepo->readAll($this->projectId);
		$this->optionals = $this->optionalRepo->readAll($this->projectId);
		$this->costHelper = new Calendarista_CostHelper($this->viewState);
		$this->optionalsHelper = $this->costHelper->optionalsHelper;
		$this->optionalStateBag = $this->getViewStateValue('optionals') ? array_map('intval', explode(',', $this->getViewStateValue('optionals'))) : array();
		$this->adjustQuantity();
		$this->render();
	}
	public function adjustQuantity(){
		foreach($this->optionalGroups as $group){
			foreach($this->optionals as $index=>$optional){
				if($optional->groupId !== $group->id){
					continue;
				}
				$args = array(
					'fromDate'=>$this->costHelper->availableDate
					, 'toDate'=>$this->costHelper->endDate
					, 'projectId'=>$this->projectId
				);
				$args['optionalId'] = $optional->id;
				if($optional->limitMode === 1/*by time*/){
					if($this->costHelper->startTimeslot){
						$fromDate = date('Y-m-d H:i:s', strtotime($this->costHelper->availableDate . ' ' . $this->costHelper->startTimeslot->timeslot));
						$args['fromDate'] = $fromDate;
						$args['toDate'] = $fromDate;
					}
					if($this->costHelper->endTimeslot){
						$args['toDate'] = date('Y-m-d H:i:s', strtotime($this->costHelper->endDate . ' ' . $this->costHelper->endTimeslot->timeslot));
					}
				}
				$bookedOptionals = Calendarista_AvailabilityHelper::getOptionalQuantityFromCart($this->project, $args['fromDate'], $args['toDate']);
				$quantity = $this->getIncrementalValue($optional->id, null, $bookedOptionals);
				if($optional->quantity > 0){
					if($group->displayMode === Calendarista_OptionalDisplayMode::INCREMENTAL_INPUT){
						$optional->bookedIncrementQuantity = $quantity ? $quantity : $this->optionalRepo->readUsedIncrementalInputQuantity($args);
					}else{
						$optional->bookedQuantity = $quantity ? $quantity : $this->optionalRepo->readUsedQuantity($args);
					}
				}
			}
		}
	}
	public function getIncrementalValue($id, $value = null, $stateBag = null){
		$result = $value;
		if(!$stateBag){
			$stateBag = $this->getViewStateValue('optional_incremental');
		}
		$optionalStateBag = $stateBag ? explode(',', $stateBag) : array();
		foreach($optionalStateBag as $osb){
			$needle = $id . ':';
			if(strpos($osb, $needle) !== false){
				$item = explode(':', $osb);
				$result = $item[1];
				break;
			}
		}
		return $result;
	}
	public function selectedValue($value){
		return in_array($value, $this->optionalStateBag) ? 'selected=selected' : null;
	}
	public function checked($value){
		return in_array($value, $this->optionalStateBag) ? 'checked="true"' : null;
	}
	public function incrementalInputInStock($group){
		if(!$group->minRequired){
			return true;
		}
		$result = false;
		foreach($this->optionals as $optional){
			if($optional->groupId !== $group->id){
				continue;
			}
			if($optional->hasQuantity()){
				$result = true;
			}
		}
		return $result;
	}
	public function renderIncrementalInput($group){
	?>
		<div class="container calendarista-optional-incremental-container">
			<div class="row align-items-center">
				<?php foreach($this->optionals as $optional):?>
				<?php if($optional->groupId !== $group->id){continue;}?>
				<div class="col calendarista-project-card-col">
					<div class="card calendarista-optional-card">
						<?php if($optional->thumbnailUrl): ?>
						<img src="<?php echo esc_url($optional->thumbnailUrl) ?>" class="card-img-top" alt="<?php echo esc_attr(Calendarista_StringResourceHelper::decodeString($optional->name)) ?>">
						<?php endif; ?>
						<div class="card-header calendarista-optional-card-header">
							<div class="calendarista-typography--subtitle1 calendarista-optional-card-title text-center text-wrap">
								<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($optional->name)) ?>
							</div>
						</div>
						<div class="card-body calendarista-optional-card-body mx-auto">
							<div class="d-grid gap-2">
								<?php if($optional->description): ?>
									<div class="calendarista-typography--subtitle4"><?php echo esc_html(Calendarista_StringResourceHelper::decodeString($optional->description)) ?></div>
								<?php endif; ?>
								<div class="col calendarista-increment-button">
									<div class="input-group">
										<button type="button" 
											class="calendarista-increment-left-minus btn btn-outline-danger"  
											data-calendarista-input="optional_incremental_<?php echo $optional->id ?>"
										<?php if(!$optional->hasQuantity()):?>
											disabled
										 <?php endif; ?>>
											<span class="fa fa-minus"></span>
										</button>
										<input type="text" 
											id="optional_incremental_<?php echo $optional->id ?>" 
											name="optional_incremental_<?php echo $optional->id?>" 
											class="form-control calendarista-incremental-input calendarista_parsley_validated incremental_input_group_<?php echo $group->id ?>" 
											value="<?php echo $this->getIncrementalValue($optional->id, $optional->minIncrement) ?>" 
											data-calendarista-min="<?php echo $optional->minIncrement ?>" 
											data-calendarista-max="<?php echo $optional->getMaxIncrement() ?>"
											data-parsley-errors-messages-disabled="true"
											data-parsley-error-message="<?php echo sprintf($this->stringResources['OPTIONAL_QUANTITY_REQUIRED'], esc_attr($optional->name)) ?>"
											<?php if($group->minRequired > 0):?>
											data-parsley-group-required=".incremental_input_group_<?php echo $group->id ?>"
											<?php endif;?>
											readonly
											<?php if(!$optional->hasQuantity()):?>
											disabled
											<?php endif; ?>>
										<button type="button" 
											class="calendarista-increment-right-plus btn btn-outline-primary" 
											data-calendarista-input="optional_incremental_<?php echo $optional->id ?>"
											<?php if(!$optional->hasQuantity()):?>
												disabled
											 <?php endif; ?>>
											<span class="fa fa-plus"></span>
										</button>
										<?php if($optional->cost > 0): ?>
											<span class="input-group-text"><?php echo Calendarista_MoneyHelper::formatCurrencySymbol(sprintf('%g', $optional->cost), true) ?></span>
										<?php endif; ?>
									</div>
									<?php if($optional->quantity > 0): ?>
										<?php if(!$optional->hasQuantity()):?>
										<div class="calendarista-optional-quantity-exhasuted"><?php echo esc_html($this->stringResources['OPTIONAL_QUANTITY_EXHAUSTED']) ?></div>
										<?php else: ?>
										<div class="calendarista-optional-quantity-status"><?php echo sprintf($this->stringResources['OPTIONAL_QUANTITY_STATUS'], $optional->getQuantity()) ?></div>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach;?>
				<?php if(!$this->incrementalInputInStock($group)):?>
					<input type="hidden" 
						data-parsley-error-message="<?php echo esc_html($this->stringResources['OPTIONAL_QUANTITY_UNAVAILABLE']) ?>"
						class="calendarista_parsley_validated" 
						data-parsley-required="true">
				<?php endif;?>
			</div>
		</div>
	<?php
	}
	public function renderDropdownList($group){
	?>
		<select id="optional_group_<?php echo $group->id ?>" 
			name="optional_group_<?php echo $group->id ?>[]" 
			class="form-select calendarista-typography--caption1 calendarista-optional calendarista_parsley_validated"
			<?php echo $group->displayMode === 3 ? 'multiple' : ''?>
			<?php if($group->minRequired > 0):?>
			data-parsley-required="true"
			<?php endif;?>
			<?php if($group->minRequired > 0 && $group->displayMode === 3/*listbox*/):?>
				data-parsley-mincheck="<?php echo $group->minRequired?>"
			<?php endif;?>
			<?php if($group->maxSelection > 0 && $group->displayMode === 3/*listbox*/):?>
				data-parsley-maxcheck="<?php echo $group->maxSelection ?>"
			<?php endif;?>>
		<?php foreach($this->optionals as $optional):?>
			<?php if($optional->groupId !== $group->id){continue;}?>
			<option value="<?php echo $optional->id?>" <?php echo !$optional->hasQuantity() ? 'disabled' : '' ?> 
				<?php echo $this->selectedValue($optional->id); ?>><?php echo $this->optionalsHelper->formatOptionalItemCaption($optional, $group); ?></option>
		<?php endforeach;?>
		</select>
		<?php if($group->displayMode === 3):?>
		<div class="calendarista-text calendarista-typography--caption1">
			<p><?php echo esc_html($this->stringResources['OPTIONAL_LISTBOX_NOTE'])?></p>
		</div>
		<?php endif;?>
	<?php 
	}
	public function renderInput($group){
		$type = $group->displayMode === 0 ? 'checkbox' : 'radio';
		$j = 0;
	?>
		<?php for($i = 0; $i < $this->optionals->count(); $i++):?>
			<?php $optional = $this->optionals->item($i);
				if($optional->groupId !== $group->id){continue;}?>
		<div class="form-check">
			<label class="form-check-label calendarista-typography--caption1">	
				<input type="<?php echo $type; ?>" value="<?php echo $optional->id?>"  
					class="form-check-input calendarista-optional calendarista_parsley_validated"
					data-parsley-errors-container="#form_check_input_error_message<?php echo $group->id ?>"
					<?php if(($type === 'checkbox' && $group->minRequired) && $j === 0):?>
					data-parsley-mincheck="<?php echo $group->minRequired ?>"
					data-parsley-required="true"
					<?php endif;?>
					<?php if(($type === 'checkbox' && $group->maxSelection) && $j === 0):?>
					data-parsley-maxcheck="<?php echo $group->maxSelection ?>"
					<?php endif;?>
					<?php if(($type === 'radio' && $group->minRequired) && $j === 0):?>
					checked
					<?php endif;?>
					 <?php if(!$optional->hasQuantity()):?>
					 disabled
					 <?php endif; ?>
						name="optional_group_<?php echo $group->id?>[]" <?php echo $this->checked($optional->id); ?>>
					<?php echo $this->optionalsHelper->formatOptionalItemCaption($optional, $group); ?>
			</label>
		</div>
		<?php $j++; ?>
		<?php endfor;?>
		<div id="form_check_input_error_message<?php echo $group->id ?>"></div>
	<?php 
	}
	public function render(){
	?>
	<?php if($this->optionalGroups->count() > 0):?>
		<?php foreach($this->optionalGroups as $group):?>
			<div class="col-xl-12">
				<div class="form-group">
					<label class="calendarista-optional-label form-control-label calendarista-typography--subtitle1" for="calendarista_group_<?php echo $group->id ?>">
						<?php echo esc_html(Calendarista_StringResourceHelper::decodeString($group->name)) ?>
					</label>
					<?php if (in_array($group->displayMode, array(2, 3))): 
						$this->renderDropdownList($group);
					elseif(in_array($group->displayMode, array(4))):
						$this->renderIncrementalInput($group);
					else:
						$this->renderInput($group);
					endif;?>
				</div>
			</div>
	   <?php endforeach;?>
	<?php endif; ?>
	<script type="text/javascript">
	(function(){
		"use strict";
		function init(){
			var optionals = new Calendarista.optionals({
				'id': '<?php echo $this->uniqueId ?>'
				, 'projectId': <?php echo $this->projectId ?>
				, 'ajaxUrl': '<?php echo $this->ajaxUrl ?>'
			})
			, $nextButton;
			$nextButton = optionals.$root.find('button[name="next"]');
			$nextButton.on('click', function(e){
				if(!Calendarista.wizard.isValid(optionals.$root)){
					e.preventDefault();
					return false;
				}
				optionals.unload();
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