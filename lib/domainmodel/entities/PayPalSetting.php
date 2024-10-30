<?php
class Calendarista_PayPalSetting extends Calendarista_EntityBase
{
	public $id = -1;
	public $businessEmail;
	public $useSandbox;
	public $enabled;
	public $paymentOperator = Calendarista_PaymentOperator::PAYPAL;
	public $title;
	public $orderIndex = 0;
	public $enableInlineForm = false;
	public $imageUrl;
	public function __construct($args)
	{
		$this->title = __('Secure payment with PayPal', 'calendarista');
		if(array_key_exists('businessEmail', $args)){
			$this->businessEmail = (string)$args['businessEmail'];
		}
		if(array_key_exists('useSandbox', $args)){
			$this->useSandbox = (bool)$args['useSandbox'];
		}
		if(array_key_exists('enabled', $args)){
			$this->enabled = (bool)$args['enabled'];
		}
		if(array_key_exists('title', $args)){
			$this->title = (string)$args['title'];
		}
		if(array_key_exists('imageUrl', $args)){
			$this->imageUrl = (string)$args['imageUrl'];
		}
		if(array_key_exists('orderIndex', $args)){
			$this->orderIndex = (int)$args['orderIndex'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	public function updateResources(){
		$this->registerWPML();
	}
	public function deleteResources(){
		$this->unregisterWPML();
	}
	protected function init(){
		$this->title = Calendarista_TranslationHelper::t('paypal_title_' . $this->id, $this->title);
	}
	protected function registerWPML(){
		Calendarista_TranslationHelper::register('paypal_title_' . $this->id, $this->title);
	}
	protected function unregisterWPML(){
		Calendarista_TranslationHelper::unregister('paypal_title_' . $this->id);
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'businessEmail'=>$this->businessEmail
			, 'useSandbox'=>$this->useSandbox
			, 'enabled'=>$this->enabled
			, 'paymentOperator'=>$this->paymentOperator
			, 'title'=>$this->title
			, 'orderIndex'=>$this->orderIndex
			, 'enableInlineForm'=>$this->enableInlineForm
			, 'imageUrl'=>$this->imageUrl
		);
	}
}
?>