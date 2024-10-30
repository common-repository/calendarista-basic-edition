<?php
class Calendarista_Style extends Calendarista_EntityBase{
	public $id = -1;
	public $projectId = -1; 
	public $fontFamily;
	public $theme = 'none';
	public $mainColor = 'rgba(0,0,0,.03);';
	public $summaryBgColor;
	public $summaryBorderColor;
	public $summaryColor;
	public $tabBorderColor;
	public $buttonColor;
	public $buttonBgColor;
	public $buttonBorderColor;
	public $buttonBgHoverColor;
	public $buttonBorderHoverColor;
	public $buttonBgFocusColor;
	public $buttonBorderFocusColor;
	public $buttonDisabledBgColor;
	public $buttonBorderDisabledColor;
	public $buttonBoxShadow;
	public $highlight;
	public $disableStateColor;
	public $navItemBackgroundColor;
	public $navItemBorderColor;
	public $navItemActiveColor;
	public $navItemDisabledColor;
	public $navItemHoverColor;
	public $navItemFontColor;
	public $buttonOutlineBgColor;
	public $buttonOutlineBorderColor;
	public $thumbnailBorderWidth = 1;
	public $thumbnailBorderColor;
	public $roundedThumbnail = true;
	public $enableThumbnailShadow = true;
	public $thumbnailWidth = 180;
	public $thumbnailHeight = 180;
	public $numberIndicatorBackground;
	public $numberIndicatorColor;
	public $selectedNumberIndicatorBackground;
	public $selectedNumberIndicatorColor;
	public $selectedDayBgColor;
	public $partiallyThemed;
	public $bookingSummaryTemplate = <<<EOT
	<div class="calendarista-summary-body">
		<div class="calendarista-summary-info alert alert-primary">
			<div class="calendarista-typography--subtitle1">
				<i class="fa fa-calendar"></i>
				{{{booking_date}}}
				{{#if_has_nights}}
					<span class="badge text-bg-secondary">{{nights_label}}</span>
				{{/if_has_nights}}
				{{#if_has_seats}}
					<span class="calendarista-seats-summary">
						—{{seats_summary}}
					</span>
				{{/if_has_seats}}
			</div>
			{{#if_has_customer_name_email}}
			<div class="calendarista-customer-info calendarista-typography--subtitle4">
			<i class="fa fa-address-card"></i>
			{{customer_name_email}}
			</div>
			{{/if_has_customer_name_email}}
			{{#if_has_multiple_availability}}
			<div class="calendarista-availability-list">
				<span class="calendarista-typography--caption1">{{{availability_list}}}</span>
			</div>
			{{/if_has_multiple_availability}}
		</div>
		<table class="table mt-3 calendarista-summary-table">
			<tbody>
			{{#if_has_dynamic_fields}}
				{{{dynamic_fields}}}
			{{/if_has_dynamic_fields}}
			{{#if_has_extend_next_day}}
				<tr class="calendarista-extend-next-day">
					<td class="calendarista-typography--caption1" colspan="2">{{extend_next_day_message}}</td>
				</tr>
			{{/if_has_extend_next_day}}
			{{#if_has_from_address}}
				<tr class="calendarista-from-address">
					<td class="calendarista-typography--caption1">{{from_address}}</td>
					<td class="calendarista-typography--caption1 text-end">{{from_address_label}}</td>
				</tr>
			{{/if_has_from_address}}
			{{#if_has_waypoints}}
			{{{stops}}}
			{{/if_has_waypoints}}
			{{#if_has_to_address}}
				<tr class="calendarista-to-address">
					<td class="calendarista-typography--caption1">{{to_address}}</td>
					<td class="calendarista-typography--caption1 text-end">{{to_address_label}}</td>
				</tr>
			{{/if_has_to_address}}
			{{#if_has_distance}}
				<tr class="calendarista-distance">
					<td class="calendarista-typography--caption1">{{distance_label}}</td>
					<td class="calendarista-typography--caption1 text-end">{{distance}} {{unitType}}</td>
				</tr>
			{{/if_has_distance}}
			{{#if_has_duration}}
				<tr class="calendarista-duration">
					<td class="calendarista-typography--caption1">{{duration_label}}</td>
					<td class="calendarista-typography--caption1 text-end">{{duration}}</td>
				</tr>
			{{/if_has_duration}}
			{{#if_has_optionals}}
				{{{optionals}}}
			{{/if_has_optionals}}
			{{#if_has_custom_form_fields}}
				<tr class="calendarista-custom-form-fields">
					<td class="calendarista-typography--caption1" colspan="2">{{{custom_form_fields}}}</td>
				</tr>
			{{/if_has_custom_form_fields}}
			{{#if_include_total_time}}
				<tr class="calendarista-total-time">
					<td class="calendarista-typography--caption1">{{total_time_label}}</td>
					<td class="calendarista-typography--caption1 text-end">{{total_time}}</td>
				</tr>
			{{/if_include_total_time}}
			{{#if_has_total_amount}}
				{{#if_has_base_cost}}
					<tr class="calendarista-base-cost">
						<td class="calendarista-typography--caption1">{{base_cost_label}}</td>
						<td class="calendarista-typography--caption1 text-end">{{{base_cost}}}</td>
					</tr>
				{{/if_has_base_cost}}
				{{#if_has_selected_date_list}}
					{{{selected_date_list}}}
				{{/if_has_selected_date_list}}
				{{#if_has_subtotal}}
				<tr class="calendarista-subtotal-amount">
					<td class="calendarista-typography--caption1">{{subtotal_amount_label}}</td>
					<td class="calendarista-typography--caption1 text-end">{{{subtotal_amount}}}</td>
				</tr>
				{{/if_has_subtotal}}
				{{#if_has_custom_charge}}
				<tr class="calendarista-custom-charge">
					<td class="calendarista-typography--caption1">{{custom_charge_label}}</td>
					<td class="calendarista-typography--caption1 text-end">{{{custom_charge_amount}}}</td>
				</tr>
				{{/if_has_custom_charge}}
				{{#if_has_discount}}
					<tr class="calendarista-discount">
						<td class="calendarista-typography--caption1">{{discount_label}}</td>
						<td class="calendarista-typography--caption1 text-end">{{{discount}}}</td>
					</tr>
				{{/if_has_discount}}
				{{#if_has_tax}}
					<tr class="calendarista-tax">
						<td class="calendarista-typography--caption1">{{tax_label}}</td>
						<td class="calendarista-typography--caption1 text-end">{{tax}}%</td>
					</tr>
				{{/if_has_tax}}
				{{#if_has_deposit}}
					<tr class="calendarista-deposit">
						<td class="calendarista-typography--caption1">{{balance_label}}({{balance_pay_on_arrival}})</td>
						<td class="calendarista-typography--caption1 text-end">{{{balance}}}</td>
					</tr>
				{{/if_has_deposit}}
				<tr class="calendarista-total-amount">
					<td class="calendarista-typography--caption1">
						{{total_amount_label}}
						{{#if_has_deposit}} ({{deposit_label}}) {{/if_has_deposit}}
					</td>
					<td class="calendarista-typography--caption1 text-end">{{{total_amount}}}</td>
				</tr>
				{{#if_paid_upfront_full_amount}}
					<tr class="calendarista-upfront-payment">
						<td class="calendarista-typography--caption1">{{{upfront_payment_message}}}</td>
						<td class="calendarista-typography--caption1 text-end">{{{upfront_payment_total}}}</td>
					</tr>
				{{/if_paid_upfront_full_amount}}
			{{/if_has_total_amount}}
			</tbody>
		</table>
	</div>
EOT;
	public function __construct($args){
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if(array_key_exists('projectId', $args) && isset($args['projectId'])){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('theme', $args)){
			$this->theme = $args['theme'];
		}
		if(array_key_exists('fontFamily', $args) && isset($args['fontFamily'])){
			$this->fontFamily = stripslashes($args['fontFamily']);
		}
		if(array_key_exists('bookingSummaryTemplate', $args) && $args['bookingSummaryTemplate']){
			$this->bookingSummaryTemplate = (string)$args['bookingSummaryTemplate'];
		}
		if(array_key_exists('mainColor', $args) && isset($args['mainColor'])){
			$this->mainColor = (string)$args['mainColor'];
		}
		if(array_key_exists('summaryBgColor', $args) && isset($args['summaryBgColor'])){
			$this->summaryBgColor = (string)$args['summaryBgColor'];
		}
		if(array_key_exists('summaryBorderColor', $args) && isset($args['summaryBorderColor'])){
			$this->summaryBorderColor = (string)$args['summaryBorderColor'];
		}
		if(array_key_exists('summaryColor', $args) && isset($args['summaryColor'])){
			$this->summaryColor = (string)$args['summaryColor'];
		}
		if(array_key_exists('tabBorderColor', $args) && isset($args['tabBorderColor'])){
			$this->tabBorderColor = (string)$args['tabBorderColor'];
		}
		if(array_key_exists('buttonBorderColor', $args) && isset($args['buttonBorderColor'])){
			$this->buttonBorderColor = (string)$args['buttonBorderColor'];
		}
		if(array_key_exists('buttonColor', $args) && isset($args['buttonColor'])){
			$this->buttonColor = (string)$args['buttonColor'];
		}
		if(array_key_exists('buttonBgColor', $args) && isset($args['buttonBgColor'])){
			$this->buttonBgColor = (string)$args['buttonBgColor'];
		}
		if(array_key_exists('buttonBgHoverColor', $args) && isset($args['buttonBgHoverColor'])){
			$this->buttonBgHoverColor = (string)$args['buttonBgHoverColor'];
		}
		if(array_key_exists('buttonBorderHoverColor', $args) && isset($args['buttonBorderHoverColor'])){
			$this->buttonBorderHoverColor = (string)$args['buttonBorderHoverColor'];
		}
		if(array_key_exists('buttonBgFocusColor', $args) && isset($args['buttonBgFocusColor'])){
			$this->buttonBgFocusColor = (string)$args['buttonBgFocusColor'];
		}
		if(array_key_exists('buttonBorderFocusColor', $args) && isset($args['buttonBorderFocusColor'])){
			$this->buttonBorderFocusColor = (string)$args['buttonBorderFocusColor'];
		}
		if(array_key_exists('buttonBoxShadow', $args) && isset($args['buttonBoxShadow'])){
			$this->buttonBoxShadow = (string)$args['buttonBoxShadow'];
		}
		if(array_key_exists('highlight', $args) && isset($args['highlight'])){
			$this->highlight = (string)$args['highlight'];
		}
		if(array_key_exists('disableStateColor', $args) && isset($args['disableStateColor'])){
			$this->disableStateColor = (string)$args['disableStateColor'];
		}
		if(array_key_exists('navItemBackgroundColor', $args) && isset($args['navItemBackgroundColor'])){
			$this->navItemBackgroundColor = (string)$args['navItemBackgroundColor'];
		}
		if(array_key_exists('buttonDisabledBgColor', $args) && isset($args['buttonDisabledBgColor'])){
			$this->buttonDisabledBgColor = (string)$args['buttonDisabledBgColor'];
		}
		if(array_key_exists('buttonBorderDisabledColor', $args) && isset($args['buttonBorderDisabledColor'])){
			$this->buttonBorderDisabledColor = (string)$args['buttonBorderDisabledColor'];
		}
		if(array_key_exists('navItemBorderColor', $args) && isset($args['navItemBorderColor'])){
			$this->navItemBorderColor = (string)$args['navItemBorderColor'];
		}
		if(array_key_exists('navItemActiveColor', $args) && isset($args['navItemActiveColor'])){
			$this->navItemActiveColor = (string)$args['navItemActiveColor'];
		}
		if(array_key_exists('navItemDisabledColor', $args) && isset($args['navItemDisabledColor'])){
			$this->navItemDisabledColor = (string)$args['navItemDisabledColor'];
		}
		if(array_key_exists('navItemHoverColor', $args) && isset($args['navItemHoverColor'])){
			$this->navItemHoverColor = (string)$args['navItemHoverColor'];
		}
		if(array_key_exists('navItemFontColor', $args) && isset($args['navItemFontColor'])){
			$this->navItemFontColor = (string)$args['navItemFontColor'];
		}
		if(array_key_exists('buttonOutlineBgColor', $args) && isset($args['buttonOutlineBgColor'])){
			$this->buttonOutlineBgColor = (string)$args['buttonOutlineBgColor'];
		}
		if(array_key_exists('buttonOutlineBorderColor', $args) && isset($args['buttonOutlineBorderColor'])){
			$this->buttonOutlineBorderColor = (string)$args['buttonOutlineBorderColor'];
		}
		if(array_key_exists('thumbnailBorderWidth', $args) && isset($args['thumbnailBorderWidth'])){
			$this->thumbnailBorderWidth = (int)$args['thumbnailBorderWidth'];
		}
		if(array_key_exists('thumbnailBorderColor', $args) && isset($args['thumbnailBorderColor'])){
			$this->thumbnailBorderColor = (string)$args['thumbnailBorderColor'];
		}
		if(array_key_exists('roundedThumbnail', $args) && isset($args['roundedThumbnail'])){
			$this->roundedThumbnail = (bool)$args['roundedThumbnail'];
		}
		if(array_key_exists('enableThumbnailShadow', $args) && isset($args['enableThumbnailShadow'])){
			$this->enableThumbnailShadow = (bool)$args['enableThumbnailShadow'];
		}
		if(array_key_exists('thumbnailWidth', $args) && isset($args['thumbnailWidth'])){
			$this->thumbnailWidth = (int)$args['thumbnailWidth'];
		}
		if(array_key_exists('thumbnailHeight', $args) && isset($args['thumbnailHeight'])){
			$this->thumbnailHeight = (int)$args['thumbnailHeight'];
		}
		if(array_key_exists('numberIndicatorBackground', $args) && isset($args['numberIndicatorBackground'])){
			$this->numberIndicatorBackground =  $args['numberIndicatorBackground'];
		}
		if(array_key_exists('numberIndicatorColor', $args) && isset($args['numberIndicatorColor'])){
			$this->numberIndicatorColor =  $args['numberIndicatorColor'];
		}
		if(array_key_exists('selectedNumberIndicatorBackground', $args) && isset($args['selectedNumberIndicatorBackground'])){
			$this->selectedNumberIndicatorBackground=  $args['selectedNumberIndicatorBackground'];
		}
		if(array_key_exists('selectedNumberIndicatorColor', $args) && isset($args['selectedNumberIndicatorColor'])){
			$this->selectedNumberIndicatorColor =  $args['selectedNumberIndicatorColor'];
		}
		if(array_key_exists('selectedDayBgColor', $args) && isset($args['selectedDayBgColor'])){
			$this->selectedDayBgColor =  $args['selectedDayBgColor'];
		}
		if(array_key_exists('partiallyThemed', $args)){
			$this->partiallyThemed =  $args['partiallyThemed'];
		}
	}
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'theme'=>$this->theme
			, 'fontFamily'=>$this->fontFamily
			, 'bookingSummaryTemplate'=>$this->bookingSummaryTemplate
			, 'mainColor'=>$this->mainColor
			, 'summaryBgColor'=>$this->summaryBgColor
			, 'summaryBorderColor'=>$this->summaryBorderColor
			, 'summaryColor'=>$this->summaryColor
			, 'tabBorderColor'=>$this->tabBorderColor
			, 'buttonBorderColor'=>$this->buttonBorderColor
			, 'buttonColor'=>$this->buttonColor
			, 'buttonBgColor'=>$this->buttonBgColor
			, 'buttonBgHoverColor'=>$this->buttonBgHoverColor
			, 'buttonBorderHoverColor'=>$this->buttonBorderHoverColor
			, 'buttonBgFocusColor'=>$this->buttonBgFocusColor
			, 'buttonBorderFocusColor'=>$this->buttonBorderFocusColor
			, 'buttonDisabledBgColor'=>$this->buttonDisabledBgColor
			, 'buttonBorderDisabledColor'=>$this->buttonBorderDisabledColor
			, 'buttonBoxShadow'=>$this->buttonBoxShadow
			, 'highlight'=>$this->highlight
			, 'disableStateColor'=>$this->disableStateColor
			, 'navItemBackgroundColor'=>$this->navItemBackgroundColor
			, 'navItemBorderColor'=>$this->navItemBorderColor
			, 'navItemActiveColor'=>$this->navItemActiveColor
			, 'navItemDisabledColor'=>$this->navItemDisabledColor
			, 'navItemHoverColor'=>$this->navItemHoverColor
			, 'navItemFontColor'=>$this->navItemFontColor
			, 'buttonOutlineBgColor'=>$this->buttonOutlineBgColor
			, 'buttonOutlineBorderColor'=>$this->buttonOutlineBorderColor
			, 'thumbnailBorderWidth'=>$this->thumbnailBorderWidth
			, 'thumbnailBorderColor'=>$this->thumbnailBorderColor
			, 'roundedThumbnail'=>$this->roundedThumbnail
			, 'enableThumbnailShadow'=>$this->enableThumbnailShadow
			, 'thumbnailWidth'=>$this->thumbnailWidth
			, 'thumbnailHeight'=>$this->thumbnailHeight
			, 'numberIndicatorBackground'=>$this->numberIndicatorBackground
			, 'numberIndicatorColor'=>$this->numberIndicatorColor
			, 'selectedNumberIndicatorBackground'=>$this->selectedNumberIndicatorBackground
			, 'selectedNumberIndicatorColor'=>$this->selectedNumberIndicatorColor
			, 'selectedDayBgColor'=>$this->selectedDayBgColor
			, 'partiallyThemed'=>$this->partiallyThemed
		);
	}
}
?>