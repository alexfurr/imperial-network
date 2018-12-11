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
		// Hide the existing admin bar
		show_admin_bar(false);
		
		// Add the admin bar in the footer of every page
		add_action( 'wp_footer', array($this, 'admin_bar'), 100 );		
		
        if ( is_admin() ) {
            add_action( 'admin_head', array( $this, 'admin_head' ) );
            add_action( 'admin_footer', array( $this, 'admin_footer' ) );
        }
	
	}
	
    function admin_head() 
    {
    ?>
        <style>
        
        </style>
    <?php
    }	
    
    
    function admin_footer() 
    {
        global $imperial_adminBar;
        $imperial_adminBar->admin_bar();
    }
    
    
    
	
	static function admin_bar()
	{
		
		
		// Get the home network URL		
		$homeURL = network_home_url();
		
		// Get THIS site root
		$siteURL = get_site_url();
		
		// Define some vars
		$isSiteAdmin = false;
		$isNetworkAdmin= false;	
		$username = "";		
		
		if(isset ($_SESSION['isNetworkAdmin']) )
		{
			$isNetworkAdmin = $_SESSION['isNetworkAdmin'];
		}
		
		if(isset ($_SESSION['username']) )
		{
			$username = $_SESSION['username'];
		}		

		
		if(current_user_can('edit_posts') )
		{
			$isSiteAdmin = true;
		}

		$isDeptAdmin = imperialNetworkUtils::isDeptAdmin($username);
		
		
		
		echo '<div id="imperialAdminBar">';		
	//	echo '<div class="medlearnLogo desktop-only">';
		

	//	echo '<a href="'.$homeURL.'">';
		
		
		//echo '<img src="'.IMPERIAL_NETWORK_PLUGIN_URL.'/images/medlearn_logo.png" width="150px">';
		//echo '<img alt="medlearn Home" title="medlearn Home" src="'.IMPERIAL_NETWORK_PLUGIN_URL.'/images/imperial_logo_white.png" >';
	//	echo 'MedLearn';
//		echo '</a>';	
	//	echo '</div>';
	
		//echo $searchForm;
		
		echo '<div class="imperialAdminBarDashicons">';
		
		echo '<a href="'.$homeURL.'"><i class="fas fa-home"></i> <span class="nav-desktop-only">MedLearn Home</span></a>';
		
		$dashboardStr= '<a href="'.$siteURL.'/wp-admin"><i class="fas fa-tachometer-alt"></i> <span class="nav-desktop-only">Dashboard</span></a>';

		if(is_admin() )
		{
			$dashboardStr = '<a href="'.$siteURL.'/"><i class="fas fa-desktop"></i> <span class="nav-desktop-only">View Site</span></a>';
		}
		
		// If they are dept admin create the dept admin icon
		$deptAdminIcon = '';
		if($isDeptAdmin==true || $isNetworkAdmin==true	)
		{
			$deptAdminIcon='<a href="'.$homeURL.'admin"><i class="fas fa-cogs"></i> <span class="nav-desktop-only">Admin</span></a>';
		}
		
		// If they are network admin
		if($isNetworkAdmin==true)
		{
			echo '<a href="'.$homeURL.'wp-admin/network/"><i class="fas fa-sitemap"></i> <span class="nav-desktop-only">Network Admin</span></a>';
			
			//echo '<a href="'.$homeURL.'wp-admin/network/"><i class="fas fa-sitemap"></i> <span class="nav-desktop-only">Admin</span></a>';
		}
		
		//if they are site admin	
		if($isSiteAdmin==true || $isNetworkAdmin==true)
		{
			echo $dashboardStr;
			if(!is_admin() )
			{
				edit_post_link('Edit Page');
			}			
		}
		
		// Sjhow the dept Admin Icon - is blank if not dept admin
		echo $deptAdminIcon;
		echo '</div>';
		
		

		
		echo '<div id="adminBarSearch">';
		if(!is_admin() )
		{
			
			echo '<div id="searchIconWrap">';
			//get_search_form();
			echo '<i class="fas fa-search"></i>';
			echo '</div>';
			
		}
		echo '</div>';	
		
	
		
		
		echo '<div id="myProfile">';
		if ( is_user_logged_in() ) 
		{	
	
			$args = array(
				'CID' => $_SESSION['userID'],
				'userID' => get_current_user_id(),
				'size'	=> "square",
			);
	
			echo '<div class="profileAvatar">';		
			echo get_user_avatar( $args );		
			echo '</div>';
		}
		echo '</div>';
		

		echo '</div>';
		
		
		// Search Bar drop down
		// Search box wrap
		
		$searchPageURL = site_url("/");
		$homeSearchPageURL= network_home_url("/");
		
		// If its the root site ID then search the help page by default
		$myRootBlog = get_site_option( "imperial_root_blog"  );
		$currentBlogID = get_current_blog_id();
		
		if($myRootBlog==$currentBlogID)
		{
			$searchPageURL = get_site_url()."/med-students/";
		}		
		
		echo '<form role="search" method="get" id="banner-search-form" action="'.$searchPageURL.'">';				
		echo '<div id="bannerSearchWrap">';	
		// input box
		echo '<div id="searchBoxWrap">';
		echo '<input type="" placeholder="Search..." name="s" id="search-input">';
		
		//echo '<input type="submit" value="Go" id="searchGo">';
		
		echo '<input type="hidden" name="searchType" id="searchType" value="content">';
		echo '<input type="hidden" id="searchPageURL" value="'.$searchPageURL.'">';
		echo '<input type="hidden" id="homeSearchPageURL" value="'.$homeSearchPageURL.'">';
		echo '</div>';
		
		$searchURL = get_search_link();
		// Search options
		echo '<div id="searchOptionsWrap">';
		echo '<span class="searchText">Search What... </span><br/>';
		echo '<div id="search_content" class="searchActive">Content</div>';		
		echo '<div id="search_sites">Sites</div>';
		echo '<div id="search_people">People</div>';
		echo '</div>'; // End of search options wrap
		
		echo '</div>'; // End of search wrap	
		echo '</form>';
		
	}	
}