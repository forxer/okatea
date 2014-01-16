<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Misc;

/**
 * Classe d'affichage de données sur plusieurs pages
 *
 */
class Pager
{
	protected $okt;

	protected $env;
	protected $nb_elements;
	protected $nb_per_page;
	protected $nb_pages_per_group;
	protected $nb_pages;
	protected $nb_groups;

	protected $env_group;
	protected $index_group_start;
	protected $index_group_end;
	protected $page_url;

	public $index_start;
	public $index_end;

	public $base_url;
	public $var_page = 'page';

	public $html_item		= '%s';
	public $html_cur_page	= '<strong>%s</strong>';
	public $html_link_sep	= '-';
	public $html_prev		= '&#171;prev.';
	public $html_next		= 'next&#187;';
	public $html_prev_grp	= '…';
	public $html_next_grp	= '…';
	public $html_link_class	= '';

	public function __construct($okt, $env, $nb_elements, $nb_per_page=10, $nb_pages_per_group=10)
	{
		$this->okt = $okt;

		$this->env = abs((integer) $env);
		$this->nb_elements = abs((integer) $nb_elements);
		$this->nb_per_page = abs((integer) $nb_per_page);
		$this->nb_pages_per_group = abs((integer) $nb_pages_per_group);

		# Pages count
		$this->nb_pages = ceil($this->nb_elements/$this->nb_per_page);

		# Fix env value
		if ($this->env > $this->nb_pages || $this->env < 1) {
			$this->env = 1;
		}

		# Groups count
		$this->nb_groups = (integer) ceil($this->nb_pages/$this->nb_pages_per_group);

		# Page first element index
		$this->index_start = ($this->env-1)*$this->nb_per_page;

		# Page last element index
		$this->index_end = $this->index_start+$this->nb_per_page-1;
		if ($this->index_end >= $this->nb_elements)
			$this->index_end = $this->nb_elements-1;

		# Current group
		$this->env_group = (integer) ceil($this->env/$this->nb_pages_per_group);

		# Group first page index
		$this->index_group_start = ($this->env_group-1)*$this->nb_pages_per_group+1;

		# Group last page index
		$this->index_group_end = $this->index_group_start+$this->nb_pages_per_group-1;
		if ($this->index_group_end > $this->nb_pages)
			$this->index_group_end = $this->nb_pages;
	}

	public function getLinks()
	{
		$htmlLinks = '';
		$htmlPrev = '';
		$htmlNext = '';
		$htmlPrevGrp = '';
		$htmlNextGrp = '';

		$this->page_url = $this->setURL();

		for ($i=$this->index_group_start; $i<=$this->index_group_end; $i++)
		{
			if ($i == $this->env) {
				$htmlLinks .= sprintf($this->html_cur_page,$i);
			}
			else {
				$htmlLinks .= sprintf($this->html_item,'<a'.($i == 1 ? ' rel="nofollow"' : '').
				' class="'.$this->html_link_class.'" href="'.sprintf($this->page_url,$i).'">'.$i.'</a>');
			}

			if ($i != $this->index_group_end) {
				$htmlLinks .= $this->html_link_sep;
			}
		}

		# Previous page
		if ($this->env != 1) {
			$htmlPrev = sprintf($this->html_item,'<a href="'.sprintf($this->page_url,$this->env-1).'"'.($this->env == 2 ? ' rel="nofollow"' : '').'>'.$this->html_prev.'</a>');
		}

		# Next page
		if ($this->env != $this->nb_pages) {
			$htmlNext = sprintf($this->html_item,'<a href="'.sprintf($this->page_url,$this->env+1).'">'.$this->html_next.'</a>');
		}

		# Previous group
		if ($this->env_group != 1) {
			$htmlPrevGrp = sprintf($this->html_item,'<a href="'.sprintf($this->page_url,$this->index_group_start - $this->nb_pages_per_group).'">'.$this->html_prev_grp.'</a>');
		}

		# Next group
		if ($this->env_group != $this->nb_groups) {
			$htmlNextGrp = sprintf($this->html_item,'<a href="'.sprintf($this->page_url,$this->index_group_end+1).'">'.$this->html_next_grp.'</a>');
		}

		$res =	$htmlPrev.
				$htmlPrevGrp.
				$htmlLinks.
				$htmlNextGrp.
				$htmlNext;

		return $this->nb_elements > 0 ? $res : '';
	}

	protected function setURL()
	{
		if ($this->base_url !== null)
		{
			$this->page_url = $this->base_url;
			return;
		}

		$url = $_SERVER['REQUEST_URI'];

		# Removing session information
		if (session_id())
		{
			$url = preg_replace('/'.preg_quote(session_name().'='.session_id(),'/').'([&]?)/','',$url);
			$url = preg_replace('/&$/','',$url);
		}

		# Escape page_url for sprintf
		$url = preg_replace('/%/','%%',$url);

		# Changing page ref
		if (preg_match('/[?&]'.$this->var_page.'=[0-9]+/',$url)) {
			$url = preg_replace('/([?&]'.$this->var_page.'=)[0-9]+/','$1%1$d',$url);
		} elseif (preg_match('/[?]/',$url)) {
			$url .= '&'.$this->var_page.'=%1$d';
		} else {
			$url .= '?'.$this->var_page.'=%1$d';
		}

		return \html::escapeHTML($url);
	}

	/**
	 * Retourne le nombre de pages dans l'objet courant
	 *
	 * @return integer Nombre de pages
	 */
	public function getNbPages()
	{
		return (integer)$this->nb_pages;
	}

	public function getNbElements()
	{
		return (integer)$this->nb_elements;
	}

	public function normalise(&$page)
	{
		if ($this->nb_pages < $page || $page < 1) {
			$page = 1;
		}
	}

	public function debug()
	{
		return
		"Elements per page ........... ".$this->nb_per_page.PHP_EOL.
		'Pages per group.............. '.$this->nb_pages_per_group.PHP_EOL.
		"Elements count .............. ".$this->nb_elements.PHP_EOL.
		'Pages ....................... '.$this->nb_pages.PHP_EOL.
		'Groups ...................... '.$this->nb_groups.PHP_EOL.PHP_EOL.
		'Current page .................'.$this->env.PHP_EOL.
		'Start index ................. '.$this->index_start.PHP_EOL.
		'End index ................... '.$this->index_end.PHP_EOL.
		'Current group ............... '.$this->env_group.PHP_EOL.
		'Group first page index ...... '.$this->index_group_start.PHP_EOL.
		'Group last page index ....... '.$this->index_group_end;
	}
}
