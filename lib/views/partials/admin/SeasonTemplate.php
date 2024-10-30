<?php
class Calendarista_SeasonTemplate extends Calendarista_ViewBase{
	public $season;
	public $selectedSeasonId;
	public $availabilities;
	public $availability;
	public $projectName;
	public $availabilityName;
	public $seasonList;
	public $paymentsMode = -1;
	public $weekdays;
	public function __construct(){
		parent::__construct();
		$this->season = array('start'=>null, 'end'=>null, 'cost'=>null, 'percentageBased'=>0, 'costMode'=>0, 'repeatWeekdayList'=>array(), 'bookingDaysMinimum'=>0, 'bookingDaysMaximum'=>0);
		$this->availabilityId = isset($_GET['availabilityId']) ? (int)$_GET['availabilityId'] : null;
		$this->selectedSeasonId = isset($_POST['id']) ? (int)$_POST['id'] : null;
		if(!$this->selectedSeasonId){
			$this->selectedSeasonId = isset($_GET['id']) ? (int)$_GET['id'] : null;
		}
		if(!$this->availabilityId || isset($_POST['calendarista_new'])){
			$this->selectedSeasonId = null;
		}
		$this->weekdays = Calendarista_Weekday::toArray();
		$this->availability = new Calendarista_Availability(array());
		$seasonRepo = new Calendarista_SeasonRepository();
		new Calendarista_SeasonController( 
			array($this, 'seasonCreatedNotice')
			, array($this, 'seasonUpdatedNotice')
			, array($this, 'seasonDeletedNotice')
			, array($this, 'seasonDeleteManyNotice')
		);
		if(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'pricing_scheme'){
			if($this->getPostValue('seasonId')){
				$this->selectedSeasonId = (int)$this->getPostValue('seasonId');
			}
		}
		if($this->selectedSeasonId){
			$season = $seasonRepo->read($this->selectedSeasonId);
			if(is_array($season)){
				$this->season = $season;
			}
		}
		if($this->selectedProjectId === -1){
			return;
		}
		$repo = new Calendarista_AvailabilityRepository();
		$this->availabilities = $repo->readAll($this->selectedProjectId);
		if($this->availabilityId){
			foreach($this->availabilities as $availability){
				if($availability->id === $this->availabilityId){
					$this->availability = $availability;
					break;
				}
			}
			$this->availabilityName = $this->availability->name;
			$this->requestUrl .= '&availabilityId=' . $this->availabilityId;
		}
		$this->getProject();
		$this->projectName = $this->project->name;
		if($this->project->paymentsMode === -1){
			$this->paymentsRequiredNotice();
			return;
		}
		$this->seasonList = new Calendarista_SeasonList($this->selectedProjectId);
		$this->seasonList->bind();
		$this->render();
	}
	public function repeatWeekdayChecked($value){
		if(count($this->season['repeatWeekdayList']) > 0){
			return in_array($value, $this->season['repeatWeekdayList']) ? 'checked' : '';
		}
		return '';
	}
	public function paymentsRequiredNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('In order to use seasons, you must enable payments on the service.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function seasonCreatedNotice($id) {
		$this->selectedSeasonId = $id;
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The season has been created.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function seasonUpdatedNotice($id) {
		$this->selectedSeasonId = $id;
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The season has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function seasonDeletedNotice() {
		$this->selectedSeasonId = null;
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The season has been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function seasonDeleteManyNotice($result) {
		$this->selectedSeasonId = null;
		?>
		<div class="index updated notice is-dismissible">
			<p><?php echo sprintf(__('%d seasons have been deleted.', 'calendarista'), $result); ?></p>
		</div>
		<?php
	}
	public function getStartDate(){
		if(isset($this->season['start'])){
			return $this->season['start'];
		}
		return '';
	}
	public function getEndDate(){
		if(isset($this->season['end'])){
			return $this->season['end'];
		}
		return '';
	}
	public function render(){
		?>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<?php if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE):?>
						<p class="description"><i><?php esc_html_e('**Seasons will not apply if your cost is time slot based.', 'calendarista') ?></i></p>
						<hr>
					<?php endif; ?>
					<p><?php esc_html_e('Set up season rates', 'calendarista') ?></p>
					<form action="<?php echo esc_url($this->requestUrl) ?>" data-parsley-validate method="post">
						<input type="hidden" name="controller" value="calendarista_season"/>
						<input type="hidden" name="id" value="<?php echo $this->selectedSeasonId ?>"/>
						<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>"/>
						<input type="hidden" name="projectName" value="<?php echo esc_attr($this->projectName) ?>"/>
						<input type="hidden" name="availabilityName" value="<?php echo esc_attr($this->availabilityName) ?>"/>
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<select name="availabilityId" id="availabilityId" data-parsley-required="true" class="calendarista_parsley_validated">
											<option value=""><?php esc_html_e('Select an availability', 'calendarista'); ?></option>
											<?php foreach($this->availabilities as $availability):?>
											<option value="<?php echo esc_attr($availability->id) ?>" <?php echo $availability->id === $this->availabilityId ? 'selected=selected' : '';?>><?php echo esc_html($availability->name) ?></option>
											<?php endforeach;?>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="start"><?php esc_html_e('Season start', 'calendarista') ?></label></div>
										<input id="start" 
											name="start" 
											type="text" 
											class="medium-text enable-readonly-input" 
											data-parsley-required="true"
											data-parsley-errors-messages-disabled="true"
											readonly
											value="<?php echo $this->getStartDate() ?>" />
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="end"><?php esc_html_e('Season end', 'calendarista') ?></label></div>
										<input id="end" 
											name="end" 
											type="text" 
											class="medium-text enable-readonly-input" 
											data-parsley-required="true"
											data-parsley-errors-messages-disabled="true"
											readonly
											value="<?php echo $this->getEndDate() ?>" />
									</td>
								</tr>
								<?php if($this->project->paymentsMode !== -1 && in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_COST)):?>
								<tr>
									<td>
										<div><label for="fixedCost"><?php esc_html_e('Cost', 'calendarista') ?></label></div>
										<input id="fixedCost" 
											name="fixedCost" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											data-parsley-min="0"
											data-parsley-pattern="^\d+(\.\d{1,2})?$"
											data-parsley-errors-container="#cost_error_container"
											placeholder="0.00"
											value="<?php echo !$this->season['percentageBased'] ? $this->emptyStringIfZero((float)$this->season['cost']) : '' ?>" />
											<?php if($this->availabilityId && $this->availability->cost > 0): ?>
											&nbsp;|&nbsp;
											<input type="text" 
												id="variableCost" 
												name="variableCost" 
												class="small-text calendarista_parsley_validated" 
												data-parsley-pattern="^\d+(\.\d{1,2})?$"
												data-parsley-min="0.1"
												data-parsley-max="100"
												data-parsley-trigger="change" 
												data-parsley-errors-container="#cost_error_container"
												placeholder="0.00"
												value="<?php echo $this->season['percentageBased'] ? $this->emptyStringIfZero((float)$this->season['cost']) : '' ?>">%
											 <input type="radio"  
																	name="costMode" 
																	value="0"
																	<?php echo !(int)$this->season['costMode'] ? 'checked' : '' ?>>
											<?php esc_html_e('+Add', 'calendarista') ?>
											 <input type="radio"  
																	name="costMode" 
																	value="1"
																	<?php echo (int)$this->season['costMode'] ? 'checked' : '' ?>>
											<?php esc_html_e('-Deduct', 'calendarista') ?>
											<?php endif; ?>
										<div id="cost_error_container"></div>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<div><label for="repeatWeekdayList"><?php esc_html_e('Apply season to individual days', 'calendarista') ?></label></div>
										<fieldset>
											<legend class="screen-reader-text"><span><?php esc_html_e('Individual days', 'calendarista')?></span></legend>
											<ul class="inline-block-checkbox">
												<?php foreach($this->weekdays as $key=>$value):?>
												<li>
													<label for="<?php echo $value ?>">
														<input 
															id="<?php echo $value ?>"
															name="repeatWeekdayList[]" 
															value="<?php echo $key ?>"
															type="checkbox"  
															<?php echo $this->repeatWeekdayChecked((int)$key); ?> />
															<?php echo $value ?>
													</label>
												</li>
												<?php endforeach;?>
											</ul>
										</fieldset>
									</td>
								</tr>
								<?php if(in_array($this->project->calendarMode, array(
															Calendarista_CalendarMode::MULTI_DATE_RANGE
															, Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE
															, Calendarista_CalendarMode::CHANGEOVER
															, Calendarista_CalendarMode::MULTI_DATE
															, Calendarista_CalendarMode::MULTI_DATE_AND_TIME))):?>
								<tr>
									<td>
										<div><label for="bookingDaysMinimum"><?php esc_html_e('Min days', 'calendarista') ?></label></div>
										<input id="bookingDaysMinimum" 
											name="bookingDaysMinimum" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											value="<?php echo $this->season['bookingDaysMinimum'] ?>" />&nbsp;<?php esc_html_e('day(s)', 'calendarista') ?>
											<p class="description"><?php esc_html_e('Minimum  days bookable at a time. Leave blank or 0 if not applicable.', 'calendarista') ?></p>
									</td>
								</tr>
								<tr>
									<td>
										<div><label for="bookingDaysMaximum"><?php esc_html_e('Max days', 'calendarista') ?></label></div>
										<input id="bookingDaysMaximum" 
											name="bookingDaysMaximum" 
											type="text" 
											class="small-text" 
											data-parsley-trigger="change focusout"
											placeholder="0"
											data-parsley-type="digits"
											value="<?php echo $this->season['bookingDaysMaximum'] ?>" />&nbsp;<?php esc_html_e('day(s)', 'calendarista') ?>
											<p class="description"><?php esc_html_e('Maximum days bookable at a time. Leave blank or 0 if not applicable.', 'calendarista') ?></p>
									</td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
						<div>
							<?php if(!$this->selectedSeasonId):?>
							<button type="submit" class="button button-primary" name="calendarista_create">
								<?php esc_html_e('Create', 'calendarista') ?>
							</button>
							<?php else: ?>
							<button type="submit" class="button button-primary" name="calendarista_new">
								<?php esc_html_e('New', 'calendarista') ?>
							</button>
							<button type="submit" class="button button-primary" name="calendarista_update">
								<?php esc_html_e('Update', 'calendarista') ?>
							</button>
							<button type="submit" class="button button-primary" name="calendarista_delete">
								<?php esc_html_e('Delete', 'calendarista') ?>
							</button>
							<?php endif; ?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="widget-liquid-left">
			<div id="widgets-left">
				<div class="wrap">
					<form action="<?php echo esc_url($this->requestUrl) ?>" data-parsley-validate method="post">
						<input type="hidden" name="controller" value="calendarista_season" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<?php $this->seasonList->display(); ?>
						<p>
							<button type="submit" class="button button-primary" name="calendarista_delete_many" disabled>
								<?php esc_html_e('Delete', 'calendarista') ?>
							</button>
						</p>
					</form>
				</div>
				<?php if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_PRICING_SCHEME)):?>
				<div class="widgets-holder-wrap">
					<div class="widgets-sortables ui-droppable ui-sortable">
						<div class="sidebar-name">
							<h3><?php esc_html_e('Pricing scheme x day', 'calendarista') ?></h3>
						</div>
						<?php if($this->selectedSeasonId): ?>
							<?php new Calendarista_PricingSchemeTemplate($this->availabilityId, $this->selectedSeasonId);?>
						<?php else: ?>
							<p class="description"><?php esc_html_e('Create or select an existing season to setup a pricing scheme.', 'calendarista') ?></p>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
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
			calendarista.season = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.season.prototype.init = function(options){
				var context = this
					, $start
					, $end;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.dateFormat = 'yy-mm-dd';
				this.requestUrl = options['requestUrl'];
				this.$seasonListCheckboxes = $('.calendarista-season-list input[type="checkbox"]');
				this.$deleteAllCheckbox = $('input[name="deleteall"]');
				this.$deleteAllButton = $('button[name="calendarista_delete_many"]');
				this.$availabilityId = $('select[name="availabilityId"]');
				this.$repeatWeekdayList = $('input[name="repeatWeekdayList[]"]');
				this.checkedAllDelegate = calendarista.createDelegate(this, this.checkedAll);
				this.$seasonListCheckboxes.on('change', this.checkedAllDelegate);
				this.$fixedCost = $('input[name="fixedCost"]');
				this.$variableCost = $('input[name="variableCost"]');
				this.$costMode = $('input[name="costMode"]');
				this.fixedCostInputStateDelegate = calendarista.createDelegate(this, this.fixedCostInputState);
				this.$availabilityId.on('change', function(e){
					var selectedIndex = parseInt($(this).val(), 10)
						, requestUrl = context.requestUrl.replace(/&?availabilityId=([^&]$|[^&]*)/i, "");
					if(selectedIndex){
						requestUrl += '&availabilityId=' + selectedIndex;
					}
					window.location.href = requestUrl;
				});
				$start = $('#start').datepicker({
					dateFormat: this.dateFormat,
					changeMonth: true,
					changeYear: true,
					showButtonPanel: true,
					closeText: '<?php echo $this->decodeString(__('Select', 'calendarista')) ?>',
				}).on('change', function() {
				  $end.datepicker('option', 'minDate', context.getDate(this));
				});
				$end = $('#end').datepicker({
					dateFormat: this.dateFormat,
					changeMonth: true,
					changeYear: true,
					showButtonPanel: true,
					closeText: '<?php esc_html_e('Select', 'calendarista') ?>',
				}).on('change', function() {
				  $start.datepicker('option', 'maxDate', context.getDate(this));
				});
				this.$deleteAllCheckbox.on('change', function(){
					if(this.checked){
						context.$seasonListCheckboxes.prop('checked', true);
					}else{
						context.$seasonListCheckboxes.prop('checked', false);
					}
					context.checkedAll();
				});
				this.$fixedCost.on('keyup', this.fixedCostInputStateDelegate);
				this.$variableCost.on('keyup', this.fixedCostInputStateDelegate);
				this.$fixedCost.on('blur', this.fixedCostInputStateDelegate);
				this.$variableCost.on('blur', this.fixedCostInputStateDelegate);
				this.fixedCostInputState();
				this.$repeatWeekdayList.on('change', function(){
					var $weekdays = $('input[name="repeatWeekdayList[]"]:checked');
					if($weekdays.length === 7){
						$weekdays.prop('checked', false);
					}
				});
			};
			calendarista.season.prototype.fixedCostInputState = function(){
				this.$variableCost.prop('disabled', false);
				this.$fixedCost.prop('disabled', false);
				this.$costMode.prop('disabled', false);
				if(this.$fixedCost.val()){
					this.$variableCost.val('');
					this.$variableCost.prop('disabled', true);
					return;
				}
				if(this.$variableCost.val()){
					this.$fixedCost.prop('disabled', true);
				}
			};
			calendarista.season.prototype.getDate = function(element){
				var date;
				try {
					date = $.datepicker.parseDate(this.dateFormat, element.value);
				}catch(error){
					date = null;
				}
				return date;
			};
			calendarista.season.prototype.checkedAll = function(){
				var hasChecked = this.$seasonListCheckboxes.is(':checked');
				if(hasChecked){
					this.$deleteAllButton.prop('disabled', false);
				}else{
					this.$deleteAllButton.prop('disabled', true);
				}
			};
		window['calendarista'] = calendarista;
	})(window['jQuery'], window['calendarista_wp_ajax']);
	new calendarista.season({
		'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		, 'selectedTabIndex': <?php echo $this->selectedTab ?>
	});
	</script>
		<?php 
	}
}