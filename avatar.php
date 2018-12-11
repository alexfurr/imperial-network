<?php


/**
*	
*	---
*/
function get_user_avatar( $args )
{		
	$url = get_user_avatar_url( $args);
	return '<img src="' . $url. '?hash=' .create_hash(). '" alt="Your Avatar - Click for your profile" title="Your Avatar - Click for your profile" />';
}



/**
*	
*	---
*/
function has_avatar( $args )
{
	$url = get_user_avatar_url( $args );
	
	//return strpos( $url, 'avatar_blank' ) === false ? true : false;
    return strpos( $url, 'user-default' ) === false ? true : false;
}


/**
*	
*	---
*/
function get_user_avatar_url( $args )
{
	
	$userID='';
	$size='';
	$CID='';
	$imperialMugshot = '';
	
	if(isset($args['userID']) ){$userID=$args['userID'];}
	if(isset($args['size']) ){$size=$args['size'];}
	if(isset($args['CID']) ){$CID=$args['CID'];}
	
    //http://wp.elearningimperial.com/wp-content/themes/imperial-medlearn/assets/images/user-default.png
    //$default_avatar = get_template_directory_uri() . '/media/images/theme/avatar_blank.png';
    $default_avatar = IMPERIAL_NETWORK_PLUGIN_URL . '/assets/user-default.png';
    
//    if ( ! $userID ) {
		//return $default_avatar;
//	}

	if($CID)
	{
		$imperialMugshot = get_imperial_mugshot($CID);
	}
	
	if($imperialMugshot)
	{
		return $imperialMugshot;
	}
	
	
	
	// Get upload dir info
    $switched = false;
	if ( 1 !== get_current_blog_id() ) {
		switch_to_blog( 1 );
		$switched = true;
	}
	
	$uploadDirInfo = wp_upload_dir();
	
	if ( $switched ) {
		restore_current_blog();
	}
	
	$avatar 	= '';
	$uploadPath = $uploadDirInfo['basedir'] . '/umedia/' . $userID;
	$size 		= ! $size ? '' : '-' . $size;
	
	if ( file_exists ( $uploadPath ) )
	{			
		$items 	= array();
		$handle = @opendir( $uploadPath );
		
		if ( $handle ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( $file != '.' && $file != '..' && filetype( $uploadPath . '/' . $file ) == 'file' ) {
					$items[] = $file;
				}
			}
		}
		closedir( $handle );
		
		if ( ! empty( $items ) ) {
			foreach ( $items as $j => $file ) {
				$name = explode( '.', $file );
				if ( isset( $name[0] ) && $name[0] == 'profile-img' . $size ) {
					$avatar = $uploadDirInfo['baseurl'] . '/umedia/' . $userID . '/' . $file;
					break;
				}
			}
		}
	}
	
	return ! $avatar ? $default_avatar : $avatar;
}



function get_imperial_mugshot($CID)
{
	// Get upload dir info
    $switched = false;
	if ( 1 !== get_current_blog_id() ) {
		switch_to_blog( 1 );
		$switched = true;
	}
	
	$uploadDirInfo = wp_upload_dir();
	
	if ( $switched ) {
		restore_current_blog();
	}
	
	$mugshotRef 	= '/imperial-student-mugshots/' . $CID.'.png';
	
	$mugshotCheck = $uploadDirInfo['basedir'] . $mugshotRef;	
	
	
	if ( file_exists ( $mugshotCheck ) )
	{
		$mugshotURL = $uploadDirInfo['baseurl'].$mugshotRef;
		return $mugshotURL;
	}
	else
	{
		return false;
	}
}



/**
*	
*	---
*/
function save_user_avatar ( $userID, $url )
{
	$success = false;
	
	//delete existing
	delete_user_avatar( $userID );
	
	// Get upload dir info
	$switched = false;
	if ( 1 !== get_current_blog_id() ) {
		switch_to_blog( 1 );
		$switched = true;
	}
	
	$uploadDirInfo = wp_upload_dir();
	
	if ( $switched ) {
		restore_current_blog();
	}
	
	$filename = strrchr( $url, "/");
	$filename = str_replace( "/_", "", $filename );
	$uploadPath = $uploadDirInfo['basedir'].'/umedia/' . $userID;
	
	if ( file_exists ( $uploadPath ) ) 
	{
		$tmp_name = $uploadPath . '/_' . $filename;
		$userImageSrc =  $uploadPath . '/' . $filename;
		$success = copy( $tmp_name, $userImageSrc );
		
		$img = wp_get_image_editor( $userImageSrc );
		if ( ! is_wp_error( $img ) ) 
		{
			$img->resize( 300, 300, true );
			$filename = $img->generate_filename( 'square', $uploadPath, NULL );
			$img->save($filename);
			
			$img = wp_get_image_editor( $userImageSrc );	
			$img->resize( NULL, 100, false );
			$filename = $img->generate_filename( 'small', $uploadPath, NULL );
			$img->save( $filename );	
			
			$img = wp_get_image_editor( $userImageSrc );	
			$img->resize( NULL, 300, false );
			$filename = $img->generate_filename( 'medium', $uploadPath, NULL );
			$img->save($filename);	
		}
	}
	return $success;
}



/**
*	
*	---
*/
function delete_user_avatar( $userID )
{
	// Get upload dir info
	$switched = false;
	if ( 1 !== get_current_blog_id() ) {
		switch_to_blog( 1 );
		$switched = true;
	}
	
	$uploadDirInfo = wp_upload_dir();
	
	if ( $switched ) {
		restore_current_blog();
	}
	
	$uploadPath = $uploadDirInfo['basedir'].'/umedia/' . $userID . '/profile-img';
	$extensions = array( '.jpg', '.png', '.gif', '.JPG', '.PNG', '.GIF' );
	
	foreach ( $extensions as $ext ) {
		if ( file_exists ( $uploadPath . $ext ) ) {
			unlink( $uploadPath . $ext );
		}
		if ( file_exists ( $uploadPath . '-small' . $ext ) ) {
			unlink( $uploadPath . '-small' . $ext );
		}
		if ( file_exists ( $uploadPath . '-medium' . $ext ) ) {
			unlink( $uploadPath . '-medium' . $ext );
		}
		if ( file_exists ( $uploadPath . '-square' . $ext ) ) {
			unlink( $uploadPath . '-square' . $ext );
		}
	}
}	



/**
*	Returns a random 16 digit string.
*	---
*/
function create_hash ()
{
	$randomstring = bin2hex( openssl_random_pseudo_bytes( 16 ) );
	$hash = hash( 'md5', $randomstring );
	return $hash;
}



?>