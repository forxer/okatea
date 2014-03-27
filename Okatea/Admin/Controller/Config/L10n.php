<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Html\Escaper;

class L10n extends Controller
{
	public function index()
	{
		if (!$this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/%s/admin/l10n');

		if (($action = $this->enableLanguage()) !== false) {
			return $action;
		}

		if (($action = $this->disableLanguage()) !== false) {
			return $action;
		}

		if (($action = $this->deleteLanguage()) !== false) {
			return $action;
		}

		if (($action = $this->updateLanguagesOrderByAjax()) !== false) {
			return $action;
		}

		if (($action = $this->updateLanguagesOrderByPost()) !== false) {
			return $action;
		}

		if (($action = $this->updateConfiguration()) !== false) {
			return $action;
		}

		# Liste des langues
		$rsLanguages = $this->okt->languages->getLanguages();

		$aLanguages = array();
		while ($rsLanguages->fetch()) {
			$aLanguages[Escaper::html($rsLanguages->title)] = $rsLanguages->code;
		}

		# Liste des fuseaux horraires
		$aTimezones = \dt::getZones(true, true);

		return $this->render('Config/L10n/Index', array(
			'rsLanguages' => $rsLanguages,
			'aLanguages' => $aLanguages,
			'aTimezones' => $aTimezones
		));
	}

	public function edit()
	{
		if (!$this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/%s/admin/l10n');

		$iLanguageId = $this->request->attributes->getInt('language_id');

		$rsLanguage = $this->okt->languages->getLanguages(array('id' => $iLanguageId));

		$aUpdLanguageData = array(
			'id'        => $iLanguageId,
			'title' 	=> $rsLanguage->title,
			'code' 		=> $rsLanguage->code,
			'img' 		=> $rsLanguage->img,
			'active' 	=> $rsLanguage->active
		);

		if ($this->request->request->has('form_sent'))
		{
			$aUpdLanguageData = array(
				'id' 		=> $iLanguageId,
				'title' 	=> $this->request->request->get('edit_title'),
				'code' 		=> $this->request->request->get('edit_code'),
				'img' 		=> $this->request->request->get('edit_img'),
				'active' 	=> $this->request->request->getInt('edit_active')
			);

			if ($this->okt->languages->checkPostData($aUpdLanguageData))
			{
				$this->okt->languages->updLanguage($aUpdLanguageData);

				$this->page->flash->success(__('c_a_config_l10n_edited'));

				return $this->redirect($this->generateUrl('config_l10n_edit_language', array('language_id'=>$iLanguageId)));
			}
		}

		return $this->render('Config/L10n/Edit', array(
			'aUpdLanguageData' => $aUpdLanguageData,
			'aFlags' => $this->getIconsList()
		));
	}

	public function add()
	{
		if (!$this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/%s/admin/l10n');

		$aAddLanguageData = array(
			'language' 	=> '',
			'country' 	=> '',
			'title' 	=> '',
			'code' 		=> '',
			'img' 		=> '',
			'active' 	=> 1
		);

		if ($this->request->request->has('form_sent'))
		{
			$aAddLanguageData = array(
				'title' 	=> $this->request->request->get('add_title'),
				'code' 		=> $this->request->request->get('add_code'),
				'img' 		=> $this->request->request->get('add_img'),
				'active' 	=> $this->request->request->getInt('add_active', 0)
			);

			if ($this->okt->languages->checkPostData($aAddLanguageData))
			{
				$iLanguageId = $this->okt->languages->addLanguage($aAddLanguageData);

				$this->page->flash->success(__('c_a_config_l10n_added'));

				return $this->redirect($this->generateUrl('config_l10n_edit_language', array('language_id'=>$iLanguageId)));
			}
		}

		# fetch languages infos
		$sLanguagesListInfos = $this->okt->options->get('root_dir').'/vendor/forxer/languages-list/languages.php';

		$aLanguagesList = array(' ' => null);
		if (file_exists($sLanguagesListInfos))
		{
			$aLanguagesListInfos = require $sLanguagesListInfos;

			foreach ($aLanguagesListInfos as $aLanguageInfo) {
				$aLanguagesList[$aLanguageInfo['Native name']] = $aLanguageInfo['639-1'];
			}

			unset($aLanguagesListInfos);
		}

		# fetch country infos
		$sCountryListInfos = $this->okt->options->get('root_dir').'/vendor/umpirsky/country-list/country/cldr/'.$this->okt->user->language.'/country.php';

		$aCountryList = array(' ' => null);
		if (file_exists($sCountryListInfos))
		{
			$aCountryListInfos = require $sCountryListInfos;

			$aCountryList = array_merge($aCountryList, array_flip($aCountryListInfos));

			unset($aCountryListInfos);
		}

		return $this->render('Config/L10n/Add', array(
			'aAddLanguageData' => $aAddLanguageData,
			'aLanguagesList' => $aLanguagesList,
			'aCountryList' => $aCountryList,
			'aFlags' => $this->getIconsList()
		));
	}

	protected function getIconsList()
	{
		$aFlags = array();
		foreach (new \DirectoryIterator($this->okt->options->public_dir.'/img/flags/') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || !$oFileInfo->isFile() || $oFileInfo->getExtension() !== 'png') {
				continue;
			}

			$aFlags[str_replace('.png', '', $oFileInfo->getFilename())] = $oFileInfo->getFilename();
		}
		natsort($aFlags);

		return $aFlags;
	}

	protected function switchLanguageStatus()
	{
		if ($this->request->query->has('switch_status'))
		{
			$this->okt->languages->switchLangStatus($this->request->query->get('switch_status'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function enableLanguage()
	{
		if ($this->request->query->has('enable'))
		{
			$this->okt->languages->setLangStatus($this->request->query->get('enable'), 1);

			$this->page->flash->success(__('c_a_config_l10n_enabled'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function disableLanguage()
	{
		if ($this->request->query->has('disable'))
		{
			$this->okt->languages->setLangStatus($this->request->query->get('disable'), 0);

			$this->page->flash->success(__('c_a_config_l10n_disabled'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function deleteLanguage()
	{
		if ($this->request->query->has('delete'))
		{
			$this->okt->languages->delLanguage($this->request->query->get('delete'));

			$this->page->flash->success(__('c_a_config_l10n_deleted'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function addLanguage()
	{
		if ($this->request->request->has('add_languages'))
		{
			$this->aAddLanguageData = array(
				'title' 	=> $this->request->request->get('add_title'),
				'code' 		=> $this->request->request->get('add_code'),
				'img' 		=> $this->request->request->get('add_img'),
				'active' 	=> $this->request->request->getInt('add_active', 0)
			);

			if ($this->okt->languages->checkPostData($this->aAddLanguageData))
			{
				$this->okt->languages->addLanguage($this->aAddLanguageData);

				$this->page->flash->success(__('c_a_config_l10n_added'));

				return $this->redirect($this->generateUrl('config_l10n'));
			}
		}

		return false;
	}

	protected function updateLanguagesOrderByAjax()
	{
		if ($this->request->query->has('ajax_update_order'))
		{
			$aLanguagesOrder = $this->request->query->get('ord', array());

			if (!empty($aLanguagesOrder))
			{
				foreach ($aLanguagesOrder as $ord=>$id)
				{
					$ord = ((integer)$ord)+1;
					$this->okt->languages->updLanguageOrder($id, $ord);
				}

				$this->okt->languages->generateCacheList();
			}

			exit();
		}

		return false;
	}

	protected function updateLanguagesOrderByPost()
	{
		if ($this->request->request->has('order_languages'))
		{
			$aLanguagesOrder = $this->request->request->get('p_order', array());

			asort($aLanguagesOrder);

			$aLanguagesOrder = array_keys($aLanguagesOrder);

			if (!empty($aLanguagesOrder))
			{
				foreach ($aLanguagesOrder as $ord=>$id)
				{
					$ord = ((integer)$ord)+1;
					$this->okt->languages->updLanguageOrder($id, $ord);
				}

				$this->okt->languages->generateCacheList();

				$this->page->flash->success(__('c_a_config_l10n_neworder'));

				return $this->redirect($this->generateUrl('config_l10n'));
			}
		}

		return false;
	}

	protected function updateConfiguration()
	{
		if ($this->request->request->has('config_sent'))
		{
			if ($this->okt->error->isEmpty())
			{
				$this->okt->config->write(array(
					'language' 				=> $this->request->request->get('p_language'),
					'timezone' 				=> $this->request->request->get('p_timezone'),
					'admin_lang_switcher' 	=> $this->request->request->has('p_admin_lang_switcher')
				));

				$this->okt->languages->generateCacheList();

				$this->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_l10n'));
			}
		}

		return false;
	}
}
