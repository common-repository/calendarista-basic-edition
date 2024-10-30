<?php
class Calendarista_TagsTemplate extends Calendarista_ViewBase{
	public $tag;
	public $tagList;
	function __construct( ){
		parent::__construct(false, true);
		new Calendarista_TagsController(
			array($this, 'createdNotice')
			, array($this, 'updatedNotice')
			, array($this, 'deletedNotice')
		);
		$this->tag = new Calendarista_Tag(array(
			'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
			'tagId'=>isset($_POST['tagId']) ? (int)$_POST['tagId'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['name'] : null
		));
		if(array_key_exists('calendarista_edit', $_POST)){
			$id = (int)$this->getPostValue('calendarista_edit');
			$repo = new Calendarista_TagsRepository();
			$this->tag = $repo->read($id);
		}else if(array_key_exists('calendarista_reset', $_POST) || array_key_exists('calendarista_delete', $_POST)){
			$this->tag = new Calendarista_Tag(array());
		}
		$this->tagList = new Calendarista_TagList();
		$this->tagList->bind();
		$this->render();
	}
	public function createdNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The search attribute(s) created successfully.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function updatedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The search attribute has been updated.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function deletedNotice() {
		?>
		<div class="index updated notice is-dismissible">
			<p><?php esc_html_e('The search attribute(s) have been deleted.', 'calendarista'); ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
	<div class="widget-liquid-left">
		<div id="widgets-left">
			<div class="wrap">
				<form action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-excluded="[disabled=disabled]">
					<input type="hidden" name="controller" value="tags" />
					<input type="hidden" name="id" value="<?php echo $this->tag->id ?>"/>
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
					<table class="form-table">
						<tbody>
							<tr>
								<td>
									<div><label for="name"><?php esc_html_e('Search attribute', 'calendarista') ?></label></div>
									<?php if($this->tag->id):?>
										<input id="name" 
											name="name" 
											type="text" 
											class="regular-text" 
											data-parsley-required="true"
											value="<?php echo esc_attr(Calendarista_StringResourceHelper::decodeString($this->tag->name)) ?>"/>
										<?php else: ?>
											<textarea id="name" 
											class="regular-text" 
											name="name" 
											rows="3"
											data-parsley-required="true"
											></textarea>
										<p class="description"><?php esc_html_e('To create multiple search attributes, separate each attribute by a comma eg: TV, Internet, Furnished', 'calendarista') ?></p>
									<?php endif; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<?php if($this->tag->id):?>
						<input type="submit" name="calendarista_reset" id="calendarista_reset" class="button" value="<?php esc_html_e('New', 'calendarista') ?>">
						<input type="submit" name="calendarista_delete" id="calendarista_delete" class="button" value="<?php esc_html_e('Delete', 'calendarista') ?>">
						<input type="submit" name="calendarista_update" id="calendarista_update" class="button button-primary" value="<?php esc_html_e('Save changes', 'calendarista') ?>">
						<?php else:?>
						<input type="submit" name="calendarista_create" id="calendarista_create" class="button button-primary" value="<?php esc_html_e('Create new', 'calendarista') ?>">
						<?php endif;?>
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="widget-liquid-right">
		<div id="widgets-right">
			<div class="single-sidebar">
				<div class="sidebars-column-1">
					<form action="<?php echo esc_url($this->requestUrl) ?>" method="post">
						<input type="hidden" name="controller" value="tags" />
						<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
						<?php $this->tagList->display(); ?>
						<p>
							<button type="submit" name="calendarista_delete" class="button button-primary" disabled>
								<?php esc_html_e('Delete', 'calendarista') ?>
							</button>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		(function($, wp){
			var calendarista = window['calendarista'] ? window['calendarista'] : function(){};
			calendarista.createDelegate = function (instance, method) {
				return function () {
					return method.apply(instance, arguments);
				};
			};
			calendarista.tags = function(options){
				var context = this;
				$(window).ready(function(){
					context.init(options);
				});
			};
			calendarista.tags.prototype.init = function(options){
				var context = this;
				this.ajaxUrl = wp.url;
				this.nonce = wp.nonce;
				this.$tagCheckboxes = $('input[name="tags[]"]');
				this.$deleteButton = $('button[name="calendarista_delete"]');
				this.$tagsAllCheck = $('input[name="selectall"]');
				this.checkedAllDelegate = calendarista.createDelegate(this, this.checkedAll);
				this.$tagCheckboxes.on('change', this.checkedAllDelegate);
				this.tagsCheckAllDelegate = calendarista.createDelegate(this, this.tagsCheckall);
				this.$tagsAllCheck.on('change', this.tagsCheckAllDelegate);
			};
			calendarista.tags.prototype.checkedAll = function(){
				var hasChecked = this.$tagCheckboxes.is(':checked');
				if(hasChecked){
					this.$deleteButton.prop('disabled', false);
				}else{
					this.$deleteButton.prop('disabled', true);
				}
			};
			calendarista.tags.prototype.tagsCheckall = function(e){
				var target = e.currentTarget;
				if(target.checked){
					this.$tagCheckboxes.prop('checked', true);
				}else{
					this.$tagCheckboxes.prop('checked', false);
				}
				this.checkedAll();
			};
		window['calendarista'] = calendarista;
	})(window['jQuery'], window['calendarista_wp_ajax']);
	new calendarista.tags({<?php echo $this->requestUrl ?>'});
	</script>
	<?php
	}
}