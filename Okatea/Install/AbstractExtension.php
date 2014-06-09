<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install;

use ArrayObject;

abstract class AbstractExtension
{

	protected $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;
	}

	abstract public function load();

	protected function addStep($stepper, array $aStep)
	{
		$stepper->aStepsList[] = $aStep;
	}

	protected function insertStepAfter($stepper, $sAfterStep, array $aStep)
	{
		$aNewSteps = [];

		$iCurrentPosition = 0;
		foreach ($stepper->aStepsList as $aStepInfo)
		{
			$aNewSteps[$iCurrentPosition++] = $aStepInfo;

			if ($aStepInfo['step'] === $sAfterStep)
			{
				$aNewSteps[$iCurrentPosition++] = $aStep;
			}
		}

		$stepper->aStepsList = new ArrayObject($aNewSteps);
	}
}
