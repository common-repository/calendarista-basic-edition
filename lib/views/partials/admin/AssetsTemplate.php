<?php
class Calendarista_AssetsTemplate extends Calendarista_ViewBase{
	public $setting;
	public $themes;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		$this->setting = $generalSettingsRepository->read();
		$calendarThemesPath = dirname(__FILE__) . '/../../../../assets/css/jquery-ui/';
		//themes have been deprecated.
		//$this->calendarThemes = $this->getThemes(array('None'=>''), $calendarThemesPath);
		$this->calendarThemes = array('None'=>'', 'smoothness'=>'smoothness');
		$this->render();
	}
	protected function getThemes($themes, $themeRoot){
		//Calendar themes are too heavy, deprecated.
		//might bring back as an add-on in the future.
		//if using a child theme then the theme has to be defined there 
		//and not in the parent theme
		if(file_exists($themeRoot)){
			$children = glob($themeRoot . '*' , GLOB_ONLYDIR);
			foreach($children as $child){
				$name = basename($child);
				$themes[$name] = $name;
			}
		}
		return $themes;
	}
	
	public function render(){
	?>
		<div class="wrap">
			<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
				<input type="hidden" name="controller" value="calendarista_assets"/>
				<input type="hidden" name="id" value="<?php echo esc_html($this->setting->id) ?>"/>
				<input type="hidden" name="currency" value="<?php echo esc_html($this->setting->currency) ?>"/>
				<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Bootstrap stylesheet', 'calendarista')?></label>
							</th>
							<td>
							<input name="refBootstrapStyleSheet" type="hidden" value="0">
							<input name="refBootstrapStyleSheet" 
								type="checkbox" <?php echo $this->setting->refBootstrapStyleSheet ? "checked" : ""?> /> 
														<?php esc_html_e('Reference bootstrap style sheet include', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Parsley library', 'calendarista')?></label>
							</th>
							<td>
							<input name="refParsleyJS" type="hidden" value="0">
							<input name="refParsleyJS" 
									type="checkbox" <?php echo $this->setting->refParsleyJS ? "checked" : ""?> /> 
								<?php esc_html_e('Reference Parsley JavaScript include', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for=""><?php esc_html_e('Debugging', 'calendarista')?></label>
							</th>
							<td>
								<input name="debugMode" type="checkbox" <?php echo $this->setting->debugMode ? "checked" : ""?> /> 
									<?php esc_html_e('Debug Mode', 'calendarista')?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="calendarTheme"><?php esc_html_e('Calendar theme', 'calendarista')?></label></th>
							<td>
								<select id="calendarTheme" name="calendarTheme">
								<?php foreach($this->calendarThemes as $key=>$value): ?>
									<option value="<?php echo $value ?>" <?php echo $this->setting->calendarTheme === $value ? 'selected' : '' ?>><?php echo $key ?></option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="fontFamilyUrl"><?php esc_html_e('Font family url', 'calendarista')?></label></th>
							<td>
							 <input type="text" 
								id="fontFamilyUrl" 
								name="fontFamilyUrl" 
								class="regular-text" 
								value="<?php echo esc_url($this->setting->fontFamilyUrl) ?>" />
								<p class="description"><?php esc_html_e('Link to a font family resource. Please use full URL with http: or https:', 'calendarista')?></p>
							</td>
						</tr>
					</body>
				</table>
				<p class="submit">
				<?php if($this->setting->id === -1) :?>
					<button class="button button-primary" name="calendarista_create"><?php esc_html_e('Save', 'calendarista') ?></button>
				<?php else:?>
					<button class="button button-primary" 
							name="calendarista_update" 
							value="<?php echo esc_html($this->setting->id) ?>">
							<?php esc_html_e('Save', 'calendarista') ?>
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
				calendarista.assets = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.assets.prototype.init = function(options){
					var context = this;
					this.requestUrl = options['requestUrl'];
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.assets({<?php echo $this->requestUrl ?>'});
		</script>
	<?php
	}
}