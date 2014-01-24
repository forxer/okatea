<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Controller;

use Okatea\Install\Controller;
use Okatea\Tao\Misc\Utilities;

class Supa extends Controller
{
	public function page()
	{
		$aUsersData = array(
			'sudo' => array(
				'username' 	=> '',
				'password' 	=> '',
				'email' 	=> ''
			),
			'admin' => array(
				'username' 	=> '',
				'password' 	=> '',
				'email' 	=> ''
			)
		);

		if ($this->request->request->has('sended'))
		{
			$aUsersData = array(
				'sudo' => array(
					'username' 	=> $this->request->request->get('sudo_username'),
					'password' 	=> $this->request->request->get('sudo_password'),
					'email' 	=> $this->request->request->get('sudo_email')
				),
				'admin' => array(
					'username' 	=> $this->request->request->get('admin_username'),
					'password' 	=> $this->request->request->get('admin_password'),
					'email' 	=> $this->request->request->get('admin_email')
				)
			);

			if (empty($aUsersData['sudo']['username'])) {
				$this->okt->error->set(__('i_supa_must_sudo_username'));
			}

			if (empty($aUsersData['sudo']['password'])) {
				$this->okt->error->set(__('i_supa_must_sudo_password'));
			}

			if (empty($aUsersData['sudo']['email'])) {
				$this->okt->error->set(__('i_supa_must_sudo_email'));
			}

			if (!empty($aUsersData['admin']['username']) || !empty($aUsersData['admin']['password']) || !empty($aUsersData['admin']['email']))
			{
				if (empty($aUsersData['admin']['username'])) {
					$this->okt->error->set(__('i_supa_must_admin_info'));
				}
				elseif (empty($aUsersData['admin']['password'])) {
					$this->okt->error->set(__('i_supa_must_admin_info'));
				}
				elseif (empty($aUsersData['admin']['email'])) {
					$this->okt->error->set(__('i_supa_must_admin_info'));
				}
			}

			# si pas d'erreur on ajoutent les utilisateurs
			if ($this->okt->error->isEmpty())
			{
				$db = $this->okt->getDb();

				$iCurrentTimestamp = time();

				# insertion invité id 1
				$query =
				'INSERT INTO `'.$db->prefix.'core_users` (`id`, `username`, `group_id`, `password`) '.
				'VALUES ( 1, \'Guest\', 3, \'Guest\' );';

				$db->query($query);


				# insertion superadmin (id 2)
				$query =
				'INSERT INTO `'.$db->prefix.'core_users` ('.
					'`id`, `username`, `group_id`, `salt`, `password`, `language`, `timezone`, `email`, `registered`, `last_visit`'.
				') VALUES ( '.
					'2, '.
					'\''.$db->escapeStr($aUsersData['sudo']['username']).'\', '.
					'1, '.
					'\''.$db->escapeStr(Utilities::random_key(12)).'\', '.
					'\''.$db->escapeStr(password_hash($aUsersData['sudo']['password'], PASSWORD_DEFAULT)).'\', '.
					'\'fr\', '.
					'\'Europe/Paris\', '.
					'\''.$db->escapeStr($aUsersData['sudo']['email']).'\', '.
					$iCurrentTimestamp.', '.
					$iCurrentTimestamp.' '.
				');';

				$db->query($query);

				$this->session->set('okt_install_sudo_user', $aUsersData['sudo']['username']);
				$this->session->set('okt_install_sudo_password', $aUsersData['sudo']['password']);
				$this->session->set('okt_install_sudo_email', $aUsersData['sudo']['password']);

				# insertion admin id 3
				if (!empty($aUsersData['admin']['username']) || !empty($aUsersData['admin']['password']) || !empty($aUsersData['admin']['email']))
				{
					$query =
					'INSERT INTO `'.$db->prefix.'core_users` ('.
						'`id`, `username`, `group_id`, `salt`, `password`, `language`, `timezone`, `email`, `registered`, `last_visit`'.
					') VALUES ( '.
						'3, '.
						'\''.$db->escapeStr($aUsersData['admin']['username']).'\', '.
						'2, '.
						'\''.$db->escapeStr(Utilities::random_key(12)).'\', '.
						'\''.$db->escapeStr(password_hash($aUsersData['admin']['password'], PASSWORD_DEFAULT)).'\', '.
						'\'fr\', '.
						'\'Europe/Paris\', '.
						'\''.$db->escapeStr($aUsersData['admin']['email']).'\', '.
						$iCurrentTimestamp.', '.
						$iCurrentTimestamp.' '.
					');';

					$db->query($query);
				}

				return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
			}
		}

		return $this->render('Supa', array(
			'title' => __('i_supa_title'),
			'aUsersData' => $aUsersData
		));
	}
}
