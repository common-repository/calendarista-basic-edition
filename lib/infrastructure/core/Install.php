<?php
class Calendarista_Install{
	const metaKeyName = 'calendarista_page_type';
	public function __construct(){

	}
	private static function readText($path){
		$content = '';
		if ($handle = fopen($path, 'rb')) {
			$len = filesize($path);
			if ($len > 0){
				$content = fread($handle, $len);
			}
			fclose($handle);
		}
		return trim($content);
	}
	public static function getContents($path){
		return self::readText($path);
	}
	public static function getSqlScript($file_name){
		$path = CALENDARISTA_ROOT_FOLDER  . '/assets/sql/' . $file_name;
		return self::readText($path);
	}
	public static function init(){
		global $wpdb;
		$charset_collate = '';
		if (!empty($wpdb->charset)){
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if (!empty($wpdb->collate)){
			$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$dbVersion = floatval(get_option('calendarista_db_version'));
		$scriptFileName = 'create.sql';
		$sql = null;
		$newVersion = floatval(self::getSqlScript('version.txt'));
		try{
			if($dbVersion !== $newVersion){
				$sql = self::getSqlScript($scriptFileName);
				if($sql){
					$sql = str_replace('prefix_', $wpdb->prefix, $sql);
					$sql = str_replace('charset_collate_placeholder', $charset_collate, $sql);
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
					update_option('calendarista_db_version', $newVersion);
				}
			}
			
		} catch (Exception $e) {
			update_option('calendarista_db_error', $e->getMessage());
		}
	}
	public static function uninstall(){
		global $wpdb;
		$sql = self::getSqlScript('drop.sql');
		
		if(!$sql){
			return;
		}

		$sql = str_replace('prefix_', $wpdb->prefix, $sql);
		$statements = explode(';', $sql);
		$length = count($statements) - 1;

		try{
			self::deleteResources();
			for($i = 0; $i < $length; $i++){
				$stmt = $statements[$i];
				$wpdb->query($stmt);
			}	
			delete_option('calendarista_db_version');
			delete_option('calendarista_db_error');
			delete_option('calendarista_google_calendar_has_error');
			delete_option('calendarista_woo_order_limit_by_day');
			//only if we want to explicitly delete
			wp_clear_scheduled_hook(Calendarista_EmailReminderJob::HOOK);
		
			include_once( ABSPATH . 'wp-admin/includes/plugin.php');
			if(is_plugin_active(CALENDARISTA_RELATIVE_PATH_TO_PLUGIN)) {
				deactivate_plugins(CALENDARISTA_RELATIVE_PATH_TO_PLUGIN);
				wp_redirect(admin_url('plugins.php?deactivate=true&plugin_status=all&paged=1'));
				exit();
			}
		} catch (Exception $e) {
			 add_option('calendarista_db_error', $e->getMessage());
		}
	}
	public static function deactivate(){
		wp_clear_scheduled_hook(Calendarista_ExpiredErrorLogJob::HOOK);
	}
	public static function deleteResources(){
		//delete wpml resources
		$projectRepo = new Calendarista_ProjectRepository();
		$projects = $projectRepo->readAll();
		foreach($projects as $project){
			$project->deleteResources();
		}
		$templates = Calendarista_EmailTemplateHelper::getTemplates();
		foreach($templates as $template){
			$name = 'email_template_' . $template['name'];
			Calendarista_TranslationHelper::unregister($name);
		}
	}
	public static function clearDatabase(){
		global $wpdb;
		$sql = self::getSqlScript('clear.sql');
		if(!$sql){
			return;
		}
		
		$sql = str_replace('prefix_', $wpdb->prefix, $sql);
		$statements = explode(';', $sql);

		$length = count($statements) - 1;
		try{
			for($i = 0; $i < $length; $i++){
				$stmt = $statements[$i];
				$wpdb->query($stmt);
			}	
		} catch (Exception $e) {
			 add_option('calendarista_db_error', $e->getMessage());
		}
	}
}
?>
