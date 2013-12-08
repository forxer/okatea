
<?php use Tao\Misc\Utilities as util; ?>

<ul>
<?php while ($rsItems->fetch()) : ?>
	<li><a href="<?php echo util::escapeAttrHTML($rsItems->getUrl()) ?>"><?php
	echo html::escapeHTML($rsItems->title) ?></a></li>
<?php endwhile; ?>
</ul>
