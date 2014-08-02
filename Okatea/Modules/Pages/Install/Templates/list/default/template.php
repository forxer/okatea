
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
$okt->page->js->addFile($okt['public_url'] . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php
# début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile($okt['public_url'] . '/plugins/easing/jquery.easing.min.js');
$okt->page->js->addFile($okt['public_url'] . '/plugins/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
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
$okt->page->applyLbl($okt->module('Pages')->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php
# début Okatea : javascript pour afficher les filtres s'ils sont repliés
if (! $okt->module('Pages')->filters->params->show_filters)
{
	$okt->page->js->addReady('
		var c = $("#pages-filter-control").html("<a href=\"#\">' . $view->escapeJS(__('m_pages_display_filters')) . '</a>");

		c.css("display","block");

		$("#' . $okt->module('Pages')->filters->getFilterFormId() . '").hide();

		c.click(function() {
			$("#' . $okt->module('Pages')->filters->getFilterFormId() . '").slideDown("slow");
			$(this).hide();
			return false;
		});
	');
}
# fin Okatea : javascript pour afficher les filtres s'ils sont repliés ?>


<?php
# début Okatea : on ajoutent des éléments à l'en-tête HTML
$view['slots']->start('head')?>

	<?php
	# début Okatea : si les filtres ont été utilisés, on index pas
	if ($okt->module('Pages')->filters->params->show_filters)
	:
		?>
<meta name="robots" content="none" />
<?php endif; # fin Okatea : si les filtres ont été utilisés, on index pas ?>

	<?php # début Okatea : lien vers le flux de syndication ?>
<!-- <link rel="alternate" type="application/rss+xml" title="Syndication RSS" href="<?php echo $view->generateUrl('pagesFeed') ?>" /> -->
<?php # fin Okatea : lien vers le flux de syndication ?>

<?php

$view['slots']->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>


<?php
# début Okatea : si les filtres sont activés
if ($okt->module('Pages')->config->enable_filters)
:
	?>

	<?php
	# début Okatea : lien d'affichage des filtres
	if (! $okt->module('Pages')->filters->params->show_filters)
	:
		?>
<p id="pages-filter-control" class="filters-control"></p>
<?php endif; # fin Okatea : lien d'affichage des filtres ?>


	<?php # début Okatea : affichage des filtres ?>
<form action="<?php echo $view->generateUrl('pagesList') ?>"
	id="<?php echo $okt->module('Pages')->filters->getFilterFormId() ?>"
	class="filters-form" method="get">
	<fieldset>
		<legend><?php _e('m_pages_display_filters') ?></legend>

			<?php echo $okt->module('Pages')->filters->getFiltersFields(); ?>

			<p class="center">
			<input type="submit" value="<?php _e('c_c_action_display') ?>"
				name="<?php echo $okt->module('Pages')->filters->getFilterSubmitName() ?>" />
			<a
				href="<?php echo $view->generateUrl('pagesList') ?>?init_pages_filters=1"
				rel="nofollow" class="filters-init"><?php _e('m_pages_display_filters_init') ?></a>
		</p>
	</fieldset>
</form>
<?php # fin Okatea : affichage des filtres ?>

<?php endif; # fin Okatea : si les filtres sont activés ?>


<?php
# début Okatea : affichage d'une éventuelle description de rubrique
if (! empty($rsCategory->content))
:
	?>
<div class="rubrique-description">
	<?php echo $rsCategory->content?>
</div>
<!-- .rubrique-description -->
<?php endif; # fin Okatea : affichage d'une éventuelle description de rubrique ?>


<?php
# début Okatea : si il n'y a PAS de page à afficher on peut indiquer un message
if ($rsPagesList->isEmpty())
:
	?>

<p>
	<em><?php _e('m_pages_there_is_no_pages') ?></em>
</p>

<?php endif; # fin Okatea : si il n'y a PAS de page à afficher on peut indiquer un message ?>


<?php
# début Okatea : si il y a des page à afficher
if (! $rsPagesList->isEmpty())
:
	?>
<div id="pages_list">

	<?php
	# début Okatea : boucle sur la liste des pages
	while ($rsPagesList->fetch())
	:
		?>

	<?php # début Okatea : affichage d'une page ?>
	<div class="page <?php echo $rsPagesList->odd_even ?>">

		<?php # début Okatea : affichage du titre ?>
		<h2 class="page-title">
			<a href="<?php echo $rsPagesList->url ?>"><?php echo $view->escape($rsPagesList->title) ?></a>
		</h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php
		# début Okatea : affichage du sous-titre
		if ($rsPagesList->subtitle != '')
		:
			?>
		<p class="page-subtitle">
			<strong><?php echo $view->escape($rsPagesList->subtitle) ?></strong>
		</p>
		<?php endif; # fin Okatea : affichage du sous-titre ?>

		<?php # début Okatea : affichage du contenu ?>
		<div class="page-content">

		<?php
		# début Okatea : si on as PAS accès en lecture à la page
		if (! $rsPagesList->isReadable())
		:
			?>

			<p><?php _e('m_pages_restricted_access') ?></p>

		<?php endif; # début Okatea : si on as PAS accès en lecture à la page ?>


		<?php
		# début Okatea : si on as accès en lecture à la page
		if ($rsPagesList->isReadable())
		:
			?>

			<?php
			# début Okatea : affichage texte tronqué
			if ($okt->module('Pages')->config->public_truncat_char > 0)
			:
				?>

			<p><?php echo $rsPagesList->content ?>… </p>

			<p class="read-more-link-wrapper">
				<a href="<?php echo $view->escape($rsPagesList->url) ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_read_more_of_%s'),$rsPagesList->title)) ?>"
					class="read-more-link" rel="nofollow"><?php _e('m_pages_read_more') ?></a>
			</p>

			<?php endif; # fin Okatea : affichage texte tronqué ?>


			<?php
			# début Okatea : affichage texte pas tronqué
			if (! $okt->module('Pages')->config->public_truncat_char)
			:
				?>

			<?php echo $rsPagesList->content?>

			<?php endif; # fin Okatea : affichage texte pas tronqué ?>

		<?php endif; # fin Okatea : si on as accès en lecture à la page ?>

		</div>
		<!-- .post-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div>
	<!-- .page -->
	<?php # fin Okatea : affichage d'une page ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des pages ?>

</div>
<!-- #pages_list -->

<?php
	# début Okatea : affichage pagination
	if ($rsPagesList->numPages > 1)
	:
		?>

<ul class="pagination">
		<?php echo $rsPagesList->pager->getLinks(); ?>
	</ul>

<?php endif; # fin Okatea : affichage pagination ?>

<?php endif; # fin Okatea : si il y a des page à afficher ?>

<p class="scrollTop-wrapper">
	<a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a>
</p>

