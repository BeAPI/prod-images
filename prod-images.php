<?php
/*
 * Plugin Name: BEA - Prod images
 * Version: 0.1.7
 * Plugin URI: http://www.beapi.fr
 * Description: This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Domain Path: languages
 * Network: false
 *
 * --------------
 * Copyright 2018 - BeAPI Team (human@beapi.fr)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * --------------
 *
 * 		Installation usage for WP multisite
 *
 * 		You need to add the following rule before this line "RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]"
 * 			RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-content/uploads.*) $1 [L]
 *
 *      Warning :  If define( 'WP_HTTP_BLOCK_EXTERNAL', true ); so, define( 'WP_ACCESSIBLE_HOSTS', 'url_prod' );
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'UPLOADS_STRUCTURE_NAME' ) ) {
	define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/uploads, wp-content/blogs.dir' );
}

if ( ! defined( 'PROD_UPLOADS_URL' ) ) {
	define( 'PROD_UPLOADS_URL', 'http://myproddomain' );
}

if ( ! defined( 'PROD_SSL_VERIFY' ) ) {
	define( 'PROD_SSL_VERIFY', true );
}

/**
 * Class Prod_Images
 */
class Prod_Images {

	/**
	 * @return bool
	 */
	public function replace_url() {
		ob_start();

		$_SERVER['_REQUEST_URI'] = untrailingslashit( $_SERVER['REQUEST_URI'] );

		$path_segments = array_filter( array_map( 'trim', explode( ",", UPLOADS_STRUCTURE_NAME ) ), 'strlen' );
		$flag          = false;
		foreach ( $path_segments as $path_segment ) {
			if ( false !== strpos( $_SERVER['_REQUEST_URI'], $path_segment ) ) {
				$flag = true;
				break;
			}
		}

		if ( false === $flag ) {
			return false;
		}

		// Fix conflict with WProcket
		define( 'DONOTCACHEPAGE', true );

		// Get extension
		$extension = pathinfo( $_SERVER['_REQUEST_URI'], PATHINFO_EXTENSION );

		// Send content type header
		header( 'Content-Type: ' . $this->get_mime_type_from_file_extension( $extension ) );

		// Send content proxy name
		header( 'WP-Proxy: prod-images' );

		// Test if is local file for MS subfolder installation.
		$request_uri_parts = explode( '/', ltrim( $_SERVER['_REQUEST_URI'], '/' ) );
		array_shift( $request_uri_parts );
		if ( function_exists( 'is_subdomain_install' ) && ! is_subdomain_install() && is_file( ABSPATH . implode( '/', $request_uri_parts ) ) ) {
			status_header( 200 );
			readfile( ABSPATH . implode( '/', $request_uri_parts ) );
			exit();
		}

		// Get remote media file
		$defaults = array( 'sslverify' => PROD_SSL_VERIFY );
		$args     = (array) apply_filters( 'prod_images/remote_get_args', $defaults );
		$url      = untrailingslashit( PROD_UPLOADS_URL ) . $_SERVER['_REQUEST_URI'];

		$response = wp_remote_get( $url, $args );

		// Send content proxy used URL
		header( 'WP-Proxy-URL: ' . $url );

		// Get response code
		$response_code = wp_remote_retrieve_response_code( $response );

		ob_end_clean();

		// Check for error and the response code
		if ( ! is_wp_error( $response ) && 200 == $response_code ) {
			// Parse remote HTML file
			$data = wp_remote_retrieve_body( $response );
			// Check for error
			if ( ! is_wp_error( $data ) ) {
				status_header( 200 );
				echo $data;
				exit();
			}
		}

		//TODO Improve cache
		header( 'Pragma: public' );
		header( 'Cache-Control: max-age=86400' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + 86400 ) );
		//header( 'Content-Length: 0' );
		exit();
	}

	/**
	 * @param $extension
	 *
	 * @return mixed
	 */
	public function get_mime_type_from_file_extension( $extension ) {
		global $phpmailer;

		// (Re)create it, if it's gone missing
		if ( ! ( $phpmailer instanceof PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new PHPMailer( true );
		}

		return $phpmailer->_mime_types( $extension );
	}
}

$prod_images = new Prod_Images();
$prod_images->replace_url();
