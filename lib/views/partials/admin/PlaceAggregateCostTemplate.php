<?php
class Calendarista_PlaceAggregateCostTemplate extends Calendarista_ViewBase{
	public $destinationPlaces;
	public $departurePlaces;
	public $map;
	public $aggregateCostList;
	function __construct(){
		parent::__construct(false, false, 'calendarista-places');
		new Calendarista_PlaceAggregateCostController(
			array($this, 'createdNotification')
			, array($this, 'updatedNotification')
			, array($this, 'deletedNotification')
			, array($this, 'deletedNotification')
			, array($this, 'updatedNotification')
		);
		$mapRepo = new Calendarista_MapRepository();
		$this->map = $mapRepo->readByProject($this->selectedProjectId);
		if(!isset($this->map)){
			$this->map = new Calendarista_Map($this->parseArgs('map'));
		}
		$placeRepo = new Calendarista_PlaceRepository();
		$this->departurePlaces = $placeRepo->readAll($this->map->id, Calendarista_PlaceType::DEPARTURE);
		$this->destinationPlaces = $placeRepo->readAll($this->map->id, Calendarista_PlaceType::DESTINATION);
		
		if($this->departurePlaces->count() === 0 || $this->destinationPlaces->count() === 0){
			$this->warningNotice();
			return;
		}
		$this->aggregateCostList = new Calendarista_AggregateCostList($this->map, $this->departurePlaces, $this->destinationPlaces);
		$this->aggregateCostList->bind();
		
		$this->render();
	}
	public function warningNotice() {
		?>
		<div class="calendarista-notice error notice is-dismissible">
			<p><?php esc_html_e('You must first create departure and destination places in order to set cost.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<p class="description"><?php esc_html_e('Each departure place below has a matching destination place where you can set the corresponding cost.', 'calendarista') ?></p>
		<form id="form1" data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
			<input type="hidden" name="controller" value="place_aggregate_cost"/>
			<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId?>"/>
			<input type="hidden" name="mapId" value="<?php echo $this->map->id?>"/>
			<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
			<?php $this->aggregateCostList->display() ?>
			<p class="submit">
				<input type="submit" name="calendarista_deleteMany" disabled
				class="button button-primary" value="<?php esc_html_e('Reset', 'calendarista') ?>">
				<input type="submit" name="calendarista_updateMany"
				class="button button-primary" value="<?php esc_html_e('Update', 'calendarista') ?>">
			</p>
		</form>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.aggregateCost = function(options){
				var context = this;
				this.init(options);
				this.$deleteManyCheckboxes = $('input[name="deleteMany[]"]');
				this.$updateManyCheckboxes = $('input[name="updateMany[]"]');
				this.$resetButton = $('input[name="calendarista_deleteMany"]');
				this.$updateButton = $('input[name="calendarista_updateMany"]');
				this.$resetAllCheckboxes = $('.calendarista-aggregate-cost-list input[name="deleteMany[]"]');
				this.$updateAllCheckboxes = $('.calendarista-aggregate-cost-list input.updateMany');
				this.$deleteManyCheckboxes.on('change', function(){
					var hasChecked = context.$deleteManyCheckboxes.is(':checked');
					if(hasChecked){
						context.$resetButton.prop('disabled', false);
					}else{
						context.$resetButton.prop('disabled', true);
					}
				});
				$('input[name="resetall"]').on('change', function(){
					if(this.checked){
						context.$resetAllCheckboxes.prop('checked', true);
					}else{
						context.$resetAllCheckboxes.prop('checked', false);
					}
					context.resetAllChanged();
				});
				this.resetAllChangedDelegate = calendarista.createDelegate(this, this.resetAllChanged);
				this.$resetAllCheckboxes.on('change', this.resetAllChangedDelegate);
				
				$('input[name="excludeall"]').on('change', function(){
					if(this.checked){
						context.$updateAllCheckboxes.prop('checked', true);
					}else{
						context.$updateAllCheckboxes.prop('checked', false);
					}
				});
			};
			calendarista.aggregateCost.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
			};
			calendarista.aggregateCost.prototype.resetAllChanged = function(){
				var hasChecked = this.$resetAllCheckboxes.is(':checked');
				if(hasChecked){
					this.$resetButton.prop('disabled', false);
				}else{
					this.$resetButton.prop('disabled', true);
				}
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.aggregateCost({
			'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
		});
		</script>
		<?php
	}
}