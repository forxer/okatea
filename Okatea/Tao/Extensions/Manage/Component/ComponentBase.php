<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Manage\Component;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

abstract class ComponentBase
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 * 
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 * 
	 * @var object
	 */
	protected $error;

	protected $extension;

	protected $checklist;

	protected $fs;

	public function __construct($okt, $extension)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;
		
		$this->extension = $extension;
		$this->checklist = $extension->checklist;
	}

	protected function getFs()
	{
		if (null === $this->fs)
		{
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
