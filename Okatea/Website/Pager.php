<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Website;

use Okatea\Tao\Misc\Pager as BasePager;
use Okatea\Tao\Misc\Utilities;

/**
 * Extension de la classe pager pour le coté publique.
 *
 * @addtogroup Okatea
 *
 */
class Pager extends BasePager
{
	public $html_item		= '<li>%s</li>';
	public $html_cur_page	= '<li class="active">%s</li>';
	public $html_link_sep	= '';
	public $html_prev		= '&#171;&nbsp;précédent';
	public $html_next		= 'suivant&nbsp;&#187;';
	public $html_prev_grp	= '…';
	public $html_next_grp	= '…';

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
		} else {
			$url .= '/%1$d';
		}

		return Utilities::escapeHTML($url);
	}
}
