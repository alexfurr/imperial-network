<?php

class imperialNetworkDraw
{
	
	static function drawProfileDropdown()
	{	
	
		$homeURL = network_home_url();
		
		$html='';

		$username="";
		$userType=4;
		$fullName="";
		if(isset($_SESSION['username']) )
		{
			$username = $_SESSION['username'];				
			$userType=$_SESSION['userType'];
			$fullName = $_SESSION['fullname'];
			
			$html= '<div id="profile-menu-content">';			
			$html.='<div class="profile-dropdown-div">';
			$html.='<div class="profile_name">'.$fullName.'</div>';
			$html.='<div class="profile-logout"><a href="'.$homeURL.'?imperial-logout=true">Logout</a></div>';
			$html.='</div>';
			
			$subMenuArrayItems = imperialNetworkUtils::getProfileMenuItems($userType);
			$html.='<ul>';
			foreach($subMenuArrayItems as $submenuMeta)
			{
				$itemType = $submenuMeta[0];
				$itemName = $submenuMeta[1];
				
				$html.='<li><a href="'.$homeURL.'profile?view='.$itemType.'">'.$itemName.'</a></li>';
			} 
			$html.='</ul>';
			
			$html.='</div>';	
			
		}

		
		
	

		
		return $html;
	}
	
	
	
	
	static function userSearchForm($args)
	{

	
		// Add the div that will show the results to the JSON object
		if(!isset($args["resultsDivID"]) )
		{
			$args["resultsDivID"] = "userSearchResults";
		}
		
		$resultsDivID = $args["resultsDivID"];
		
		$jsonItem  = json_encode($args);
		$str = '';
		
		$str.='<input data-args=\''.$jsonItem.'\' type="text" id="imperialUserSearchInput" class="imperialUserSearchInput">';
		$str.='<div id="'.$resultsDivID.'"></div>';
		return $str;
		
	}	
	
	
	static function drawProgressBar($myProgress)
	{
		
		$html= '<div class="progressWrap">';
		$html.= '<div class="progress">';
		$html.= '<span class="progressText">'.$myProgress.'% complete</span>';	

		if($myProgress>=1)
		{			
			$html.= '<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:'.$myProgress.'%"></div>';
		}
		$html.= '</div></div>';		
		return $html;		
		
		
	}
	
	static function drawRadialProgress($args)
	{		
		$colour = "#307bbb";
		$size = "80";		
		$number = $args['number'];
		$text = $args['text'];
		if(isset($args['colour']) )
		{
			$colour = $args['colour'];
		}
		if(isset($args['size']) )
		{
			$size = $args['size'];
		}		
		
		$str='<div class="c100 p'.$number.'" style="font-size:'.$size.'px;">
                    <span>'.$text.'</span>
                    <div class="slice">
                        <div class="bar" style="border-color:'.$colour.';"></div>
                        <div class="fill" style="border-color:'.$colour.';"></div>
                    </div>
                </div>';
					
		return $str;
	
	}	
	



