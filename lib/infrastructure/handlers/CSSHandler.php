<?php 
	class Calendarista_CSSHandler
	{
		const SEARCH_FILTER_CLASS = '.calendarista.calendarista-calendar-search';
		const SEARCH_FILTER_CALENDAR_CLASS = '.calendarista-datepicker.calendarista-calendar-search';
		public $generalSetting;
		public function applySearchFilterTheme(){
			if(!$this->generalSetting->searchFilterTheme){
				return;
			}
			$style = Calendarista_StyleHelper::getStyle(array('theme'=>$this->generalSetting->searchFilterTheme));
			$result = $this->getCss($style, self::SEARCH_FILTER_CLASS, self::SEARCH_FILTER_CALENDAR_CLASS);
			echo $result;
		}
		protected function applyFontSize(){
			if($this->generalSetting->fontSize > 0){
				$result = array();
				array_push($result, '.calendarista .calendarista-typography--caption1{font-size: %1$sem;}');
				array_push($result, '.calendarista .form-group .input-group-text, .calendarista  .form-group select.form-control, .calendarista  .form-group input.form-control {font-size: %1$sem;}');
				array_push($result, '.calendarista button.btn.calendarista-typography--button, .calendarista a.btn.calendarista-typography--button {font-size: %1$sem;}');
				echo "\n";
				echo sprintf(join("\n", $result), $this->generalSetting->fontSize);
			}
		}
		public function __construct(){
			$this->generalSetting = Calendarista_GeneralSettingHelper::get();
			$styleRepository = new Calendarista_StyleRepository();
			$styles = isset($_GET['calendarista-admin-page']) ? false : $styleRepository->readAll();
			$projects = Calendarista_ProjectHelper::getAll();
			$result = array();
			if($styles){
				//in case we support individual styles. currently we allow only changing a small subset of styles 
				//such as font-family, thumbnail width/height and templating
				foreach($styles as $s){
					if(strtolower($s->theme) == 'none' || $s->projectId == -1){
						continue;
					}
					$css = $this->getCSS($s);
					array_push($result, $css);
				}
			}
			foreach($projects as $project){
				//default styles, we actually do not allow changing individual styles but a theme as a whole. much easier
				//people don't like to thinker with too many styles apparently, gets too confusing and appears complex and becomes a turn off.
				$style = null;
				if($styles){
					foreach($styles as $s){
						if($s->projectId === $project->id){
							$style = $s;
							break;
						}
					}
				}
				if(!$style){
					continue;
				}
				$css = $this->getProjectCss($project, $style);
				array_push($result, $css);
			}
			if(count($result) > 0){
				echo join("\n\n", $result);
			}
			$this->applySearchFilterTheme();
			$this->applyFontSize();
		}
		public function getCSS($style, $uniqueSelector = null, $uniqueCalendarSelector = null){
			$result = array();
			$project = Calendarista_ProjectHelper::getProject($style->projectId);
			if(!$uniqueSelector){
				$uniqueSelector = '#calendarista_' . $style->projectId;
				$uniqueCalendarSelector = '.calendarista-datepicker.calendarista-calendar-' . $style->projectId;
			}
			if(!$style->partiallyThemed){
				array_push($result, $uniqueSelector . ' .nav-item .nav-link:before {color: ' . $style->numberIndicatorColor . ';background:' . $style->numberIndicatorBackground . ';}');
				array_push($result, $uniqueSelector . ' .nav-item a.nav-link.active.calendarista-disabled:before {border: 1px solid ' . $style->selectedNumberIndicatorBackground . ';}');
				array_push($result, $uniqueSelector . ' .nav-item a.nav-link.calendarista-disabled:before {border: 1px solid ' . $style->navItemDisabledColor . ';}');
				array_push($result, $uniqueSelector . ' .nav-item .nav-link.active:before,' . $uniqueSelector . ' .nav-item .nav-link.nav-link-enabled:before {color: ' . $style->selectedNumberIndicatorColor . '!important;' . 'background:' . $style->selectedNumberIndicatorBackground . '!important;border: 1px solid ' . $style->navItemBorderColor .  ';}');
				array_push($result, $uniqueSelector . ' .nav-item a.nav-link:hover{color: ' . $style->navItemHoverColor. ';}');
				array_push($result, $uniqueSelector . ' .nav-item .nav-link.nav-link-enabled {text-underline-position: under; text-decoration: underline; color: ' . $style->navItemFontColor .   ';}');
				array_push($result, $uniqueSelector . ' .nav-item a.nav-link.calendarista-disabled {color: ' . $style->navItemDisabledColor . ';}');
				array_push($result, $uniqueSelector . ' .nav-item a.nav-link,' . $uniqueSelector . ' .nav-item a.nav-link.active.calendarista-disabled {color: ' . $style->navItemActiveColor . ';}');
				array_push($result, sprintf("%1\$s .nav-tabs .nav-link.active {border-color: %2\$s %2\$s #fff}", $uniqueSelector, $style->tabBorderColor));
				array_push($result, sprintf("%1\$s .nav-tabs .nav-link-enabled:hover {border-color: %2\$s; color: %3\$s;text-decoration: none;}", $uniqueSelector, $style->tabBorderColor, $style->navItemHoverColor));
			}
			array_push($result, sprintf("%1\$s button.btn-primary, %1\$s a.btn-primary {background-color: %2\$s;border-color: %3\$s; color: %4\$s;}"
										, $uniqueSelector, $style->buttonBgColor, $style->buttonBorderColor, $style->buttonColor));
			array_push($result, sprintf("%1\$s button.btn-primary:hover, %1\$s a.btn-primary:hover {background-color: %2\$s;border-color: %3\$s;}"
										, $uniqueSelector, $style->buttonBgHoverColor, $style->buttonBorderHoverColor));
			array_push($result, sprintf("%1\$s .btn-primary:not(:disabled):not(.disabled):active:focus, %1\$s button.btn-primary:focus, %1\$s .btn-primary.focus {background-color: %2\$s;border-color: %3\$s;box-shadow: %4\$s}"
										, $uniqueSelector, $style->buttonBgFocusColor, $style->buttonBorderFocusColor, $style->buttonBoxShadow));
			array_push($result, sprintf("%1\$s .btn-primary.disabled, %1\$s .btn-primary:disabled {background-color: %2\$s;border-color: %3\$s;}"
										, $uniqueSelector, $style->buttonDisabledBgColor, $style->buttonBorderDisabledColor));
			array_push($result, sprintf("%1\$s .btn-outline-secondary:hover {background-color: %2\$s;border-color: %3\$s;color: %4\$s}"
										, $uniqueSelector, $style->buttonBgFocusColor, $style->buttonOutlineBorderColor, $style->buttonColor));
			array_push($result, sprintf("%1\$s button.btn-outline-secondary:focus, %1\$s .btn-outline-secondary.focus {background-color: %2\$s;border-color: %3\$s;color:  %4\$s; box-shadow: %5\$s}"
										, $uniqueSelector, $style->buttonOutlineBgColor, $style->buttonBorderFocusColor, $style->buttonColor, $style->buttonBoxShadow));
			array_push($result, sprintf("%1\$s .btn-primary:not(:disabled):not(.disabled):active, %1\$s .btn-primary:not(:disabled):not(.disabled).active:focus, %1\$s .show>%1\$s .btn-primary.dropdown-toggle:focus {background-color: %2\$s;border-color: %3\$s;}"
										, $uniqueSelector, $style->buttonBgColor, $style->buttonBorderColor));
			array_push($result, sprintf("%1\$s .btn-outline-secondary.disabled, %1\$s .btn-outline-secondary:disabled {background-color: transparent; border-color: #ced4da;color: #505050;}"
										, $uniqueSelector));
			array_push($result, sprintf("%1\$s label.input-group-text, %1\$s span.input-group-text{background-color: %2\$s;border: 1px solid %3\$s;color: %4\$s;border-left-width: 2px;}"
										, $uniqueSelector, $style->buttonBgColor, $style->buttonBorderColor, $style->buttonColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-header.ui-widget-header {border: 1px solid %2\$s;background: %2\$s;color: %3\$s;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->buttonColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-next span {width: 0;height: 0;border-top: 10px solid transparent;border-bottom: 10px solid transparent;border-left: 10px solid %2\$s;}"
										, $uniqueCalendarSelector, $style->buttonColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-prev span {width: 0; height: 0; border-top: 10px solid transparent;border-bottom: 10px solid transparent; border-right:10px solid %2\$s; }"
										, $uniqueCalendarSelector, $style->buttonColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-next .ui-icon, %1\$s .ui-datepicker-prev .ui-icon{background: transparent;}"
										, $uniqueCalendarSelector));
			array_push($result, sprintf("%1\$s .ui-state-default, %1\$s .ui-widget-content .ui-state-default, %1\$s .ui-widget-header .ui-state-default, %1\$s .ui-button, %1\$s .ui-button.ui-state-disabled:hover, %1\$s .ui-button.ui-state-disabled:active{background: %2\$s;color: %3\$s}"
										, $uniqueCalendarSelector,  $style->buttonBgColor, $style->buttonColor));
			array_push($result, sprintf("%1\$s .ui-state-hover, %1\$s .ui-state-active{background: %2\$s;color: %3\$s;}"
										, $uniqueCalendarSelector,  $style->buttonBgHoverColor, $style->buttonColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-header .ui-state-hover.ui-datepicker-prev-hover span{border-right:10px solid %2\$s}"
										, $uniqueCalendarSelector,  $style->buttonBgHoverColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-header .ui-state-hover.ui-datepicker-next-hover span{border-left:10px solid %2\$s}"
										, $uniqueCalendarSelector,  $style->buttonBgHoverColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-buttonpane{border-top: 2px solid %2\$s;}"
										, $uniqueCalendarSelector, $style->buttonBgHoverColor));
			array_push($result, sprintf("%1\$s .ui-datepicker-unselectable.ui-state-disabled span{color: %2\$s }"
										, $uniqueCalendarSelector, $style->disableStateColor));
			array_push($result, sprintf("%1\$s .spinner-border.text-primary {color: %2\$s !important;}"
										, $uniqueSelector, $style->buttonBorderColor));	
			array_push($result, sprintf("%1\$s .calendarista-halfday-start a.ui-state-default{background: linear-gradient(140deg, %2\$s 50%%, %3\$s 51%%, %3\$s) !important;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->selectedDayBgColor));	
			array_push($result, sprintf("%1\$s .calendarista-halfday-start span{background: linear-gradient(140deg, %2\$s 50%%, %3\$s 51%%, %3\$s) !important;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->selectedDayBgColor));	
			array_push($result, sprintf("%1\$s .calendarista-halfday-end a.ui-state-default{background: linear-gradient(320deg, %2\$s 50%%, %3\$s 51%%, %3\$s) !important;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->selectedDayBgColor));	
			array_push($result, sprintf("%1\$s .calendarista-halfday-end span{background: linear-gradient(320deg, %2\$s 50%%, %3\$s 51%%, %3\$s) !important;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->selectedDayBgColor));	
			array_push($result, sprintf("%1\$s .calendarista-unavailable.calendarista-halfday-start span{background: linear-gradient(320deg, %2\$s 50%%, %3\$s 51%%, %3\$s) !important;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->buttonBgColor));
			array_push($result, sprintf("%1\$s .calendarista-unavailable.calendarista-halfday-end span{background: linear-gradient(320deg, %2\$s 50%%, %3\$s 51%%, %3\$s) !important;}"
										, $uniqueCalendarSelector, $style->buttonBgColor, $style->buttonBgColor));	
			array_push($result, sprintf("%1\$s .calendarista-halfday{border-top-color:  %2\$s;border-right-color: %3\$s}"
										, $uniqueCalendarSelector, $style->selectedDayBgColor, $style->buttonBgColor));	
			array_push($result, sprintf("%1\$s .calendarista-selectedday-range{ background: %2\$s !important;opacity: .99;}"
										, $uniqueCalendarSelector, $style->selectedDayBgColor));
			array_push($result, sprintf("%1\$s  button.ui-datepicker-current.ui-state-hover, %1\$s button.ui-datepicker-close.ui-state-hover{background: %2\$s;}"
										, $uniqueCalendarSelector, $style->buttonBgHoverColor));
			if(!$style->partiallyThemed){
				array_push($result, sprintf("%1\$s .form-control:focus, %1\$s .form-control:focus {border-color: %2\$s;box-shadow: %3\$s;}"
											, $uniqueSelector, $style->buttonBorderColor, $style->buttonBoxShadow));
				array_push($result, sprintf("%1\$s .card-header {background-color: %2\$s; border-bottom: 1px solid  %3\$s;}"
											, $uniqueSelector, $style->mainColor, $style->tabBorderColor));
				array_push($result, sprintf("%1\$s.card {border: 1px solid  %2\$s !important;}"
											, $uniqueSelector, $style->tabBorderColor));
			}
			return join("\n", $result);
		}
		protected function getProjectCss($project, $style){
			if(!$style->partiallyThemed){
				return '';
			}
			$result = array();
			$uniqueSelector = '#calendarista_' . $project->id;
			$uniqueCalendarSelector = '.calendarista-calendar-' . $project->id;
			//thumbnails
			array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {border-width: ' . $style->thumbnailBorderWidth . 'px;}');
			$thumbnailBorderColor = 'rgba(255,255,255,0.5)';
			if($style->thumbnailBorderColor){
				$thumbnailBorderColor = $style->thumbnailBorderColor;
			}
			array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {border-color: ' . $thumbnailBorderColor . ';}');
			if($style->roundedThumbnail){
				array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {border-radius: 50%;}');
			}
			if($style->enableThumbnailShadow){
				array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {box-shadow: inset 1px 1px 4px rgba(0,0,0,0.5), 0 2px 3px rgba(0,0,0,0.6);}');
			}
			$thumbnailWidth = '180';
			if($style->thumbnailWidth){
				$thumbnailWidth = $style->thumbnailWidth;
			}
			array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {width: ' . $thumbnailWidth . 'px;}');
			$thumbnailHeight = '180';
			if($style->thumbnailHeight){
				$thumbnailHeight = $style->thumbnailHeight;
			}
			array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {height: ' . $thumbnailHeight . 'px;}');
			array_push($result, $uniqueSelector . ' .calendarista-wizard-section-block-thumb {border-style: solid; z-index: 10000; position: absolute; left: 50%;top: -' . (($thumbnailHeight / 2) + $style->thumbnailBorderWidth) . 'px;margin: 0 0 0 -' . (($thumbnailWidth / 2) + $style->thumbnailBorderWidth) . 'px;}');
			array_push($result, $uniqueSelector . ' .calendarista-wizard-section-thumbnail {margin: ' . ((($thumbnailHeight / 2) + $style->thumbnailBorderWidth) + 10) . 'px auto 20px auto; padding: ' . ((($thumbnailHeight / 2) + $style->thumbnailBorderWidth) + 10) . 'px 20px 20px 20px;}');
			if($style->fontFamily){
				array_push($result, sprintf("%1\$s {font-family: %2\$s}"
								, $uniqueCalendarSelector, $style->fontFamily));
			}
			return join("\n", $result);
		}
	}
?>
