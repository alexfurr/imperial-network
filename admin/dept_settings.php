
<?php
if ( ! defined( 'ABSPATH' ) ) 
{
	die();	// Exit if accessed directly
}


if(!isset($_GET['deptID']))
{
	die();
}

$deptID = $_GET['deptID'];

// Get the Dept Name
$deptInfo = imperialQueries::getDeptInfo($deptID);
$deptName = $deptInfo['deptName'];



$academicYear = "201718";

if(isset($_GET['academicYear']) )
{
	$academicYear = $_GET['academicYear'];
}



// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'form_nonce' ) )
	{

	
		$myAction = $_GET['action'];
		switch ($myAction)
		{
		
			case "addCat":
			
			
				$academicYear = $_POST['academicYear'];				
				$catName = $_POST['catName'];
				$yos = $_POST['yos'];
			
				imperialSiteCategories::addCat($deptID, $academicYear, $catName, $yos);


				
			break;
			
			
			
			case "updateCat":
			
				$catName = $_POST['catName'];
				$yos = $_POST['yos'];
				$catID = $_GET['catID'];
				
			
				imperialSiteCategories::updateCat($catID, $catName, $yos);			
			
			
			break;
		} // End of switch
	}// End of nonce check
} // End is action


?>




<?php

echo '<h1>'. $deptName.' : Site Categories ('.imperialNetworkUtils::getNiceAcademicYear($academicYear).')</h1>';


$academicYearArray = imperialNetworkUtils::getAcademicYearsArray();

echo '<select name="academicYearSwap" id="academicYearSwap">';
foreach ($academicYearArray as $academicYearValue => $niceAcademicYear)
{
	echo '<option value="'.$academicYearValue.'" ';
	if($academicYear==$academicYearValue){echo ' selected';}
	
	echo '>'.$niceAcademicYear.'</option>';
}

echo '</select>';
echo '<label for="academicYearSwap"> Academic Year</label>';


?>

<div class="admin-settings-group">
<form class="imperial-form" name="site_cat_form" action="?page=imperial-network-faculty-settings&action=addCat&deptID=<?php echo $deptID;?>"  method="post">


<?php

// Get the cats



// Add nonce
wp_nonce_field('form_nonce');    
echo  '<label for="catName">Category Name</label>';
echo '<input name="catName" id="catName">';

echo '<input name="academicYear" value="'.$academicYear.'" type="hidden">';
echo  '<label for="yos">Year Group</label>';
echo '<select name="yos" id="yos">';

$i=1;

while ($i<=6)
{
	echo '<option value="'.$i.'">Year '.$i.'</option>';
	$i++;
}
echo '<option value="0">No Year Group</option>';
echo '</select>';

?>

<input type="submit" class="button-primary" value="Add Category" />

</form>
</div>


<div class="admin-settings-group">

<?php

$niceAcacemicYear = imperialNetworkUtils::getNiceAcademicYear($academicYear);
echo '<h2>Categories for '.$niceAcacemicYear.'</h2>';
$previousYOS = '';

$mySiteCats = imperialSiteCategories::getCategoriesByYear($academicYear, $deptID);

if(count($mySiteCats)==0) 
{
	echo 'No Categories found';
}


echo '<table class="imperialTable">';
foreach ($mySiteCats as $yos => $yearCats)
{

	$yearTitle = 'Year '.$yos;
	if($yos==0){$yearTitle = 'No Year Group';}
	
	echo '<tr><td colspan="3"><h3>'.$yearTitle.'</h3></td></tr>';
	
	foreach ($yearCats as $catID => $catName)
	{
		
		
		echo '<tr><td valign="top">';
		echo $catName;

		
		
		echo '</td><td valign="top">';
		echo '<span style="color:#aaa">[show-cat-sites id='.$catID.']</span>';
		echo '</td><td valign="top">';
		echo '<span id="catFormEdit_'.$catID.'" class="button-secondary">Edit</span>';
		echo '</td>';
		echo '</tr><tr><td colspan="3" class="no-border">';
		echo '<div id="catForm_'.$catID.'" class="hidden">';
		echo '<form class="imperial-form" action="?page=imperial-network-faculty-settings&action=updateCat&deptID='.$deptID.'&catID='.$catID.'"  method="post">';
		echo '<label for="catName_'.$catID.'">Category Name</label>';
		echo '<input name="catName" id="catName_'.$catID.'" value="'.$catName.'" size="50">';
		echo '<label for="yos_'.$catID.'">Year of Study</label>';
		echo '<input name="yos" id="yos_'.$catID.'" value="'.$yos.'" size="1">';
		echo '<input type="submit" value="Update Category" class="button-primary">';
		wp_nonce_field('form_nonce');
		echo '</form>';
		echo '</div>';	
		echo '</td></tr>';
		

		
		?>
		<script>
		jQuery("#catFormEdit_<?php echo $catID;?>").click(function() {
			jQuery( "#catForm_<?php echo $catID;?>" ).toggle( "fast");

		});
		</script>
		<?php
	}
		

}
echo '</table>';



?>
</div>

<script>
jQuery('#academicYearSwap').change(function() {
    var newAcYear =  jQuery(this).val();
	window.location.href = "?page=imperial-network-faculty-settings&deptID=<?php echo $deptID;?>&academicYear="+newAcYear;
});
</script>