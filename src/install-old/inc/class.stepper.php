<?php

class oktStepper
{
	protected $aSteps = array();
	protected $iNumSteps = null;
	protected $sCurrentStep = null;
	protected $iCurrentStepPosition = 0;

	protected $defaultStepName = 'start';

	public function __construct($aSteps)
	{
		$this->sCurrentStep = !empty($_REQUEST['step']) ? $_REQUEST['step'] : $this->defaultStepName;

		$this->iNumSteps = count($aSteps);

		foreach ($aSteps as $i=>$aStep)
		{
			$this->aSteps[$i] = array(
				'step' 		=> $aStep['step'],
				'title' 	=> $aStep['title'],

				'past' 		=> false,
				'current' 	=> false,
				'last' 		=> false
			);

			if ($aStep['step'] == $this->sCurrentStep)
			{
				$this->aSteps[$i]['current'] = true;
				$this->iCurrentStepPosition = $i;
			}
		}

		foreach ($this->aSteps as $i=>$aStep)
		{
			if ($i < $this->iCurrentStepPosition) {
				$this->aSteps[$i]['past'] = true;
			}

			if ($i == $this->iNumSteps-1) {
				$this->aSteps[$i]['last'] = true;
			}
		}
	}

	public function display()
	{
		$str =
		'<div class="ui-widget-content ui-corner-all" id="ariane">'.
		'	<ul class="step10">';

		foreach ($this->aSteps as $i=>$step)
		{
			$str .= '<li';

			if ($step['current']) {
				$str .= ' class="active"';
			}
			elseif ($step['past']) {
				$str .= ' class="past"';
			}

			if ($step['last']) {
				$str .= ' id="lastStep"';
			}
			$str .= '><span><a href="#" title="">'.($i+1).'</a></span><a href="#" title="">'.$step['title'].'</a></li>';
		}

		$str .=
		'	</ul>'.
		'	<div class="clearer"></div>'.
		'</div>';

		return $str;
	}

	public function getPrevStep()
	{
		return isset($this->aSteps[($this->iCurrentStepPosition-1)]['step']) ? $this->aSteps[($this->iCurrentStepPosition-1)]['step'] : null;
	}

	public function getCurrentStep()
	{
		return isset($this->aSteps[$this->iCurrentStepPosition]['step']) ? $this->aSteps[$this->iCurrentStepPosition]['step'] : null;
	}

	public function getNextStep()
	{
		return isset($this->aSteps[($this->iCurrentStepPosition+1)]['step']) ? $this->aSteps[($this->iCurrentStepPosition+1)]['step'] : null;
	}

}
