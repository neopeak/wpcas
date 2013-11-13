<?php
/*
// Required configuration file for wpCAS plugin
*/


// the configuration array
$wpcas_options = array(
	'cas_version' => '2.0',
	'include_path' => 'phpCAS/source/CAS.php',
	'server_hostname' => 'server.university.edu',
	'server_port' => '443',
	'server_path' => '/url-path/',
	'gateway_mode' => false, // True to attempt auto-login (CAS gateway)
	'gateway_mode_check_times' => 0, // See phpCas::setCacheTimesForAuthRecheck(). -1 means check only once. 0, always check, n, check every n times.
	'provision_user_function' => '', // Put the name of your user provisioning function, example "wpcas_provision_user"
	'sync_user_function' => '' // Put the name of a sync user function that gets call at each successful login
	);

// This is an example user provisioning function. It is never called unless you set the provision_user_function option above.
function wpcas_provision_user( $user_name ){
	// Generate a random password and create the user
	$password = wp_generate_password( 12, false );
	$user_id = wp_create_user( $user_name, $password, $user_name . "@wpcas" );

	// Set the role
	$user = new WP_User( $user_id );
	$user->set_role( 'subscriber' );
	
	// the CAS user now has a WP account!
	wp_set_auth_cookie( $user->ID );
	
	// First sync
	wpcas_syncuser( $user_name, phpCAS::getAttributes() );

	if( isset( $_GET['redirect_to'] )){
		wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( $_GET['redirect_to'] ));
		die();
	}

	wp_redirect( site_url( '/wp-admin/' ));
	die();
}


// Example user sync function. Not called unless you set sync_user_function option above.
// You need to adapt it according to the Attributes your CAS server provides.
function wpcas_syncuser($user_name, $attributes) {

	// This example syncs the following CAS attributes with the WP user: 'name' and 'mail'

	// WP user fields to CAS attributes
	$map['user_login'] = 'name';
	$map['user_nicename'] = 'name';
	$map['user_email'] = 'mail';
	$map['display_name'] = 'name';
	$map['nickname'] = 'name';

	$wp_user = get_user_by( 'login', $username );

	// see if any fields needs updating
	$updates = array();
	foreach($map as $wp_key => $attr_key) {
		if (isset($attributes[$attr_key]) && $wp_user->$wp_key != $attributes[$attr_key]) {
			$updates[$wp_key] = $attributes[$attr_key];
		}
	}

	if (count($updates) > 0) {
		$updates['ID'] = $wp_user->ID;
		wp_update_user($updates);
	}

}

