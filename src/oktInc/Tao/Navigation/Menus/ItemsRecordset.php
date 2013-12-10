<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Navigation\Menus;

use Tao\Database\Recordset;

/**
 * Extension du Recordset pour les éléments de menu.
 *
 */
class ItemsRecordset extends Recordset
{

	/**
	 * L'objet oktCore
	 * @access private
	 * @var object
	 */
	private $okt;

	/**
	 * Défini l'objet de type oktCore qui sera passé à la classe après
	 * qu'elle ait été instanciée.
	 *
	 * @param oktCore okt 	Objet de type core
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
		if ($this->isExternal()) {
			return $this->url;
		}
		else {
			return $this->okt->page->getBaseUrl().$this->url;
		}
	}

}
