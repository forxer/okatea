
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url.'/components/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : Google Maps API
$okt->page->js->addFile('http://maps.google.com/maps/api/js?sensor=false');
# fin Okatea : Google Maps API ?>


<?php # début Okatea : ajout du plugin Gmap3
$okt->page->js->addFile($okt->options->public_url.'/components/jquery-gmap3/gmap3.js');
# fin Okatea : ajout du plugin Gmap3 ?>


<?php # début Okatea : Gmap3 loader
$sJsGmap3Loader =
'$("#google_map").gmap3({
	map: {
		address: "'.$view->escapeJs($okt->contact->getAdressForGmap()).'",
		options: {
			center: true,
			zoom: '.$view->escapeJs($okt->contact->config->google_map['options']['zoom']).',
			mapTypeId: google.maps.MapTypeId.'.$view->escapeJs($okt->contact->config->google_map['options']['mode']).'
		}
	},
//	marker: {
//		address: "'.$view->escapeJs($okt->contact->getAdressForGmap()).'",
//		options:{
//			draggable: false
//		}
//	},
	infowindow:{
		address: "'.$view->escapeJs($okt->contact->getAdressForGmap()).'",
		options: {
			content: "<div id=\"infobulle\"><strong>'.$view->escapeJs((!empty($okt->config->company['com_name']) ? $okt->config->company['com_name'] : $okt->config->company['name'])).'</strong><br/> '.
				$view->escapeJs($okt->config->address['street']).'<br/> '.
				($okt->config->address['street_2'] != '' ? $view->escapeJs($okt->config->address['street_2']).'<br/>' : '').
				$view->escapeJs($okt->config->address['code']).' '.$view->escapeJs($okt->config->address['city']).'<br/> '.
				$view->escapeJs($okt->config->address['country']).'</div>"
		}
	}
});';
# fin Okatea : Gmap3 loader ?>


<?php # début Okatea : affichage du plan dans la page
$okt->page->js->addReady($sJsGmap3Loader);
# fin Okatea : affichage du plan dans la page ?>



<?php # début Okatea : élément HTML qui recevra le plan ?>
<div id="google_map"></div>
<?php # fin Okatea : élément HTML qui recevra le plan ?>

