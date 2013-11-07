
<ul>
<?php while ($rsItems->fetch()) : ?>
	<li><a href="<?php echo html::escapeHTML(($rsItems->type == 1 ? $rsItems->url : $okt->page->getBaseUrl().$rsItems->url)) ?>"><?php
	echo html::escapeHTML($rsItems->title) ?></a></li>
<?php endwhile; ?>
</ul>
