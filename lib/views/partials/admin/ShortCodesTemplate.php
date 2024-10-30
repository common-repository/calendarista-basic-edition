<?php
class Calendarista_ShortCodesTemplate extends Calendarista_ViewBase{
	public $style;
	public $steps;
	public $stringResources;
	public $themes;
	function __construct( ){
		parent::__construct(false, true);
		$this->tabs = $this->getTabs();
		$this->render();
	}
	public function getTabs(){
		$url = admin_url() . 'admin.php?page=calendarista-index&projectId=' . $this->selectedProjectId;
		$this->selectedTab = isset($_GET['calendarista-sub-tab']) ? (int)$_GET['calendarista-sub-tab'] : 0;
		$result = array();
		$result[0] = array('url'=>$url . '&calendarista-tab=10', 'label'=>__('Booking form', 'calendarista'), 'active'=>false);
		$result[1] = array('url'=>$url . '&calendarista-tab=10&calendarista-sub-tab=1', 'label'=>__('Search filter', 'calendarista'), 'active'=>false);
		if($this->selectedTab !== null){
			$result[$this->selectedTab]['active'] = true;
			$this->requestUrl .= '&calendarista-sub-tab=' . $this->selectedTab;
		}else{
			$result[0]['active'] = true;
		}
		return $result;
	}
	public function render(){
	?>
	<p class="description">
		<?php foreach($this->tabs as $tab):?>
			<?php if(!isset($tab)){continue;}?>
			<input type="radio" name="shortcode" value="<?php echo esc_url($tab['url']) ?>" <?php echo $tab['active'] ? 'checked': '' ?>>—<?php echo esc_html($tab['label']) ?>&nbsp;&nbsp;
		<?php endforeach;?>
	</p>
		<?php 
			switch($this->selectedTab){
			case 0:
				new Calendarista_ShortCodeGeneratorTemplate();
				break;
			case 1:
				new Calendarista_SearchShortCodeTemplate();
				break;
		}?>
	<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.shortcode = function(options){
				var context = this;
				$(window).ready(function(){
					var selectedTab = options['selectedTab']
						,  $styleMode = $('input[name="shortcode"]');
					$styleMode.on('click', function(){
						window.location = $(this).val();
					});
					context.init(options);
				});
			};
			calendarista.shortcode.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
			};
		window['calendarista'] = calendarista;
	})(window['jQuery'], window['calendarista_wp_ajax']);
	new calendarista.shortcode({<?php echo $this->requestUrl ?>', 'selectedTab': <?php echo $this->selectedTab ?>});
	</script>
	<?php
	}
}