<?php
class Calendarista_AvailabilityMapController extends Calendarista_BaseController{
	private $repo;
	private $availability;
	public function __construct($availability, $updateCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'availability_map')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->availability = $availability;
		$this->repo = new Calendarista_AvailabilityRepository();
		parent::__construct(null, $updateCallback, $deleteCallback);
	}
	public function update($callback){
		$availability = $this->repo->read($this->availability->id);
		if(!$availability){
			$availability = new Calendarista_Availability(array());
		}
		$availability->regionAddress = $this->availability->regionAddress;
		$availability->regionLat = $this->availability->regionLat;
		$availability->regionLng = $this->availability->regionLng;
		$availability->regionMarkerIconUrl = $this->availability->regionMarkerIconUrl;
		$availability->regionMarkerIconWidth = $this->availability->regionMarkerIconWidth;
		$availability->regionMarkerIconHeight = $this->availability->regionMarkerIconHeight;
		$availability->regionInfoWindowIcon = $this->availability->regionInfoWindowIcon;
		$availability->regionInfoWindowDescription  = $this->availability->regionInfoWindowDescription;
		$availability->styledMaps = $this->availability->styledMaps;
		$availability->showMapMarker = $this->availability->showMapMarker;
		$availability->hideMapDisplay = $this->availability->hideMapDisplay;
		$this->repo->update($availability);
		$this->executeCallback($callback, array($availability->id));
	}
	public function delete($callback){
		$availability = $this->repo->read($this->availability->id);
		$availability->regionAddress = '';
		$availability->regionLat = '';
		$availability->regionLng = '';
		$availability->regionMarkerIconUrl = '';
		$availability->regionMarkerIconWidth = '';
		$availability->regionMarkerIconHeight = '';
		$availability->regionInfoWindowIcon = '';
		$availability->regionInfoWindowDescription = '';
		$availability->styledMaps = '';
		$availability->showMapMarker = false;
		$availability->hideMapDisplay = false;
		$this->repo->update($availability);
		$this->executeCallback($callback, array(true));
	}
}
?>