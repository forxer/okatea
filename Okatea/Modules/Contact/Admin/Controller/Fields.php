<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact\Admin\Controller;

use Okatea\Admin\Controller;

class Fields extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('contact_usage') || !$this->okt->checkPerm('contact_fields')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/%s/admin.fields');

		# suppression d'un champ
		if (!empty($_GET['delete']))
		{
			if ($okt->contact->delField($_GET['delete']))
			{
				$okt->page->flash->success(__('m_contact_fields_deleted'));

				return $this->redirect($this->generateUrl('Contact_fields'));
			}
		}

		# enregistrement de l'ordre des champs
		$order = array();
		if (empty($_POST['fields_order']) && !empty($_POST['order']))
		{
			$order = $_POST['order'];
			asort($order);
			$order = array_keys($order);
		}
		elseif (!empty($_POST['fields_order']))
		{
			$order = explode(',',$_POST['fields_order']);
			foreach ($order as $k=>$v) {
				$order[$k] = str_replace('ord_','',$v);
			}
		}

		if (!empty($_POST['ordered']) && !empty($order))
		{
			foreach ($order as $ord=>$id)
			{
				$ord = ((integer) $ord)+1;
				$okt->contact->updFieldOrder($id,$ord);
			}

			$okt->page->flash->success(__('m_contact_neworder'));

			return $this->redirect($this->generateUrl('Contact_fields'));
		}


		return $this->render('Contact/Admin/Templates/Fields', array(

		));
	}

	public function field()
	{

	}
}
