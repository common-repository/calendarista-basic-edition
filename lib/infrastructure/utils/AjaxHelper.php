<?php
class Calendarista_AjaxHelper{
    public static function doingAjax() {
        return defined('DOING_AJAX') && DOING_AJAX;
    }
}
?>