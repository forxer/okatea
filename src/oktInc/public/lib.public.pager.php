<?php
/**
 * Extension de la classe pager pour le coté publique.
 *
 * @addtogroup Okatea
 *
 */


class publicPager extends pager
{
	public $html_item		= '<li>%s</li>';
	public $html_cur_page	= '<li class="active">%s</li>';
	public $html_link_sep	= '';
	public $html_prev		= '&#171;&nbsp;précédent';
	public $html_next		= 'suivant&nbsp;&#187;';
	public $html_prev_grp	= '…';
	public $html_next_grp	= '…';

	public function __construct($env,$nb_elements,$nb_per_page=10,$nb_pages_per_group=10)
	{
		parent::__construct($env,$nb_elements,$nb_per_page,$nb_pages_per_group);

		$this->html_prev = '&#171;&nbsp;'.__('c_c_previous_f');
		$this->html_next = __('c_c_next_f').'&nbsp;&#187;';
	}

} # class
