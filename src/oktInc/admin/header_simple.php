<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/


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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Language" content="fr" />
<title><?php echo html::escapeHtml($okt->page->titleTag(' - ')) ?></title>
<?php echo $okt->page->css ?>
<!--[if lt IE 9]><script type="text/javascript" src="<?php echo $okt->options->public_url ?>/plugins/html5shiv/dist/html5shiv.js"></script><![endif]-->
</head>
<body <?php if ($okt->page->hasPageId()) : ?>
	id="adminpage-<?php echo $okt->page->getPageId() ?>" <?php endif; ?>>
	<div id="page-simple" class="ui-widget-content">

		<?php
		# affichage des éventuels messages d'erreurs
		if ($okt->page->errors->hasError()) {
			echo $okt->page->errors->getErrors('<div class="errors_box ui-corner-all">%s</div>');
		}

		# affichage des éventuels messages d'avertissements
		if ($okt->page->warnings->hasWarning()) {
			echo $okt->page->warnings->getWarnings('<div class="warnings_box ui-corner-all">%s</div>');
		}

		# affichage des éventuels messages de confirmation
		if ($okt->page->success->hasSuccess()) {
			echo $okt->page->success->getSuccess('<div class="success_box ui-corner-all">%s</div>');
		}

		# affichage des éventuels messages d'information
		if ($okt->page->infos->hasInfo()) {
			echo $okt->page->infos->getInfos('<div class="infos_box ui-corner-all">%s</div>');
		}
		?>