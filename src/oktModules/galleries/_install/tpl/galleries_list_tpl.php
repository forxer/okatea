
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


<?php # début Okatea : si il n'y a PAS de galerie à afficher on peut indiquer un message
if ($rsGalleriesList->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_gallery')?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS de galerie à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des galeries on affiche la liste des galeries
if (!$rsGalleriesList->isEmpty()) : ?>

<div id="galleries_list">

	<?php # début Okatea : boucle sur la liste des galeries
	while ($rsGalleriesList->fetch()) : ?>

	<?php # début Okatea : affichage d'une galerie ?>
	<div class="gallery">

		<?php # début Okatea : affichage du titre ?>
		<h2 class="gallery-title"><a href="<?php echo html::escapeHTML($rsGalleriesList->getGalleryUrl()) ?>"><?php echo html::escapeHTML($rsGalleriesList->title) ?></a></h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php # début Okatea : affichage image
		$gallery_image = $rsGalleriesList->getImagesInfo();
		if (!empty($gallery_image) && isset($gallery_image['min_url'])) : ?>

			<?php if ($okt->galleries->config->dysplay_clic_gal_image == 'enter') : ?>
			<p>
				<a href="<?php echo html::escapeHTML($rsGalleriesList->getGalleryUrl()) ?>"><img src="<?php echo $gallery_image['min_url'] ?>"
				<?php echo $gallery_image['min_attr']?>
				alt="<?php echo util::escapeAttrHTML((isset($gallery_image['alt']) ? $gallery_image['alt'] : $rsGalleriesList->title)) ?>" /></a>
			</p>
			<?php else : ?>
			<p class="modal-box">
				<a href="<?php echo $gallery_image['img_url']?>"
				title="<?php echo util::escapeAttrHTML($rsGalleriesList->title) ?>"
				class="modal"><img src="<?php echo $gallery_image['min_url']?>"
				<?php echo $gallery_image['min_attr']?>
				alt="<?php echo util::escapeAttrHTML((isset($gallery_image['alt']) ? $gallery_image['alt'] : $rsGalleriesList->title)) ?>" /></a>
			</p>
			<?php endif; ?>

		<?php endif; # fin Okatea : affichage image ?>

	</div><!-- .gallery -->
	<?php # fin Okatea : affichage d'une galerie ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des galeries ?>

</div><!-- #galleries_list -->

<?php endif; # fin Okatea : si il y a des galeries on affiche la liste des galeries ?>

