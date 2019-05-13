<?php
/*
 * Plugin Name: BEA - Prod images
 * Version: 0.1.9
 * Plugin URI: https://beapi.fr
 * Description: This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.
 * Author: Be API
 * Author URI: https://beapi.fr
 * Domain Path: languages
 * Network: false
 *
 * --------------
 * Copyright 2018-2019 - Be API Team (human@beapi.fr)
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
	 * Check for image URL error, and load image as a proxy
	 *
	 * @return void
	 */
	public function __construct() {
		ob_start();

		$_SERVER['_REQUEST_URI'] = untrailingslashit( $_SERVER['REQUEST_URI'] );

		$path_segments = array_filter( array_map( 'trim', explode( ',', UPLOADS_STRUCTURE_NAME ) ), 'strlen' );
		$flag          = false;
		foreach ( $path_segments as $path_segment ) {
			if ( false !== strpos( $_SERVER['_REQUEST_URI'], $path_segment ) ) {
				$flag = true;
				break;
			}
		}

		if ( false === $flag ) {
			return;
		}

		// Fix conflict with WProcket
		define( 'DONOTCACHEPAGE', true );

		// Get extension
		$extension = pathinfo( $_SERVER['_REQUEST_URI'], PATHINFO_EXTENSION );

		// Send content type header
		header( 'Content-Type: ' . $this->get_mime_types( $extension ) );

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
		$args = (array) apply_filters( 'prod_images/remote_get_args', array( 'sslverify' => PROD_SSL_VERIFY ) );
		$url  = apply_filters( 'prod_images/remote_get_url', untrailingslashit( PROD_UPLOADS_URL ) . $_SERVER['_REQUEST_URI'], $_SERVER['_REQUEST_URI'] );

		$response = wp_remote_get( $url, $args );

		// Send content proxy used URL
		header( 'WP-Proxy-URL: ' . $url );

		// Get response code
		$response_code = wp_remote_retrieve_response_code( $response );

		ob_end_clean();

		// Check for error and the response code
		if ( ! is_wp_error( $response ) && 200 === $response_code ) {
			// Parse remote HTML file
			$data = wp_remote_retrieve_body( $response );
			// Check for error
			if ( ! is_wp_error( $data ) ) {
				status_header( 200 );
				echo $data; // phpcs:ignore
				exit();
			}
		}

		header( 'Pragma: public' );
		header( 'Cache-Control: max-age=86400' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + 86400 ) ); // 24 hours
		//header( 'Content-Length: 0' );

		exit();
	}


	/**
	 * Get the MIME type for a file extension.
	 * Copy from --- wp-includes/class-phpmailer.php
	 *
	 * @param string $ext File extension
	 *
	 * @access public
	 * @return string MIME type of file.
	 */
	public function get_mime_types( $ext = '' ) {
		$mimes = array(
			'xl'    => 'application/excel',
			'js'    => 'application/javascript',
			'hqx'   => 'application/mac-binhex40',
			'cpt'   => 'application/mac-compactpro',
			'bin'   => 'application/macbinary',
			'doc'   => 'application/msword',
			'word'  => 'application/msword',
			'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'ppsx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'sldx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'xlam'  => 'application/vnd.ms-excel.addin.macroEnabled.12',
			'xlsb'  => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'class' => 'application/octet-stream',
			'dll'   => 'application/octet-stream',
			'dms'   => 'application/octet-stream',
			'exe'   => 'application/octet-stream',
			'lha'   => 'application/octet-stream',
			'lzh'   => 'application/octet-stream',
			'psd'   => 'application/octet-stream',
			'sea'   => 'application/octet-stream',
			'so'    => 'application/octet-stream',
			'oda'   => 'application/oda',
			'pdf'   => 'application/pdf',
			'ai'    => 'application/postscript',
			'eps'   => 'application/postscript',
			'ps'    => 'application/postscript',
			'smi'   => 'application/smil',
			'smil'  => 'application/smil',
			'mif'   => 'application/vnd.mif',
			'xls'   => 'application/vnd.ms-excel',
			'ppt'   => 'application/vnd.ms-powerpoint',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc'  => 'application/vnd.wap.wmlc',
			'dcr'   => 'application/x-director',
			'dir'   => 'application/x-director',
			'dxr'   => 'application/x-director',
			'dvi'   => 'application/x-dvi',
			'gtar'  => 'application/x-gtar',
			'php3'  => 'application/x-httpd-php',
			'php4'  => 'application/x-httpd-php',
			'php'   => 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
			'phps'  => 'application/x-httpd-php-source',
			'swf'   => 'application/x-shockwave-flash',
			'sit'   => 'application/x-stuffit',
			'tar'   => 'application/x-tar',
			'tgz'   => 'application/x-tar',
			'xht'   => 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'zip'   => 'application/zip',
			'mid'   => 'audio/midi',
			'midi'  => 'audio/midi',
			'mp2'   => 'audio/mpeg',
			'mp3'   => 'audio/mpeg',
			'mpga'  => 'audio/mpeg',
			'aif'   => 'audio/x-aiff',
			'aifc'  => 'audio/x-aiff',
			'aiff'  => 'audio/x-aiff',
			'ram'   => 'audio/x-pn-realaudio',
			'rm'    => 'audio/x-pn-realaudio',
			'rpm'   => 'audio/x-pn-realaudio-plugin',
			'ra'    => 'audio/x-realaudio',
			'wav'   => 'audio/x-wav',
			'bmp'   => 'image/bmp',
			'gif'   => 'image/gif',
			'jpeg'  => 'image/jpeg',
			'jpe'   => 'image/jpeg',
			'jpg'   => 'image/jpeg',
			'png'   => 'image/png',
			'tiff'  => 'image/tiff',
			'tif'   => 'image/tiff',
			'eml'   => 'message/rfc822',
			'css'   => 'text/css',
			'html'  => 'text/html',
			'htm'   => 'text/html',
			'shtml' => 'text/html',
			'log'   => 'text/plain',
			'text'  => 'text/plain',
			'txt'   => 'text/plain',
			'rtx'   => 'text/richtext',
			'rtf'   => 'text/rtf',
			'vcf'   => 'text/vcard',
			'vcard' => 'text/vcard',
			'xml'   => 'text/xml',
			'xsl'   => 'text/xml',
			'mpeg'  => 'video/mpeg',
			'mpe'   => 'video/mpeg',
			'mpg'   => 'video/mpeg',
			'mov'   => 'video/quicktime',
			'qt'    => 'video/quicktime',
			'rv'    => 'video/vnd.rn-realvideo',
			'avi'   => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie',
			'svg'   => 'image/svg+xml', // Added by BeAPI
		);

		if ( array_key_exists( strtolower( $ext ), $mimes ) ) {
			return $mimes[ strtolower( $ext ) ];
		}

		return 'application/octet-stream';
	}
}

new Prod_Images();
