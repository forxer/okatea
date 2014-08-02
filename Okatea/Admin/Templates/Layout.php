<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Misc\Utilities;

# Init and get user bars
$aUserBars = $okt->page->getUserBars();

# -- CORE TRIGGER : adminBeforeSendHeader
$okt->triggers->callTrigger('adminBeforeSendHeader');

?>
<!DOCTYPE html>
<html class="" lang="<?php echo $okt->user->language ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
	<title><?php echo $view->escape($okt->page->titleTag(' - ')) ?></title>
	<?php echo $okt->page->css?>
	<!--[if lt IE 9]><script type="text/javascript" src="<?php echo $okt['public_url'] ?>/components/html5shiv/dist/html5shiv.js"></script><![endif]-->
</head>
<body <?php if ($okt->page->hasPageId()) : ?>id="adminpage-<?php echo $okt->page->getPageId() ?>" <?php endif; ?>>
	<div id="page">
		<header>
			<p id="access-link">
				<a href="#main-<?php echo $okt['config']->admin_menu_position ?>"><?php _e('c_c_go_to_content') ?></a>
				- <a href="#mainMenu-<?php echo $okt['config']->admin_menu_position ?>"><?php _e('c_c_go_to_menu') ?></a>
			</p>
			<div id="banner" class="ui-widget-header ui-corner-all">
				<h1><?php echo $view->escape($okt->page->getSiteTitle()) ?></h1>
				<p id="desc"><?php echo $view->escape($okt->page->getSiteDescription()) ?></p>
			</div><!-- #banner -->

			<?php if ($okt['config']->admin_menu_position == 'top') : ?>
			<nav><?php echo $okt->page->getMainMenuHtml(); ?></nav>
			<?php endif; ?>

			<div id="helpers" class="ui-widget-content ui-corner-all">
				<div id="messages">
					<h2 id="breadcrumb"><?php echo $okt->page->breadcrumb->getBreadcrumb('<span class="ui-icon ui-icon-carat-1-e" style="display:inline-block;vertical-align: bottom;"></span> %s') ?></h2>

					<?php # affichage des éventuels messages d'erreurs
					if ($okt['flash']->hasError()) :
						echo $view->render('Common/Messages', [
							'type'        => Okatea\Tao\Session\FlashMessages::TYPE_ERROR,
							'messages'    => $okt['flash']->getError()
						]);
					endif; ?>

					<?php # affichage des éventuels messages d'avertissements
					if ($okt['flash']->hasWarning()) :
						echo $view->render('Common/Messages', [
							'type'        => Okatea\Tao\Session\FlashMessages::TYPE_WARNING,
							'messages'    => $okt['flash']->getWarning()
						]);
					endif; ?>

					<?php # affichage des éventuels messages de confirmation
					if ($okt['flash']->hasSuccess()) :
						echo $view->render('Common/Messages', [
							'type'        => Okatea\Tao\Session\FlashMessages::TYPE_SUCCESS,
							'messages'    => $okt['flash']->getSuccess()
						]);
					endif; ?>

					<?php # affichage des éventuels messages d'information
					if ($okt['flash']->hasInfo()) :
						echo $view->render('Common/Messages', [
							'type'        => Okatea\Tao\Session\FlashMessages::TYPE_INFO,
							'messages'    => $okt['flash']->getInfo()
						]);
					endif; ?>
				</div><!-- #messages -->
				<div id="welcome">
					<?php if (!empty($aUserBars['first'])) : ?><p><?php echo implode(' - ', $aUserBars['first']) ?></p><?php endif; ?>
					<?php if (!empty($aUserBars['second'])) : ?><p><?php echo implode(' - ', $aUserBars['second']) ?></p><?php endif; ?>
				</div><!-- #welcome -->
			</div><!-- #helpers -->
		</header>

		<div id="main-<?php echo $okt['config']->admin_menu_position ?>">
			<section id="content" class="ui-widget-content">
				<?php $view['slots']->output('_content'); ?>
			</section><!-- #content -->
		</div><!-- #main -->

<?php if ($okt['config']->admin_menu_position != 'top') : ?>
<nav><?php echo $okt->page->getMainMenuHtml(); ?></nav>
<?php endif; ?>

<?php # init footer content
$aFooterContent = new ArrayObject();

$sSoftware = $okt['software_name'];
if (!empty($okt['software_url']))
{
	$sSoftware = '<a href="' . $okt['software_url'] . '">' . $okt['software_name'] . '</a>';
}

$aFooterContent[10] = sprintf(__('c_c_proudly_propulsed_%s'), $sSoftware);

if ($okt['debug'])
{
	$aFooterContent[20] = ' - ' . sprintf(__('c_c_version_%s'), $okt->getVersion()) . ' - ' . sprintf(__('c_c_env_%s'), __('c_c_env_' . $okt['env'])) . ' - ' . Utilities::formatNumber(Utilities::getExecutionTime(), 3) . ' s - ' . Utilities::l10nFileSize(memory_get_usage());
}

# -- CORE TRIGGER : adminFooterContent
$okt->triggers->callTrigger('adminFooterContent', $aFooterContent);

# sort items of footer content
$aFooterContent->ksort();

# remove empty values of footer content
$aFooterContent = array_filter((array) $aFooterContent);

?>
	<footer>
		<p id="footer" class="clearb ui-widget ui-corner-all ui-state-default">
			<img src="<?php echo $okt['public_url'] ?>/img/ajax-loader/big-circle-ball.gif" alt="" class="preload" />
			<?php echo implode('&nbsp;', $aFooterContent) ?>
		</p>
	</footer>
</div><!-- #page -->

<?php echo $okt->page->js ?>

<?php # -- CORE TRIGGER : adminBeforeHtmlBodyEndTag
$okt->triggers->callTrigger('adminBeforeHtmlBodyEndTag'); ?>
</body>
</html>
