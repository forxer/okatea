<?php
use Okatea\Tao\L10n\DateTime;
?>

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
$okt->page->applyLbl($okt->module('News')->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php
# début Okatea : javascript pour afficher les filtres s'ils sont repliés
if ($okt->module('News')->config->enable_filters && !$okt->module('News')->filters->params->show_filters)
{
	$okt->page->js->addReady('
		var c = $("#news-filters-control").html("<a href=\"#\">' . $view->escapeJS(__('m_news_display_filters')) . '</a>");

		c.css("display","block");

		$("#' . $okt->module('News')->filters->getFilterFormId() . '").hide();

		c.click(function() {
			$("#' . $okt->module('News')->filters->getFilterFormId() . '").slideDown("slow");
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
	if ($okt->module('News')->filters->params->show_filters)
	:
		?>
<meta name="robots" content="none" />
<?php endif; # fin Okatea : si les filtres ont été utilisés, on index pas ?>

	<?php # début Okatea : lien vers le flux de syndication ?>
<link rel="alternate" type="application/rss+xml" title="Syndication RSS"
	href="<?php echo $view->generateUrl('newsFeed') ?>" />
<?php # fin Okatea : lien vers le flux de syndication ?>

<?php

$view['slots']->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>


<?php
# début Okatea : si les filtres sont activés
if ($okt->module('News')->config->enable_filters)
:
	?>

	<?php
	# début Okatea : lien d'affichage des filtres
	if (!$okt->module('News')->filters->params->show_filters)
	:
		?>
<p id="news-filters-control" class="filters-control"></p>
<?php endif; # fin Okatea : lien d'affichage des filtres ?>

	<?php # début Okatea : affichage des filtres ?>
<form action="<?php echo $view->generateUrl('newsList') ?>"
	id="<?php echo $okt->module('News')->filters->getFilterFormId() ?>"
	class="filters-form" method="get">
	<fieldset>
		<legend><?php _e('m_news_display_filters') ?></legend>

			<?php echo $okt->module('News')->filters->getFiltersFields(); ?>

			<p class="center">
			<input type="submit" value="<?php _e('c_c_action_display') ?>"
				name="<?php echo $okt->module('News')->filters->getFilterSubmitName() ?>" />
			<a
				href="<?php echo $view->generateUrl('newsList') ?>?init_news_filters=1"
				rel="nofollow" class="filters-init"><?php _e('m_news_display_filters_init') ?></a>
		</p>
	</fieldset>
</form>
<?php # fin Okatea : affichage des filtres ?>

<?php endif; # fin Okatea : si les filtres sont activés ?>


<?php
# début Okatea : si il n'y a PAS d'actualité à afficher on peut indiquer un message
if ($rsPostsList->isEmpty())
:
	?>

<p>
	<em><?php _e('m_news_there_is_no_post') ?></em>
</p>



<?php endif;
# fin Okatea : si il n'y a PAS d'actualité à afficher on peut indiquer un message ?>


<?php
# début Okatea : si il y a des actualités on affiche la liste
if (!$rsPostsList->isEmpty())
:
	?>

<div id="news_list">

	<?php
	# début Okatea : boucle sur la liste des actualités
	while ($rsPostsList->fetch())
	:
		?>

	<?php # début Okatea : affichage d'un article ?>
	<div class="post <?php echo $rsPostsList->odd_even ?>">


		<?php # début Okatea : affichage du titre ?>
		<h2 class="post-title">
			<a href="<?php echo $view->escape($rsPostsList->url) ?>"><?php echo $view->escape($rsPostsList->title) ?></a>
		</h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php
		# début Okatea : affichage du sous-titre
		if ($rsPostsList->subtitle != '')
		:
			?>
		<p class="post-subtitle">
			<strong><?php echo $view->escape($rsPostsList->subtitle) ?></strong>
		</p>
		<?php endif; # fin Okatea : affichage du sous-titre ?>

		<?php
		# début Okatea : affichage des infos
		if ($okt->module('News')->config->public_display_date || $okt->module('News')->config->public_display_author || $okt->module('News')->config->categories['enable'])
		:
			?>
		<p class="post-infos">
			<?php _e('m_news_published')?>

			<?php
			# début Okatea : affichage date de l'article
			if ($okt->module('News')->config->public_display_date)
			:
				?>
			<?php printf(__('m_news_on_%s'), DateTime::full($rsPostsList->created_at))?>
			<?php endif; # fin Okatea : affichage date de l'article ?>

			<?php
			# début Okatea : affichage l'auteur de l'article
			if ($okt->module('News')->config->public_display_author)
			:
				?>
			<?php printf(__('m_news_by_%s'), $view->escape($rsPostsList->author))?>
			<?php endif; # fin Okatea : affichage l'auteur de l'article ?>

			<?php
			# début Okatea : affichage rubrique
			if ($okt->module('News')->config->categories['enable'] && $rsPostsList->category_title)
			:
				?>
			<?php printf(__('m_news_in_%s'),'<a href="'.$view->escape($rsPostsList->category_url).'">'.$view->escape($rsPostsList->category_title).'</a>')?>
			<?php endif; # fin Okatea : affichage rubrique ?>

		</p>
		<!-- .post-infos -->
		<?php endif; # fin Okatea : affichage des infos ?>


		<?php # début Okatea : affichage du contenu ?>
		<div class="post-content">

		<?php
		# début Okatea : si on as PAS accès en lecture à l'article
		if (!$rsPostsList->isReadable())
		:
			?>

			<p><?php _e('m_news_restricted_access') ?></p>

		<?php endif; # début Okatea : si on as PAS accès en lecture à l'article ?>


		<?php
		# début Okatea : si on as accès en lecture à l'article
		if ($rsPostsList->isReadable())
		:
			?>

			<?php
			# début Okatea : affichage image
			if (!empty($rsPostsList->images) && isset($rsPostsList->images[1]) && isset($rsPostsList->images[1]['min_url']))
			:
				?>

			<div class="modal-box">
				<a href="<?php echo $rsPostsList->images[1]['img_url']?>"
					title="<?php echo $view->escapeHtmlAttr((isset($rsPostsList->images[1]['title'][$okt['visitor']->language]) ? $rsPostsList->images[1]['title'][$okt['visitor']->language] : $rsPostsList->title)) ?>"
					class="modal"><img
					src="<?php echo $rsPostsList->images[1]['min_url'] ?>"
					<?php echo $rsPostsList->images[1]['min_attr']?>
					alt="<?php echo $view->escapeHtmlAttr((isset($rsPostsList->images[1]['alt'][$okt['visitor']->language]) ? $rsPostsList->images[1]['alt'][$okt['visitor']->language] : $rsPostsList->title)) ?>" /></a>
			</div>
			<?php endif; # fin Okatea : affichage image ?>


			<?php
			# début Okatea : affichage texte tronqué
			if ($okt->module('News')->config->public_truncat_char > 0)
			:
				?>

			<p><?php echo $rsPostsList->content ?>… <a
					href="<?php echo $view->escape($rsPostsList->url) ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_news_read_more_of_%s'),$rsPostsList->title)) ?>"
					class="read-more-link" rel="nofollow"><?php _e('m_news_read_more') ?></a>
			</p>

			<?php endif; # fin Okatea : affichage texte tronqué ?>


			<?php
			# début Okatea : affichage texte pas tronqué
			if (!$okt->module('News')->config->public_truncat_char)
			:
				?>

			<?php echo $rsPostsList->content?>

			<?php endif; # fin Okatea : affichage texte pas tronqué ?>


		<?php endif; # début Okatea : si on as accès en lecture à l'article ?>

		</div>
		<!-- .post-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div>
	<!-- .post -->
	<?php # fin Okatea : affichage d'un article ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des actualités ?>

</div>
<!-- #news_list -->


<?php
	# début Okatea : affichage pagination
	if ($rsPostsList->numPages > 1)
	:
		?>

<ul class="pagination">
	<?php echo $rsPostsList->pager->getLinks(); ?>
</ul>



	<?php endif;
	# fin Okatea : affichage pagination 		?>


<p class="scrollTop-wrapper">
	<a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a>
</p>

<?php endif; # fin Okatea : si il y a des actualités on affiche la liste ?>


