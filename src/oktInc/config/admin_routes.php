<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$adminCollection = new RouteCollection();

# admin home page (will become dashboard ;)
$adminCollection->add('home',
	new Route('/', array('_controller' => 'Tao\Admin\Controller\Home::homePage'))
);

# login / logout / forget password
$adminCollection->add('login',
	new Route('/login', array('_controller' => 'Tao\Admin\Controller\Connexion::login'))
);
$adminCollection->add('logout',
	new Route('/logout', array('_controller' => 'Tao\Admin\Controller\Connexion::logout'))
);
$adminCollection->add('forget_password',
	new Route('/forget-password', array('_controller' => 'Tao\Admin\Controller\Connexion::forget_password'))
);

# configuration
$configCollection = new RouteCollection();

$configCollection->add('config_general',
	new Route('/general', array('_controller' => 'Tao\Admin\Controller\Config\General::page'))
);
$configCollection->add('config_display',
	new Route('/display', array('_controller' => 'Tao\Admin\Controller\Config\Display::page'))
);
$configCollection->add('config_languages',
	new Route('/languages', array('_controller' => 'Tao\Admin\Controller\Config\Languages::page'))
);
$configCollection->add('config_tools',
	new Route('/tools', array('_controller' => 'Tao\Admin\Controller\Config\Tools::page'))
);
$configCollection->add('config_infos',
	new Route('/infos', array('_controller' => 'Tao\Admin\Controller\Config\Infos::page'))
);
$configCollection->add('config_update',
	new Route('/update', array('_controller' => 'Tao\Admin\Controller\Config\Update::page'))
);
$configCollection->add('config_logadmin',
	new Route('/logadmin', array('_controller' => 'Tao\Admin\Controller\Config\Logadmin::page'))
);
$configCollection->add('config_router',
	new Route('/router', array('_controller' => 'Tao\Admin\Controller\Config\Router::page'))
);
$configCollection->add('config_advanced',
	new Route('/advanced', array('_controller' => 'Tao\Admin\Controller\Config\Advanced::page'))
);



$configCollection->addPrefix('/configuration');
$adminCollection->addCollection($configCollection);



return $adminCollection;
