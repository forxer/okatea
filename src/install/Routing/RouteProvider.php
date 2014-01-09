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

return $collection;