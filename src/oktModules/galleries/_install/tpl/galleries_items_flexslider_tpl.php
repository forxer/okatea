
<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_COMMON_URL.'/js-plugins/FlexSlider/flexslider.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js-plugins/FlexSlider/jquery.flexslider-min.js');
# fin Okatea : ajout de jQuery ?>


<?php

$aConfig = array(
	'prevText' => __('c_c_previous'),
	'nextText' => __('c_c_next')
);

$aConfig = array_merge($okt->galleries->config->flexslider_options,$aConfig);

$okt->page->js->addReady('$(".flexslider").flexslider('.json_encode($aConfig).');');
?>

<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_item') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des éléments on affiche la liste de ces éléments
if (!$rsItems->isEmpty()) : ?>
<div class="flexslider">
	<ul class="slides">
		<?php while($rsItems->fetch()) : ?>
			<li class="placeBtn">
				<?php # début Okatea : affichage image
				$item_image = $rsItems->getImagesInfo();
				if (!empty($item_image) && isset($item_image['img_url'])) : ?>
                <img src="<?php echo $item_image['img_url']?>" <?php echo $item_image['img_attr']?>
                alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" />
				<?php endif; # fin Okatea : affichage image ?>
				
                <p class="flex-caption"><?php echo html::escapeHTML($rsItems->title) ?></p>
			</li>
		<?php endwhile; ?>
	</ul>
</div>

<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>
