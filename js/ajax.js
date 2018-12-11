

// Default for Ajax function for all Ajax stuff




function imperialClickEvent(dataObj)
{
	console.log(dataObj);
	var myAction = 	dataObj.action;
	var updateDivID = 	dataObj.updateDiv;
	var args = 	dataObj.args;

	
	//console.log("myAction = "+myAction);
	//console.log("updateDivID = "+updateDivID);
	//console.log("args = "+args);
	
	
	jQuery.ajax({
		type: 'POST',
		url: imperialAjax_params.ajaxurl,
		data: {			
			"action"	: myAction,
			"args"		: args,
			"security"	: imperialAjax_params.ajax_nonce
		},
		
		
		success: function(data)
		{	
			
			console.log("Success");
			console.log("updateDivID = "+updateDivID);
			document.getElementById(updateDivID).innerHTML = data;
			
			// Check the args for clearDivIDs and clear them
			var clearDivID = args.clearDivID;
			if(clearDivID)
			{
				document.getElementById(clearDivID).innerHTML = "";
			}
		}
			
	});
}
	
	
	
	
	
