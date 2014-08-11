<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages\Admin\Controller;

use Okatea\Admin\Controller;

class Display extends Controller
{

	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('pages_display'))
		{
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt['l10n']->loadFile(__DIR__ . '/../../Locales/%s/admin.display');

		if ($this->okt['request']->request->has('form_sent'))
		{
			$p_admin_filters_style = $this->okt['request']->request->get('p_admin_filters_style', 'dialog');

			$p_public_default_order_by = $this->okt['request']->request->get('p_public_default_order_by', 'id');
			$p_public_default_order_direction = $this->okt['request']->request->get('p_public_default_order_direction', 'DESC');
			$p_public_default_nb_per_page = $this->okt['request']->request->getInt('p_public_default_nb_per_page', 10);

			$p_public_truncat_char = $this->okt['request']->request->getInt('p_public_truncat_char');
			$p_insert_truncat_char = $this->okt['request']->request->getInt('p_insert_truncat_char');

			$p_admin_default_order_by = $this->okt['request']->request->get('p_admin_default_order_by', 'id');
			$p_admin_default_order_direction = $this->okt['request']->request->get('p_admin_default_order_direction', 'DESC');
			$p_admin_default_nb_per_page = $this->okt['request']->request->getInt('p_admin_default_nb_per_page', 10);

			$p_lightbox_type = $this->okt['request']->request->get('p_lightbox_type');

			if (!$this->okt['flashMessages']->hasError())
			{
				$aNewConf = array(
					'admin_filters_style' => $p_admin_filters_style,

					'admin_default_order_by' => $p_admin_default_order_by,
					'admin_default_order_direction' => $p_admin_default_order_direction,
					'admin_default_nb_per_page' => (integer) $p_admin_default_nb_per_page,

					'public_default_order_by' => $p_public_default_order_by,
					'public_default_order_direction' => $p_public_default_order_direction,
					'public_default_nb_per_page' => (integer) $p_public_default_nb_per_page,

					'public_truncat_char' => (integer) $p_public_truncat_char,
					'insert_truncat_char' => (integer) $p_insert_truncat_char,

					'lightbox_type' => $p_lightbox_type
				);

				$this->okt->module('Pages')->config->write($aNewConf);

				$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('Pages_display'));
			}
		}

		$aFieldChoiceOrderBy = array(
			__('m_pages_display_order_by_created') => 'created_at',
			__('m_pages_display_order_by_updated') => 'updated_at',
			__('m_pages_display_order_by_title') => 'title',
			__('m_pages_display_order_by_category') => 'rubrique'
		);

		$aFieldChoiceOrderDirection = array(
			__('c_c_sorting_Ascending') => 'ASC',
			__('c_c_sorting_Descending') => 'DESC'
		);

		return $this->render('Pages/Admin/Templates/Display', array(
			'aFieldChoiceOrderBy' => $aFieldChoiceOrderBy,
			'aFieldChoiceOrderDirection' => $aFieldChoiceOrderDirection
		));
	}
}
