function attendevent()
{
	
	var post_id = jQuery('input#hid1').val();
	var author_name = jQuery('input#hid2').val();
	jQuery.ajax({
		url : my_ajax_script.ajaxurl,
		type : 'post',
			data : {
			action : 'post_attend_method',
			'post_id' : post_id,
			'author_name' : author_name,
			 },
		success : function( data ) 
		 {
			 alert(data);
			 location.reload();
		}
	 });

	return false; 
 }