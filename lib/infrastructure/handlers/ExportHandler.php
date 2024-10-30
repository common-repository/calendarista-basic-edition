<?php 
	class Calendarista_ExportHandler
	{
		public $projectId;
		public $availabilityId;
		public $fromDate;
		public $endDate;
		public $email;
		public $includeSales;
		public $includeOptionals;
		public $includeFormFields;
		public $includeDynamicFields;
		public $includeMap;
		public $status;
		public $stringResources;
		public function __construct($args){
			$this->projectId = isset($args['calendarista_projectid']) ? (int)$args['calendarista_projectid'] : null;
			$this->availabilityId = isset($args['calendarista_availabilityid']) && !empty($args['calendarista_availabilityid']) ? (int)$args['calendarista_availabilityid'] : null;
			$this->fromDate = isset($args['calendarista_from']) ? sanitize_text_field($args['calendarista_from']) : null;
			$this->toDate = isset($args['calendarista_to']) ? sanitize_text_field($args['calendarista_to']) : null;
			$this->email = isset($args['calendarista_email']) ? sanitize_email($args['calendarista_email']) : null;
			$this->includeSales = isset($args['calendarista_sales']) ? sanitize_text_field($args['calendarista_sales']) : null;
			$this->includeOptionals = isset($args['calendarista_optionals']) ? sanitize_text_field($args['calendarista_optionals']) : null;
			$this->includeFormFields = isset($args['calendarista_formfields']) ? sanitize_text_field($args['calendarista_formfields']) : null;
			$this->includeDynamicFields = isset($args['calendarista_dynamicfields']) ? sanitize_text_field($args['calendarista_dynamicfields']) : null;
			$this->includeMap = isset($args['calendarista_map']) ? sanitize_text_field($args['calendarista_map']) : null;
			$this->status = (isset($args['calendarista_status']) && sanitize_text_field($args['calendarista_status']) != '-1') ? sanitize_text_field($args['calendarista_status']) : null;
			$this->stringResources = Calendarista_StringResourceHelper::getResource($this->projectId);
		}
		public function buildResult(){
			$args = array(
				'projectId'=>$this->projectId
				, 'availabilityId'=>$this->availabilityId
				, 'fromDate'=>$this->fromDate
				, 'toDate'=>$this->toDate
				, 'email'=>$this->email
				, 'status'=>$this->status
			);
			$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
			$appointments = $bookedAvailabilityRepo->export(array_merge($args, array('order'=>'ORDER BY a.seats')));
			if(!(is_array($appointments) && count($appointments) > 0)){
				return null;
			}
			$cols = array('Invoice ID', 'Service', 'Availability', 'Status', 'Start Date', 'End Date', 'Timezone', 'Server Timezone', 'Seats', 'User Email', 'Full Name', 'WooCommerce Order ID');
			$cols = apply_filters('calendarista_export_columns', $cols);
			$rows = array();
			foreach($appointments as $appointment){
				//association key.
				$i = (int)$appointment->id;
				if(!isset($rows[$i])){
					$rows[$i] = array();
				}
				foreach($appointment as $key=>$value){
					switch($key){
						case 'invoiceId':
						array_splice($rows[$i], 0, 0, $this->encode($value));
						break;
						case 'projectName':
						array_splice($rows[$i], 1, 0, $this->encode($value));
						break;
						case 'availabilityName':
						array_splice($rows[$i], 2, 0, $this->encode($value));
						break;
						case 'status':
						switch((int)$value){
							case 0:
							$value = __('Pending', 'calendarista');
							break;
							case 1:
							$value = __('Approved', 'calendarista');
							break;
							case 2:
							$value = __('Cancelled', 'calendarista');
							break;
						}
						array_splice($rows[$i], 3, 0, $this->encode($value));
						break;
						case 'fromDate':
						if($appointment->fullDay === '1'){
							$value = date(CALENDARISTA_DATEFORMAT, strtotime($value));
						}
						array_splice($rows[$i], 4, 0, $this->encode($value));
						break;
						case 'toDate':
						if($appointment->fullDay === '1' && $value){
							$value = date(CALENDARISTA_DATEFORMAT, strtotime($value));
						}
						array_splice($rows[$i], 5, 0, $this->encode($value));
						break;
						case 'timezone':
						array_splice($rows[$i], 6, 0, $this->encode($value));
						break;
						case 'serverTimezone':
						array_splice($rows[$i], 7, 0, $this->encode($value));
						break;
						case 'seats':
						array_splice($rows[$i], 8, 0, (int)$value);
						break;
						case 'userEmail':
						array_splice($rows[$i], 9, 0, $this->encode($value));
						break;
						case 'fullName':
						array_splice($rows[$i], 10, 0, $this->encode($value));
						break;
						case 'wooCommerceOrderId':
						array_splice($rows[$i], 11, 0, $this->encode($value));
						break;
					}
				}
			}
			if($this->includeSales){
				$repo = new Calendarista_OrderRepository();
				$sales = $repo->export($args);
				if((is_array($sales) && count($sales) > 0)){
					$lastIndex = count($cols);
					$cols = array_merge($cols, array(
						'Order Date'
						, 'Payment Status'
						, 'Total Amount'
						, 'Discount'
						, 'Tax'
						, 'Refund Amount'
						, 'Payment Date'
						, 'Payment Operator'
						, 'Deposit'
						, 'Balance'
					));
					foreach($sales as $sale){
						$i = (int)$sale->availabilityId;
						if(!isset($rows[$i])){
							continue;
						}
						foreach($sale as $key=>$value){
							switch($key){
								case 'orderDate':
								$value = date(CALENDARISTA_DATEFORMAT, strtotime($value));
								array_splice($rows[$i], $lastIndex+1, 0, $this->encode($value));
								break;
								case 'paymentStatus':
								if($value === '0'){
									$value = __('Unpaid', 'calendarista');
								}else if($value === '1'){
									$value = __('Paid', 'calendarista');
								}else if($value === '2'){
									$value = __('Refunded', 'calendarista');
								}
								array_splice($rows[$i], $lastIndex+2, 0, $this->encode($value));
								break;
								case 'totalAmount':
								$value = (float)$value > 0 ? Calendarista_MoneyHelper::formatCurrencySymbol((float)$value, true, $sale->currency, $sale->currencySymbol) : '';
								array_splice($rows[$i], $lastIndex+3, 0, $this->encode($value));
								break;
								case 'discount':
								$value = (float)$value > 0 ? Calendarista_MoneyHelper::toDouble($value) : '';
								if($value && $sale->discountMode === '0'){
									$value .= '%';
								}
								array_splice($rows[$i], $lastIndex+4, 0, $this->encode($value));
								break;
								case 'tax':
								$value = (float)$value > 0 ? $this->encode(Calendarista_MoneyHelper::toDouble($value) . '%') : '';
								array_splice($rows[$i], $lastIndex+5, 0, $this->encode($value));
								break;
								case 'refundAmount':
								$value = (float)$value > 0 ? Calendarista_MoneyHelper::formatCurrencySymbol((float)$value, true, $sale->currency, $sale->currencySymbol) : '';
								array_splice($rows[$i], $lastIndex+6, 0, $this->encode($value));
								break;
								case 'paymentDate':
								if($value){
									$value = date(CALENDARISTA_DATEFORMAT, strtotime($value));
								}
								array_splice($rows[$i], $lastIndex+7, 0, $this->encode($value));
								break;
								case 'paymentOperator':
								array_splice($rows[$i], $lastIndex+8, 0, $this->encode($value));
								break;
								case 'deposit':
								$value = (float)$value > 0 ? Calendarista_MoneyHelper::toDouble($value) : '';
								if($value && $sale->depositMode === '0'){
									$value .= '%';
								}
								array_splice($rows[$i], $lastIndex+9, 0, $this->encode($value));
								break;
								case 'balance':
								$value = (float)$value > 0 ? Calendarista_MoneyHelper::formatCurrencySymbol((float)$value, true, $sale->currency, $sale->currencySymbol) : '';
								array_splice($rows[$i], $lastIndex+10, 0, $this->encode($value));
								break;
							}
						}
					}
				}
			}
			if($this->includeDynamicFields){
				$repo = new Calendarista_BookedDynamicFieldRepository();
				$dynamicFields = $repo->export($args);
				if(is_array($dynamicFields) && count($dynamicFields) > 0){
					$lastIndex = count($cols);
					foreach($dynamicFields as $field){
						$bookedAvailability = $bookedAvailabilityRepo->readByOrderId($field['orderId']);
						if(!$bookedAvailability){
							continue;
						}
						foreach($bookedAvailability as $ba){
							$i = $ba->id;
							if(!isset($rows[$i])){
								continue;
							}
							$label = $this->encode($field['label']);
							if(!in_array($label, $cols)){
								array_splice($cols, $lastIndex, 0, $label);
								++$lastIndex;
							}
							$colIndex = array_search($label, $cols);
							foreach($rows as $k=>$row){
								if(count($rows[$k]) < $colIndex){
									array_splice($rows[$k], $colIndex, 0, '');
								}
							}
							array_splice($rows[$i], $colIndex, 0, $this->encode($field['value']));
						}
					}
				}
			}
			if($this->includeFormFields){
				$repo = new Calendarista_BookedFormElementRepository();
				$formElements = $repo->export(array_merge($args, array('order'=>'ORDER BY f.id')));
				if(is_array($formElements) && count($formElements) > 0){
					$lastIndex = count($cols);
					foreach($formElements as $formElement){
						$bookedAvailability = $bookedAvailabilityRepo->readByOrderId($formElement->orderId);
						if(!$bookedAvailability){
							continue;
						}
						foreach($bookedAvailability as $ba){
							$i = $ba->id;
							if(!isset($rows[$i])){
								continue;
							}
							$label = $this->encode($formElement->label);
							if(!in_array($label, $cols)){
								array_splice($cols, $lastIndex, 0, $label);
								++$lastIndex;
							}
							$colIndex = array_search($label, $cols);
							foreach($rows as $k=>$row){
								if(count($rows[$k]) < $colIndex){
									array_splice($rows[$k], $colIndex, 0, '');
								}
							}
							if(isset($rows[$i])){
								array_splice($rows[$i], $colIndex, 0, $this->encode($formElement->value));
							}
						}
					}
				}
			}
			if($this->includeOptionals){
				$repo = new Calendarista_BookedOptionalRepository();
				$optionals = $repo->export($args);
				if(is_array($optionals) && count($optionals) > 0){
					$lastIndex = count($cols);
					foreach($optionals as $optional){
						$bookedAvailability = $bookedAvailabilityRepo->readByOrderId($optional->orderId);
						if(!$bookedAvailability){
							continue;
						}
						foreach($bookedAvailability as $ba){
							$i = $ba->id;
							if(!isset($rows[$i])){
								continue;
							}
							$groupName = $optional->groupName;
							if(!is_null($optional->incrementValue)){
								$groupName = sprintf('%s - %s' , $optional->groupName, $optional->name);
							}
							$label = $this->encode($groupName);
							if(!in_array($label, $cols)){
								array_splice($cols, $lastIndex, 0, $label);
								++$lastIndex;
							}
							$colIndex = array_search($label, $cols);
							foreach($rows as $k=>$row){
								if(count($rows[$k]) < $colIndex){
									array_splice($rows[$k], $colIndex, 0, '');
								}
							}
							$name = $optional->name;
							if(!is_null($optional->incrementValue)){
								$name = sprintf($this->stringResources['BOOKING_OPTIONAL_QUANTITY_LABEL'], $optional->incrementValue);
							}
							if(isset($rows[$i])){
								array_splice($rows[$i], $colIndex, 0, $this->encode($name));
							}
						}
					}
				}
			}
			if($this->includeMap){
				$repo = new Calendarista_BookedMapRepository();
				$mapFields = $repo->export($args);
				if((is_array($mapFields) && count($mapFields) > 0)){
					$lastIndex = count($cols);
					$cols = array_merge($cols, array(
						'From Address'
						, 'From Lat'
						, 'From Lng'
						, 'To Address'
						, 'To Lat'
						, 'To Lng'
						, 'Distance'
						, 'Duration'
					));
					foreach($mapFields as $map){
						$i = (int)$map->availabilityId;
						if(!isset($rows[$i])){
							continue;
						}
						foreach($map as $key=>$value){
							switch($key){
								case 'fromAddress':
								array_splice($rows[$i], $lastIndex, 0, $this->encode($value));
								break;
								case 'fromLat':
								array_splice($rows[$i], $lastIndex+1, 0, $this->encode($value));
								break;
								case 'fromLng':
								array_splice($rows[$i], $lastIndex+2, 0, $this->encode($value));
								break;
								case 'toAddress':
								array_splice($rows[$i], $lastIndex+3, 0, $this->encode($value));
								break;
								case 'toLat':
								array_splice($rows[$i], $lastIndex+4, 0, $this->encode($value));
								break;
								case 'toLng':
								array_splice($rows[$i], $lastIndex+5, 0, $this->encode($value));
								break;
								case 'distance':
								if($value){
									$unit = $map->unitType === 0 ? 'km' : 'miles';
									$value = sprintf('%s %s', Calendarista_MoneyHelper::toDouble($value), $unit);
								}
								array_splice($rows[$i], $lastIndex+6, 0, $this->encode($value));
								break;
								case 'duration':
								if($value){
									$timeUnitLabels = Calendarista_StringResources::getTimeUnitLabels($this->stringResources);
									$value = Calendarista_TimeHelper::secondsToTime((float)$value, $timeUnitLabels);
								}
								array_splice($rows[$i], $lastIndex+7, 0, $this->encode($value));
								break;
							}
						}
					}
				}
				$repo = new Calendarista_BookedWaypointRepository();
				$waypoints = $repo->export($args);
				if((is_array($waypoints) && count($waypoints) > 0)){
					$lastIndex = count($cols);
					foreach($waypoints as $waypoint){
						$i = (int)$waypoint->availabilityId;
						if(!isset($rows[$i])){
							continue;
						}
						array_splice($cols, $lastIndex, 0, $this->encode('Waypoint Address'));
						foreach($rows as $k=>$row){
							array_splice($rows[$k], $lastIndex, 0, '');
						}
						array_splice($rows[$i], $lastIndex, 0, $this->encode($waypoint->address));
						
						array_splice($cols, ++$lastIndex, 0, $this->encode('Waypoint Lat'));
						foreach($rows as $k=>$row){
							array_splice($rows[$k], $lastIndex, 0, '');
						}
						array_splice($rows[$i], $lastIndex, 0, $this->encode($waypoint->lat));
						
						array_splice($cols, ++$lastIndex, 0, $this->encode('Waypoint Lng'));
						foreach($rows as $k=>$row){
							array_splice($rows[$k], $lastIndex, 0, '');
						}
						array_splice($rows[$i], $lastIndex, 0, $this->encode($waypoint->lng));
						++$lastIndex;
					}
				}
			}
			return array('cols'=>$cols, 'rows'=>$rows);
		}
		public function render(){
			$result = $this->buildResult();
			if(!$result){
				return;
			}
			$cols = $result['cols'];
			$rows = $result['rows'];
			if(count($cols) && count($rows)){
				echo implode(",", $cols);
				echo "\n";
				foreach($rows as $row){
					echo implode(",", $row);
					echo "\n";
				}
			}
		}
		function encode($value) {
			if(!trim($value)){
				return '';
			}
			if(strpos($value, '"') !== false || 
				strpos($value, "\n") !== false) 
			{
				$value = str_replace('"', '""', $value);
				$value = str_replace("\n", '', $value);
			}
			return '"' . $value . '"';
		}
	}
?>
