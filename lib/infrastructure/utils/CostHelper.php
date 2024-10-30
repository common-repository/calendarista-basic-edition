<?php
class Calendarista_CostHelper{
	public $availability;
	public $availabilityId;
	public $projectId;
	public $availableDate;
	public $endDate;
	public $startTime;
	public $startTimeslot;
	public $endTime;
	public $endTimeslot;
	public $returnCost;
	public $distance;
	public $seats = 0;
	public $optionalCost = 0;
	public $multiDateSelection;
	public $enableDateRemoveButton = true;
	public $hasCustomCharge = false;
	public $customChargeValue = 0;
	//subTotalAmount contains cost before discount,tax,deposit etc
	public $subTotalAmount;
	public $totalAmount;
	public $totalReturnCost;
	public $totalCostBeforeDiscount;
	public $totalAmountBeforeTax;
	public $totalAmountBeforeDeposit;
	public $dateRange;
	public $dateRangePlainText;
	public $project;
	public $optionals;
	public $couponHelper;
	public $tax;
	public $taxAmount = 0;
	public $roundTrip;
	public $nights = 0;
	public $numberOfDays = 0;
	//balance is the deposit remainder, remaining amount due
	public $balance;
	public $timezone;
	public $fromPlaceId;
	public $toPlaceId;
	public $optionalsHelper;
	public $baseCost;
	public $selectedPeriodCost;
	public $selectedDateList = array();
	public $pricingSchemeList = array();
	public $pricingSchemeBySeasonList = array();
	public $costBySeasonList = array();
	public $availabilities;
	public $availabilityNames = array();
	public $hasMultipleAvailabilities = false;
	public $hours;
	public $totalHours;
	public $minutes;
	public $totalMinutes;
	public $days;
	public $extendsNextDay = false;
	protected $_baseCost;
	protected $ignoreSeats = false;
	protected $timeslotHelper;
	protected $generalSetting;
	protected $stringResources;
	protected $map;
	protected $enableCostByDistance;
	protected $enableCostByLocation1;
	protected $enableCostByLocation2;
	protected $fromPlace;
	protected $toPlace;
	protected $placeAggregateCost;
	protected $args;
	protected $hasReturnDate = true;
	protected $hasEndTime = false;
	protected $seasons;
	protected $numberOfSlots = 0;
	protected $dynamicFields = array();
	protected $flag1;
	public function __construct($args){
		$this->args = $args;
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		if(array_key_exists('availableDate', $args)){
			$this->availableDate = (string)$args['availableDate'];
		}
		if(array_key_exists('endDate', $args)){
			$this->endDate = (string)$args['endDate'];
		}
		if(array_key_exists('startTime', $args) && $args['startTime']){
			$this->startTime = (string)$args['startTime'];
		}
		if(array_key_exists('endTime', $args) && $args['endTime']){
			$this->endTime = (string)$args['endTime'];
			$this->hasEndTime = true;
		}else{
			$this->endTime = $this->startTime;
		}
		if(array_key_exists('multiDateSelection', $args)){
			$this->multiDateSelection = (string)$args['multiDateSelection'];
		}
		if(array_key_exists('seats', $args)){
			$this->seats = (int)$args['seats'];
		}
		if(array_key_exists('availabilityId', $args)){
			$this->availabilityId = (int)$args['availabilityId'];
		}
		if(array_key_exists('availabilities', $args)){
			$availabilities = is_array($args['availabilities']) ? $args['availabilities'] : explode(',', $args['availabilities']);
			$this->availabilities = isset($args['availabilities']) && $args['availabilities'] ? array_map('intval', $availabilities) : null;
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('distance', $args)){
			$this->distance = (float)$args['distance'];
		}
		if(array_key_exists('fromPlaceId', $args)){
			$this->fromPlaceId = (int)$args['fromPlaceId'];
		}
		if(array_key_exists('toPlaceId', $args)){
			$this->toPlaceId = (int)$args['toPlaceId'];
		}
		//client timezone
		if(array_key_exists('timezone', $args)){
			$this->timezone = $args['timezone'];
		}
		if(array_key_exists('ignoreSeats', $args)){
			$this->ignoreSeats = $args['ignoreSeats'];
		}
		if(array_key_exists('dynamicFields', $args)){
			$this->dynamicFields = $args['dynamicFields'];
		}
		if(array_key_exists('enableDateRemoveButton', $args)){
			$this->enableDateRemoveButton = $args['enableDateRemoveButton'];
		}
		if(array_key_exists('tax', $args)){
			$this->generalSetting->tax = $args['tax'];
		}
		if(array_key_exists('taxMode', $args) && is_numeric($args['taxMode'])){
			$this->generalSetting->taxMode = (int)$args['taxMode'];
		}
		if(!$this->availableDate){
			$this->availableDate = $this->endDate;
		}
		if(!$this->endDate){
			$this->endDate = $this->availableDate;
			$this->hasReturnDate = false;
		}
		$args['hasReturnDate'] = $this->hasReturnDate;
		$this->project = Calendarista_ProjectHelper::getProject($this->projectId);
		if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_RETURN) && $this->hasReturnDate){
			$this->roundTrip = true;
		}
		$timeslotRepo = new Calendarista_TimeslotRepository();
		$this->startTimeslot = new Calendarista_Timeslot(array('timeslot'=>isset($args['startTimeslot']) ? $args['startTimeslot'] : ''));
		$this->endTimeslot = new Calendarista_Timeslot(array('timeslot'=>isset($args['endTimeslot']) ? $args['endTimeslot'] : ''));
		if($this->startTime){
			$this->startTimeslot = $timeslotRepo->read($this->startTime);
		}
		if($this->endTime){
			$this->endTimeslot = $timeslotRepo->read($this->endTime);
		}
		$mapRepo = new Calendarista_MapRepository();
		$this->map = $mapRepo->readByProject($this->projectId);
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		$this->enableCostByDistance = ($this->map && ($this->map->costMode === Calendarista_CostMode::DISTANCE  && $this->distance));
		$this->enableCostByLocation1 = ($this->map && in_array($this->map->costMode, array(Calendarista_CostMode::DEPARTURE_AND_DESTINATION)));
		$this->enableCostByLocation2 = ($this->map && in_array($this->map->costMode, array(Calendarista_CostMode::DEPARTURE_ONLY)));
		if($this->enableCostByLocation2){
			$repo = new Calendarista_PlaceRepository();
			if($this->fromPlaceId){
				$this->fromPlace = $repo->read($this->fromPlaceId);
			}
			if($this->toPlaceId){
				$this->toPlace = $repo->read($this->toPlaceId);
			}
		}else if($this->enableCostByLocation1){
			$repo = new Calendarista_PlaceAggregateCostRepository();
			$this->placeAggregateCost = $repo->readByLocation($this->fromPlaceId, $this->toPlaceId);
		}
		$repo = new Calendarista_AvailabilityRepository();
		$availabilityList = array($this->availabilityId);
		if($this->availabilities && count($this->availabilities) > 0){
			$availabilityList = array_merge($availabilityList, $this->availabilities);
			$this->hasMultipleAvailabilities = true;
		}
		$this->flag1 = true;
		$idList = array();
		foreach($availabilityList as $availabilityId){
			$this->availability = $repo->read($availabilityId);
			if(!$this->availability){
				continue;
			}
			if(!in_array($this->availability->id, $idList)){
				array_push($this->availabilityNames, $this->availability->name);
				array_push($idList, $this->availability->id);
			}
			$this->timeslotHelper = new Calendarista_TimeslotHelper(array(
				'availability'=>$this->availability
				, 'project'=>$this->project
			));
			if($this->availability->fullDay){
				$seasonRepo = new Calendarista_SeasonRepository();
				$this->seasons = $seasonRepo->readByAvailability($this->availability->id);
			}
			if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY && $this->availability->daysInPackage > 1){
				$availabilityHelper = new Calendarista_AvailabilityHelper(array(
					'projectId'=>$this->projectId
					, 'availabilityId'=>$this->availabilityId
				));
				$result = $availabilityHelper->getNextOccurrenceByPackage(strtotime($this->availableDate));
				if($result){
					$this->endDate = date(CALENDARISTA_DATEFORMAT, $result['endDate']);
				}
			}
			$this->_baseCost = $this->getBaseCost($this->availableDate, $this->endDate);
			$this->optionalsHelper = new Calendarista_OptionalsHelper(array_merge($args, 
				array('seats'=>$this->getSeats($this->seats)
					, 'availabilityList'=>$availabilityList)));
			$this->couponHelper = new Calendarista_CouponHelper($args);
			$this->init();
			$this->flag1 = false;
		}
	}
	public function calculateRepeatPeriod(){
		$supportsTimeslots = in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS) 
										&& $this->availability->timeMode === Calendarista_TimeMode::LITERAL;
		$checkoutHelper = new Calendarista_CheckoutHelper(array('viewState'=>$this->args));
		$repeatDateList = $checkoutHelper->getRepeatDateList();
		$result = 0;
		if(!$repeatDateList || count($repeatDateList) === 0){
			$repeatDateList = Calendarista_CheckoutHelper::getRepeatAppointmentDates($this->availability, $this->args);
		}
		if(count($repeatDateList) > 0){
			foreach($repeatDateList as $repeatDate){
				$startDate = date(CALENDARISTA_DATEFORMAT, strtotime($repeatDate['startDate']));
				$endDate = date(CALENDARISTA_DATEFORMAT, strtotime($repeatDate['endDate']));
				$cost = $this->getBaseCost($startDate, $endDate);
				if(!$supportsTimeslots){
					$cost = Calendarista_SeasonHelper::getCost(strtotime($startDate), $cost, $this->seasons);
					array_push($this->selectedDateList, array('date'=>$startDate, 'time'=>null, 'cost'=>$cost, 'datetime'=>$this->formatDateTime($startDate)));
				}else{
					$datetime = $this->formatDateTime($startDate, $this->startTimeslot->timeslot);
					if($this->endTimeslot->id !== -1 && $this->startTimeslot->id !== $this->endTimeslot->id){
						$datetime .= ' - ' . $this->endTimeslot->timeslot;
					}
					array_push($this->selectedDateList, array('date'=>$startDate, 'time'=>$this->startTimeslot->timeslot, 'cost'=>$cost, 'datetime'=>$datetime));
				}
				$result += $cost;
				
			}
		}
		return $result;
	}
	public function getSeats($seats){
		if(!$this->availability->selectableSeats){
			$guestCount = 0;
			if(is_array($this->dynamicFields)){
				foreach($this->dynamicFields as $field){
					if($field['limitBySeat'] || $field['byOptional']){
						$guestCount += (int)$field['value'];
					}
				}
			}
			if($guestCount){
				$seats = $guestCount;
			}
		}
		return $seats;
	}
	protected function init(){
		$this->tax = $this->generalSetting->tax;
		$this->totalAmount += $this->getTotalCost();
		$this->totalReturnCost += $this->calculateCostByLocationOrDistance($this->returnCost);
		$this->dateRange = $this->getDateRange();
		$this->dateRangePlainText = $this->getDateRange(true/*plainText*/);
		$this->optionals = $this->optionalsHelper->summary();
	}
	protected function calculateTotalCost(){
		if(!$this->availability){
			return null;
		}
		$startDate = strtotime($this->availableDate);
		$endDate = strtotime($this->endDate);
		$cost = $this->_baseCost;
		if($this->availability->cost > 0){
			$cost = Calendarista_SeasonHelper::getCost($startDate, $this->_baseCost, $this->seasons);
		}
		$regularCost = $cost;
		$selectedPeriodCost = $cost;
		$this->returnCost = (!$this->hasReturnDate || $this->availability->cost == 0) ? 0 : ($this->availability->returnCost > 0 ? $this->availability->returnCost : $cost);
		if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_DATE)){
			$cost = 0;
			$selectedPeriodCost = 0;
			if($this->multiDateSelection){
				$this->numberOfDays += 0;
				$this->numberOfSlots += 0;
				$selectedDates = explode(';', $this->multiDateSelection);
				foreach($selectedDates as $dt){
					$sdt;
					$this->numberOfDays += 1;
					$this->numberOfSlots += 1;
					if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE){
						$sdt = strtotime($dt);
						if($sdt){
							$regularCost = $this->getFullDayCharge($sdt, $sdt);
							$_cost = Calendarista_SeasonHelper::getCost($sdt, $regularCost, $this->seasons);
							$fallsWithinSeason = Calendarista_SeasonHelper::fallsWithinSeason($sdt, $this->seasons);
							if($fallsWithinSeason){
								$this->hasCustomCharge = false;
							}
							$originalCost = Calendarista_SeasonHelper::getCost($sdt, $this->availability->cost, $this->seasons);
							array_push($this->selectedDateList, array('date'=>$dt, 'time'=>null, 'cost'=>$originalCost, 'datetime'=>$this->formatDateTime($dt)));
							$cost += $_cost;
						}
					}else{
						$pair = explode(':', $dt);
						$tm = $pair[1];
						$timeslotRepo = new Calendarista_TimeslotRepository();
						$slot = $timeslotRepo->read((int)$tm);
						if(!$slot){
							continue;
						}
						array_push($this->selectedDateList, array('date'=>$pair[0], 'time'=>$slot->timeslot, 'cost'=>$slot->cost, 'datetime'=>$this->formatDateTime($pair[0], $slot->timeslot)));
						$cost += $slot->cost;
					}
				}
				$cost = $this->applyPricingScheme(array(
					'startDate'=>strtotime($this->availableDate)
					, 'endDate'=>strtotime($this->endDate)
					, 'cost'=>$cost
					, 'regularCost'=>$this->availability->cost
					, 'numberOfDays'=>$this->numberOfDays
				));
				$selectedPeriodCost = $cost;
				$cost = $this->calculateCostByLocationOrDistance($cost);
				if($this->seats && !$this->ignoreSeats){
					$cost = ($cost * $this->seats);
					$selectedPeriodCost = ($selectedPeriodCost * $this->seats);
				}
			}
		}else if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_RETURN)){
			//ToDO: add a returnCost setting to seasons in case returncost is supported. missing implementation.
			$cost = $this->calculateCostByLocationOrDistance($cost);
			if($this->seats && !$this->ignoreSeats){
				$cost = ($cost * $this->seats);
			}
			$this->returnCost =  (!$this->hasReturnDate || $this->availability->cost == 0) ? 0 : ($this->availability->returnCost > 0 ? $this->availability->returnCost : $cost);
			if($this->returnCost){
				$cost += $this->returnCost;
			}
			$selectedPeriodCost = $cost;
		}else{
			if($this->project->calendarMode === Calendarista_CalendarMode::PACKAGE || ($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY && $this->availability->daysInPackage > 1)){
				$cost = $this->availability->cost;
				$this->numberOfDays = $this->availability->daysInPackage;
				if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY){
					$cost = Calendarista_SeasonHelper::getCost($startDate, $this->availability->cost, $this->seasons);
				}
			}else if($this->availability->fullDay){
				$this->numberOfDays = 0;
				$supportsTimeslots = in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS) 
										&& $this->availability->timeMode === Calendarista_TimeMode::LITERAL;
				if($supportsTimeslots){
					//some full days, support timeslots
					$startDate = strtotime($this->availableDate . ' ' . $this->startTimeslot->timeslot);
					$endDate = strtotime($this->endDate . ' ' . $this->endTimeslot->timeslot);
				}
				$checkoutDate = date(CALENDARISTA_DATEFORMAT, $endDate);
				if($this->availability->cost > 0){
					$cost = 0;
				}
				while ($supportsTimeslots ? $startDate < $endDate : $startDate <= $endDate){
					$currentDate = date(CALENDARISTA_DATEFORMAT, $startDate);
					if($this->project->calendarMode === Calendarista_CalendarMode::CHANGEOVER){
						if($currentDate == $checkoutDate){
							break;
						}
						$this->nights += 1;
						$this->numberOfDays += 1;
					}else if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_RANGE){
						if($this->availability->dayCountMode === 1/*Difference*/ && $currentDate == $checkoutDate){
							break;
						}
						$this->nights += 1;
						$this->numberOfDays += 1;
					}else if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE){
						$this->numberOfDays += 1;
					}
					if($this->availability->cost > 0){
						$regularCost = $this->getFullDayCharge($startDate, $endDate);
						$cost += Calendarista_SeasonHelper::getCost($startDate, $regularCost, $this->seasons);
					}
					$startDate = strtotime('+1 day', $startDate);
				}
			}
			$cost = $this->applyPricingScheme(array(
				'startDate'=>strtotime($this->availableDate)
				, 'endDate'=>strtotime($this->endDate)
				, 'cost'=>$cost
				, 'regularCost'=>$this->availability->cost
				, 'numberOfDays'=>$this->numberOfDays
			));
			$selectedPeriodCost = $cost;
			$cost = $this->calculateCostByLocationOrDistance($cost);
			if($this->seats && !$this->ignoreSeats){
				$cost = ($cost * $this->seats);
				$selectedPeriodCost = ($selectedPeriodCost * $this->seats);
			}
		}
		$this->selectedPeriodCost += $selectedPeriodCost;
		$cost += $this->applyDynamicFields();
		if($this->flag1){
			$this->optionalsHelper->setNumberOfSlots($this->numberOfSlots);
			$this->optionalsHelper->setNumberOfDays($this->numberOfDays);
			$optionalCost = $this->optionalsHelper->getTotalCost();
			$cost += $optionalCost;
			$this->optionalCost += $optionalCost;
		}
		$cost += $this->calculateRepeatPeriod();
		$this->subTotalAmount += self::getTotalAmountAfterTax($cost, $this->project, $this->generalSetting);
		$cost = apply_filters('calendarista_total_amount', $cost, $this->args);
		$this->totalCostBeforeDiscount += $this->applyTax($cost);
		$costAfterDiscount = $this->couponHelper->applyDiscount($cost);
		if($costAfterDiscount >= 0){
			$cost = $costAfterDiscount;
		}
		$this->totalAmountBeforeTax = $cost;
		$cost = $this->applyTax($cost);
		$this->totalAmountBeforeDeposit = $cost < 0 ? 0 : $cost;
		$cost = $this->applyDeposit($cost);
		//optionals support negative values so ensure we don't go into negative.
		if($cost < 0){
			$cost = 0;
		}
		return $cost;
	}
	//ToDO: breakdown in pricing scheme/seasons
	protected function applyPricingScheme($args){
		$startDate = $args['startDate'];
		//$endDate = $args['endDate'];
		$cost = $args['cost'];
		$regularCost = $args['regularCost'];
		$numberOfDays = $args['numberOfDays'];
		if($this->availability->cost == 0 && $this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE){
			//cost is timeslot based, do nothing.
			return $cost;
		}
		if(!in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_SEQUENCE) && $this->project->calendarMode !== Calendarista_CalendarMode::MULTI_DATE){
			return $cost;
		}
		$repo = new Calendarista_PricingSchemeRepository();
		$tempNumberOfDays = 0;
		$result = 0;
		$seasons = array();
		$flag = false;
		if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE){
			if($this->multiDateSelection){
				$selectedDates = explode(';', $this->multiDateSelection);
				foreach($selectedDates as $dt){
					$sdt = strtotime($dt);
					$season = Calendarista_SeasonHelper::getSeason($sdt, $this->seasons);
					if($season){ 
						if(!isset($seasons[$season['id']])){
							$seasons[$season['id']] = array('season'=>$season, 'numberOfDays'=>1);
						}else{
							$seasons[$season['id']]['numberOfDays']++;
						}
					}else{
						$tempNumberOfDays++;
					}
				}
			}
		} else {
			$sd = $startDate;
			for($i = 0; $i < $numberOfDays; $i++){
				$season = Calendarista_SeasonHelper::getSeason($sd, $this->seasons);
				if($season){ 
					if(!isset($seasons[$season['id']])){
						$seasons[$season['id']] = array('season'=>$season, 'numberOfDays'=>1);
					}else{
						$seasons[$season['id']]['numberOfDays']++;
					}
				}else{
					$tempNumberOfDays++;
				}
				$sd = strtotime('+1 day', $sd);
			}
		}
		foreach($seasons as $value){
			$flag = false;
			$season = $value['season'];
			$seasonStart = strtotime($season['start']);
			$seasonEnd = strtotime($season['end']);
			$pricingSchemes = $repo->readBySeasonId($season['id']);
			foreach($pricingSchemes as $scheme){
				if($scheme['days'] == $value['numberOfDays']){
					foreach($this->selectedDateList as $key=>$item){
						$date = strtotime($item['date']);
						if($date >= $seasonStart && $date <= $seasonEnd){
							$this->selectedDateList[$key]['cost'] = $scheme['cost'];
						}
					}
					array_push($this->pricingSchemeBySeasonList, array(
						'startDate'=>$this->formatDateTime($season['start'])
						, 'endDate'=>$this->formatDateTime($season['end'])
						, 'numberOfDays'=>$scheme['days']
						, 'cost'=>$scheme['cost']
					));
					$result += $scheme['cost'];
					$flag = true;
					break;
				}
			}
			if(!$flag){
				//no pricing scheme found, use cost in season.
				$seasonCost = Calendarista_SeasonHelper::getCost($startDate, $regularCost, null, $season);
				if($seasonCost != $regularCost){
					foreach($this->selectedDateList as $key=>$item){
						$date = strtotime($item['date']);
						if($date >= $seasonStart && $date <= $seasonEnd){
							$this->selectedDateList[$key]['cost'] = $seasonCost;
						}
					}
					array_push($this->costBySeasonList, array(
						'startDate'=>$this->formatDateTime($season['start'])
						, 'endDate'=>$this->formatDateTime($season['end'])
						, 'numberOfDays'=>$value['numberOfDays']
						, 'cost'=>$seasonCost * $value['numberOfDays']
					));
				}
				$result += $seasonCost * $value['numberOfDays'];
			}
		}
		$flag = false;
		if($result === 0 || $tempNumberOfDays > 0){
			//we do not fall within a season OR some of our days fall outside a season so
			//get pricing scheme from availability instead
			$pricingSchemes = $repo->readByAvailabilityId($this->availability->id, true);
			$daysCount = $tempNumberOfDays > 0 ? $tempNumberOfDays : $numberOfDays;
			foreach($pricingSchemes as $scheme){
				if($scheme['days'] == $daysCount){
					$flag = true;
					array_push($this->pricingSchemeList, array(
						'numberOfDays'=>$scheme['days']
						, 'cost'=>$scheme['cost']
					));
					$result += $scheme['cost'];
					break;
				}
			}
		}
		if($result > 0){
			if(!$flag && $tempNumberOfDays > 0){
				//we did find a season and applied the cost in $result
				//so now calculate the cost of the remaining selected days that do not fall within a season
				$result += $regularCost * $tempNumberOfDays;
			}
			return $result;
		}
		return $cost;
	}
	public function applyDynamicFields(){
		$dynamicFieldPricingRepo = new Calendarista_DynamicFieldPricingRepository();
		$cost = 0;
		if(is_array($this->dynamicFields) && count($this->dynamicFields) > 0){
			foreach($this->dynamicFields as $item){
				$flag = false;
				$priceList = $dynamicFieldPricingRepo->readByDynamicFieldId($item['id']);
				if(!is_array($priceList)){
					continue;
				}
				foreach($priceList as $price){
					if($item['value'] == $price['fieldValue']){
						$flag = true;
						$cost += $this->getCostBySlotsAndDays($price['cost'], $item['fixedCost']);
					}
				}
				if(!$flag && $item['cost']){
					$cost += $this->getCostBySlotsAndDays($item['value'] * $item['cost'], $item['fixedCost']);
				}
			}
		}
		return $cost;
	}
	protected function getCostBySlotsAndDays($cost, $hasFixedCost = false){
		if($hasFixedCost){
			return $cost;
		}
		if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTIPLY_BY_TIMESLOT_SELECTION)){
			return $cost * ($this->numberOfSlots ? $this->numberOfSlots : 1);
		}else{
			return $cost * ($this->numberOfDays ? $this->numberOfDays : 1);
		}
	}
	public function getTotalAmountAfterTaxBeforeDiscount(){
		return $this->applyTax($this->totalCostBeforeDiscount);
	}
	public function getTotalAmount($upfrontPayment = false){
		$fullAmountDiscount = $this->availability->fullAmountDiscount;
		if($fullAmountDiscount && $this->seats > 1){
			$fullAmountDiscount = $fullAmountDiscount * $this->seats;
		}
		if($upfrontPayment && ($fullAmountDiscount > 0 && 
			$this->totalAmountBeforeDeposit > $fullAmountDiscount)){
			$discountedValue = $this->totalAmountBeforeDeposit - $fullAmountDiscount;
			return $discountedValue;
		}
		return $this->totalAmount;
	}
	protected function getFullDayCharge($start = null, $end = null){
		$startDate = !$start ? strtotime($this->availableDate) : $start;
		$endDate = !$end ? strtotime($this->endDate) : $end;
		if($this->project->calendarMode !== Calendarista_CalendarMode::CHANGEOVER){
			$startDate = strtotime('-1 day', $startDate);
		}
		$result = $this->availability->cost;
		//we need the difference including the current date so subtract 1
		$numberDays = ($endDate - $startDate)  / (60 * 60 * 24);
		if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTI_DATE)){
			$numberDays = $this->numberOfDays;
		}
		if($this->availability->customChargeDays > 1 && $numberDays >= $this->availability->customChargeDays){
			$this->hasCustomCharge = true;
			if($this->availability->customChargeMode === 1/*Flat fee*/){
				if($this->availability->customCharge < 0){
					$result = $this->availability->cost - abs($this->availability->customCharge);
				} else{
					$result = $this->availability->cost + $this->availability->customCharge;
				}
			}
			else if($this->availability->customChargeMode === 0/*Percentage*/){
				$result = (($this->availability->cost / 100) * abs($this->availability->customCharge));
				if($this->availability->customCharge < 0){
					$result = $this->availability->cost - $result;
				}else{
					$result = $this->availability->cost + $result;
				}
			}
		}
		return $result;
	}
	public static function getTotalAmountAfterTax($totalAmount, $project, $generalSetting){
		//show inclusive cost if enabled
		if($generalSetting->tax && $generalSetting->taxMode === 0/*exclusive*/){
			$totalAmount += ($totalAmount / 100) * $generalSetting->tax;
		}
		return $totalAmount;
	}
	protected function applyTax($totalAmount){
		if($this->tax && $this->generalSetting->taxMode === 0/*exclusive*/){
			$this->taxAmount = ($totalAmount / 100) * $this->tax;
			$totalAmount += $this->taxAmount;
		}
		return $totalAmount;
	}
	protected function applyDeposit($totalAmount){
		$depositAmount = $this->availability->deposit;//fixed deposit
		if($this->availability->deposit){
			if($this->availability->depositMode === 0/*percentage based*/){
				$depositAmount = ($totalAmount / 100) * $this->availability->deposit;
			}else if($this->seats > 1 && $this->availability->depositMode === 2/*flat deposit x seats*/ ){
				$depositAmount = $depositAmount * $this->seats;
			}
			//deposit amount cannot be greater than the total amount payable.
			//typical when setting fixed deposit amount.
			if($totalAmount >= $depositAmount){
				$this->balance = $totalAmount - $depositAmount;
				return $depositAmount;
			}
		}
		return $totalAmount;
	}
	protected function getBaseCost($availableDate, $endDate){
		if($this->availability->fullDay && ($this->availability->cost > 0 || !in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_FULL_DAY_AND_TIMESLOT_COST))){
				return $this->availability->cost;
		}
		$result = 0;
		if($this->endTimeslot->id !== -1 && $this->startTimeslot->id !== $this->endTimeslot->id){
			$date1 = new Calendarista_DateTime(sprintf('%s %s', $availableDate, $this->startTimeslot->timeslot));
			$date2 = new Calendarista_DateTime(sprintf('%s %s', $endDate, $this->endTimeslot->timeslot));
			$diff = $date1->diff($date2);
			$this->hours = $diff->h;
			$this->totalHours = $this->hours + ($diff->days*24);
			$this->minutes = $diff->i;
			$this->totalMinutes = $this->minutes + ($this->totalHours * 60);
			$this->days = $diff->d;
			if($availableDate != $endDate && $this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE){
				$this->extendsNextDay = true;
			}
		}
		if(($this->availability->cost == 0 && $this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE_AND_TIME_RANGE) ||
			($availableDate != $endDate && $this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE)){
			//cost is timeslot based
			$startDate = strtotime($availableDate);
			$endDate = strtotime($endDate);
			$_startDate = strtotime($availableDate);
			$_endDate = strtotime($endDate);
			$start = new DateTime(date('H:i', strtotime($this->startTimeslot->timeslot)));
			$end = new DateTime(date('H:i', strtotime($this->endTimeslot->timeslot)));
			while($startDate <= $endDate){
				$timeslots = Calendarista_TimeslotHelper::filterTimeslots(date(CALENDARISTA_DATEFORMAT, $startDate), $this->timeslotHelper->timeslots);
				foreach($timeslots as $slot){
					$ts = new DateTime(date('H:i', strtotime($slot->timeslot)));
					if($startDate == $_startDate && $ts <= $start){
						continue;
					}
					$this->numberOfSlots++;
					$result += $slot->cost;
					if($startDate == $endDate && $ts >= $end){
						break;
					}
				}
				$startDate = strtotime('+1 day', $startDate);
			}
			return $result;
		}
		$timeslots = Calendarista_TimeslotHelper::filterTimeslots($availableDate, $this->timeslotHelper->timeslots);
		if($this->project->calendarMode === Calendarista_CalendarMode::ROUND_TRIP_WITH_TIME){
			$result = $this->startTimeslot->cost;
			if($this->hasReturnDate){
				$result += $this->endTimeslot->cost;
			}
			return $result;
		}
		$start = new DateTime(date('H:i', strtotime($this->startTimeslot->timeslot)));
		$end = new DateTime(date('H:i', strtotime($this->endTimeslot->timeslot)));
		$timeslotsByRange = array();
		$selectedTimeslots = array();
		foreach($timeslots as $slot){
			$ts = new DateTime(date('H:i', strtotime($slot->timeslot)));
			if($ts >= $start && $ts <= $end){
				if($this->project->calendarMode === Calendarista_CalendarMode::SINGLE_DAY_AND_TIME_RANGE){
					//allows to avoid the second slot in between the start/end date 
					//since it is the same single slot so avoid additional cost
					array_push($timeslotsByRange, $slot);
				}
				if(in_array($this->project->calendarMode, Calendarista_CalendarMode::$SUPPORTS_MULTIPLY_BY_TIMESLOT_SELECTION)){
					$this->numberOfSlots++;
				}
				$result += $slot->cost;
				array_push($selectedTimeslots, $slot);
			}
		}
		if(in_array($this->project->calendarMode, array(Calendarista_CalendarMode::SINGLE_DAY_AND_TIME)) &&
				($this->availability->minimumTimeslotCharge > 0 && count($timeslotsByRange) <= $this->availability->maxTimeslots)){
			return $this->availability->minimumTimeslotCharge;
		}
		$len = count($timeslotsByRange);
		if($len > 0){
			$result = 0;
			$this->numberOfSlots = 0;
			for($i = 0; $i < $len; $i++){
				if(($i + 1) === $len){
					array_pop($selectedTimeslots);
					//step behind, because it's a time range
					break;
				} 
				$result += $timeslotsByRange[$i]->cost;
				$this->numberOfSlots++;
			}
		}
		$result = apply_filters('calendarista_timeslot_amount', $result, $selectedTimeslots, $this->project, $this->availability);
		return $result;
	}
	protected function calculateCostByLocationOrDistance($cost){
		$result = 0;
		if($this->enableCostByDistance && $this->map->unitCost > 0){
			if($this->map->minimumUnitValue > 0){
				if($this->distance > $this->map->minimumUnitValue){
					//fixed cost
					$result =  $this->map->minimumUnitCost;
				}else{
					//cost x distance
					$result = $this->distance * $this->map->unitCost;
				}
			}else{
				//definitely cost by distance
				$result = $this->distance * $this->map->unitCost;
			}
		}else if($this->enableCostByLocation1 && $this->placeAggregateCost){
			$result = (float)$this->placeAggregateCost->cost;
		}else if($this->enableCostByLocation2){
			if($this->fromPlace){
				$result = $this->fromPlace->cost;
			}
			if($this->toPlace){
				$result += $this->toPlace->cost;
			}
		}
		if($this->roundTrip){
			//it's a round trip, so charge for return as well
			$result = $result * 2;
		}
		return $cost + $result;
	}
	protected function getDateRange($plainText = false){
		$availableDate = Calendarista_TimeHelper::formatDate($this->availableDate);
		$endDate = Calendarista_TimeHelper::formatDate($this->endDate);
		$startTime = Calendarista_TimeHelper::formatTime(array(
			'timezone'=>null//$this->timezone
			, 'serverTimezone'=>null//$this->availability->timezone
			, 'time'=>$this->startTimeslot->timeslot
		));
		$endTime = Calendarista_TimeHelper::formatTime(array(
			'timezone'=>null//$this->timezone
			, 'serverTimezone'=>null//$this->availability->timezone
			, 'time'=>$this->endTimeslot->timeslot
		));
		$x = trim($availableDate . ' ' . $startTime);
		$y = trim($endDate . ' ' . $endTime);
		switch($this->project->calendarMode){
			case 0://SINGLE_DAY
				if($this->availability->daysInPackage > 1){
					return sprintf($this->stringResources['BOOKING_PACKAGE_SUMMARY'], $this->availability->name, $availableDate, $endDate);
				}
				return sprintf($this->stringResources['BOOKING_DATE_SUMMARY'], $availableDate);
			case 1://SINGLE_DAY_AND_TIME
			case 9://SINGLE_DAY_AND_TIME_WITH_PADDING 
				if($this->hasEndTime && $startTime != $endTime){
					//means multiple time slot selection enabled
					$x .= sprintf(' - %s', $endTime);
				}
				return sprintf($this->stringResources['BOOKING_DATE_SUMMARY'], $x);
			case 2://SINGLE_DAY_AND_TIME_RANGE
				if($this->availableDate != $this->endDate){
					return sprintf($this->stringResources['BOOKING_DATETIME_RANGE_SUMMARY'], $x, $y);
				}
				return sprintf($this->stringResources['BOOKING_DATETIME_RANGE_SUMMARY'], $x, $endTime);
			case 3://MULTI_DATE_RANGE
				return sprintf($this->stringResources['BOOKING_DATE_RANGE_SUMMARY'], $availableDate, $endDate);
			case 4://MULTI_DATE_AND_TIME_RANGE
				return sprintf($this->stringResources['BOOKING_DATE_RANGE_SUMMARY'], $x, $y);
			case 5://CHANGEOVER 
				return sprintf($this->stringResources['BOOKING_DATE_RANGE_PARTIAL_CHARGE_SUMMARY'], $availableDate, $endDate);
			case 6://PACKAGE
				if($this->availability->daysInPackage > 1){
					return sprintf($this->stringResources['BOOKING_PACKAGE_SUMMARY'], $this->availability->name, $availableDate, $endDate);
				}
				return sprintf($this->stringResources['BOOKING_DATE_SUMMARY'], $availableDate);
			case 7://ROUND_TRIP
				//new label saying: booking a hasReturnDate trip?
				if(!$this->hasReturnDate){
					return sprintf($this->stringResources['BOOKING_ONEWAY_TRIP_DATE_SUMMARY'], $availableDate);
				}
				return sprintf($this->stringResources['BOOKING_ROUND_TRIP_DATE_SUMMARY'], $availableDate, $endDate);
			case 8://ROUND_TRIP_WITH_TIME
				if(!$this->hasReturnDate){
					return sprintf($this->stringResources['BOOKING_ONEWAY_TRIP_DATE_SUMMARY'], $x);
				}
				return sprintf($this->stringResources['BOOKING_ROUND_TRIP_DATE_SUMMARY'], $x, $y);
			case 11://MULTI_DATE
			case 12://MULTI_DATE_AND_TIME
				$dateList1 = array();
				$dateList2 = array();
				if($this->multiDateSelection){
					$selectedDates = explode(';', $this->multiDateSelection);
					$button = '<span class="badge text-bg-secondary calendarista-multi-date-badge">%1$s';
					if($this->enableDateRemoveButton){
						$button .= '&nbsp;&nbsp;<i class="fa fa-close calendarista-multi-date-btn" data-calendarista-value="%2$s"></i>';
					}
					$button .= '<span class="sr-only">%1$s</span></span>';
					foreach($selectedDates as $dt){
						if($this->project->calendarMode === Calendarista_CalendarMode::MULTI_DATE){
							array_push($dateList1, sprintf($button, Calendarista_TimeHelper::formatDate($dt), $dt));
							array_push($dateList2, Calendarista_TimeHelper::formatDate($dt));
						}else{
							$pair = explode(':', $dt);
							$timeslotRepo = new Calendarista_TimeslotRepository();
							$slot = $timeslotRepo->read((int)$pair[1]);
							if(!$slot){
								continue;
							}
							$time = Calendarista_TimeHelper::formatTime(array(
								'timezone'=>null
								, 'serverTimezone'=>null
								, 'time'=>$slot->timeslot
							));
							array_push($dateList1, sprintf($button, sprintf('%s %s', Calendarista_TimeHelper::formatDate($pair[0]), $time), $dt));
							array_push($dateList2, sprintf('%s %s', Calendarista_TimeHelper::formatDate($pair[0]), $time));
						}
					}
				}
				if(count($dateList1) === 0){
					return null;
				}
				if($plainText){
					return implode(', ', $dateList2);
				}
				return $this->stringResources['BOOKING_MULTI_DATE_SUMMARY'] . '<div class="calendarista-multi-date-list">' . implode('&nbsp;', $dateList1) . '</div>';
		}
	}
	public function getBaseCostSummary(){
		$availableDate = Calendarista_TimeHelper::formatDate($this->availableDate);
		$endDate = Calendarista_TimeHelper::formatDate($this->endDate);
		$startTime = Calendarista_TimeHelper::formatTime(array(
			'timezone'=>null//$this->timezone
			, 'serverTimezone'=>null//$this->availability->timezone
			, 'time'=>$this->startTimeslot->timeslot
		));
		$endTime = Calendarista_TimeHelper::formatTime(array(
			'timezone'=>null//$this->timezone
			, 'serverTimezone'=>null//$this->availability->timezone
			, 'time'=>$this->endTimeslot->timeslot
		));
		$x = trim($availableDate . ' ' . $startTime);
		$y = trim($endDate . ' ' . $endTime);
		$result = null;
		switch($this->project->calendarMode){
			case 0://SINGLE_DAY
			case 6://PACKAGE
				if($this->availability->daysInPackage > 1){
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_DATE_RANGE_LABEL'], $availableDate, $endDate);
				}else if(count($this->selectedDateList) === 0){
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_LABEL'], $availableDate);
				}else{
					$result = $availableDate;
				}
				break;
			case 1://SINGLE_DAY_AND_TIME
			case 9://SINGLE_DAY_AND_TIME_WITH_PADDING 
				if($this->hasEndTime && $startTime != $endTime){
					//means multiple time slot selection enabled
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_DATE_RANGE_LABEL'], $x, $endTime);
				}else if(count($this->selectedDateList) === 0){
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_SINGLE_SLOT_LABEL'], $x);
				}else{
					$result = $x;
				}
				break;
			case 2://SINGLE_DAY_AND_TIME_RANGE
				if($this->availableDate != $this->endDate){
					return sprintf($this->stringResources['BOOKING_BASE_COST_DATE_RANGE_LABEL'], $x, $y);
				}
				$result = sprintf($this->stringResources['BOOKING_BASE_COST_DATE_RANGE_LABEL'], $x, $endTime);
			break;
			case 4://MULTI_DATE_AND_TIME_RANGE
				$result = sprintf($this->stringResources['BOOKING_BASE_COST_DATE_RANGE_LABEL'], $x, $y);
				break;
			case 5://CHANGEOVER
			case 3://MULTI_DATE_RANGE
				$result = sprintf($this->stringResources['BOOKING_BASE_COST_DATE_RANGE_LABEL'], $availableDate, $endDate);
				break;
			case 7://ROUND_TRIP
				//new label saying: booking a hasReturnDate trip?
				if(!$this->hasReturnDate){
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_ONEWAY_TRIP_LABEL'], $availableDate);
				}else{
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_ROUND_TRIP_LABEL'], $availableDate, $endDate);
				}
				break;
			case 8://ROUND_TRIP_WITH_TIME
				if(!$this->hasReturnDate){
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_ONEWAY_TRIP_LABEL'], $x);
				}else{
					$result = sprintf($this->stringResources['BOOKING_BASE_COST_ROUND_TRIP_LABEL'], $x, $y);
				}
				break;
			case 11://MULTI_DATE
			case 12://MULTI_DATE_AND_TIME
			break;
		}
		if($result && $this->seats > 1){
			$result .= ' ' . sprintf($this->stringResources['BOOKING_BASE_COST_X_SEATS_LABEL'], $this->seats);
		}
		return $result;
	}
	protected function formatDateTime($dt, $tm = null){
		$result =  Calendarista_TimeHelper::formatDate($dt);
		if($tm){
			$result .= ' ' . Calendarista_TimeHelper::formatTime(array(
				'timezone'=>null
				, 'serverTimezone'=>null
				, 'time'=>$tm
			));
		}
		return $result;
	}
	public function getSelectedDateList(){
		$template = '<tr class="calendarista-base-cost">
						<td class="calendarista-typography--caption1">%s</td>
						<td class="calendarista-typography--caption1 text-end">%s</td>
					</tr>';
		$result = array();
		if(count($this->selectedDateList) === 0){
			return null;
		}
		foreach($this->selectedDateList as $item){
			array_push($result, sprintf($template, $item['datetime'], Calendarista_MoneyHelper::toLongCurrency($item['cost'])));
		}
		return implode('', $result);
	}
	public function getTotalCost(){
		return $this->calculateTotalCost();
	}
	public function depositToString(){
		return $this->availability->depositToString();
	}
	public function getTotalTime(){
		$result = null;
		if($this->totalHours){
			$result = sprintf($this->stringResources['BOOKING_HOURS_LABEL'], $this->totalHours);
		}
		if($this->minutes){
			if($result){
				$result .= ' ';
			}
			$result .= sprintf($this->stringResources['BOOKING_MINUTES_LABEL'], $this->minutes);
		}
		return $result;
	}
}
?>