<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=big5" />
<title>Correlation Optimized Warping </title>
</head>
<style type="text/css">
<!--
.style1 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
<? 
/*Response.Buffer = True 
Response.ExpiresAbsolute = Now() - 1 
Response.Expires = 0 
Response.CacheControl = "no-cache" 
*/?> 
<script type="text/javascript">
var over_flag = 0;
function caption()
{
	     over_flag = 1;
	          ListTitle.innerText = "";
	          document.all.info.style.visibility = "visible";
		       document.all.comment.style.visibility = "visible";
		       document.all.fishdata.style.visibility = "visible";
}

var word = "Data loading";
var dot = ".....";
var pos = 0;
var space = "     ";
function loading_text()
{
	     if(over_flag)
		          {
				            return;
					         }
	          ListTitle.innerText = word + dot.substring(0,pos)+space.substring(0,5-pos);
	          if( pos++ == 5 ){
			            pos = 0;
				              setTimeout("loading_text()", 2000);
				         }else{
						 	  setTimeout("loading_text()", 500);
							       }
}
</script>

<?php
#make a unique working fold
$nowtime=gettimeofday();
$dir="dtmp".$nowtime["sec"].$nowtime["usec"];
mkdir($dir,0777);
?>

<body onunload="window.open('clear.php?dir=<?php echo $dir; ?>','clear',config='height=300,width=300');">
  <h1>
    <script>loading_text(</script>
  </h1>
<hr />
<?php
exec("chmod 777 ".$dir);
if(chdir("./".$dir)){
	echo "<br>";
	mkdir("aligned_retentiontime_file",0777);
	exec("chmod 775 aligned_retentiontime_file");
}else{
	echo "please go back to the previous page and refresh it!"."<br>";
	exit;
}
#check the uploading files
#echo "tempfile :".$_FILES['Samples']['name']."<BR>";
#echo "filename:".$_FILES['Samples']['tmp_name']."1"."<br>";
#echo "filesize:".$_FILES['Samples']['size']."<br>";  
#    echo "filetype: $userfile_type<br>";   
if  ( copy($_FILES['Samples']['tmp_name'], "./".$_FILES['Samples']['name'])) {
	ob_start();

	echo "Uploaded successfuly.";
	ob_end_flush();
	flush();

	#   unzip the uploading files
	exec("unzip -ouj ".$_FILES['Samples']['name']." -d ./",$a);
	echo "<br>";
	exec("rm ".$_FILES['Samples']['name'],$a);
	#Check whether the target file exists or not
	if(!file_exists($_POST['target'])){
		echo "Please check your target file name. The target file name you give in the previous page dosn't exist in your uploaded file"."<br>";
		exit;
	}
	#translate space" " to "\ " for COW.c
	$C_target=str_replace(" ","\ ",$_POST['target']);

	# generate the script file and display input.txt
	$script = fopen("COW.sh","w");
	$file_list = file("input.txt"); 
	$number_of_sample = count($file_list);
	$line = explode("\t", $file_list[0]);     
	$number_of_para=count($line);
	#Check whether other files exist or not
	for($i=1 ;$i < $number_of_sample; $i++){
		$line = explode("\t", $file_list[$i]);     
		if(!file_exists($line[0])){
			echo "Please check your files' name."."You mention".$line[0]."in your input.txt but it doesn't exist in your uploaded file."."<br>";
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
	if($_POST['start_flag']!="yes"){
		$eof_flag=-1;
		echo "You don't give a fixed analysis start time"."<br>";
		$number_of_rows=$number_of_rows+1;
	}else{
		$eof_flag=$_POST['start_time'];
		echo "You give a fixed analysis start time :".$_POST['start_time']."<br>";
	}
	if($_POST['end_flag']!="yes"){
		$end_flag=-1;
		echo "You don't give a fixed analysis end time"."<br>";
		$number_of_rows=$number_of_rows+1;
	}else{
		$end_flag=$_POST['end_time'];
		$MAX_Range=$end_flag;
		echo "You give a fixed analysis end time :".$_POST['end_time']."<br>";
		 
		
		
	}
	echo "Each of your parameter should contain "."<p class="style1">".$number_of_rows."</p>""." rows.<br>";
	#find the longest end time 
	if($MAX_Range==0){
		for($i=1 ;$i < $number_of_sample; $i++){
			$line = explode("\t", $file_list[$i]);     
			$parameter_file = file("P".$line[0]); 
			$rows = count($parameter_file);
			if($number_of_rows!=$rows){
				echo "Please check the parameter file P".$line[0]." . It only contains.".$rows." rows.<br>";
				exit;
			}else{
				if($parameter_file[$number_of_rows-1]>$MAX_Range){
					$MAX_Range=$parameter_file[$number_of_rows-1];
				}
			}
		}
		$MAX_Range=str_replace("\r\n","",$MAX_Range);
	}

	if(($number_of_sample-1)!=$_POST['no_samples']){
		echo "Please check your \"input.txt\".In \"input.txt\", it contains ".$number_of_para." samples.<br>";
		echo "But the number of samples you entered in previous page is".$_POST['no_samples']."<br>";
		echo "Please also check your \"input.txt\"'s file format<br>";
	}else{
		echo "<table border=1>";
		$line = explode("\t", $file_list[0]);     
		echo "<tr>";  
		for($j=0; $j < $number_of_para; $j++)
		{
			echo "<td>";
			echo $line[$j]; 
			echo "</td>";
		} 
		echo "</tr>";
		$sample_list="";
		for($i=1 ;$i < $number_of_sample; $i++)
		{
			$line = explode("\t", $file_list[$i]);     
			if(strcmp($_POST['target'],$line[0])!=0){
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
			echo "<tr>";  
			for($j=0; $j < $number_of_para; $j++)
			{
				echo "<td>";
				echo $line[$j]; 
				echo "</td>";
			} 
			echo "</tr>";
		}
		echo "</table>";

		fclose($script);
	}

}else{
	echo "Copy file failure.<br>";
	exit;
}
?>	
<script>loading_text()</script>
<?php
	/*running COW*/
	system("sh ./COW.sh");
	/*generate R code*/
	$sample_list=str_replace(".txt",NULL,$sample_list);
	$_POST['target']=str_replace(".txt",NULL,$_POST['target']);
	$pre_process='
		rm(list=ls());
	max_range='.$MAX_Range*60*($_POST['no_datapoints']/$_POST['no_secs']).';
	name=c('.$sample_list.');
	end=".txt";
	number='.($number_of_sample-2).';
	count=rep(0,number);
	raw="./aligned_retentiontime_file/";
	image="./chromatograms/";
	targetname=paste(raw,"'.$_POST['target'].'","-processed",end,sep="");';
	$Rfp=fopen("tmp.r","w");
	fputs($Rfp,$pre_process);
	fclose($Rfp);
	exec("cat tmp.r ../chromatogram.r > chromatogram.r");
	exec("zip -rj aligned_retentiontime_file.zip ./aligned_retentiontime_file/*");
	exec("chmod 777 aligned_retentiontime_file.zip");
	echo"<br><a href=".$dir."/aligned_retentiontime_file.zip>donwload the retention time intensity file after alignment</a>"; 
	exec("mkdir chromatograms");
	exec("/usr/bin/R --no-save < chromatogram.r");
	exec("zip -rj imagedata.zip ./chromatograms/*");
	exec("zip -r alldata.zip ./chromatograms/* ./aligned_retentiontime_file/*");
	exec("chmod 777 imagedata.zip");
	exec("chmod 777 *");
	echo "<br><a href=".$dir."/imagedata.zip>donwload the chromatogram</a>";
	echo "<br><a href=".$dir."/alldata.zip>donwload full data</a>"; 

?>
</body>
</html>
