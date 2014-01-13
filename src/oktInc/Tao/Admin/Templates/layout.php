<?php

# populates messages from flash messages queue
$okt->page->infos->setItems($okt->page->flash->get('infos'));
$okt->page->success->setItems($okt->page->flash->get('success'));
$okt->page->warnings->setItems($okt->page->flash->get('warnings'));
$okt->page->errors->setItems($okt->page->flash->get('errors'));

# construction du menu principal
$mainMenuHtml = null;
if ($okt->page->display_menu)
{
	$mainMenuHtml = $okt->page->mainMenu->build();

	$okt->page->accordion(array(
			'heightStyle' => 'auto',
			'active' => ($mainMenuHtml['active'] === null ? 0 : $mainMenuHtml['active'])
	), '#mainMenu-'.($okt->config->admin_sidebar_position == 0 ? 'left' : 'right'));
}



# init user bars
$aUserBarA = new \ArrayObject;
$aUserBarB = new \ArrayObject;

# logged in user
if (!$okt->user->is_guest)
{
	# profil link
	$sProfilLink = $view->escape($okt->user->usedname);
	if ($okt->modules->moduleExists('users')) {
		$sProfilLink = '<a href="module.php?m=users&amp;action=profil&amp;id='.$okt->user->id.'">'.$sProfilLink.'</a>';
	}

	$aUserBarA[10] = sprintf(__('c_c_user_hello_%s'), $sProfilLink);
	unset($sProfilLink);

	# log off link
	$aUserBarA[90] = '<a href="'.$okt->adminRouter->generate('logout').'">'.__('c_c_user_log_off_action').'</a>';

	# last visit info
	$aUserBarB[10] = sprintf(__('c_c_user_last_visit_on_%s'), \dt::str('%A %d %B %Y %H:%M',$okt->user->last_visit));
}
# guest user
else {
	$aUserBarA[10] = __('c_c_user_hello_you_are_not_logged');
}

# languages switcher
if ($okt->config->admin_lang_switcher && !$okt->languages->unique)
{
	$sBaseUri = $okt->request->getUri();
	$sBaseUri .= strpos($sBaseUri,'?') ? '&' : '?';

	foreach ($okt->languages->list as $aLanguage)
	{
		if ($aLanguage['code'] == $okt->user->language) {
			continue;
		}

		$aUserBarB[50] = '<a href="'.$sBaseUri.'switch_lang='.$view->escape($aLanguage['code']).'" title="'.$view->escape($aLanguage['title']).'">'.
				'<img src="'.$okt->options->public_url.'/img/flags/'.$aLanguage['img'].'" alt="'.$view->escape($aLanguage['title']).'" /></a>';
	}

	unset($sBaseUri,$aLanguage);
}

$aUserBarB[100] = '<a href="'.$okt->config->app_path.'">'.__('c_c_go_to_website').'</a>';

# -- CORE TRIGGER : adminHeaderUserBars
$okt->triggers->callTrigger('adminHeaderUserBars', $aUserBarA, $aUserBarB);


# sort items of user bars by keys
$aUserBarA->ksort();
$aUserBarB->ksort();

# remove empty values of user bars
$aUserBarA = array_filter((array)$aUserBarA);
$aUserBarB = array_filter((array)$aUserBarB);

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
			<?php if (!empty($aUserBarA)) : ?><p><?php echo implode(' - ', $aUserBarA) ?></p><?php endif; ?>
			<?php if (!empty($aUserBarB)) : ?><p><?php echo implode(' - ', $aUserBarB) ?></p><?php endif; ?>
		</div><!-- #welcome -->
	</div><!-- #helpers -->
</header>

<div id="main-<?php echo ($okt->config->admin_sidebar_position == 0 ? 'right' : 'left') ?>">

	<section id="content" class="ui-widget-content">

	<?php $view['slots']->output('_content'); ?>

	</section><!-- #content -->
</div><!-- #main -->

<nav><?php echo $mainMenuHtml['html'] ?></nav>

<?php # init footer content
$aFooterContent = new ArrayObject;

$aFooterContent[10] = sprintf(__('c_c_proudly_propulsed_%s'), '<a href="http://okatea.org/">Okatea</a>');

if ($okt->options->get('debug')) {
	$aFooterContent[20] = $okt->getVersion();
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
