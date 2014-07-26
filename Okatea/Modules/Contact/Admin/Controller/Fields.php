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
use Okatea\Modules\Contact\Fields as FieldsManager;

class Fields extends Controller
{

	protected $aFieldData;

	public function page()
	{
		if (! $this->okt->checkPerm('contact_usage') || ! $this->okt->checkPerm('contact_fields'))
		{
			return $this->serve401();
		}
		
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.fields');
		
		if (($action = $this->deleteField()) !== false)
		{
			return $action;
		}
		
		if (($action = $this->updateFieldsOrderByAjax()) !== false)
		{
			return $action;
		}
		
		if (($action = $this->updateFieldsOrderByPost()) !== false)
		{
			return $action;
		}
		
		$rsFields = $this->okt->module('Contact')->fields->getFields(array(
			'language' => $this->okt->user->language
		));
		
		return $this->render('Contact/Admin/Templates/Fields/Index', array(
			'rsFields' => $rsFields,
			'aTypes' => FieldsManager::getFieldsTypes(),
			'aStatus' => FieldsManager::getFieldsStatus()
		));
	}

	public function addField()
	{
		if (! $this->okt->checkPerm('contact_usage') || ! $this->okt->checkPerm('contact_fields'))
		{
			return $this->serve401();
		}
		
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.fields');
		
		$this->initFieldData();
		
		if ($this->request->request->has('form_sent'))
		{
			$this->populateFieldDataFromPost();
			
			$this->okt->module('Contact')->fields->checkPostData($this->aFieldData);
			
			if ($this->okt->error->isEmpty())
			{
				if (($this->aFieldData['id'] = $this->okt->module('Contact')->fields->addField($this->aFieldData)) !== false)
				{
					$this->flash->success(__('m_contact_fields_field_added'));
					
					return $this->redirect($this->generateUrl('Contact_field_values', array(
						'field_id' => $this->aFieldData['id']
					)));
				}
			}
		}
		
		return $this->render('Contact/Admin/Templates/Fields/Add', array(
			'aFieldData' => $this->aFieldData
		));
	}

	public function field()
	{
		if (! $this->okt->checkPerm('contact_usage') || ! $this->okt->checkPerm('contact_fields'))
		{
			return $this->serve401();
		}
		
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.fields');
		
		$this->initFieldData();
		
		$this->aFieldData['id'] = $this->request->attributes->getInt('field_id');
		
		$rsField = $this->okt->module('Contact')->fields->getField($this->aFieldData['id']);
		
		if (null === $this->aFieldData['id'] || $rsField->isEmpty())
		{
			$this->okt->error->set(sprintf(__('m_contact_field_%s_not_exists'), $this->aFieldData['id']));
			return $this->serve404();
		}
		
		$this->aFieldData['status'] = $rsField->status;
		$this->aFieldData['type'] = $rsField->type;
		$this->aFieldData['html_id'] = $rsField->html_id;
		
		$rsFieldL10n = $this->okt->module('Contact')->fields->getFieldL10n($this->aFieldData['id']);
		
		foreach ($this->okt->languages->list as $aLanguage)
		{
			while ($rsFieldL10n->fetch())
			{
				if ($rsFieldL10n->language == $aLanguage['code'])
				{
					$this->aFieldData['locales'][$aLanguage['code']]['title'] = $rsFieldL10n->title;
					$this->aFieldData['locales'][$aLanguage['code']]['description'] = $rsFieldL10n->description;
				}
			}
		}
		
		if ($this->request->request->has('form_sent'))
		{
			$this->populateFieldDataFromPost();
			
			$this->okt->module('Contact')->fields->checkPostData($this->aFieldData);
			
			if ($this->okt->error->isEmpty())
			{
				if ($this->okt->module('Contact')->fields->updField($this->aFieldData) !== false)
				{
					$this->flash->success(__('m_contact_fields_field_updated'));
					
					return $this->redirect($this->generateUrl('Contact_field', array(
						'field_id' => $this->aFieldData['id']
					)));
				}
			}
		}
		
		return $this->render('Contact/Admin/Templates/Fields/Edit', array(
			'aFieldData' => $this->aFieldData
		));
	}

