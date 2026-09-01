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

$settings = get_option( 'pmp2fa_settings', array() );

// Respect the "keep my data" choice — do nothing further if set.
if ( ! empty( $settings['keep_data_on_uninstall'] ) ) {
	return;
}

global $wpdb;

delete_option( 'pmp2fa_settings' );

$user_ids = $wpdb->get_col(
	"SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key IN ('_pmp2fa_trusted','_pmp2fa_token','pmp2fa_phone')"
);

foreach ( $user_ids as $user_id ) {
	delete_user_meta( $user_id, '_pmp2fa_trusted' );
	delete_user_meta( $user_id, '_pmp2fa_token' );
	delete_user_meta( $user_id, 'pmp2fa_phone' );
}
