<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Admin\Controller;

use Okatea\Admin\Controller;

class Config extends Controller
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
			if ($this->okt->error->isEmpty())
			{
				$aNewConf = array(
					'repository_url' => $this->request->request->get('repository_url'),
					'modules' 	=> array(
						'repository_url' 	=> $this->request->request->get('modules_repository_url'),
						'repository' 		=> $this->request->request->get('modules_repository', array()),
						'package' 			=> $this->request->request->get('modules_package', array())
					),
					'themes' 	=> array(
						'repository_url' 	=> $this->request->request->get('themes_repository_url'),
						'repository' 		=> $this->request->request->get('themes_repository', array()),
						'package' 			=> $this->request->request->get('themes_package', array())
					)
				);

				try
				{
					$this->okt->module('Builder')->config->write($aNewConf);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('Builder_config'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		return $this->render('Builder/Admin/Templates/Config', array(
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
