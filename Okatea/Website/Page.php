<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Website;

use Okatea\Tao\Controller;
use Okatea\Tao\Html\Page as BasePage;
use Okatea\Tao\Navigation\Breadcrumb;

/**
 * Construction des pages publiques.
 *
 * @addtogroup Okatea
 */
class Page extends BasePage
{

	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($okt)
	{
		parent::__construct($okt, 'public');
		
		$this->breadcrumb->add(__('c_c_Home'), $this->getBaseUrl());
	}

	public function serve404()
	{
		$this->okt['request']->attributes->set('controller', 'Okatea\Website\Controller::serve404');
	}

	public function serve503()
	{
		$this->okt['request']->attributes->set('controller', 'Okatea\Website\Controller::serve503');
	}
}
