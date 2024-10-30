<?php
class Calendarista_OptionalDisplayMode{
	const CHECKBOX_LIST = 0;
	const RADIOBUTTON_LIST = 1;
	const DROPDOWNLIST = 2;
	const MULTI_SELECTION_LISTBOX = 3;
	const INCREMENTAL_INPUT = 4;

	public static function toArray(){
		return array(
			__('Checkbox list', 'calendarista')
			, __('Radiobutton list', 'calendarista')
			, __('Dropdownlist', 'calendarista')
			, __('Multi selection listbox', 'calendarista')
			, __('Incremental Input', 'calendarista')
		);
	}
}
?>