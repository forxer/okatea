<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

# -- CORE TRIGGER : installBeforeSendHtml
$okt['triggers']->callTrigger('installBeforeSendHtml');

?>
<!DOCTYPE html>
<html class=""
	lang="<?php echo $okt['session']->get('okt_install_language') ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />

<title><?php _e('i_'.$okt['session']->get('okt_install_process_type').'_interface') ?> - Okatea <?php if ($okt->getVersion()) { echo $okt->getVersion(); } ?></title>

	<?php echo $okt->page->css?>
</head>

<body>
	<div id="page">
		<header>
			<div id="banner" class="ui-widget-header ui-corner-all">
				<h1>
					Okatea <span class="version"><?php if ($okt->getVersion()) { echo $okt->getVersion(); } ?></span>
				</h1>
				<p id="desc"><?php _e('i_'.$okt['session']->get('okt_install_process_type').'_interface') ?></p>
			</div>
			<!-- #banner -->

		<?php echo $okt->stepper->display()?>
	</header>
		<div id="main" class="ui-widget">

			<h2 id="page-title" class="ui-widget-header ui-corner-top"><?php if (!empty($title)) { echo $title; } else { _e('i_'.$okt['session']->get('okt_install_process_type').'_interface'); }?></h2>

			<section id="content" class="ui-widget-content ui-corner-bottom">

			<?php # affichage des éventuels messages d'erreurs
			if ($okt['messages']->hasError()) :
				echo $view->render('Messages', [
					'type'        => Okatea\Tao\Messages\MessagesInterface::TYPE_ERROR,
					'messages'    => $okt['messages']->getError()
				]);
			endif; ?>

			<?php # affichage des éventuels messages d'avertissements
			if ($okt['messages']->hasWarning()) :
				echo $view->render('Messages', [
					'type'        => Okatea\Tao\Messages\MessagesInterface::TYPE_WARNING,
					'messages'    => $okt['messages']->getWarning()
				]);
			endif; ?>

			<?php # affichage des éventuels messages de confirmation
			if ($okt['messages']->hasSuccess()) :
				echo $view->render('Messages', [
					'type'        => Okatea\Tao\Messages\MessagesInterface::TYPE_SUCCESS,
					'messages'    => $okt['messages']->getSuccess()
				]);
			endif; ?>

			<?php # affichage des éventuels messages d'information
			if ($okt['messages']->hasInfo()) :
				echo $view->render('Messages', [
					'type'        => Okatea\Tao\Messages\MessagesInterface::TYPE_INFO,
					'messages'    => $okt['messages']->getInfo()
				]);
			endif; ?>

			<?php $view['slots']->output('_content'); ?>

		</section><!-- #content -->

			<footer>
				<p id="footer" class="clearb ui-widget ui-corner-all ui-state-default">
					Okatea<?php if ($okt->getVersion()) { echo ' version <strong>'.$okt->getVersion().'</strong> '; } ?>
				</p><!-- #footer -->
			</footer>
		</div><!-- #main -->
	</div><!-- #page -->

<?php echo $okt->page->js?>

<?php
# -- CORE TRIGGER : installBeforeHtmlBodyEndTag
$okt['triggers']->callTrigger('installBeforeHtmlBodyEndTag'); ?>
</body>
</html>
