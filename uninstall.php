<?php
/**
 * Uninstall handler.
 *
 * WordPress core only runs this file after the user confirms deletion in
 * wp-admin (Plugins → Delete). We additionally respect a "keep data on
 * uninstall" setting so store data is not lost by accident.
 *
 * @package PMP_2FA_Authentication
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$pmp2fa_settings = get_option( 'pmp2fa_settings', array() );

// Respect the "keep my data" choice — do nothing further if set.
if ( ! empty( $pmp2fa_settings['keep_data_on_uninstall'] ) ) {
	return;
}

global $wpdb;

delete_option( 'pmp2fa_settings' );

// Direct query is required here: uninstall.php runs once, outside any
// cacheable request context, so there is nothing to cache against.
$pmp2fa_user_ids = $wpdb->get_col(
	"SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key IN ('_pmp2fa_trusted','_pmp2fa_token','pmp2fa_phone')" // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
);

foreach ( $pmp2fa_user_ids as $pmp2fa_user_id ) {
	delete_user_meta( $pmp2fa_user_id, '_pmp2fa_trusted' );
	delete_user_meta( $pmp2fa_user_id, '_pmp2fa_token' );
	delete_user_meta( $pmp2fa_user_id, 'pmp2fa_phone' );
}
