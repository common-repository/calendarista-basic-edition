<?php
class Calendarista_RemindersTemplate extends Calendarista_ViewBase{
	public $reminderList;
	public $id;
	public $totalRemindersCount;
	public $schedulesCount;
	public $project;
	public $redirectURI;
	public $reminderCronJobUrl;
	public $setting;
	public $twilioUrl;
	function __construct( ){
		parent::__construct(false, true, 'calendarista-settings');
		$this->id = isset($_GET['id']) ? (int)$_GET['id'] : null;
		$project = new Calendarista_Project(array());
		if($this->selectedProjectId !== -1){
			$project = $this->getProject();
		}
		new Calendarista_RemindersController(
			$project
			, array($this, 'save')
			, array($this, 'delete')
			, array($this, 'deleteAll')
			, array($this, 'clearSchedules')
			, array($this, 'resend')
		);
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		$this->setting = $generalSettingsRepository->read();
		$this->cronJobController();
		$this->project = $project;
		$this->reminderList = new Calendarista_ReminderList();
		$this->reminderList->bind();
		$this->requestUrl = $this->baseUrl;
		$this->totalRemindersCount = $this->reminderList->count;
		$this->schedulesCount = Calendarista_EmailReminderJob::getSchedulesCount();
		$this->redirectURI = get_site_url();
		$this->reminderCronJobUrl = $this->redirectURI . '?calendarista_handler=reminder_cron';
		$this->twilioUrl = admin_url() . 'admin.php?page=calendarista-settings&calendarista-tab=9&calendarista-sub-tab=3';
		$this->render();
	}
	public function cronJobController(){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'calendarista_reminder_cron')){
			return;
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		Calendarista_EmailReminderJob::cancelAllSchedules();
		$this->setting->reminderAltCronJob = $this->getPostValue('reminderAltCronJob');
		$generalSettingsRepository = new Calendarista_GeneralSettingsRepository();
		if($this->setting->id === -1){
			$result = $generalSettingsRepository->insert($this->setting);
		}else{
			$result = $generalSettingsRepository->update($this->setting);
		}
	}
	function emptyNotification(){
		?>
		<div class="settings error notice is-dismissible">
			<p><?php esc_html_e('There are currently no reminders scheduled in the system', 'calendarista'); ?></p>
		</div>
		<?php
	}
	function resend($result){
		if($result){
			$this->resendNotification();
		}
	}
	function clearSchedules($result){
		if($result){
			$this->clearSchedulesNotification();
		}
	}
	function save($result){
		if($result){
			$this->saveNotification($result);
		}
	}
	function delete($result){
		if($result){
			$this->deleteNotification();
		}
	}
	function deleteAll($result){
		if($result){
			$this->deleteAllNotification();
		}
	}
	public function resendNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('A notification has been sent again', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function clearSchedulesNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('All schedules have been cleared', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function saveNotification($project) {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php echo sprintf(__('The reminder for [%s] service has been saved', 'calendarista'), $project->name) ?></p>
		</div>
		<?php
	}
	public function deleteNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('The notification has been deleted successfully', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function deleteAllNotification() {
		?>
		<div class="settings updated notice is-dismissible">
			<p><?php esc_html_e('All notifications have been deleted successfully', 'calendarista') ?></p>
		</div>
		<?php
	}
	public function render(){
	?>
		<div class="wrap">
			<div class="column-pane">
				<form id="form1" action="<?php echo esc_url($this->requestUrl) ?>" method="post">
					<input type="hidden" name="controller" value="calendarista_reminders">
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
					<button class="button button-primary" name="clearSchedules" id="clearSchedules"
						<?php echo $this->schedulesCount ? '' : 'disabled' ?>>
						<?php esc_html_e('Clear WordPress Cron queue', 'calendarista') ?>
					</button>
					|
					<button class="button button-primary" name="deleteAll" 
						title="<?php esc_html_e('Deletes all the history of reminders sent out by system.', 'calendarista')?>" 
						<?php echo $this->totalRemindersCount ? '' : 'disabled' ?>>
						<?php esc_html_e('Clear history', 'calendarista')?>
					</button>
					|
					<?php esc_html_e('Reminders in queue:', 'calendarista') . ' ' . $this->schedulesCount ?>
				</form>
				<p class="description"><?php esc_html_e('When setting a reminder, it will apply only to appointments made from here on.', 'calendarista') ?></p>
			</div>
			<div class="column-pane">
				<form id="form2" action="<?php echo esc_url($this->requestUrl) ?>" method="post" data-parsley-validate>
					<input type="hidden" name="controller" value="calendarista_reminders">
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
					<p>
						<?php $this->renderProjectSelectList(true, __('Select a service', 'calendarista'), false, false, array(), array(), true) ?>
						<br class="clear">
					</p>
					<p>
					<label class="col-xl-4 form-control-label" for="reminder"><?php esc_html_e('Appointment reminder', 'calendarista') ?></label>
					<input id="reminder" 
						name="reminder" 
						type="text" 
						placeholder="0"
						data-parsley-type="digits"	
						data-parsley-required="true"
						data-parsley-errors-messages-disabled="true"
						class="form-control calendarista_parsley_validated small-text"
						value="<?php echo $this->project->reminder ?>" 
						<?php echo $this->selectedProjectId == -1 ? 'disabled' : '' ?>/> <?php esc_html_e('(minutes)', 'calendarista') ?>
					</p>
					<p class="description">
						 <?php esc_html_e('Applied before appointment begins. Leave 0 to disable.', 'calendarista') ?>
					</p>
					<p>
						<label class="col-xl-4 form-control-label" for="thankyouReminder">
							<?php esc_html_e('Thank you email reminder', 'calendarista') ?>
						</label>
						&nbsp;
						<input id="thankyouReminder" 
						name="thankyouReminder" 
						type="text" 
						placeholder="0"
						data-parsley-type="digits"	
						data-parsley-required="true"
						data-parsley-errors-messages-disabled="true"
						class="form-control calendarista_parsley_validated small-text"
						value="<?php echo $this->project->thankyouReminder ?>" 
						 <?php echo $this->selectedProjectId == -1 ? 'disabled' : '' ?>/> <?php esc_html_e('(minutes)', 'calendarista') ?>
					</p>
					<p class="description">
						<?php esc_html_e('Applied after appointment ends. Leave 0 to disable.', 'calendarista') ?>
					</p>
					<p>
						<button class="button button-primary" name="save"
							<?php echo $this->selectedProjectId != -1 ? '' : 'disabled' ?>>
							<?php esc_html_e('Save', 'calendarista') ?>
						</button>
					</p>
					<br class="clear">
				</form>
				<hr>
				<form id="form1" action="<?php echo esc_url($this->requestUrl) ?>" method="post">
					<input type="hidden" name="controller" value="calendarista_reminder_cron">
					<input type="hidden" name="calendarista_nonce" value="<?php echo wp_create_nonce('calendarista-ajax-nonce') ?>"/>
					<input name="reminderAltCronJob" type="hidden" value="0">
					<p>
						<input id="reminderAltCronJob" name="reminderAltCronJob" 
							type="checkbox" <?php echo $this->setting->reminderAltCronJob ? "checked" : ""?> /> 
						<?php esc_html_e('When checked, a WordPress Cron job is used. Note, this is not a true CRON job.', 'calendarista')?>
					</p>
					<p class="description">
						<?php esc_html_e('For accurate results, uncheck the above option and instead use the following URL. Set the job to run every 1 minute:', 'calendarista')?>
						<br>
						<strong><?php echo esc_url($this->reminderCronJobUrl) ?></strong>
					</p>
					<p>
						<button type="submit" name="calendarista_reminder_cron_update" class="button button-primary">
							<?php esc_html_e('Update WordPress Cron settings', 'calendarista') ?>
						</button>
					</p>
				</form>
			</div>
			<div class="settings info notice">
				<p><?php echo sprintf(__('By default, email reminders are sent. For SMS messages, set up %s.', 'calendarista'), '<a href="'. $this->twilioUrl . '" target="_blank">Twilio</a>') ?></p>
			</div>
			<div  class="column-pane">
				<?php $this->reminderList->display(); ?>
				<br class="clear">
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
				calendarista.reminders = function(options){
					var context = this;
					$(window).ready(function(){
						context.init(options);
					});
				};
				calendarista.reminders.prototype.init = function(options){
					var context = this;
					this.ajaxUrl = wp.url;
					this.nonce = wp.nonce;
					this.requestUrl = options['requestUrl'];
					this.$projectList = $('select[name="projectId"]');
					this.$projectList.on('change', function(e){
						$('#form2').off('submit.Parsley').submit();
					});
				};
			window['calendarista'] = calendarista;
		})(window['jQuery'], window['calendarista_wp_ajax']);
		new calendarista.reminders({<?php echo $this->requestUrl ?>'});
		</script>
	<?php
	}
}