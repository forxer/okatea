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

		$this->page->pageId('connexion');

		$this->page->breadcrumb->reset();

		$this->page->display_menu = false;

		return $this->render('Connexion/Login', array(
			'sUserId' => $sUserId
		));
	}

	public function forget_password()
	{
		# allready logged
		if (!$this->okt->user->is_guest) {
			$this->redirect($this->generateUrl('home'));
		}

		$bPasswordUpdated = false;
		$bPasswordSended = false;

		if ($this->request->query->has('key') && $this->request->query->has('uid'))
		{
			$bPasswordUpdated = $this->okt->user->validatePasswordKey(
				$this->request->query->getInt('key'),
				$this->request->query->get('key')
			);
		}
		elseif ($this->request->request->has('email'))
		{
			$bPasswordSended = $this->okt->user->forgetPassword(
				$this->request->request->filter('email', null, false, FILTER_SANITIZE_EMAIL),
				$this->generateUrl('forget_password', array(), true)
			);
		}


		$this->page->pageId('connexion');

		$this->page->breadcrumb->reset();

		$this->page->display_menu = false;

		return $this->render('Connexion/ForgetPassword', array(
			'bPasswordUpdated' => $bPasswordUpdated,
			'bPasswordSended' => $bPasswordSended
		));
	}
}
