<h1>Imperial Users</h1>




<?php
// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{


	
	$myAction = $_GET['action'];
	switch ($myAction)
	{
		
		case "deleteUserCheck":
			$username = $_GET['username'];
			echo 'Are you sure you want to delete the user '.$username.'<br/>';
			echo '<a href="?page=imperial-network-users&action=userDelete&username='.$username.'">Yes delete this user</a>';
			echo ' | <a href="?page=imperial-network-users">Cancel</a><hr/>';
		break;
		
		case "userDelete":
				global $wpdb;
				global $imperialNetworkDB;	
				$userTable = $imperialNetworkDB::imperialTableNames()['dbTable_users'];	
				$deleteUsername = $_GET['username'];
				// Using where formatting.
				$wpdb->delete( $userTable, array( 'username' => $deleteUsername ), array( '%s' ) );
				
				echo '<div class="notice notice-success is-dismissible"><p>User Deleted</p></div>';
				
				
				
		break;
		
		
		case "userEdit":
		
		
			// Check the nonce before proceeding;	
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			
			if (wp_verify_nonce($retrieved_nonce, 'userEdit_nonce' ) )
			{		


				global $wpdb;
				global $imperialNetworkDB;					
				
				$userTable = $imperialNetworkDB::imperialTableNames()['dbTable_users'];		
		
		
		
				$username = $_POST['username'];
				
				$userInfo = imperialQueries::getUserInfo($username);
				
				if(isset($userInfo['username']) )
				{
					// update
					
					
					// Do the update				
					$wpdb->query( $wpdb->prepare(
						"UPDATE   ".$userTable." SET 
						userID=%s,
						first_name=%s,
						last_name=%s,						
						email=%s,
						yos=%d,
						user_type=%d,
						deptID=%s						
						WHERE username = %s",
						$_POST['userID'],
						$_POST['first_name'],
						$_POST['last_name'],
						$_POST['email'],
						$_POST['yos'],
						$_POST['user_type'],
						$_POST['deptID'],
						$username
					));  					
					
					
					echo '<div class="notice notice-success is-dismissible"><p>User Updated</p></div>';
				}
				
				else
				{
					// insert					
					$myFields="INSERT into $userTable (userID, first_name, last_name, username, email, user_type, yos, deptID) ";
					$myFields.="VALUES (%d, %s, %s, %s, %s, %d, %d, %s)";

					$RunQry = $wpdb->query( $wpdb->prepare($myFields,
						$_POST['userID'],
						$_POST['first_name'],
						$_POST['last_name'],
						$_POST['username'],
						$_POST['email'],
						$_POST['user_type'],
						$_POST['yos'],
						$_POST['deptID']
					));					
					
					echo '<div class="notice notice-success is-dismissible"><p>User Created</p></div>';
					
				}

				
		
		
		
				
				
			}
		
		
		break;
		
		
		

		case "CSVUpload":
		
		
			// Check the nonce before proceeding;	
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			
			if (wp_verify_nonce($retrieved_nonce, 'CSV_UploadNonce' ) )
			{
				$newFilename = dirname(__FILE__).'\tempImport.csv';
				
				if(isset($_FILES['csvFile']['tmp_name']))
				{
					
					move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
					
					// Go through the CSV stuff
					ini_set('auto_detect_line_endings',1);
					$handle = fopen($newFilename, 'r');

					global $wpdb;
					global $imperialNetworkDB;					
					
					
					$titlesArray = array
					(
						"Professor",
						"Prof",
						"Dr",
						"Mr",
						"Miss",
						"Ms",
						"Doctor",
						"Mrs",
					);
					
					//$thisTable = $wpdb->prefix . $networkTables['dbTable_facultyList'];
					$userTable = $imperialNetworkDB::imperialTableNames()['dbTable_users'];
					$tutorAllocationstable = $imperialNetworkDB::imperialTableNames()['dbTable_tutorAllocations'];
					$importCount = 0;
					$errorCount = 0;
					$errorLog='';
					
					$currentRow=1;

										
					while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
					{
						
						if($currentRow>1)
						{
							$userID = utf8_encode(trim($data[0])); // A
							$status = utf8_encode(trim(strtolower($data[1]))); // B
							$title = utf8_encode(trim($data[2])); // C
							$firstName = utf8_encode(trim(ucfirst($data[3]))); // D
							$preferredFirstName = utf8_encode(trim(ucfirst($data[4]))); // E
							$surname = utf8_encode(trim(ucfirst($data[5])));	// F	
							$gender = utf8_encode(trim(lcfirst($data[6])));	// G												
							$programmeCode = utf8_encode(trim($data[7]));	// H												
							$academicYear = filter_var($data[8], FILTER_SANITIZE_NUMBER_FLOAT);	// I					
							$yos = filter_var($data[9], FILTER_SANITIZE_NUMBER_FLOAT); //J
							$email = utf8_encode(trim(strtolower($data[10]))); //K
							$username = utf8_encode(trim(strtolower($data[11]))); // L
							$tutorName = utf8_encode(trim($data[12])); //M
							$tutorEmail = utf8_encode(trim($data[13])); //N
							$tutorUsername = utf8_encode(trim(strtolower($data[14]))); //O
							
							// Correct email issues with non utf chars
							//$email = preg_replace('/[^\p{L}\p{N}\s]/u', '', $email);
							//$tutorEmail = preg_replace('/[^\p{L}\p{N}\s]/u', '', $tutorEmail);
							
							
							$gender = substr($gender, 0, 1);
							
							$userID = imperialNetworkUtils::getValidStudentID($userID); // Pad out with Zeros
							
							
							
							//
							
							// Create Tutor Components
							$tutorTitle='';
							$tutorFirstName='';
							$tutorSurname='';

							// Split the tutorname into parts
							$tutorNameParts = explode(" ", $tutorName);
							
							//echo '<pre>';
							//print_r($tutorNameParts);
							//echo '</pre>';
							// Detect if first bit is a title
							$tutorFirstPart = ucfirst($tutorNameParts[0]);
							
							if(in_array($tutorFirstPart, $titlesArray) )
							{
								$tutorTitle = $tutorFirstPart;								
								array_shift($tutorNameParts);
								//echo '<pre>';
								//print_r($tutorNameParts);
								//echo '</pre>';
							}
							
							$tutorSurname = array_pop($tutorNameParts);
							$tutorFirstName = implode(" ", $tutorNameParts);					

							
							
							// Manually Set vars for medical students
							$user_type=4;
							$deptID = "SM";							
							
							$userTable = $imperialNetworkDB->imperialTableNames()['dbTable_users'];		
							
							// Delete the old data and add it
							if($username<>"" && filter_var($email, FILTER_VALIDATE_EMAIL) )
							{

						
								$importCount++;
								// See if they exist or not
								$userInfo = imperialQueries::getUserInfo($username);
								
								if($userInfo['username']<>"")
								{
									// Do the update
									//echo 'EXISTS = do the update for '.$username.' in '.$userTable.'<br/>';
									
									// Do the update				
									$wpdb->query( $wpdb->prepare(
										"UPDATE  $userTable SET
										userID=%s,
										first_name=%s,
										last_name=%s,
										email=%s,
										user_status=%s,
										programme=%s,
										title=%s,
										yos=%d
										WHERE username = %s",
										$userID,
										$firstName,
										$surname,
										$email,
										$status,
										$programmeCode,
										$title,
										$yos,
										$username
									));
								}
								else
								{
									// Do the insert
									//echo 'DOES NOT EXIST = do the insert<br/>';
									$myFields="INSERT into $userTable (userID, first_name, last_name, username, email, user_type, programme, yos, deptID) ";
									$myFields.="VALUES (%s, %s, %s, %s, %s, %d, %s, %d, %s)";

									$RunQry = $wpdb->query( $wpdb->prepare($myFields,
										$userID,
										$firstName,
										$surname,
										$username,
										$email,
										$user_type,
										$programmeCode,
										$yos,
										$deptID
									));							

								}
								
								// Now process the tutor
								// See if they exist or not
								$tutorInfo = imperialQueries::getUserInfo($tutorUsername);
								
								
								// If they have a tutor add to the tutor table
								if($tutorUsername<>"")
								{
									
									$wpdb->delete($tutorAllocationstable, array('username' => $username, 'academicYear' => $academicYear));									
									
									
									
									$myFields="INSERT into $tutorAllocationstable (username, tutorUsername, academicYear) ";
									$myFields.="VALUES (%s, %s, %d)";

									$RunQry = $wpdb->query( $wpdb->prepare($myFields,										
										$username,
										$tutorUsername,
										$academicYear
									));
									
									
								}
								
								
								
								if($tutorInfo['username']<>"")
								{
									// Do the update
									//echo 'EXISTS = do the update for TUTOR '.$tutorUsername.' in '.$userTable.'<br/>';
									// Do the update	
									
									//echo 'tutorFirstName = '.$tutorFirstName.'<br/>';
									//echo 'tutorSurname = '.$tutorSurname.'<br/>';
									//echo 'tutorEmail = '.$tutorEmail.'<br/>';
									//echo 'title = '.$title.'<br/>';
									//echo 'tutorUsername = '.$tutorUsername.'<br/>';
									
									$wpdb->query( $wpdb->prepare(
										"UPDATE  $userTable SET										
										first_name=%s,
										last_name=%s,
										email=%s,										
										title=%s										
										WHERE username = %s",										
										$tutorFirstName,
										$tutorSurname,
										$tutorEmail,										
										$title,
										$tutorUsername
									));
									
									
								}
								else
								{
									// Do the insert
									//echo 'DOES NOT EXIST = do the insert<br/>';
									$myFields="INSERT into $userTable (first_name, last_name, username, email, user_type, deptID) ";
									$myFields.="VALUES (%s, %s, %s, %s, %d, %s)";

									$RunQry = $wpdb->query( $wpdb->prepare($myFields,										
										$tutorFirstName,
										$tutorSurname,
										$tutorUsername,
										$tutorEmail,
										1,
										$deptID
									));
								}

							}
							else
							{
								$errorCount++;
								$errorLog.='The Email '.$email.' is invalid.<br/>';
							}	// End of if valid email
							
						}
						$currentRow++;

					} // End CSV loop
					
				} // End if file type is CSV
				// Now delete the temp file
				unlink ($newFilename);
				
				echo '<div class="admin-settings-group">';
				echo '<h2>Upload Report</h2>';
				echo '<strong>'.$importCount.'</strong> users imported<br/>';
				echo '<strong>'.$errorCount.'</strong> errors reported';
				if($errorCount>=1)
				{
					echo '<br/><strong>Error Log</strong><br/> ';$errorLog;
				}
				echo '</div>';
			
			
			}

		break;				
	} // End of switch
} // End is action



