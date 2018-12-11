<?php
/*
Plugin Name: Imperial Network
Description: Installs the tables required for the Imperial Multisite and adds admin screens etc. Pretty essential.
Version: 0.1
Author: Alex Furr
*/

// Global defines
define( 'IMPERIAL_NETWORK_PLUGIN_URL', plugins_url('imperial-network' , dirname( __FILE__ )) );
define( 'IMPERIAL_NETWORK_PATH', plugin_dir_path(__FILE__) );


include_once( IMPERIAL_NETWORK_PATH . 'functions.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-db.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-queries.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-utils.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-custom-admin-bar.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-ajax.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-draw.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-actions.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-site-cats.php');
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-api.php');
// Libraries



// List Pages Plugin
include_once( IMPERIAL_NETWORK_PATH . '/classes/class-page-list.php');

// Privacy options - 
include_once( IMPERIAL_NETWORK_PATH . '/classes/class-privacy-options.php');

//include_once( IMPERIAL_NETWORK_PATH . '/classes/class-draw.php');


// Avatar
include_once( IMPERIAL_NETWORK_PATH . '/avatar.php');


// TCPDF Library
include_once( IMPERIAL_NETWORK_PATH . 'classes/class-pdf.php');

if (!defined("PDF_CREATOR") )
{

	include_once( IMPERIAL_NETWORK_PATH . '/libs/tcpdf/tcpdf.php' );	
	include_once( IMPERIAL_NETWORK_PATH . '/libs/tcpdf/config/tcpdf_config.php' );
}


?>
