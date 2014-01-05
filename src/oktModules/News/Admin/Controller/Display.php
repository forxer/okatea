<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\News\Admin\Controller;

use Tao\Admin\Controller;

class Display extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('news_display')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/'.$this->okt->user->language.'/admin.display');

		if ($this->request->request->has('form_sent'))
		{
			try
			{
				$this->okt->News->config->write(array(
					'admin_dysplay_style' => $this->request->request->get('p_admin_dysplay_style', 'list'),
					'admin_filters_style' => $this->request->request->get('p_admin_filters_style', 'dialog'),

					'admin_default_order_by' => $this->request->request->get('p_admin_default_order_by', 'id'),
					'admin_default_order_direction' => $this->request->request->get('p_admin_default_order_direction', 'DESC'),
					'admin_default_nb_per_page' => $this->request->request->getInt('p_admin_default_nb_per_page', 10),

					'public_default_order_by' => $this->request->request->get('p_public_default_order_by', 'id'),
					'public_default_order_direction' => $this->request->request->get('p_public_default_order_direction', 'DESC'),
					'public_default_nb_per_page' => $this->request->request->getInt('p_public_default_nb_per_page', 10),

					'public_display_date' => $this->request->request->has('p_public_display_date') ? true : false,
					'public_display_author' => $this->request->request->has('p_public_display_author') ? true : false,

					'public_truncat_char' => $this->request->request->getInt('p_public_truncat_char', 0),
					'insert_truncat_char' => $this->request->request->getInt('p_insert_truncat_char', 0),

					'lightbox_type' => $this->request->request->get('p_lightbox_type')
				));

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('News_display'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		$aFieldChoiceOrderBy = array(
			__('m_news_display_order_by_created') => 'created_at',
			__('m_news_display_order_by_updated') => 'updated_at',
			__('m_news_display_order_by_title') => 'title',
			__('m_news_display_order_by_category') => 'rubrique'
		);

		$aFieldChoiceOrderDirection = array(
			__('c_c_sorting_Ascending') => 'ASC',
			__('c_c_sorting_Descending') => 'DESC'
		);

		return $this->render('news/Admin/Templates/Display', array(
			'aFieldChoiceOrderBy' => $aFieldChoiceOrderBy,
			'aFieldChoiceOrderDirection' => $aFieldChoiceOrderDirection
		));
	}
}
