<?php
/**
 * WooCommerce check class.
 */

namespace Niteo\WooCart\WooDash;

/**
 * Check if the WooCommerce plugin is active or not and render the admin
 * message accordingly.
 *
 * @package Niteo\WooCart\WooDash
 */
class WooCheck {

	/**
	 * Helper function to determine whether a plugin is active.
	 *
	 * @param string $plugin_name plugin name, as the plugin-filename.php
	 * @return boolean true if the named plugin is installed and active
	 * @since 1.0.0
	 */
	public static function is_plugin_active( $plugin_name ) {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
		}

		$plugin_filenames = array();

		foreach ( $active_plugins as $plugin ) {
			if ( false !== strpos( $plugin, '/' ) ) {
				// Plugin name (plugin-dir/plugin-filename.php).
				list( , $filename ) = explode( '/', $plugin );
			} else {
				// No directory, just plugin file.
				$filename = $plugin;
			}

			$plugin_filenames[] = $filename;
		}

		return in_array( $plugin_name, $plugin_filenames );
	}

	/**
	 * Renders a notice when WooCommerce version is outdated.
	 *
	 * @since 1.0.0
	 */
	public static function inactive_notice() {
		$message = sprintf(
			/* translators: %1$s - <strong>, %2$s - </strong>, %3$s - <a>, %4$s - version number, %5$s - </a> */
			esc_html__( '%1$sCustomize Woo%2$s won\'t work properly as it requires WooCommerce. Please %3$sactivate%4$s WooCommerce version %5$s or newer.', 'woodash' ),
			'<strong>',
			'</strong>',
			'<a href="' . admin_url( 'plugins.php' ) . '">',
			'</a>',
			Config::MIN_WC_VERSION
		);

		printf( '<div class="error"><p>%s</p></div>', $message );
	}

}
