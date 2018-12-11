<h1>Faculty List</h1>
<?php
if ( ! defined( 'ABSPATH' ) ) 
{
	die();	// Exit if accessed directly
}


// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'CSV_UploadNonce' ) )
	{

	
		$myAction = $_GET['action'];
		switch ($myAction)
		{
		
			case "CSVUpload":
			
			
				$newFilename = dirname(__FILE__).'\tempImport.csv';	

				
				if(isset($_FILES['csvFile']['tmp_name']))
				{
					
					move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
					
					// Go through the CSV stuff
					ini_set('auto_detect_line_endings',1);
					$handle = fopen($newFilename, 'r');

					global $wpdb;
					global $imperialNetworkDB;
					
					
					//$thisTable = $wpdb->prefix . $networkTables['dbTable_facultyList'];
					$thisTable = $imperialNetworkDB::imperialTableNames()['dbTable_facultyList'];
					

					// Remove Old Faculties
					$delete = $wpdb->query("TRUNCATE TABLE $thisTable");
					
					
					
					while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
					{
						$deptID = trim($data[0]);
						$deptName= trim($data[1]);
						$parentID= trim($data[2]);
						
						if($deptID)
						{
							
							$myFields="INSERT into $thisTable (deptID, deptName, parentID) ";
							$myFields.="VALUES (%s, %s, %s)";
							
							$RunQry = $wpdb->query( $wpdb->prepare($myFields,
								$deptID,
								$deptName,
								$parentID
							));
							
							$RunQry;
						}
					}
					
					
				} // End if file type is CSV
				// Now delete the temp file
				unlink ($newFilename);	
				
			break;
		} // End of switch
	}// End of nonce check
} // End is action


?>

<form name="csvUploadForm" action="?page=imperial-network-faculties&action=CSVUpload"  method="post" enctype="multipart/form-data">
Upload your faculty list as CSV file with the following columns:<br/>
Faculty/Dept Name, Faculty/Dept code,parent code
<br/>
For Faculties use 'faculty' in the parentID field
<hr/>
<input type="file" name="csvFile" id="csvFile" size="20"/><br/>
<input type="submit" value="Upload" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('CSV_UploadNonce');    
?>

</form>


<?php

/* Get the list of faculties */
$facultyArray = imperialQueries::getFaculties();

// Get the Root Blog URL
$myRootBlog = get_site_option( "imperial_root_blog"  );

$home_blog_details = get_blog_details($myRootBlog);
$adminURL = $home_blog_details->siteurl.'/admin/?deptID=';


echo '<table class="imperialTable">';
echo '<tbody>';

foreach ($facultyArray as $facultyID => $facultyInfo)
{
	
	$facultyName = $facultyInfo['Faculty'];
	$deptList = array();
	if(isset($facultyInfo['Departments']) )
	{
		$deptList = $facultyInfo['Departments'];
	}
	
	echo '<tr><td><h2>'.$facultyName.'</h2></td><td>'.$facultyID.'</td></tr>';	
	foreach($deptList as $deptID => $deptName)
	{
		
		echo '<tr>';
		echo '<td><a href="'.$adminURL.$deptID.'">'.$deptName.'</a></td>';
		echo '<td><a href="?page=imperial-network-faculty-settings&deptID='.$deptID.'">Settings</a></td>';
		echo '<td>'.$deptID.'</td>';
		

		echo '</tr>';
	}	
}

echo '</tbody></table>';



?>