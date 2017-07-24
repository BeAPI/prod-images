<?php
/*
 * Plugin Name: BEA - Prod images
 * Version: 0.1.5
 * Plugin URI: http://www.beapi.fr
 * Description: This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Domain Path: languages
 * Network: false
 *
 * --------------
 * Copyright 2016 - BeAPI Team (human@beapi.fr)
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
		ob_start();

		$_SERVER['_REQUEST_URI'] = untrailingslashit( $_SERVER['REQUEST_URI'] );
		if ( false === strpos( $_SERVER['_REQUEST_URI'], UPLOADS_STRUCTURE_NAME ) ) {
			return false;
		}

		// Fix conflict with WProcket
		define( 'DONOTCACHEPAGE', true );

		// Get extension
		$extension = pathinfo( $_SERVER['_REQUEST_URI'], PATHINFO_EXTENSION );

		// Send content type header
		header( 'Content-Type: ' . $this->get_mime_type_from_file_extension( $extension ) );

		// Test if is local file for MS subfolder installation.
		$request_uri_parts = explode( '/', ltrim( $_SERVER['_REQUEST_URI'], '/' ) );
		array_shift( $request_uri_parts );
		if ( function_exists( 'is_subdomain_install' ) && ! is_subdomain_install() && is_file( ABSPATH . implode( '/', $request_uri_parts ) ) ) {
			status_header( 200 );
			readfile( ABSPATH . implode( '/', $request_uri_parts ) );
			exit();
		}

		// Get remote HTML file
		$response = wp_remote_get( untrailingslashit( PROD_UPLOADS_URL ) . $_SERVER['_REQUEST_URI'] );

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
		 $mimes = array(
            'ai'    => 'application/postscript',
            'aif'   => 'audio/x-aiff',
            'aifc'  => 'audio/x-aiff',
            'aiff'  => 'audio/x-aiff',
            'avi'   => 'video/x-msvideo',
            'bin'   => 'application/macbinary',
            'bmp'   => 'image/bmp',
            'class' => 'application/octet-stream',
            'cpt'   => 'application/mac-compactpro',
            'css'   => 'text/css',
            'dcr'   => 'application/x-director',
            'dir'   => 'application/x-director',
            'dll'   => 'application/octet-stream',
            'dms'   => 'application/octet-stream',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dvi'   => 'application/x-dvi',
            'dxr'   => 'application/x-director',
            'eml'   => 'message/rfc822',
            'eps'   => 'application/postscript',
            'exe'   => 'application/octet-stream',
            'gif'   => 'image/gif',
            'gtar'  => 'application/x-gtar',
            'hqx'   => 'application/mac-binhex40',
            'htm'   => 'text/html',
            'html'  => 'text/html',
            'jpe'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'js'    => 'application/javascript',
            'lha'   => 'application/octet-stream',
            'log'   => 'text/plain',
            'lzh'   => 'application/octet-stream',
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mif'   => 'application/vnd.mif',
            'mov'   => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2'   => 'audio/mpeg',
            'mp3'   => 'audio/mpeg',
            'mpe'   => 'video/mpeg',
            'mpeg'  => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mpga'  => 'audio/mpeg',
            'oda'   => 'application/oda',
            'pdf'   => 'application/pdf',
            'php'   => 'application/x-httpd-php',
            'php3'  => 'application/x-httpd-php',
            'php4'  => 'application/x-httpd-php',
            'phps'  => 'application/x-httpd-php-source',
            'phtml' => 'application/x-httpd-php',
            'png'   => 'image/png',
            'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps'    => 'application/postscript',
            'psd'   => 'application/octet-stream',
            'qt'    => 'video/quicktime',
            'ra'    => 'audio/x-realaudio',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'rtf'   => 'text/rtf',
            'rtx'   => 'text/richtext',
            'rv'    => 'video/vnd.rn-realvideo',
            'sea'   => 'application/octet-stream',
            'shtml' => 'text/html',
            'sit'   => 'application/x-stuffit',
            'sldx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'smi'   => 'application/smil',
            'smil'  => 'application/smil',
            'so'    => 'application/octet-stream',
            'svg'   => 'image/svg+xml',
            'swf'   => 'application/x-shockwave-flash',
            'tar'   => 'application/x-tar',
            'text'  => 'text/plain',
            'tgz'   => 'application/x-tar',
            'tif'   => 'image/tiff',
            'tiff'  => 'image/tiff',
            'txt'   => 'text/plain',
            'vcard' => 'text/vcard',
            'vcf'   => 'text/vcard',
            'wav'   => 'audio/x-wav',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc'  => 'application/vnd.wap.wmlc',
            'word'  => 'application/msword',
            'xht'   => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'xl'    => 'application/excel',
            'xlam'  => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xls'   => 'application/vnd.ms-excel',
            'xlsb'  => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xml'   => 'text/xml',
            'xsl'   => 'text/xml',
            'zip'   => 'application/zip',
        );

        if ( array_key_exists( strtolower( $extension ), $mimes ) ) {
            return $mimes[ strtolower( $extension ) ];
        }

        return 'application/octet-stream';
	}
}

$prod_images = new Prod_Images();
$prod_images->replace_url();
