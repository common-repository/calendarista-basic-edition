<?php
class Calendarista_BookingCostSummaryTmpl extends Calendarista_TemplateBase{
	public $costHelper;
	public $userSummaryData;
	public $mustacheEngine;
	public $template;
	public $upfrontPayment;
	public $appointment;
	public function __construct($includeFullSummary = false, $enableDateRemoveButton = true){
		parent::__construct();
		if(!$enableDateRemoveButton){
			$this->viewState['enableDateRemoveButton'] = false;
		}
		$this->costHelper = new Calendarista_CostHelper($this->viewState);
		if($includeFullSummary){
			if((array_key_exists('email', $this->viewState) && $this->getViewStateValue('email')) && 
					(array_key_exists('name', $this->viewState) && $this->getViewStateValue('name'))){
				$this->userSummaryData = implode(', ', array($this->sanitize($this->getViewStateValue('name')), $this->getViewStateValue('email')));
			}
		}
		$styleRepository = new Calendarista_StyleRepository();
		$style = $styleRepository->readByProject($this->projectId);
		if(!$style){
			$style = new Calendarista_Style(array());
		}
		$this->template = $style->bookingSummaryTemplate;
		if(!class_exists('Mustache_Engine')){
			require_once CALENDARISTA_MUSTACHE . 'Autoloader.php';
			Mustache_Autoloader::register();
		}
		$this->mustacheEngine = new Mustache_Engine();
		$paymentsMode = $this->costHelper->project ? $this->costHelper->project->paymentsMode : -1;
		if(in_array($paymentsMode, array(1/*online mode*/, 2/*online and offline mode*/, 3/*woocommerce*/))){
			$stagingId = $this->getViewStateValue('stagingId');
			if($stagingId){
				$stagingRepo = new Calendarista_StagingRepository();
				$stagingRepo->update(array('id'=>$stagingId, 'viewState'=>$this->stateBag));
			}
		}	
		$this->upfrontPayment = $this->getViewStateValue('upfrontPayment') !== null ? boolval($this->getViewStateValue('upfrontPayment')) : false;
		$this->appointment = isset($_POST['appointment']) ? (int)$_POST['appointment'] : 0/*front-end*/;
		$this->render();
	}
	public static function getDynamicFields($items){
		$dynamicFieldPricingRepo = new Calendarista_DynamicFieldPricingRepository();
		if(is_array($items)){
			$result = array();
			foreach($items as $item){
				$flag = false;
				$priceList = $dynamicFieldPricingRepo->readByDynamicFieldId($item['id']);
				if(is_array($priceList) && count($priceList) > 0){
					foreach($priceList as $price){
						if($item['value'] == $price['fieldValue']){
							array_push($result, sprintf('<tr class="calendarista-dynamic-fields"><td class="calendarista-typography--caption1">%s —%s</td><td class="calendarista-typography--caption1 text-end">%s</td></tr>'
								, Calendarista_StringResourceHelper::decodeString($item['label'])
								, $item['value']
								, Calendarista_MoneyHelper::toLongCurrency($price['cost']
							)));
							$flag = true;
							break;
						}
					}
				}
				if(!$flag){
					if($item['cost']){
						array_push($result, sprintf('<tr class="calendarista-dynamic-fields"><td class="calendarista-typography--caption1">%s —%s</td><td class="calendarista-typography--caption1 text-end">%s</td></tr>'
								, Calendarista_StringResourceHelper::decodeString($item['label'])
								, $item['value']
								, Calendarista_MoneyHelper::toLongCurrency($item['value'] * $item['cost']
							)));
					}else{
						array_push($result, sprintf('<tr class="calendarista-dynamic-fields"><td colspan="2" class="calendarista-typography--caption1">%s —%s</td></tr>'
							, Calendarista_StringResourceHelper::decodeString($item['label'])
							, $item['value']));
					}
				}
			}
			return implode('', $result);
		}
		return null;
	}
	protected function getWaypoints(){
		$waypoints = $this->getViewStateValue('waypoints');
		if($waypoints){
			$result = array();
			if(count($waypoints) > 0){
				foreach($waypoints as $w){
					$waypoint = (array)$w;
					array_push($result, sprintf('<tr class="calendarista-waypoint"><td class="calendarista-typography--caption1">%s</td><td class="calendarista-typography--caption1 text-end">%s</td></tr>', $waypoint['address'], $this->stringResources['MAP_WAYPOINT_LABEL']));
				}
			}
			return implode('', $result);
		}
		return null;
	}
	public static function getCustomFormElements($seats, $formElements){
		if($formElements){
			$result = array();
			foreach($formElements as $formElement){
				if(trim($formElement['value']) == false){
					continue;
				}
				if($formElement['elementId'] === -1 && $formElement['guestIndex'] > ($seats - 1)){
					continue;
				}
				array_push($result, sprintf('<strong>%s</strong> —<i>%s</i>'
					, Calendarista_StringResourceHelper::decodeString($formElement['label'])
					, Calendarista_StringResourceHelper::decodeString($formElement['value'])));
			}
			return implode('<br>', $result);
		}
		return null;
	}
	public function getSeats($availability, $seats){
		if(!$availability->selectableSeats){
			$dynamicFields = $this->getViewStateValue('dynamicFields');
			$guestCount = 0;
			if(is_array($dynamicFields)){
				foreach($dynamicFields as $field){
					if($field['limitBySeat']){
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
	protected function listRepeatDates(){
		$availability = $this->costHelper->availability;
		$repeatPageSize = $this->costHelper->project->repeatPageSize;
		if($availability){
			$repeatDates = Calendarista_CheckoutHelper::getRepeatAppointmentDates($availability, $this->viewState);
			new Calendarista_RepeatItemsTmpl($repeatDates, $repeatPageSize);
		}
	}
	public function getTotalAmountBeforeDiscount(){
		$fullAmountDiscount = $this->costHelper->availability->fullAmountDiscount;
		if($fullAmountDiscount && $this->costHelper->seats > 1){
			$fullAmountDiscount = $fullAmountDiscount * $this->costHelper->seats;
		}
		if($fullAmountDiscount > 0 && 
			$this->costHelper->totalAmountBeforeDeposit > $fullAmountDiscount){
			$discountedValue = $this->costHelper->totalAmountBeforeDeposit - $fullAmountDiscount;
			return sprintf('<span class="calendarista-strikethrough">%s</span>&nbsp;&nbsp;<span class="calendarista-discounted-full-amount">%s</span>&nbsp;&nbsp;<span class="calendarista-discount-msg">%s</span>'
				, Calendarista_MoneyHelper::toShortCurrency($this->costHelper->totalAmountBeforeDeposit)
				, Calendarista_MoneyHelper::toShortCurrency($discountedValue)
				, sprintf($this->stringResources['PAYMENT_FULL_AMOUNT_DISCOUNT_LABEL'], Calendarista_MoneyHelper::toShortCurrency($fullAmountDiscount))
			);
		}
		return Calendarista_MoneyHelper::toShortCurrency($this->costHelper->totalAmountBeforeDeposit);
	}
	public function render(){
		$ignoreAddress = $this->getPostValue('ignoreAddress');
		$fromAddress = $ignoreAddress ? null : $this->getViewStateValue('fromAddress');
		$toAddress = $ignoreAddress ? null : $this->getViewStateValue('toAddress');
		//distance & unit type is also retrieved from $_POST when the current view is the BookingMapTmpl
		//so special treatment, need to cast manually
		$distance = (float)$this->getViewStateValue('distance');
		$unitType = (int)$this->getViewStateValue('unitType');
		$duration = (int)$this->getViewStateValue('duration');
		$bookingDate = $this->costHelper->dateRange;
		if(!$bookingDate){
			return;
		}
		$seats = $this->getSeats($this->costHelper->availability, $this->costHelper->seats);
		$waypoints =  $ignoreAddress ? null : $this->getWaypoints();
		$customFormElements = self::getCustomFormElements($seats, $this->getViewStateValue('formelements'));
		$timeUnitLabels = Calendarista_StringResources::getTimeUnitLabels($this->stringResources);
		$serviceName = $this->costHelper->project ? $this->costHelper->project->name : '';
		$availabilityName = $this->costHelper->availability ? $this->costHelper->availability->name : '';
		$nightStayLabel = $this->costHelper->nights > 1 ? 
				sprintf($this->stringResources['BOOKING_NIGHT_PLURAL_LABEL'], $this->costHelper->nights) :
				sprintf($this->stringResources['BOOKING_NIGHT_SINGULAR_LABEL'], $this->costHelper->nights);
		$dynamicFields = self::getDynamicFields($this->getViewStateValue('dynamicFields'));
		$deposit = $this->costHelper->depositToString();
		$hasDiscount = $this->costHelper->couponHelper->discountToString() !== null;
		$discount = $this->costHelper->couponHelper->discountToString();
		$discountValue = Calendarista_MoneyHelper::toLongCurrency($this->costHelper->couponHelper->discountValue);
		if($this->costHelper->couponHelper->discountMode === 1/*fixed*/ && $this->costHelper->totalCostBeforeDiscount < $this->costHelper->couponHelper->discount){
			$hasDiscount = false;
			$discount = 0;
			$discountValue = Calendarista_MoneyHelper::toLongCurrency(0);
		}
		$baseCostLabel = $this->costHelper->getBaseCostSummary();
		$personalize = apply_filters('calendarista_cost_summary', array('hasCustomCharge'=>false, 'customChargeLabel'=>null, 'customChargeValue'=>null), $this->viewState);
		if($personalize && !$personalize['hasCustomCharge']){
			$personalize['hasCustomCharge'] = $this->costHelper->hasCustomCharge;
			$personalize['customChargeValue'] = Calendarista_MoneyHelper::toLongCurrency($this->costHelper->availability->customCharge);
			if($this->costHelper->availability->customChargeMode === 0/*Percentage*/){
				$personalize['customChargeValue'] = $this->costHelper->availability->customCharge . '%';
			}
			$personalize['customChargeLabel'] = $this->stringResources['CUSTOM_CHARGE_LABEL'];
		}else if($personalize && $personalize['hasCustomCharge']){
			$personalize['customChargeValue'] = Calendarista_MoneyHelper::toLongCurrency($personalize['hasCustomCharge']);
		}
		echo $this->mustacheEngine->render($this->template, array(
			'booking_summary_label'=>$this->stringResources['BOOKING_SUMMARY_LABEL']
			, 'if_has_nights'=>$this->costHelper->nights > 0
			, 'nights_label'=>$nightStayLabel
			, 'service_name'=>Calendarista_StringResourceHelper::decodeString($serviceName)
			, 'availability_name'=>Calendarista_StringResourceHelper::decodeString($availabilityName)
			, 'booking_date'=>$bookingDate
			, 'if_has_seats'=>$seats > 1
			, 'seats_summary'=>sprintf($this->stringResources['BOOKING_SEATS_SUMMARY'], $seats)
			, 'if_has_from_address'=>isset($fromAddress)
			, 'from_address'=>$fromAddress
			, 'from_address_label'=>$this->stringResources['MAP_DEPARTURE_LABEL']
			, 'if_has_waypoints'=>isset($waypoints)
			, 'stops'=>$waypoints
			, 'if_has_to_address'=>isset($toAddress)
			, 'to_address'=>$toAddress
			, 'to_address_label'=> $this->stringResources['MAP_DESTINATION_LABEL']
			, 'if_has_distance'=>isset($distance) && $distance > 0
			, 'distance'=>Calendarista_MoneyHelper::toDouble($distance)
			, 'distance_label'=>$this->stringResources['MAP_DISTANCE_LABEL']
			, 'if_has_duration'=>isset($duration) && $duration > 0
			, 'duration'=>Calendarista_TimeHelper::secondsToTime($duration, $timeUnitLabels)
			, 'duration_label'=>$this->stringResources['MAP_DURATION_LABEL']
			, 'unitType'=>$unitType === 0 ? 'km' : 'miles'
			, 'if_has_optionals'=>count($this->costHelper->optionals) > 0
			, 'optionals'=>implode('', $this->costHelper->optionals)
			, 'if_has_customer_name_email'=>!empty($this->userSummaryData)
			, 'customer_name_email'=>$this->userSummaryData
			, 'if_has_custom_form_fields'=>strlen($customFormElements) > 0
			, 'custom_form_fields'=>$customFormElements
			, 'if_has_subtotal'=>$this->costHelper->subTotalAmount > 0 && $this->costHelper->subTotalAmount != $this->costHelper->totalAmount
			, 'subtotal_amount'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->subTotalAmount)
			, 'subtotal_amount_label'=>$this->stringResources['SUBTOTAL']
			, 'if_has_total_amount'=>($this->costHelper->totalAmount > 0 || ($this->costHelper->totalAmount == 0 && $this->costHelper->couponHelper->discount > 0))
			, 'total_amount'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->totalAmount)
			, 'total_amount_label'=>$this->stringResources['TOTAL']
			, 'if_has_discount'=>$hasDiscount
			, 'discount'=>$discount
			, 'discount_value'=>$discountValue
			, 'applied'=>$this->stringResources['APPLIED']
			, 'discount_label'=>$this->stringResources['DISCOUNT']
			, 'if_has_tax'=>$this->costHelper->tax > 0
			, 'tax_label'=>$this->stringResources['TAX_LABEL']
			, 'tax'=>$this->costHelper->tax
			, 'tax_amount'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->taxAmount)
			, 'total_amount_before_tax'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->totalAmountBeforeTax)
			, 'if_has_deposit'=>$deposit !== null
			, 'deposit_label'=>$this->stringResources['BOOKING_DEPOSIT_LABEL']
			, 'balance_label'=>$this->stringResources['BALANCE_LABEL']
			, 'balance'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->balance)
			, 'balance_pay_on_arrival'=>$this->stringResources['BALANCE']
			, 'base_cost_label'=>$baseCostLabel
			, 'if_has_base_cost'=>$baseCostLabel && $this->costHelper->selectedPeriodCost > 0
			, 'base_cost'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->selectedPeriodCost)
			, 'if_has_dynamic_fields'=>$dynamicFields !== null
			, 'dynamic_fields'=>$dynamicFields
			, 'if_has_selected_date_list'=>count($this->costHelper->selectedDateList) > 0
			, 'selected_date_list'=>$this->costHelper->getSelectedDateList()
			, 'if_has_custom_charge'=>$personalize['hasCustomCharge']
			, 'custom_charge_label'=>$personalize['customChargeLabel']
			, 'custom_charge_amount'=>$personalize['customChargeValue']
			, 'if_has_multiple_availability'=>$this->costHelper->hasMultipleAvailabilities
			, 'availability_list'=>sprintf($this->stringResources['MULTIPLE_AVAILABILITY_LIST'], implode(', ', $this->costHelper->availabilityNames))
			, 'total_hours'=>$this->costHelper->totalHours > 0 ? $this->costHelper->totalHours : null
			, 'total_minutes'=>$this->costHelper->totalMinutes > 0 ? $this->costHelper->totalMinutes : null
			, 'if_has_extend_next_day'=>$this->costHelper->extendsNextDay
			, 'extend_next_day_message'=>$this->stringResources['BOOKING_EXTEND_NEXT_DAY_LABEL']
			, 'if_include_total_time'=>$this->costHelper->totalHours > 0 || $this->costHelper->totalMinutes > 0
			, 'total_time_label'=>$this->stringResources['BOOKING_TOTAL_HOURS_MINUTES_LABEL']
			, 'total_time'=>$this->costHelper->getTotalTime()
			, 'pay_now_full_amount_label'=>$this->stringResources['BOOKING_PAY_NOW_FULL_AMOUNT_LABEL']
			, 'has_pay_now_full_amount'=>$this->costHelper->availability->enableFullAmountOrDeposit
			, 'pay_now_full_amount'=>Calendarista_MoneyHelper::toLongCurrency($this->costHelper->totalAmountBeforeDeposit)
		));
		$this->listRepeatDates();
		?>
		<input type="hidden" name="totalAmountBeforeDiscount" value="<?php echo $this->costHelper->totalCostBeforeDiscount ?>">
		<div class="calendarista-row-double"></div>
		<?php if($this->appointment === 1/*back-end*/ && ($this->costHelper->availability->deposit && $this->costHelper->availability->enableFullAmountOrDeposit)): ?>
		<div class="col-xl-12">
			<div class="form-check">
				<label for="<?php echo $this->uniqueId ?>_upfrontpayment" class="form-check-label calendarista-typography--caption1">
					<input 
						id="<?php echo $this->uniqueId ?>_upfrontpayment"
						name="upfrontPayment"
						type="checkbox"
						class="form-check-input"
						value="1"
						<?php echo $this->upfrontPayment ? 'checked' : '' ?>>
					<?php echo esc_html($this->stringResources['PAYMENT_OPTIONALLY_FULL_AMOUNT_LABEL'])?>
					<?php echo $this->getTotalAmountBeforeDiscount(); ?>
				</label>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}
}