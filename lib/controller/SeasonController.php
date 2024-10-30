<?php
class Calendarista_SeasonController extends Calendarista_BaseController{
	private $season;
	private $seasonRepository;
	public function __construct($createCallback, $updateCallback, $deleteCallback, $deleteManyCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_season')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->seasonRepository = new Calendarista_SeasonRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if(array_key_exists('calendarista_delete_many', $_POST)){
			$this->deleteMany($deleteManyCallback);
		}
	}
	protected function getParams(){
		$id = (int)$this->getPostValue('id');
		$projectId = (int)$this->getPostValue('projectId');
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$projectName = sanitize_text_field($this->getPostValue('projectName'));
		$availabilityName = sanitize_text_field($this->getPostValue('availabilityName'));
		$start = sanitize_text_field($this->getPostValue('start'));
		$end = sanitize_text_field($this->getPostValue('end'));
		$fixedCost = (float)$this->getPostValue('fixedCost');
		$variableCost = (float)$this->getPostValue('variableCost');
		$costMode = (int)$this->getPostValue('costMode');
		$bookingDaysMinimum = (int)$this->getPostValue('bookingDaysMinimum');
		$bookingDaysMaximum = (int)$this->getPostValue('bookingDaysMaximum');
		return array(
			'id'=>$id, 
			'projectId'=>$projectId, 
			'availabilityId'=>$availabilityId, 
			'projectName'=>$projectName,
			'availabilityName'=>$availabilityName,
			'start'=>$start, 
			'end'=>$end, 
			'cost'=>(isset($_POST['fixedCost']) ? $fixedCost : $variableCost), 
			'percentageBased'=>isset($_POST['variableCost']),
			'costMode'=>$costMode,
			'repeatWeekdayList'=>isset($_POST['repeatWeekdayList']) ? (array)$_POST['repeatWeekdayList'] : array(),
			'bookingDaysMinimum'=>$bookingDaysMinimum,
			'bookingDaysMaximum'=>$bookingDaysMaximum
		);
	}
	public function create($callback){
		$params = $this->getParams();
		$result = $this->seasonRepository->insert($params);
		$this->executeCallback($callback, array($result));
	}
	
	public function update($callback){
		$params = $this->getParams();
		$result = $this->seasonRepository->update($params);
		$this->executeCallback($callback, array($params['id'], $result));
	}
	
	public function delete($callback){
		$id = (int)$this->getPostValue('id');
		$result = $this->seasonRepository->delete($id);
		$pricingSchemeRepo = new Calendarista_PricingSchemeRepository();
		$pricingSchemeRepo->deleteBySeasonId($id);
		$this->executeCallback($callback, array($id, $result));
	}
	public function deleteMany($callback){
		$val = isset($_POST['seasons']) ? (array)$_POST['seasons'] : null;
		if($val){
			$seasons = array_map('intval', $val);
			$pricingSchemeRepo = new Calendarista_PricingSchemeRepository();
			foreach($seasons as $seasonId){
				$this->seasonRepository->delete($seasonId);
				$pricingSchemeRepo->deleteBySeasonId($seasonId);
			}
		}
		$this->executeCallback($callback, array(count($seasons)));
	}
}
?>