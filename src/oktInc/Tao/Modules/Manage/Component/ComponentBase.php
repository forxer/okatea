<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Modules\Manage\Component;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

abstract class ComponentBase
{
	protected $fs;

	public function __construct($okt, $module)
	{
		$this->okt = $okt;
		$this->module = $module;
		$this->checklist = $module->checklist;
	}

	protected function getFs()
	{
		if (null === $this->fs) {
			$this->fs = new Filesystem();
		}

		return $this->fs;
	}

	protected function getFinder()
	{
		return Finder::create();
	}

	protected function yamlParse($input, $exceptionOnInvalidType = false, $objectSupport = false)
	{
		return Yaml::parse($input, $exceptionOnInvalidType, $objectSupport);
	}

	protected function yamlDump($array, $inline = 2, $indent = 4, $exceptionOnInvalidType = false, $objectSupport = false)
	{
		return Yaml::dump($array, $inline, $indent, $exceptionOnInvalidType, $objectSupport);
	}
}
