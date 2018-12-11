<?php

class imperialNetworkUtils
{
	
	static function removeNonAlphaNumeric($str)
	{
		$outstr = preg_replace("/[^A-Za-z0-9 ]/", '', $str);
		
		return $outstr;
	}
	
	static function makeBlogURL($blogTitle)
	{
		$siteURL = strtolower($blogTitle);
		//Make alphanumeric (removes all other characters)
		$siteURL = imperialNetworkUtils::removeNonAlphaNumeric($siteURL);
		//Clean up multiple dashes or whitespaces
		$siteURL = preg_replace("/[\s-]+/", " ", $siteURL);
		//Convert whitespaces and underscore to dash
		$siteURL = preg_replace("/[\s_]/", "-", $siteURL);	

		return $siteURL;
	}
	
	
	
	// $userID accepts username or WP ID
	static function isDeptAdmin($username, $deptID="")
	{
		// COnvert user ID to username if interface_exists
		if(is_int ($username) )
		{

			$user_info = get_userdata($username);
			$username = $user_info->user_login;
		}
				
		if($username=="")
		{			
			return false;
		}
		
		$isDeptAdmin = false;
		$adminUsers = imperialQueries::getDeptAdmins($deptID);

		if(array_key_exists($username, $adminUsers) )
		{
			return true;
		}
		
		// Finally check if they are network admin
		
		if(imperialNetworkUtils::isNetworkAdmin() ==true)
		{
			return true;
		}
		
		
		return false;
	}
	
