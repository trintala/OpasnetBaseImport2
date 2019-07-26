function submit_data()
{
//	var path = document.maintenance_form.path.value;
	//$('popup').update('<img src="'+path+'/images/ajax-loader.gif" alt="Loading..." />');
    show_popup("preview");
	//document.obj_form.actn.value = 'check_'+diag;
	$('obj_form').request({
		onSuccess: function(transport){
	      var response = transport.responseText;
	      $('preview').update(response);
	      //if (response != '0')
		  //	$$('td#'+diag+'_actions input').each(function(i){i.disabled = false});
	    },
	    onFailure: function(){ alert('Something went wrong...') }
	});
}

function show_popup(id)
{
	$('smokescreen').style.display = 'block';
	$(id).style.display = 'block';	
}

function hide_popup(id)
{
	$(id).style.display = 'none';
	$('smokescreen').style.display = 'none';		
}


function check_special(elem)
{
	if (elem.checked == false)
	{
		$('special').style.opacity = '1';
		$$('#special input').each(function(a){a.disabled = false;});
	} else {
		$('special').style.opacity = '0.5';
		$$('#special input').each(function(a){a.disabled = true;});	
	}
}