<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Navigation\Menus;

use Okatea\Tao\Database\Recordset;

/**
 * Extension du Recordset pour les éléments de menu.
 */
class ItemsRecordset extends Recordset
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Défini l'instance application qui sera passé à la classe après
	 * qu'elle ait été instanciée.
	 *
	 * @param Okatea\Tao\Application $okt
	 *        	Okatea application instance.
	 * @return void
	 */
	public function setCore($okt)
	{
		$this->okt = $okt;
	}

	public function isInternal()
	{
		return $this->type == 0;
	}

	public function isExternal()
	{
		return $this->type == 1;
	}

	public function getUrl()
	{
		if ($this->isExternal())
		{
			return $this->url;
		}
		else
		{
			return $this->okt->page->getBaseUrl() . $this->url;
		}
	}
}
