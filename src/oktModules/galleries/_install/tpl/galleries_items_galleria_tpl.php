
<?php # début Okatea : si il y a des éléments on initialise galeria
if (!$rsItems->isEmpty()) : ?>
	<?php $okt->page->galleria('#galleria', $okt->galleries->config->galleria_options); ?>
<?php endif; # début Okatea : si il y a des éléments on initialise galeria ?>

<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_item')?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des éléments on affiche la liste de ces éléments
if (!$rsItems->isEmpty()) : ?>

	<div id="galleria">

		<?php # début Okatea : boucle sur la liste des éléments
		while ($rsItems->fetch()) : ?>

		<?php # début Okatea : affichage d'un élément ?>



			<?php # début Okatea : affichage image
			$item_image = $rsItems->getImagesInfo();
			if (!empty($item_image) && isset($item_image['min_url'])) : ?>

					<a href="<?php echo $item_image['img_url']?>"
					title="<?php echo util::escapeAttrHTML($rsItems->title) ?>"><img
					src="<?php echo $item_image['min_url']?>"
					title="<?php echo util::escapeAttrHTML($rsItems->title) ?>"
					<?php echo $item_image['min_attr']?>
					alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" /></a>


			<?php endif; # fin Okatea : affichage image ?>

		<!-- .item -->
		<?php # fin Okatea : affichage d'un élément ?>

		<?php endwhile; # fin Okatea : boucle sur la liste des éléments ?>

	</div><!-- #galleria -->

<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>