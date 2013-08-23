
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/easing/jquery.easing.min.js');
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->pages->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<div id="page_item">

	<?php # début Okatea : affichage du sous-titre
	if ($rsPage->subtitle != '') : ?>
	<p class="page-subtitle"><strong><?php echo html::escapeHTML($rsPage->subtitle) ?></strong></p>
	<?php endif; # fin Okatea : affichage du sous-titre ?>


	<?php # début Okatea : si les images sont activées
	if ($okt->pages->config->images['enable'] && !empty($rsPage->images)) : ?>
	<p class="page-images modal-box">

		<?php # début Okatea : boucle sur les images
		foreach ($rsPage->images as $i=>$image) : ?>

			<?php # si c'est la première image on affiche la miniature
			if ($i == 1 && isset($image['min_url'])) : ?>

			<a href="<?php echo $image['img_url'] ?>"
			title="<?php echo util::escapeAttrHTML((isset($image['title']) && isset($image['title'][$okt->user->language]) ? $image['title'][$okt->user->language] : $rsPage->title)) ?>"
			class="modal center" rel="page-images">
			<img src="<?php echo $image['min_url'] ?>"
			<?php echo $image['min_attr'] ?>
			alt="<?php echo util::escapeAttrHTML((isset($image['alt']) && isset($image['alt'][$okt->user->language]) ? $image['alt'][$okt->user->language] : $rsPage->title)) ?>" /></a>

			<br />

			<?php # si c'est pas la première image on affiche le square
			elseif (isset($image['square_url'])) : ?>

			<a href="<?php echo $image['img_url'] ?>"
			title="<?php echo util::escapeAttrHTML((isset($image['title']) && isset($image['title'][$okt->user->language]) ? $image['title'][$okt->user->language] : $rsPage->title)) ?>"
			class="modal" rel="page-images">
			<img src="<?php echo $image['square_url'] ?>"
			<?php echo $image['square_attr'] ?>
			alt="<?php echo util::escapeAttrHTML((isset($image['alt']) && isset($image['alt'][$okt->user->language]) ? $image['alt'][$okt->user->language] : $rsPage->title)) ?>" /></a>

			<?php endif; ?>

		<?php endforeach; # fin Okatea : boucle sur les images ?>

	</p><!-- .page-images -->
	<?php endif; # fin Okatea : si les images sont activées ?>


	<?php # début Okatea : affichage du contenu ?>
	<div class="page-content">
		<?php echo $rsPage->content ?>
	</div><!-- .page-content -->
	<?php # fin Okatea : affichage du contenu ?>


	<?php # début Okatea : si les fichiers sont activées
	if ($okt->pages->config->files['enable'] && !empty($rsPage->files)) : ?>
	<div class="page-files three-cols">

		<?php # début Okatea : boucle sur les fichiers
		foreach ($rsPage->files as $i=>$file) : ?>

		<p class="col"><a href="<?php echo $file['url'] ?>"><img src="<?php echo OKT_PUBLIC_URL.'/img/media/'.$file['type'].'.png' ?>" alt="" /></a>
		<?php echo !empty($file['title'][$okt->user->language]) ? html::escapeHTML($file['title'][$okt->user->language]) : ''; ?> (<?php echo $file['mime'] ?>)
		- <?php echo util::l10nFileSize($file['size']) ?></p>

		<?php endforeach; # fin Okatea : boucle sur les fichiers ?>

	</div><!-- .page-files -->
	<?php endif; # fin Okatea : si les fichiers sont activées ?>

</div><!-- #page_item -->

<p class="scrollTop-wrapper"><a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a></p>
