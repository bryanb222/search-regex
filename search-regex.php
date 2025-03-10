<?php
/*
Plugin Name: Search Regex
Plugin URI: https://searchregex.com/
Description: Adds search and replace functionality across posts, pages, comments, and meta-data, with full regular expression support
Version: 3.0-beta-3
Author: John Godley
Text Domain: search-regex
Domain Path: /locale
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages(including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort(including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================
*/

define( 'SEARCHREGEX_FILE', __FILE__ );
define( 'SEARCHREGEX_DEV_MODE', false );
define( 'SEARCHREGEX_DEBUG', false );

// This file must support PHP < 5.6 so as not to crash
if ( version_compare( phpversion(), '5.6' ) < 0 ) {
	add_action( 'plugin_action_links_' . basename( dirname( SEARCHREGEX_FILE ) ) . '/' . basename( SEARCHREGEX_FILE ), 'searchregex_deprecated_php', 10, 4 );

	/**
	 * Show a deprecated PHP warning in the plugin page
	 *
	 * @param Array $links Plugin links.
	 * @return Array
	 */
	function searchregex_deprecated_php( $links ) {
		/* translators: 1: server PHP version. 2: required PHP version. */
		array_unshift( $links, '<a href="https://searchregex.com/support/problems/php-version/" style="color: red; text-decoration: underline">' . sprintf( __( 'Disabled! Detected PHP %1$s, need PHP %2$s+', 'search-regex' ), phpversion(), '5.6' ) . '</a>' );
		return $links;
	}

	return;
}

require_once __DIR__ . '/search-regex-version.php';
require_once __DIR__ . '/search-regex-settings.php';
require_once __DIR__ . '/search-regex-capabilities.php';

/**
 * Is the request for WP CLI?
 *
 * @return Bool
 */
function searchregex_is_wpcli() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	return false;
}

/**
 * Is the request for Search Regex admin?
 *
 * @return Bool
 */
function searchregex_is_admin() {
	if ( is_admin() ) {
		return true;
	}

	return false;
}

/**
 * Start the Search Regex REST API
 *
 * @return void
 */
function searchregex_start_rest() {
	require_once __DIR__ . '/search-regex-admin.php';
	require_once __DIR__ . '/api/api.php';

	Search_Regex_Api::init();
	Search_Regex_Admin::init();

	remove_action( 'rest_api_init', 'searchregex_start_rest' );
}

/**
 * Set the Search Regex text domain
 *
 * @return void
 */
function searchregex_locale() {
	/** @psalm-suppress PossiblyFalseArgument */
	load_plugin_textdomain( 'search-regex', false, dirname( plugin_basename( SEARCHREGEX_FILE ) ) . '/locale/' );
}

if ( searchregex_is_admin() || searchregex_is_wpcli() ) {
	require_once __DIR__ . '/search-regex-admin.php';
	require_once __DIR__ . '/api/api.php';
}

if ( searchregex_is_wpcli() ) {
	require_once __DIR__ . '/search-regex-cli.php';
}

add_action( 'rest_api_init', 'searchregex_start_rest' );
add_action( 'init', 'searchregex_locale' );
