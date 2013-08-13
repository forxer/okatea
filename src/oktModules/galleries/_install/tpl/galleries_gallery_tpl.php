
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_THEME.'/modules/galleries/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : affichage du fil d'ariane
if ($okt->galleries->config->enable_ariane) :
$okt->page->breadcrumb->setHtmlSeparator(' &rsaquo; ');
$okt->page->breadcrumb->display('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
endif; # fin Okatea : affichage du fil d'ariane ?>


<?php # début Okatea : affichage du titre de la galerie ?>
<!-- <h1><?php echo html::escapeHTML($rsGallery->title) ?></h1> -->
<?php # fin Okatea : affichage du titre de la galerie ?>


<?php # début Okatea : affichage de la description de la galerie ?>
<?php echo $rsGallery->description ?>
<?php # fin Okatea : affichage de la description de la galerie ?>


<?php # Okatea : si affichage du formulaire de mot de passe
if ($bGalleryRequirePassword) : ?>

<h2><?php _e('m_galleries_Gallery_protected_by_password')?></h2>

<form action="<?php echo html::escapeHTML($rsGallery->getGalleryUrl()) ?>" method="post">

	<p class="field"><label for="email">Mot de passe</label>
	<input id="okt_gallery_password" type="text" name="okt_gallery_password" maxlength="255" /></p>

	<p><input class="submit" type="submit" value="<?php _e('c_c_action_Send') ?>" /></p>

</form>

<?php # Okatea : sinon affichage de la galerie (liste de ses sous-galeries et de ses éléments)
else : ?>

	<?php # Okatea : si il y a des sous-galeries à afficher
	if (!$rsSubGalleries->isEmpty()) : ?>

	<div id="galleries_list">

		<?php # début Okatea : boucle sur la liste des sous-galeries
		while ($rsSubGalleries->fetch()) : ?>

		<?php # début Okatea : affichage d'une sous-galerie ?>
		<div class="gallery">

			<?php # début Okatea : affichage du titre de la sous-galerie ?>
			<h2 class="gallery-title"><a href="<?php echo html::escapeHTML($rsSubGalleries->getGalleryUrl()) ?>"><?php echo html::escapeHTML($rsSubGalleries->title) ?></a></h2>
			<?php # fin Okatea : affichage du titre de la sous-galerie ?>

			<?php # début Okatea : affichage image de la sous-galerie
			$gallery_image = $rsSubGalleries->getImagesInfo();
			if (!empty($gallery_image) && isset($gallery_image['min_url'])) : ?>

				<?php if ($okt->galleries->config->dysplay_clic_gal_image == 'enter') : ?>
				<p>
					<a href="<?php echo html::escapeHTML($rsSubGalleries->getGalleryUrl()) ?>"
					title="<?php echo util::escapeAttrHTML($rsSubGalleries->title) ?>"><img
					src="<?php echo $gallery_image['min_url']?>"
					<?php echo $gallery_image['min_attr']?>
					alt="<?php echo util::escapeAttrHTML((isset($gallery_image['alt']) ? $gallery_image['alt'] : $rsSubGalleries->title)) ?>" /></a>
				</p>
				<?php else : ?>
				<p class="modal-box">
					<a href="<?php echo $gallery_image['img_url']?>"
					title="<?php echo util::escapeAttrHTML($rsSubGalleries->title) ?>" class="modal"><img
					src="<?php echo $gallery_image['min_url']?>"
					<?php echo $gallery_image['min_attr']?>
					alt="<?php echo util::escapeAttrHTML((isset($gallery_image['alt']) ? $gallery_image['alt'] : $rsSubGalleries->title)) ?>" /></a>
				</p>
				<?php endif; ?>

			<?php endif; # fin Okatea : affichage image de la sous-galerie ?>

		</div><!-- .gallery -->
		<?php # fin Okatea : affichage d'une sous-galerie ?>

		<?php endwhile; # fin Okatea : boucle sur la liste des sous-galeries ?>

	</div><!-- #galleries_list -->

	<?php endif; # fin Okatea : si il y a des sous-galeries à afficher ?>


	<?php # affichage du template des élements
	echo $okt->tpl->render($okt->galleries->getTemplateName()); ?>


<?php # fin Okatea : affichage de la galerie (liste de ses sous-galeries et de ses éléments)
endif; ?>

