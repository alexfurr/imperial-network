<h1>Courses</h1>
<?php


// Define some course fields for editing / updating later

$courseFieldsArray = array(
	array ("course_code", "Course Code"),
	array ("course_name", "Course Name"),
	array ("deptID", "Dept ID"),
	array ("academic_year", "Academic Year"),
	array ("yos", "Year Of Study"),
	array ("blogID", "Blog ID"),
	array ("programme_code", "Programme"),

);


$view = '';
if ( isset( $_GET['view'] ) )
{
	$view = $_GET['view'];
}

// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{
	// Check the nonce before proceeding;	


	
	$myAction = $_GET['action'];
	switch ($myAction)
	{
		
		
		case "courseEdit":
		
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			if (wp_verify_nonce($retrieved_nonce, 'courseEdit_nonce' ) )
			{	
		
				$courseID = $_POST['courseID'];				
				
				global $wpdb;
				global $imperialNetworkDB;				
	
				$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];	
				
				
				
				// Do the update				
				$wpdb->query( $wpdb->prepare(
					"UPDATE   ".$table_name." SET 
					course_code=%s,
					course_name=%s,
					deptID=%s,
					academic_year=%d,
					programme_code=%s,
					yos=%d,
					blogID=%d
					
					WHERE courseID = %d",
					$_POST['course_code'],
					$_POST['course_name'],
					$_POST['deptID'],
					$_POST['academic_year'],
					$_POST['programme_code'],
					$_POST['yos'],
					$_POST['blogID'],
					$courseID
				));  	

				echo '<div class="notice notice-success is-dismissible"><p>Course Updated</p></div>';

			}		
		
		break;
		
		case "createCourse":
		
			$courseID = $_GET['courseID'];
			
			// Get the Course meta
			$courseInfo = imperialQueries::getCourseInfo($courseID);
			
					
			$course_code = $courseInfo['course_code'];
			$course_name = $courseInfo['course_name'];
			$semester = $courseInfo['semester'];
			$blogID = $courseInfo['blogID'];
			
			
			// Quit if it already exists
			if($blogID)
			{
				echo 'Blog Already Exists for this site';
				die();
			}
			
			$yos = $courseInfo['yos'];
			$academicYear = $courseInfo['academic_year'];			
			
			$courseMeta = array(
				"academicYear"	=> $academicYear,
				"yos" 			=> $yos,
				"courseID"		=> $courseID,
			);
			
			$course_name;
			
			$siteURL = imperialNetworkUtils::makeBlogURL($course_name.' '.substr($academicYear, 2));
		
			$args = array
			(
				"siteTitle"	=> $course_name,
				"siteURL"	=> $siteURL,
				"siteType"	=> "course",
				"courseMeta"	=> $courseMeta,
			);
			

			$feedback = imperialNetworkActions::createSite($args);		
			echo $feedback;
		
		
		break;
		
		
		
	
		case "CSVUpload":
				
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			if (wp_verify_nonce($retrieved_nonce, 'CSV_UploadNonce' ) )
			{		
		
				$newFilename = dirname(__FILE__).'/tempImport.csv';
				
				if(isset($_FILES['csvFile']['tmp_name']))
				{
					
					move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
					
					// Go through the CSV stuff
					ini_set('auto_detect_line_endings',1);
					$handle = fopen($newFilename, 'r');

					global $wpdb;
					global $imperialNetworkDB;
					$thisTable = $imperialNetworkDB::imperialTableNames()['dbTable_courses'];

					
					$row = 1;
					while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
					{
						$courseID = $data[0];
						$course_code= $data[1];
						$course_name= $data[2];
						$deptID = $data[3];
						$academic_year= $data[4];
						$course_lead= $data[5];
						$semester= $data[6];
						$yos= $data[7];
						$theme= $data[8];
						
						echo $courseID.' '.$course_code.' ('. $course_name.')<hr/>';
						
						// Ignore the first row (header)
						if($row>1)
						{
							$myFields="INSERT into $thisTable (courseID, course_code, course_name, deptID, academic_year, course_lead, semester, yos, theme) ";
							$myFields.="VALUES (%s, %s, %s, %s, %d, %s, %d, %d, %s)";
							
							$RunQry = $wpdb->query( $wpdb->prepare($myFields,
								$courseID,
								$course_code,
								$course_name,
								$deptID,
								$academic_year,
								$course_lead,
								$semester,
								$yos,
								$theme
							));
							
							echo $RunQry.'<br/>';
							
							$RunQry;
						}
						
						$row++;
					}
					
				} // End if file type is CSV
				// Now delete the temp file
				unlink ($newFilename);	
			} // End of nonce check
		}// End if grouopsUpload case	
} // End is action


