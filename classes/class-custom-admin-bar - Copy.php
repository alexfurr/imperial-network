<?php

$imperial_adminBar = new imperial_adminBar();
class imperial_adminBar
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
		
		
		show_admin_bar(false);
		
		//Admin Menu
		add_action('admin_bar_menu', array($this, 'add_toolbar_links') ) ;
		add_action( 'wp_before_admin_bar_render', array($this, 'remove_admin_bar_links' ) );	
	
	}
			
	
	static function add_toolbar_links($wp_admin_bar)
	{

		$args = array(
		'id' => 'medlearn_profile',
		'title' => 'My Profile',
		'href' => "#",
		'meta' => array(
			'class' => 'medlearn_profile_adminbar_link',
			'title' => 'medlearn Menu',
			'onclick'  => 'return false;',
		)
		);
		$wp_admin_bar->add_node($args);		
		
		
		
		$rootURL = network_site_url();
		
		$args = array(
		'id' => 'medlearn_root',
		'title' => 'Back to medlearn',
		'href' => $rootURL,
		'meta' => array(
		'class' => 'customlink',
		'title' => 'Back to medlearn'
		)
		);
		$wp_admin_bar->add_node($args);
		
		
	
		
	
	}
	
	function remove_admin_bar_links() {
		global $wp_admin_bar;

		//Remove WordPress Logo Menu Items
		$wp_admin_bar->remove_menu('wp-logo'); // Removes WP Logo and submenus completely, to remove individual items, use the below mentioned codes


		//Remove Site Name Items		
		if (!current_user_can('manage_options')) {
			$wp_admin_bar->remove_menu('site-name'); // Removes Site Name if subscriber
		}
		
		
		
		
		//$wp_admin_bar->remove_menu('site-name'); // Removes Site Name and submenus completely, To remove individual items, use the below mentioned codes
		//$wp_admin_bar->remove_menu('view-site'); // 'Visit Site'
		//$wp_admin_bar->remove_menu('dashboard'); // 'Dashboard'
		$wp_admin_bar->remove_menu('themes'); // 'Themes'
		$wp_admin_bar->remove_menu('widgets'); // 'Widgets'
		$wp_admin_bar->remove_menu('menus'); // 'Menus'
		
		
		$wp_admin_bar->remove_menu('customize'); // 'Customise form front end'
		$wp_admin_bar->remove_menu('search'); // 'Search
		//$wp_admin_bar->remove_menu('my-sites'); // 'Network Sites

		$wp_admin_bar->remove_menu('bar-archive');
		
		// Remove Comments Bubble
		$wp_admin_bar->remove_menu('comments');

		//Remove Update Link if theme/plugin/core updates are available
		$wp_admin_bar->remove_menu('updates');

		//Remove '+ New' Menu Items
		$wp_admin_bar->remove_menu('new-content'); // Removes '+ New' and submenus completely, to remove individual items, use the below mentioned codes
		$wp_admin_bar->remove_menu('new-post'); // 'Post' Link
		$wp_admin_bar->remove_menu('new-media'); // 'Media' Link
		$wp_admin_bar->remove_menu('new-link'); // 'Link' Link
		$wp_admin_bar->remove_menu('new-page'); // 'Page' Link
		$wp_admin_bar->remove_menu('new-user'); // 'User' Link

		// Remove 'Howdy, username' Menu Items
		$wp_admin_bar->remove_menu('my-account'); // Removes 'Howdy, username' and Menu Items
		$wp_admin_bar->remove_menu('user-actions'); // Removes Submenu Items Only
		$wp_admin_bar->remove_menu('user-info'); // 'username'
		$wp_admin_bar->remove_menu('edit-profile'); // 'Edit My Profile'
		$wp_admin_bar->remove_menu('logout'); // 'Log Out'

	}
	
	
	
	
	
}