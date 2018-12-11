<h1>Add Users to this Site</h1>
<div id="feedbackDiv"></div>
<?php
// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}

		$myAction = $_GET['action'];
		switch ($myAction)
		{

			case "CSVUpload":
			
				if (wp_verify_nonce($retrieved_nonce, 'formNonce' ) )
				{			
	
					$newFilename = dirname(__FILE__).'/tempImport.csv';
					
					if(isset($_FILES['csvFile']['tmp_name']))
					{
						
						move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
						
						// Go through the CSV stuff
						ini_set('auto_detect_line_endings',1);
						$handle = fopen($newFilename, 'r');

						global $wpdb;
						
						$studentCount = 0;
						while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
						{
							$username = trim(strtolower($data[0]));

							// Check to see if the user has a wordpress account
							$userInfo = get_user_by( 'login', $username );
							
							if($userInfo)
							{
								$userID = $userInfo->ID;			
							}
							else
							{
								$userID = imperialNetworkActions::createWP_user($username);			
							}
							
							if($userID)
							{
								// Now make them a subscriber
								$userObject = new WP_User( $userID );
								// Add role
								$userObject->set_role( 'subscriber' );
								$studentCount++;
							}
							
							
						}
						
					} // End if file type is CSV
					// Now delete the temp file
					unlink ($newFilename);	
					
					echo '<div class="notice notice-success is-dismissible"><p>'.$studentCount.' users added as students</p></div>';
			
				}
			break;
			
			
			case "bulkdelete":			
				
				$blogusers = get_users( 'role=subscriber' );
				
				$userCount = count($blogusers);
				echo '<div class="notice notice-success is-dismissible"><p>'.$userCount.' students removed succesfully</p></div>';
				
				$blogusers = get_users( 'role=subscriber' );
				
				// Array of WP_User objects.
				foreach ( $blogusers as $user ) {
					
					$userID = $user->ID;
					$blogID = get_current_blog_id();
					remove_user_from_blog($userID, $blogID);
				}				
				
				
				
			
			break;
			
			
	}// End if grouopsUpload case	
} // End is action

?>
<div class="admin-settings-group">

<h2>Add a single user</h2>

<?php

$blogID = get_current_blog_id();

$args = array
(
	"myAction"	=> "addUserToBlog",
	"blogID" 	=> $blogID,
	"updateDiv"	=> "feedbackDiv",	


);

echo imperialNetworkDraw::userSearchForm($args);


?>
</div>

<div class="admin-settings-group">
<h2>Bulk Upload Students</h2>
<form name="csvUploadForm" action="users.php?page=imperial-add-users&action=CSVUpload"  method="post" enctype="multipart/form-data">
Upload your user list as a CSV file with a single column containing username<br/>
<br/>
<input type="file" name="csvFile" size="20"/><br/>
<input type="submit" value="Upload" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('formNonce');    
?>

</form>
</div>

<div class="admin-settings-group">
<h2>Bulk Remove Students</h2>
<button id="confirmDeleteStudentsCheck" class="button-secondary">Remove All students</button>
<?php

echo '<div id="confirmDeleteStudentsDiv" style="display:none; padding-top:20px;">';
echo 'Are you sure you want to remove all students from this site?<br/><br/>';
echo '<a href="?page=imperial-add-users&action=bulkdelete&type=subscribers" class="button-primary">Yes, remove all students</a>';
echo '</div>';
?>

<script>
jQuery( "#confirmDeleteStudentsCheck" ).click(function() {
  jQuery( "#confirmDeleteStudentsDiv" ).toggle("fast");
});
</script>

</div>



<?php




?>

