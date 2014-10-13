<?php
session_start();
	#session_register("DIR");
//if(!session_is_registered("DIR")){
//}
#make a unique working fold
$nowtime=gettimeofday();
$dir="dtmp".$nowtime["sec"].$nowtime["usec"];
$_SESSION['DIR']=$dir;
mkdir($dir,0777);

$_POST['no_samples']=str_replace(" ","",$_POST['no_samples'] );
$_POST['no_spikes']=str_replace(" ","",$_POST['no_spikes'] );
$_POST['no_datapoints']=str_replace(" ","",$_POST['no_datapoints'] );
$_POST['no_secs']=str_replace(" ","",$_POST['no_secs'] );
$_POST['start_time']=str_replace(" ","",$_POST['start_time'] );
//$_POST['end_time']=str_replace(" ","",$_POST['end_time'] );


?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" src="bar.js"></script>
<!--<script type='text/javascript' 
        src='http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js'></script>-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Alignment with user-specified peak </title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
        <div id="right_content">
        	<table border="0" width="580" cellpadding="5" cellspacing="1">
            	<tr><th class="right_content_title">Input information</th></tr>
		<tr >
			<td class="right_content_body">
			
<?php
exec("chmod 777 ".$dir);
if(chdir("./".$dir)){
	mkdir("aligned_retentiontime_file",0777);
	exec("chmod 775 aligned_retentiontime_file");
}else{
	echo "please go back to the previous page and refresh it!"."<br>";
	exit;
}
#check the uploading files
#echo "file name :".$_FILES['Samples']['name']."<BR>";
#echo "location :".$_FILES['Samples']['tmp_name']."1"."<br>";
#echo "file size :".$_FILES['Samples']['size']."<br>";  
#echo "upload type :".$_FILES['Samples']['error']."<br>";  
#echo "filetype :".$_FILES['Samples']['type']."<br>";
	$Copy_file_failure=1;   
