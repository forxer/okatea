<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<?php 
# début Okatea : ce template étend le template principal "main"
$view->extend('main');
# fin Okatea : ce template étend le template principal "main" ?>


<?php 
# début Okatea : titre de la page (graphic title)
if ($okt->page->hasTitle())
:
	?>
<?php echo $view->escape($okt->page->getTitle()); ?>
<?php endif; # fin Okatea : titre de la page (graphic title) ?>


<?php 
# début Okatea : titre SEO de la page (h1)
if ($okt->page->hasTitleSeo())
:
	?>
<h1><?php echo $view->escape($okt->page->getTitleSeo()); ?></h1>
<?php endif; # fin Okatea : titre SEO de la page (h1) ?>


<?php 
# début Okatea : affichage du contenu de la page
$view['slots']->output('_content');
# fin Okatea : affichage du contenu de la page ?>
