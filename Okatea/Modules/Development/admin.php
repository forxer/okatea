<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	# title tag
$okt->page->addTitleTag(__('Development'));

# fil d'ariane
$okt->page->addAriane(__('Development'), 'module.php?m=development');

# inclusion du fichier requis en fonction de l'action demandée
if ((! $okt->page->action || $okt->page->action === 'index'))
{
	require __DIR__ . '/admin/index.php';
}
elseif ($okt->page->action === 'debug_bar' && $okt['visitor']->checkPerm('development_debug_bar'))
{
	require __DIR__ . '/admin/debug_bar.php';
}
elseif ($okt->page->action === 'bootstrap' && $okt['visitor']->checkPerm('development_bootstrap'))
{
	require __DIR__ . '/admin/bootstrap.php';
}
elseif ($okt->page->action === 'counting' && $okt['visitor']->checkPerm('development_counting'))
{
	require __DIR__ . '/admin/counting.php';
}
else
{
	http::redirect('index.php');
}
