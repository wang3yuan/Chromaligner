<?php
 
class ProgressBar {

    function ProgressBar($message='', $hide=false, $sleepOnFinish=0, $barLength=200, $precision=20,
    					 $backgroundColor='#cccccc', $foregroundColor='blue', $domID='progressbar',
    					 $stepElement='<div style="width:%spx;height:20px;float:left;"></div>'
    					 ){

    	//increase time limit
		if(!ini_get('safe_mode')){
			set_time_limit(0);
		}

    	$this->hide = (bool) $hide;
    	$this->sleepOnFinish = (int) $sleepOnFinish;
    	$this->domID = strip_tags($domID);
    	$this->message = $message;
    	$this->stepElement = $stepElement;
    	$this->barLength = (int) $barLength;
    	$this->precision = (int) $precision;
    	$this->backgroundColor = strip_tags($backgroundColor);
		$this->foregroundColor = strip_tags($foregroundColor);
    	if($this->barLength < $this->precision){
    		$this->barLength = $this->precision;
    	}

    	$this->StepCount = 0;
    	$this->CallCount = 0;
    }

	
	//  Print the empty progress bar
	function initialize($numElements)
	{
		$numElements = (int) $numElements ;
    	if($numElements == 0){
    		$numElements = 1;
    	}
		//calculate the number of calls for one step
    	$this->CallsPerStep = ceil(($numElements/$this->precision));

		//calculate the total number of steps
		if($numElements >= $this->CallsPerStep){
			$this->numSteps = round($numElements/$this->CallsPerStep);
		}else{
			$this->numSteps = round($numElements);
		}

    	//calculate the length of one step
    	$stepLength = floor($this->barLength/$this->numSteps);

    	//the rest is the first step
    	$this->rest = $this->barLength-($stepLength*$this->numSteps);
    	if($this->rest > 0){
			$this->firstStep = sprintf($this->stepElement,$this->rest);
    	}

		//build the basic step-element
		$this->oneStep = sprintf($this->stepElement,$stepLength);

		//build bar background
		$backgroundLength = $this->rest+($stepLength*$this->numSteps);
		$this->backgroundBar = sprintf($this->stepElement,$backgroundLength);

		//stop buffering
    	ob_end_flush();
    	//start buffering
    	ob_start();

		echo '<div id="'.$this->domID.'">'.
			 $this->message.'<br/>'.
			 '<div style="position:absolute;color:'.$this->backgroundColor.';background-color:'.$this->backgroundColor.'">'.$this->backgroundBar.'</div>' .
			 '<div style="position:absolute;color:'.$this->foregroundColor.';background-color:'.$this->foregroundColor.'">';

		ob_flush();
		flush();
	}


	// Count steps and increase bar length
	function increase()
	{
		$this->CallCount++;

		if(!$this->started){

			echo $this->firstStep;
			ob_flush();
			flush();
		}

		if($this->StepCount < $this->numSteps
		&&(!$this->started || $this->CallCount == $this->CallsPerStep)){

			echo $this->oneStep;
			ob_flush();
			flush();

			$this->StepCount++;
			$this->CallCount=0;
		}
		$this->started = true;

		if(!$this->finished && $this->StepCount == $this->numSteps){


			echo '</div></div><br/>';
			ob_flush();
			flush();


			if($this->sleepOnFinish > 0){
				sleep($this->sleepOnFinish);
			}


			if($this->hide){
				echo '<script type="text/javascript">document.getElementById("'.$this->domID.'").style.display = "none";</script>';
				ob_flush();
				flush();
			}
			$this->finished = true;
		}
	}
}
?>