$view = '';
if ( isset( $_GET['view'] ) )
{
	$view = $_GET['view'];
}



if($view=="userEdit")
{
	
	if(isset($_GET['username']) )
	{
		
		$username = $_GET['username'];
		$userInfo = imperialQueries::getUserInfo($username);
	}
	else
	{
		$username='';
		$userInfo = array(
		"first_name" => "",
		"last_name" => "",
		"email" => "",
		"userID" => "",
		"yos" => "",
		"username" => "",
		"user_type" => "",
		"deptID" => "",
		
		);
	}
		
	
	echo '<form method="post" action="?page=imperial-network-users&action=userEdit" class="imperial-form">';

	
	if($username=="")
	{	
		echo '<h2>Create new user</h2>';

		echo '<label for="username">Username</label>';
		echo '<input name="username" id="username">';
		
		$buttontext = 'Create User';
	}
	else
	{
		
		echo '<h1>'.$userInfo['first_name'].'</h1>';
		
		echo 'Username : '.$userInfo['username'].'<br/>';
		echo '<input type="hidden" value="'.$username.'" name="username">';
		$buttontext = 'Update User';			
	}

	
	echo '<label for="first_name">First Name</label>';
	echo '<input name="first_name" id="first_name" value="'.$userInfo['first_name'].'">';

	
	echo '<label for="last_name">Last Name</label>';
	echo '<input name="last_name" id="last_name" value="'.$userInfo['last_name'].'">';
	
	echo '<label for="email">Email</label>';
	echo '<input name="email" id="email" value="'.$userInfo['email'].'">';
	
	echo '<label for="userID">User ID</label>';
	echo '<input name="userID" id="userID" value="'.$userInfo['userID'].'">';
	
	echo '<label for="yos">Year of Study</label>';
	echo '<input name="yos" id="yos" value="'.$userInfo['yos'].'">';	
	
	$facultyArray = imperialQueries::getFaculties();
	
	echo '<label for="deptID">Department</label>';
	echo '<select id="deptID" name="deptID">';
	foreach ($facultyArray as $facultyID => $facultyInfo)
	{		
		$facultyName = $facultyInfo['Faculty'];
		$deptList = array();
		if(isset($facultyInfo['Departments']) )
		{
			$deptList = $facultyInfo['Departments'];
		}
		
		echo '<option value="'.$facultyID.'"';
		if($userInfo['deptID']==$facultyID) {echo ' selected ';}		
		echo '>'.$facultyName.' ('.$facultyID.')</option>';
		
		foreach($deptList as $deptID => $deptName)
		{			
			echo '<option value="'.$deptID.'"';
			if($userInfo['deptID']==$deptID) {echo ' selected ';}
			
			echo '> - '.$deptName.' ('.$deptID.')</option>';
		}	
	}
	echo '</select>';
	
	
	$userTypesArray = imperialNetworkUtils::getUserTypesArray();
	// Reverse array so UG is default
	$userTypesArray = array_reverse($userTypesArray, true);	
	
	echo '<label for="user_type">User Type</label>';
	echo '<select name="user_type" id="user_type">';
	foreach($userTypesArray as $KEY => $VALUE)
	{
		echo '<option value="'.$KEY.'" ';
		if($userInfo['user_type']==$KEY)
		{
			echo ' selected ';
		}

		
		echo '/>'.$VALUE.'</option>';
	}
	
	echo '</select>';
	


	
	echo '<input type="submit" value="'.$buttontext.'">';
	
	wp_nonce_field('userEdit_nonce');    
	
	
	echo '</form>';
		

	
	
	
	
}
else
{
	
	echo '<a href="?page=imperial-network-users&view=userEdit" class="button-secondary">Create User</a>';
	
	
	
	
	
	
	
	

	?>
	<div class="admin-settings-group">
	<h2>Bulk Upload Students CSV</h2>
	<form name="csvUploadForm" action="?page=imperial-network-users&action=CSVUpload"  method="post" enctype="multipart/form-data">
	Upload your user list as a CSV file with the following columns:<br/>
	Student ID |  Status (e.g. Active) | Title | First Name | Preferred First Name |  Surname | Gender | Programme Code | Academic Year | Year of Study | Email | Username | Tutor Name | Tutor Email | Tutor Username<br/>
	<input type="file" name="csvFile" size="20"/><br/>
	<input type="submit" value="Upload" name="submit" class="button-primary" />
	<?php
	// Add nonce
	wp_nonce_field('CSV_UploadNonce');    
	?>

	</form>
	</div>
<?php

	/* Get the list of faculties */
	$usersArray = imperialQueries::getUsers();

	echo '<table class="imperialTable" id="imperialTable">';
	echo '<thead><tr><th>Name</th><th>Username</th><th>User ID</th><th>Type</th><th>Dept</th><th></th></tr><thead>';
	echo '<tbody>';

	foreach ($usersArray as $userMeta)
	{
		$firstName = $userMeta['first_name'];
		$lastName = $userMeta['last_name'];
		$username = $userMeta['username'];
		$userID = $userMeta['userID'];
		$userType = $userMeta['user_type'];
		$deptID = $userMeta['deptID'];
		
		// GEt the User Type
		$userTypeStr = imperialNetworkUtils::getUserTypeStr($userType);
		
		
		echo '<tr>';
		echo '<td><a href="?page=imperial-network-users&username='.$username.'&view=userEdit">';
		
		echo $lastName.', '.$firstName.'</a></td>';	
		echo '<td>'.$username.'</td>';	
		echo '<td>'.$userID.'</td>';	
		echo '<td>'.$userTypeStr.'</td>';	
		echo '<td>'.$deptID.'</td>';
		echo '<td><a href="?page=imperial-network-users&action=deleteUserCheck&username='.$username.'">Delete</a></td>';
		
		echo '</tr>';
		
	}

	echo '</tbody></table>';


}


?>

<script>
	jQuery(document).ready(function(){	
		if (jQuery('#imperialTable').length>0)
		{
			jQuery('#imperialTable').dataTable({
				"bAutoWidth": true,
				"bJQueryUI": true,
				"sPaginationType": "full_numbers",
				"iDisplayLength": 50, // How many numbers by default per page
			//	"order": [[2, "desc"]]
			});
		}
		
	});
</script>	