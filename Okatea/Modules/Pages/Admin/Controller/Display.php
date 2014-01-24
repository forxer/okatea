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
		if (!$this->okt->checkPerm('pages_display')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../locales/'.$this->okt->user->language.'/admin.display');

		if ($this->request->request->has('form_sent'))
		{
			$p_admin_filters_style = $this->request->request->get('p_admin_filters_style', 'dialog');

			$p_public_default_order_by = $this->request->request->get('p_public_default_order_by', 'id');
			$p_public_default_order_direction = $this->request->request->get('p_public_default_order_direction', 'DESC');
			$p_public_default_nb_per_page = $this->request->request->getInt('p_public_default_nb_per_page', 10);

			$p_public_truncat_char = $this->request->request->getInt('p_public_truncat_char');
			$p_insert_truncat_char = $this->request->request->getInt('p_insert_truncat_char');

			$p_admin_default_order_by = $this->request->request->get('p_admin_default_order_by', 'id');
			$p_admin_default_order_direction = $this->request->request->get('p_admin_default_order_direction', 'DESC');
			$p_admin_default_nb_per_page = $this->request->request->getInt('p_admin_default_nb_per_page', 10);

			$p_lightbox_type = $this->request->request->get('p_lightbox_type');

			if ($this->okt->error->isEmpty())
			{
				$new_conf = array(
					'admin_dysplay_style' => $p_admin_dysplay_style,
					'admin_filters_style' => $p_admin_filters_style,

					'admin_default_order_by' => $p_admin_default_order_by,
					'admin_default_order_direction' => $p_admin_default_order_direction,
					'admin_default_nb_per_page' => (integer)$p_admin_default_nb_per_page,

					'public_default_order_by' => $p_public_default_order_by,
					'public_default_order_direction' => $p_public_default_order_direction,
					'public_default_nb_per_page' => (integer)$p_public_default_nb_per_page,

					'public_truncat_char' => (integer)$p_public_truncat_char,
					'insert_truncat_char' => (integer)$p_insert_truncat_char,

					'lightbox_type' => $p_lightbox_type
				);

				try
				{
					$this->okt->Pages->config->write($new_conf);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('Pages_display'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
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
