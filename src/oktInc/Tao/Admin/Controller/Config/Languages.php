<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;

class Languages extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('languages')) {
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.languages');

		$iLangId = $this->request->request->getInt('id', $this->request->query->getInt('id'));

		$aAddLanguageData = array(
			'title' 	=> '',
			'code' 		=> '',
			'img' 		=> '',
			'active' 	=> 1
		);

		$aUpdLanguageData = array(
			'title' 	=> '',
			'code' 		=> '',
			'img' 		=> '',
			'active' 	=> 1
		);

		if ($iLangId)
		{
			$rsLanguage = $this->okt->languages->getLanguages(array('id'=>$iLangId));

			$aUpdLanguageData = array(
				'title' 	=> $rsLanguage->title,
				'code' 		=> $rsLanguage->code,
				'img' 		=> $rsLanguage->img,
				'active' 	=> $rsLanguage->active
			);
		}

		# switch language status
		if ($this->request->query->has('switch_status'))
		{
			$this->okt->languages->switchLangStatus($this->request->query->get('switch_status'));
			$this->redirect($this->generateUrl('config_languages'));
		}

		# enable language
		if ($this->request->query->has('enable'))
		{
			$this->okt->languages->setLangStatus($this->request->query->get('enable'), 1);
			$this->page->flash->success(__('c_a_config_l10n_enabled'));
			$this->redirect($this->generateUrl('config_languages'));
		}

		# disable language
		if ($this->request->query->has('disable'))
		{
			$this->okt->languages->setLangStatus($this->request->query->get('disable'), 0);
			$this->page->flash->success(__('c_a_config_l10n_disabled'));
			$this->redirect($this->generateUrl('config_languages'));
		}

		# suppression d'une langue
		if ($this->request->query->has('delete'))
		{
			$this->okt->languages->delLanguage($this->request->query->get('delete'));
			$this->page->flash->success(__('c_a_config_l10n_deleted'));
			$this->redirect($this->generateUrl('config_languages'));
		}

		# ajout d'une langue
		if ($this->request->request->has('add_languages'))
		{
			$aAddLanguageData = array(
				'title' 	=> $this->request->request->get('add_title'),
				'code' 		=> $this->request->request->get('add_code'),
				'img' 		=> $this->request->request->get('add_img'),
				'active' 	=> $this->request->request->getInt('add_active', 0)
			);

			if ($this->okt->languages->checkPostData($aAddLanguageData))
			{
				$this->okt->languages->addLanguage($aAddLanguageData);
				$this->page->flash->success(__('c_a_config_l10n_added'));
				$this->redirect($this->generateUrl('config_languages'));
			}
		}

		# modification d'une langue
		if ($this->request->request->has('edit_languages') && $iLangId)
		{
			$aUpdLanguageData = array(
				'id' 		=> $iLangId,
				'title' 	=> $this->request->request->get('edit_title'),
				'code' 		=> $this->request->request->get('edit_code'),
				'img' 		=> $this->request->request->get('edit_img'),
				'active' 	=> $this->request->request->getInt('edit_active', 0)
			);

			if ($this->okt->languages->checkPostData($aUpdLanguageData))
			{
				$this->okt->languages->updLanguage($aUpdLanguageData);
				$this->page->flash->success(__('c_a_config_l10n_edited'));
				$this->redirect($this->generateUrl('config_languages'));
			}
		}


		# AJAX : changement de l'ordre des langues
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

		# POST : changement de l'ordre des langues
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

				$this->redirect($this->generateUrl('config_languages'));
			}
		}

		# configuration
		if ($this->request->request->has('config_sent'))
		{
			$p_language = $this->request->request->get('p_language');
			$p_timezone = $this->request->request->get('p_timezone');
			$p_admin_lang_switcher = $this->request->request->has('p_admin_lang_switcher') ? true : false;

			if ($this->okt->error->isEmpty())
			{
				$new_conf = array(
					'language' => $p_language,
					'timezone' => $p_timezone,
					'admin_lang_switcher' => $p_admin_lang_switcher
				);

				try
				{
					$this->okt->config->write($new_conf);
					$this->okt->languages->generateCacheList();
					$this->page->flash->success(__('c_c_confirm_configuration_updated'));
					$this->redirect($this->generateUrl('config_languages'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		# Liste des langues
		$rsLanguages = $this->okt->languages->getLanguages();

		$aLanguages = array();
		while ($rsLanguages->fetch()) {
			$aLanguages[\html::escapeHTML($rsLanguages->title)] = $rsLanguages->code;
		}

		# Liste des fuseaux horraires
		$aTimezones = \dt::getZones(true,true);

		# Liste des icônes
		$aFlags = array();
		foreach (new \DirectoryIterator($this->okt->options->public_dir.'/img/flags/') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || !$oFileInfo->isFile() || $oFileInfo->getExtension() !== 'png') {
				continue;
			}

			$aFlags[str_replace('.png', '', $oFileInfo->getFilename())] = $oFileInfo->getFilename();
		}
		natsort($aFlags);

		return $this->render('Config/Languages', array(
			'iLangId' => $iLangId,
			'aAddLanguageData' => $aAddLanguageData,
			'aUpdLanguageData' => $aUpdLanguageData,
			'rsLanguages' => $rsLanguages,
			'aLanguages' => $aLanguages,
			'aTimezones' => $aTimezones,
			'aFlags' => $aFlags
		));
	}
}
