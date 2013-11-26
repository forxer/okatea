<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page d'accueil de l'administration
 *
 * @addtogroup Okatea
 *
 */

require __DIR__.'/../oktInc/admin/prepend.php';


# Suppression automatique des logs
$okt->logAdmin->deleteLogsDate($okt->config->log_admin['ttl_months']);


# Roundabout
$roundAboutOptions = new ArrayObject;
$roundAboutOptions['tilt'] = 4;
$roundAboutOptions['easing'] = 'easeOutElastic';
$roundAboutOptions['duration'] = 1400;

$okt->page->css->addCss('
	#roundabout img {
		display: block;
		margin: 0 auto;
	}
	.roundabout-holder {
		list-style: none;
		width: 75%;
		height: 15em;
		margin: 1em auto;
	}
	.roundabout-moveable-item {
		height: 4em;
		width: 8em;
		font-size: 2em;
		text-align: center;
		cursor: pointer;
	}
	.roundabout-moveable-item a {
		text-decoration: none;
	}
	.roundabout-moveable-item a:focus {
		outline: none;
	}
	.roundabout-in-focus {
		cursor: auto;
	}
');


# -- CORE TRIGGER : adminIndexRoundaboutOptions
$okt->triggers->callTrigger('adminIndexRoundaboutOptions', $okt, $roundAboutOptions);


$okt->page->roundabout($roundAboutOptions,'#roundabout');


# RoundAbout defaults Items
$roundAboutItems = new ArrayObject;

$sRoundAboutItemFormat = '<a href="%2$s">%3$s<span>%1$s</span></a>';

foreach ($okt->page->mainMenu->getItems() as $item)
{
	$roundAboutItems[] = sprintf($sRoundAboutItemFormat, $item['title'], $item['url'],
			($item['icon'] ? '<img src="'.$item['icon'].'" alt="" />' : ''));
}

if ($okt->modules->moduleExists('users'))
{
	$roundAboutItems[] = sprintf($sRoundAboutItemFormat, __('c_c_user_profile'), 'module.php?m=users&amp;action=profil&amp;id='.$okt->user->id,
			'<img src="'.OKT_PUBLIC_URL.'/img/admin/contact-new.png" alt="" />');
}

$roundAboutItems[] = sprintf($sRoundAboutItemFormat, __('c_c_user_Log_off_action'), 'index.php?logout=1',
	'<img src="'.OKT_PUBLIC_URL.'/img/admin/system-log-out.png" alt="" />');


# -- CORE TRIGGER : adminIndexRoundaboutItems
$okt->triggers->callTrigger('adminIndexRoundaboutItems', $okt, $roundAboutItems);


# konami code hehe ;)
$okt->page->js->addScript('
if (window.addEventListener) {
	var kkeys = [], konami = "38,38,40,40,37,39,37,39,66,65";
	window.addEventListener("keydown", function(e){
		kkeys.push(e.keyCode);
		if (kkeys.toString().indexOf( konami ) >= 0) {
			window.location = "http://jquery.com/";
		}
	}, true);
}
');


# News feed reader
if ($okt->config->news_feed['enabled'] && !empty($okt->config->news_feed['url'][$okt->user->language]))
{
	require_once OKT_VENDOR_PATH.'/simplepie/simplepie/autoloader.php';

	// We'll process this feed with all of the default options.
	$feed = new SimplePie();

	if (!is_dir(OKT_CACHE_PATH.'/feeds/')) {
		files::makeDir(OKT_CACHE_PATH.'/feeds/',true);
	}

	$feed->set_cache_location(OKT_CACHE_PATH.'/feeds/');

	// Set which feed to process.
	$feed->set_feed_url($okt->config->news_feed['url'][$okt->user->language]);

	// Run SimplePie.
	$feed_success = $feed->init();

	// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
	$feed->handle_content_type();

	$okt->page->css->addCss('
		#news_feed_list {
			height: 13em;
			width: 28%;
			overflow-y: scroll;
			overflow-x: hidden;
			padding-right: 0.8em;
			float: right;
		}
		#news_feed_list .ui-widget-header a {
			text-decoration: none;
		}
		#news_feed_list .ui-widget-header {
			margin-bottom: 0;
			padding: 0.3em 0.5em;
		}
		#news_feed_list .ui-widget-content {
			padding: 0.5em;
		}

		#roundabout-wrapper {
			float: left;
			width: 70%;
		}
	');
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php # updates notifications
if ($okt->config->update_enabled && $okt->checkPerm('is_superadmin') && is_readable(OKT_DIGESTS))
{
	$updater = new oktUpdate($okt->config->update_url, 'okatea', $okt->config->update_type, OKT_CACHE_PATH.'/versions');
	$new_v = $updater->check(util::getVersion());

	if ($updater->getNotify() && $new_v)
	{
		# locales
		l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.update');

		echo
		'<div id="updates-notifications"><h3>'.__('c_a_update').'</h3>'.
		'<p>'.sprintf(__('c_a_update_okatea_%s_available'),$new_v).'</p><ul>'.
		'<li><strong><a href="configuration.php?action=update">'.sprintf(__('c_a_update_upgrade_now'),$new_v).'</a></strong></li>'.
		'<li><a href="configuration.php?action=update&amp;hide_msg=1">'.__('c_a_update_remind_later').'</a></li></ul></div>';
	}
}
?>

<div class="ui-helper-clearfix">
	<?php # lecteur de flux d'actualités
	if ($okt->config->news_feed['enabled'] && $feed_success)  : ?>

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
		<ul id="roundabout"><li><?php echo implode("</li>\n<li>", (array)$roundAboutItems) ?></li></ul>
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
$okt->triggers->callTrigger('adminIndexHtmlContent', $okt); ?>

<?php # pied de page
require OKT_ADMIN_FOOTER_FILE; ?>
