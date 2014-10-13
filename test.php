<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=big5" />
<title>無標題文件</title>
</head>
<?
  require_once 'ProgressBar.class.php';
  $bar = new ProgressBar();
  $elements = 10000000; //total number of elements to process
  $bar->initialize($elements); //print the empty bar
  $j = 0;
  for($i=0;$i<$elements;$i++){
   	//do something here...
  		$bar->increase(); //calls the bar with every processed element
  }
?>
<body>
</body>
</html>
