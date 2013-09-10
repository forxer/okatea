
<?php # début Okatea : ce template étend le template principal "main"
$this->extend('main');
# fin Okatea : ce template étend le template principal "main" ?>


<div id="page">
	<header>
		<div id="head">
			<div class="title"><a href="<?php echo util::escapeAttrHTML($okt->page->getBaseUrl()) ?>"><?php echo html::escapeHTML(util::getSiteTitle()) ?></a></div>
			<div class="description"><?php echo html::escapeHTML(util::getSiteDescription()) ?></div>
		</div><!-- #head -->

		<?php # début Okatea : affichage du switcher de langues
		if (!$okt->languages->unique) : ?>
		<ul id="lang-switcher">
			<?php foreach ($okt->languages->list as $aLanguage) : ?>
			<li id="lang_switcher_<?php echo html::escapeHTML($aLanguage['code']) ?>"><a href="<?php echo html::escapeHTML($aLanguage['code'].'/'.$okt->router->getPath()) ?>" title="<?php echo html::escapeHTML($aLanguage['title']) ?>"><img
			src="<?php echo OKT_PUBLIC_URL.'/img/flags/'.$aLanguage['img'] ?>" alt="<?php echo html::escapeHTML($aLanguage['title']) ?>" /></a></li>
			<?php endforeach; ?>
		</ul><!-- #lang-switcher -->
		<?php endif; # fin Okatea : affichage du switcher de langues ?>
	</header>

	<nav id="main-navigation">
		<?php echo $okt->navigation->render(4) ?>
	</nav>

	<div id="navigation-helpers">
		<?php # début Okatea : titre de la page (graphic title)
		if ($okt->page->hasTitle()) : ?>
		<div id="graphic-title"><?php echo html::escapeHtml($okt->page->getTitle()); ?></div>
		<?php endif; # fin Okatea : titre de la page (graphic title) ?>

		<?php # début Okatea : affichage du fil d'ariane
		$okt->page->breadcrumb->setHtmlSeparator(' &rsaquo; ');
		$okt->page->breadcrumb->display('<div id="breadcrumb"><em>'.__('c_c_user_you_are_here').'</em> %s</div>');
		# fin Okatea : affichage du fil d'ariane ?>
	</div><!-- #navigation-helpers -->

	<div id="main">

		<?php # début Okatea : titre SEO de la page (h1) ou titre si pas de titre SEO
		if ($okt->page->hasTitleSeo()) : ?>
		<h1><?php echo html::escapeHtml($okt->page->getTitleSeo()); ?></h1>
		<?php elseif ($okt->page->hasTitle()) : ?>
		<h1><?php echo html::escapeHtml($okt->page->getTitle()); ?></h1>
		<?php endif; # fin Okatea : titre SEO de la page (h1) ou titre si pas de titre SEO ?>

		<?php # début Okatea : affichage du contenu de la page
		echo $this->get('content');
		# fin Okatea : affichage du contenu de la page ?>

	</div><!-- #main -->

	<footer>
		<p><?php _e('Proudly propulsed by Okatea') ?></p>
	</footer>
</div><!-- #page -->