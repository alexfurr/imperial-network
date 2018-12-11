<?php


$imperialAjax = new imperialAjax();
class imperialAjax
{
	
	//~~~~~
	public function __construct ()
	{
		$this->addWPActions();
	}	
	
	
	function addWPActions()
	{	
	
		// Generic User Search with single click for action
		add_action( 'wp_ajax_showUserSearchList', array($this, 'showUserSearchList' ));		
	
	
		// Add textual feedback for clicks
		add_action( 'wp_ajax_removeDeptAdmin', array($this, 'removeDeptAdmin' ));
		
		
		// Add User To Blog
		add_action( 'wp_ajax_addUserToBlog', array($this, 'addUserToBlog' ));		
		
		// Add Dept Admin
		add_action( 'wp_ajax_addDeptAdmin', array($this, 'addDeptAdmin' ));			
	
	}
	
	function addDeptAdmin()
	{
		// Check the AJAX nonce				
		check_ajax_referer( 'imperial_ajax_nonce', 'security' );
		
		$args = $_POST['args'];
		$deptID = $args['deptID'];
		$username = $args['username'];
		
		
		imperialNetworkActions::addDeptAdmin($deptID, $username);
		
		// Check that  person has a wordpress account and if not create one
		imperialNetworkActions::createWP_user($username);		
		
		$html = medlearnDraw::imperialFeedback("User Added as Dept Admin");
		$html.= medlearnDraw::drawDeptAdminList($deptID);		
		echo $html;
		
		die();
	}
	
	
	function removeDeptAdmin()
	{
		// Check the AJAX nonce				
		check_ajax_referer( 'imperial_ajax_nonce', 'security' );
		
		$args = $_POST['args'];
		$deptID = $args['deptID'];
		$username = $args['username'];

		echo imperialNetworkActions::removeDeptAdmin($deptID, $username);	
		$html = medlearnDraw::imperialFeedback("User Removed as Dept Admin");

		$html.= medlearnDraw::drawDeptAdminList($deptID);		
		echo $html;
		
		die();

	}
	
	
	function showUserSearchList()
	{
		
		// Check the AJAX nonce				
		check_ajax_referer( 'imperial_ajax_nonce', 'security' );
		
		
		//$args = "{myAction:'addDeptAdmin',deptID:'WM',updateDiv:'deptAdminList',userSearchStr:'Abani}";
		//$args = stripslashes($_POST['args']);
		$args = $_POST['args'];		


		$searchStr = $args['userSearchStr'];
		$myAction = $args['myAction'];
		$thisDeptID = $args['deptID'];
		$updateDiv = $args['updateDiv'];
		$href = $args['href'];
		
		
		
		
		
		$userResults = imperialQueries::getUsers($searchStr);
		
		$userCount = count($userResults);
		
		if($userCount==0)
		{
			echo 'No Users Found';
			die();
		}
		
		// Get the list of dept for lookup
		$deptArray = imperialQueries::getFacultyLookupArray();
		
		$height = "400";
		
		$userCount = count($userResults);
		
		if($userCount<10)
		{
			$height = ($userCount * 55);
		}
		
		if($height<200)
		{
			$height = 200;
		}
		
		
		
		echo '<strong>'.$userCount.'</strong> user(s) found<br/>';
		
		echo '<div style="overflow-y: scroll; height:'.$height.'px; border:1px solid #ccc;">';
		echo '<table class="imperial-table-1 stripes">';
		
		foreach($userResults as $userInfo)
		{
			$firstName = $userInfo['first_name'];
			$lastName = $userInfo['last_name'];
			$email = $userInfo['email'];
			$username = $userInfo['username'];
			$userID = $userInfo['userID'];
			$deptID = $userInfo['deptID'];
			$userType = $userInfo['user_type'];
			$userType = imperialNetworkUtils::getUserTypeStr($userType);
			
			$deptName = $deptArray[$deptID];
			
			
			

			
			$jsonData = array(
				"action"	=> $myAction,
				"updateDiv" => $updateDiv,
				"args" 		=> array (
								"username" => $username,
								"deptID"	=> $thisDeptID,
								"clearDivID"	=> "userSearchResults",
								),
			);

			$jsonItem = json_encode($jsonData);		
			
			echo  '<tr>';
			echo '<td>';
			
			if($href==true)
			{
				

				
				$URL = $args['link'];
				$qrystrItems = $args['qrystr'];
				
				$myQrystr = '';
				foreach($qrystrItems as $qryItem)
				{
					$myQrystr=$qryItem.'='.$$qryItem.'&';
				}
				
				echo '<a href="'.$URL.'?'.$myQrystr.'">';
				
				
				
			}
			else
			{			
				echo '<a href="#" onclick = \'javascript:imperialClickEvent('.$jsonItem.');\'">';
			}
			
			echo $firstName.' '.$lastName.'</a></td>';
			
			echo '<td>'.$username.'</td>';
			echo '<td>'.$userID.'</td>';
			echo '<td>'.$email.'</td>';
			echo '<td>'.$userType.'</td>';		
			echo '<td>'.$deptName.'</td>';		
			
			echo  '</tr>';
			
			
		}
		
		echo '</table>';
		echo '</div>';
		
		die();
	}
	
	
	function addUserToBlog()
	{
		// Check the AJAX nonce				
		check_ajax_referer( 'imperial_ajax_nonce', 'security' );
		
		$args = $_POST['args'];
		$username = $args['username'];
		
		
		// Check to see if the user has a wordpress account
		$userInfo = get_user_by( 'login', $username );
		
		if($userInfo)
		{
			$userID = $userInfo->ID;			
		}
		else
		{
			$userID = imperialNetworkActions::createWP_user($username);			
		}
		
		// Now make them a subscriber
		$userObject = new WP_User( $userID );
		// Add role
		$userObject->set_role( 'subscriber' );
		
		echo '<div class="notice notice-success is-dismissible"><p>User added as a student</p></div>';

		die();

	}
	
}


?>