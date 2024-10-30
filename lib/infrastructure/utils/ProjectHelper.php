<?php
class Calendarista_ProjectHelper{
	protected static $project = null;
	protected static $projectId = null;
	protected static $projectRepo = null;
    public static function getProject($projectId) {
        if (!self::$project || $projectId !== self::$projectId) {
			self::$projectRepo = new Calendarista_ProjectRepository();
			self::$project = self::$projectRepo->read($projectId);
			self::$projectId = $projectId;
        }
        return self::$project;
    }
	public static function getAll(){
		if(!self::$projectRepo){
			self::$projectRepo = new Calendarista_ProjectRepository();
		}
		return self::$projectRepo->readAll();
	}
    private function __construct() { }
}
?>