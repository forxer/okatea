
<?php use Tao\Utils as util; ?>

<ul id="menu_middle">
<?php while ($rsItems->fetch()) : ?>
	<li><a href="<?php echo util::escapeAttrHTML($rsItems->getUrl()) ?>"><?php
	echo html::escapeHTML($rsItems->title) ?></a></li>
<?php endwhile; ?>
</ul><!-- #menu_middle -->
