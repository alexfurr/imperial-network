(function($) {
$(document).ready(function () {
/* Start of use $ for jquery */
	
	
	
	
	// Toggle the Profile coming down (top right)
	$("#myProfile" ).click(function()
	{
	  
		//$("#profile-menu-content").animate({height:'toggle'},100);
		$("#profile-menu-content").slideToggle("fast");
	});	
		

	// Toggle the Search coming down (top right)
	$("#searchIconWrap" ).click(function()
	{
	  
		//$("#profile-menu-content").animate({height:'toggle'},100);
		$("#bannerSearchWrap").slideToggle("fast");
		
		// Focus on the search box
		 $('#search-input').focus();	
	});	
	
	// Toggle the Search Options
	$('#searchOptionsWrap').bind('click', function(event) {
		var divID = $(event.target).attr('id');	
		
		
		if(divID.includes("search_") )
		{
			var searchType = divID.split('_')[1];
			
			// Remove active class of others
			$('#search_content').removeClass("searchActive"); 
			$('#search_sites').removeClass("searchActive"); 
			$('#search_people').removeClass("searchActive"); 
			
			$('#'+divID).addClass("searchActive"); 
			$('#searchType').val(searchType);
			
			console.log("searchType="+searchType);
			
			$('#search-input').focus();	
			
			
			if(searchType=="sites" || searchType=="people")
			{
				
				var formAction = $('#homeSearchPageURL').val();
				console.log("Set Form to "+homeSearchPageURL);
				$("#banner-search-form").attr("action", formAction);
			}
			
			if(searchType=="content")
			{
				
				var formAction = $('#searchPageURL').val(); 
				console.log("Set Form to "+searchPageURL);
				$("#banner-search-form").attr("action", formAction);
			}		
			
			
		}
		
	 });	
	 




		
		
	// This adds a space to shift the page content down to accomdate fixed header bar
	$( "body" ).prepend( "<div id='adminBarSpacer'></div>" );	
	
	
	
	
	
	/* Show the search results for a user in any imperialUserSearchInput */
	$('#imperialUserSearchInput').on('input', function() {
    
		
		var userSearchStr = $("#imperialUserSearchInput").val();
		
		var strLen = userSearchStr.length;
		
		// Get the Data Args from this input
		var customArgs = document.getElementById('imperialUserSearchInput');
		 
		var customArgsList = customArgs.dataset.args // This is data-args on the input
		var customArgsObject = JSON.parse(customArgsList);

		// Parse the custom args into an array
		//var customArgsObject = JSON.parse(customArgsList);
		customArgsObject.userSearchStr = userSearchStr;
		
		
		updateDivID = customArgsObject.resultsDivID;
		
		console.log("updateDivID="+updateDivID);
		
		// Now Convert object back to JSON
		//customArgsObject = JSON.stringify(customArgsObject);

				
		//console.log("deptID  = "+customArgsObject.deptID);
		//console.log("updateDiv  = "+customArgsObject.updateDiv);
		//console.log("myAction  = "+customArgsObject.myAction);		
		console.log("userSearchStr  = "+customArgsObject.userSearchStr);		
		
		
		
		if(strLen>=3)
		{
			var dataObj = {action:"showUserSearchList", updateDiv:updateDivID, args:customArgsObject};
			
			imperialClickEvent(dataObj);

		}
		
		
	});
	
	
	
	
	
	
/* End of use $ for jQuery */
;});
})( jQuery );


// Make Font Awesome Pseudo styles work
window.FontAwesomeConfig = {searchPseudoElements: true }