	public function fieldValues()
	{
		if (! $this->okt->checkPerm('contact_usage') || ! $this->okt->checkPerm('contact_fields'))
		{
			return $this->serve401();
		}
		
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.fields');
		
		$iFieldId = $this->request->attributes->get('field_id');
		
		$rsField = $this->okt->module('Contact')->fields->getFields(array(
			'id' => $iFieldId,
			'language' => $this->okt->user->language
		));
		
		if (null === $iFieldId || $rsField->isEmpty())
		{
			$this->okt->error->set(sprintf(__('m_contact_field_%s_not_exists'), $iFieldId));
			return $this->serve404();
		}
		
		$rsFieldL10n = $this->okt->module('Contact')->fields->getFieldL10n($iFieldId);
		
		$aValues = array();
		
		while ($rsFieldL10n->fetch())
		{
			if ($rsField->isSimpleField())
			{
				$aValues[$rsFieldL10n->language] = $rsFieldL10n->value;
			}
			else
			{
				$aValues[$rsFieldL10n->language] = array_filter((array) unserialize($rsFieldL10n->value));
			}
		}
		
		if ($this->request->request->has('form_sent'))
		{
			$aValues = $this->request->request->get('p_value');
			
			if ($this->okt->module('Contact')->fields->setFieldValues($iFieldId, $aValues) !== false)
			{
				$this->flash->success(__('m_contact_fields_field_updated'));
				
				return $this->redirect($this->generateUrl('Contact_field_values', array(
					'field_id' => $iFieldId
				)));
			}
		}
		
		return $this->render('Contact/Admin/Templates/Fields/Values', array(
			'rsField' => $rsField,
			'aValues' => $aValues,
			'iNumValues' => count(max($aValues)),
			'aTypes' => FieldsManager::getFieldsTypes()
		));
	}

	protected function deleteField()
	{
		if ($this->request->query->has('delete'))
		{
			$this->okt->module('Contact')->fields->deleteField($this->request->query->get('delete'));
			
			$this->flash->success(__('m_contact_fields_field_deleted'));
			
			return $this->redirect($this->generateUrl('Contact_fields'));
		}
		
		return false;
	}

	protected function updateFieldsOrderByAjax()
	{
		if ($this->request->query->has('ajax_update_order'))
		{
			$aFieldsOrder = $this->request->query->get('ord', array());
			
			if (! empty($aFieldsOrder))
			{
				foreach ($aFieldsOrder as $ord => $id)
				{
					$ord = ((integer) $ord) + 1;
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
			
			if (! empty($aFieldsOrder))
			{
				foreach ($aFieldsOrder as $ord => $id)
				{
					$ord = ((integer) $ord) + 1;
					$this->okt->module('Contact')->fields->updFieldOrder($id, $ord);
				}
				
				$this->flash->success(__('m_contact_fields_neworder'));
				
				return $this->redirect($this->generateUrl('Contact_fields'));
			}
		}
		
		return false;
	}

	protected function initFieldData()
	{
		$this->aFieldData = new ArrayObject();
		
		$this->aFieldData['id'] = null;
		$this->aFieldData['status'] = 0;
		$this->aFieldData['type'] = 1;
		$this->aFieldData['html_id'] = '';
		$this->aFieldData['locales'] = array();
		
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aFieldData['locales'][$aLanguage['code']] = array();
			$this->aFieldData['locales'][$aLanguage['code']]['title'] = '';
			$this->aFieldData['locales'][$aLanguage['code']]['description'] = '';
		}
	}

	protected function populateFieldDataFromPost()
	{
		$this->aFieldData['type'] = $this->request->request->getInt('field_type');
		$this->aFieldData['status'] = $this->request->request->getInt('field_status');
		$this->aFieldData['html_id'] = $this->request->request->get('field_html_id');
		
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aFieldData['locales'][$aLanguage['code']]['title'] = $this->request->request->get('field_title[' . $aLanguage['code'] . ']', null, true);
			$this->aFieldData['locales'][$aLanguage['code']]['description'] = $this->request->request->get('field_description[' . $aLanguage['code'] . ']', null, true);
		}
	}
}
