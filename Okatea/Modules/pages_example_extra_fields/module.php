<?php
/**
 * @ingroup okt_module_pages
 * @brief La classe principale du Module Pages example extra fields.
 *
 */

use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Modules\Module;

class module_pages_example_extra_fields extends Module
{
	protected function prepend()
	{
		if (!$this->okt->modules->isLoaded('pages')) {
			return null;
		}

		# enregistrement des triggers
		$this->okt->module('Pages')->triggers->registerTrigger('getPagesSelectFields',  array($this, 'getPagesSelectFields'));
		$this->okt->module('Pages')->triggers->registerTrigger('adminPostInit',         array($this, 'adminPostInit'));
		$this->okt->module('Pages')->triggers->registerTrigger('adminPopulateData',     array($this, 'adminPopulateData'));
		$this->okt->module('Pages')->triggers->registerTrigger('checkPostData',         array($this, 'checkPostData'));
		$this->okt->module('Pages')->triggers->registerTrigger('beforePageUpdate',      array($this, 'beforePageUpdate'));
		$this->okt->module('Pages')->triggers->registerTrigger('beforePageCreate',      array($this, 'beforePageCreate'));
		$this->okt->module('Pages')->triggers->registerTrigger('adminPostBuildTabs',    array($this, 'adminPostBuildTabs'));
	}

	/**
	 * Ajout des champs à la requete de récupération des pages.
	 *
	 * @param arrayObject $oFields
	 * @return void
	 */
	public function getPagesSelectFields($oFields)
	{
		# récupération du champ "checkbox"
		$oFields[] = 'p.checkbox';

		# récupération du champ "input"
		$oFields[] = 'p.date';

		# récupération du champ "required"
		$oFields[] = 'p.required';

		# récupération du champ "multilangue"
		$oFields[] = 'pl.multilangue';

		# récupération du champ "editor"
		$oFields[] = 'pl.editor';
	}

	/**
	 * Initialisation des champs dans le tableau de données de la page en cours d'ajout/modification.
	 *
	 * @param arrayObject $aPageData
	 * @param pagesRecordset $rsPage
	 * @param recordset $rsPageI18n
	 * @return void
	 */
	public function adminPostInit($aPageData, $rsPage=null, $rsPageI18n=null)
	{
		# initialisation du champ "checkbox"
		$aPageData['post']['checkbox'] = !empty($rsPage) ? $rsPage->checkbox : 0;

		# initialisation du champ "date"
		$aPageData['post']['date'] = !empty($rsPage) ? $rsPage->date : null;

		# initialisation du champ "required"
		$aPageData['post']['required'] = !empty($rsPage) ? $rsPage->required : null;

		# initialisation des champs "multilangue" et "editor"
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aPageData['locales'][$aLanguage['code']]['multilangue'] = null;
			$aPageData['locales'][$aLanguage['code']]['editor'] = null;
		}

