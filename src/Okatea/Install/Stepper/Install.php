<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Stepper;

use Okatea\Tao\Html\Stepper as BaseStepper;

class Install extends BaseStepper
{
	public function __construct($okt, $sCurrentStep)
	{
		$aStep = new \ArrayObject(array(
			array(
				'step' 		=> 'start',
				'title' 	=> __('i_step_start')
			),
			array(
				'step' 		=> 'checks',
				'title' 	=> __('i_step_checks')
			),
			array(
				'step' 		=> 'db_conf',
				'title' 	=> __('i_step_db_conf')
			),
			array(
				'step' 		=> 'database',
				'title' 	=> __('i_step_db')
			),
			array(
				'step' 		=> 'supa',
				'title' 	=> __('i_step_supa')
			),
/*			array(
				'step' 		=> 'config',
				'title' 	=> __('i_step_config')
			),
			array(
				'step' 		=> 'theme',
				'title' 	=> __('i_step_theme')
			),
			array(
				'step' 		=> 'colors',
				'title' 	=> __('i_step_colors')
			),
			array(
				'step' 		=> 'modules',
				'title' 	=> __('i_step_modules')
			),
			array(
				'step' 		=> 'pages',
				'title' 	=> __('i_step_pages')
			),
*/			array(
				'step' 		=> 'end',
				'title' 	=> __('i_step_end')
			)
		));

		# -- CORE TRIGGER : installBeforeBuildInstallStepper
		$okt->triggers->callTrigger('installBeforeBuildInstallStepper', $aStep);

		parent::__construct((array)$aStep, $sCurrentStep);
	}
}
