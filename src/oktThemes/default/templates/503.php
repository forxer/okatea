
<?php $view->extend('layout'); ?>

<?php
$okt->page->addTitleTag($okt->page->getSiteTitleTag(null, $okt->page->getSiteTitle()));
$okt->page->addTitleTag(__('c_c_service_unavailable'));

$okt->page->breadcrumb->add(__('c_c_service_unavailable'));
?>

<h1><?php _e('c_c_service_unavailable') ?></h1>

<p><?php _e('c_c_server_currently_unavailable') ?></p>
