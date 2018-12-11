<?php

class imperialNetworkActions
{
	
	static function addDeptAdmin($deptID, $username)
	{
		
		global  $wpdb;
		global $imperialNetworkDB;
		
		if($username && $deptID)
		{
		
			$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_facultyAdmins'];		

			$wpdb->query( $wpdb->prepare(		
			"INSERT INTO ".$table_name." (deptID, username) VALUES ( %s, %s )",
			array(
				$deptID,
				$username
				)
			));
		}


		
	}	
	
	
	static function removeDeptAdmin($deptID, $username)
	{
		
		global  $wpdb;
		global $imperialNetworkDB;
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_facultyAdmins'];		
		
		$wpdb->query(
              'DELETE  FROM '.$table_name.' WHERE deptID = "'.$deptID.'" AND username = "'.$username.'"'
		);
		
	}	
	
		
	
	
	// Create a wordpress account for a user found in the imperial users DB.
	static function createWP_user($username)
	{
		
		$thisUserInfo = imperialQueries::getUserInfo($username);
		
		$firstName = trim($thisUserInfo['first_name']);
		$lastName = trim($thisUserInfo['last_name']);
		$email = trim($thisUserInfo['email']);
		$checkUsername = trim($thisUserInfo['username']);
		
		if($checkUsername)
		{
		
			/* Create the User */
			$userData = array(
				'user_login'  =>  $username,
				'user_email'	=> $email,
				'first_name'	=> $firstName,
				'last_name'		=> $lastName,
				'display_name'		=> $firstName.' '.$lastName,
				'user_pass'   =>  NULL  // When creating an user, `user_pass` is expected.
			);
			

			$user_id = wp_insert_user( $userData );
			return $user_id;		
		}
		else
		{
			return false;	
		}
	}
	
	public static function createSite($args)
	{
		
		$network_info = get_current_site();
		$domain = $network_info->domain;
		$path = $network_info->path;		
		
		
		$siteTitle = $args['siteTitle'];
		$siteURL = $args['siteURL'];
		$siteType = $args['siteType'];
		$programme = $args['programme'];
		
		
		// Get the user ID of the requester
		if(isset($args['requesterUsername']) )
		{
			$requesterUsername = $args['requesterUsername'];
			$userInfo = get_user_by( 'login', $requesterUsername );
			$requesterID = $userInfo->ID;
			
			// Get the requester deptID
			$imperialUserInfo = imperialQueries::getUserInfo($requesterUsername);
			$deptID = $imperialUserInfo['deptID'];		
		}
		else
		{
			$requesterID = get_current_user_id();
		}
		
		$deptID="SM"; // MANUAL OVERRIDE MAKE EVERYTHING medicine
				
		$newSiteURL = $path.$siteURL;	

		$checkIfExists = get_blog_id_from_url($domain, $path.$siteURL."/");
		if($checkIfExists<>0)
		{
			
			$feedback = '<div class="notice notice-error is-dismissible">';
			$feedback.=  '<p>This Site already Exists. <a href="'.$newSiteURL.'">Click here to view</a></p>';
			$feedback.= '</div>';			
			
			
			return $feedback;

		}


		
		
		/*
		echo 'newSiteURL = '.$newSiteURL.'<br/>';
		echo 'path = '.$path.'<br/>';
		echo 'domain = '.$domain.'<br/>';
		echo 'siteTitle = '.$siteTitle.'<br/>';
		echo 'siteType = '.$siteType.'<br/>';
		*/

		
		
		
		$newBlogID = wpmu_create_blog($domain, $newSiteURL, $siteTitle, $requesterID);				
		switch_to_blog($newBlogID);
		
		// Get the table class for updates / inserts
		global $wpdb;
		global $imperialNetworkDB;

		
		switch ($siteType)
		{
			case "course":
			case "emodule":
				switch_theme( 'imperial-course' );
				
				// Remove the tagline
				update_blog_option ( $newBlogID, 'blogdescription', "");
				
				
				// Only do this if its from the offocial 'module' list
				if(isset($args['courseMeta'] ) )
				{
					$courseMeta = $args['courseMeta'];
					$academicYear = $courseMeta['academicYear'];
					$courseID = $courseMeta['courseID'];

					$niceAcademicYear = imperialNetworkUtils::getNiceAcademicYear($academicYear);
					
					
					update_blog_option ( $newBlogID, 'blogdescription', $niceAcademicYear);
					
					// update the courses table_name
					$thisTable = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];

					$wpdb->update( 
						$thisTable, 
						array( 
							'blogID' => $newBlogID, // column name and update value
						), 
						array( 'courseID' => $courseID ), // Where Clause
						array( 
							'%d',	// Expected Data types
						), 
						array( '%s' ) // Where Format
					);						

				}
				else
				{
					switch ($siteType)
					{
						
						case "course":

					
							// update the courses table_name
							$thisTable = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];

							$feedback = $wpdb->insert( 
								$thisTable, 
								array( 
									'course_name' => $siteTitle,
									'blogID' => $newBlogID,
									'deptID' => $deptID,
									'programme_code'	=>$programme,
									'academic_year' => $_POST['academicYear'],
									'yos'	=> $_POST['yos'],
								), 
								array( 
									'%s',
									'%d',
									'%s',									
									'%s',
									'%d',
									'%d',									
								) 
							);
							
						break;
						
						case "emodule":
						
						
							$currentDate = current_time( 'mysql'); 
						
							// Update the table who requested it
							$thisTable = $imperialNetworkDB::imperialTableNames()['dbTable_arbitraryCourses'];				
							
							$wpdb->insert( 
								$thisTable, 
								array( 
									'blogID' => $newBlogID, 
									'username' => $requesterUsername,
									'deptID' => $deptID,
									'createDate' => $currentDate,
								), 
								array( 
									'%d', 
									'%s',
									'%d',
									'%s',
									
								) 
							);						
						
						
						
						
							
						break;
					
					
					}
					
				}
				

