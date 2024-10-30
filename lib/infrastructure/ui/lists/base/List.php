<?php
require_once ABSPATH . 'wp-admin/includes/template.php';
if(file_exists(ABSPATH . 'wp-admin/includes/class-wp-screen.php') && !class_exists('WP_Screen')){
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
}
require_once ABSPATH . 'wp-admin/includes/screen.php';
require_once 'ListTable.php';

class Calendarista_List extends Calendarista_ListTable {
	public $uniqueNamespace = '';
	public $pagedKey;
	public $orderByKey;
	public $orderKey;
	public $currencySymbol;
	public $currency;
	public $generalSetting;
	public $timeFormat;
	public $dateFormat;
	protected $fullPager = true;
	function __construct( $args = array(), $fullPager = true ) {
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		$this->timeFormat = get_option('time_format');
		$this->dateFormat = get_option('date_format');
		$localeInfo = Calendarista_MoneyHelper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol = $localeInfo['currencySymbol'];
		$this->orderByKey = 'orderby';
		$this->orderKey = 'order';
		$this->pagedKey = 'paged';
		parent::__construct($args);
	}
}
?>