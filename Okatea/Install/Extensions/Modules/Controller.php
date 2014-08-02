<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Modules;

use Okatea\Install\Controller as BaseController;
use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;

class Controller extends BaseController
{

	public function page()
	{
		$this->okt->startModules();

		$aDefaultModules = [
			'Contact',
			'Pages',
		];

		# Retrieving the list of modules in the file system (all modules)
		$this->aModulesList = $this->okt->modules->getManager()->getAll();

		# Load modules main locales files
		foreach ($this->aModulesList as $aModuleInfos)
		{
			$this->okt->l10n->loadFile($aModuleInfos['root'] . '/Locales/%s/main');

			$this->aModulesList[$aModuleInfos['id']]['name_l10n'] = __($aModuleInfos['name']);
			$this->aModulesList[$aModuleInfos['id']]['desc_l10n'] = __($aModuleInfos['desc']);
		}

		ModulesCollection::sort($this->aModulesList);

		$aValues = [];

		if ($this->okt['request']->request->has('sended'))
		{
			$aModules = $this->okt['request']->request->get('p_modules', array(), true);

			foreach ($aModules as $sModuleId)
			{
				if (!array_key_exists($sModuleId, $this->aModulesList)) {
					continue;
				}

				$this->okt->modules->getInstaller($sModuleId)->doInstall();

				$this->okt->modules->getManager()->enableExtension($sModuleId);
			}

			return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
		}

		return $this->render('Modules/Template', [
			'title' => __('i_modules_title'),
			'aDefaultModules' => $aDefaultModules,
			'aModulesList' => $this->aModulesList,
			'aValues' => $aValues
		]);
	}
}