				//Create Topics PAGE
				$topicsPage = array(
				  'post_title'		=> 'Topics',
				  'post_content'	=> '[imperial-topics]',
				  'post_status' 	=> 'publish',
				  'post_type'		=> 'page',
				);
				 
				// Insert the post into the database
				wp_insert_post( $topicsPage );				
				
				// Plugins to Activate
				$pluginsArray = array(
					"quiz-tool-lite",
					"ek-user-stats",
					"h5p",
					"icl-pdf-creator",
				);
				
				foreach($pluginsArray as $pluginName)
				{
					imperialNetworkUtils::plugin_activation($pluginName.'/'.$pluginName.'.php');
				
				}

				// Finally Remove the current logged in user as admin as we don't want them added
				remove_user_from_blog($requesterID, $newBlogID );		
				
				

				
				
			break;
			
			
			// Arbitrary course
			default:
				switch_theme( 'imperial-theme' );
				
				// Remove the tagline
				update_blog_option ( $newBlogID, 'blogdescription', "");
				
				// Update the table who requested it
				$thisTable = $imperialNetworkDB::imperialTableNames()['dbTable_arbitraryCourses'];				
				
			
				$currentDate = current_time( 'mysql'); 
			
				$wpdb->insert( 
					$thisTable, 
					array( 
						'blogID' => $newBlogID, 
						'username' => $requesterUsername,
						'deptID' => $deptID,
						'createDate' => $currentDate,
					), 
					array( 
						'%d', 
						'%s',
						'%d',
						'%s',
						
					) 
				);	
				
				
			break;
		}
		
		
		
		// Delete First Post permanently
		wp_delete_post( 1, true );
		
		// Delete the first PAGE permanently
		wp_delete_post( 2, true );
		
		
		
		// FOrce permalinks to postname
		global $wp_rewrite; 

		//Write the rule
		$wp_rewrite->set_permalink_structure('/%postname%/'); 

		//Set the rewrite permalnk option
		update_option( "rewrite_rules", FALSE ); 

		//Flush the rules and tell it to write htaccess
		$wp_rewrite->flush_rules( true );
		
		// Add Privacy to be college only
		update_option('blog_public', "-1");
		

		// Disable Comments by default
		update_option("default_comment_status", false);
		update_option("default_ping_status", false);
		update_option("default_pingback_flag", false);
		update_option("comment_whitelist", false);
		

		// Create default pages
		$homePage = array(
		  'post_title'		=> 'Home',
		  'post_content'	=> 'Welcome',
		  'post_status' 	=> 'publish',
		  'post_type'		=> 'page',
		);
		 
		// Insert the post into the database
		$homepageID = wp_insert_post( $homePage );	
		

		update_option( 'page_on_front', $homepageID );
		update_option( 'show_on_front', 'page' );	

		restore_current_blog();		
		
		$feedback = '<div class="notice notice-success is-dismissible">';
		$feedback.=  '<p>Site Created. <a href="'.$newSiteURL.'">Click here to view</a></p>';
		$feedback.= '</div>';
		
		return $feedback;
		
		
	}
	


}



?>