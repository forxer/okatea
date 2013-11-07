<?php

class oktNavigationsItemsRecordset extends recordset
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

} # class
