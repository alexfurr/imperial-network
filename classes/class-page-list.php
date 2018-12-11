<?php
$imperialPageList = new imperialPageList();

class imperialPageList
{

	//~~~~~
	function __construct ()
	{
		
		$this->addWPActions();
		
	}
	

	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		add_action( 'wp_footer', array( $this, 'frontendEnqueues' ) );	
		
		add_shortcode('imperial-child-pages', array ($this, 'drawChildList' ));
		add_shortcode('imperial-sibling-pages', array ($this, 'drawSiblingList' ));
	
	}
	
	function frontendEnqueues ()
	{

		// Progress Bars
		wp_enqueue_style( 'imperial-network-page-list', IMPERIAL_NETWORK_PLUGIN_URL . '/css/page-list.css' );

		
	}		
	
	function drawChildList( $atts )
	{

		// Get the current page and siblings
		global $post;
		$pageID =  $post->ID;
		
		// Find out the style type
		
		$showPageChildrenStyle = get_post_meta( $post->ID, 'showPageChildrenStyle', true );
				

		$args = array(
			'post_type'			=> 'page',
			'posts_per_page'	=> -1,
			'post_parent'		=> $pageID,
			'order'				=> 'ASC',
			'orderby'			=> 'menu_order',
			'post__not_in'		=> array($pageID), 
		);


		$siblings = new WP_Query( $args );
		
		$str = $this->drawPageTiles($siblings, $showPageChildrenStyle);

		wp_reset_postdata();


		return $str;
	}	
	
	function drawSiblingList( $atts )
	{

		// Get the current page and siblings
		global $post;
		$pageID =  $post->ID;
		
		$parentID = wp_get_post_parent_id( $pageID );

		$args = array(
			'post_type'			=> 'page',
			'posts_per_page'	=> -1,
			'post_parent'		=> $parentID,
			'order'				=> 'ASC',
			'orderby'			=> 'menu_order',
			'post__not_in'		=> array($pageID), 
		);


		$siblings = new WP_Query( $args );
		
		$str = $this->drawPageTiles($siblings);

		wp_reset_postdata();


		return $str;
	}
	
	
	function drawPageTiles($pageArray, $styleType="")
	{
		
		
		$str='';

		if ( $pageArray->have_posts() ) : 
		$str.='<div class="page_list_container clearfix">';
		//$str.='<div class="row">';		
		$str.='<div class="pages_list '.$styleType.'">';
		while ( $pageArray->have_posts() ) : $pageArray->the_post();

		$childID = get_the_id();
		$permalink = get_the_permalink($childID);
		$pageName = get_the_title();
		$excerpt = get_the_excerpt();
		$excerpt = wp_trim_words( $excerpt, 25, '...' );
		
		$imageInfo = wp_get_attachment_image_src( get_post_thumbnail_id( $childID ), 'full' );
		$image_url = $imageInfo[0] ? esc_attr( $imageInfo[0] ) : IMPERIAL_NETWORK_PLUGIN_URL.'/assets/page-list-placeholder.png';
		 
		 
		switch ($styleType)
		{
			case "circles-tiled":			
			
				$str .='<a href="' .$permalink. '">';
				$str.='<div class="page-list-circle-item">';
				$str.='<div class="rounded-image" style="width:150px; height:150px">';
				$str.='<img src="'.$image_url.'">';
				$str.='</div>';
				$str.='<div class="title">' .$pageName. '</div>';
				$str.='</div>';
				$str .='</a>';
			
			
			break;
			
			case "box-list":			
			
				
				$str .=     '<div>';
				$str .=         '<a href="' .$permalink. '">';
				$str .=			'<div class="listWrap">';
				$str .=             '<div class="image"><img src="' .$image_url. '"></div>';
				$str .=             '<div class="pageInfo">';
				$str .=                 '<div class="title">' .$pageName. '</div>';
				$str .=                 $excerpt.'<br/><span class="readMore">Read More...</span>';
				$str .=             '</div>';
				$str .=         '</div>';
				$str .=         '</a>';
				$str .=     '</div>';
				
			
			
			break;			
			
			
			default:
			
			
				$str .= '<div class="page_tile">';
				$str .=     '<div>';
				$str .=         '<a href="' .$permalink. '">';
				$str .=             '<div class="image" style="background-image:url(' .$image_url. ');"></div>';
				$str .=             '<div class="overlay">';
				$str .=                 '<div class="title">' .$pageName. '</div>';
				$str .=             '</div>';
				$str .=         '</a>';
				$str .=     '</div>';
				$str .= '</div>';
			
			break;
		}
		 
	
		

		endwhile;
		$str.='</div></div>';
		
		endif;

		
		
		
		
		return $str;
	}
	



	
}





?>