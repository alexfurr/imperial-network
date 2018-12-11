<?php

$imperialNetworkDB = new imperialNetworkDB();

class imperialNetworkDB
{
	
	// Was 0.6
	var $DBversion = 1.0;

	
	
	static function imperialTableNames()
	{
		
		global  $wpdb;
		$prefix = $wpdb->base_prefix;
		

		/* Define some table names */		
		$imperialNetworkTables = array
		(
			"dbTable_users"	=> $prefix."imperial_users", // DB of ALL users
			"dbTable_courses"	=> $prefix."imperial_courses", // All courses
			"dbTable_studentEnrolments"	=> $prefix."imperial_student_enrolments", // Student Enrolments
			"dbTable_staffEnrolments"	=> $prefix."imperial_staff_enrolments", // Staff enrolments
			"dbTable_facultyList"	=> $prefix."imperial_faculties", // All Faculties
			"dbTable_facultyAdmins"	=> $prefix."imperial_faculty_admins", // List of faculty admins by username
			"dbTable_arbitraryCourses"	=> $prefix."imperial_arbitrary_courses", // List of generic courses and who requested them
			"dbTable_siteCategories"	=> $prefix."imperial_site_categories", // List of generic courses and who requested them
			"dbTable_siteCategoryAllocations"	=> $prefix."imperial_site_cat_allocations", // List of generic courses and who requested them
			"dbTable_tutorAllocations"	=> $prefix."imperial_tutor_allocations", // List of generic courses and who requested them			
			"dbTable_studentGrades"	=> $prefix."imperial_student_grades", // List of generic courses and who requested them			
			"dbTable_assessmentCodes"	=> $prefix."imperial_assessment_codes", // List of generic courses and who requested them			
			
			
			
			
		);	

		return 	$imperialNetworkTables;
		
	}
	

	
	//~~~~~
	function __construct ()
	{
		add_action( 'init',  array( $this, 'checkCompat' ) );
	}

	//~~~~~
	function checkCompat ()
	{
	
		// Get the Current DB and check against this verion
		$currentDBversion = get_option('myImperialDB_version');
		$thisDBversion = $this->DBversion;
		

		if($thisDBversion>$currentDBversion)
		{
			
			$this->createTables();
			update_option('myImperialDB_version', $thisDBversion);			
		}
		//$this->createTables(); // Testing		
	}
	
	
	
	function createTables ()
	{


		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		// Get the Character set
		$WPversion = substr( get_bloginfo('version'), 0, 3);
		$charset_collate = ( $WPversion >= 3.5 ) ? $wpdb->get_charset_collate() : $this->getCharsetCollate();
		
		// Create the Users Table
		//$thisTable = $wpdb->base_prefix.$this->imperialNetworkTables["dbTable_users"];

		$thisTable = $this->imperialTableNames()['dbTable_users'];		
		
		
		$sql = "CREATE TABLE $thisTable (
			userID varchar(50),
			first_name varchar(255),
			last_name varchar(255),
			title varchar(50),
			username varchar(50) NOT NULL,
			email varchar(255),
			user_type tinyint,
			yos tinyint,
			programme varchar(50),
			programmeName varchar(255),
			deptID varchar(255),
			user_status varchar(50),
			preferred_email varchar(255),
			user_bio TEXT,
			job_title varchar(255),
			PRIMARY KEY (username)
			
		) $charset_collate;";
		dbDelta( $sql );
		
		

		// Create the Courses Table
		//$thisTable = $wpdb->base_prefix.$this->imperialNetworkTables["dbTable_courses"];
		$thisTable = $this->imperialTableNames()['dbTable_courses'];		
		
