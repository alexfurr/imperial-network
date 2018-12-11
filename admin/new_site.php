<h1>Create New Site</h1>
<?php
$showForm=true; // Show form by default

//get_blog_details of root - use for domain and path etc
$network_info = get_current_site();
$domain = $network_info->domain;
$path = $network_info->path;


$siteURL = '';
$siteTitle = '';

if ( isset( $_GET['action'] ) )
{
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}


	
	$myAction = $_GET['action'];
	switch ($myAction)
	{
	
		case "createSite":
		
			if (wp_verify_nonce($retrieved_nonce, 'newSite_UploadNonce' ) )
			{
			
			
				$errorArray = array();
				$allowCreate = true; // By Default don't create it
				
				$siteTitle = $_POST['siteTitle'];
				$siteURL = $_POST['siteURL'];	
				
				$site_type = $_POST['site_type'];
				
				
				$academicYear = $_POST['academicYear'];
				$yos = $_POST['yos'];
				
				$programme= $_POST['programme'];

				
				$siteURL = strtolower($siteURL);
				//Make alphanumeric (removes all other characters)
				$siteURL = preg_replace("/[^a-z0-9_\s-]/", "", $siteURL);
				//Clean up multiple dashes or whitespaces
				$siteURL = preg_replace("/[\s-]+/", " ", $siteURL);
				//Convert whitespaces and underscore to dash
				$siteURL = preg_replace("/[\s_]/", "-", $siteURL);
				
				
				
				if($siteTitle=="")
				{
					$errorArray[] = 'Your Sitename cannot be blank'; 
					$allowCreate=false;
				}
				
				if($siteURL=="")
				{
					$errorArray[] = 'Your Site URL cannot be blank'; 
					$allowCreate=false;
				}				
				
				
				
				
				//pharm-201718
				
				// See if the site exists already	
				$checkIfExists = get_blog_id_from_url($domain, $path.$siteURL."/");
				if($checkIfExists<>0)
				{
					$allowCreate=false;
					$errorArray[] = 'The site URL '.$domain. $path.$siteURL.' already exists'; 
				}
				
				
				// See if that user exists
				$adminUsername = $_POST['adminUsername'];
				$userCheck = get_user_by( 'login', $adminUsername );
				
				if(!is_object($userCheck))
				{
					$allowCreate=false;
					$errorArray[] = 'The username "'.$adminUsername.'" cannot be found'; 
				}
				
				
				
				if($allowCreate==false)
				{
					echo '<ul>';
					foreach($errorArray as $errorMessage)
					{
						echo'<li>'.$errorMessage.'</li>';
					}
					echo '</ul>';

				}
				else
				{

					
					$showForm=false;
					
					
					echo '<h3>Create site with the following information</h3>';
					
					echo 'Blog Name : <b>'.stripslashes($siteTitle).'</b><br/>';
					echo 'Blog URL  : <b>'.$domain, $path.$siteURL."/".'</b><br/>';
					echo 'Admin  : <b>'.$userCheck->first_name . ' ' . $userCheck->last_name.' ('.$adminUsername.')<br/>';
					echo 'Site Type  : <b>'.$site_type.'</b><br/>';
					
					if($site_type=="course")
					{
						echo 'Academic Year : <b>'.$academicYear.'</b><br/>';
						echo 'Year of Study : <b>'.$yos.'</b><br/>';
						echo 'Programme : <b>'.$programme.'</b><br/>';
					}
					
					// Hidden Input fields

					echo '<form name="confirmSiteCreation" action="?page=imperial-network-add-site&action=confirmCreateSite"  method="post">';
					wp_nonce_field('confirmNewSite_UploadNonce');
					
					
					echo '<input type="hidden" name="siteTitle" value="'.$siteTitle.'">';
					echo '<input type="hidden"  name="siteURL" value="'.$siteURL.'">';
					echo '<input type="hidden" name="adminUsername" value="'.$adminUsername.'">';
					echo '<input type="hidden" name="siteType" value="'.$site_type.'">';
					echo '<input type="hidden" name="programme" value="'.$programme.'">';
					echo '<input type="hidden" name="academicYear" value="'.$academicYear.'">';
					echo '<input type="hidden" name="yos" value="'.$yos.'">';

					
					echo '<hr/><input type="submit" class="button-primary" value="Yes, create this site">';

					
					echo '</form>';
				
				}
				

				//echo '<div class="notice  notice-success">';
				//echo 'Site Created';
				//echo '</div>';
			}
			
		break;
		
		case "confirmCreateSite":
		
			if (wp_verify_nonce($retrieved_nonce, 'confirmNewSite_UploadNonce' ) )
			{
				
				$siteTitle = $_POST['siteTitle'];
				$siteURL = $_POST['siteURL'];
				$requesterUsername = $_POST['adminUsername'];
				$siteType = $_POST['siteType'];
				$programme = $_POST['programme'];
				
				
				
				$args = array
				(
					"siteTitle"			=> $siteTitle,
					"siteURL"			=> $siteURL,
					"siteType"			=> $siteType,
					"programme"			=> $programme,
					"requesterUsername"	=> $requesterUsername,
				);
				
				$feedback = imperialNetworkActions::createSite($args);
				echo $feedback;
			}
		break;
		
	}
	

}

