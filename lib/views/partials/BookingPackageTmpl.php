<?php
class Calendarista_BookingPackageTmpl extends Calendarista_TemplateBase{
	public $project;
	public $hasEndDate;
	public $packages = array();
	public $appointment;
	public $_availabilityId;
	public $_availableDate;
	public $_endDate;
	public $searchResultAvailabilityId;
	public function __construct($availabilities, $appointment = -1, $stateBag = null, $searchResultAvailabilityId = null){
		parent::__construct($stateBag);
		$this->availabilities = $availabilities;
		$this->appointment = $appointment;
		$this->searchResultAvailabilityId = $searchResultAvailabilityId;
		$repo = new Calendarista_ProjectRepository();
		$this->project = $repo->read($this->projectId);
		$this->getPackages();
		$this->render();
	}
	public function getPackages(){
		//if we are in edit mode, get selected package
		$selectedPackage = $this->getSelectedPackage();
		if($selectedPackage){
			array_push($this->packages, $selectedPackage);
		}
		foreach($this->availabilities as $availability){
			$availabilityHelper = new Calendarista_AvailabilityHelper(array(
				'projectId'=>$availability->projectId
				, 'availabilityId'=>$availability->id
			));
			$result = $availabilityHelper->getNextOccurrenceByPackage();
			if($result){
				$startDate = date(CALENDARISTA_DATEFORMAT, $result['startDate']);
				$endDate = date(CALENDARISTA_DATEFORMAT, $result['endDate']);
				if($selectedPackage && 
						($selectedPackage['availabilityId'] === $availability->id && 
							($selectedPackage['startDate'] == $startDate && $selectedPackage['endDate'] == $endDate))){
					continue;
				}
				array_push($this->packages, array(
					'projectId'=>$availability->projectId
					, 'availabilityId'=>$availability->id
					, 'startDate'=>$startDate
					, 'endDate'=>$endDate
					, 'name'=>$availability->name
					, 'appendPackagePeriodToName'=>$availability->appendPackagePeriodToName
					, 'daysInPackage'=>$availability->daysInPackage
				));
			}
		}
	}
	public function getSelectedPackage(){
		if($this->appointment !== 1/*edit mode*/){
			return null;
		}
		//since we are editing a package, get it from viewstate as it may have expired
		$this->_availabilityId = $this->getViewStateValue('_availabilityId');
		$this->_availableDate = $this->getViewStateValue('_availableDate');
		$this->_endDate = $this->getViewStateValue('_endDate');
		$result = null;
		if(!in_array(null, array($this->_availabilityId, $this->_availableDate, $this->_endDate))){
			$startDate = date(CALENDARISTA_DATEFORMAT, strtotime($this->_availableDate));
			$endDate = date(CALENDARISTA_DATEFORMAT, strtotime($this->_endDate));
			$result = array(
				'availabilityId'=>(int)$this->_availabilityId
				, 'startDate'=>$startDate
				, 'endDate'=>$endDate
				, 'name'=>sprintf(__('Expired %s - %s', 'calendarista'), $startDate, $endDate)
			);
		}
		return $result;
	}
	protected function _selected($availabilityId){
		$value = $this->searchResultAvailabilityId ? $this->searchResultAvailabilityId : $this->getViewStateValue('availabilityId');
		if($value == $availabilityId){
			return 'selected=selected';
		}
		return null;
	}
	public function packageSelected($package){
		if($this->appointment === 1/*edit mode*/){
			$availabilityId = $this->getViewStateValue('availabilityId');
			$startDate = $this->getViewStateValue('availableDate');
			$endDate = $this->getViewStateValue('endDate');
			$result = null;
			if(!in_array(null, array($availabilityId, $startDate, $endDate))){
				$startDate = date(CALENDARISTA_DATEFORMAT, strtotime($startDate));
				$endDate = date(CALENDARISTA_DATEFORMAT, strtotime($endDate));
				if($package['availabilityId'] === (int)$availabilityId && $package['startDate'] == $startDate && $package['endDate'] == $endDate){
					return 'selected=selected';
				}
			}
		}
		return $this->_selected($package['availabilityId']);
	}
	public function render(){
	?>
	<?php if($this->appointment === 1/*edit mode*/):?>
		<?php if($this->_availabilityId): ?>
		<input type="hidden" value="<?php echo $this->_availabilityId ?>" name="_availabilityId">
		<?php endif; ?>
		<?php if($this->_availableDate): ?>
		<input type="hidden" value="<?php echo $this->_availableDate ?>" name="_availableDate">
		<?php endif; ?>
		<?php if($this->_endDate): ?>
		<input type="hidden" value="<?php echo $this->_endDate ?>" name="_endDate">
		<?php endif; ?>
	<?php endif; ?>
	<div class="col-xl-12">
		<div class="form-group">
			<?php if(count($this->packages) > 1): ?>
			<label class="form-control-label calendarista-typography--caption1" for="seats_<?php echo $this->uniqueId ?>">
				<?php echo esc_html($this->stringResources['BOOKING_PACKAGE_LABEL']) ?>
			</label>
			<?php endif; ?>
			<div>
				<?php if(count($this->packages) > 1): ?>
					<select id="calendarista_package_<?php echo $this->uniqueId ?>" 
						name="package" 
						class="form-select calendarista-typography--caption1 calendarista_parsley_validated"
						data-calendarista-selected-package="<?php echo $this->getViewStateValue('availabilityId') ?>"
						data-parsley-required="true">
						<option value=""><?php echo esc_html($this->stringResources['BOOKING_PACKAGE_DEFAULT_VALUE']) ?></option>
						<?php foreach($this->packages as $package):?>
						<option value="<?php echo $package['availabilityId'] ?>"
							data-calendarista-startdate="<?php echo $package['startDate'] ?>" 
							data-calendarista-enddate="<?php echo $package['endDate'] ?>"
							<?php echo $this->packageSelected($package); ?>>
							<?php if($package['daysInPackage'] > 1): ?>
								<?php echo $package['appendPackagePeriodToName'] ? sprintf('%s (%s - %s)', esc_html(Calendarista_StringResourceHelper::decodeString($package['name'])), Calendarista_TimeHelper::formatDate($package['startDate']),  Calendarista_TimeHelper::formatDate($package['endDate'])) : esc_html(Calendarista_StringResourceHelper::decodeString($package['name'])) ?>
							<?php else: ?>
								<?php echo $package['appendPackagePeriodToName'] ? sprintf('%s (%s)', esc_html(Calendarista_StringResourceHelper::decodeString($package['name'])), Calendarista_TimeHelper::formatDate($package['startDate'])) : Calendarista_StringResourceHelper::decodeString($package['name']) ?>
							<?php endif; ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php elseif(count($this->packages) === 1): ?>
				<input type="hidden" value="<?php echo $this->packages[0]['availabilityId'] ?>" name="availabilityId">
				<input type="hidden" value="<?php echo $this->packages[0]['startDate'] ?>" name="__availableDate">
				<input type="hidden" value="<?php echo $this->packages[0]['endDate'] ?>" name="__endDate">
				<div class="calendarista-row-single">
					<?php echo Calendarista_StringResourceHelper::decodeString($this->packages[0]['name']) ?>
				</div>
				<?php else: ?>
					<div class="alert alert-warning calendarista-typography--caption1" role="alert">
						<strong><?php echo esc_html($this->stringResources['WARNING']) ?></strong>&nbsp;<?php echo esc_html($this->stringResources['BOOKING_PACKAGE_EXHAUSTED']) ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php
	}
}
