<?php
/**
 * @package CalendaristaBasic
 * Plugin Name: Calendarista - Appointment booking system
 * Plugin URI: https://www.calendarista.com
 * Description: The ultimate booking plug-in. Prepare for greatness!
 * Requires at least: 6.3
 * Tested up to: 6.4
 * Requires PHP: 7.0
 * Version: 3.0.8
 * Author: typps
 * Author URI: http://www.typps.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if(!defined('CALENDARISTA_VERSION')){
	define('CALENDARISTA_VERSION', '3.0.8');
}
if(!defined('CALENDARISTA_ROOT_FOLDER')){
	define('CALENDARISTA_ROOT_FOLDER', dirname( __FILE__ ));
}
if(!defined('CALENDARISTA_ROOT_FILE')){
	define('CALENDARISTA_ROOT_FILE', __FILE__);
}
if(!defined('CALENDARISTA_LANGUAGES_FOLDER')){
	define('CALENDARISTA_LANGUAGES_FOLDER', dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}
if(!defined('CALENDARISTA_PLUGINDIR')){
	define('CALENDARISTA_PLUGINDIR', content_url() . '/plugins/' . basename(dirname( __FILE__ )) . '/'    ) ;
}
if(!defined('CALENDARISTA_MUSTACHE')){
	define('CALENDARISTA_MUSTACHE', dirname( __FILE__ ) . '/ext/mustache/src/Mustache/');
}
if(!defined('CALENDARISTA_RELATIVE_PATH_TO_PLUGIN')){
	define('CALENDARISTA_RELATIVE_PATH_TO_PLUGIN', basename(dirname( __FILE__ )) . '/Calendarista.php');
}
if(!defined('CALENDARISTA_RELATIVE_PATH_TO_DOCUMENTATION')){
	define('CALENDARISTA_RELATIVE_PATH_TO_DOCUMENTATION', plugins_url() . '/' . basename(dirname( __FILE__ )) . '/CALENDARISTA-README.pdf');
}
if(!defined('CALENDARISTA_ABSOLUTE_PATH_TO_DOCUMENTATION')){
	define('CALENDARISTA_ABSOLUTE_PATH_TO_DOCUMENTATION', '');
}
if(!defined('CALENDARISTA_ABSOLUTE_PATH_TO_PLUGIN')){
	define('CALENDARISTA_ABSOLUTE_PATH_TO_PLUGIN', __FILE__);
}
if(!defined('CALENDARISTA_PLUGIN_SLUG')){
	define('CALENDARISTA_PLUGIN_SLUG', basename(dirname( __FILE__ )));
}
if(!defined('CALENDARISTA_DATEFORMAT')){
	define('CALENDARISTA_DATEFORMAT', 'Y-m-d');
}
if(!defined('CALENDARISTA_FULL_DATEFORMAT')){
	define('CALENDARISTA_FULL_DATEFORMAT', 'Y-m-d H:i');
}
if(!defined('CALENDARISTA_LONG_DATEFORMAT')){
	define('CALENDARISTA_LONG_DATEFORMAT', 'F j, Y, g:i a');
}
if(!defined('CALENDARISTA_META_KEY_NAME')){
	define('CALENDARISTA_META_KEY_NAME', 'calendarista_page_type');
}
/*if(!defined('CALENDARISTA_ACTIVATION_NOREDIRECT')){
	define('CALENDARISTA_ACTIVATION_NOREDIRECT', true);
}*/
require_once dirname(__FILE__) . '/lib/Initialize.php';
new Calendarista_Initialize();
?>