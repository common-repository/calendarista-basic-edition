<?php
class Calendarista_Index extends Calendarista_ViewBase{
	public $tabs;
	public $calendarMode;
	public $project;
	public $generalSetting;
	public function __construct(){
		parent::__construct(false, true);
		$this->project = $this->getProject();
		$this->generalSetting = Calendarista_GeneralSettingHelper::get();
		if($this->project){
			$this->calendarMode = $this->project->calendarMode;
		}
		$this->tabs = $this->getTabs();
		$this->render();
	}
	public function getTabs(){
		$url = admin_url() . 'admin.php?page=calendarista-index';
		$result = array();
		$result[0] = array('url'=>$url, 'label'=>__('Services', 'calendarista'), 'active'=>false);
		$result[1] = array('url'=>$url . '&calendarista-tab=1', 'label'=>__('Availability', 'calendarista'), 'active'=>false);
		if(in_array($this->calendarMode, Calendarista_CalendarMode::$SUPPORTS_TIMESLOTS)){
			$result[2] = array('url'=>$url . '&calendarista-tab=2', 'label'=>__('Timeslots', 'calendarista'), 'active'=>false);
		}
		if(in_array($this->calendarMode, Calendarista_CalendarMode::$SUPPORTS_SEASONS) && !$this->generalSetting->disableSeasonsPage){
			$result[3] = array('url'=>$url . '&calendarista-tab=3', 'label'=>__('Seasons', 'calendarista'), 'active'=>false);
		}
		$result[5] = array('url'=>$url . '&calendarista-tab=5', 'label'=>__('Custom form fields', 'calendarista'), 'active'=>false);
		$result[6] = array('url'=>$url . '&calendarista-tab=6', 'label'=>__('Optionals', 'calendarista'), 'active'=>false);
		if(!$this->generalSetting->disableMapPage){
			$result[7] = array('url'=>$url . '&calendarista-tab=7', 'label'=>__('Map', 'calendarista'), 'active'=>false);
		}
		$result[8] = array('url'=>$url . '&calendarista-tab=8', 'label'=>__('Styles', 'calendarista'), 'active'=>false);
		$result[9] = array('url'=>$url . '&calendarista-tab=9', 'label'=>__('Text', 'calendarista'), 'active'=>false);
		$result[10] = array('url'=>$url . '&calendarista-tab=10', 'label'=>__('Short codes', 'calendarista'), 'active'=>false);
		$result[11] = array('url'=>$url . '&calendarista-tab=11', 'label'=>__('Search attributes', 'calendarista'), 'active'=>false);
		if($this->selectedProjectId !== -1){
			for($i = 0; $i <= count($result); $i++){
				if(isset($result[$i])){
					$result[$i]['url'] .= '&projectId=' . $this->selectedProjectId;
				}
			}
		}	
		if($this->selectedTab !== null){
			$result[$this->selectedTab]['active'] = true;
		}else{
			$result[0]['active'] = true;
		}
		return $result;
	}
	public function getPremiumNotice(){
		
	?>
	<div class="wrap">
	   <div class="notice notice-error">
		  <p>Calendarista has many more feature to help you take online appointments. Get more features by upgrading to <a href="https://www.calendarista.com/get-calendarista/" target="__blank">Calendarista Premium</a> (our paid version), which puts much more additional features and settings for a onetime fee with lifetime updates and six months of customer support.</p>
	   </div>
	</div>
	<?php
	}
	public function render(){
	?>
		<h2 class="calendarista nav-tab-wrapper">
			<?php foreach($this->tabs as $key=>$tab):?>
			<?php if(!isset($tab)){continue;}?>
			<a class="nav-tab <?php echo esc_attr($tab['active']) ? 'nav-tab-active' : '' ?>" href="<?php echo esc_url($tab['url']) ?>" data-calendarista-tabindex="<?php echo esc_attr($key) ?>"><?php echo esc_html($tab['label']) ?></a>
			<?php endforeach;?>
		  <div class="nav-tab" title="<?php esc_html_e('Service', 'calendarista') ?>"><i class="fa fa-cog projects-selector"></i></div>
		</h2>
		<?php $this->getPremiumNotice(); ?>
		<?php 
			switch($this->selectedTab){
				case 1:
					new Calendarista_AvailabilityTemplate();
					break;
				case 2:
					new Calendarista_TimeslotsTemplate();
					break;
				case 3:
					new Calendarista_SeasonTemplate();
					break;
				case 5:
					new Calendarista_CustomFormTemplate();
					break;
				case 6:
					new Calendarista_OptionalsTemplate();
					break;
				case 7:
					new Calendarista_AvailabilityMapTemplate();
					break;
				case 8:
					new Calendarista_StyleTemplate();
					break;
				case 9:
					new Calendarista_StringResourcesTemplate();
					break;
				case 10:
					new Calendarista_ShortCodesTemplate();
					break;
				case 11:
					new Calendarista_TagsTemplate();
					break;
				default:
					new Calendarista_ProjectTemplate();
					$this->readAllProjects();
				break;
			}
		?>
		<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
			<?php $this->renderProjectSelectList(false, __('Select a service', 'calendarista')) ?>
		</form>
		<div class="clear"></div>
		<script type="text/javascript">
		(function($){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.index = function(options){
				var context = this;
				this.requestUrl = options['requestUrl'];
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.index.prototype.init = function(options){
				var context = this
					, selectedTabIndex;
				this.$projectsModalDialog = $('.projects-modal').dialog({
					autoOpen: false
					, height: 'auto'
					, width: 'auto'
					, modal: true
					, resizable: false
					, dialogClass: 'calendarista-dialog'
					, buttons: [
						{
							'text': '<?php echo __("Cancel", "calendarista") ?>'
							, 'click':  function(){
								context.$projectsModalDialog.dialog('close');
							}
						}
					]
				});
				this.$projectsModalDialog.removeClass('hide');
				$('.calendarista.nav-tab-wrapper a').on('click', function(e){
					var projectId = <?php echo $this->selectedProjectId ?>;
					selectedTabIndex = parseInt($(this).attr('data-calendarista-tabindex'), 10);
					if([0, 10, 11].indexOf(selectedTabIndex) === -1 && projectId === -1){
						context.$projectsModalDialog.dialog('open');
						return false;
					}
				});
				$('#projectId').on('change', function(e){
					var val = parseInt($(this).val(), 10)
						, $activeTab = $('.nav-tab.nav-tab-active')
						, selectedTabIndex = parseInt($activeTab.attr('data-calendarista-tabindex'), 10);
					if(val !== -1){
						window.location.href = context.requestUrl + '&calendarista-tab=' + selectedTabIndex + '&projectId=' + val;
					}
				});
				$('.projects-selector').on('click', function(e){
					context.$projectsModalDialog.dialog('open');
				});
			};
			window['calendarista'] = calendarista;
		})(window['jQuery']);
		new calendarista.index({
			'requestUrl': '<?php echo $this->baseUrl ?>'
		});
		</script>
		<?php
	}
}