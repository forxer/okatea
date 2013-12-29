<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('home',
	new Route('/', array('_controller' => 'Tao\Admin\Controller\Home::homePage'))
);

$collection->add('login',
	new Route('/connexion', array('_controller' => 'Tao\Admin\Controller\Connexion::login'))
);

$collection->add('config_site',
	new Route('/configuration/site', array('_controller' => 'Tao\Admin\Controller\Configuration::site'))
);
$collection->add('config_display',
	new Route('/configuration/display', array('_controller' => 'Tao\Admin\Controller\Configuration::display'))
);


return $collection;
