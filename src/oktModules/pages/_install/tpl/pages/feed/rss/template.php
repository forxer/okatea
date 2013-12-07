<?php
use Tao\Utils as util;

echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<rss version="2.0"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:wfw="http://wellformedweb.org/CommentAPI/"
xmlns:content="http://purl.org/rss/1.0/modules/content/"
xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo html::escapeHTML(util::getSiteTitle().' - '.$okt->pages->getTitle()) ?></title>
		<link><?php echo html::escapeHTML($okt->config->app_host.$okt->pages->config->url) ?></link>
		<atom:link href="<?php echo html::escapeHTML($okt->config->app_host.$okt->pages->config->feed_url) ?>" rel="self" type="application/rss+xml"/>
		<description><?php echo html::escapeHTML(util::getSiteDescription()) ?></description>
		<language><?php echo html::escapeHTML($okt->config->language) ?></language>
		<!-- <pubDate>{{tpl:BlogUpdateDate rfc822="1"}}</pubDate> -->
		<!-- <copyright>{{tpl:BlogCopyrightNotice encode_xml="1"}}</copyright> -->
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<generator>Okatea</generator>

	<?php # début Okatea : boucle sur la liste des pages
	while ($rsPagesList->fetch()) : ?>

		<?php # début Okatea : si on as accès en lecture à la page
		if ($rsPagesList->isReadable()) : ?>
		<item>
			<title><?php echo html::escapeHTML($rsPagesList->title) ?></title>
			<link><?php echo html::escapeHTML($okt->config->app_host.$rsPagesList->url) ?></link>
			<!--
			<guid isPermaLink="false">{{tpl:EntryFeedID}}</guid>
			-->
			<pubDate><?php echo dt::rfc822(strtotime($rsPagesList->created_at),$okt->config->timezone) ?></pubDate>
			<!--<dc:creator><?php echo html::escapeHTML($rsPagesList->author) ?></dc:creator>-->

			<?php if ($okt->pages->config->categories['enable'] && !empty($rsPagesList->category_title)) : ?>
			<category><?php echo html::escapeHTML($rsPagesList->category_title) ?></category>
			<?php endif; ?>

			<description><?php echo html::escapeHTML($rsPagesList->content) ?></description>

		</item>

		<?php endif; # début Okatea : si on as accès en lecture à la page ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des pages ?>

	</channel>
</rss>