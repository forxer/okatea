
<?php # début Okatea : ce template étend le layout
$view->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile($okt->theme->url.'/modules/##module_id##/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url.'/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->##module_id##->config->lightbox_type);
# fin Okatea : ajout du modal ?>


# début Okatea : javascript pour afficher les filtres s'ils sont repliés
if (!$okt->##module_id##->filters->params->show_filters)
{
	$okt->page->js->addReady('
		var c = $("###module_id##-filter-control").html("<a href=\"#\">'.html::escapeJs(__('m_##module_id##_display_filters')).'</a>");

		c.css("display","block");

		$("#'.$okt->##module_id##->filters->getFilterFormId().'").hide();

		c.click(function() {
			$("#'.$okt->##module_id##->filters->getFilterFormId().'").slideDown("slow");
			$(this).hide();
			return false;
		});
	');
}
# fin Okatea : javascript pour afficher les filtres s'ils sont repliés ?>


<h1><?php # début Okatea : affichage du titre du site
echo html::escapeHTML($okt->page->getSiteTitle());
# fin Okatea : affichage du titre du site ?></h1>


<?php # début Okatea : affichage du fil d'ariane
$okt->page->breadcrumb->getBreadcrumb('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
# fin Okatea : affichage du fil d'ariane ?>


<?php # début Okatea : lien d'affichage des filtres
if (!$okt->##module_id##->filters->params->show_filters) : ?>
<p id="##module_id##-filter-control"></p>
<?php endif; # fin Okatea : lien d'affichage des filtres ?>


<?php # début Okatea : affichage des filtres ?>
<form action="<?php echo html::escapeHTML($okt->##module_id##->config->url) ?>" class="##module_id##-filters-form" method="get" id="<?php echo $okt->##module_id##->filters->getFilterFormId() ?>">
	<fieldset>
		<legend><?php _e('m_##module_id##_display_filters') ?></legend>

		<?php echo $okt->##module_id##->filters->getFiltersFields(); ?>

		<p class="center"><input type="submit" value="<?php _e('c_c_action_display') ?>" name="<?php echo $okt->##module_id##->filters->getFilterSubmitName() ?>" />
		<a href="<?php echo html::escapeHTML($okt->##module_id##->config->url) ?>?init_##module_id##_filters=1" rel="nofollow" class="italic"><?php _e('c_c_reset_filters') ?></a></p>

	</fieldset>
</form>
<?php # fin Okatea : affichage des filtres ?>


<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
if ($rsItemsList->isEmpty()) : ?>

<p><em><?php _e('m_##module_id##_there_is_no_item') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des éléments on affiche la liste des éléments
if (!$rsItemsList->isEmpty()) : ?>

<div id="items_list">

	<?php # début Okatea : boucle sur la liste des éléments
	while ($rsItemsList->fetch()) : ?>

	<?php # début Okatea : affichage d'un élément ?>
	<div class="item <?php echo $rsItemsList->odd_even ?>">

		<?php # début Okatea : affichage du titre ?>
		<h2 class="item-title"><a href="<?php echo html::escapeHTML($rsItemsList->url) ?>"><?php echo html::escapeHTML($rsItemsList->title) ?></a></h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php # début Okatea : affichage du contenu ?>
		<div class="item-content">

			<?php echo $rsItemsList->description ?>

		</div><!-- .item-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div><!-- .item -->
	<?php # fin Okatea : affichage d'un élément ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des éléments ?>

</div><!-- items_list -->

<?php # début Okatea : affichage pagination
if ($rsItemsList->numPages > 1) : ?>

<ul class="pagination">
	<?php echo $rsItemsList->pager->getLinks(); ?>
</ul>

<?php endif;
# fin Okatea : affichage pagination ?>

<?php endif; # fin Okatea : si il y a des éléments on affiche la liste des éléments ?>

