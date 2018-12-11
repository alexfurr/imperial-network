<?php

class imperialSiteCategories
{
	
	
	public static function getCategoriesByYear($academicYear, $deptID, $yos="")
	{
		
		global $wpdb;
		global $imperialNetworkDB;
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategories'];

		
		$yosClause = '';
		
		if($yos || $yos=="0")
		{
			$yosClause = ' AND yos = '.$yos.' ';
		}
		
		$SQL='Select * FROM '.$table_name.' WHERE academic_year = '.$academicYear.' and deptID = "'.$deptID.'"';
		
		
		$SQL.=$yosClause;
		
		$SQL.=' ORDER by yos ASC, cat_name ASC';
		
		
		$mySiteCats = $wpdb->get_results( $SQL, ARRAY_A );
		
		$siteCatArray = array();
		// Create array with YOS as the key

		foreach ($mySiteCats as $catInfo)
		{
			$catName = $catInfo['cat_name'];
			$yos = $catInfo['yos'];
			$catID = $catInfo['ID'];

			
			$siteCatArray[$yos][$catID] = $catName;


		}		
			
		return $siteCatArray;
		
		
	}


	public static function getAllCategories()
	{
		
		global $wpdb;
		global $imperialNetworkDB;
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategories'];

		

		$SQL='Select * FROM '.$table_name;
		
	
		$mySiteCats = $wpdb->get_results( $SQL, ARRAY_A );
		
		$siteCatArray = array();

		foreach ($mySiteCats as $catInfo)
		{
			$catName = $catInfo['cat_name'];
			$yos = $catInfo['yos'];
			$catID = $catInfo['ID'];

			
			$siteCatArray[$catID] = $catName;


		}		
			
		return $siteCatArray;
		
		
	}	
	
	
	public static function getSiteCategories($siteID)
	{
		
		global $wpdb;
		global $imperialNetworkDB;
		$catAllocations_table = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategoryAllocations'];
		$categories_table = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategories'];

		$SQL='SELECT '.$catAllocations_table.'.catID, '.$categories_table.'.cat_name FROM ';
		$SQL.=$categories_table.' INNER JOIN '.$catAllocations_table.' ON '.$categories_table.'.ID = '.$catAllocations_table.'.catID ';
		$SQL.='WHERE siteID = '.$siteID;
		


		$mySiteCats = $wpdb->get_results( $SQL, ARRAY_A );
		$myCatArray = array();
		foreach($mySiteCats as $catInfo)
		{
			$myCatArray[$catInfo['catID']] = $catInfo['cat_name'];
		}
		
		
			
		return $myCatArray;
		
		
	}		
	
	
	
	static function addCat($deptID, $academicYear, $catName, $yos)
	{
		
		global  $wpdb;
		global $imperialNetworkDB;

		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategories'];	

		$wpdb->query( $wpdb->prepare(		
		"INSERT INTO ".$table_name." (deptID, academic_year, cat_name, yos) VALUES ( %s, %d, %s, %d )",
		array(
			$deptID,
			$academicYear,
			$catName,
			$yos
			)
		));		
	}	
	
	
	
	static function updateCat($catID, $catName, $yos)
	{
		
		global  $wpdb;
		global $imperialNetworkDB;

		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategories'];	
		
		
		// Do the update				
		$wpdb->query( $wpdb->prepare(
			"UPDATE   ".$table_name." SET 
			cat_name=%s,
			yos=%d						
			WHERE ID = %d",
			$catName,
			$yos,
			$catID
		));  			

	}


	public static function updateSiteCategories	($siteID)
	{
		
		
		// Firstly delete the old categories for this blogID
		global  $wpdb;
		global $imperialNetworkDB;
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategoryAllocations'];		
		
		$wpdb->query(
              "DELETE  FROM ".$table_name." WHERE siteID = ".$siteID
		);		
		
		
		$siteCats = $_POST['siteCats'];
		if(!empty($siteCats)) 
		{
			foreach($_POST['siteCats'] as $catID)
			{
				$wpdb->query( $wpdb->prepare(		
				"INSERT INTO ".$table_name." (siteID, catID) VALUES ( %d, %d )",
				array(
					$siteID,
					$catID
					)
				));					
				
			}
		}		
		
	}
	
	

	
	static function getSitesInCat($catID)
	{
		global  $wpdb;
		global $imperialNetworkDB;
		$table_name = $imperialNetworkDB::imperialTableNames()['dbTable_siteCategoryAllocations'];	

		$SQL='SELECT * FROM '.$table_name.' WHERE catID = '.$catID;

		$mySites = $wpdb->get_results( $SQL, ARRAY_A );
		
		
		return $mySites;

		
	}
	
	
	
	static function drawCatSites($atts)
	{
		
		$atts = shortcode_atts( 
			array(
				'id'		=> '',
				), 
			$atts
		);
		
		$catID = (int) $atts['id'];
		
		$mySites = imperialSiteCategories::getSitesInCat($catID);
		
		$html = '';
		
		
		$html.='<div class="imperial-flex-container">';
		
		foreach ($mySites as $siteInfo)
		{
			$siteID = $siteInfo['siteID'];
			
			$blog_details = get_blog_details($siteID);
			$siteName = $blog_details->blogname;
			$siteURL = $blog_details->siteurl;
			
			$html.='<div class="contentBox" style="width:300px">';
			$html.= '<a href="'.$siteURL.'">'.$siteName.'</a><br/>';
			$html.='</div>';
			
		}
		$html.='</div>';
		
		return $html;
		
	}
	
		
	

	


}



?>