	static function drawUserSites($userID)
	{
		$acYearStr = '';
		$genericStr = '';
		$mySiteArray = imperialNetworkUtils::getUserSites($userID);
		
		$html='';
		$siteCount = count($mySiteArray);
		
		
		if($siteCount==0)
		{
			return '<span class="greyText">No courses found</span>';
		}

		
		// Create blank array
		$tempSiteArray = array();
		
		// Get all the categories as lookup
		$siteCatLookupArray = imperialSiteCategories::getAllCategories();


		foreach ($mySiteArray as $academicYear => $siteList)
		{

			// Generic sites - sort by name
			if($academicYear==0)
			{
				// Sort this list by name
				usort($siteList, imperialNetworkUtils::build_sorter('siteName'));
				$tempSiteArray[0] = $siteList;


			}	
			else
			{
				


				$thisYearTempArray = array();
				foreach ($siteList as $siteInfo)
				{
					// Get the Cat list and add to aray by cat
					$myCats = $siteInfo['siteCat'];					
					
					$catCount = count($myCats);
						
					if($catCount>=1)
					{
						foreach ($myCats as $catID => $catName)
						{									
							$thisYearTempArray[$catID][] = $siteInfo;
						}
					}
					else
					{
						$thisYearTempArray[0][] = $siteInfo;
					}
				}	

				$tempSiteArray[$academicYear] = $thisYearTempArray;
			}
		}
			
			
			
		// Sort the temp site DESC
		krsort($tempSiteArray);
		
		
			
		// Now go through the whole array		
		foreach ($tempSiteArray as $academicYear => $mySites)
		{			
		
			$html.=	'<div class="myCoursesAcYearBox">';		

			if($academicYear==0)
			{
				$html.= '<div class="myCoursesAcYearTitle" style="margin-bottom:20px">Generic Courses</div>';
				
				$html.='<div class="content myCoursesCatContainer">';
				

				foreach ($mySites as $siteInfo)				
				{
					$html.= imperialNetworkDraw::drawCourseItem($siteInfo, $userID);
				
				}
				$html.='</div>';				
				
				
			}
			else
			{
				
				$academicYear = imperialNetworkUtils::getNiceAcademicYear($academicYear);

				$html.= '<div class="myCoursesAcYearTitle">Academic Year '.$academicYear.'</div>';	
				$otherCoursesStr='';
				$html.='<div class="myCoursesCatWrap">';
				
				foreach ($mySites as $catID => $catSites)
				{
					
					if($catID==0)
					{
						$otherCoursesStr.= '<div class="catTitle">Other Courses</div>';
						$otherCoursesStr.='<div class="content myCoursesCatContainer">';
						

						foreach ($catSites as $siteInfo)
						{

							$otherCoursesStr.= imperialNetworkDraw::drawCourseItem($siteInfo, $userID);

						}
						$otherCoursesStr.='</div>';					}
					else
					{
						$catName = $siteCatLookupArray[$catID];
						$html.= '<div class="catTitle">'.$catName.'</div>';
						$html.='<div class="content myCoursesCatContainer">';
						

						foreach ($catSites as $siteInfo)
						{

									
							$html.= imperialNetworkDraw::drawCourseItem($siteInfo, $userID);

						}
						$html.='</div><hr/>';

					}

				}	

				$html.=$otherCoursesStr;
				$html.='</div>';

			}
			
			$html.='</div>'; // End of academic year box
			
			
		}
		
			

		
			
		return $html;
		
		
	}
	
	static function drawCourseItem($args, $userID)
	{
		$siteName = $args['siteName'];
		$siteURL = $args['siteURL'];
		$siteIcon = $args['siteIcon'];
		$blogID = $args['siteID'];
		
		// Swap to blog
		switch_to_blog($blogID);		
		
		// Get the Blog theme
		// If its a module then get the percent complete
		$theme = wp_get_theme();
				
		$themeDir = get_stylesheet_directory();
		$themeName = basename($themeDir);
		
		$progress="";
		if ($themeName=="imperial-course")
		{
			$progress = ek_user_stats_utils::getOverallProgress($userID);			
		}
		
		restore_current_blog(); 

		$html='<div class="myCoursesItem">';
		$html.='<div class="handbookImage">';
		$html.='<a href="'.$siteURL.'">';
	//	$html.='<span class="icl_handbook_logo_small"></span>';
		$html.='<img width="250" src="'.$siteIcon.'"></a>';
		$html.='</div>';
		$html.='<div class="handbookMeta">';
		$html.='<div class="handbookTitle"><a href="'.$siteURL.'">'.$siteName.'</a></div>';
		
		if($progress<>"")
		{
			
			$progressBar = imperialNetworkDraw::drawProgressBar($progress);
			$html.='<a href="'.$siteURL.'">';
			$html.=$progressBar;
			$html.='</a>';
		}
		
		$html.='</div>';
		$html.='</div>';	


		
		return $html;

	
	}
	
