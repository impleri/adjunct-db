<?php
/**
 * adjunct root file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
Plugin Name: Adjunct Database
Version: 0.2
Plugin URI: http://github.com/impleri/adjunct-db/
Description: Better form handling for The Adjunct Project
*/

// Keep this file short and sweet; leave the clutter for elsewhere!
define('ADB_VERSION', '0.2');
load_plugin_textdomain( 'adjunct', false, basename(dirname(__FILE__)) . '/lang' );

// the static system class and installer are always loaded
require_once dirname(__FILE__) . '/system.php';
require_once dirname(__FILE__) . '/install.php';

// only include rest of system if installed
if (adb_system::isInstalled()) {
	require_once dirname(__FILE__) . '/parent.php'; // generic parent class
	require_once dirname(__FILE__) . '/metro.php'; // metro areas
	require_once dirname(__FILE__) . '/campus.php'; // campuses
	require_once dirname(__FILE__) . '/contract.php'; // contracts
	require_once dirname(__FILE__) . '/benefit.php'; // benefits
	require_once dirname(__FILE__) . '/display.php'; // frontend display

	// handle updates
	adb_install::update();

	wp_enqueue_style('ml-style', plugins_url('/css/adjunt.css', __FILE__));
}

register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), array('adb_install', 'activate'));
