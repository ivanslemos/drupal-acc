document.getElementById('testConfigButton').onclick = function() {
	var baseurl = document.getElementById('base_Url');
	var url = baseurl.getAttribute("data");
	var finalUrl = url+'/testConfig';
	var myWindow = window.open(finalUrl, "TEST OAUTH LOGIN", "scrollbars=1 width=800, height=600");
}

document.getElementById('showMetaButton').onclick = function() {
	jQuery('#backup_import_form').show();
	jQuery('#clientdata').hide();
	jQuery('#tabhead').hide();
}

document.getElementById('hideMetaButton').onclick = function() {
	jQuery('#backup_import_form').hide();
	jQuery('#clientdata').show();
	jQuery('#tabhead').show();
}