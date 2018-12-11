<?php

class imperialQueries
{

	static function getUsers($searchStr="")
	{
		global $wpdb;
		global $imperialNetworkDB;
		
		if($searchStr)
		{
			$searchStr=' WHERE username  LIKE "%'.$searchStr.'%" OR 
						userID LIKE "%'.$searchStr.'%" OR 
						last_name LIKE "%'.$searchStr.'%" ';
		}
		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_users'];
		$SQL='Select * FROM '.$table_name.$searchStr.' ORDER by last_name ASC';
		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;		
		
	}
	
	
	
	// Pass userID or username - defaults to username
	static function getUserInfo($str, $strType="username")
	{
		global $wpdb;
		global $imperialNetworkDB;
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_users'];
		
		switch ($strType)
		{
			
			case "username":
				$whereStr = ' WHERE username = "'.$str.'"';				
			break;

			case "userID":
				$whereStr = ' WHERE userID = "'.$str.'"';				
			break;			
			
			
		}
		
		$SQL='Select * FROM '.$table_name.$whereStr;
		
		$userInfo = $wpdb->get_row( $SQL, ARRAY_A );	
		return $userInfo;		
		
	}	
	
	static function getCourses($args)
	{
		global $wpdb;
		global $imperialNetworkDB;
		

		
		$deptID_where = '';
		$academicYear_where='';	
		$programme_where = '';		
		
		if(isset($args['academicYear']) )
		{
			$academicYear_where = ' WHERE academic_year = "'.$args['academicYear'].'"';
		}			
		
		if(isset($args['programme']) )
		{
			
			if($academicYear_where<>"")
			{
				$programme_where.=' AND ';
			}			
			
			$programme_where.= ' programme_code = "'.$args['programme'].'"';
		}			
		
		if(isset($args['deptID']) )
		{
			
			if($academicYear_where<>"" || $programme_where<>"")
			{
				$deptID_where.=' AND ';
			}
			
			$deptID_where.= ' deptID = "'.$args['deptID'].'"';
		}
		
	
		
		//$academicYear = $args['academicYear'];
		//$deptID = $args['deptID'];
//		$table_name = $wpdb->base_prefix . $imperialNetworkDB->imperialNetworkTables['dbTable_courses'];
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];
		
		$deptID = "";
		
