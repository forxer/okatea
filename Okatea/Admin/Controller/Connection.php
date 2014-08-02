<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller;

use Okatea\Admin\Controller;

class Connection extends Controller
{
	public function login()
	{
		# allready logged
		if (! $this->okt->user->is_guest) {
			return $this->redirect($this->generateUrl('home'));
		}

		# identification
		$sUserId = $this->okt['request']->request->get('user_id', $this->okt['request']->query->get('user_id'));
		$sUserPwd = $this->okt['request']->request->get('user_pwd', $this->okt['request']->query->get('user_pwd'));

		if (! empty($sUserId) && ! empty($sUserPwd))
		{
			$bUserRemember = $this->okt['request']->request->has('user_remember') ? true : false;

			if ($this->okt->user->login($sUserId, $sUserPwd, $bUserRemember))
			{
				$redir = $this->generateUrl('home');

				if ($this->okt['request']->cookies->has($this->okt['cookie_auth_from']))
				{
					if ($this->okt['request']->cookies->get($this->okt['cookie_auth_from']) != $this->okt['request']->getUri())
					{
						$redir = $this->okt['request']->cookies->get($this->okt['cookie_auth_from']);
					}

					$this->okt->user->setAuthFromCookie('', 0);
				}

				return $this->redirect($redir);
			}
		}

		$this->page->pageId('connexion');

		$this->page->breadcrumb->reset();

		$this->page->display_menu = false;

		return $this->render('Connection/Login', array(
			'sUserId' => $sUserId
		));
	}

	public function logout()
	{
		$this->okt->user->setAuthFromCookie('');

		$this->okt->user->logout();

		return $this->Redirect($this->generateUrl('login'));
	}

	public function forget_password()
	{
		# allready logged
		if (! $this->okt->user->is_guest) {
			return $this->redirect($this->generateUrl('home'));
		}

		$bPasswordUpdated = false;
		$bPasswordSended = false;

		if ($this->okt['request']->query->has('key') && $this->okt['request']->query->has('uid'))
		{
			$bPasswordUpdated = $this->okt->getUsers()->validatePasswordKey($this->okt['request']->query->getInt('key'), $this->okt['request']->query->get('key'));
		}
		elseif ($this->okt['request']->request->has('email'))
		{
			$bPasswordSended = $this->okt->getUsers()->forgetPassword($this->okt['request']->request->filter('email', null, false, FILTER_SANITIZE_EMAIL), $this->generateUrl('forget_password', array(), true));
		}

		$this->page->pageId('connexion');

		$this->page->breadcrumb->reset();

		$this->page->display_menu = false;

		return $this->render('Connection/ForgetPassword', array(
			'bPasswordUpdated' => $bPasswordUpdated,
			'bPasswordSended' => $bPasswordSended
		));
	}
}
