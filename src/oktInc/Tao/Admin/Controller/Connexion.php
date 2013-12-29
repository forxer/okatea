<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller;

use Tao\Admin\Controller;

class Connexion extends Controller
{
	public function login()
	{
		# allready logged
		if (!$this->okt->user->is_guest) {
			$this->redirect($this->generateUrl('home'));
		}

		# identification
		$sUserId = $this->request->request->get('user_id', $this->request->query->get('user_id'));
		$sUserPwd = $this->request->request->get('user_pwd', $this->request->query->get('user_pwd'));

		if (!empty($sUserId) && !empty($sUserPwd))
		{
			$bUserRemember = $this->request->request->has('user_remember') ? true : false;

			if ($this->okt->user->login($sUserId, $sUserPwd, $bUserRemember))
			{
				$redir = $this->generateUrl('home');

				if ($this->request->cookies->has($this->okt->options->get('cookie_auth_from')))
				{
					if ($this->request->cookies->get($this->okt->options->get('cookie_auth_from')) != $this->request->getUri()) {
						$redir = $this->request->cookies->get($this->okt->options->get('cookie_auth_from'));
					}

					$this->okt->user->setAuthFromCookie('', 0);
				}

				$this->redirect($redir);
			}
		}

		# Titre de la page
		$this->page->addGlobalTitle(__('c_c_auth_login'));

		$this->page->pageId('connexion');

		$this->page->breadcrumb->reset();

		define('OKT_DISABLE_MENU', true);

		return $this->render('connexion', array(
			'sUserId' => $sUserId
		));
	}
}
