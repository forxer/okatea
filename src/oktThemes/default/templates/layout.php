
<?php # début Okatea : ce template étend le template principal "main"
$view->extend('main');
# fin Okatea : ce template étend le template principal "main" ?>


<?php # début Okatea : titre de la page (graphic title)
if ($okt->page->hasTitle()) : ?>
<?php echo html::escapeHtml($okt->page->getTitle()); ?>
<?php endif; # fin Okatea : titre de la page (graphic title) ?>


<?php # début Okatea : titre SEO de la page (h1)
if ($okt->page->hasTitleSeo()) : ?>
<h1><?php echo html::escapeHtml($okt->page->getTitleSeo()); ?></h1>
<?php endif; # fin Okatea : titre SEO de la page (h1) ?>


<?php # début Okatea : affichage du contenu de la page
$view['slots']->output('_content');
# fin Okatea : affichage du contenu de la page ?>
