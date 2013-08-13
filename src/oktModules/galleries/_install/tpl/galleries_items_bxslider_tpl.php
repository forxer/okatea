
<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>

<?php # début Okatea : ajout de jQuery easing
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/easing/jquery.easing.min.js');
# fin Okatea : ajout de jQuery easing ?>

<?php # début Okatea : ajout de bxSlider
$okt->page->js->addFile(OKT_COMMON_URL.'/js-plugins/bxslider/scripts/jquery.bxSlider.min.js');
$okt->page->css->addFile(OKT_COMMON_URL.'/js-plugins/bxslider/css/bx.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php

$aConfig = array(
	'prevText' => __('c_c_previous'),
	'nextText' => __('c_c_next'),
	'startText' => __('c_c_start'),
	'stopText' => __('c_c_stop')
);

$aConfig = array_merge($okt->galleries->config->bxslider_options,$aConfig);

$okt->page->js->addReady('$(".bxSlider").bxSlider('.json_encode($aConfig).');');
?>

<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_item') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des éléments on affiche la liste de ces éléments
if (!$rsItems->isEmpty()) : ?>
<div class="bx-wrapper-wrapper">
    <div class="bx-wrapper">
        <ul class="bxSlider">
            <?php while($rsItems->fetch()) : ?>
                <li>
                    <?php # début Okatea : affichage image
                    $item_image = $rsItems->getImagesInfo();
                    if (!empty($item_image) && isset($item_image['img_url'])) : ?>
                    <img src="<?php echo $item_image['img_url']?>" <?php echo $item_image['img_attr']?>
                    title="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>"
                    alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" />
                    <?php endif; # fin Okatea : affichage image ?>
                </li>
            <?php endwhile; ?>
        </ul><!-- .bxSlider -->
    </div><!-- .bx-wrapper --> 
</div><!-- .bx-wrapper-wrapper -->
<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>
