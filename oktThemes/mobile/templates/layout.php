
<?php # début Okatea : ce template étend le template principal "main"
$this->extend('main');
# fin Okatea : ce template étend le template principal "main" ?>


<div data-role="page">

	<div data-role="header">
		<?php # début Okatea : titre de la page (graphic title)
		if ($okt->page->hasTitle()) : ?>
		<h1><?php echo html::escapeHtml($okt->page->getTitle()); ?></h1>
		<?php # fin Okatea : titre de la page (graphic title)

		# début Okatea : titre SEO de la page (h1)
		elseif ($okt->page->hasTitleSeo()) : ?>
		<h1><?php echo html::escapeHtml($okt->page->getTitleSeo()); ?></h1>
		<?php endif; # fin Okatea : titre SEO de la page (h1) ?>

	</div><!-- /header -->

	<div data-role="content">

		<?php # début Okatea : affichage du contenu de la page
		echo $this->get('content');
		# fin Okatea : affichage du contenu de la page ?>

	</div><!-- /content -->

	<div data-role="footer">
		<h4>Footer content</h4>
	</div><!-- /footer -->

</div><!-- /page -->