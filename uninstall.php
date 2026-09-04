<?php
/**
 * Uninstall handler.
 *
 * @package PMP_2FA_Authentication
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$pmp2fa_settings = get_option( 'pmp2fa_settings', array() );

if ( ! empty( $pmp2fa_settings['keep_data_on_uninstall'] ) ) {
	return;
}

delete_option( 'pmp2fa_settings' );

$pmp2fa_meta_keys = array(
	'_pmp2fa_trusted',
	'_pmp2fa_token',
	'pmp2fa_phone',
);

$pmp2fa_users = get_users(
	array(
		'fields'     => 'ID',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => '_pmp2fa_trusted',
			),
			array(
				'key' => '_pmp2fa_token',
			),
			array(
				'key' => 'pmp2fa_phone',
			),
		),
	)
);

foreach ( $pmp2fa_users as $pmp2fa_user_id ) {
	foreach ( $pmp2fa_meta_keys as $pmp2fa_meta_key ) {
		delete_user_meta( $pmp2fa_user_id, $pmp2fa_meta_key );
	}
}
