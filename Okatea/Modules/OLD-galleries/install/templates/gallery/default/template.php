
<?php 
# début Okatea : ce template étend le layout
$view->extend('Layout');
# fin Okatea : ce template étend le layout ?>


<?php 
# début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__ . '/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php 
# début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php 
# début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile($okt->options->public_url . '/plugins/easing/jquery.easing.min.js');
$okt->page->js->addFile($okt->options->public_url . '/plugins/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php 
# début Okatea : ajout du modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : affichage du titre de la galerie ?>
<!-- <h1><?php echo $view->escape($rsGallery->title) ?></h1> -->
<?php # fin Okatea : affichage du titre de la galerie ?>


<?php # début Okatea : affichage de la description de la galerie ?>
<?php echo $rsGallery->content?>
<?php # fin Okatea : affichage de la description de la galerie ?>


<?php 
# Okatea : si affichage du formulaire de mot de passe
if ($bGalleryRequirePassword)
:
	?>

<h2><?php _e('m_galleries_Gallery_protected_by_password')?></h2>

<form id="gallery_password_form"
	action="<?php echo $view->escape($rsGallery->url) ?>" method="post">

	<p class="field">
		<label for="email">Mot de passe</label> <input
			id="okt_gallery_password" type="text" name="okt_gallery_password"
			maxlength="255" />
	</p>

	<p>
		<input class="submit" type="submit"
			value="<?php _e('c_c_action_Send') ?>" />
	</p>

</form>


<?php 
# Okatea : sinon affichage de la galerie (liste de ses sous-galeries et de ses éléments)
else
:
	?>

	<?php 
# Okatea : si il y a des sous-galeries à afficher
	if (! $rsSubGalleries->isEmpty())
	:
		?>

<div id="galleries_list">

		<?php 
# début Okatea : boucle sur la liste des sous-galeries
		while ($rsSubGalleries->fetch())
		:
			?>

		<?php # début Okatea : affichage d'une sous-galerie ?>
		<div class="gallery <?php echo $rsSubGalleries->odd_even ?>">

			<?php # début Okatea : affichage du titre de la sous-galerie ?>
			<h2 class="gallery-title">
			<a href="<?php echo $view->escape($rsSubGalleries->url) ?>"><?php echo $view->escape($rsSubGalleries->title) ?></a>
		</h2>
			<?php # fin Okatea : affichage du titre de la sous-galerie ?>

			<?php 
# début Okatea : affichage image de la sous-galerie
			if (! empty($rsSubGalleries->image) && isset($rsSubGalleries->image['min_url']))
			:
				?>

				<?php if ($okt->galleries->config->dysplay_clic_gal_image == 'enter') : ?>
				<p>
			<a href="<?php echo $view->escape($rsSubGalleries->url) ?>"><img
				src="<?php echo $rsSubGalleries->image['min_url']?>"
				<?php echo $rsSubGalleries->image['min_attr']?>
				<?php if (isset($rsSubGalleries->image['title']) && isset($rsSubGalleries->image['title'][$okt->user->language])) : ?>
				title="<?php echo $view->escapeHtmlAttr($rsSubGalleries->image['title'][$okt->user->language]) ?>"
				<?php endif; ?>
				alt="<?php echo $view->escapeHtmlAttr((isset($rsSubGalleries->image['alt']) && $rsSubGalleries->image['alt'][$okt->user->language] ? $rsSubGalleries->image['alt'][$okt->user->language] : $rsSubGalleries->title)) ?>" /></a>
		</p>
				<?php else : ?>
				<p class="modal-box">
			<a href="<?php echo $rsSubGalleries->image['img_url']?>"
				class="modal"><img
				src="<?php echo $rsSubGalleries->image['min_url']?>"
				<?php echo $rsSubGalleries->image['min_attr']?>
				<?php if (isset($rsSubGalleries->image['title']) && isset($rsSubGalleries->image['title'][$okt->user->language])) : ?>
				title="<?php echo $view->escapeHtmlAttr($rsSubGalleries->image['title'][$okt->user->language]) ?>"
				<?php endif; ?>
				alt="<?php echo $view->escapeHtmlAttr((isset($rsSubGalleries->image['alt']) && isset($rsSubGalleries->image['alt'][$okt->user->language]) ? $rsSubGalleries->image['alt'][$okt->user->language] : $rsSubGalleries->title)) ?>" /></a>
		</p>
				<?php endif; ?>

			<?php endif; # fin Okatea : affichage image de la sous-galerie ?>

		</div>
	<!-- .gallery -->
		<?php # fin Okatea : affichage d'une sous-galerie ?>

		<?php endwhile; # fin Okatea : boucle sur la liste des sous-galeries ?>

	</div>
<!-- #galleries_list -->

<?php endif; # fin Okatea : si il y a des sous-galeries à afficher ?>


	<?php 
# début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
	if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty())
	:
		?>

<p>
	<em><?php _e('m_galleries_no_item')?></em>
</p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


	<?php 
# début Okatea : si il y a des éléments on affiche la liste de ces éléments
	if (! $rsItems->isEmpty())
	:
		?>

<div id="items_list">

		<?php 
# début Okatea : boucle sur la liste des éléments
		while ($rsItems->fetch())
		:
			?>

		<?php # début Okatea : affichage d'un élément ?>
		<div class="item">

			<?php # début Okatea : affichage du titre ?>
			<h2 class="item-title">
			<a href="<?php echo $view->escape($rsItems->getItemUrl()) ?>"><?php echo $view->escape($rsItems->title) ?></a>
		</h2>
			<?php # fin Okatea : affichage du titre ?>

			<?php 
# début Okatea : affichage image
			if (! empty($rsItems->image) && isset($rsItems->image['min_url']))
			:
				?>

				<?php if ($okt->galleries->config->dysplay_clic_items_image == 'details') : ?>
				<p>
			<a href="<?php echo $view->escape($rsItems->getItemUrl()) ?>"
				title="<?php echo $view->escapeHtmlAttr($rsItems->title) ?>"><img
				src="<?php echo $rsItems->image['min_url']?>"
				<?php echo $rsItems->image['min_attr']?>
				alt="<?php echo $view->escapeHtmlAttr((isset($rsItems->image['alt']) && isset($rsItems->image['alt'][$okt->user->language]) ? $rsItems->image['alt'][$okt->user->language] : $rsItems->title)) ?>" /></a>
		</p>
				<?php else : ?>
				<p class="modal-box">
			<a href="<?php echo $rsItems->image['img_url']?>"
				title="<?php echo $view->escapeHtmlAttr($rsItems->title) ?>"
				class="modal" rel="gallery"><img
				src="<?php echo $rsItems->image['min_url']?>"
				<?php echo $rsItems->image['min_attr']?>
				alt="<?php echo $view->escapeHtmlAttr((isset($rsItems->image['alt']) && isset($rsItems->image['alt'][$okt->user->language]) ? $rsItems->image['alt'][$okt->user->language] : $rsItems->title)) ?>" /></a>
		</p>
				<?php endif; ?>

			<?php endif; # fin Okatea : affichage image ?>

		</div>
	<!-- .item -->
		<?php # fin Okatea : affichage d'un élément ?>

		<?php endwhile; # fin Okatea : boucle sur la liste des éléments ?>

	</div>
<!-- #items_list -->

<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>


<?php
# fin Okatea : affichage de la galerie (liste de ses sous-galeries et de ses éléments)
endif;
?>


<p class="scrollTop-wrapper">
	<a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a>
</p>


