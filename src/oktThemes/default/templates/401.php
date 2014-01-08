
<?php $view->extend('layout'); ?>

<?php # Title tag
$okt->page->addTitleTag($okt->page->getSiteTitleTag(null, $okt->page->getSiteTitle()));
$okt->page->addTitleTag(__('c_c_unauthorized')); ?>

<h1><?php _e('c_c_unauthorized') ?></h1>

<p><?php _e('c_c_access_is_denied') ?></p>
