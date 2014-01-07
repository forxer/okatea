<?php

$this->extend('layout');

# Module title tag
$okt->page->addTitleTag($okt->Pages->getTitle());

# Start breadcrumb
$okt->page->addAriane($okt->Pages->getName(), $view->generateUrl('Pages_index'));
