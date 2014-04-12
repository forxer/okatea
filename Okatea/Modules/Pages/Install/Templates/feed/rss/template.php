<?php
use Carbon\Carbon;

echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<rss version="2.0"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:wfw="http://wellformedweb.org/CommentAPI/"
xmlns:content="http://purl.org/rss/1.0/modules/content/"
xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo $view->escape($okt->page->getSiteTitle().' - '.$okt->module('Pages')->getTitle()) ?></title>
		<link><?php echo $view->escape($okt->request->getSchemeAndHttpHost().$view->generateUrl('pagesList')) ?></link>
		<atom:link href="<?php echo $view->escape($okt->request->getSchemeAndHttpHost().$view->generateUrl('pagesFeed')) ?>" rel="self" type="application/rss+xml"/>
		<description><?php echo $view->escape($okt->page->getSiteDescription()) ?></description>
		<language><?php echo $view->escape($okt->config->language) ?></language>
		<!-- <pubDate>{{tpl:BlogUpdateDate rfc822="1"}}</pubDate> -->
		<!-- <copyright>{{tpl:BlogCopyrightNotice encode_xml="1"}}</copyright> -->
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<generator>Okatea</generator>

	<?php # début Okatea : boucle sur la liste des pages
	while ($rsPagesList->fetch()) : ?>

		<?php # début Okatea : si on as accès en lecture à la page
		if ($rsPagesList->isReadable()) : ?>
		<item>
			<title><?php echo $view->escape($rsPagesList->title) ?></title>
			<link><?php echo $view->escape($okt->request->getSchemeAndHttpHost().$rsPagesList->url) ?></link>
			<!--
			<guid isPermaLink="false">{{tpl:EntryFeedID}}</guid>
			-->
			<pubDate><?php echo Carbon::parse($rsPagesList->created_at)->toRFC2822String() ?></pubDate>
			<!--<dc:creator><?php echo $view->escape($rsPagesList->author) ?></dc:creator>-->

			<?php if ($okt->module('Pages')->config->categories['enable'] && !empty($rsPagesList->category_title)) : ?>
			<category><?php echo $view->escape($rsPagesList->category_title) ?></category>
			<?php endif; ?>

			<description><?php echo $view->escape($rsPagesList->content) ?></description>

		</item>

		<?php endif; # début Okatea : si on as accès en lecture à la page ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des pages ?>

	</channel>
</rss>
