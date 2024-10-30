<?php
class Calendarista_WooCommerceHelper{
    public function __construct() {

	}
	
	public static function logUnregisterdOrders(){
		
	}
	public static function registerCustomProductType(){
		
	}
	
	public static function wooCommerceActive(){
		return false;
	}
	
	public static function setCheckoutUrl(){
		
	}
	public static function ensureWooCommerceInitiated(){
		return false;
	}
	public static function wooCommerceInitiated(){
		return self::ensureWooCommerceInitiated();
	}
}
?>