<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

?>

<ul>
<?php while ($rsItems->fetch()) : ?>
	<li><a href="<?php echo $view->escapeHtmlAttr($rsItems->getUrl()) ?>"><?php
	echo $view->escape($rsItems->title) ?></a></li>
<?php endwhile; ?>
</ul>
