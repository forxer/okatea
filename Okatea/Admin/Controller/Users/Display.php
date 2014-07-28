<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Users;

use ArrayObject;
use Okatea\Admin\Controller;

class Display extends Controller
{

	protected $aPageData;

	public function page()
	{
		if (! $this->okt->checkPerm('users_display'))
		{
			return $this->serve401();
		}
		
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir') . '/%s/admin/users');
		
		$this->aPageData = new ArrayObject(array());
		$this->aPageData['config'] = array(
			'users_filters' => array(
				'public_default_nb_per_page' => $this->okt->config->users_filters['public_default_nb_per_page'],
				'admin_default_nb_per_page' => $this->okt->config->users_filters['admin_default_nb_per_page']
			)
		);
		
		# enregistrement configuration
		if ($this->okt->request->request->has('form_sent'))
		{
			$this->aPageData['config'] = array(
				'users_filters' => array(
					'public_default_nb_per_page' => $this->okt->request->request->getInt('p_public_default_nb_per_page', 10),
					'admin_default_nb_per_page' => $this->okt->request->request->getInt('p_admin_default_nb_per_page', 10)
				)
			);
			
			if (! $this->flash->hasError())
			{
				$this->okt->config->write($this->aPageData['config']);
				
				$this->okt->flash->success(__('c_c_confirm_configuration_updated'));
				
				return $this->redirect($this->generateUrl('Users_display'));
			}
		}
		
		return $this->render('Users/Display', array(
			'aPageData' => $this->aPageData
		));
	}
}
