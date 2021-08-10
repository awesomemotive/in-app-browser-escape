<?php
/**
 * Plugin Name:  Escape Facebook/Instagram In-App Browsers
 * Description:  Site visitors using Facebook/Instagram in-app browsers on Android devices will be prompted open a link in external browser.
 * Author:       WPForms
 * Author URI:   https://wpforms.com
 * Version:      1.0.2
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Break out of Instagram/Facebook in-app browser on Android.
 *
 * @since 1.0.0
 */
add_action(
	'template_redirect',
	static function () {

		if ( ! in_breakout_mode() ) {
			return;
		}

		if ( in_breakout_mode() && is_inapp_browser() ) {
			header( 'Content-type: image/gif' );
			header( 'Content-Disposition: attachment; filename=out.gif' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Accept-Ranges: bytes' );

			echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw=='); // phpcs:ignore

            exit;
		}

		if ( in_breakout_mode() && ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore
			nocache_headers();
			wp_safe_redirect( esc_url_raw( $_GET['redirect_to'] ) ); // phpcs:ignore

			exit;
		}

	}
);

/**
 * Register custom query var for identifying Instagram/Facebook.
 *
 * @since 1.0.0
 *
 * @param array $query_vars Allowlist of query variables.
 *
 * @return array Updated list.
 */
add_filter(
	'query_vars',
	static function ( $query_vars ) {

		$query_vars[] = 'in-app';

		return $query_vars;
	}
);

/**
 * Register custom rewrite rule to redirect Instagram/Facebook in-app browser to.
 *
 * @since 1.0.0
 */
add_action(
	'init',
	static function () {

		add_rewrite_rule( '^in-app?','index.php?in-app=1','top' );
	}
);

/**
 * Flush rewrite rules on plugin activation.
 */
register_activation_hook(
    __FILE__,
    static function () {

	    delete_option( 'rewrite_rules' );
    }
);

/**
 * Print a simple redirect script for Instagram/Facebook in-app browser on Android.
 *
 * @since 1.0.0
 */
add_action(
	'wp_head',
	static function () {
		?>
		<script>
	        if( navigator.userAgent.indexOf('Android') !== -1 && ( navigator.userAgent.indexOf('Instagram') !== -1 || navigator.userAgent.match(/\bFB[\w_]+\//) !== null ) ) {
	            var originalLocation = window.location.href;
	            window.location.href = '<?php echo esc_url_raw( home_url( 'in-app/?redirect_to=' ) ); ?>' + originalLocation;
	        }
		</script>
		<?php
	}
);

/**
 * Is it Instagram/Facebook in-app browser on Android?
 *
 * @since 1.0.0
 *
 * @return bool
 */
function is_inapp_browser() {

	// phpcs:disable
	return isset( $_SERVER['HTTP_USER_AGENT'] )
		&& strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' )
		&& ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Instagram' ) || preg_match( '/\bFB[\w_]+\//', $_SERVER['HTTP_USER_AGENT'] ) );
	// phpcs:enable
}

/**
 * Are we in the break-out of in-app browser redirect?
 *
 * @since 1.0.0
 *
 * @return bool
 */
function in_breakout_mode() {

	return get_query_var( 'in-app' ) !== '';
}
