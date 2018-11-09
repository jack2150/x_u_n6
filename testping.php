<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Client-Server Test & Ping</title>
<script type="text/javascript" src="includes/js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="includes/js/jquery.xdajax.js"></script>
<script type="text/javascript">
document.domain = 'xun6.com';

sid = ['s001ag', 's002bg', 's100am', 's101bm', 's102am', 's103bm', 's104am', 's105bm', 's106am', 's107bm', 's108am', 's109bm', 
	's110am', 's111bm', 's112am', 's113bm', 's114am', 's115bm', 's200ap', 's201bp', 's202ap', 's203bp', 's204ap', 's205bp'];
	
website = '.xun6.com';

function test_server(url) {
//$('#test_result1').load('http://localhost/fileservers.test/testping.html');
// cross domain problem
//$('#test_result1').load('http://s001ag.xun6.com/testping.html');

$('#test_result1').xdajax({
    url:'http://s001ag.xun6.com/testping.html',
    type:"GET",
    success: function(o) {
        var root=$.loadXML(o.responseText);
		alert(o.responseText);
    },
});


}
</script>
</head>

<body>

<div id="test_result1">000</div>

<input id="testbutton" name="" type="button" value="Test" onclick="test_server(sid[0]);" />

</body>
</html>
