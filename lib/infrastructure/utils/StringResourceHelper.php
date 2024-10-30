<?php
class Calendarista_StringResourceHelper{
	protected static $stringResources = null;
	protected static $projectId = null;
    public static function getResource($projectId) {
        if (!self::$stringResources || $projectId !== self::$projectId) {
			$stringResourcesRepo = new Calendarista_StringResourcesRepository();
			$result = $stringResourcesRepo->readByProject($projectId);
			self::$stringResources = $result->resources;
			self::$projectId = $projectId;
        }
        return self::$stringResources;
    }
	public static function decodeString($val){
		return htmlspecialchars(stripcslashes($val), ENT_QUOTES);
	}
    private function __construct() { }
}
?>