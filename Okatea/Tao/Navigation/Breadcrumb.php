<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Navigation;

use Okatea\Tao\Html\Escaper;

class Breadcrumb
{

	/**
	 * Pile d'éléments
	 *
	 * @var array
	 */
	protected $stack;

	/**
	 * Nombre d'éléments
	 *
	 * @var array
	 */
	protected $iNum;

	/**
	 * Format du bloc HTML
	 *
	 * @var string
	 */
	protected $sHtmlBlock = '<p class="breadcrumb">%s</p>';

	/**
	 * Format d'un élément
	 *
	 * @var string
	 */
	protected $sHtmlItem = '%s';

	/**
	 * Format d'un lien
	 *
	 * @var string
	 */
	protected $sHtmlLink = '<a href="%s">%s</a>';

	/**
	 * Séparateur d'éléments
	 *
	 * @var string
	 */
	protected $sHtmlSeparator = ' &rsaquo; ';

	public function __construct()
	{
		$this->reset();
	}

	public function reset()
	{
		$this->stack = array();
		$this->iNum = 0;
	}

	public function add($label, $url = null)
	{
		$this->stack[] = array(
			'label' => $label,
			'url' => $url
		);
		
		++ $this->iNum;
	}

	public function getCurrent()
	{
		return isset($this->stack[$this->iNum]) ? $this->stack[$this->iNum] : null;
	}

	public function getPrevious()
	{
		return isset($this->stack[$this->iNum - 1]) ? $this->stack[$this->iNum - 1] : null;
	}

	public function setHtmlBlock($sHtmlBlock = null)
	{
		if (null === $sHtmlBlock)
		{
			return false;
		}
		
		$this->sHtmlBlock = $sHtmlBlock;
	}

	public function setHtmlItem($sHtmlItem = null)
	{
		if (null === $sHtmlItem)
		{
			return false;
		}
		
		$this->sHtmlItem = $sHtmlItem;
	}

	public function setHtmlLink($sHtmlLink = null)
	{
		if (null === $sHtmlLink)
		{
			return false;
		}
		
		$this->sHtmlLink = $sHtmlLink;
	}

	public function setHtmlSeparator($sHtmlSeparator = null)
	{
		if (null === $sHtmlSeparator)
		{
			return false;
		}
		
		$this->sHtmlSeparator = $sHtmlSeparator;
	}

	public function getBreadcrumb($sHtmlBlock = null, $sHtmlItem = null, $sHtmlLink = null, $sHtmlSeparator = null)
	{
		$this->setHtmlBlock($sHtmlBlock);
		
		$this->setHtmlItem($sHtmlItem);
		
		$this->setHtmlLink($sHtmlLink);
		
		$this->setHtmlSeparator($sHtmlSeparator);
		
		return $this->buildBreadcrumb();
	}

	protected function buildBreadcrumb()
	{
		if (null === $this->iNum || $this->iNum <= 0)
		{
			return null;
		}
		
		$res = array();
		
		for ($i = 0; $i < $this->iNum; $i ++)
		{
			if (empty($this->stack[$i]['url']) || $i === $this->iNum - 1)
			{
				$res[] = sprintf($this->sHtmlItem, Escaper::html($this->stack[$i]['label']));
			}
			else
			{
				$res[] = sprintf($this->sHtmlItem, sprintf($this->sHtmlLink, Escaper::html($this->stack[$i]['url']), Escaper::html($this->stack[$i]['label'])));
			}
		}
		
		if (empty($res))
		{
			return null;
		}
		
		return sprintf($this->sHtmlBlock, implode($this->sHtmlSeparator, $res));
	}
}
