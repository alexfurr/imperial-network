jQuery(document).ready(function($) {
	
	
	
	
	$(".contentTreeMenu ul > li > a").after('<span class="toggleIcon minus"><i class="far far fa-minus-square"></i></span>');
	$(".contentTreeMenu ul > ol > li > a").after('<span class="toggleIcon minus"><i class="far fa-minus-square"></i></span>');
	
	
	
	$('.contentTreeMenu .toggleIcon').click(function()
		{
			// Toggle the content
			$(this).closest('li').next().toggle("quick");	
			
			
			// Get the class and apply revelent content based on that
			var myClass = $(this).attr("class");
			if(myClass.indexOf("minus") != -1)
			{
				$(this).html('<i class="far far fa-plus-square"></i>');	
				$(this).removeClass('minus');	
				$(this).addClass('plus');	
			}
			else{
				$(this).html('<i class="far far fa-minus-square"></i>');
				$(this).removeClass('plus');	
				$(this).addClass('minus');	
				
			}
			
			
		}
	);  
	
	
	
	
	
});