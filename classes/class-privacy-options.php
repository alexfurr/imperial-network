<?php


new imperialPrivacyOptions();

class imperialPrivacyOptions
{
	
	
	var $allowedPostTypes = array('page', 'imperial_topic', 'topic_session', 'session_page');
	
	function __construct ()
	{
		$this->addWPActions();
	}
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		//Save the data
		add_action( 'save_post', array( $this, 'savePostData' ) );
		
		// Post type metaboxes
		add_action( 'add_meta_boxes', array( $this, 'addPrivacyMetaBox' ));		
		add_filter( 'the_content', array($this, 'checkPagePrivacy' ) );
		
	}
	
	
	
	// Register the metaboxes
	function  addPrivacyMetaBox()
	{
		
		//Privacy Metabox
		$id 			= 'imperial_privacy';
		$title 			= 'Page Privacy';
		$drawCallback 	= array( $this, 'drawMetaBox_imperialPrivacy' );
		$screen 		= $this->allowedPostTypes;
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
		

		
		
		
		
		// Check the privacy options and only add them if its available to all in the college
		$siteVis = get_option('blog_public');
		
		if($siteVis	== "-1")
		{

			add_meta_box( 
				$id, 
				$title, 
				$drawCallback, 
				$screen, 
				$context,
				$priority, 
				$callbackArgs
			);		
		}		
		
	
		
		
	}
	
	function drawMetaBox_imperialPrivacy($post, $metabox)
	{
		

		// Add Nonce Field
		wp_nonce_field( 'save_imperial_privacy_nonce', 'imperial_privacy_nonce' );
		
		$imperialPrivatePage = get_post_meta( $post->ID, 'imperialPrivatePage', true );


		echo '<label for="imperialPrivatePage">';
		echo '<input type="checkbox" name="imperialPrivatePage" id="imperialPrivatePage" ';
		if($imperialPrivatePage=="on")
		{
			echo ' checked ';
		}
		echo '/>Restrict page to enrolled students</label>';
		echo '</label>';


	}	
	

	
	
	function savePostData( $post_id )
	{	
	

	
		$allowedPostTypes = $this->allowedPostTypes;

	
		if ( empty( $_POST['post_type'] )  ) {
			return;
		}
		
		if(!in_array($_POST['post_type'], $allowedPostTypes) )
		{
			return;
		}
		
		
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		
		if(isset($_POST['imperial_privacy_nonce']) )
		{
			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['imperial_privacy_nonce'], 'save_imperial_privacy_nonce' ) ) {
				return;
			}	
			

			if( ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			
			$state = ( isset( $_POST['imperialPrivatePage'] ) ) ? 'on' : '';
			update_post_meta( 
				$post_id, 
				'imperialPrivatePage', 
				$state
			);			
			
			
		}		
		


	}	
	
	
	function checkPagePrivacy($content)
	{

	
		if(!is_admin() )
		{
			global $post;
			
			// Check to see if its a private page		
			$imperialPrivatePage = get_post_meta( $post->ID, 'imperialPrivatePage', true );
			
			if($imperialPrivatePage=="on")
			{
				// Check to see if they are enrolled on the site
				
				if(current_user_can('read') )
				{
					return $content;
				}
				else
				{
					return 'This content is restricted to enrolled students';
				}
				
			}
		}
		
		return $content;
		
	}


}



?>