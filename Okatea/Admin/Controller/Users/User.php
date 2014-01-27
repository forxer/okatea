<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Users\Users;

class User extends Controller
{
	protected $aPageData;

	public function profile()
	{
		$this->init();

		$this->aPageData['user'] = array(
			'id'                 => $this->okt->user->id,
			'civility'           => $this->okt->user->civility,
			'status'             => $this->okt->user->status,
			'username'           => $this->okt->user->username,
			'lastname'           => $this->okt->user->lastname,
			'firstname'          => $this->okt->user->firstname,
			'displayname'        => $this->okt->user->displayname,
			'password'           => '',
			'password_confirm'   => '',
			'email'              => $this->okt->user->email,
			'timezone'           => $this->okt->user->timezone,
			'language'           => $this->okt->user->language
		);

		return $this->render('Users/User/Profile', array(
			'userData'       => $this->aPageData['user'],
			'aLanguages'     => $this->getLanguages(),
			'aCivilities'    => $this->getCivilities()
		));
	}

	public function add()
	{
		if (!$this->okt->checkPerm('users')) {
			return $this->serve401();
		}

		$this->init();

		return $this->render('Users/User/Add', array(
			'userData'       => $this->aPageData['user'],
			'aLanguages'     => $this->getLanguages(),
			'aCivilities'    => $this->getCivilities()
		));
	}

	public function edit()
	{
		if (!$this->okt->checkPerm('users_edit')) {
			return $this->serve401();
		}

		$this->init();

		return $this->render('Users/User/Edit', array(
			'userData'       => $this->aPageData['user'],
			'aLanguages'     => $this->getLanguages(),
			'aCivilities'    => $this->getCivilities()
		));
	}

	protected function init()
	{
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/'.$this->okt->user->language.'/admin/users');

		$this->aPageData = new \ArrayObject();

		$this->aPageData['user'] = array(
			'id'                 => null,
			'civility'           => 0,
			'status'             => 1,
			'username'           => '',
			'lastname'           => '',
			'firstname'          => '',
			'displayname'        => '',
			'password'           => '',
			'password_confirm'   => '',
			'email'              => '',
			'timezone'           => $this->okt->config->timezone,
			'language'           => $this->okt->config->language
		);
	}

	protected function getLanguages()
	{
		$rsLanguages = $this->okt->languages->getLanguages();
		$aLanguages = array();
		while ($rsLanguages->fetch()) {
			$aLanguages[Utilities::escapeHTML($rsLanguages->title)] = $rsLanguages->code;
		}

		return $aLanguages;
	}

	protected function getCivilities()
	{
		return array_merge(
			array(' ' => 0),
			Users::getCivilities(true)
		);
	}
}
