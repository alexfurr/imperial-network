<h1>Tools</h1>
<?php



//$upload_dir = wp_upload_dir(); // Array of key => value pairs
$basedir =ABSPATH.'wp-content';
$upload_url = get_site_url().'/wp-content';

$myDir = $basedir;



$myGetDir = '';
if(isset($_GET['path']) )
{
	$myGetDir = html_entity_decode(urldecode($_GET['path']));
	$myDir = $basedir.'/'.$myGetDir;
	
}


if(isset($_GET['action']) )
{
	$action = $_GET['action'];
	
	switch ($action)
	{
	
	case "checkDelete":
	
		$filename = $_GET['filename'];
		echo 'Are you sure you want to delete "'.$filename.'"<br/>';
		echo '<a class="button-primary" href="?page=imperial-network-tools&path='.$myGetDir.'&action=deleteConfirm&filename='.$filename.'"">Yes, delete this file!</a>';
	break;
	
	
	case "deleteConfirm":
		$filename = $_GET['filename'];
		
		$fileToDelete = $myDir.'/'.$filename;
		if (unlink($fileToDelete)) {
			echo 'File deleted';
		} else {
			echo 'Cannot remove that file';
		}
	break;
	
	
	}
}





$myDir = str_replace("//","/", $myDir);

echo '<h2>'.$myDir.'</h2>';

$files = scandir($myDir);


// remove the . and ..
$del_val = '.';
if (($key = array_search($del_val, $files)) !== false) {
    unset($files[$key]);
}

$del_val = '..';
if (($key = array_search($del_val, $files)) !== false) {
    unset($files[$key]);
}



$upFolderTemp = str_replace($basedir, "", $myDir);

$upFolder = substr($upFolderTemp, 0, strrpos( $upFolderTemp, '/'));

$folderStr = '<a href="?page=imperial-network-tools&path='.$upFolder.'"><i class="fas fa-level-up-alt"></i> Up</a><hr/>';


$fileStr = '';
foreach ($files as $fileName)
{
	
	$fullPath = $myDir.'/'.$fileName;
	
	if(is_dir($fullPath) && $fileName<>'..' && $fileName<>'.')
	{
		
		$newLink = $myGetDir.'/'.$fileName;
	
		$folderStr.='<i class="fas fa-folder"></i> <a href="?page=imperial-network-tools&path='.$newLink.'">';
		$folderStr.=$fileName.'</a><hr/>';
	}
	else
	{
		$filePath = $upload_url.'/'.$fileName;
		$fileStr.='<i class="fas fa-file"></i> <a href="'.$filePath.'">'. $fileName.'</a> ';
		$fileStr.='<span style="font-size:8px;"><a href="?page=imperial-network-tools&path='.$myGetDir.'&action=checkDelete&filename='.$fileName.'">Delete</a></span><hr/>';
	}
}



echo $folderStr;
echo $fileStr;

?>
