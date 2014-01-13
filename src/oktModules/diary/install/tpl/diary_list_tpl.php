
<?php # début Okatea : ce template étend le layout
$view->extend('layout');
# fin Okatea : ce template étend le layout ?>

<?php # début Okatea : initialisation des filtres
$okt->diary->filtersStart('public');

# création des filtres
$okt->diary->filters->getFiltersDate();
# fin Okatea : initialisation des filtres ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile($okt->theme->url.'/modules/diary/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>

<?php # début Okatea : on ajoutent des éléments à l'en-tête HTML
$view['slots']->start('head') ?>

	<?php # début Okatea : si les filtres ont été utilisés, on index pas
	if ($okt->diary->filters->params->show_filters) : ?>
	<meta name="robots" content="none" />
	<?php endif; # fin Okatea : si les filtres ont été utilisés, on index pas ?>

<?php $view['slots']->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>

<?php # début Okatea : si les filtres sont activés
if ($okt->diary->config->enable_filters) : ?>
	<form action="<?php echo $view->escape(DiaryHelpers::getDiaryUrl()) ?> " method="get" id="filters-form">
		<fieldset>
			<legend><?php _e('m_diary_display_filters_public') ?></legend>

			<?php echo $okt->diary->filters->getFiltersFieldsDate('<div class="two-cols">%s</div>'); ?>

			<p class="center"><input type="submit" value="<?php _e('c_c_action_display') ?>" name="<?php echo $okt->diary->filters->getFilterSubmitName() ?>" /></p>

		</fieldset>
	</form>
<?php  endif; # fin Okatea : si les filtres sont activés ?>


<?php # début Okatea : affichage du calendrier
echo $oCal->getHtml();
# fin Okatea : affichage du calendrier ?>