		$SQL='Select * FROM '.$table_name.' '.$academicYear_where.$programme_where.$deptID_where.'  ORDER by academic_year ASC, yos ASC, course_code ASC';
	
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;
	}
	
	static function getCourseInfo($courseID)
	{
		global $wpdb;
		global $imperialNetworkDB;		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];
		
		$SQL='Select * FROM '.$table_name.' WHERE courseID = "'.$courseID.'"';
		
		$courseInfo = $wpdb->get_row( $SQL, ARRAY_A );	
		return $courseInfo;
	}
	
	static function getCourseInfoFromBlogID($blogID)
	{
		global $wpdb;
		global $imperialNetworkDB;		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];
		
		$SQL='Select * FROM '.$table_name.' WHERE blogID = '.$blogID;
		
		
		$courseInfo = $wpdb->get_row( $SQL, ARRAY_A );	
		return $courseInfo;
	}	
	
	static function getDeptInfo($deptID)
	{
		
		global $wpdb;
		global $imperialNetworkDB;
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_facultyList'];
		// Get the Faculties first
		$SQL='Select * FROM '.$table_name.' WHERE deptID="'.$deptID.'"';
		$deptInfo = $wpdb->get_row( $SQL, ARRAY_A );	
		
		return $deptInfo;
		
	}
	
	static function getFaculties()
	{
		global $wpdb;
		global $imperialNetworkDB;
	//	$table_name = $wpdb->base_prefix . $imperialNetworkDB->imperialNetworkTables['dbTable_facultyList'];

		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_facultyList'];
		
		
		// Get the Faculties first
		$SQL='Select * FROM '.$table_name;
		$facultyList = $wpdb->get_results( $SQL, ARRAY_A );	
		
		// Create the master array
		$masterFacultyList = array();
		
		foreach($facultyList as $deptInfo)
		{
			$deptName = $deptInfo['deptName'];
			$deptID = $deptInfo['deptID'];
			$parentID = $deptInfo['parentID'];
			
			if($parentID=="faculty")
			{
				$masterFacultyList[$deptID]["Faculty"] = $deptName;
			}
		}
		
		// Go through again and add the depts
		foreach($facultyList as $deptInfo)
		{
			$deptName = $deptInfo['deptName'];
			$deptID = $deptInfo['deptID'];
			$parentID = $deptInfo['parentID'];
			
			if($parentID<>"faculty")
			{
				// Get parent Name
				//$masterFacultyList[$parentID][$deptID] =  $deptName;
				
				$masterFacultyList[$parentID]["Departments"][$deptID] = $deptName;
			}

		}

		
		return $masterFacultyList;

		
		
	}
	
	
	// Get a sinlge arrya of all deptIDs for quick lookup
	static function getFacultyLookupArray()
	{
		global $wpdb;
		global $imperialNetworkDB;
	//	$table_name = $wpdb->base_prefix . $imperialNetworkDB->imperialNetworkTables['dbTable_facultyList'];

		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_facultyList'];
		
		
		// Get the Faculties first
		$SQL='Select * FROM '.$table_name;
		$facultyList = $wpdb->get_results( $SQL, ARRAY_A );	
		
		// Create the master array
		$deptArray = array();
		
		foreach($facultyList as $deptInfo)
		{
			$deptName = $deptInfo['deptName'];
			$deptID = $deptInfo['deptID'];
			$parentID = $deptInfo['parentID'];
			
			$deptArray[$deptID] = $deptName;
		}
		
		// Go through again and add the depts
		foreach($facultyList as $deptInfo)
		{
			$deptName = $deptInfo['deptName'];
			$deptID = $deptInfo['deptID'];
			$parentID = $deptInfo['parentID'];
			$deptArray[$deptID] = $deptName;


		}

		
		return $deptArray;

		
	}	
	
	
	static function getDeptAdmins($deptID="")
	{
		global $wpdb;
		global $imperialNetworkDB;
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_facultyAdmins'];

		
		
		$deptClause='';
		if($deptID<>"")
		{
			
			$deptClause =  ' WHERE deptID = "'.$deptID.'"';
		}
		
		$SQL='Select * FROM '.$table_name.$deptClause;
		
		$adminsRS = $wpdb->get_results( $SQL, ARRAY_A );
		
		$userAdminArray = array();
		foreach ($adminsRS as $adminInfo)
		{
			$username = $adminInfo['username'];
			
			if($username)
			{
			
				// Get User Info based on getuserbylogin
				$userIDMeta = get_user_by('login', $username);



				$userID = $userIDMeta->ID;	
				$userEmail = $userIDMeta->user_email;
				$userMeta = get_user_meta($userID); 
				
				
				$userAdminArray[$username] = array
				(
					"firstName"	=> $userMeta['first_name'][0],
					"lastName"	=> $userMeta['last_name'][0],
					"email" 	=> $userEmail,
					"userID" 	=> $userID,
				);
			}
			
			
		}
		
		/*
		echo '<pre>';
		print_r($userAdminArray);
		echo '</pre>';
		*/
		
		return $userAdminArray;
		

	}
	
	
	// Get students by year group
	public static function getStudentsByYOS($yos, $programmeCode="MBBS")
	{
		
		global $wpdb;
		global $imperialNetworkDB;
		
		// Get the array of programme codes for this programme
		$myCodes = imperialQueries::getCourseCodesForProgramme($programmeCode);	
		
		$codeCount = count($myCodes);
		// Query for the programme
		$programmeQuery = ' AND (';
		$i=1;
		foreach ($myCodes as $thisCode)
		{
			$programmeQuery.= ' programme = "'.$thisCode.'" '; 
			if($codeCount>$i)
			{
				$programmeQuery.= ' OR '; 
			}
			$i++;
			
		}
		$programmeQuery.=')';
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_users'];
		$SQL='Select * FROM '.$table_name.' WHERE yos = '.$yos.' '.$programmeQuery.' ORDER by last_name ASC';
		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;			
		
	}
	
	static function getCourseCodeArray()
	{
		$codeArray = array(
			"A100" 	=> array (
				"name"	=> "Medicine (MBBS & BSc 6YFT)",
				"programme"	=> "MBBS",
			),
			"A109" 	=> array (
				"name"	=> "Graduate Medicine (MBBS 5YFT)",
				"programme"	=> "MBBS",
			),
			"A300" 	=> array (
				"name"	=> "Medicine (Oxbridge entrants) (MBBS 5YFT)",
				"programme"	=> "MBBS",
			),
			"B101" 	=> array (
				"name"	=> "BSc Medical Biosciences (3YFT)",
				"programme"	=> "BMB",
			),
			"B111" 	=> array (
				"name"	=> "BSc Medical Biosciences with Management(4YFT)",
				"programme"	=> "BMB",
			),
			"B900" 	=> array (
				"name"	=> "Biomedical Science (BSc 3YFT)",
				"programme"	=> "BMB",
			),
			"B9N2" 	=> array (
				"name"	=> "Biomedical Sciences with Management (BSc 4YFT)",
				"programme"	=> "BMB",
			),


		);	
		
		return $codeArray;
	}
	
	
	static function getCourseCodesForProgramme($programme)
	{
		
		if($programme=="MBBS")
		{
			$codeArray = array(
				"A100",
				"A101",
				"A109",
				"A300",
			);
			return $codeArray;
		}
		
		if($programme=="BMB")
		{
			$codeArray = array(
				"B101",
				"B111",
				"B900",
				"B9N2",
			);
			return $codeArray;
		}		
		
	}

	
	static function getCourseNameFromCode($code)
	{
		$codeArray = imperialQueries::getCourseCodeArray();
		
		$courseName='';
		if(isset($codeArray[$code]) )
		{
			$courseName = $codeArray[$code]['name'];
		}
		
		return $courseName;
	}
	
	static function getMyAssessments($CID)
	{

		global $wpdb;
		global $imperialNetworkDB;
		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_studentGrades'];
		$SQL='Select * FROM '.$table_name.' WHERE studentID = "'.$CID.'"';
		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		
		return $rs;

	}	
	
	
	static function getAssessmentCode($unitCode)
	{

		global $wpdb;
		global $imperialNetworkDB;
		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_assessmentCodes'];
		$SQL='Select unitTitle FROM '.$table_name.' WHERE unitCode = "'.$unitCode.'"';
		
		$rs = $wpdb->get_row( $SQL, ARRAY_A );
		
		return $rs;

	}
	
	static function getAllAssessmentCodes()
	{

		global $wpdb;
		global $imperialNetworkDB;
		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_assessmentCodes'];
		$SQL='Select *  FROM '.$table_name;
		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		$lookupArray = array();
		foreach ($rs as $resultInfo)
		{
			$lookupArray[$resultInfo['unitCode'] ]= $resultInfo['unitTitle'];
		}
		
		return $lookupArray;

	}	
	
		
	// Gets all grades for a fgiven ac year in a given code
	static function getUnitGrades($unitCode, $academicYear)
	{

		global $wpdb;
		global $imperialNetworkDB;		
		
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_studentGrades'];
		$SQL='Select unitMark, studentID FROM '.$table_name.' WHERE unitCode = "'.$unitCode.'" AND academicYear='.$academicYear;
		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;

	}
	
	
	
}