<?php
class Calendarista_OptionalsHelper{
	public $optionalGroups;
	public $optionals;
	public $seats = 0;
	public $ignoreSeats = false;
	public $projectId;
	public $project;
	public $stringResources;
	public $generalSetting;
	public $optionalIncrementals;
	protected $hasReturnDate;
	protected $numberOfSlots;
	protected $numberOfDays;
	protected $availabilityList = array();
	protected $availabilityCount = 1;
	public function __construct($args){
		$optionals = array();
		$optionalIncrementals = array();
		if(array_key_exists('optionals', $args)){
			$optionals = array_map('intval', explode(',', $args['optionals']));
		}
		if(array_key_exists('optional_incremental', $args)){
			$optionalIncrementals = explode(',', $args['optional_incremental']);
			foreach($optionalIncrementals as $oi){
				$item = explode(':', $oi);
				array_push($optionals, (int)$item[0]);
			}
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('seats', $args)){
			$this->seats = (int)$args['seats'];
		}
		if(array_key_exists('ignoreSeats', $args)){
			$this->ignoreSeats = $args['ignoreSeats'];
		}
		if(array_key_exists('hasReturnDate', $args)){
			$this->hasReturnDate = $args['hasReturnDate'];
		}
		if(array_key_exists('numberOfSlots', $args)){
			$this->numberOfSlots = $args['numberOfSlots'];
		}
		if(array_key_exists('numberOfDays', $args)){
			$this->numberOfDays = $args['numberOfDays'];
		}
		if(array_key_exists('availabilityList', $args)){
			$this->availabilityList = $args['availabilityList'];
		}
		$this->project = Calendarista_ProjectHelper::getProject($this->projectId);
		$this->availabilityCount = !$this->project->optionalByService && $this->availabilityList ? count($this->availabilityList) : 1;
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->optionals = new Calendarista_Optionals();
		$this->optionalGroups = new Calendarista_OptionalGroups();
		if(count($optionals) === 0){
			return;
		}
		$repo = new Calendarista_OptionalRepository();
		$this->optionals = $repo->readAllByIdList($optionals);
		$optionalGroupIdList = array();
		foreach($this->optionals as $value){
			if(!in_array($value->groupId, $optionalGroupIdList)){
				array_push($optionalGroupIdList, $value->groupId);
			}
			foreach($optionalIncrementals as $oi){
				$item = explode(':', $oi);
				if($value->id === (int)$item[0]){
					$value->incrementValue = (int)$item[1];
				}
			}
		}
		$optionalGroupRepo = new Calendarista_OptionalGroupRepository();
		$this->optionalGroups = $optionalGroupRepo->readAllByIdList($optionalGroupIdList);
	}
	public function setNumberOfSlots($value){
		$this->numberOfSlots = $value;
	}
	public function setNumberOfDays($value){
		$this->numberOfDays = $value;
	}
	public function getTotalCost(){
		$result = 0;
		foreach($this->optionals as $optional){
			$cost = ($optional->doubleCostIfReturn && $this->hasReturnDate) ? $optional->cost * 2 : $optional->cost;
			if($optional->incrementValue && $optional->incrementValue > 1){
				$cost = $cost * $optional->incrementValue;
			}
			$group = $this->getGroup($optional->groupId);
			$cost = $this->calculateCost(array(
				'cost'=>$cost
				, 'multiplyMode'=>$group->multiply
				, 'numberOfSlots'=>$this->numberOfSlots
				, 'numberOfDays'=>$this->numberOfDays
			));
			$result += $cost;
		}
		return $result;
	}
	protected function getGroup($groupId){
		foreach($this->optionalGroups as $group){
			if($group->id === $groupId){
				return $group;
			}
		}
		return null;
	}
	public function calculateCost($args){
		$cost = $args['cost'];
		if($cost != 0){
			if(in_array($args['multiplyMode'], array(1/*BY_DAYS_TIMESLOTS*/, 3/*BY_DAYS_TIMESLOTS_AND_SEATS*/))){
				if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTIPLY_BY_TIMESLOT_SELECTION)){
					$cost = $cost * ($args['numberOfSlots'] ? $args['numberOfSlots'] : 1);
				}else{
					$cost = $cost * ($args['numberOfDays'] ? $args['numberOfDays'] : 1);
				}
			}
			if($this->seats > 0 && (in_array($args['multiplyMode'], array(2/*BY_SEATS*/, 3/*BY_DAYS_TIMESLOTS_AND_SEATS*/)) && !$this->ignoreSeats)){
				$cost = $cost * $this->seats;
			}
		}
		if($this->availabilityCount > 1){
			return $cost * $this->availabilityCount;
		}
		return $cost;
	}
	public function summary($longCurrency = true){
		$result = array();
		$complete = array();
		if($this->optionals->count() === 0){
			return $result;
		}
		$result = array();
		foreach($this->optionalGroups as $group){
			foreach($this->optionals as $optional){
				if($group->id !== $optional->groupId || in_array($optional->groupId, $complete)){
					continue;
				}
				array_push($result, '<tr class="calendarista-optionals">');
				$cost = $optional->cost;
				if($this->availabilityCount > 1){
					$cost = $cost * $this->availabilityCount;
				}
				if($cost != 0){
					if(in_array($group->multiply, array(1/*BY_DAYS_OR_TIMELSOTS*/, 3/*BY_DAYS_OR_TIMESLOTS_OR_SEATS*/))){
						if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTIPLY_BY_TIMESLOT_SELECTION)){
							$cost = $this->numberOfSlots * $cost;
						}else{
							$cost = $this->numberOfDays * $cost;
						}
					}
					if($optional->incrementValue > 0){
						$cost = $cost * $optional->incrementValue;
					}
				}
				$name = Calendarista_StringResourceHelper::decodeString($optional->name);
				if($optional->incrementValue > 0){
					$name = sprintf('%s - <i class="calendarista-quantity">%s</i>', $name, sprintf($this->stringResources['BOOKING_OPTIONAL_QUANTITY_LABEL'], $optional->incrementValue));
				}
				$name = sprintf('%s —%s', Calendarista_StringResourceHelper::decodeString($group->name), $name);
				if($group->multiply === 1/*BY_DAYS_OR_TIMESLOTS*/){
					$totalDaysOrSlots = 0;
					$label = null;
					if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTIPLY_BY_TIMESLOT_SELECTION)){
						$label = $this->stringResources['BOOKING_OPTIONAL_MULTIPLY_BY_SLOTS_LABEL'];
						$totalDaysOrSlots = $this->numberOfSlots;
					} else{
						$label = $this->stringResources['BOOKING_OPTIONAL_MULTIPLY_BY_DAY_LABEL'];
						$totalDaysOrSlots = $this->numberOfDays;
					}
					array_push($result, sprintf('<td class="calendarista-typography--caption1">%s</td>', sprintf($label, $name, $totalDaysOrSlots)));
				}else if($group->multiply === 2/*BY_SEATS*/){
					$seats = $this->seats > 0 ? $this->seats : 1;
					array_push($result, sprintf('<td class="calendarista-typography--caption1">%s</td>', sprintf($this->stringResources['BOOKING_OPTIONAL_MULTIPLY_BY_SEAT_LABEL'], $name, $seats)));
				}else{
					array_push($result, sprintf('<td class="calendarista-typography--caption1">%s</td>', $name));
				}
				if($this->seats > 0 && in_array($group->multiply, array(2/*BY_SEATS*/, 3/*BY_DAYS_OR_TIMESLOTS_OR_SEATS*/))){
					$cost = $cost * $this->seats;
				}
				if($longCurrency){
					array_push($result, sprintf('<td class="calendarista-typography--caption1 text-end">%s</td>',  Calendarista_MoneyHelper::toLongCurrency($cost)));
				}else{
					array_push($result, sprintf('<td class="calendarista-typography--caption1 text-end">%s</td>',  function_exists('wc_price') ? wc_price($cost) : Calendarista_MoneyHelper::toShortCurrency($cost)));
				}
				array_push($result, '</tr>');
			}
			array_push($complete, $group->id);
		}
		return $result;
	}
	public function getSimpleNameValueRow(){
		$row = '<tr class="cart-optional">
            <td><span class="calendarista-optional-group-label">%s —<i class="calendarista-optional-item">%s</i></span></td>
            <td data-title="optional-item">%s</td>
        </tr>';
		$result = array();
		foreach($this->optionalGroups as $group){
			foreach($this->optionals as $optional){
				if($group->id !== $optional->groupId){
					continue;
				}
				$cost = '';
				if($optional->cost){
					$cost = $this->optionalItemCost($optional, $group);
				}
				$name = Calendarista_StringResourceHelper::decodeString($optional->name);
				if($optional->incrementValue > 0){
					$name = sprintf('%s - <i class="calendarista-quantity">%s</i>', $name, sprintf($this->stringResources['BOOKING_OPTIONAL_QUANTITY_LABEL'], $optional->incrementValue));
				}
				array_push($result, sprintf(
					$row
					, Calendarista_StringResourceHelper::decodeString($group->name)
					, $name
					, $cost
				));
			}
		}
		return implode('', $result);
	}
	public function optionalItemCost($optional, $group){
		if($optional->cost > 0){
			$cost = $this->calculateCost(array(
				'cost'=>$optional->cost
				, 'multiplyMode'=>$group->multiply
				, 'numberOfSlots'=>$this->numberOfSlots
				, 'numberOfDays'=>$this->numberOfDays
			));
			if(class_exists('WooCommerce') && function_exists('wc_price')){
				$cost = wc_price($cost);
			}
			$cost = Calendarista_MoneyHelper::formatCurrencySymbol(sprintf('%g', $cost), true);
			return $cost;
		}
		return 0;
	}
	public function formatOptionalItemCaption($optional, $group){
		$result = Calendarista_StringResourceHelper::decodeString($optional->name);
		if($optional->incrementValue > 0){
			$result = sprintf('%s - <i class="calendarista-quantity">%s</i>', $name, sprintf($this->stringResources['BOOKING_OPTIONAL_QUANTITY_LABEL'], $optional->incrementValue));
		}
		if($optional->quantity > 0){
			$quantity = ($optional->bookedQuantity > $optional->quantity) ? 0 : ($optional->quantity - $optional->bookedQuantity);
			$result .= ' —' . ($quantity > 0 ? sprintf($this->stringResources['OPTIONAL_QUANTITY_SUMMARY'], $quantity) : $this->stringResources['OPTIONAL_QUANTITY_EXHAUSTED']);
		}
		$cost = $this->calculateCost(array(
			'cost'=>$optional->cost
			, 'multiplyMode'=>$group->multiply
			, 'numberOfSlots'=>$this->numberOfSlots
			, 'numberOfDays'=>$this->numberOfDays
		));
		if($optional->cost > 0){
			$result .= ' - ' . Calendarista_MoneyHelper::formatCurrencySymbol(sprintf('%g', $cost), true);
		}else if($optional->cost < 0){
			$result .= sprintf(' - %s %s', Calendarista_MoneyHelper::formatCurrencySymbol(sprintf('%g', abs($cost)), true), __('**discount', 'calendarista'));
		}
		return $result;
	}
}
?>