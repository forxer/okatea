
<ul id="menu_middle">
<?php while ($rsItems->fetch()) : ?>
	<li><a href="<?php echo $view->escapeHtmlAttr($rsItems->getUrl()) ?>"><?php
	echo $view->escape($rsItems->title) ?></a></li>
<?php endwhile; ?>
</ul><!-- #menu_middle -->
