<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Admin\Controller;

use Okatea\Admin\Controller;

class Builder extends Controller
{
	protected $aModules;

	protected $aThemes;

	public function page()
	{
		if (!$this->okt->checkPerm('okatea_builder')) {
			return $this->serve401();
		}

		# save config
		if ($this->request->request->has('config_sent'))
		{

		}

		return $this->render('Builder/Admin/Templates/Builder', array(
			'aModules' 		=> $this->getModules(),
			'aThemes' 		=> $this->getThemes()
		));
	}

	protected function getModules()
	{
		if (null === $this->aModules)
		{
			$this->aModules = $this->okt->modules->getManager()->getAll();

			$this->setExtensionsL10n($this->aModules);
		}

		return $this->aModules;
	}

	protected function getThemes()
	{
		if (null === $this->aThemes)
		{
			$this->aThemes = $this->okt->themes->getManager()->getAll();

			$this->setExtensionsL10n($this->aThemes);
		}

		return $this->aThemes;
	}

	protected function setExtensionsL10n(&$aExtensions)
	{
		foreach ($aExtensions as $sExtensionId => $aExtensionInfos)
		{
			$this->okt->l10n->loadFile($aExtensionInfos['root'].'/locales/'.$this->okt->user->language.'/main');

			$aExtensions[$sExtensionId]['name_l10n'] = __($aExtensionInfos['name']);
		}
	}
}
