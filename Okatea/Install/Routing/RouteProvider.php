<?php
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->add('start', new Route('/', array(
	'controller' => 'Okatea\Install\Controller\Start::page'
)));
$collection->add('merge_config', new Route('/merge-config', array(
	'controller' => 'Okatea\Install\Controller\MergeConfig::page'
)));
$collection->add('checks', new Route('/checks-requirements', array(
	'controller' => 'Okatea\Install\Controller\Checks::page'
)));
$collection->add('db_conf', new Route('/database-configuration', array(
	'controller' => 'Okatea\Install\Controller\DatabaseConfiguration::page'
)));
$collection->add('database', new Route('/database', array(
	'controller' => 'Okatea\Install\Controller\Database::page'
)));
$collection->add('supa', new Route('/users', array(
	'controller' => 'Okatea\Install\Controller\Supa::page'
)));
$collection->add('end', new Route('/end', array(
	'controller' => 'Okatea\Install\Controller\End::page'
)));

return $collection;
