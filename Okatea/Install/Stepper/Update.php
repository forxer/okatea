<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Stepper;

use Okatea\Tao\Html\Stepper as BaseStepper;

class Update extends BaseStepper
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
				'step' => 'merge_config',
				'title' => __('i_step_merge_config'),
				'position' => 100
			],
			[
				'step' => 'checks',
				'title' => __('i_step_checks'),
				'position' => 200
			],
			[
				'step' => 'database',
				'title' => __('i_step_db'),
				'position' => 300
			],
			[
				'step' => 'end',
				'title' => __('i_step_end'),
				'position' => 1000
			]
		];

		# -- CORE TRIGGER : installBeforeBuildUpdateStepper
		$okt['triggers']->callTrigger('installBeforeBuildUpdateStepper', $this->aStepsList);

		parent::__construct((array) $this->aStepsList, $sCurrentStep);
	}
}
