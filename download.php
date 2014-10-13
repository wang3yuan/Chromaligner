<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>download</title>
</head>
<link rel="stylesheet" type="text/css" href="style.css">
<body>
        <div id="right_content">
		<table border="0" width=580 cellpadding="5" cellspacing="1" >
		<tr><th class="right_content_title">Result page</th></tr>
		<tr >
			<td class="right_content_body">
				<h4>The retention time to intensity files after alignment</h4>
<?php
		echo "<a href=".$_SESSION['DIR']."/aligned_retentiontime_file.zip>Please click here to download data</a>";
?>
				<br></br>
				<br></br>
				
				<h4>The chromatograms (before and after alignment)</h4>
<?php
		echo	"<a href=".$_SESSION['DIR']."/chromatograms.zip>Please click here to download data</a>";
?>
				<br></br>
				<br></br>
				<h4 style="margin-bottom:0px; ">The full data</h4>
				<h4 style="margin-top:0px; ">(include both retention time to intensity file and chromatograms)</h4>
<?php
		echo	"<a href=".$_SESSION['DIR']."/fulldata.zip>Please click here to download data</a>";
?>
				<br></br>
				<br></br>
				</td>
		</tr>
	</div>
</body>
</html>
