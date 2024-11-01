<?php
/*
* Please read the license to apply some modify.
*/

if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// drop a custom database table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}notification_setting" );

$option_name = 'Notification-bar';

delete_option( $option_name );
