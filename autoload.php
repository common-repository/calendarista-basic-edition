<?php
function Calendarista_Autoloader($value) {
	$nameSplit = explode('_', $value);
	if ($nameSplit[0] !== 'Calendarista') {
		return;
	}
	$className = implode('_', array_slice($nameSplit, 1));

	$dirs = array(
		'base/' 
		, 'controller/'
		, 'controller/base/'
		, 'controller/utils/'
		, 'domainmodel/base/'
		, 'domainmodel/entities/'
		, 'domainmodel/repository/'
		, 'views/'
		, 'views/base/'
		, 'views/partials/'
		, 'views/partials/admin/'
		, 'infrastructure/actions/'
		, 'infrastructure/core/'
		, 'infrastructure/emails/base/'
		, 'infrastructure/emails/'
		, 'infrastructure/handlers/base/'
		, 'infrastructure/handlers/'
		, 'infrastructure/ui/lists/base/'
		, 'infrastructure/ui/lists/'
		, 'infrastructure/ui/'
		, 'infrastructure/utils/'
	);
	foreach( $dirs as $dir ) {
		$filePath = dirname(__FILE__) . '/lib/' . $dir . $className . '.php';
		if (file_exists($filePath)) {
			require_once($filePath);
			return;
		}
	}
}

spl_autoload_register('Calendarista_Autoloader');
?>