<?php

$imperialNetworkAPI = new imperialAPI();

class imperialAPI
{
	
	//~~~~~
	function __construct ()
	{
		
		$this->addWPActions();
		
	}
	

	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		
		add_action( 'rest_api_init', array ($this, 'wpc_register_wp_api_endpoints' ) );

	}
	
	function wpc_register_wp_api_endpoints() {
		register_rest_route( 'imperial', '/current-username', array(
			'methods' => 'GET',
			'callback' => array($this, 'wpc_somename_search_callback'),
		));
	}
	function wpc_somename_search_callback( $request_data )
	{

		$username = $_SESSION['username'];
	
		return $username;
	}	

}



?>