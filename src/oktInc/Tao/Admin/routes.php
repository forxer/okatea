<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$rootCollection = new RouteCollection();

# login / logout / forget password
$rootCollection->add('login',
	new Route('/login', array('_controller' => 'Tao\Admin\Controller\Connexion::login'))
);
$rootCollection->add('logout',
	new Route('/logout', array('_controller' => 'Tao\Admin\Controller\Connexion::logout'))
);
$rootCollection->add('forget_password',
	new Route('/forget-password', array('_controller' => 'Tao\Admin\Controller\Connexion::forget_password'))
);

# home page
$rootCollection->add('home',
	new Route('/', array('_controller' => 'Tao\Admin\Controller\Home::homePage'))
);

# configuration
$configCollection = new RouteCollection();

$configCollection->add('config_general',
	new Route('/general', array('_controller' => 'Tao\Admin\Controller\Config\General::page'))
);
$configCollection->add('config_display',
	new Route('/display', array('_controller' => 'Tao\Admin\Controller\Config\Display::page'))
);
$configCollection->add('config_logadmin',
	new Route('/logadmin', array('_controller' => 'Tao\Admin\Controller\Config\Logadmin::page'))
);


$configCollection->addPrefix('/configuration');
$rootCollection->addCollection($configCollection);




return $rootCollection;
