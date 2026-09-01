<?php
/**
 * 2FA Modal Overlay — injected into wp_footer on the existing login page.
 *
 * The site's own header, footer, and branding remain visible behind a
 * frosted-glass backdrop. A centred card contains the OTP form.
 *
 * Variables provided by pmp2fa_render_2fa_modal():
 *   $user         WP_User
 *   $settings     array
 *   $method       string  'email' | 'sms'
 *   $show_both    bool
 *   $has_phone    bool
 *   $remember_opt bool
 *   $expiry       int     minutes
 *   $masked       string
 *   $otp_length   int
 *   $nonce        string
 *   $cancel_url   string
 *   $ajax_url     string
 *   $site_name    string
 *   $site_url     string
 *   $logo_url     string
 *
 * @package PMP_2FA_Authentication
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<!-- PMP 2FA Authentication – modal overlay -->


<div id="pmp2fa-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="pmp2fa-modal-title">
	<div class="pmp2fa-modal">

		<!-- Top accent stripe -->
		<div class="pmp2fa-modal__stripe" aria-hidden="true"></div>

		<div class="pmp2fa-modal__body">

			<!-- Brand row -->
			<div class="pmp2fa-modal__brand">
				<a href="<?php echo esc_url( $site_url ); ?>" style="display:flex;align-items:center;text-decoration:none;">
					<?php if ( $logo_url ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" class="pmp2fa-modal__logo-img">
					<?php else : ?>
						<span class="pmp2fa-modal__logo-text"><?php echo esc_html( $site_name ); ?></span>
					<?php endif; ?>
				</a>
				<div class="pmp2fa-modal__brand-sep" aria-hidden="true"></div>
				<div class="pmp2fa-modal__brand-tag">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
					</svg>
					2-Step Verification
				</div>
			</div>

			<!-- Heading -->
			<h2 id="pmp2fa-modal-title" class="pmp2fa-modal__title"><?php esc_html_e( 'Check your inbox', 'pmp-2fa-authentication' ); ?></h2>
			<p class="pmp2fa-modal__subtitle" id="pmp2fa-dest-msg">
				<?php
				/* translators: %s: masked email address or phone number */
				printf(
					esc_html__( 'We sent a verification code to %s', 'pmp-2fa-authentication' ),
					'<strong>' . esc_html( $masked ) . '</strong>'
				);
				?>
			</p>

			<!-- Notice -->
			<div id="pmp2fa-notice" class="pmp2fa-notice" role="alert" aria-live="polite" aria-atomic="true"></div>

			<!-- Method tabs -->
			<?php if ( $show_both && $has_phone ) : ?>
			<div class="pmp2fa-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Delivery method', 'pmp-2fa-authentication' ); ?>">
				<button
					type="button"
					class="pmp2fa-tab<?php echo 'email' === $method ? ' is-active' : ''; ?>"
					data-method="email"
					role="tab"
					aria-selected="<?php echo 'email' === $method ? 'true' : 'false'; ?>"
				>
					&#9993;&nbsp; <?php esc_html_e( 'Email', 'pmp-2fa-authentication' ); ?>
				</button>
				<button
					type="button"
					class="pmp2fa-tab<?php echo 'sms' === $method ? ' is-active' : ''; ?>"
					data-method="sms"
					role="tab"
					aria-selected="<?php echo 'sms' === $method ? 'true' : 'false'; ?>"
				>
					&#128241;&nbsp; <?php esc_html_e( 'SMS', 'pmp-2fa-authentication' ); ?>
				</button>
			</div>
			<?php endif; ?>

			<!-- OTP form -->
			<form id="pmp2fa-form" novalidate>
				<input type="hidden" name="action" value="pmp2fa_verify_otp">
				<input type="hidden" name="nonce"  value="<?php echo esc_attr( $nonce ); ?>">

				<div class="pmp2fa-field">
					<label for="pmp2fa-otp" class="pmp2fa-label"><?php esc_html_e( 'Verification Code', 'pmp-2fa-authentication' ); ?></label>
					<div class="pmp2fa-otp-wrap">
						<input
							type="text"
							id="pmp2fa-otp"
							name="otp"
							class="pmp2fa-otp-input"
							inputmode="numeric"
							autocomplete="one-time-code"
							maxlength="<?php echo esc_attr( $otp_length ); ?>"
							placeholder="<?php echo esc_attr( str_repeat( '·', $otp_length ) ); ?>"
							autofocus
							required
							spellcheck="false"
							autocorrect="off"
						>
					</div>
					<p class="pmp2fa-hint">
						<?php
						printf(
							/* translators: %d: OTP expiry in minutes */
							esc_html( _n( 'Expires in %d minute', 'Expires in %d minutes', $expiry, 'pmp-2fa-authentication' ) ),
							absint( $expiry )
						);
						?>
					</p>
				</div>

				<?php if ( $remember_opt ) : ?>
				<label class="pmp2fa-check-label">
					<input type="checkbox" name="remember_device" value="1">
					<?php
					printf(
						/* translators: %d: number of days */
						esc_html( _n( 'Trust this device for %d day', 'Trust this device for %d days', (int) $settings['remember_days'], 'pmp-2fa-authentication' ) ),
						(int) $settings['remember_days']
					);
					?>
				</label>
				<?php endif; ?>

				<button type="submit" id="pmp2fa-submit" class="pmp2fa-btn-primary">
					<span class="pmp2fa-btn-text"><?php esc_html_e( 'Verify Code', 'pmp-2fa-authentication' ); ?></span>
					<span class="pmp2fa-spinner" aria-hidden="true"></span>
				</button>
			</form>

			<!-- Footer actions -->
			<div class="pmp2fa-modal__footer">
				<button type="button" id="pmp2fa-resend" class="pmp2fa-link-btn" disabled>
					<?php esc_html_e( 'Resend Code', 'pmp-2fa-authentication' ); ?><span id="pmp2fa-countdown"></span>
				</button>
				<span class="pmp2fa-divider-dot" aria-hidden="true"></span>
				<a id="pmp2fa-cancel" href="<?php echo esc_url( $cancel_url ); ?>" class="pmp2fa-back-link">
					&larr; <?php esc_html_e( 'Back to login', 'pmp-2fa-authentication' ); ?>
				</a>
			</div>

		</div><!-- /.pmp2fa-modal__body -->

		<!-- Secure footer -->
		<div class="pmp2fa-modal__secure" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
			</svg>
			<?php esc_html_e( 'Secured by PMP 2FA Authentication', 'pmp-2fa-authentication' ); ?>
		</div>

	</div><!-- /.pmp2fa-modal -->
</div><!-- /#pmp2fa-modal-backdrop -->

