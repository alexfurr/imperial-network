<h1>Student Mugshots</h1>

<?php
echo 'Current Max Upload Size = '.$upload_max_size = ini_get('upload_max_filesize').'<br/>';


// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{

	$myAction = $_GET['action'];
	switch ($myAction)
	{
		case "zipUpload":
			// Check the nonce before proceeding;	
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			
			
			echo 'test';
			
			if (wp_verify_nonce($retrieved_nonce, 'ZIP_UploadNonce' ) )
			{
				echo '<div class="admin-settings-group" style="width:90%">';
				echo '<h1>Upload Check</h1>';
				echo 'Passed Nonce Check<br/>';
				
				if($_FILES["zip_file"]["name"])
				{

					
					$filename = $_FILES["zip_file"]["name"];
					$source = $_FILES["zip_file"]["tmp_name"];
					$type = $_FILES["zip_file"]["type"];

					echo 'Filename : '.$filename.'<br/>';
					echo 'Type : '.$type.'<br/>';
					
					$name = explode(".", $filename);
					$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
					foreach($accepted_types as $mime_type) {
						if($mime_type == $type) {
							$okay = true;
							break;
						} 
					}
					
					$continue = strtolower($name[1]) == 'zip' ? true : false;
					if(!$continue) {
						echo  "The file you are trying to upload is not a .zip file. Please try again.";
					}
					
					$zipFilename = pathinfo($_FILES['zip_file']['name'], PATHINFO_FILENAME);

					$upload = wp_upload_dir();
					$upload_dir = $upload['basedir'];								
					$this_upload_dir = $upload_dir . '/imperial-student-mugshots';
										
					if (! is_dir($this_upload_dir))
					{
						echo 'Upload dir does not exist - create it<br/>';						
						if(mkdir( $this_upload_dir, 0755 ))
						{
							echo 'Created Folder OK<br/>';
						}
						
						else
						{
							echo 'Failed to create folder<br/>';							
						}
					}

					$target_path = $this_upload_dir.'/'.$filename;

					echo 'Attempting to move from:<br/>';
					
					if(move_uploaded_file($source, $target_path)) {
						$zip = new ZipArchive();
						$x = $zip->open($target_path);
						if ($x === true) {
							$zip->extractTo($this_upload_dir); // change this to the correct site path
							$zip->close();
					
							unlink($target_path);
						}
						echo "Your .zip file was uploaded and unpacked.<br/>";
						
					} else {	
						echo "There was a problem with the upload. Please try again.";
					}
				}	

				echo  '</div>';	

			}

		break;				
	} // End of switch
} // End is action
?>
<div class="admin-settings-group" style="width:300px">
<form class="imperial-form" name="zipUploadForm" action="?page=imperial-network-add-mugshots&action=zipUpload"  method="post" enctype="multipart/form-data">
Upload a zip file<br/>
<input type="file" name="zip_file" size="20"/><br/>
<input type="submit" value="Upload" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('ZIP_UploadNonce');    
?>

</form>
</div>


