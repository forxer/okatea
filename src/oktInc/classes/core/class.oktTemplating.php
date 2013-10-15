<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktTemplating
 * @ingroup okt_classes_core
 * @brief Le système de templating étendu de sfTemplateEngine
 *
 */
class oktTemplating extends sfTemplateEngine
{
	protected $aAssignedVars = array(); /**< La pile qui contient les variables assignées pour le moteur de templates. */

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
