<?php

# paramètres personnalisés de selection des questions internationalisées
//$aFaqCustomParams = array();


# fichier nécessaire pour afficher une liste de questions internationalisées
require_once __DIR__ . '/oktModules/faq/inc/public/list_questions.php';

# affichage du template
echo $okt['tpl']->render(($okt->faq->config->enable_categories ? 'faq_list_questions_with_categories_tpl' : 'faq_list_questions_tpl'), array(
	'faqList' => $faqList
));
