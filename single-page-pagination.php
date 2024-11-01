<?php
/**
 * @package single-page-pagination
 * @version 1.0.4
 * 
 * 
 * Plugin Name: Single Page Pagination
 * Plugin URI: http://wordpress.org/plugins/single-page-pagination/
 * Description: A simple plugin that displays next and previous links on single post pages.
 * Author: onest.by
 * Version: 1.0.4
 * Text Domain: leafer
 * License: GPLv2 or later
 */

include plugin_dir_path(__FILE__) . 'single-page-pagination-admin.php';
include plugin_dir_path(__FILE__) . 'single-page-pagination-front.php';

if ( is_admin() ) {
    $leafer_settings_page = new LeaferSettingsPage();
} else {
    $leafer_front= new LeaferFront();
}
