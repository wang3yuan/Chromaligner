<?php
	session_start();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" src="bar.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Correlation Optimized Warping </title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<style type="text/css">
</style>

<body>

<?php

if(chdir("./".$_SESSION['DIR'])){
	$root_head="<root>\n";
	$root_end="</root>";
	$sta_tag_head="<status>";
	$sta_tag_end="</status>\n";
	$msg_tag_head="<message>";
	$msg_tag_end="</message>\n";
	/*running COW*/
	$script=file("COW.sh");
	$bar_length=count($script)+1;
	$working_progress=0;
	$increase=100/$bar_length;
//	$status="Preparing for running Chromaligner.";
	for($i=0 ; $i< $bar_length-1;$i++){
		$line = explode(" ", $script[$i]);     
		$working_progress=$working_progress+$increase;
		$status="Now we are aligning ".$line[2]." with ".$line[1].". Please wait.";
		$progress=fopen("progress.xml","w");
		fwrite($progress,$root_head
			.$msg_tag_head.round($working_progress).$msg_tag_end
			.$sta_tag_head.$status.$sta_tag_end
			.$root_end);
		fclose($progress);
		$status=system($script[$i]);
	}
	/*Generate the download links of retention time to intensity files after alignment*/
	/*if the process is broken, don't give user the URL.*/
	exec("zip -rj aligned_retentiontime_file.zip ./aligned_retentiontime_file/*");
	if(!file_exists("./aligned_retentiontime_file.zip")){
		$status=$status." Some unexpected error occurs. The retention time intensity files after alignment are broken.";
		fwrite($progress,$root_head);
		fwrite($progress,$msg_tag_head."-1".$msg_tag_end);
		fwrite($progress,$sta_tag_head.$status.$sta_tag_end);
		fwrite($progress,$root_end);
		fclose($progress);
		exit;
	}
/*	$working_progress=$working_progress+$increase-1;
	$progress=fopen("progress.xml","w");
		fwrite($progress,$root_head);
	fwrite($progress,$msg_tag_head.$working_progress.$msg_tag_end);
	fwrite($progress,$sta_tag_head."Generatin the chromatograms after alignment.".$sta_tag_end);
	fwrite($progress,$root_end);
	fclose($progress);*/
	exec("chmod 777 ./aligned_retentiontime_file/*");
	exec("cat tmp.r ../chromatogram.r > chromatogram.r");
	exec("chmod 777 aligned_retentiontime_file.zip");
	exec("mkdir chromatograms");
	exec("/usr/bin/R --no-save < chromatogram.r");
	exec("zip -rj chromatograms.zip ./chromatograms/*");
	exec("zip -r fulldata.zip ./chromatograms/* ./aligned_retentiontime_file/*");
	exec("chmod 777 chromatograms.zip");
	exec("chmod 777 *");
	/*if the process is broken, don't give user the URL.*/
	if(!file_exists("./chromatograms.zip")){
		$status="Some unexpected error occurs. The chromatograms after alignment are broken.";
		fwrite($progress,$root_head);
		fwrite($progress,$msg_tag_head."-1".$msg_tag_end);
		fwrite($progress,$sta_tag_head.$status.$sta_tag_end);
		fwrite($progress,$root_end);
		fclose($progress);
		exit;
	}
	if(!file_exists("./fulldata.zip")){
		$status="Some unexpected error occurs. All data after alignment are broken.";
		fwrite($progress,$root_head);
		fwrite($progress,$msg_tag_head."-1".$msg_tag_end);
		fwrite($progress,$sta_tag_head.$status.$sta_tag_end);
		fwrite($progress,$root_end);
		fclose($progress);
		exit;
	}

	/*Complete*/
	$working_progress=100;
	$progress=fopen("progress.xml","w");
		fwrite($progress,$root_head);
	fwrite($progress,$msg_tag_head.$working_progress.$msg_tag_end);
	fwrite($progress,$sta_tag_head."Generating the download links.".$sta_tag_end);
		fwrite($progress,$root_end);
	fclose($progress);
}

?>

</body>
</html>

