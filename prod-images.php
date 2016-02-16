<?php
/*
 * Plugin Name: BEA - Prod images
 * Version: 0.1
 * Plugin URI: http://www.beapi.fr
 * Description: This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Domain Path: languages
 * Network: false
 *
 * --------------
 * Copyright 2015 - BeAPI Team (technique@beapi.fr)
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'UPLOADS_STRUCTURE_NAME' ) ) {
	define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/uploads' );
}

if ( ! defined( 'PROD_UPLOADS_URL' ) ) {
	define( 'PROD_UPLOADS_URL', 'http://myproddomain' );
}

/**
 * Class Prod_Images
 */
class Prod_Images {

	/**
	 * @return bool
	 */
	public function replace_url() {

		if ( false === strpos( $_SERVER['REQUEST_URI'], UPLOADS_STRUCTURE_NAME ) ) {
			return false;
		}

		// Get extension
		$extension = pathinfo( $_SERVER['REQUEST_URI'], PATHINFO_EXTENSION );

		// Send content type header
		header( 'Content-Type: ' . $this->get_mime_type_from_file_extension( $extension ) );

		// Get remote HTML file
		$response = wp_remote_get( untrailingslashit( PROD_UPLOADS_URL ) . $_SERVER['REQUEST_URI'] );

		// Get response code
		$response_code = wp_remote_retrieve_response_code( $response );

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

		header( 'Content-Length: 0' );
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