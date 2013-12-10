<!DOCTYPE html>
<html lang="<?php echo $okt->user->language ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<?php # début Okatea : title tag ?>
	<title><?php echo $view->escape($okt->page->titleTag(' - ')); ?></title>
	<?php # fin Okatea : title tag ?>

	<?php # début Okatea : élément base pour la ré-écriture d'URL ?>
	<base href="<?php echo $view->escape($okt->config->app_url) ?>" >
	<?php # fin Okatea : élément base pour la ré-écriture d'URL ?>

	<?php # début Okatea : affichage meta description
	if (!empty($okt->page->meta_description)) : ?>
	<meta name="description" content="<?php echo $view->escape($okt->page->meta_description) ?>" >
	<?php endif; # fin Okatea : affichage meta description ?>

	<?php # début Okatea : affichage meta keywords
	if (!empty($okt->page->meta_keywords)) : ?>
	<meta name="keywords" content="<?php echo $view->escape($okt->page->meta_keywords) ?>" >
	<?php endif; # fin Okatea : affichage meta keywords ?>

	<?php # début Okatea : appels CSS
	echo $okt->page->css->getCss();
	# fin Okatea : appels CSS ?>

	<?php # début Okatea : appels JS
	echo $okt->page->js->getJs();
	# fin Okatea : appels JS ?>

	<?php # début Okatea : ajout d'éléments à l'en-tête
	$view['slots']->output('head');
	# fin Okatea : ajout d'éléments à l'en-tête ?>

	<?php # début Okatea :  -- CORE TRIGGER : publicBeforeHtmlHeadEndTag
	$okt->triggers->callTrigger('publicBeforeHtmlHeadEndTag', $okt);
	# fin Okatea :  -- CORE TRIGGER : publicBeforeHtmlHeadEndTag ?>
</head>
<body>
<?php # début Okatea :  -- CORE TRIGGER : publicAfterHtmlBodyStartTag
$okt->triggers->callTrigger('publicAfterHtmlBodyStartTag', $okt);
# fin Okatea :  -- CORE TRIGGER : publicAfterHtmlBodyStartTag ?>

<?php # début Okatea : affichage du contenu de la page
$view['slots']->output('_content');
# fin Okatea : affichage du contenu de la page ?>

<?php # début Okatea :  -- CORE TRIGGER : publicBeforeHtmlBodyEndTag
$okt->triggers->callTrigger('publicBeforeHtmlBodyEndTag', $okt);
# fin Okatea :  -- CORE TRIGGER : publicBeforeHtmlBodyEndTag ?>
</body>
</html>