if  ( copy($_FILES['Samples']['tmp_name'], "./".$_FILES['Samples']['name'])) {
	ob_start();
	$Copy_file_failure=0; 

	echo "<h4>Uploaded successfuly.</h4>";
	ob_end_flush();
	flush();
		#run autospike.r
	$s_time=-1;
	$e_time=-1;
	$nsegment=-1;
	$no_datapoint=-1;
	$no_secs=-1;
	$start_time=-1;
	$end_time=-1;
	$s_flag=-1;
	$e_flag=-1;
	
	if($_POST['start_flag']=="yes"){
		$start_time=$_POST['start_time'];
		$s_flag=1;
		}
	if($_POST['end_flag']=="yes"){
		$end_time=$_POST['end_time'];
		$e_flag=1;
		}
	
	
	
	$command = 
	"rm(list=ls())"."\n".
	"nsegment="		.$_POST['no_spikes']	."\n".
	"no_datapoint="	.$_POST['no_datapoints']."\n".
	"no_secs="		.$_POST['no_secs']		."\n".
	"start_time="	.$start_time			."\n".
	"end_time="		.$end_time				."\n".
	"s_flag="		.$s_flag				."\n".
	"e_flag="		.$e_flag				."\n";

$fp=fopen("temp.r","w");
fputs($fp,$command);
fclose($fp);
exec("cat temp.r ../autospike.r > autospike.r");
	#   unzip the uploading files
	exec("unzip -ouj ".$_FILES['Samples']['name']." -d ./",$a);
	exec("rm ".$_FILES['Samples']['name'],$a);
	#Check whether the target file exists or not
exec("/usr/bin/R --no-save < autospike.r");
exec("mkdir spike_plot");
exec("mv *.png spike_plot/");

	
	if(!file_exists($_POST['target'])){
		echo "Please check your target file name. The target file name you give in the previous page dosn't exist in your uploaded file"."<br>";
		exit;
	}



	#translate space" " to "\ " for COW.c
	$C_target=str_replace(" ","\ ",$_POST['target']);
#echo "<ba>Ctarget  ".$C_target."<br>";			
	# generate the script file and display index.txt
	$script = fopen("COW.sh","w");
	$file_list = file("index.txt");
	$number_of_sample = count($file_list);
	#Accept both space and tab
	$file_list[0]=str_replace(" ","\t",$file_list[0]);
	$line = explode("\t", $file_list[0]);     
	$number_of_para=count($line);
	#Check whether other files exist or not
	for($i=1 ;$i < $number_of_sample; $i++){
		#Accept both space and tab
		$file_list[$i]=str_replace(" ","\t",$file_list[$i]);
		$line = explode("\t", $file_list[$i]);     
		if(!file_exists($line[0])){
			echo "Please check your files' name."."You mention".$line[0]."in your index.txt but it doesn't exist in your uploaded file."."<br>";
			exit;
		}
		if(!file_exists("P".$line[0])){
			echo "Please check the following parameter file. P".$line[0]."doesn't exist in your uploaded file."."<br>";
			exit;
		}
	}
	#define how many rows should a parameter file contain

		$number_of_spikes=$_POST['no_spikes'];
	    $number_of_rows=$number_of_spikes;

	#define the longest data file to malloc
	$MAX_Range=0;
	#Check what kind of parameters the user choose
	#echo "Each of your parameter should contain "."<span class=\"style1\">".$number_of_rows."</span>". "rows.<br>";
	#show the corresponding parameter file setting image
	if($_POST['start_flag']!="yes" & $_POST['end_flag']!="yes")
	{
		$eof_flag=-1;
		$number_of_rows=$number_of_rows+1;
		$end_flag=-1;
		$number_of_rows=$number_of_rows+1;
	 echo "<h4>No starting or ending time specified.</h4>";	
#	 echo "<br><h4>The following figure is an example telling the contents in parameter files.</h4>";	 
#	 echo "<img border=1 src=\"Spec/PPopt0_nopeaks.jpg\" /><br></br>";	    
#	 echo "00";
	}
	if($_POST['start_flag']!="yes" & $_POST['end_flag']=="yes")
	{
		$eof_flag=-1;
		$number_of_rows=$number_of_rows+1;
		$end_flag=$_POST['end_time'];
		$MAX_Range=$end_flag;
	 echo "<h4>Specify the same ending time ".$_POST['end_time']."(mins).</h4>";	 
#	 echo "<br><h4>The following image is an example telling the contents in parameter files.</h4>";	 
#	 echo "<img border=1 src=\"Spec/PPopt2_nopeaks.jpg\" /><br></br>";	 
#	 echo "01";
	}
	if($_POST['start_flag']=="yes" & $_POST['end_flag']!="yes")
	{
		$eof_flag=$_POST['start_time'];
		$end_flag=-1;
		$number_of_rows=$number_of_rows+1;
	 echo "<h4>Specify the same starting time ".$_POST['start_time']."(mins).</h4>";	 
#	 echo "<br><h4>The following image is an example telling the contents in parameter files.</h4>";	 
#	 echo "<img border=1 src=\"Spec/PPopt1_nopeaks.jpg\" /><br></br>";	 
# 	 echo "10";
	}	
	if($_POST['start_flag']=="yes" & $_POST['end_flag']=="yes")
	{
		$eof_flag=$_POST['start_time'];
		$end_flag=$_POST['end_time'];
		$MAX_Range=$end_flag;
		echo "<h4>Specify the same starting time ".$_POST['start_time']."(mins) and the same ending time ".
			$_POST['end_time']."(mins).</h4>";	 
#	 echo "11";	 
	}	
	#find the longest end time 

	if($MAX_Range==0){
		for($i=1 ;$i < $number_of_sample; $i++){
			$line = explode("\t", $file_list[$i]);     
			$parameter_file = file("P".$line[0]); 
			$rows = count($parameter_file);
			if($number_of_rows!=$rows){
				echo "Please check the parameter file P".$line[0]." . It only contains ".$rows." rows.<br>";
				exit;
			}else{
				if($parameter_file[$number_of_rows-1]>$MAX_Range){
					$MAX_Range=$parameter_file[$number_of_rows-1];
				}
			}
		}
		$MAX_Range=str_replace("\r\n","",$MAX_Range);
		$MAX_Range=str_replace("\n","",$MAX_Range);
	}

	$MAX_Range=str_replace("\n","",$MAX_Range);

#	echo "<br>max range".$MAX_Range."<br>";
	if(($number_of_sample-1)!=$_POST['no_samples']){
		echo "Please check your \"index.txt\".In \"index.txt\", it contains ".$number_of_para." samples.<br>";
		echo "But the number of samples you entered in previous page is".$_POST['no_samples']."<br>";
		echo "Please also check your \"index.txt\"'s file format<br>";
		exit;
	}else{
#		echo "<table border=1>";
		$line = explode("\t", $file_list[0]);     
#		echo "<tr>";  
#		for($j=0; $j < $number_of_para; $j++)
#		{
#			echo "<td>";
#			echo $line[$j]; 
#			echo "</td>";
#		} 
#		echo "</tr>";
		$sample_list="";
		for($i=1 ;$i < $number_of_sample; $i++)
		{
			$line = explode("\t", $file_list[$i]);     
/**/			if(strcmp(str_replace(" ","\ ",$_POST['target']),str_replace(" ","\ ",$line[0]))!=0){			
				$current_sample=str_replace(" ","\ ",$line[0]);
				$sample_list=$sample_list."\"".$line[0]."\"";
				if($i!=($number_of_sample-1)){
					$sample_list=$sample_list.",";
				}
				 fwrite($script,"../COW ".$C_target." ".$current_sample.
					" P".$C_target." P".$current_sample." ".
					$MAX_Range." ".$number_of_spikes." ".$_POST['no_datapoints']." ".$_POST['no_secs']." ".
					$eof_flag." ".$end_flag."\n");
			}
/**/			if($i==($number_of_sample-1))	
/**/			{
/**/			 if(strcmp(str_replace(" ","\ ",$_POST['target']),str_replace(" ","\ ",$line[0]))==0)
/**/			 $sample_list=substr($sample_list,0,strlen($sample_list)-1);
# 		 	echo "sample list".$sample_list."<br>";
/**/			}			
#			echo "<tr>";  
#			for($j=0; $j < $number_of_para; $j++)
#			{
#				echo "<td>";
#				echo $line[$j]; 
#				echo "</td>";
#			} 
#			echo "</tr>";
		}
#		echo "</table>";
 		 	echo "<h4>Target file name :</h4>".$_POST['target'];
 		 	echo "<br></br><h4>Samples' file name :</h4>".str_replace(",",", ",str_replace("\"","",$sample_list))."<br>";
	
		fclose($script);
		$exe=1; 

	}
}

	/*generate R code*/
	$sample_list=str_replace(".txt",NULL,$sample_list);
	$_POST['target']=str_replace(".txt",NULL,$_POST['target']);
	$pre_process='
		rm(list=ls());
	max_range='.$MAX_Range.';
	name=c('.$sample_list.');
	end=".txt";
	number='.($number_of_sample-2).';
	count=rep(0,number);
	raw="./aligned_retentiontime_file/";
	image="./chromatograms/";
	freq='.$_POST['no_datapoints'].'/'.$_POST['no_secs'].'
	targetname=paste("'.$_POST['target'].'","-processed",end,sep="");';
	$Rfp=fopen("tmp.r","w");
	fputs($Rfp,$pre_process);
	fclose($Rfp);

	echo "<br>";
	/*prepare the progress bar*/
	$progress=fopen("progress.xml","w");
	fwrite($progress,"<root>\n<message>0</message>\n<status>Preparing for running Chromaligner.</status>\n</root>");
	fclose($progress);

        echo "<input type=\"button\" value=\"Start to run Chromaligner\" id=\"runbutton\" name=\"runbutton\" Onclick=\"submitTask('".$dir."')\"/>";
	echo "<input onclick=\"history.go(-1)\"  type=\"button\" value=\"Go Back to modify\" name=\"Go back\"><p>&nbsp</p>";

?>

<div id="task_id">

</div>
<div id="progress">

</div>

</body>
</html>
