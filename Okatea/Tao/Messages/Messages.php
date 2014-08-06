<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Messages;

use Okatea\Tao\Application;

class Messages
{
	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	public function __construct(Application $okt)
	{
		$this->okt = $okt;
	}

	public function getInfo()
	{
		return array_merge($this->okt['instantMessages']->getInfo(), $this->okt['flashMessages']->getInfo());
	}

	public function hasInfo()
	{
		return $this->okt['instantMessages']->hasInfo() || $this->okt['flashMessages']->hasInfo();
	}

	public function getSuccess()
	{
		return array_merge($this->okt['instantMessages']->getSuccess(), $this->okt['flashMessages']->getSuccess());
	}

	public function hasSuccess()
	{
		return $this->okt['instantMessages']->hasSuccess() || $this->okt['flashMessages']->hasSuccess();
	}

	public function getWarning()
	{
		return array_merge($this->okt['instantMessages']->getWarning(), $this->okt['flashMessages']->getWarning());
	}

	public function hasWarning()
	{
		return $this->okt['instantMessages']->hasWarning() || $this->okt['flashMessages']->hasWarning();
	}

	public function getError()
	{
		return array_merge($this->okt['instantMessages']->getError(), $this->okt['flashMessages']->getError());
	}

	public function hasError()
	{
		return $this->okt['instantMessages']->hasError() || $this->okt['flashMessages']->hasError();
	}
}
