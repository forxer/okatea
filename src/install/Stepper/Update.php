<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Stepper;

use Tao\Html\Stepper as BaseStepper;

class Update extends BaseStepper
{
	public function __construct($sCurrentStep)
	{
		parent::__construct(array(
			array(
				'step' 		=> 'start',
				'title' 	=> __('i_step_start')
			),
			array(
				'step' 		=> 'merge_config',
				'title' 	=> __('i_step_merge_config')
			),
			array(
				'step' 		=> 'checks',
				'title' 	=> __('i_step_checks')
			),
			array(
				'step' 		=> 'db',
				'title' 	=> __('i_step_db')
			),
			array(
				'step' 		=> 'end',
				'title' 	=> __('i_step_end')
			)
		), $sCurrentStep);
	}
}