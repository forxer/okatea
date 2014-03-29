<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact\Admin\Controller;

use ArrayObject;
use Okatea\Admin\Controller;

class Fields extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('contact_usage') || !$this->okt->checkPerm('contact_fields')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/%s/admin.fields');

		if (($action = $this->deleteField()) !== false) {
			return $action;
		}

		if (($action = $this->updateFieldsOrderByAjax()) !== false) {
			return $action;
		}

		if (($action = $this->updateFieldsOrderByPost()) !== false) {
			return $action;
		}

		return $this->render('Contact/Admin/Templates/Fields', array(

		));
	}

	public function addField()
	{
		if (!$this->okt->checkPerm('contact_usage') || !$this->okt->checkPerm('contact_fields')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/%s/admin.fields');

		$aFieldData = new ArrayObject();

		$aFieldData['status'] = 0;
		$aFieldData['type'] = 1;
		$aFieldData['html_id'] = '';
		$aFieldData['locales'] = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aFieldData['locales'][$aLanguage['code']] = array();
			$aFieldData['locales'][$aLanguage['code']]['title'] = '';
			$aFieldData['locales'][$aLanguage['code']]['description'] = '';
		}

		if ($this->request->request->has('form_sent'))
		{
			$field_data = array(
				'type' 			=> $this->request->request->getInt('p_type'),
				'status' 		=> $this->request->request->getInt('p_status'),
				'title' 		=> (!empty($_POST['p_title']) ? $_POST['p_title'] : array()),
				'html_id' 		=> (!empty($_POST['p_html_id']) ? $_POST['p_html_id'] : ''),
				'description' 	=> (!empty($_POST['p_description']) ? $_POST['p_description'] : array())
			);

			if (empty($field_data['title']['fr'])) {
				$okt->error->set('Vous devez saisir un titre.');
			}

			if (empty($field_data['type'])) {
				$okt->error->set('Vous devez choisir un type.');
			}

			if ($okt->error->isEmpty())
			{
				if (($iFieldId = $okt->contact->addField($field_data)) !== false) {
					$this->redirect($this->generateUrl('Contact_field_values', array('field_id' => $iFieldId)));
				}
			}
		}

		return $this->render('Contact/Admin/Templates/addField', array(
			'aFieldData' 	=> $aFieldData
		));
	}

	public function fieldValues()
	{
		if (!$this->okt->checkPerm('contact_usage') || !$this->okt->checkPerm('contact_fields')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/%s/admin.fields');

	}

	public function field()
	{
		if (!$this->okt->checkPerm('contact_usage') || !$this->okt->checkPerm('contact_fields')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/%s/admin.fields');

	}

	protected function deleteField()
	{
		if ($this->request->query->has('delete'))
		{
			$this->okt->module('Contact')->fields->deleteField($this->request->query->get('delete'));

			$this->page->flash->success(__('m_contact_fields_field_deleted'));

			return $this->redirect($this->generateUrl('Contact_fields'));
		}

		return false;
	}

	protected function updateFieldsOrderByAjax()
	{
		if ($this->request->query->has('ajax_update_order'))
		{
			$aFieldsOrder = $this->request->query->get('ord', array());

			if (!empty($aFieldsOrder))
			{
				foreach ($aFieldsOrder as $ord=>$id)
				{
					$ord = ((integer)$ord)+1;
					$this->okt->module('Contact')->fields->updFieldOrder($id, $ord);
				}
			}

			exit();
		}

		return false;
	}

	protected function updateFieldsOrderByPost()
	{
		if ($this->request->request->has('order_languages'))
		{
			$aFieldsOrder = $this->request->request->get('p_order', array());

			asort($aFieldsOrder);

			$aFieldsOrder = array_keys($aFieldsOrder);

			if (!empty($aFieldsOrder))
			{
				foreach ($aFieldsOrder as $ord=>$id)
				{
					$ord = ((integer)$ord)+1;
					$this->okt->module('Contact')->fields->updFieldOrder($id, $ord);
				}

				$this->page->flash->success(__('m_contact_fields_neworder'));

				return $this->redirect($this->generateUrl('Contact_fields'));
			}
		}

		return false;
	}

}
