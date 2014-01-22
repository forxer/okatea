<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;

class Display extends Controller
{
    protected $aPageData;
    
	public function page()
	{
		if (!$this->okt->checkPerm('users_display')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/'.$this->okt->user->language.'/admin/users');

		$this->aPageData = new \ArrayObject(array());
        $this->aPageData['config'] = array(
	        'users_filters' => array(
    		    'admin_default_nb_per_page' 	=> $this->okt->request->request->getInt('p_admin_default_nb_per_page', 10),
    		    'public_default_nb_per_page' 	=> $this->okt->request->request->getInt('p_public_default_nb_per_page', 10)		
	        )    
		);
		
		# enregistrement configuration
		if ($this->okt->request->request->has('form_sent'))
		{
			if ($this->okt->error->isEmpty())
			{
				$this->aPageData['config'] = array(
				    'users_filters' => array(
    					'admin_default_nb_per_page' 	=> $this->okt->request->request->getInt('p_admin_default_nb_per_page', 10),
    					'public_default_nb_per_page' 	=> $this->okt->request->request->getInt('p_public_default_nb_per_page', 10)
				    )
				);

				try
				{
					$this->okt->config->write($this->aPageData['config']);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('Users_display'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		return $this->render('Users/Display', array(
		));
	}
}
