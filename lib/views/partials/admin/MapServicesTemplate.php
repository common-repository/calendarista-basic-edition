<?php
class Calendarista_MapServicesTemplate extends Calendarista_ViewBase{
	public $maps;
	function __construct(){
		parent::__construct(false, true, 'calendarista-places');
		$mapRepo = new Calendarista_MapRepository();
		$this->maps = $mapRepo->readAll();
		$this->render();
	}
	public function render(){
	?>
		<div class="wrap">
			<p class="description"><?php esc_html_e('The services below will have a maps section and allow departure & destination routes', 'calendarista') ?></p>
			<form id="form1" data-parsley-validate action="<?php echo esc_url($this->baseUrl) ?>" method="post">
				<input type="hidden" name="tabName" value="new_place"/>
				<input type="hidden" name="controller" value="map" />
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<?php if(count($this->maps) > 0): ?>
				<table class="wp-list-table calendarista wp-list-table widefat fixed striped">
					<thead>
						<th><?php esc_html_e('Service', 'calendarista') ?></th>
						<th style="width: 80px"><?php esc_html_e('Action', 'calendarista') ?></th>
					</thead>
					<tbody>
						<?php foreach($this->maps as $map):?>
						<tr>
							<td>
								<input type="checkbox" name="id[]" value="<?php echo $map['id'] ?>">
								<span title="<?php echo strlen($map['projectName']) > 20 ? esc_attr($map['projectName']) : '' ?>">
									<?php echo esc_html($this->trimString($map['projectName'])) ?>
								</span>
							</td>
							<td style="width: 80px"><a href="<?php echo admin_url() . 'admin.php?page=calendarista-places&calendarista-tab=1&projectId=' . $map['projectId'] ?>" 
								value="<?php echo $map['projectId'] ?>">[<?php esc_html_e('Select', 'calendarista') ?>]</a>	
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
				<div class="calendarista-notice error notice is-dismissible">
					<p><?php esc_html_e('There are still no services with map based departure and destination fields.', 'calendarista') ?></p>
				</div>
				<?php endif; ?>
				<p class="submit">
					<button type="submit" name="calendarista_delete" class="button button-primary" disabled>
						 <?php esc_html_e('Delete', 'calendarista') ?>
					</button>
				</p>
			</form>
		</div>
		<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.mapServices = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.mapServices.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.projectId = options['projectId'];
				this.requestUrl = options['requestUrl'];
				this.baseUrl = options['baseUrl'];
				this.$deleteAllCheckboxes = $('input[name="id[]"]');
				this.$deleteButton = $('button[name="calendarista_delete"]');
				this.$deleteAllCheckboxes.on('change', function(){
					var hasChecked = context.$deleteAllCheckboxes.is(':checked');
					if(hasChecked){
						context.$deleteButton.prop('disabled', false);
					}else{
						context.$deleteButton.prop('disabled', true);
					}
				});
			};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.mapServices({
				'requestUrl': '<?php echo $_SERVER['REQUEST_URI'] ?>'
				, 'baseUrl': '<?php echo $this->baseUrl ?>'
		});
		</script>
	<?php
	}
}