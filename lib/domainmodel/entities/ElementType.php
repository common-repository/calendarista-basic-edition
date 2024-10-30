<?php
class Calendarista_ElementType{
	const TEXTBOX = 0;
	const TEXTAREA = 1;
	const DROPDOWNLIST = 2;
	const MULTISELECT = 3;
	const CHECKBOX = 4;
	const RADIOBUTTON = 5;
	const PLAINTEXT = 6;
	const TERMS = 7;
	const PHONE = 8;
	
	public static function toArray() {
	return array(
			__('Textbox', 'calendarista')
			, __('Textarea', 'calendarista')
			, __('Dropdown list', 'calendarista')
			, __('Multi select list', 'calendarista')
			, __('Checkbox', 'calendarista')
			, __('Radio button', 'calendarista')
			, __('Plain text', 'calendarista')
			, __('Terms and conditions', 'calendarista')
			, __('Phone number', 'calendarista')
		);
    }
}
?>