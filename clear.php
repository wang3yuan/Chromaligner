<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=big5" />
<script type="text/javascript">
</script>
<title>Clear files </title>
</head>
<?php
exec("rm -r -f ".$_GET['dir']);
?>	
<% 
Response.Buffer = True 
Response.ExpiresAbsolute = Now() - 1 
Response.Expires = 0 
Response.CacheControl = "no-cache" 
%> 

<body>

<script   language=javascript>   
         window.opener=null;   
         window.open("","_self");   
         window.close();   
</script>   

</body>
</html>

