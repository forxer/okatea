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

	public $aStepsList;

	public function __construct($okt, $sCurrentStep)
	{
		$this->aStepsList = [
			[
				'step' => 'start',
				'title' => __('i_step_start'),
				'position' => 0
			],
			[
				'step' => 'checks',
				'title' => __('i_step_checks'),
				'position' => 100
			],
			[
				'step' => 'db_conf',
				'title' => __('i_step_db_conf'),
				'position' => 200
			],
			[
				'step' => 'database',
				'title' => __('i_step_db'),
				'position' => 300
			],
			[
				'step' => 'supa',
				'title' => __('i_step_supa'),
				'position' => 400
			],
			[
				'step' => 'end',
				'title' => __('i_step_end'),
				'position' => 1000
			]
		];

		# -- CORE TRIGGER : installBeforeBuildInstallStepper
		$okt['triggers']->callTrigger('installBeforeBuildInstallStepper', $this);

		parent::__construct((array) $this->aStepsList, $sCurrentStep);
	}
}
