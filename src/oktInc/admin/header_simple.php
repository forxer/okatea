<?php
/**
 * En-tête "simple" des pages d'administration
 *
 * @addtogroup Okatea
 *
 */

# récupération des erreurs du core
if ($okt->error->notEmpty())
{
	foreach($okt->error->get(false) as $error) {
		$okt->page->errors->set($error['message']);
	}
}


# -- CORE TRIGGER : adminBeforeSendHeader
$okt->triggers->callTrigger('adminBeforeSendHeader', $okt);

# En-tête HTTP
header('Content-Type: text/html; charset=utf-8');

# Start output buffering
ob_start();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Language" content="fr" />

	<title><?php echo html::escapeHtml($okt->page->titleTag(' - ')) ?></title>

	<link type="text/css" href="<?php echo $okt->config->app_path ?>oktMin/?g=css_admin" rel="stylesheet" media="screen" />
	<!--[if lte IE 8]>
	<link type="text/css" href="<?php echo OKT_COMMON_URL ?>/css/ie-pu-du-ku-c-fou.css" rel="stylesheet for IE" media="screen" />
	<![endif]-->
	<?php echo $okt->page->css ?>

</head>
<body<?php if ($okt->page->hasPageId()) : ?> id="adminpage-<?php echo $okt->page->getPageId() ?>"<?php endif; ?>>
<div id="page-simple" class="ui-widget-content">

<?php
# affichage des éventuelles erreurs
if ($okt->page->errors->hasError()) {
	echo $okt->page->errors->getErrors('<div class="error_box ui-corner-all">%s</div>');
}

# affichage des éventuels avertissements
elseif ($okt->page->warnings->hasWarning()) {
	echo $okt->page->warnings->getWarnings('<div class="wrn_box ui-corner-all">%s</div>');
}

# affichage des éventuels messages
elseif ($okt->page->messages->hasMessage()) {
	echo $okt->page->messages->getMessages('<div class="msg_box ui-corner-all">%s</div>');
}
?>