	static function drawUserSites2($userID)
	{
		$acYearStr = '';
		$genericStr = '';
		$mySiteArray = imperialNetworkUtils::getUserSites($userID);
		
		$html='';
		$siteCount = count($mySiteArray);
		
		
		if($siteCount==0)
		{
			return '<span class="greyText">No courses found</span>';
		}

		
		// Create blank array
		$tempSiteArray = array();
		
		// Get all the categories as lookup
		$siteCatLookupArray = imperialSiteCategories::getAllCategories();


		foreach ($mySiteArray as $academicYear => $siteList)
		{

			// Generic sites - sort by name
			if($academicYear==0)
			{
				// Sort this list by name
				usort($siteList, imperialNetworkUtils::build_sorter('siteName'));
				$tempSiteArray[0] = $siteList;


			}	
			else
			{
				


				$thisYearTempArray = array();
				foreach ($siteList as $siteInfo)
				{
					// Get the Cat list and add to aray by cat
					$myCats = $siteInfo['siteCat'];					
					
					$catCount = count($myCats);
						
					if($catCount>=1)
					{
						foreach ($myCats as $catID => $catName)
						{									
							$thisYearTempArray[$catID][] = $siteInfo;
						}
					}
					else
					{
						$thisYearTempArray[0][] = $siteInfo;
					}
				}	

				$tempSiteArray[$academicYear] = $thisYearTempArray;
			}
		}
			
			
			
		// Sort the temp site DESC
		krsort($tempSiteArray);
		
		
			
		// Now go through the whole array		
		foreach ($tempSiteArray as $academicYear => $mySites)
		{			
		
			$html.=	'<div class="myCoursesAcYearBox">';		

			if($academicYear==0)
			{
				$html.= '<div class="myCoursesAcYearTitle" style="margin-bottom:20px">Generic Courses</div>';
				
				$html.='<div class="content myCoursesCatContainer">';
				

				foreach ($mySites as $siteInfo)
				{

					$siteName = $siteInfo['siteName'];
					$siteURL = $siteInfo['siteURL'];
					$siteIcon = $siteInfo['siteIcon'];
					
					$html.='<div class="myCoursesItem">';
					$html.='<a href="'.$siteURL.'" class="flex-container__link">';
					$html.='<span class="icl_handbook_logo_small"></span>';
					$html.='<img class="flex-container__img" width="250" src="'.$siteIcon.'">';
					$html.='<span class="flex-container__title">'.$siteName.'</span></a></div>';


				}
				$html.='</div>';				
				
				
			}
			else
			{
				
				$academicYear = imperialNetworkUtils::getNiceAcademicYear($academicYear);

				$html.= '<div class="myCoursesAcYearTitle">Academic Year '.$academicYear.'</div>';	
				$otherCoursesStr='';
				$html.='<div class="myCoursesCatWrap">';
				
				foreach ($mySites as $catID => $catSites)
				{
					
					if($catID==0)
					{
						$otherCoursesStr.= '<div class="catTitle">Other Courses</div>';
						$otherCoursesStr.='<div class="content myCoursesCatContainer">';
						

						foreach ($catSites as $siteInfo)
						{

							$siteName = $siteInfo['siteName'];
							$siteURL = $siteInfo['siteURL'];
							$siteIcon = $siteInfo['siteIcon'];							
							
							$otherCoursesStr.='<div class="myCoursesItem">';
							$otherCoursesStr.='<a href="'.$siteURL.'" class="flex-container__link">';
							$otherCoursesStr.='<span class="icl_handbook_logo"></span>';							
							$otherCoursesStr.='<img class="flex-container__img" width="250" src="'.$siteIcon.'">';
							$otherCoursesStr.='<span class="flex-container__title">'.$siteName.'</span></a></div>';


						}
						$otherCoursesStr.='</div>';					}
					else
					{
						$catName = $siteCatLookupArray[$catID];
						$html.= '<div class="catTitle">'.$catName.'</div>';
						$html.='<div class="content myCoursesCatContainer">';
						

						foreach ($catSites as $siteInfo)
						{

							$siteName = $siteInfo['siteName'];
							$siteURL = $siteInfo['siteURL'];
							$siteIcon = $siteInfo['siteIcon'];
									
							$html.='<div class="myCoursesItem">';
							$html.='<a href="'.$siteURL.'" class="flex-container__link">';
							$html.='<span class="icl_handbook_logo"></span>';							
							$html.='<img class="flex-container__img" width="250" src="'.$siteIcon.'">';
							$html.='<span class="flex-container__title">'.$siteName.'</span></a></div>';


						}
						$html.='</div><hr/>';

					}

				}	

				$html.=$otherCoursesStr;
				$html.='</div>';

			}
			
			$html.='</div>'; // End of academic year box
			
			
		}
		
			

		
			
		return $html;
		
		
	}	
	
	
	static function drawDeptModules($deptID, $academicYear, $returnAsList=false)
	{
		
		$programme_code='MBBS';
		
		if($_SESSION['programme']<>"")
		{
			$programme = $_SESSION['programme'];
			
			// Get the programme (MBBS or BMB)
			$programmeLookupArray = imperialQueries::getCourseCodeArray();
			
			$programmeCode = $programmeLookupArray[$programme]['programme'];
		}
		
	
		
		
		
		
		
		$args = array(
			"deptID" => $deptID,
			"academicYear"	=> $academicYear,
			"programme"	=> $programme_code,
		);
		$deptSiteArray = imperialQueries::getCourses($args);
		
		 
		$defaultIcon = get_stylesheet_directory_uri().'/assets/images/cover-template.jpg';	

		$deptCourseArray = array();
		foreach ($deptSiteArray as $moduleInfo)
		{
			$yos = $moduleInfo['yos'];
			if($yos==""){$yos=0;}
			$blogID = $moduleInfo['blogID'];
			
			switch_to_blog($blogID);
	
			// Get the site privacy
			$siteVis = get_option("blog_public");	
			$showSite = true;
			if($siteVis=="-3" || $siteVis=="-2")
			{
				$showSite=false;
			}	


			if($showSite==true)
			{


				$site_title = get_bloginfo( 'name' );				
				$site_url = get_site_url();
				$siteIcon = get_option("siteIcon", $defaultIcon);	
				
				$tempModuleArray = array(
					'site_title'	=> $site_title,
					'site_url'	=> $site_url,
					'site_icon'	=> $siteIcon,
				);
				// Get the blog info	
				$deptCourseArray[$yos][] = $tempModuleArray;
			}
			
			restore_current_blog();	
			
			
		}
		
		


		$deptModuleStr='';
		$moduleCount=0;
		
		
		$deptModuleStr.='<section class="sites-container"><section class="sites-accordion">';
		
		
		if($returnAsList==true)
		{
			$deptModuleStr.='<ul>';

		}		
		
		
		$boxNumber=1;
		foreach ($deptCourseArray as $yos => $yearModules)
		{			
		
		
			// Sort the Arrach alphabeticlly
			asort($yearModules);
		
			$yosTitle = 'Year '.$yos;
			if($yos==0){$yosTitle='General Courses';}
			
			if($returnAsList==true)
			{
				$deptModuleStr.='<li>'.$yosTitle.'</li>';

			}
			else
			{
				$deptModuleStr.='<div class="sites-accordion-item">';
				$deptModuleStr.= '<span class="yosTitle" id="yosTitle_'.$boxNumber.'">'.$yosTitle.' ';
				$deptModuleStr.= '<i class="fas fa-plus-square" id="plusBox'.$boxNumber.'"';
				if($boxNumber==1)
				{
					$deptModuleStr.=' style="display:none"';
				}				
				$deptModuleStr.='></i>';
				$deptModuleStr.= '<i class="fas fa-minus-square" id="minusBox'.$boxNumber.'"';
				if($boxNumber<>1)
				{
					$deptModuleStr.=' style="display:none"';
				}

				
				$deptModuleStr.='></i>';		
				
				
				$deptModuleStr.='</span>';
				$deptModuleStr.='<div class="content flex-container" id="yosSitesContent_'.$boxNumber.'"';
				
				
				if($boxNumber<>1)
				{
					$deptModuleStr.=' style="display:none;" ';
				}
				$deptModuleStr.='>';
			}			
			
			if($returnAsList==true)
			{
				$deptModuleStr.='<ul>';
			}
			

			foreach($yearModules as $moduleInfo)
			{
				$site_title  = $moduleInfo['site_title'];
				$site_url  = $moduleInfo['site_url'];
				$site_icon= $moduleInfo['site_icon'];
				if($returnAsList==true)
				{
					$deptModuleStr.='<li>'.$deptModuleStr.= '<a href="'.$site_url.'">'.$site_title.'</a></li>';
				}
				else
				{
					$deptModuleStr.='<div class="flex-container__item"><a href="'.$site_url.'" class="flex-container__link">';
					$deptModuleStr.='<span class="icl_handbook_logo"></span>';
					$deptModuleStr.='<img class="flex-container__img" width="250" src="'.$site_icon.'">';
                    $deptModuleStr.='<span class="flex-container__title">'.$site_title.'</span></a></div>';					
				}
				
				
				
				$moduleCount++;
				
				
				$deptModuleStr.='<script>
				jQuery( "#yosTitle_'.$boxNumber.'" ).click(function() {
				jQuery( "#yosSitesContent_'.$boxNumber.'" ).toggle( "fast", function() {
					


						console.log("SHOW PLUS FOR '.$boxNumber.'");
						jQuery("#minusBox'.$boxNumber.'").toggle();
						jQuery("#plusBox'.$boxNumber.'").toggle();
						



				});
				});			
				</script>';
				$boxNumber++;
			}
			
			


			
			if($returnAsList==true)
			{
				$deptModuleStr.='</ul>';
			}
			else
			{
				$deptModuleStr.='</div></div>';

			}
			
		}
		
		if($returnAsList==true)
		{
			$deptModuleStr.='</ul>';

		}
		
		$deptModuleStr.'</div></div>';
	
		if($moduleCount==0)
		{
			$deptModuleStr = '<br/>No sites found';
		}
	
		return $deptModuleStr;				
	}
	
