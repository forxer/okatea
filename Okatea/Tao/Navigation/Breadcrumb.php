<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Navigation;

use Okatea\Tao\Html\Escaper;

/**
 * @class breadcrumb
 * @ingroup okt_classes_html
 * @brief Fil d'ariane
 *
 */
class Breadcrumb
{
	/**
	 * Pile d'éléments
	 * @var array
	 */
	protected $stack;

	/**
	 * Nombre d'éléments
	 * @var array
	 */
	protected $iNum;

	/**
	 * Format du bloc HTML
	 * @var string
	 */
	protected $htmlBlock='<p class="breadcrumb">%s</p>';

	/**
	 * Format d'un élément
	 * @var string
	 */
	protected $htmlItem='%s';

	/**
	 * Format d'un lien
	 * @var string
	 */
	protected $htmlLink='<a href="%s">%s</a>';

	/**
	 * Séparateur d'éléments
	 * @var string
	 */
	protected $htmlSeparator=' &rsaquo; ';


	public function __construct()
	{
		$this->reset();
	}

	public function reset()
	{
		$this->stack = array();
		$this->iNum = 0;
	}

	public function add($label, $url='')
	{
		$this->stack[] = array(
			'label' => $label,
			'url' => $url
		);

		++$this->iNum;
	}

	public function getCurrent()
	{
		return isset($this->stack[$this->iNum]) ? $this->stack[$this->iNum] : null;
	}

	public function getPrevious()
	{
		return isset($this->stack[$this->iNum-1]) ? $this->stack[$this->iNum-1] : null;
	}

	public function setHtmlBlock($str)
	{
		$this->htmlBlock = $str;
	}

	public function setHtmlItem($str)
	{
		$this->htmlItem = $str;
	}

	public function setHtmlLink($str)
	{
		$this->htmlLink = $str;
	}

	public function setHtmlSeparator($str)
	{
		$this->htmlSeparator = $str;
	}

	public function display($htmlBlock=null, $htmlItem=null, $htmlLink=null, $htmlSeparator=null)
	{
		if ($htmlBlock) {
			$this->htmlBlock = $htmlBlock;
		}

		if ($htmlItem) {
			$this->htmlItem = $htmlItem;
		}

		if ($htmlLink) {
			$this->htmlLink = $htmlLink;
		}

		if ($htmlSeparator) {
			$this->htmlSeparator = $htmlSeparator;
		}

		echo $this->getBreadcrumb();
	}

	public function getBreadcrumb()
	{
		return $this->buildBreadcrumb();
	}

	protected function buildBreadcrumb()
	{
		$res = array();

		for ($i=0; $i<$this->iNum; $i++)
		{
			if (!isset($this->stack[$i]['url']) || $i == $this->iNum-1)
			{
				$res[] = sprintf($this->htmlItem, Escaper::html($this->stack[$i]['label']));
			}
			else {
				$res[] = sprintf($this->htmlItem, sprintf($this->htmlLink, Escaper::html($this->stack[$i]['url']), Escaper::html($this->stack[$i]['label'])));
			}
		}

		if (!empty($res)) {
			return sprintf($this->htmlBlock, implode($this->htmlSeparator,$res));
		}
		else {
			return null;
		}
	}
}
