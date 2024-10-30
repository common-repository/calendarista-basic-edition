<?php
class Calendarista_Project extends Calendarista_EntityBase{
	public $maxTimeslots = 1;
	public $paymentsMode = -1;
	public $enableStrongPassword;
	public $membershipRequired;
	public $calendarMode;
	public $enableCoupons;
	public $currentDayColor;
	public $unavailableColor;
	public $availableColor;
	public $selectedDayColor;
	public $halfDayRangeColor;
	public $selectedDayRangeColor;
	public $rangeUnavailableDayColor;
	public $name;
	public $status = Calendarista_ProjectStatus::RUNNING;
	public $orderIndex;
	public $reminder;
	public $wooProductId;
	public $previewUrl;
	public $previewImageHeight;
	public $searchPage;
	public $optionalByService;
	public $repeatPageSize = 10;
	public $thankyouReminder;
	public $id;
	public function  __construct($args){
		if(array_key_exists('reminder', $args)){
			$this->reminder = (int)$args['reminder'];
		}
		if(array_key_exists('thankyouReminder', $args)){
			$this->thankyouReminder = (int)$args['thankyouReminder'];
		}
		if(array_key_exists('paymentsMode', $args)){
			$this->paymentsMode = (int)$args['paymentsMode'];
		}
		if(array_key_exists('enableStrongPassword', $args)){
			$this->enableStrongPassword = (bool)$args['enableStrongPassword'];
		}
		if(array_key_exists('membershipRequired', $args)){
			$this->membershipRequired = (bool)$args['membershipRequired'];
		}
		if(array_key_exists('calendarMode', $args)){
			$this->calendarMode = (int)$args['calendarMode'];
		}
		if(array_key_exists('enableCoupons', $args)){
			$this->enableCoupons = (bool)$args['enableCoupons'];
		}
		if(array_key_exists('currentDayColor', $args)){
			$this->currentDayColor = (string)$args['currentDayColor'];
		}
		if(array_key_exists('unavailableColor', $args)){
			$this->unavailableColor = (string)$args['unavailableColor'];
		}
		if(array_key_exists('availableColor', $args)){
			$this->availableColor = (string)$args['availableColor'];
		}
		if(array_key_exists('selectedDayColor', $args)){
			$this->selectedDayColor = (string)$args['selectedDayColor'];
		}
		if(array_key_exists('halfDayRangeColor', $args)){
			$this->halfDayRangeColor = (string)$args['halfDayRangeColor'];
		}
		if(array_key_exists('selectedDayRangeColor', $args)){
			$this->selectedDayRangeColor = (string)$args['selectedDayRangeColor'];
		}
		if(array_key_exists('rangeUnavailableDayColor', $args)){
			$this->rangeUnavailableDayColor = (string)$args['rangeUnavailableDayColor'];
		}
		if(array_key_exists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if(array_key_exists('wooProductId', $args)){
			$this->wooProductId = (int)$args['wooProductId'];
		}
		if(array_key_exists('previewUrl', $args)){
			$this->previewUrl = (string)$args['previewUrl'];
		}
		if(array_key_exists('previewImageHeight', $args)){
			$this->previewImageHeight = (int)$args['previewImageHeight'];
		}
		if(array_key_exists('searchPage', $args)){
			$this->searchPage = (int)$args['searchPage'];
		}
		if(array_key_exists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex = (int)$args['orderIndex'];
		}
		if(array_key_exists('optionalByService', $args)){
			$this->optionalByService = (bool)$args['optionalByService'];
		}
		if(array_key_exists('repeatPageSize', $args)){
			$this->repeatPageSize = (int)$args['repeatPageSize'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	
	protected function init(){
		$this->name = Calendarista_TranslationHelper::t('name_project' . $this->id, $this->name);
	}
	public function updateResources(){
		$this->registerWPML();
	}
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('name_project' . $this->id, $this->name);
	}
	
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('name_project' . $this->id);
	}
	
	public function toArray(){
		return array(
			'maxTimeslots'=>$this->maxTimeslots
			, 'reminder'=>$this->reminder
			, 'paymentsMode'=>$this->paymentsMode
			, 'enableStrongPassword'=>$this->enableStrongPassword
			, 'membershipRequired'=>$this->membershipRequired
			, 'calendarMode'=>$this->calendarMode
			, 'enableCoupons'=>$this->enableCoupons
			, 'currentDayColor'=>$this->currentDayColor
			, 'unavailableColor'=>$this->unavailableColor
			, 'availableColor'=>$this->availableColor
			, 'halfDayRangeColor'=>$this->halfDayRangeColor
			, 'selectedDayRangeColor'=>$this->selectedDayRangeColor
			, 'selectedDayColor'=>$this->selectedDayColor
			, 'rangeUnavailableDayColor'=>$this->rangeUnavailableDayColor
			, 'name'=>$this->name
			, 'wooProductId'=>$this->wooProductId
			, 'previewUrl'=>$this->previewUrl
			, 'previewImageHeight'=>$this->previewImageHeight
			, 'searchPage'=>$this->searchPage
			, 'status'=>$this->status
			, 'orderIndex'=>$this->orderIndex
			, 'optionalByService'=>$this->optionalByService
			, 'repeatPageSize'=>$this->repeatPageSize
			, 'thankyouReminder'=>$this->thankyouReminder
			, 'id'=>$this->id
		);
	}
}
?>