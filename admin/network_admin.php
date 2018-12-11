<h1>Imperial Network Admin</h1>
<?php
if ( ! defined( 'ABSPATH' ) ) 
{
	die();	// Exit if accessed directly
}

global $wpdb;

global $imperialNetworkDB;



/* Update network options */
if(isset($_GET['action']))
{
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'myAdminNonce' ) )
	{	
		if($_GET['action']=="saveSettings")
		{
			$rootBlogID = $_POST['imperial_root_blog'];
			update_site_option( "imperial_root_blog", $rootBlogID );
			
			$currentAcademicYear = $_POST['currentAcademicYear'];
			update_site_option( "current_academic_year", $currentAcademicYear );
			
			
			echo '<div class="updated notice"><p>Settings Updated</p></div>';
		}
	}
}




/* Get Blog list and specifiy which the main student landing page is */



$myRootBlog = get_site_option( "imperial_root_blog"  );

$currentAcademicYear = get_site_option("current_academic_year");

if($myRootBlog=="")
{
	echo '<div class="notice notice-error"><p>No Root Blog Set</p></div>';
}

echo '<form class="imperial-form" action="admin.php?page=imperial-network-admin&action=saveSettings" method="post">';


echo '<label for = "imperial_root_blog">Root blog</label>';
echo '<select name="imperial_root_blog" id="imperial_root_blog">';

$subsites = get_sites();
foreach( $subsites as $subsite ) {
  $subsiteID = get_object_vars($subsite)["blog_id"];
  $subsiteName = get_blog_details($subsiteID)->blogname;
  
  echo '<option value="'.$subsiteID.'"';
  if($myRootBlog==$subsiteID){echo ' selected ';}
  
  
  echo '>'.$subsiteName.'</option>';
}

echo '</select>';


echo '<label for="currentAcademicYear">';
echo 'Current Academic Year';
echo '</label>';

$academicYearArray = imperialNetworkUtils::getAcademicYearsArray();

echo '<select name="currentAcademicYear" id="currentAcademicYear">';
foreach ($academicYearArray as $KEY => $VALUE)
{
	echo '<option value="'.$KEY.'" ';
	if($currentAcademicYear==$KEY){echo ' selected ';}
	
	echo '>'.$VALUE.'</option>';
}

echo '</select>';


echo '<input type="submit" class="button-primary" value="Update Settings">';
wp_nonce_field('myAdminNonce');
echo '</form>';


/* Show Data about the Network */


/* Get the Table Names */

$DBversion = $imperialNetworkDB->DBversion;
$networkTables = $imperialNetworkDB::imperialTableNames();


echo '<div style="border:1px solid grey; background:#fff; padding:20px; margin:20px;">';

echo 'Table Name : <b>'.$wpdb->dbname.'</b><hr/>';

echo '<h1>Table Config</h1>';
echo 'Database Version : '.$DBversion.'<hr/>';


echo '<table class="imperialTable">';
echo '<tr><th>Description</th><th>Table Name</th><th>Status</th></tr>';

foreach($networkTables as $tableDescription => $tableName)
{
	
	echo '<tr>';
	echo '<td>'. $tableDescription.' </td>';
	echo '<td>'.$tableName.'</td>';
	
	echo '<td>';
	if($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName) {
		  //Your code here
		  echo ' <span class="successText"><i class="fas fa-check"></i> Table Exists</span>';
	}
	echo '</td>';
	
	
	echo '</tr>';
	
	
}
echo '</table>';
echo '</div>';


/*
echo 'Database Version : '.$DBversion.'<hr/>';
echo 'Users Table : '.$usersTable.'<hr/>';
echo 'Student Enrolments Table : '.$studentEnrolmentsTable.'<hr/>';
echo 'Student Enrolments Table : '.$studentEnrolmentsTable.'<hr/>';
echo 'Staff Enrolments Table : '.$staffEnrolmentsTable.'<hr/>';
*/


?>




