<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Themes;

use Okatea\Install\Controller as BaseController;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;

class Controller extends BaseController
{
	public function page()
	{
		$this->okt->startThemes();

		$aDefaultThemes = [
		];

		# Retrieving the list of themes in the file system (all themes)
		$this->aThemesList = $this->okt['themes']->getManager()->getAll();

		# Load themes main locales files
		foreach ($this->aThemesList as $aThemeInfos)
		{
			$this->okt['l10n']->loadFile($aThemeInfos['root'] . '/Locales/%s/main');

			$this->aThemesList[$aThemeInfos['id']]['name_l10n'] = __($aThemeInfos['name']);
			$this->aThemesList[$aThemeInfos['id']]['desc_l10n'] = __($aThemeInfos['desc']);
		}

		ThemesCollection::sort($this->aThemesList);

		$aValues = [];

		if ($this->okt['request']->request->has('sended'))
		{
			$aThemes = $this->okt['request']->request->get('p_themes', [], true);

			array_unshift($aThemes, 'DefaultTheme');

			foreach ($aThemes as $sThemeId)
			{
				if (!array_key_exists($sThemeId, $this->aThemesList)) {
					continue;
				}

				$this->okt['themes']->getInstaller($sThemeId)->doInstall();

				$this->okt['themes']->getManager()->enableExtension($sThemeId);
			}

			return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
		}

		unset($this->aThemesList['DefaultTheme']);

		return $this->render('Themes/Template', [
			'title' => __('i_themes_title'),
			'aDefaultThemes' => $aDefaultThemes,
			'aThemesList' => $this->aThemesList,
			'aValues' => $aValues
		]);
	}
}
