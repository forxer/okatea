
<?php # début Okatea : ce template étend le layout
$view->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile($okt->theme->url.'/modules/##module_id##/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url .'/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->##module_id##->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<h1><?php # début Okatea : affichage du titre du site
echo html::escapeHTML($okt->page->getSiteTitle());
# fin Okatea : affichage du titre du site ?></h1>


<?php # début Okatea : affichage du fil d'ariane
echo $okt->page->breadcrumb->getBreadcrumb('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
# fin Okatea : affichage du fil d'ariane ?>


<?php # début Okatea : affichage du titre de l'élément ?>
<h2><?php echo html::escapeHTML($rsItem->title) ?></h2>
<?php # fin Okatea : affichage du titre de l'élément ?>


<?php # début Okatea : si les images sont activées
if ($okt->##module_id##->config->images['enable'] && !empty($rsItem->images)) : ?>
<p id="##module_id##-images" class="modal-box">

	<?php # début Okatea : boucle sur les images
	foreach ($rsItem->images as $i=>$image) : ?>

		<?php # si c'est la première image on affiche la miniature
		if ($i == 1 && isset($image['min_url'])) : ?>

		<a href="<?php echo $image['img_url'] ?>"
		title="<?php echo $view->escapeHtmlAttr($rsItem->title) ?>, image <?php echo $i ?>" class="modal center" rel="images">
		<img src="<?php echo $image['min_url'] ?>"
		<?php echo $image['min_attr'] ?>
		alt="<?php echo $view->escapeHtmlAttr((isset($image['alt']) ? $image['alt'] : $rsItem->title)) ?>" /></a>

		<br />

		<?php # si c'est pas la première image on affiche le square
		elseif (isset($image['square_url'])) : ?>

		<a href="<?php echo $image['img_url'] ?>"
		title="<?php echo $view->escapeHtmlAttr($rsItem->title) ?>, image <?php echo $i ?>" class="modal" rel="images">
		<img src="<?php echo $image['square_url'] ?>"
		<?php echo $image['square_attr'] ?>
		alt="<?php echo $view->escapeHtmlAttr((isset($image['alt']) ? $image['alt'] : $rsItem->title)) ?>" /></a>

		<?php endif; ?>

	<?php endforeach; # fin Okatea : boucle sur les images ?>

</p><!-- ###module_id##-images -->
<?php endif; # fin Okatea : si les images sont activées ?>


<?php # début Okatea : affichage du contenu ?>
<div id="##module_id##-content">
	<?php echo $rsItem->description ?>
</div><!-- ###module_id##-content -->
<?php # fin Okatea : affichage du contenu ?>


<?php # début Okatea : si les fichiers sont activées
if ($okt->##module_id##->config->files['enable']) : ?>
<div id="##module_id##-files" class="three-cols">

	<?php # début Okatea : boucle sur les fichiers
	foreach ($rsItem->files as $i=>$file) : ?>

	<p class="col"><a href="<?php echo $file['url'] ?>"><img src="<?php echo $okt->options->public_url.'/img/media/'.$file['type'].'.png' ?>" alt="<?php echo html::escapeHTML($file['title']) ?>" /></a>
	<?php echo html::escapeHTML($file['title']) ?> (<?php echo $file['mime'] ?>)
	- <?php echo Utilities::l10nFileSize($file['size']) ?></p>

	<?php endforeach; # fin Okatea : boucle sur les fichiers ?>

</div><!-- ###module_id##-files -->
<?php endif; # fin Okatea : si les fichiers sont activées ?>

