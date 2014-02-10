<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin;

use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Pager as BasePager;

/**
 * Extension de la classe pager pour l'administration.
 *
 */
class Pager extends BasePager
{
	public $html_item		= '<li class="ui-state-default">%s</li>';
	public $html_cur_page	= '<li class="active ui-state-active">%s</li>';
	public $html_link_sep	= '';
	public $html_prev		= '&#171;&nbsp;précédent';
	public $html_next		= 'suivant&nbsp;&#187;';
	public $html_prev_grp	= '…';
	public $html_next_grp	= '…';
	public $html_link_class = '';

	public function __construct($okt, $env, $nb_elements, $nb_per_page=10, $nb_pages_per_group=10)
	{
		parent::__construct($okt, $env, $nb_elements, $nb_per_page, $nb_pages_per_group);

		$this->html_prev = '&#171;&nbsp;'.__('c_c_previous_f');
		$this->html_next = __('c_c_next_f').'&nbsp;&#187;';
	}

	protected function setURL()
	{
		if ($this->base_url !== null)
		{
			$this->page_url = $this->base_url;
			return;
		}

		$url = $this->okt->request->getBasePath().$this->okt->request->getPathInfo();

		# Escape page_url for sprintf
		$url = preg_replace('/%/','%%',$url);

		# Changing page ref
		if (preg_match('#/[0-9]+$#',$url)) {
			$url = preg_replace('#(/)[0-9]+#','$1%1$d',$url);
		}
		else {
			$url .= '/%1$d';
		}

		return Escaper::html($url);
	}
}
