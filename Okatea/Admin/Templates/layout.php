<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Misc\Utilities;

# récupération des erreurs du core
if ($okt->error->notEmpty())
{
	foreach($okt->error->get(false) as $error) {
		$okt->page->errors->set($error['message']);
	}
}

# populates messages from flash messages queues
$okt->page->infos->setItems($okt->page->flash->get('infos'));
$okt->page->success->setItems($okt->page->flash->get('success'));
$okt->page->warnings->setItems($okt->page->flash->get('warnings'));
$okt->page->errors->setItems($okt->page->flash->get('errors'));

# Init and get user bars
$aUserBars = $okt->page->getUserBars();

# -- CORE TRIGGER : adminBeforeSendHeader
$okt->triggers->callTrigger('adminBeforeSendHeader');

?><!DOCTYPE html>
<html class="" lang="<?php echo $okt->user->language ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
	<title><?php echo $view->escape($okt->page->titleTag(' - ')) ?></title>
	<?php echo $okt->page->css ?>
	<!--[if lt IE 9]><script type="text/javascript" src="<?php echo $okt->options->public_url ?>/components/html5shiv/dist/html5shiv.js"></script><![endif]-->
</head>
<body<?php if ($okt->page->hasPageId()) : ?> id="adminpage-<?php echo $okt->page->getPageId() ?>"<?php endif; ?>>
<div id="page">
<header>
	<p id="access-link">
		<a href="#main-<?php echo ($okt->config->admin_sidebar_position == 0 ? 'right' : 'left') ?>"><?php _e('c_c_go_to_content') ?></a>
		-
		<a href="#mainMenu-<?php echo ($okt->config->admin_sidebar_position == 0 ? 'left' : 'right') ?>"><?php _e('c_c_go_to_menu') ?></a>
	</p>
	<div id="banner" class="ui-widget-header ui-corner-all">
		<h1><?php echo $view->escape($okt->page->getSiteTitle()) ?></h1>
		<p id="desc"><?php echo $view->escape($okt->page->getSiteDescription()) ?></p>
	</div><!-- #header -->

	<div id="helpers" class="ui-widget-content ui-corner-all">
		<div id="messages">

			<h2 id="breadcrumb"><?php $okt->page->breadcrumb->display('<span class="ui-icon ui-icon-carat-1-e" style="display:inline-block;vertical-align: bottom;"></span> %s') ?></h2>

			<?php # affichage des éventuels messages d'erreurs
			echo $okt->page->errors->getErrors('<div class="errors_box ui-corner-all">%s</div>'); ?>

			<?php # affichage des éventuels messages d'avertissements
			echo $okt->page->warnings->getWarnings('<div class="warnings_box ui-corner-all">%s</div>'); ?>

			<?php # affichage des éventuels messages de confirmation
			echo $okt->page->success->getSuccess('<div class="success_box ui-corner-all">%s</div>'); ?>

			<?php # affichage des éventuels messages d'information
			echo $okt->page->infos->getInfos('<div class="infos_box ui-corner-all">%s</div>'); ?>

		</div><!-- #messages -->
		<div id="welcome">
			<?php if (!empty($aUserBars['first'])) : ?><p><?php echo implode(' - ', $aUserBars['first']) ?></p><?php endif; ?>
			<?php if (!empty($aUserBars['second'])) : ?><p><?php echo implode(' - ', $aUserBars['second']) ?></p><?php endif; ?>
		</div><!-- #welcome -->
	</div><!-- #helpers -->
</header>

<div id="main-<?php echo ($okt->config->admin_sidebar_position == 0 ? 'right' : 'left') ?>">

	<section id="content" class="ui-widget-content">

	<?php $view['slots']->output('_content'); ?>

	</section><!-- #content -->
</div><!-- #main -->

<nav><?php echo $okt->page->getMainMenHtml(); ?></nav>

<?php # init footer content
$aFooterContent = new ArrayObject;

$aFooterContent[10] = sprintf(__('c_c_proudly_propulsed_%s'), '<a href="http://okatea.org/">Okatea</a>');

if ($okt->options->get('debug'))
{
	$aFooterContent[20] =
		' - version '.$okt->getVersion().' - '.
		Utilities::getExecutionTime().' s - '.
		Utilities::l10nFileSize(memory_get_usage()).
		' ('.Utilities::l10nFileSize(memory_get_peak_usage()).')';
}

# -- CORE TRIGGER : adminFooterContent
$okt->triggers->callTrigger('adminFooterContent', $aFooterContent);


# sort items of footer content
$aFooterContent->ksort();

# remove empty values of footer content
$aFooterContent = array_filter((array)$aFooterContent);

?>
<footer>
	<p id="footer" class="clearb ui-widget ui-corner-all ui-state-default">
	<img src="<?php echo $okt->options->public_url ?>/img/ajax-loader/big-circle-ball.gif" alt="" class="preload" />
	<?php echo implode('&nbsp;', $aFooterContent) ?></p>
</footer>
</div><!-- #page -->

<?php echo $okt->page->js ?>

<?php # -- CORE TRIGGER : adminBeforeHtmlBodyEndTag
$okt->triggers->callTrigger('adminBeforeHtmlBodyEndTag'); ?>
</body>
</html>
