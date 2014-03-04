<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder;

use Okatea\Tao\Html\Stepper as BaseStepper;

class Stepper extends BaseStepper
{
	public function __construct($sBaseUrl, $sCurrentStep)
	{
		$aStepsList = array(
			array(
				'step' 		=> 'start',
				'title' 	=> __('m_builder_step_start')
			),
			array(
				'step' 		=> 'version',
				'title' 	=> __('m_builder_step_version')
			),
			array(
				'step' 		=> 'end',
				'title' 	=> __('m_builder_step_end')
			)
		);

		parent::__construct($aStepsList, $sCurrentStep);
	}
}
