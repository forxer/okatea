<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use DirectoryIterator;
use Okatea\Admin\Controller;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\L10n\Date;

class L10n extends Controller
{
	public function index()
	{
		if (! $this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/l10n');

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
		$aLanguages = $this->okt->languages->getLanguages();

		$aLanguagesForSelect = array();
		foreach ($aLanguages as $language) {
			$aLanguagesForSelect[Escaper::html($language['title'])] = $language['code'];
		}

		# Liste des fuseaux horraires
		$aTimezones = Date::getTimezonesList(true, true);

		return $this->render('Config/L10n/Index', array(
			'aLanguages' => $aLanguages,
			'aLanguagesForSelect' => $aLanguagesForSelect,
			'aTimezones' => $aTimezones
		));
	}

	public function edit()
	{
		if (! $this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/l10n');

		$iLanguageId = $this->okt['request']->attributes->getInt('language_id');

		$aLanguage = $this->okt->languages->getLanguage($iLanguageId);

		if (!$iLanguageId || !$aLanguage) {
			return $this->serve404();
		}

		$aUpdLanguageData = array(
			'id' 		=> $iLanguageId,
			'title' 	=> $aLanguage['title'],
			'code' 		=> $aLanguage['code'],
			'img' 		=> $aLanguage['img'],
			'active' 	=> $aLanguage['active']
		);

		if ($this->okt['request']->request->has('form_sent'))
		{
			$aUpdLanguageData = array(
				'id' 		=> $iLanguageId,
				'title' 	=> $this->okt['request']->request->get('edit_title'),
				'code' 		=> $this->okt['request']->request->get('edit_code'),
				'img' 		=> $this->okt['request']->request->get('edit_img'),
				'active' 	=> $this->okt['request']->request->getInt('edit_active')
			);

			if ($this->okt->languages->checkPostData($aUpdLanguageData))
			{
				$this->okt->languages->updLanguage($aUpdLanguageData);

				$this->okt['flash']->success(__('c_a_config_l10n_edited'));

				return $this->redirect($this->generateUrl('config_l10n_edit_language', array(
					'language_id' => $iLanguageId
				)));
			}
		}

		return $this->render('Config/L10n/Edit', array(
			'aUpdLanguageData' => $aUpdLanguageData,
			'aFlags' => $this->getIconsList()
		));
	}

	public function add()
	{
		if (! $this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/l10n');

		$aAddLanguageData = array(
			'language' 	=> '',
			'country' 	=> '',
			'title' 	=> '',
			'code' 		=> '',
			'img' 		=> '',
			'active' 	=> 1
		);

		if ($this->okt['request']->request->has('form_sent'))
		{
			$aAddLanguageData = array(
				'title' 	=> $this->okt['request']->request->get('add_title'),
				'code' 		=> $this->okt['request']->request->get('add_code'),
				'img' 		=> $this->okt['request']->request->get('add_img'),
				'active' 	=> $this->okt['request']->request->getInt('add_active', 0)
			);

			if ($this->okt->languages->checkPostData($aAddLanguageData))
			{
				$iLanguageId = $this->okt->languages->addLanguage($aAddLanguageData);

				$this->okt['flash']->success(__('c_a_config_l10n_added'));

				return $this->redirect($this->generateUrl('config_l10n_edit_language', array(
					'language_id' => $iLanguageId
				)));
			}
		}

		# fetch languages infos
		$sLanguagesListInfos = $this->okt['root_dir'] . '/vendor/forxer/languages-list/languages.php';

		$aLanguagesList = array(
			' ' => null
		);
		if (file_exists($sLanguagesListInfos))
		{
			$aLanguagesListInfos = require $sLanguagesListInfos;

			foreach ($aLanguagesListInfos as $aLanguageInfo)
			{
				$aLanguagesList[$aLanguageInfo['Native name']] = $aLanguageInfo['639-1'];
			}

			unset($aLanguagesListInfos);
		}

		# fetch country infos
		$sCountryListInfos = $this->okt['okt_dir'] . '/Tao/L10n/country-list/' . $this->okt->user->language . '/country.php';

		$aCountryList = array(
			' ' => null
		);

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
		foreach (new DirectoryIterator($this->okt['public_dir'] . '/img/flags/') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || ! $oFileInfo->isFile() || $oFileInfo->getExtension() !== 'png') {
				continue;
			}

			$aFlags[str_replace('.png', '', $oFileInfo->getFilename())] = $oFileInfo->getFilename();
		}
		natsort($aFlags);

		return $aFlags;
	}

	protected function switchLanguageStatus()
	{
		if ($this->okt['request']->query->has('switch_status'))
		{
			$this->okt->languages->switchLangStatus($this->okt['request']->query->get('switch_status'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function enableLanguage()
	{
		if ($this->okt['request']->query->has('enable'))
		{
			$this->okt->languages->setLangStatus($this->okt['request']->query->get('enable'), 1);

			$this->okt['flash']->success(__('c_a_config_l10n_enabled'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function disableLanguage()
	{
		if ($this->okt['request']->query->has('disable'))
		{
			$this->okt->languages->setLangStatus($this->okt['request']->query->get('disable'), 0);

			$this->okt['flash']->success(__('c_a_config_l10n_disabled'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function deleteLanguage()
	{
		if ($this->okt['request']->query->has('delete'))
		{
			$this->okt->languages->delLanguage($this->okt['request']->query->get('delete'));

			$this->okt['flash']->success(__('c_a_config_l10n_deleted'));

			return $this->redirect($this->generateUrl('config_l10n'));
		}

		return false;
	}

	protected function addLanguage()
	{
		if ($this->okt['request']->request->has('add_languages'))
		{
			$this->aAddLanguageData = array(
				'title' => $this->okt['request']->request->get('add_title'),
				'code' => $this->okt['request']->request->get('add_code'),
				'img' => $this->okt['request']->request->get('add_img'),
				'active' => $this->okt['request']->request->getInt('add_active', 0)
			);

			if ($this->okt->languages->checkPostData($this->aAddLanguageData))
			{
				$this->okt->languages->addLanguage($this->aAddLanguageData);

				$this->okt['flash']->success(__('c_a_config_l10n_added'));

				return $this->redirect($this->generateUrl('config_l10n'));
			}
		}

		return false;
	}

	protected function updateLanguagesOrderByAjax()
	{
		if ($this->okt['request']->query->has('ajax_update_order'))
		{
			$aLanguagesOrder = $this->okt['request']->query->get('ord', array());

			if (! empty($aLanguagesOrder))
			{
				foreach ($aLanguagesOrder as $ord => $id)
				{
					$ord = ((integer) $ord) + 1;
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
		if ($this->okt['request']->request->has('order_languages'))
		{
			$aLanguagesOrder = $this->okt['request']->request->get('p_order', array());

			asort($aLanguagesOrder);

			$aLanguagesOrder = array_keys($aLanguagesOrder);

			if (! empty($aLanguagesOrder))
			{
				foreach ($aLanguagesOrder as $ord => $id)
				{
					$ord = ((integer) $ord) + 1;
					$this->okt->languages->updLanguageOrder($id, $ord);
				}

				$this->okt->languages->generateCacheList();

				$this->okt['flash']->success(__('c_a_config_l10n_neworder'));

				return $this->redirect($this->generateUrl('config_l10n'));
			}
		}

		return false;
	}

	protected function updateConfiguration()
	{
		if ($this->okt['request']->request->has('config_sent'))
		{
			if (! $this->okt['flash']->hasError())
			{
				$this->okt['config']->write(array(
					'language' => $this->okt['request']->request->get('p_language'),
					'timezone' => $this->okt['request']->request->get('p_timezone'),
					'admin_lang_switcher' => $this->okt['request']->request->has('p_admin_lang_switcher')
				));

				$this->okt->languages->generateCacheList();

				$this->okt['flash']->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_l10n'));
			}
		}

		return false;
	}
}
