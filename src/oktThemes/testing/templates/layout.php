
<?php use Tao\Misc\Utilities as util; ?>

<?php # début Okatea : ce template étend le template principal "main"
$this->extend('main');
# fin Okatea : ce template étend le template principal "main" ?>


<div id="page">
	<header class="clearfix">

		<div id="head">
			<div class="title"><?php echo html::escapeHTML(util::getSiteTitle()) ?></div>
			<div class="description"><?php echo html::escapeHTML(util::getSiteDescription()) ?></div>
		</div><!-- #head -->

		<div id="search">
			<?php # début Okatea : affichage de la boite de recherche de véhicules
			if ($okt->modules->moduleExists('vehicles')) :
			echo $okt->tpl->render('vehicles_search_tpl');
			endif; # fin Okatea : affichage de la boite de recherche de véhicules ?>

			<?php # début Okatea : affichage du switcher de langues
			if (!$okt->languages->unique) : ?>
			<ul id="lang_switcher">
				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<li id="lang_switcher_<?php echo html::escapeHTML($aLanguage['code']) ?>"><a href="<?php echo html::escapeHTML($aLanguage['code'].'/'.$okt->router->getPath()) ?>" title="<?php echo html::escapeHTML($aLanguage['title']) ?>"><img
				src="<?php echo OKT_PUBLIC_URL.'/img/flags/'.$aLanguage['img'] ?>" alt="<?php echo html::escapeHTML($aLanguage['title']) ?>" /></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; # fin Okatea : affichage du switcher de langues ?>
		</div><!-- #search -->

		<?php # début Okatea : affichage menu haut
			echo $okt->navigation->render('MenuTop');
		# fin Okatea : affichage menu haut ?>

	</header>

	<?php # début Okatea : titre SEO de la page (h1)
	if ($okt->page->hasTitleSeo()) : ?>
	<h1><?php echo html::escapeHtml($okt->page->getTitleSeo()); ?></h1>
	<?php endif; # fin Okatea : titre SEO de la page (h1) ?>

	<?php # début Okatea : affichage de la barre utilisateur
	if ($okt->modules->moduleExists('users')) :
	echo $okt->tpl->render($okt->users->getUserBarTplPath());
	endif; # fin Okatea : affichage de la barre utilisateur ?>


	<div id="sidebar">
		<?php # début Okatea : affichage menu milieu
			echo $okt->navigation->render('MenuMiddle');
		# fin Okatea : affichage menu milieu ?>

		<?php # début Okatea : si le module news est présent, affichage de l'encart
		if ($okt->modules->moduleExists('news')) :
			echo $this->render($okt->news->getInsertTplPath());
		endif; # fin Okatea : si le module news est présent, affichage de l'encart ?>


		<?php # début Okatea : affichage des pages de la rubrique id 1
		if ($okt->modules->moduleExists('pages')) :
			echo pagesHelpers::getPagesByCatId(1);
		endif; # fin Okatea : affichage des pages de la rubrique id 1 ?>

		<?php # début Okatea : affichage des sous-rubriques de la rubrique id 1
		if ($okt->modules->moduleExists('pages')) :
			echo pagesHelpers::getSubCatsByCatId(1);
		endif; # fin Okatea : affichage des sous-rubriques de la rubrique id 1 ?>

		<?php # début Okatea : affichage de l'arbre des rubriques
		if ($okt->modules->moduleExists('pages')) :
			echo pagesHelpers::getCategories();
		endif; # fin Okatea : affichage de l'arbre des rubriques ?>


		<?php # début Okatea : affichage des news de la rubrique id 1
		if ($okt->modules->moduleExists('news')) :
			echo newsHelpers::getPostsByCatId(1);
		endif; # fin Okatea : affichage des news de la rubrique id 1 ?>

		<?php # début Okatea : affichage des sous-rubriques de la rubrique id 1
		if ($okt->modules->moduleExists('news')) :
			echo newsHelpers::getSubCatsByCatId(1);
		endif; # fin Okatea : affichage des sous-rubriques de la rubrique id 1 ?>

		<?php # début Okatea : affichage de l'arbre des rubriques
		if ($okt->modules->moduleExists('news')) :
			echo newsHelpers::getCategories();
		endif; # fin Okatea : affichage de l'arbre des rubriques ?>

	</div><!-- #sidebar -->

	<div id="content">

		<?php # début Okatea : titre de la page
		if ($okt->page->hasTitle()) : ?>
		<h2 id="rubric_title"><?php echo html::escapeHtml($okt->page->getTitle()); ?></h2>
		<?php endif; # fin Okatea : titre de la page ?>

		<?php # début Okatea : affichage du contenu de la page
		$view['slots']->output('_content');
		# fin Okatea : affichage du contenu de la page ?>

	</div><!-- #content -->

	<footer>
		<ul>
			<li><?php echo html::escapeHtml($okt->config->company['com_name']) ?></li>
			<li><?php echo html::escapeHtml($okt->config->address['street']) ?></li>
			<?php if (!empty($okt->config->address['street_2'])) : ?><li><?php echo html::escapeHtml($okt->config->address['street_2']) ?></li><?php endif; ?>
			<li><?php echo html::escapeHtml($okt->config->address['code'].' '.$okt->config->address['city']) ?></li>
			<?php if (!empty($okt->config->address['tel'])) : ?><li><abbr title="<?php _e('c_c_Phone') ?>"><?php _e('c_c_Phone_abbr') ?></abbr> <?php echo html::escapeHtml($okt->config->address['tel']) ?></li><?php endif; ?>
			<?php if (!empty($okt->config->address['fax'])) : ?><li><?php _e('c_c_Fax') ?> <?php echo html::escapeHtml($okt->config->address['fax']) ?></li><?php endif; ?>
		</ul>

		<?php # début Okatea : affichage menu milieu
			echo $okt->navigation->render('MenuBottom');
		# fin Okatea : affichage menu milieu ?>

	</footer>
</div><!-- #page -->