		$sql = "CREATE TABLE $thisTable (
			courseID int NOT NULL AUTO_INCREMENT,
			course_code varchar(50),
			course_name varchar(255),
			deptID varchar (50),
			academic_year int,
			course_lead varchar(50),
			semester int,
			yos int,
			theme varchar (255),
			programme_code varchar (255),
			blogID int,
			PRIMARY KEY (courseID)
			
			
		) $charset_collate;";			
		dbDelta( $sql );		
		
		
		// Create the Student Enrolments Table
		//$thisTable = $wpdb->base_prefix.$this->imperialNetworkTables["dbTable_studentEnrolments"];
		$thisTable = $this->imperialTableNames()['dbTable_studentEnrolments'];		
	
		$sql = "CREATE TABLE $thisTable (
			courseID varchar(50) NOT NULL,
			studentID varchar(50) NOT NULL			
			
		) $charset_collate;";
			
		dbDelta( $sql );	

		// Create the staff  Enrolments Table
		//$thisTable = 	$wpdb->base_prefix.$this->imperialNetworkTables["dbTable_staffEnrolments"];
		$thisTable = $this->imperialTableNames()['dbTable_staffEnrolments'];		
		
		$sql = "CREATE TABLE $thisTable (
			courseID varchar(50) NOT NULL,
			staffID varchar(50) NOT NULL,
			staff_type varchar(255) NOT NULL
			
		) $charset_collate;";			
		dbDelta( $sql );
		
		// Create the Faculty list table
		//$thisTable = $wpdb->base_prefix.$this->imperialNetworkTables["dbTable_facultyList"];
		$thisTable = $this->imperialTableNames()['dbTable_facultyList'];		
		// Create the staff  Enrolments Table
		$sql = "CREATE TABLE $thisTable (
			deptID varchar(50) NOT NULL,
			deptName varchar(255) NOT NULL,
			parentID varchar(50)
			
		) $charset_collate;";			
		dbDelta( $sql );
		
		// Create the Faculty Admin table
		//$thisTable = $wpdb->base_prefix.$this->imperialNetworkTables["dbTable_facultyAdmins"];
		$thisTable = $this->imperialTableNames()['dbTable_facultyAdmins'];	
		
		// Create the staff  Enrolments Table
		$sql = "CREATE TABLE $thisTable (
			deptID varchar(50) NOT NULL,
			username varchar(255) NOT NULL			
			
		) $charset_collate;";			
		dbDelta( $sql );		
		
				
		// Create the Arbitrary Courses table
		$thisTable = $this->imperialTableNames()['dbTable_arbitraryCourses'];	
		
		// Create the staff  Enrolments Table
		$sql = "CREATE TABLE $thisTable (
			blogID int  NOT NULL,
			username varchar(255) NOT NULL,
			deptID	varchar(50),
			createDate datetime
			
		) $charset_collate;";		

		
		dbDelta( $sql );	



		// Create the site category table
		$thisTable = $this->imperialTableNames()['dbTable_siteCategories'];		
		$sql = "CREATE TABLE $thisTable (
			ID int NOT NULL AUTO_INCREMENT,
			cat_name varchar(255),
			academic_year int,
			deptID varchar(50),
			yos int,
			PRIMARY KEY (ID)
			
		) $charset_collate;";			
		dbDelta( $sql );	
		
		// Create the site category table
		$thisTable = $this->imperialTableNames()['dbTable_siteCategoryAllocations'];		
		$sql = "CREATE TABLE $thisTable (
			siteID int NOT NULL,
			catID int NOT NULL
			
		) $charset_collate;";			
		dbDelta( $sql );
		
		// Create the tutor allocations
		$thisTable = $this->imperialTableNames()['dbTable_tutorAllocations'];		
		$sql = "CREATE TABLE $thisTable (
			username varchar(255) NOT NULL,
			tutorUsername varchar(255) NOT NULL,
			academicYear int				
		) $charset_collate;";			
		dbDelta( $sql );	
		
		
		// Create the student grades table			
		$thisTable = $this->imperialTableNames()['dbTable_studentGrades'];		
		$sql = "CREATE TABLE $thisTable (
			ID int NOT NULL AUTO_INCREMENT,		
			studentID varchar(50) NOT NULL,
			yearOfProgramme INT NOT NULL,
			academicYear INT NOT NULL,
			unitCode varchar(50) NOT NULL,
			unitMark  int,
			unitGrade  varchar(10),
			gradeDate date,
			PRIMARY KEY (ID),
			INDEX (studentID),
			INDEX (unitCode, academicYear)
		) $charset_collate;";			
		dbDelta( $sql );		


		// Create the assessment codes for lookup
		$thisTable = $this->imperialTableNames()['dbTable_assessmentCodes'];		
		$sql = "CREATE TABLE $thisTable (
			unitCode varchar(50) NOT NULL,
			unitTitle varchar(255) NOT NULL,
			PRIMARY KEY (unitCode)			
		) $charset_collate;";			
		dbDelta( $sql );	
		


	}

	
	function getCharsetCollate () 
	{
		global $wpdb;
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) )
		{
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) 
		{
			$charset_collate .= " COLLATE $wpdb->collate";
		}
		return $charset_collate;
	}	

}



?>