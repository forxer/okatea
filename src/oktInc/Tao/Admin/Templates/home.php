
<?php $view->extend('layout'); ?>

<?php if (!empty($sNewVersion)) : ?>
<div id="updates-notifications"><h3><?php _e('c_a_update') ?></h3>
	<p><?php printf(__('c_a_update_okatea_%s_available'), $sNewVersion) ?></p>
	<ul>
		<li><strong><a href="<?php echo $view->generateUrl('config_update') ?>"><?php sprintf(__('c_a_update_upgrade_now'), $sNewVersion) ?></a></strong></li>
		<li><a href="<a href="<?php echo $view->generateUrl('config_update') ?>?hide_msg=1"><?php _e('c_a_update_remind_later') ?></a></li>
	</ul>
</div>
<?php endif; ?>

<div class="ui-helper-clearfix">
	<?php # lecteur de flux d'actualités
	if ($bFeedSuccess) : ?>

	<div id="news_feed_list">
		<?php foreach ($feed->get_items() as $item): ?>
		<div class="ui-widget">
			<h3 class="ui-widget-header ui-corner-top"><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></h3>
			<div class="ui-widget-content ui-corner-bottom"><?php echo $item->get_description(); ?></div>
		</div>
		<?php endforeach; ?>
	</div><!-- #news_list -->

	<?php endif; ?>


	<!--[if lte IE 8]>
	<style type="text/css">
	/* <![CDATA[ */
	.roundabout-moveable-item img,
	.roundabout-moveable-item a {
		background-color: #FDFEFE;
	}
	/* ]]> */
	</style>
	<![endif]-->

	<div id="roundabout-wrapper">
		<ul id="roundabout"><li><?php echo implode("</li>\n<li>", $aRoundAboutItems) ?></li></ul>
	</div>
</div>
<!--[if lte IE 8]>
<div id="alertMsie">
	<p>Cette interface a été testée avec succès sous les navigateurs suivants :
	<ul>
		<li><a href="http://www.mozilla-europe.org/fr/products/firefox/">Mozilla Firefox</a></li>
		<li><a href="http://www.google.com/chrome/?hl=fr">Google Chrome</a></li>
		<li><a href="http://www.apple.com/fr/safari/">Apple Safari</a></li>
		<li><a href="http://www.opera.com/download/">Opera</a></li>
	</ul>
	<p>Il semblerait que vous utilisez Internet Explorer 8 ou inférieur,
	pour une meilleure expérience utilisateur sur l’interface d’administration
	nous recommandons d'utiliser un <a href="http://browsehappy.com/">navigateur alternatif</a>.</p>
</div>
<![endif]-->

<?php # -- CORE TRIGGER : adminIndexHtmlContent
$okt->triggers->callTrigger('adminIndexHtmlContent'); ?>