	// Is the current user a network admin
	static function isNetworkAdmin()
	{	

		if(current_user_can('manage_network') )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	static function getUserTypesArray()
	{
		$userTypeArray = array();
		$userTypeArray[1] = 'Staff';
		$userTypeArray[2] = 'PG Research';
		$userTypeArray[3] = 'PG Taught';
		$userTypeArray[4] = 'Undergraduate';		
		
		return $userTypeArray;
		
	}
	
	
	static function getUserTypeStr($userType)
	{
		$userType = intval ($userType);
		$userTypeArray = imperialNetworkUtils::getUserTypesArray();
		
		// Only return value if its between 1 and 4
		if(is_int($userType) && $userType>=1 && $userType<=4)
		{		
			$userTypeStr = $userTypeArray[$userType];
			return $userTypeStr;

		}
		else
		{
			return '-';
		}
		
	}
	
	
	static function plugin_activation( $plugin )
	{
		if( ! function_exists('activate_plugin') ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if( ! is_plugin_active( $plugin ) ) {
			activate_plugin( $plugin );
		}
	}
	
	static function getNiceAcademicYear($academicYear, $sep="/")
	{
		$niceAcademicYear = substr(chunk_split($academicYear, 4, $sep), 0, -1);		
		return $niceAcademicYear;
	}
	
	
	
	static function getAcademicYearsArray()
	{
		
		$academicYearsArray = array();
		$startYear = "2017"; // When the WP was first introduced
	
		$currentYear = date('Y');
		$futureYearsMax = ($currentYear+3);
		
		while($startYear<=$futureYearsMax)
		{

			$yearNext = substr(($startYear+1), -2);
			$thisAcademicYear = $startYear.$yearNext;
			$academicYearsArray[$thisAcademicYear] = imperialNetworkUtils::getNiceAcademicYear($thisAcademicYear);
			$startYear++;

		}
		
		return $academicYearsArray;
		
	}
	
	static function getUserSites($userID)
	{		
	
		$user_blogs = get_blogs_of_user( $userID );
		
		$defaultIcon = get_stylesheet_directory_uri().'/assets/images/cover-template.jpg';	
	
		
		$mySiteArray = array();
		foreach ($user_blogs AS $user_blog) {
			
			$siteURL = $user_blog->siteurl;
			$siteID = $user_blog->userblog_id;
			$siteName = $user_blog->blogname;
			
			switch_to_blog( $siteID );
			
			// Get the site privacy
			$siteVis = get_option("blog_public");	
			$showSite = true;
			if($siteVis=="-3")
			{
				$showSite=false;
				if(current_user_can("manage_options") )
				{
					$showSite=true;
				}
			}	
				
				
			if($showSite==true)
			{
				// Get the Site Academic year		
				$courseInfo = imperialQueries::getCourseInfoFromBlogID($siteID);

				// Get the site Icon if it exists
				$siteIcon = get_option("siteIcon", $defaultIcon);		
				
				
				if($siteIcon==""){$siteIcon = $defaultIcon;}
				
				// Also get the blog category if it exists
				$thisBlogCats = imperialSiteCategories::getSiteCategories($siteID);
				
				
				$academicYear = $courseInfo['academic_year'];
				if($academicYear=="")
				{
					$academicYear=0;
				}
				
							
				$mySiteArray[$academicYear][] = array
				(
					"siteName" => $siteName,
					"siteID"	=> $siteID,
					"siteURL"	=> $siteURL,
					"siteIcon"	=> $siteIcon,
					"siteCat"	=> $thisBlogCats,
				);
				
			}
			

			
			restore_current_blog();

		}	

		return 	$mySiteArray;
		
		
		

	}
	
	public static function getDeptModuleYearGroups($deptID, $academicYear)
	{
		

		$args = array(
			"deptID" => $deptID,
			"academicYear"	=> $academicYear,
		);
		$deptSiteArray = imperialQueries::getCourses($args);

		$deptCourseArray = array();
		foreach ($deptSiteArray as $moduleInfo)
		{
			$yos = $moduleInfo['yos'];
			if($yos==""){$yos=0;}
			$blogID = $moduleInfo['blogID'];
			
			switch_to_blog($blogID);
			$site_title = get_bloginfo( 'name' );
			
			$site_url = get_site_url();
			restore_current_blog();	
			
			$tempModuleArray = array(
				'site_title'	=> $site_title,
				'site_url'	=> $site_url,
			);
			// Get the blog info	
			$deptCourseArray[$yos][] = $tempModuleArray;			
		}
	
	
		return $deptCourseArray;				
	}	
	
	
	public static function convertTextFromDB($string)
	{
		$string = stripslashes($string);
		$string = wp_kses_post($string);
	
		
		return $string;		
	}
	
	
	public static function getProfileMenuItems($userType)
	{
		// Students Submenu
		$subMenuArrayItems  = array(
			array ("", "My Profile"),
			array ("courses", "My Courses"),
			array ("grades", "My Grades"),
			array ("forms", "My Sign Offs"),
			array ("placements", "My Placements"),
			array ("tutor", "My Tutor"),						
			
		);


		// If Staff
		if($userType==1)
		{
			$subMenuArrayItems  = array(
			array ("", "My Profile"),
			array ("courses", "My Courses"),
			array ("tutees", "My Tutees"),		
			array ("tutee-timeslots", "Tutee Bookings"),		
			
			);
		}
		
		return $subMenuArrayItems;

	}
	
	// Pads our the student ID with leading zeros if needed
	public static function getValidStudentID($studentID)
	{
		$studentID = str_pad($studentID, 8, "0", STR_PAD_LEFT);	

		return 	$studentID;
	
	}
	
	
	public static function getFileTypeClass($ext)
	{
		
		$ext = strtolower($ext);
		
		switch ($ext)
		{	
		
			case "doc":
			case "docx":
				$class = 'docDownload';			
			break;
			
			case "pdf":
				$class = 'pdfDownload';
			break;	

			case "ppt":
			case "pptx":
				$class = 'pptDownload';
			break;	
			
			case "xlsx":
			case "xls":
			case "csv":
				$class = 'xlsDownload';
			break;				
			
			default:
			
				$class = 'txtDownload';
			break;
		}
		
		// Add FiletypeLink to add the padding
		return ' fileTypeLink '.$class.' ';
		
	}
	
	static function getUKdate($inputDate)
	{
		$tz = new DateTimeZone('Europe/London');
		$date = new DateTime($inputDate);
		$date->setTimezone($tz);
		$UKdate = $date->format('Y-m-d H:i:s');
		
		
		return $UKdate;
	}	
	

	
	// Sort multidimensional arrays
	// Use FOR EXAMPLE 	usort($array, imperialNetworkUtils::build_sorter('arrayVALUE'));

	static function build_sorter($key)
	{
		return function ($a, $b) use ($key) {
			return strnatcasecmp($a[$key], $b[$key]);
		};
	}	
	
	
	static function getGradeString($grade)
	{
		
		$gradeLookupArray = array(
		"P"	=> "Pass",
		"A"	=> "Grade A",
		"B"	=> "Grade B",
		"C"	=> "Grade C",
		"D"	=> "Grade D",
		"DI*"	=> "Star Distinction",
		"DI"	=> "Distinction",
		"M"	=> "Merit",
		"X"	=> "Fail",
		);
				
		
		if(array_key_exists($grade, $gradeLookupArray))
		{
			$myGrade = $gradeLookupArray[$grade];
			return $myGrade;
		}
		
		return $grade;
	}
	
	static function getOrdinal($num)
	{
		$last=substr($num,-1);
		if( $last>3  or 
			$last==0 or 
			( $num >= 11 and $num <= 19 ) )
		{
			$ext='th';
		}
		else if( $last==3 )
		{
			$ext='rd';
		}
		else if( $last==2 )
		{
			$ext='nd';
		}
		else 
		{
			$ext='st';
		}
		return $num.$ext;
	}
	
	static function restrictToStaff()
	{

		if($_SESSION['userType']>1)
		{
			echo 'You do not have permission to view this page (staff only)';
			die();
		}
		
		
	}
}


?>