<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Helper\SlotsHelper;

/**
 * Le système de templating étendu de sfTemplateEngine.
 *
 */
class Templating extends PhpEngine
{
	protected $aAssignedVars = array(); /**< La pile qui contient les variables assignées pour le moteur de templates. */

	public function __construct($aTplDirectories)
	{
		$loader = new FilesystemLoader($aTplDirectories);

		parent::__construct(new TemplateNameParser(), $loader);

		$this->set(new SlotsHelper());
	}

	/**
	 * Assignation de variables de templates.
	 *
	 * @param array $aVars
	 * @return void
	 */
	public function assign($aVars=array())
	{
		$this->aAssignedVars = array_merge($this->aAssignedVars, $aVars);
	}

	/**
	 * Retourne les variables de template actuellement assignées.
	 *
	 * @return array Les variables de template actuellement assignées.
	 */
	public function getAssignedVars()
	{
		return $this->aAssignedVars;
	}

	/**
	 * Rendu d'un template en utilisant les éventuelles variables pré-assignées.
	 *
	 * @see sfTemplateEngine::render()
	 */
	public function render($sTemplateFile, array $aVars=array())
	{
		if (!empty($aVars)) {
			$this->assign($aVars);
		}

		return parent::render($sTemplateFile, $this->aAssignedVars);
	}

} # class
