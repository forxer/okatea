<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php # début Okatea : title tag ?>
	<title><?php echo html::escapeHtml($okt->page->titleTag(' - ')); ?></title>
	<?php # fin Okatea : title tag ?>

	<?php # début Okatea : élément base pour la ré-écriture d'URL ?>
	<base href="<?php echo html::escapeHTML($okt->request->getSchemeAndHttpHost().$okt->config->app_path) ?>" />
	<?php # fin Okatea : élément base pour la ré-écriture d'URL ?>

	<?php # début Okatea : affichage meta description
	if (!empty($okt->page->meta_description)) : ?>
	<meta name="description" content="<?php echo html::escapeHTML($okt->page->meta_description) ?>" />
	<?php endif; # fin Okatea : affichage meta description ?>

	<?php # début Okatea : affichage meta keywords
	if (!empty($okt->page->meta_keywords)) : ?>
	<meta name="keywords" content="<?php echo html::escapeHTML($okt->page->meta_keywords) ?>" />
	<?php endif; # fin Okatea : affichage meta keywords ?>

	<?php # début Okatea : appels CSS
	echo $okt->page->css->getCss();
	# fin Okatea : appels CSS ?>

	<?php # début Okatea : appels JS
	echo $okt->page->js->getJs();
	# fin Okatea : appels JS ?>

	<?php # début Okatea : ajout d'éléments à l'en-tête
	echo $this->get('head');
	# fin Okatea : ajout d'éléments à l'en-tête ?>
</head>

<body>

<?php # début Okatea : affichage du contenu de la page
echo $this->get('content');
# fin Okatea : affichage du contenu de la page ?>


</body>
</html>