		if (!is_null($rsPageI18n))
		{
			foreach ($this->okt->languages->list as $aLanguage)
			{
				while ($rsPageI18n->fetch())
				{
					if ($rsPageI18n->language == $aLanguage['code'])
					{
						$aPageData['locales'][$aLanguage['code']]['multilangue'] = $rsPageI18n->multilangue;
						$aPageData['locales'][$aLanguage['code']]['editor'] = $rsPageI18n->editor;
					}
				}
			}
		}
	}

	/**
	 * Peuplement des données de la page avec données de $_POST.
	 *
	 * @param arrayObject $aPageData
	 * @return void
	 */
	public function adminPopulateData($aPageData)
	{
		# récupération du champ "checkbox"
		$aPageData['post']['checkbox'] = !empty($_POST['p_checkbox']) ? 1 : 0;

		# récupération du champ "date"
		$aPageData['post']['date'] = !empty($_POST['p_date']) ? MySqli::formatDateTime($_POST['p_date']) : null;

		# récupération du champ "required"
		$aPageData['post']['required'] = !empty($_POST['p_required']) ? $_POST['p_required'] : null;

		# récupération des champs "multilangue" et "editor"
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aPageData['locales'][$aLanguage['code']]['multilangue'] = !empty($_POST['p_multilangue'][$aLanguage['code']]) ? $_POST['p_multilangue'][$aLanguage['code']] : '';
			$aPageData['locales'][$aLanguage['code']]['editor'] = !empty($_POST['p_editor'][$aLanguage['code']]) ? $_POST['p_editor'][$aLanguage['code']] : '';
		}
	}

	/**
	 * Vérification des données envoyées en $_POST.
	 *
	 * @param arrayObject $aPageData
	 * @return void
	 */
	public function checkPostData($aPageData)
	{
		# vérification du champ "required"
		if (empty($aPageData['post']['required'])) {
			$this->okt->error->set(__('m_pages_example_extra_fields_must_set_required'));
		}
	}

	/**
	 * Traitement des champs avant modification d'une page.
	 *
	 * @param arrayObject $aPageData
	 * @return void
	 */
	public function beforePageUpdate($aPageData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (!empty($aPageData['locales'][$aLanguage['code']]['editor'])) {
				$aPageData['locales'][$aLanguage['code']]['editor'] = $this->okt->HTMLfilter($aPageData['locales'][$aLanguage['code']]['editor']);
			}
		}
	}

	/**
	 * Traitement des champs avant ajout d'une page.
	 *
	 * @param arrayObject $aPageData
	 * @return void
	 */
	public function beforePageCreate($aPageData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (!empty($aPageData['locales'][$aLanguage['code']]['editor'])) {
				$aPageData['locales'][$aLanguage['code']]['editor'] = $this->okt->HTMLfilter($aPageData['locales'][$aLanguage['code']]['editor']);
			}
		}
	}

	/**
	 * Ajout des champs au formulaire de modification d'une page.
	 *
	 * @param arrayObject $aPageData
	 * @return void
	 */
	public function adminPostBuildTabs($aPageData)
	{
		# ajout du champ "checkbox" à l'onglet "options"
		$aPageData['tabs'][40]['content'] .=
			'<p class="field"><label>'.form::checkbox('p_checkbox', 1, $aPageData['post']['checkbox']).
			' '.__('m_pages_example_extra_fields_checkbox_label').'</label></p>';

		# ajout du champ "date" à l'onglet "options" avec le UI datepicker
		$this->okt->page->datePicker();
		$aPageData['tabs'][40]['content'] .=
			'<p class="field col"><label for="p_date">'.__('m_pages_example_extra_fields_date_label').'</label>'.
			form::text('p_date', 20, 255, (!empty($aPageData['post']['date']) ? dt::dt2str('%d-%m-%Y', $aPageData['post']['date']) : ''), 'datepicker').'</p>';

		# ajout du champ "required" à l'onglet "Contenu"
		$aPageData['tabs'][40]['content'] .=
			'<p class="field col"><label for="p_required" title="'.__('c_c_required_field').'" class="required">'.__('m_pages_example_extra_fields_required_label').'</label>'.
			form::text('p_required', 20, 255, $aPageData['post']['required']).'</p>';

		# ajout des champs "multilangue" et "editor" à l'onglet "Contenu"
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aPageData['tabs'][10]['content'] .=
				'<p class="field" lang="'.$aLanguage['code'].'"><label for="p_multilangue_'.$aLanguage['code'].'">'.($this->okt->languages->unique ? __('m_pages_example_extra_fields_multilangue_label') : sprintf(__('m_pages_example_extra_fields_multilangue_label_in_%s'),$aLanguage['title'])).' <span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_multilangue['.$aLanguage['code'].']','p_multilangue_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['multilangue'])).'</p>';

			$aPageData['tabs'][10]['content'] .=
				'<p class="field" lang="'.$aLanguage['code'].'"><label for="p_editor_'.$aLanguage['code'].'">'.($this->okt->languages->unique ? __('m_pages_example_extra_fields_editor_label') : sprintf(__('m_pages_example_extra_fields_editor_label_in_%s'),$aLanguage['title'])).' <span class="lang-switcher-buttons"></span></label>'.
				form::textarea(array('p_editor['.$aLanguage['code'].']','p_editor_'.$aLanguage['code']), 97, 15, $aPageData['locales'][$aLanguage['code']]['editor'], 'richTextEditor').'</p>';
		}
	}
}
