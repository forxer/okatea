<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->add(
	'start',
	new Route('/', array('_controller' => 'Okatea\Install\Controller\Start::page'))
);
$collection->add(
	'merge_config',
	new Route('/merge-config', array('_controller' => 'Okatea\Install\Controller\MergeConfig::page'))
);
$collection->add(
	'checks',
	new Route('/checks-requirements', array('_controller' => 'Okatea\Install\Controller\Checks::page'))
);
$collection->add(
	'db_conf',
	new Route('/database-configuration', array('_controller' => 'Okatea\Install\Controller\DatabaseConfiguration::page'))
);
$collection->add(
	'database',
	new Route('/database', array('_controller' => 'Okatea\Install\Controller\Database::page'))
);
$collection->add(
	'supa',
	new Route('/users', array('_controller' => 'Okatea\Install\Controller\Supa::page'))
);
$collection->add(
	'config',
	new Route('/configuration', array('_controller' => 'Okatea\Install\Controller\Config::page'))
);
$collection->add(
	'theme',
	new Route('/theme', array('_controller' => 'Okatea\Install\Controller\Theme::page'))
);
$collection->add(
	'colors',
	new Route('/colors', array('_controller' => 'Okatea\Install\Controller\Colors::page'))
);
$collection->add(
	'modules',
	new Route('/modules', array('_controller' => 'Okatea\Install\Controller\Modules::page'))
);
$collection->add(
	'pages',
	new Route('/pages', array('_controller' => 'Okatea\Install\Controller\Pages::page'))
);
$collection->add(
	'end',
	new Route('/end', array('_controller' => 'Okatea\Install\Controller\End::page'))
);

return $collection;