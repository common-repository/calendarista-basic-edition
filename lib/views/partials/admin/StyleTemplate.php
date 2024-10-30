<?php
class Calendarista_StyleTemplate extends Calendarista_ViewBase{
	public $style;
	public $steps;
	public $stringResources;
	public $themes;
	public $selectedTab;
	function __construct( ){
		parent::__construct(true, true);
		new Calendarista_StyleController(
			array($this, 'createdStyleNotification')
			, array($this, 'updatedStyleNotification')
			, array($this, 'deletedStyleNotification')
		);
		$styleRepository = new Calendarista_StyleRepository();
		$this->style = $styleRepository->readByProject($this->selectedProjectId);
		if(!$this->style){
			$this->style = new Calendarista_Style(array());
		}
		$this->stringResources = Calendarista_StringResourceHelper::getResource($this->selectedProjectId);
		$this->tabs = $this->getTabs();
		$this->themes = Calendarista_StyleHelper::getThemes();
		$this->render();
	}
	public function getTabs(){
		$url = admin_url() . 'admin.php?page=calendarista-index&projectId=' . $this->selectedProjectId;
		$this->selectedTab = isset($_GET['calendarista-sub-tab']) ? (int)$_GET['calendarista-sub-tab'] : 0;
		$result = array();
		$result[0] = array('url'=>$url . '&calendarista-tab=8', 'label'=>__('Basic', 'calendarista'), 'active'=>false);
		$result[1] = array('url'=>$url . '&calendarista-tab=8&calendarista-sub-tab=1', 'label'=>__('Advanced', 'calendarista'), 'active'=>false);
		if($this->selectedTab !== null){
			$result[$this->selectedTab]['active'] = true;
			$this->requestUrl .= '&calendarista-sub-tab=' . $this->selectedTab;
		}else{
			$result[0]['active'] = true;
		}
		return $result;
	}
	public function upperCaseWords($value){
		return ucwords(join(' ', explode('_', $value)));
	}
	public function createdStyleNotification($result, $errorMessage) {
		if($errorMessage):
		?>
		<div class="wrap">
			<div class="calendarista-notice error notice is-dismissible">
				<p><?php echo sprintf(__('An error has occurred: %s. The changes made were not applied.', 'calendarista'), $errorMessage) ?></p>
			</div>
			<hr>
		</div>
		<?php
		else:
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The styles have been updated', 'calendarista') ?></p>
			</div>
		</div>
		<?php
		endif;
	}
	public function updatedStyleNotification($result, $errorMessage) {
		if($errorMessage):
		?>
		<div class="wrap">
			<div class="calendarista-notice error notice is-dismissible">
				<p><?php echo sprintf(__('An error has occurred: %s. The changes made were not applied.', 'calendarista'), $errorMessage) ?></p>
			</div>
			<hr>
		</div>
		<?php
		else:
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The styles have been updated', 'calendarista') ?></p>
			</div>
			<hr>
		</div>
		<?php
		endif;
	}
	public function deletedStyleNotification($result) {
		?>
		<div class="wrap">
			<div class="calendarista-notice updated notice is-dismissible">
				<p><?php esc_html_e('The styles have been reset to factory', 'calendarista') ?></p>
			</div>
		</div>
		<?php
	}
	public function render(){
	?>
	<p class="description">
		<?php esc_html_e('All customizations apply to the booking wizard form.', 'calendarista') ?>
		<br>
		<br>
		<?php foreach($this->tabs as $tab):?>
			<?php if(!isset($tab)){continue;}?>
			<input type="radio" name="stylemode" value="<?php echo esc_url($tab['url']) ?>" <?php echo $tab['active'] ? 'checked': '' ?>>—<?php echo esc_html($tab['label']) ?>&nbsp;&nbsp;
		<?php endforeach;?>
	</p>
	<form data-parsley-validate action="<?php echo esc_url($this->requestUrl) ?>" method="post">
		<input type="hidden" name="controller" value="calendarista_style"/>
		<input type="hidden" name="id" value="<?php echo $this->style->id ?>"/>
		<input type="hidden" name="projectId" value="<?php echo $this->selectedProjectId ?>">
		<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
		<?php 
			switch($this->selectedTab){
				case 0:?>
				<div class="wrap">
					<table class="form-table">
						<tbody>
							<tr>
								<td>
									<p><strong><label for="theme"><?php esc_html_e('Theme', 'calendarista') ?></label></strong></p>
									<select
										id="theme" 
										name="theme">
											<?php foreach($this->themes as $key=>$value):?>
												<option value="<?php echo $key ?>" style="background-color: <?php echo $value ?>;" <?php echo $this->style->theme === $key ? 'selected' : null?>><?php echo $this->upperCaseWords($key) ?></option>
											<?php endforeach; ?>
										</select>
								</td>
							</tr>
							<tr>
								<td>
									<p><strong><label for="fontFamily"><?php esc_html_e('Font-family', 'calendarista') ?></label></strong></p>
									<input type="text" 
										class="regular-text" 
										id="fontFamily" 
										name="fontFamily" 
										value="<?php echo esc_url(stripslashes($this->style->fontFamily)) ?>"/>
									<p class="description"><?php esc_html_e('If using font from an external resource, then set fontfamily URL in the settings page.', 'calendarista') ?></p>
								</td>
							</tr>
							<tr>
								<td>
									<p>
										<strong><label for=""><?php esc_html_e('Apply theme partially', 'calendarista')?></label></strong>
									</p>
									<input name="partiallyThemed" type="hidden" value="0">
									<input name="partiallyThemed" 
											type="checkbox" <?php echo $this->style->partiallyThemed ? "checked" : ""?> /> 
										<?php esc_html_e('Apply only on buttons and calendar.', 'calendarista')?>
								</td>
							</tr>
							<tr>
								<td>
									<p><strong><label for="thumbnailWidth"><?php esc_html_e('Thumbnail width', 'calendarista') ?></label></strong></p>
									<input id="thumbnailWidth" 
										name="thumbnailWidth" 
										type="text" 
										class="small-text" 
										data-parsley-trigger="change focusout"
										data-parsley-type="digits"
										data-parsley-min="15"
										value="<?php echo $this->style->thumbnailWidth ?>" />px
								</td>
							</tr>
							<tr>
								<td>
									<p><strong><label for="thumbnailHeight"><?php esc_html_e('Thumbnail height', 'calendarista') ?></label></strong></p>
									<input id="thumbnailHeight" 
										name="thumbnailHeight" 
										type="text" 
										class="small-text" 
										data-parsley-trigger="change focusout"
										data-parsley-type="digits"
										data-parsley-min="15"
										value="<?php echo $this->style->thumbnailHeight ?>" />px
								</td>
							</tr>
						</body>
					</table>
				</div>
			<?php break;
			  case 1: ?>
				<div class="wrap">
					<?php wp_editor($this->style->bookingSummaryTemplate, 'bookingSummaryTemplate', $settings = array('media_buttons'=>false, 'tinymce'=>false)); ?> 
					<p class="description"><strong><?php esc_html_e('Note!', 'calendarista') ?></strong>&nbsp;<?php esc_html_e('When editing pay particular attention to how tokens are enclosed within 2 braces {{token}} or 3 braces {{{token}}}.', 'calendarista') ?></p>
					<div>
						<div class="calendarista-borderless-accordion">
							<div id="template_tokens">
								<h3><?php esc_html_e('Tokens', 'calendarista') ?></h3>
								<div>
									<p class="description">
										<?php esc_html_e('Use any of the tokens below to include in your template. Note that some tokens contains 3 curly braces instead of the usual 2.', 'calendarista')?>
									</p>
									<ul>
										<li>{{{booking_date}}}</li>
										<li>{{seats_summary}}</li>
										<li>{{nights_label}}</li>
										<li>{{from_address}}</li>
										<li><strong>{{{</strong>stops<strong>}}}</strong></li>
										<li>{{to_address}}</li>
										<li>{{distance}}</li>
										<li>{{unitType}}</li>
										<li><strong>{{{</strong>optionals<strong>}}}</strong></li>
										<li>{{customer_name_email}}</li>
										<li><strong>{{{</strong>subtotal_amount<strong>}}}</strong></li>
										<li>{{subtotal_amount_label}}</li>
										<li><strong>{{{</strong>total_amount<strong>}}}</strong></li>
										<li>{{total_amount_label}}</li>
										<li>{{discount_label}}</li>
										<li><strong>{{{</strong>discount<strong>}}}</strong></li>
										<li><strong>{{{</strong>discount_value<strong>}}}</strong></li>
										<li>{{applied}}</li>
										<li>{{tax_label}}</li>
										<li>{{tax}}</li>
										<li>{{tax_amount}}</li>
										<li>{{total_amount_before_tax}}</li>
										<li>{{balance_label}}</li>
										<li><strong>{{{</strong>balance<strong>}}}</strong></li>
										<li>{{balance_pay_on_arrival}}</li>
										<li>{{service_name}}</li>
										<li>{{availability_name}}</li>
										<li>{{{base_cost}}}</li>
										<li><strong>{{{</strong>dynamic_fields<strong>}}}</strong></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="calendarista-borderless-accordion">
							<div id="control_statements">
								<h3><?php esc_html_e('Control statements', 'calendarista') ?></h3>
								<div>
									<p class="description">
										<?php esc_html_e('Along with tokens, you can use the following control statements', 'calendarista')?>
									</p>
									<ul>
										<li>
										{{#if_has_seats}}
										<br>
										{{/if_has_seats}}
										</li>
										<li>
										{{#if_has_nights}}
										<br>
										{{/if_has_nights}}
										</li>
										<li>
										{{#if_has_from_address}}
										<br>
										{{/if_has_from_address}}
										</li>
										<li>
										{{#if_has_waypoints}}
										<br>
										{{/if_has_waypoints}}
										</li>
										<li>
										{{#if_has_to_address}}
										<br>
										{{/if_has_to_address}}
										</li>
										<li>
										{{#if_has_distance}}
										<br>
										{{/if_has_distance}}
										</li>
										<li>
										{{#if_has_optionals}}
										<br>
										{{/if_has_optionals}}
										</li>
										<li>
										{{#if_has_customer_name_email}}
										<br>
										{{/if_has_customer_name_email}}
										</li>
										<li>
										{{#if_has_subtotal}}
										<br>
										{{/if_has_subtotal}}
										</li>
										<li>
										{{#if_has_total_amount}}
										<br>
										{{/if_has_total_amount}}
										</li>
										<li>
										{{#if_has_discount}}
										<br>
										{{/if_has_discount}}
										</li>
										<li>
										{{#if_has_tax}}
										<br>
										{{/if_has_tax}}
										</li>
										<li>
										{{#if_has_deposit}}
										<br>
										{{/if_has_deposit}}
										</li>
										<li>
										{{#if_has_base_cost}}
										<br>
										{{/if_has_base_cost}}
										</li>
										<li>
										{{#if_has_dynamic_fields}}
										<br>
										{{/if_has_dynamic_fields}}
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
		<?php break;
		}?>
		<br class="clear">
		<p class="submit">
			<?php if($this->style->id === -1) :?>
				<button class="button button-primary" name="calendarista_create"><?php esc_html_e('Save', 'calendarista') ?></button>
			<?php else:?>
				<button class="button button-primary" 
						name="calendarista_update" 
						value="<?php echo $this->style->id ?>">
						<?php esc_html_e('Save', 'calendarista') ?>
				</button>
				<button class="button button-primary" 
						name="calendarista_delete" 
						value="<?php echo $this->style->id ?>">
						<?php esc_html_e('Reset', 'calendarista') ?>
				</button>
			<?php endif;?>
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
			calendarista.style = function(options){
				var context = this;
				$(window).ready(function(){
					var selectedTab = options['selectedTab']
						,  $styleMode = $('input[name="stylemode"]');
					$styleMode.on('click', function(){
						window.location = $(this).val();
					});
					context.init(options);
				});
			};
			calendarista.style.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				$('#template_tokens').accordion({
					collapsible: true
					, active: false
					, heightStyle: 'content'
					, autoHeight: false
					, clearStyle: true
				});
				$('#control_statements').accordion({
					collapsible: true
					, active: false
					, heightStyle: 'content'
					, autoHeight: false
					, clearStyle: true
				});
			};
		window['calendarista'] = calendarista;
	})(window['jQuery'], window['calendarista_wp_ajax']);
	new calendarista.style({<?php echo $this->requestUrl ?>', 'selectedTab': <?php echo $this->selectedTab ?>});
	</script>
	<?php
	}
}