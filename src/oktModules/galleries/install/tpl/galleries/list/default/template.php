
<?php # début Okatea : ce template étend le layout
$view->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url.'/components/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile($okt->options->public_url.'/plugins/easing/jquery.easing.min.js');
$okt->page->js->addFile($okt->options->public_url.'/plugins/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : si il n'y a PAS de galerie à afficher on peut indiquer un message
if ($rsGalleriesList->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_gallery') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS de galerie à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des galeries on affiche la liste des galeries
if (!$rsGalleriesList->isEmpty()) : ?>

<div id="galleries_list">

	<?php # début Okatea : boucle sur la liste des galeries
	while ($rsGalleriesList->fetch()) : ?>

	<?php # début Okatea : affichage d'une galerie ?>
	<div class="gallery <?php echo $rsGalleriesList->odd_even ?>">

		<?php # début Okatea : affichage du titre ?>
		<h2 class="gallery-title"><a href="<?php echo $view->escape($rsGalleriesList->url) ?>"><?php echo $view->escape($rsGalleriesList->title) ?></a></h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php # début Okatea : affichage image
		if (!empty($rsGalleriesList->image) && isset($rsGalleriesList->image['min_url'])) : ?>

			<?php if ($okt->galleries->config->dysplay_clic_gal_image == 'enter') : ?>
			<p>
				<a href="<?php echo $view->escape($rsGalleriesList->url) ?>"><img src="<?php echo $rsGalleriesList->image['min_url'] ?>"
				<?php echo $rsGalleriesList->image['min_attr'] ?>
				<?php if (isset($rsGalleriesList->image['title']) && isset($rsGalleriesList->image['title'][$okt->user->language])) : ?> title="<?php echo $view->escapeHtmlAttr($rsGalleriesList->image['title'][$okt->user->language]) ?>"<?php endif; ?>
				alt="<?php echo $view->escapeHtmlAttr((isset($rsGalleriesList->image['alt']) && isset($rsGalleriesList->image['alt'][$okt->user->language]) ? $rsGalleriesList->image['alt'][$okt->user->language] : $rsGalleriesList->title)) ?>" /></a>
			</p>
			<?php else : ?>
			<p class="modal-box">
				<a href="<?php echo $rsGalleriesList->image['img_url'] ?>" class="modal"><img src="<?php echo $rsGalleriesList->image['min_url'] ?>"
				<?php echo $rsGalleriesList->image['min_attr']?>
				<?php if (isset($rsGalleriesList->image['title']) && isset($rsGalleriesList->image['title'][$okt->user->language])) : ?> title="<?php echo $view->escapeHtmlAttr($rsGalleriesList->image['title'][$okt->user->language]) ?>"<?php endif; ?>
				alt="<?php echo $view->escapeHtmlAttr((isset($rsGalleriesList->image['alt']) && isset($rsGalleriesList->image['alt'][$okt->user->language]) ? $rsGalleriesList->image['alt'][$okt->user->language] : $rsGalleriesList->title)) ?>" /></a>
			</p>
			<?php endif; ?>

		<?php endif; # fin Okatea : affichage image ?>

	</div><!-- .gallery -->
	<?php # fin Okatea : affichage d'une galerie ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des galeries ?>

</div><!-- #galleries_list -->

<?php endif; # fin Okatea : si il y a des galeries on affiche la liste des galeries ?>


<p class="scrollTop-wrapper"><a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a></p>

