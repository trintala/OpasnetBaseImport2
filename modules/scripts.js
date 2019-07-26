mw.OpasnetBaseImport = {

check_special: function(elem)
{
	if (elem.checked == false)
	{
		$('#special').css("opacity",'1');
		$('#special input').each(function(a){this.disabled = false;});
	} else {
		$('#special').css("opacity",'0.5');
		$('#special input').each(function(a){this.disabled = true;});	
	}
}

}