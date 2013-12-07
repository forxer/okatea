<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin;

use Tao\Misc\Pager as BasePager;

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

	public function __construct($env,$nb_elements,$nb_per_page=10,$nb_pages_per_group=10)
	{
		parent::__construct($env,$nb_elements,$nb_per_page,$nb_pages_per_group);

		$this->html_prev = '&#171;&nbsp;'.__('c_c_previous_f');
		$this->html_next = __('c_c_next_f').'&nbsp;&#187;';
	}

} # class
