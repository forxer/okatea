<?php
use Okatea\Tao\L10n\Date;

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"> <channel>
<title><?php echo $view->escape($okt->page->getSiteTitle().' - '.$okt->module('News')->getTitle()) ?></title>
<link><?php echo $view->escape($okt->request->getSchemeAndHttpHost().$view->generateUrl('newsList')) ?></link>
<atom:link
	href="<?php echo $view->escape($okt->request->getSchemeAndHttpHost().$view->generateUrl('newsFeed')) ?>"
	rel="self" type="application/rss+xml" /> <description><?php echo $view->escape($okt->page->getSiteDescription()) ?></description>
<language><?php echo $view->escape($okt->config->language) ?></language>
<!-- <pubDate>{{tpl:BlogUpdateDate rfc822="1"}}</pubDate> --> <!-- <copyright>{{tpl:BlogCopyrightNotice encode_xml="1"}}</copyright> -->
<docs>http://blogs.law.harvard.edu/tech/rss</docs> <generator>Okatea</generator>

	<?php 
# début Okatea : boucle sur la liste des actualités
	while ($rsPostsList->fetch())
	:
		?>

		<?php 
# début Okatea : si on as accès en lecture à l'article
		if ($rsPostsList->isReadable())
		:
			?>
		<item>
<title><?php echo $view->escape($rsPostsList->title) ?></title>
<link><?php echo $view->escape($okt->request->getSchemeAndHttpHost().$rsPostsList->url) ?></link>
<!--
			<guid isPermaLink="false">{{tpl:EntryFeedID}}</guid>
			--> <pubDate><?php echo Date::parse($rsPostsList->created_at)->toRSSString() ?></pubDate>
<dc:creator><?php echo $view->escape($rsPostsList->author) ?></dc:creator>

			<?php if ($okt->module('News')->config->categories['enable'] && $rsPostsList->rubrique_name) : ?>
			<category><?php echo $view->escape($rsPostsList->rubrique_name) ?></category>
			<?php endif; ?>

			<description><?php echo $view->escape($rsPostsList->content) ?></description>

			<?php
			
$image = $rsPostsList->getFirstImageInfo();
			if (! empty($image) && isset($image['square_url']))
			:
				?>
			<!--
			<enclosure url="<?php echo $view->escape($okt->request->getSchemeAndHttpHost().$image['square_url']) ?>"
			length="<?php echo filesize($image['square_file']) ?>"
			type="<?php echo $image['square_type'] ?>" />
			-->
			<?php endif; ?>

			<!--
			<comments>{{tpl:EntryURL}}#comment-form</comments>
			<wfw:comment>{{tpl:EntryURL}}#comment-form</wfw:comment>
			<wfw:commentRss>{{tpl:BlogFeedURL}}/comments/{{tpl:EntryID}}</wfw:commentRss>
			--> </item>
		<?php endif; # début Okatea : si on as accès en lecture à l'article ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des actualités ?>

	</channel> </rss>
