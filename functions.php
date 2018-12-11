<?php
$myImperialNetwork = new imperialNetwork();

class imperialNetwork
{
	var $networkMenuSlug = 'imperial-network-admin';	
	var $currentVersion = '1.1';

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
		//Frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'frontendEnqueues' ), 1 );		
		add_action( 'admin_enqueue_scripts', array( $this, 'adminSettingsEnqueues' ) );
		

		//Scripts for both
		add_action( 'wp_enqueue_scripts', array( $this, 'helperEnqueues' ), 1 );		
		add_action( 'admin_enqueue_scripts', array( $this, 'helperEnqueues' ) );
		
		
		// Add shortcode to display placements
		//add_shortcode( 'imperial-placements', array( 'imperialPlacementsDraw', 'drawPlacementsFrontEnd' ) );
		//add_shortcode( 'my-placements', array( 'imperialPlacementsDraw', 'drawMyPlacements' ) );
		
		add_shortcode( 'cat-sites', array( 'imperialSiteCategories', 'drawCatSites' ) );		
		
		
		
		add_action('wp_footer',  array($this, 'footer_code') );
		add_action('admin_footer', array($this, 'footer_code') );
		
		//Admin Menu
		//add_action( 'init',  array( $this, 'create_CPTs' ) );		
		
		add_action( 'network_admin_menu', array( $this, 'create_NetworkAdminPages' ));			
		
		/* Remove Screen options and help drop down from all pages */
		add_filter( 'contextual_help', array($this, 'mytheme_remove_help_tabs'), 999, 3 );
		add_filter('screen_options_show_screen', '__return_false');
		
		
		add_action( 'admin_menu', array ($this, 'imperialCustomAdminMenu' ), 999 );
		
		
		// Remove useless widgets
		add_action('widgets_init', array($this, 'unregister_default_widgets'), 11 );


		//	Check for logout
		add_action('plugins_loaded', array($this, 'check_for_logout') );
		
		// Check for other custom actions
		add_action('plugins_loaded', array($this, 'check_for_actions') );

		// Allow Wp to send email from AWS
		add_filter('wp_mail_from', array ($this, 'imperial_mail_custom_from') );
		add_filter('wp_mail_from_name', array ($this, 'imperial_mail_custom_from_name') );

		// Remove subscribers from root blogID
		add_action( 'wp_login', array ($this, 'remove_subscribers' ) );	
		
		// Setup Global usermeta for current user
		//add_action('init', array ($this, 'globalize_currentUserMeta'));

		//add_action('init', array($this, 'imperialLoginRoutine'), 99 ); // Later than other pligins to retain session info
		add_action('init', array($this, 'myStartSession'), 99 );
		
		
		
		// Remove the H1 tag from editor. It should only be used for page headings
		add_filter('tiny_mce_before_init', array($this, 'tiny_mce_remove_unused_formats' ) );
		
		// Force Kitchen sink on
		add_filter( 'tiny_mce_before_init', array($this, 'force_kitchensink_open' ) );
		