?>
<!--
<form name="csvUploadForm" action="?page=imperial-network-courses&action=CSVUpload"  method="post" enctype="multipart/form-data">
Upload your user list as a CSV file with the following columns:<br/>
courseID,course_code,course_name,deptID,academic_year,course_lead,semester
<br/>
<input type="file" name="csvFile" size="20"/><br/>
<input type="submit" value="Upload" name="submit" class="button-primary" />

-->
<?php
// Add nonce
wp_nonce_field('CSV_UploadNonce');    
?>

<!-- </form> -->


<?php

/* Get the list of course */


if($view=="courseEdit")
{
	$courseID = $_GET['courseID'];
	$courseInfo = imperialQueries::getCourseInfo($courseID);
	
	echo '<form action="admin.php?page=imperial-network-courses&action=courseEdit" method="post" class="imperial-form">';
	
	foreach ($courseFieldsArray as $fieldInfo)
	{
		echo '<label for="'.$fieldInfo[0].'">'.$fieldInfo[1].'</label>';
		echo '<input id="'.$fieldInfo[0].'" name="'.$fieldInfo[0].'" value="'.$courseInfo[$fieldInfo[0]].'">';
	}
	
	echo '<input type="hidden" value="'.$courseID.'" name="courseID">';
	echo '<input type="submit" value="Update Course Info">';
	wp_nonce_field('courseEdit_nonce');
	echo '</form>';
	
}
else
{

	$args = array(); // Get all Courses
	$courseList = imperialQueries::getCourses($args);
	$deptLookupArray = imperialQueries::getFacultyLookupArray();


	echo '<table id="coursesTable" class="imperialTable imperialTableAdmin">';

	echo '<thead><tr><th>Course Name</th><th>Academic Year</th><th>YOS</th><th>Dept</th><th>Site ID</th><th></th></tr></thead>';

	foreach ($courseList as $courseInfo)
	{
		$courseID = $courseInfo['courseID'];
		$course_code = $courseInfo['course_code'];
		$course_name = $courseInfo['course_name'];
		$semester = $courseInfo['semester'];
		$blogID = $courseInfo['blogID'];
		$yos = $courseInfo['yos'];
		$academicYear = $courseInfo['academic_year'];
		$academicYear = imperialNetworkUtils::getNiceAcademicYear($academicYear);
		$deptID = $courseInfo['deptID'];

		if($blogID)
		{
			$blogURL = get_site_url($blogID);
			$createBlogLink = '<a class="button-primary" href="'.$blogURL.'">Visit Site</a>';
		}
		else
		{
			$createBlogLink = '<a class="button-secondary" href="admin.php?page=imperial-network-courses&action=createCourse&courseID='.$courseID.'">Create Site</a>';
		}	
		echo '<tr>';	
		//echo '<td>'.$course_code.'</td>';

		echo '<td>';
		
		if($blogID)
		{
			echo '<a href="'.$blogURL.'">';
		}
		
		echo $course_name;
		
		if($blogID)
		{
			echo '</a>';
		}	
		echo '</td>';
		echo '<td>'.$academicYear.'</td>';
		echo '<td>'.$yos.'</td>';
		echo '<td>';
		$deptName = $deptLookupArray[$deptID];
		
		echo $deptName.' ( '.$deptID.')';
		echo '</td>';

		echo '<td>'.$createBlogLink.'</td>';
		echo '<td><a class="button-secondary" href="?page=imperial-network-courses&view=courseEdit&courseID='.$courseID.'">Edit</a></td>';	
		echo '</tr>';
	}

	echo '</table>';
	
	
	?>
	<script>
	jQuery(document).ready(function(){	
		if (jQuery('#coursesTable').length>0)
		{
			jQuery('#coursesTable').dataTable({
				"bAutoWidth": true,
				"bJQueryUI": true,
				"sPaginationType": "full_numbers",
				"iDisplayLength": 50, // How many numbers by default per page
			//	"order": [[2, "desc"]]
			});
		}
		
	});
</script>	
<?php

}


?>
