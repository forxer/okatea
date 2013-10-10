<?php
/**
 * L'en-tête
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */


$oktVersion = util::getVersion();
$oktRevision = util::getRevision();

?><!DOCTYPE html>
<html class="" lang="<?php echo $_SESSION['okt_install_language'] ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Language" content="<?php echo $_SESSION['okt_install_language'] ?>" />

	<title><?php _e('i_'.$_SESSION['okt_install_process_type'].'_interface') ?> - Okatea <?php if ($oktVersion) { echo $oktVersion; } ?></title>

	<?php echo $oHtmlPage->css ?>
</head>

<body>
<div id="page">

<header>
	<div id="banner" class="ui-widget-header ui-corner-all">
		<h1>Okatea <span class="version"><?php if ($oktVersion) { echo $oktVersion; } ?></span></h1>
		<p id="desc"><?php _e('i_'.$_SESSION['okt_install_process_type'].'_interface') ?></p>
	</div><!-- #banner -->

	<?php echo $stepper->display() ?>
</header>

<div id="main" class="ui-widget">

	<h2 id="page-title" class="ui-widget-header ui-corner-top"><?php if (!empty($title)) { echo $title; } else { _e('i_'.$_SESSION['okt_install_process_type'].'_interface'); }?></h2>
	<div id="content" class="ui-widget-content ui-corner-bottom">

	<?php if (isset($errors) && !$errors->isEmpty()) : ?>
	<div class="error_box">
		<h3><?php _e('i_errors') ?></h3>
		<?php echo $errors->get(); ?>
	</div>
	<?php endif; ?>

	<?php if (isset($okt) && !$okt->error->isEmpty()) : ?>
	<div class="error_box">
		<h3><?php _e('i_errors') ?></h3>
		<?php echo $okt->error->get(); ?>
	</div>
	<?php endif; ?>