		// Custom Buttons
		add_filter( 'mce_buttons', array ($this, 'remove_tiny_mce_buttons_from_editor_row1') );
		add_filter( 'mce_buttons_2', array ($this, 'remove_tiny_mce_buttons_from_editor_row2') );
		
		
		// Change the name of subscriber to student and remove author role
		add_action('init', array ($this, 'edit_roles' ));		

	

	}
	
	// Override from email
	function imperial_mail_custom_from($email){
		return "wordpress@ifuture.cloud";
	}	
	
	function imperial_mail_custom_from_name($from_name){
		return "MedLearn";
	}		
	

	function create_NetworkAdminPages()
	{

		$parentMenuSlug = $this->networkMenuSlug;		

		/* Network Admin Pages */	
		$page_title = "Imperial Network";
		$menu_title = "Imperial Network";
		$capability = "manage_network_options"; //'manage_options' for administrators.
		$function = array( $this, 'drawImperialNetworkAdmin' );
		$icon = 'dashicons-networking';
		$handle = add_menu_page( $page_title, $menu_title, $capability, $parentMenuSlug, $function, $icon );
		
		/* Network Admin Users */
		$page_title="Users";
		$menu_title="Users";
		$menu_slug="imperial-network-users";
		$function=  array( $this, 'drawImperialNetworkAdminUsers' );
		$myCapability = "manage_network_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	
		
		/* Courses Admin Users */
		$page_title="Courses";
		$menu_title="Courses";
		$menu_slug="imperial-network-courses";
		$function=  array( $this, 'drawImperialNetworkAdminCourses' );
		$myCapability = "manage_network_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	


		/* Faculties */
		$page_title="Faculty List";
		$menu_title="Faculty List";
		$menu_slug="imperial-network-faculties";
		$function=  array( $this, 'drawImperialNetworkFaculties' );
		$myCapability = "manage_network_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	

		/* Faculties */
		$page_title="Department Settings";
		$menu_title="Department Settings";
		$menu_slug="imperial-network-faculty-settings";
		$function=  array( $this, 'drawImperialNetworkFacultySettings' );
		$myCapability = "manage_network_options";
		add_submenu_page("imperial-network-faculties", $page_title, $menu_title, $myCapability, $menu_slug, $function);	


		
		/* Create New Blog  Screen */
		$page_title="Create New Site";
		$menu_title="New Site";
		$menu_slug="imperial-network-add-site";
		$function=  array( $this, 'drawImperialNetworkCreateSite' );
		$myCapability = "manage_network_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	
		
		
		/* Mugshots */
		$page_title="Upload Mugshots";
		$menu_title="Student Mughosts";
		$menu_slug="imperial-network-add-mugshots";
		$function=  array( $this, 'drawImperialNetworkMugshots' );
		$myCapability = "manage_network_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);			
	
		
		

		/* Tools */
		$page_title="Imperial Tools";
		$menu_title="Imperial Tools";
		$menu_slug="imperial-network-tools";
		$function=  array( $this, 'drawImperialNetworkTools' );
		$myCapability = "manage_network_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);			
		
		
		
	}
	
	
	//~~~~~ Drawing
	function drawImperialNetworkAdmin()
	{
		include_once( dirname(__FILE__) . '/admin/network_admin.php');
	}
	
	function drawImperialNetworkAdminUsers()
	{
		include_once( dirname(__FILE__) . '/admin/users.php');
	}	
	
	
	function drawImperialNetworkAdminCourses()
	{
		include_once( dirname(__FILE__) . '/admin/courses.php');
	}		


	function drawImperialNetworkFaculties()
	{
		include_once( dirname(__FILE__) . '/admin/faculties.php');
	}		
	
	
	
	function drawImperialNetworkCreateSite()
	{
		include_once( dirname(__FILE__) . '/admin/new_site.php');
	}	
	
	function drawImperialNetworkFacultySettings()
	{
		include_once( dirname(__FILE__) . '/admin/dept_settings.php');
	}		
	
	
	function drawImperialNetworkMugshots()
	{
		include_once( dirname(__FILE__) . '/admin/mugshots.php');
	}
	
	
	function drawImperialNetworkTools()
	{
		include_once( dirname(__FILE__) . '/admin/tools.php');
	}	
	

	function helperEnqueues ()
	{
		//Scripts
		wp_enqueue_script('jquery');
		
		// Global  Styles
		wp_enqueue_style( 'imperial-global-style', IMPERIAL_NETWORK_PLUGIN_URL . '/css/global-styles.css' );

		wp_enqueue_script('imperial-network-js', IMPERIAL_NETWORK_PLUGIN_URL. '/js/custom.js', array( 'jquery' ) );
		wp_enqueue_style( 'imperial-network-admin-bar', IMPERIAL_NETWORK_PLUGIN_URL . '/css/admin-bar.css' );		

		//DataTables js
		wp_register_script( 'datatables', ( '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js' ), false, null, true );
		wp_enqueue_script( 'datatables' );

		//DataTables css
		wp_enqueue_style('datatables-style','//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css');	
		
		// Expanding UL LI menu tree
		wp_enqueue_script('imperial-helpers-contentTreeMenu-js', IMPERIAL_NETWORK_PLUGIN_URL. '/libs/contentTreeMenu/contentTreeMenu.js', array( 'jquery' ) );
		wp_enqueue_style( 'imperial-helpers-contentTreeMenu-styles', IMPERIAL_NETWORK_PLUGIN_URL . '/libs/contentTreeMenu/contentTreeMenu.css' );
		
		// Table Styles
		wp_enqueue_style( 'imperial-table-styles', IMPERIAL_NETWORK_PLUGIN_URL . '/css/tables.css' );

		
		
		// Register Ajax script for front end
		wp_enqueue_script('imperial-network-ajax', IMPERIAL_NETWORK_PLUGIN_URL.'/js/ajax.js', array( 'jquery' ) );	
		
		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajax_nonce' => wp_create_nonce('imperial_ajax_nonce'),
		);	
		wp_localize_script( 'imperial-network-ajax', 'imperialAjax_params', $params );
		
		// Font Awesome CSS
		wp_enqueue_style( 'imperial-font-awesome', '//use.fontawesome.com/releases/v5.2.0/css/all.css' );
		//wp_register_script( 'imperial-font-awesome-js', '//use.fontawesome.com/releases/v5.2.0/js/all.js' );
		//wp_enqueue_script( 'imperial-font-awesome-js' );
		
	}	

	
	

	
	function frontendEnqueues ()
	{

		// Progress Bars
		wp_enqueue_style( 'imperial-network-progress', IMPERIAL_NETWORK_PLUGIN_URL . '/css/progress_bar.css' );
		wp_enqueue_style( 'imperial-network-progress-radial', IMPERIAL_NETWORK_PLUGIN_URL . '/css/radial_progress.css' );		
		wp_enqueue_style( 'imperial-datepicker', IMPERIAL_NETWORK_PLUGIN_URL . '/css/datepicker.css' );		
		
	}	
	
	function adminSettingsEnqueues ()
	{
		
		// Restrict viewing of certain pages
		global $pagenow;
		
		
		switch ($pagenow)
		{
			
			// Do not show the default user add new page
			case "user-new.php":
			

			
				// Only redirect if they are not in network admin
				if(!is_network_admin() )
				{
					echo '<script>
					window.location.replace("users.php?page=imperial-add-users");
					</script>';
					//wp_redirect( $redirectURL );
					exit;
				}
				else
				{
								
					echo '<script>
					window.location.replace("admin.php?page=imperial-network-users&view=userEdit");
					</script>';
					//wp_redirect( $redirectURL );
					exit;
				}
				

			
			break;
			
			
			case "site-new.php":
				// Only redirect if they are not in network admin
					echo '<script>
					window.location.replace("admin.php?page=imperial-network-add-site");
					</script>';
					//wp_redirect( $redirectURL );
					exit;
			
			
			break;
		}		

		
	}	
	
	// unregister useless widgets
	function unregister_default_widgets() {
		unregister_widget('WP_Widget_Pages');
		unregister_widget('WP_Widget_Calendar');
		unregister_widget('WP_Widget_Archives');
		unregister_widget('WP_Widget_Links');
		unregister_widget('WP_Widget_Meta');
		unregister_widget('WP_Widget_Search');
		//unregister_widget('WP_Widget_Text');
		unregister_widget('WP_Widget_Categories');
		unregister_widget('WP_Widget_Recent_Posts');
		unregister_widget('WP_Widget_Recent_Comments');
		unregister_widget('WP_Widget_RSS');
		unregister_widget('WP_Widget_Tag_Cloud');
		unregister_widget('WP_Nav_Menu_Widget');
		unregister_widget('Twenty_Eleven_Ephemera_Widget');
	}	
		
		
		
	function imperialCustomAdminMenu()
	{
		// Remove the 'my Profile' and the 'Add users' page from Users
		remove_submenu_page( 'users.php', 'profile.php' );
		remove_submenu_page( 'users.php', 'user-new.php' );
		
		// hide the 'Quizzes' part of forminator
		remove_submenu_page( 'forminator', 'forminator-quiz' );		
		

		
		// Add new custom Add users Page for bulk adding users etc
		/* Create New Blog  Screen */
		$parentMenuSlug = "users.php";
		$page_title="Add Users";
		$menu_title="Add / Edit Users";
		$menu_slug="imperial-add-users";
		$function=  array( $this, 'drawImperialAddUsersPage' );
		$myCapability = "manage_options";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	
		
		
		
		
		
		
	}
	
	
	function drawImperialAddUsersPage()
	{
		include_once( dirname(__FILE__) . '/admin/imperial_enrol_users.php');
	}
	
	
	function footer_code()
	{
		$profileDropdown = imperialNetworkDraw::drawProfileDropdown();		
		echo $profileDropdown;
	}
	
	function mytheme_remove_help_tabs($old_help, $screen_id, $screen){
		$screen->remove_help_tabs();
		return $old_help;
	}	
	
	
	
	// Logs the person out of wordpress
	function check_for_logout()
	{
		
		if(isset($_GET['imperial-logout']))
		{
			
			// Remove sessions
			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}		
			
			//remove PHPSESSID from browser
			if ( isset( $_COOKIE[session_name()] ) )
			setcookie( session_name(), "", time()-3600, "/" );
			//clear session from globals
			$_SESSION = array();
			//clear session from disk
			session_destroy();
			
			wp_logout();
		
			$homeURL = network_home_url();
		
			?>
			<script>
			window.location.replace('<?php echo $homeURL; ?>');
			</script>
			<?php			
			die();

		}
	
	}
		
		
		
		function check_for_actions()
		{
			if(isset($_GET['action']) )
			{
				$myAction = $_GET['action'];
				
				
				
				switch ($myAction)
				{
					case "adoptRole":
					
					
						imperialNetwork::myStartSession();
					
						if($_SESSION['isNetworkAdmin']==true)
						{
							
							$username = $_GET['username'];

							// Get their Firstname, last Name and email from the WP DB	
							$thisUserInfo = imperialQueries::getUserInfo($username);	
							$deptID = $thisUserInfo['deptID'];
							$email = $thisUserInfo['email'];
							$userType = $thisUserInfo['user_type'];
							$yos = $thisUserInfo['yos'];
							$programme = $thisUserInfo['programme'];
							$firstName = $thisUserInfo['first_name'];
							$lastName = $thisUserInfo['last_name'];
							$userID = $thisUserInfo['userID'];	
							
							
							// Set the session vars
							$_SESSION['username']=$username;
							$_SESSION['deptID']=$deptID;
							$_SESSION['userType']=$userType;
							$_SESSION['programme']=$programme;
							$_SESSION['firstName']=$firstName;
							$_SESSION['lastName']=$lastName;
							$_SESSION['fullname']=$firstName.' '.$lastName;
							$_SESSION['userID']=$userID;
							$_SESSION['email']=$email;
							
							$tutorUsername = ''; // Define as blank to start with
							
							
							// Set their tutor if exsits
							// Check if class exists
							
							if (class_exists('imperialTutorQueries'))
							{
								$tutorInfo = imperialTutorQueries::getMyTutor($username);
								$tutorUsername = $tutorInfo['username'];	
							}

					
							$_SESSION['tutorUsername']=$tutorUsername;		
							
							// Set the fact its a user swap
							$_SESSION['userSwapped']=true;

							
							
						}					
					
					
					
					
					
					
					break;
					
				}
				
			}
		}
	
	function remove_subscribers()
	{
		
		
		//Get Root Blog ID
		$myRootBlogID = get_site_option( "imperial_root_blog"  );
		switch_to_blog( $myRootBlogID );						
		$args = array( 'role' => 'Subscriber' );
		$subscribers = get_users( $args );
		foreach ($subscribers as $userMeta)
		{
			$userID = $userMeta->ID;			
			remove_user_from_blog($userID, $myRootBlogID );		
		}				
				
		restore_current_blog();

	}	
		
	// Start Sessions if one does not already exist
	public static function myStartSession()
	{	
		$has_session = session_status() == PHP_SESSION_ACTIVE;		
		if($has_session==false) {
			session_start();
		}		
		
		

		
		if(is_user_logged_in() && !isset($_SESSION['username']) )
		{
			// Get the logged in username

			$current_user = wp_get_current_user();
			$username = $current_user->user_login;
				
			// Define some vars
			$deptID="";
			$tutorUsername="";
			$userType=4;
			$isDeptAdmin="";
			$isNetworkAdmin = "";
			$programme="";
			$yos="";	
			$userID="";		
			
			// Get their Firstname, last Name and email from the WP DB	
			$thisUserInfo = imperialQueries::getUserInfo($username);		
			
			$thisUsername = $thisUserInfo['username'];
			
			// If they are not in the imperial user list then add them
			if($thisUsername=="")
			{
				
				// Get the current user info			
				$userEmail = $current_user->user_email;
				$firstName = $current_user->user_firstname;
				$lastName = $current_user->user_lastname;			
				
				
				global $wpdb;
				global $imperialNetworkDB;					
				$userTable = $imperialNetworkDB::imperialTableNames()['dbTable_users'];
				
				
				$myFields="INSERT into $userTable (first_name, last_name, username, email) ";
				$myFields.="VALUES (%s, %s, %s, %s)";

				
				$RunQry = $wpdb->query( $wpdb->prepare($myFields,
					$firstName,
					$lastName,
					$username,
					$userEmail
				));
			}	
			else
			{
				$deptID = $thisUserInfo['deptID'];
				$userEmail = $thisUserInfo['email'];
				$userType = $thisUserInfo['user_type'];
				$yos = $thisUserInfo['yos'];
				$programme = $thisUserInfo['programme'];
				$firstName = $thisUserInfo['first_name'];
				$lastName = $thisUserInfo['last_name'];
				$userID = $thisUserInfo['userID'];			
			}
			
			// Set Wordpres user ID
			$wpUserID = $current_user->ID;	  
			$_SESSION['wpUserID']=$wpUserID;	
			
			// Set the session vars
			$_SESSION['username']=$username;
			$_SESSION['deptID']=$deptID;
			$_SESSION['userType']=$userType;
			$_SESSION['programme']=$programme;
			$_SESSION['firstName']=$firstName;
			$_SESSION['lastName']=$lastName;
			$_SESSION['fullname']=$firstName.' '.$lastName;
			$_SESSION['isDeptAdmin']=$isDeptAdmin;
			$_SESSION['userID']=$userID;
			$_SESSION['email']=$userEmail;
			
			
			// Set their tutor if exsits
			$tutorUsername = '';
			if(class_exists('imperialTutorQueries') )
			{
				$tutorInfo = imperialTutorQueries::getMyTutor($username);
				$tutorUsername = $tutorInfo['username'];		
				$_SESSION['tutorUsername']=$tutorUsername;		
			}
			
			// Are they a dept admin?
			$isDeptAdmin = imperialNetworkUtils::isDeptAdmin($username);			
			$_SESSION['isDeptAdmin']=$isDeptAdmin;
			
			
			// Are they network admin?
			if(user_can( $wpUserID, 'manage_network') )
			{
				$isNetworkAdmin=true;
			}
			
			$_SESSION['isNetworkAdmin']=$isNetworkAdmin;
		
			
		}
	}	
	
	/*
	 * Modify TinyMCE editor to remove H1.
	 */
	function tiny_mce_remove_unused_formats($args) {
		// Add block format elements you want to show in dropdown
		$args['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;';
		$args['wordpress_adv_hidden'] = false;
		
		return $args;
	}	

			
	function my_mce_buttons_2($buttons)
	{	
		/**
		 * Add in a core button that's disabled by default
		 */
		$buttons[] = 'superscript';
		$buttons[] = 'subscript';


		return $buttons;
	}
	

	
	
	
	// Custom Row 1 buttons
	function remove_tiny_mce_buttons_from_editor_row1( $buttons ) {
		$remove_buttons = array(
			'formatselect',
			'strikethrough',
			'wp_more', // read more link
			'spellchecker',
			'wp_adv',
			
		);
		foreach ( $buttons as $button_key => $button_value ) {
			if ( in_array( $button_value, $remove_buttons ) ) {
				unset( $buttons[ $button_key ] );
			}
		}
		return $buttons;
	}	
	
	function remove_tiny_mce_buttons_from_editor_row2( $buttons )
	{

		$buttons = $buttons = array(
        'formatselect', // format dropdown menu for <p>, headings, etc
		'superscript',
		'subscript',
        'underline',
        'forecolor', // text color
        'pastetext', // paste as text
        'removeformat', // clear formatting
        'charmap', // special characters
        'outdent',
        'indent',
        'undo',
        'redo',
		);		
		return $buttons;
	}		
	
	/**
	 * Force the kitchen sink to always be on
	 */
	function force_kitchensink_open( $args )
	{
	 // $args['wordpress_adv_hidden'] = false;
	  return $args;
	}	
	
	
	static function edit_roles() {
		wp_roles()->remove_role( 'contributor' );
		wp_roles()->remove_role( 'author' );


		
	}	
	
	
}


// Get the Current logged in username
function getUserIDfromUsername($username)
{
	$userID = 0;
	$userMeta = get_user_by( 'login', $username );
	if($userMeta)
	{
		$userID = $userMeta->ID;
	}	

	return $userID;
}




?>