	public static function drawDeptModulesByCategory($deptID, $academicYear)
	{
		
		// Get all Categories
		$mySiteCats = imperialSiteCategories::getCategoriesByYear($academicYear, $deptID);

		if(count($mySiteCats)==0) 
		{
			echo 'No Categories found';
		}

		foreach ($mySiteCats as $yos => $yearCats)
		{

			$yearTitle = 'Year '.$yos;
			if($yos==0){$yearTitle = 'No Year Group';}
			
			echo '<div class="contentBox">';
			echo '<h3>'.$yearTitle.'</h3>';
			
			foreach ($yearCats as $catID => $catName)
			{
				echo '<h4>'.$catName.'</h4>';
				
				// Get the Blog IDs of those that are in this category
				$mySites = imperialSiteCategories::getSitesInCat($catID);
				
				$siteCount = count($mySites);
				
				if($siteCount==0)
				{
					echo 'No Sites found';
				}
				else
				{
					foreach ($mySites as $KEY => $siteCatMeta)
					{
						$siteID = $siteCatMeta['siteID'];
						
						switch_to_blog($siteID);
						$site_title = get_bloginfo( 'name' );						
						$site_url = get_site_url();
						
						echo '<a href="'.$site_url.'">'.$site_title.'</a><br/>';
						restore_current_blog();	

						
					}
				}
				echo '<hr/>';



			}
			echo '</div>';
				

		}
		
		
		
		echo '<hr/>';
		
		
		
		

	}


	
}



?>