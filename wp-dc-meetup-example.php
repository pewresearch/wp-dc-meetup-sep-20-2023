<?php
/**
 * Plugin Name:  WP DC Meetup "Synced Entity" Example
 * Plugin URI:   https://github.com/pewresearch/wp-dc-meetup-sep-20-2023
 * Description:  WP Crontrol enables you to view and control what's happening in the WP-Cron system.
 * Author:       Seth Rubenstein
 * Author URI:   https://sethrubenstein.info
 * Version:      1.0.0
 * Text Domain:  wp-dc-meetup-example
 * Requires PHP: 8.1
 * License:      GPL v2 or later
 *
 * ## LICENSE
 *
 * WP DC Meetup "Synced Entity" Example is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package    wp-dc-meetup-synced-entity-example
 * @author     Seth Rubenstein <srubenstein@pewresearch.org>
 * @copyright  Copyright 2023 Seth Rubenstein
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GPL 2.0
 * @link       https://github.com/pewresearch/wp-dc-meetup-sep-20-2023
 */

namespace WP_DC_EXAMPLE;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! version_compare( PHP_VERSION, '8.1', '>=' ) ) {
	return new WP_Error( 'php_version', 'WP DC Meetup "Synced Entity" Example requires PHP 8.1 or higher.' );
}

function init() {
	register_post_meta(
		'post',
		'example_synced_field',
		array(
			'single'        => true,
			'type'          => 'string',
			'show_in_rest'  => true,
			'auth_callback' => function() {
				// Right now I am explicitly only trusting users that can delete objects to be able to explore this functionality. I suggest you do the same; until greater editorial controls and a visual revisions interface that your users can understand comes online.
				return current_user_can( 'delete_posts' );
			},
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\init' );

function register_post_parent_to_post() {
	register_rest_field(
		'post',
		'post_parent',
		array(
			'get_callback' => function( $object ) {
				$post_id = (int) ( array_key_exists('id', $object) ? $object['id'] : $object['ID'] );
				return wp_get_post_parent_id( $post_id );
			},
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_post_parent_to_post' );

/**
 * @hook enqueue_block_editor_assets
 * @return WP_Error|true
 */
function register_assets() {
	$asset_file  = include(  plugin_dir_path( __FILE__ )  . 'build/index.asset.php' );
	$script_src  = plugin_dir_url( __FILE__ ) . 'build/index.js';

	$script = wp_register_script(
		'wp-dc-meetup-example',
		$script_src,
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	if ( ! $script ) {
		return new WP_Error( 404, 'Failed to register all assets for wp-dc-meetup-example' );
	}
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\register_assets' );

/**
 * Enqueue the assets for this block editor plugin.
 * @hook enqueue_block_editor_assets
 * @return void
 */
function enqueue_assets() {
	$registered = wp_script_is( 'wp-dc-meetup-example', 'registered' );
	if ( is_admin() && !! $registered) {
		$screen = get_current_screen();
		if ( in_array( $screen->post_type, array('post') ) ) {
			wp_enqueue_script( 'wp-dc-meetup-example' );
		}
	}
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_assets' );
