<?php
/*
// Required configuration file for wpCAS plugin
*/


// the configuration array
$wpcas_options = array(
	'cas_version' => '2.0',
	'include_path' => '/absolute/path/to/CAS.php',
	'server_hostname' => 'server.university.edu',
	'server_port' => '443',
	'server_path' => '/url-path/',
	'gateway_mode' => false, // True to attempt auto-login (CAS gateway)
	'gateway_mode_check_times' => 0 // See phpCas::setCacheTimesForAuthRecheck(). -1 means check only once. 0, always check, n, check every n times.
	);

// this function gets executed 
// if the CAS username doesn't match a username in WordPress
function wpcas_nowpuser( $user_name ){
	die('you do not have permission here');
}

// Upon every successful login, this function is called
// Use it to update the user profile
function wpcas_syncuser($user_name, $attributes) {
}

