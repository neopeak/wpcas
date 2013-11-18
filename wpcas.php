<?php
/*
Plugin Name: Advanced wpCAS
Version: 1.0.0-dev
Plugin URI: https://github.com/neopeak/wpcas
Description: Plugin to integrate WordPress with existing <a href="http://en.wikipedia.org/wiki/Central_Authentication_Service">CAS</a> single sign-on architectures. Forked from <a href="http://maisonbisson.com/projects/wpcas">Casey Bisson</a>'s wpCAS plugin.
Author: Cedric Veilleux
Author URI: http://www.neopeak.com/
*/

/*

 Some changes Copyright (C) 2013 Cedric Veilleux

 Based on the work of Casey Bisson:
  Copyright (C) 2008 Casey Bisson
  http://wordpress.org/extend/plugins/wpcas/

 Which was based on the work of Stephen Schwink
  Copyright (C) 2008 Stephen Schwink
  http://wordpress.org/extend/plugins/cas-authentication/


 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA	 02111-1307	 USA
*/


$error_reporting = error_reporting(0); // hide any warnings when attempting to fetch the optional config file
include_once( dirname(__FILE__).'/wpcas-conf.php' ); // attempt to fetch the optional config file
error_reporting( $error_reporting ); // unhide warnings

// Validate configuration:
$cas_configured = true;
if( !is_array( $wpcas_options ))
	$cas_configured = false;

else if ($wpcas_options['include_path'] == '')
	$cas_configured = false;

else if ((include_once $wpcas_options['include_path']) != true)
	$cas_configured = false;

else if ($wpcas_options['server_hostname'] == '' ||
			$wpcas_options['server_path'] == '' ||
			intval($wpcas_options['server_port']) == 0)
	$cas_configured = false;

// Init phpCAS
if ($cas_configured) {
	phpCAS::client($wpcas_options['cas_version'],
		$wpcas_options['server_hostname'],
		intval($wpcas_options['server_port']),
		$wpcas_options['server_path']);

	if (isset($wpcas_options['gateway_mode_check_times'])) {
		phpCAS::setCacheTimesForAuthRecheck($wpcas_options['gateway_mode_check_times']);
	}

	// TODO: add support for configuring a cert
	phpCAS::setNoCasServerValidation();
}

// plugin hooks into authentication system
add_action('init', array('wpCAS', 'init'));
add_action('wp_authenticate', array('wpCAS', 'authenticate'), 10, 2);
add_action('wp_logout', array('wpCAS', 'logout'));
add_action('lost_password', array('wpCAS', 'disable_function'));
add_action('retrieve_password', array('wpCAS', 'disable_function'));
add_action('check_passwords', array('wpCAS', 'check_passwords'), 10, 3);
add_action('password_reset', array('wpCAS', 'disable_function'));
add_filter('show_password_fields', array('wpCAS', 'show_password_fields'));


class wpCAS {

  function init() {
    global $wpcas_options;

    // gateway mode support
    if ($wpcas_options['gateway_mode']) {
      wpCAS::authenticate(true);
    }
  }

	/*
	 We call phpCAS to authenticate the user at the appropriate time
	 (the script dies there if login was unsuccessful)
	 If the user is not provisioned, 'provision_user_function' is called
	*/
	function authenticate($gatewayMode=false) {
		global $wpcas_options, $cas_configured;

		if ( !$cas_configured )
			die( __( 'wpCAS plugin not configured', 'wpcas' ));

		if( !phpCAS::isAuthenticated() ){
		  // hey, authenticate
		  if ($gatewayMode) {
		    phpCAS::checkAuthentication();
		    return;

		  } else {
		    phpCAS::forceAuthentication();
		    die();

		  }
		}

		if ($gatewayMode && get_current_user_id() > 0) {
			// user is already logged
			return;
		}

		// CAS was successful
		$user = get_userdatabylogin( phpCAS::getUser() );

		if (!$user) {

		  // the CAS user _does_not_have_ a WP account
		  if (!empty($wpcas_options['provision_user_function']) && function_exists( $wpcas_options['provision_user_function']))
		    $user = call_user_func($wpcas_options['provision_user_function'], phpCAS::getUser());

		  if (!$user) {
		    // There is no automated user provisioning or user provisioning refused to create a user
		    die( __( 'you do not have permission here', 'wpcas' ));
		  }

		}

	  // user exists, complete the login:

		// Allow custom user profile sync
		if (!empty($wpcas_options['sync_user_function']) && function_exists( $wpcas_options['sync_user_function']))
			call_user_func($wpcas_options['sync_user_function'], phpCAS::getUser(), phpCAS::getAttributes());

		// reload the user after the sync
		$user = get_userdatabylogin( phpCAS::getUser() );

		if (!$user) {
		  // The sync deleted the user. Fail.
		  die( __( 'you do not have permission here', 'wpcas' ));
		}

		wp_set_auth_cookie( $user->ID );

		// Allow other plugins to act on user login
		do_action('wp_login', $user->user_login, $user);

		if( isset( $_GET['redirect_to'] )){
			wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( $_GET['redirect_to'] ));
			die();
		}

		wp_redirect( $_SERVER["REQUEST_URI"] );
		die();

	}


	// hook CAS logout to WP logout
	function logout() {
		global $cas_configured;

		if (!$cas_configured)
			die( __( 'wpCAS plugin not configured', 'wpcas' ));

		phpCAS::logout( array( 'url' => get_settings( 'siteurl' )));
		exit();
	}

	// hide password fields on user profile page.
	function show_password_fields( $show_password_fields ) {
		return false;
	}

	// disabled reset, lost, and retrieve password features
	function disable_function() {
		die( __( 'Sorry, this feature is disabled.', 'wpcas' ));
	}

	// set the passwords on user creation
	// patched Mar 25 2010 by Jonathan Rogers jonathan via findyourfans.com
	function check_passwords( $user, $pass1, $pass2 ) {
		$random_password = substr( md5( uniqid( microtime( ))), 0, 8 );
		$pass1=$pass2=$random_password;
	}
}


