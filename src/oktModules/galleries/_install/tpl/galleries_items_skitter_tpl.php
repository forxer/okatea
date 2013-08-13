
<?php $okt->page->skitter('.box_skitter', $okt->galleries->config->skitter_options); ?>


<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_item')?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des éléments on affiche la liste de ces éléments
if (!$rsItems->isEmpty()) : ?>

<div class="box_skitter">
	<ul>
		<?php while($rsItems->fetch()) : ?>
			<li>
				<?php # début Okatea : affichage image
				$item_image = $rsItems->getImagesInfo();
				if (!empty($item_image) && isset($item_image['img_url'])) : ?><img
						src="<?php echo $item_image['img_url']?>"
						<?php echo $item_image['min_attr']?>
						alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" />

				<?php endif; # fin Okatea : affichage image ?>
				<div class="label_text">
					<p><?php echo html::escapeHTML($rsItems->title) ?></p>
				</div>
			</li>
		<?php endwhile; ?>
	</ul>

</div>

<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>