if($showForm==true)
{
	
	?>

	<form class="imperial-form" name="createSiteForm" action="?page=imperial-network-add-site&action=createSite"  method="post" enctype="multipart/form-data">
	<?php
	wp_nonce_field('newSite_UploadNonce');
	?>
	
	<label for="siteTitle">Site Name</label>
	<input type="text" placeholder="Site Name" name="siteTitle" id="siteTitle" value="<?php echo $siteTitle;?>" />
	
	
	<label for="siteURL">Site URL : <?php	echo $domain.$path;	?></label>
	<input type="text" placeholder="Site URL" name="siteURL" id="siteURL"  value="<?php echo $siteURL;?>"/>	
	
	
	<label for="adminUsername">Primary Admin Username</label>
	<input type="text" placeholder="Username" name="adminUsername" id="adminUsername" />	
	

	
	
	<h3>Site Type</h3>

	<label for="site_type_generic">
	<input type="radio" value="generic" name="site_type" id="site_type_generic" checked>Arbitrary</label>
	
	<label for="site_type_course">
	<input type="radio" value="course" name="site_type" id="site_type_course">Course</label>	

	<label for="site_type_emodule">
	<input type="radio" value="emodule" name="site_type" id="site_type_emodule">eModule</label>
	<hr/>
	
	<div id="courseMetaDiv" style="display:none">
	
		<label for="academicYear">Academic Year</label>
		<select id="academicYear" name="academicYear">
		<?php
		$academicYearsArray = imperialNetworkUtils::getAcademicYearsArray();	
		foreach ($academicYearsArray as $key => $value)
		{
			echo '<option value="'.$key.'">'.$value.'</option>';
		}
		
		?>
		</select>		
		
		<label for="yos">Year of Study</label>
		<select id="yos" name="yos">
		<?php
		$i=1;
		while($i<=6)
		{
			echo '<option value="'.$i.'">Year '.$i.'</option>';
			$i++;
		}	
		?>
		</select>
		
		<label for="yos">Programme</label>
		<select id="programme" name="programme">
		<?php
		echo '<option value="MBBS">MBBS</option>';
		echo '<option value="BMB">BMB</option>';
		?>
		</select>		
	
	</div> <!-- end of course meta --->

	
	<input type="submit" value="Create" class="button-primary" />
	</form>
	
	<script>
	// With the element initially shown, we can hide it slowly:
	jQuery( "#site_type_generic, #site_type_emodule" ).click(function() {
	  jQuery( "#courseMetaDiv" ).hide( "quick");
	});	
	
	jQuery( "#site_type_course" ).click(function() {
	  jQuery( "#courseMetaDiv" ).show( "quick");
	});	
	
	
	
	
	</script>

<?php
}


				
				




?>





