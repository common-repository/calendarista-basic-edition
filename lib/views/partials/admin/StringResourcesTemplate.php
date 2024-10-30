<?php
class Calendarista_StringResourcesTemplate extends Calendarista_ViewBase{
	public $stringResources;
	function __construct( ){
		parent::__construct();
		new Calendarista_StringResourcesController(
			array($this, 'created')
			, array($this, 'updated')
			, array($this, 'deleted')
		);
		$stringResourcesRepository = new Calendarista_StringResourcesRepository();
		$this->stringResources = $stringResourcesRepository->readByProject($this->selectedProjectId);
		$this->render();
	}
	function format($value){
		$values = explode('_', $value);
		$result = ucfirst(strtolower($values[0]));
		if(count($values) > 1){
			foreach($values as $key=>$val){
				if($key === 0){
					$values[$key] = ucfirst(strtolower($val));
				}else{
					$values[$key] = strtolower($val);
				}
			}
			$result = implode(' ', $values);
		}
		return $result;
	}
	function created($result){
		if($result){
			$this->createdNotification();
		}
	}
	function updated($result){
		if($result){
			$this->updatedNotification();
		}
	}
	function deleted($result){
		if($result){
			$this->deletedNotification();
		}
	}
	public function createdNotification() {
		?>
		<div class="calendarista index updated notice is-dismissible">
			<p><?php esc_html_e('The string resources have been updated', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function updatedNotification() {
		?>
		<div class="calendarista index updated notice is-dismissible">
			<p><?php esc_html_e('The string resources have been updated', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function deletedNotification() {
		?>
		<div class="calendarista index updated notice is-dismissible">
			<p><?php esc_html_e('The string resources have been reset to factory', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="wrap">
			<p class="description"><?php esc_html_e('If you are going to translate this plugin using WPML, Polylang or manual translations, then do not set any values here.', 'calendarista') ?></p>
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_stringresources"/>
				<input type="hidden" name="id" value="<?php echo $this->stringResources->id ?>"/>
				<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>">
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<?php foreach($this->stringResources->resources as $key=>$value):?>
						<tr>
							<td>
								<div><label for="<?php echo esc_attr($key) ?>"><?php echo esc_html($this->format($key)) ?></label></div>
								<input type="text" 
									class="regular-text" 
									id="<?php echo $key?>" 
									name="<?php echo $key?>" 
									value="<?php echo $value?>"/>
							</td>
						</tr>
						<?php endforeach;?>
					</body>
				</table>
				<p class="submit">
				<?php if($this->stringResources->id === -1) :?>
					<button class="button button-primary" name="calendarista_create"><?php esc_html_e('Save', 'calendarista') ?></button>
				<?php else:?>
					<button class="button button-primary" 
							name="calendarista_update" 
							value="<?php echo $this->stringResources->id?>">
							<?php esc_html_e('Save', 'calendarista') ?>
					</button>
					<button class="button button-primary" 
							name="calendarista_delete" 
							value="<?php echo $this->stringResources->id?>">
							<?php esc_html_e('Reset', 'calendarista') ?>
					</button>
				<?php endif;?>
				</p>
			</form>
		</div>
		<script type="text/javascript">
			(function($, wp){
				var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
				calendarista.createDelegate = function (instance, method) {
					return function () {
						return method.apply(instance, arguments);
					};
				};
				calendarista.stringResources = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.stringResources.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.stringResources({<?php echo $this->requestUrl ?>'});
		</script>
	<?php
	}
}