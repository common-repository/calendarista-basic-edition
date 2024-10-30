<?php
class Calendarista_Register{
	private $pages;
	public function __construct(){
		$args = array( 
			'meta_key'=>'calendarista_page_type'
			, 'hierarchical' => 0
		);
		$this->pages = get_pages($args);
		add_action('wp_head', array($this, 'metaData'));
		add_filter('wp_get_nav_menu_items', array($this, 'excludeFromRegisteredMenu'), 10, 3);
		add_filter('wp_page_menu_args', array($this, 'excludeFromDefaultMenu'), 10, 1);
		add_action('init', array($this, 'init'));
		new Calendarista_RegisterFrontEndAssets();
		new Calendarista_RenderFrontEndShortCodes();
		new Calendarista_RegisterJobs();
	}
	public function init(){
	}
	public function excludeFromDefaultMenu($args){
		$excludes = array();
		foreach($this->pages as $page){
			array_push($excludes, $page->ID);
		}
		$args['exclude'] = implode(',', $excludes);
		return $args;
	}
	public function excludeFromRegisteredMenu( $items, $menu, $args ) {
		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {
			foreach($this->pages as $page){
				if ( $item->object_id === $page->ID ) {
					unset( $items[$key] );
				}
			}
		}
		return $items;
	}
	public function metaData(){
		$generalSetting = Calendarista_GeneralSettingHelper::get();
		if($generalSetting->enableMobileInitialScale){
			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		}
		echo '<meta name="plugins" content="calendarista ' . CALENDARISTA_VERSION . '" />';
	}
}
?>