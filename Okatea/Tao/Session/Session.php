<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Session;

use Symfony\Component\HttpFoundation\Session\Session as BaseSession;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends BaseSession
{
	/**
	 * The namespace for the session variable and form inputs.
	 *
	 * @var string
	 */
	protected $sTokenNamespace;

	public function __construct(SessionStorageInterface $storage = null, $attributes = null, $flashes = null, $sTokenNamespace = 'okt_csrf')
	{
		parent::__construct($storage, $attributes, $flashes);

		$this->storage->setOptions([
			'use_trans_sid' => '0',
			'use_only_cookies' => '1'
		]);

		$this->start();

		$this->sTokenNamespace = $sTokenNamespace;

		$this->setToken();
	}

	/**
	 * Return the token from session.
	 *
	 * @return string
	 */
	public function getToken()
	{
		return $this->get($this->sTokenNamespace, '');
	}

	/**
	 * Verify if supplied token matches the stored token.
	 *
	 * @param string $userToken
	 * @return boolean
	 */
	public function isValidToken($userToken)
	{
		$bIsValid = ($userToken === $this->getToken());

		$this->generateToken();

		return $bIsValid;
	}

	/**
	 * Return the HTML input field with the token, and namespace
	 * as the name of the field
	 *
	 * @return string
	 */
	public function getTokenInputField()
	{
		return '<input type="hidden" name="' . $this->sTokenNamespace . '" value="' . htmlspecialchars($this->getToken()) . '" />';
	}

	/**
	 * Generates a new token value and stores it in session, or else
	 * does nothing if one already exists in session.
	 *
	 * @return void
	 */
	protected function setToken()
	{
		$storedToken = $this->getToken();

		if ($storedToken === '') {
			$this->generateToken();
		}
	}

	/**
	 * Remove token.
	 *
	 * @return void
	 */
	protected function removeToken()
	{
		$this->remove($this->sTokenNamespace);
	}

	protected function generateToken()
	{
		$sToken = sha1(uniqid(mt_rand(), true));

		$this->set($this->sTokenNamespace, $sToken);
	}
}
