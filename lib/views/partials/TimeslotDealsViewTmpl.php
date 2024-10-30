<?php
class Calendarista_TimeslotDealsViewTmpl extends Calendarista_TemplateBase{
	public $container;
	public $hasDeals;
	public $deals;
	public function __construct($container){
		parent::__construct();
		$this->container = $container;
		foreach($this->container->timeslots as $timeslot){
			if($timeslot->deal){
				$this->hasDeals = true;
			}
		}
		$this->deals = $this->getDeals();
		$this->hasDeals = is_array($this->deals);
		$this->render();
	}
	public function getDeals(){
		/*
			Morning       5 am to 12 pm (noon)
			Afternoon     12 pm to 5 pm
			Evening       5 pm to 9 pm
			Night         9 pm to 4 am
		*/
		$result = null;
		foreach($this->container->timeslots as $timeslot){
			if($timeslot->deal){
				if(!$result){
					$result = array(
						'morning'=>array('title'=>$this->stringResources['DEALS_MORNING'], 'averageCost'=>0, 'items'=>array())
						, 'afternoon'=>array('title'=>$this->stringResources['DEALS_AFTERNOON'], 'averageCost'=>0, 'items'=>array())
						, 'evening'=>array('title'=>$this->stringResources['DEALS_EVENING'], 'averageCost'=>0, 'items'=>array())
						, 'night'=>array('title'=>$this->stringResources['DEALS_NIGHT'], 'averageCost'=>0, 'items'=>array())
					);
				}
				$seats = $timeslot->seats > 0 ? $timeslot->getSeatCount() : -1; 
				$soldout = (!($seats === -1 || $seats > 0));
				$timeOfDay = (int)date('G', strtotime($timeslot->timeslot));
				if ($timeOfDay >= 4 && $timeOfDay < 12) {
					$averageCost = $result['morning']['averageCost'];
					if(($timeslot->cost > 0 && !$soldout) && (!$averageCost || $averageCost > $timeslot->cost)){
						$result['morning']['averageCost'] = $timeslot->cost;
					}
					array_push($result['morning']['items'], $timeslot);
				} else if ($timeOfDay >= 12 && $timeOfDay < 17) {
					$averageCost = $result['afternoon']['averageCost'];
					if(($timeslot->cost > 0 && !$soldout) && (!$averageCost || $averageCost > $timeslot->cost)){
						$result['afternoon']['averageCost'] = $timeslot->cost;
					}
					array_push($result['afternoon']['items'], $timeslot);
				} else if ($timeOfDay >= 17 && $timeOfDay < 19) {
					$averageCost = $result['evening']['averageCost'];
					if(($timeslot->cost > 0 && !$soldout) && (!$averageCost || $averageCost > $timeslot->cost)){
						$result['evening']['averageCost'] = $timeslot->cost;
					}
					array_push($result['evening']['items'], $timeslot);
				} else if ($timeOfDay >= 19 || $timeOfDay < 4) {
					$averageCost = $result['night']['averageCost'];
					if(($timeslot->cost > 0 && !$soldout) && (!$averageCost || $averageCost > $timeslot->cost)){
						$result['night']['averageCost'] = $timeslot->cost;
					}
					array_push($result['night']['items'], $timeslot);
				}
			}
		}
		return $result;
	}
	public function render(){
	?>
	<div class="<?php echo esc_attr($this->container->placeholderClassName) ?>">
		<div class="form-group calendarista-row-single">
			<div>
				<?php if(!$this->container->slotType):?>
				<input type="hidden" name="availabilityId" value="<?php echo $this->container->availabilityId ?>"/>
				<?php endif; ?>
				<input type="hidden" name="startTime" value="<?php echo $this->container->selectedTime == -1 ? '': $this->container->selectedTime ?>" data-parsley-required="true" class="calendarista_parsley_validated" data-parsley-error-message="<?php echo esc_html($this->stringResources['DEALS_TIMESLOT_ERROR'])?>" />
				<div>
					<div><strong><?php echo esc_html($this->stringResources['DEALS_AT']) ?></strong>&nbsp;<i class="calendarista-deal-availabilityname"><?php echo esc_html($this->container->availability->name) ?></i></div>
					<?php foreach($this->container->timeslots as $key=>$timeslot): ?>
					<?php if($timeslot->deal){continue;} ?>
					<?php 
						$soldout = false;
						if(!($timeslot->seats === 0 && $this->container->availability->seats === 0)){
							$seats = $timeslot->seats > 0 ? $timeslot->getSeatCount() : $this->container->availability->seats - $this->container->dailyPool;
							$soldout = $seats > 0 ? false : true;
							if($this->container->appointment === 1/*edit mode*/){
								$soldout = false;
							} else if($this->container->appointment !== 1 && $timeslot->outOfStock){
								$soldout = true;
								$seats = 0;
							}
						}
					?>
					<?php if(!$soldout): ?> <a href="#" <?php else: ?> <div<?php endif; ?> class="calendarista-timeslot-deals">
						<div class="calendarista-timeslot-deals-tile text-center <?php echo $this->container->selectedValue($timeslot, 'calendarista-timeslot-deals-selected');?> calendarista-typography--subtitle1 <?php echo $seats > 0 || $seats === -1 ? '' : 'calendarista-soldout' ?>">
							<time data-calendarista-value="<?php echo $timeslot->id?>"  data-calendarista-time="<?php echo $this->container->formatTime($timeslot, true) ?>"><div><?php echo $this->container->formatTime($timeslot, true) ?></div></time>
							<?php if($timeslot->cost <= 0 && $seats === -1): ?>
							<p class="calendarista-timeslot-deals-price text-center"><?php echo esc_html($this->stringResources['DEALS_TIMESLOT_AVAILABLE']) ?></p>
							<?php elseif($seats === -1 || $seats > 0): ?>
							<p class="calendarista-timeslot-deals-price text-center"><sup><?php if($timeslot->cost > 0): ?><?php echo Calendarista_MoneyHelper::getCurrencySymbol() ?></sup><?php echo floatval($timeslot->cost) ?><?php endif; ?><span class="calendarista-typography--subtitle2"> <?php echo $seats === -1 ? '' : sprintf($this->stringResources['DEALS_TIMESLOT_SEAT_REMAINING'], $seats); ?></span></p>
							<?php else: ?>
							<p class="calendarista-timeslot-deals-soldout text-center"><?php echo esc_html($this->stringResources['DEALS_SOLDOUT']) ?></p>
							<?php endif; ?>
						</div>
					<?php if(!$soldout): ?> 
					</a>
					<?php else: ?> 
					</div>
					<?php endif; ?>
					<?php endforeach; ?>
					<div class="clearfix"></div>
				</div>
				<?php if ($this->hasDeals): ?>
					<?php foreach($this->deals as $deal): ?>
						<?php if(count($deal['items']) === 0){continue;} ?>
						<div>
							<div><strong><?php echo $deal['title'] ?></strong><?php if($deal['averageCost']): ?> &nbsp;<i class="calendarista-deal-availabilityname"><?php echo sprintf($this->stringResources['DEALS_AVERAGE_COST'], Calendarista_MoneyHelper::toShortCurrency($deal['averageCost']))  ?></i><?php endif; ?></div>
							<div>
								<?php foreach($deal['items'] as $key=>$timeslot): ?>
									<?php 
										$soldout = false;
										if(!($timeslot->seats === 0 && $this->container->availability->seats === 0)){
											$seats = $timeslot->seats > 0 ? $timeslot->getSeatCount() : $this->container->availability->seats - $this->container->dailyPool;
											$soldout = $seats > 0 ? false : true;
											if($this->container->appointment === 1/*edit mode*/){
												$soldout = false;
											} else if($this->container->appointment !== 1 && $timeslot->outOfStock){
												$soldout = true;
												$seats = 0;
											}
										}
									?>
									<?php $seats = $timeslot->seats > 0 ? $timeslot->getSeatCount() : -1; ?>
									<?php if(!$soldout): ?> <a href="#" <?php else: ?> <div<?php endif; ?> class="calendarista-timeslot-deals">
										<div class="calendarista-timeslot-deals-tile text-center <?php echo $this->container->selectedValue($timeslot, 'calendarista-timeslot-deals-selected');?> calendarista-typography--subtitle1 <?php echo $seats > 0 || $seats === -1 ? '' : 'calendarista-soldout' ?>">
											<time data-calendarista-value="<?php echo $timeslot->id?>"  data-calendarista-time="<?php echo $this->container->formatTime($timeslot, true) ?>"><div><?php echo $this->container->formatTime($timeslot, true) ?></div></time>
											<?php if($timeslot->cost <= 0 && $seats === -1): ?>
											<p class="calendarista-timeslot-deals-price text-center"><?php echo esc_html($this->stringResources['DEALS_TIMESLOT_AVAILABLE']) ?></p>
											<?php elseif($seats === -1 || $seats > 0): ?>
											<p class="calendarista-timeslot-deals-price text-center"><sup><?php if($timeslot->cost > 0): ?><?php echo Calendarista_MoneyHelper::getCurrencySymbol() ?></sup><?php echo floatval($timeslot->cost) ?><?php endif; ?><span class="calendarista-typography--subtitle2"> <?php echo $seats === -1 ? '' : sprintf($this->stringResources['DEALS_TIMESLOT_SEAT_REMAINING'], $seats); ?></span></p>
											<?php else: ?>
											<p class="calendarista-timeslot-deals-soldout text-center"><?php echo esc_html($this->stringResources['DEALS_SOLDOUT']) ?></p>
											<?php endif; ?>
										</div>
									<?php if(!$soldout): ?> 
									</a>
									<?php else: ?> 
									</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
							<div class="clearfix"></div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
<?php
	}
}
