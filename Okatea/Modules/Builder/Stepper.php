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
		$i = 0;

		$aStepsList = [
			[
				'step' => 'start',
				'title' => __('m_builder_step_start'),
				'position' => $i++
			],
			[
				'step' => 'version',
				'title' => __('m_builder_step_version'),
				'position' => $i++
			],
			[
				'step' => 'copy',
				'title' => __('m_builder_step_copy'),
				'position' => $i++
			],
			[
				'step' => 'cleanup',
				'title' => __('m_builder_step_cleanup'),
				'position' => $i++
			],
			[
				'step' => 'changelog',
				'title' => __('m_builder_step_changelog'),
				'position' => $i++
			],
			[
				'step' => 'config',
				'title' => __('m_builder_step_config'),
				'position' => $i++
			],
			[
				'step' => 'modules',
				'title' => __('m_builder_step_modules'),
				'position' => $i++
			],
			[
				'step' => 'themes',
				'title' => __('m_builder_step_themes'),
				'position' => $i++
			],
			[
				'step' => 'digests',
				'title' => __('m_builder_step_digests'),
				'position' => $i++
			],
			[
				'step' => 'packages',
				'title' => __('m_builder_step_packages'),
				'position' => $i++
			],
			[
				'step' => 'end',
				'title' => __('m_builder_step_end'),
				'position' => $i++
			]
		];

		parent::__construct($aStepsList, $sCurrentStep);
	}
}
