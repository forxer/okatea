<?php

# fichier nécessaire pour afficher un article des questions
require_once dirname(__FILE__).'/oktModules/faq/inc/public/question.php';


# affichage du template
echo $okt->tpl->render('faq_question_tpl', array(
	'faqQuestion' => $faqQuestion,
	'faqQuestionLocales' => $faqQuestionLocales
));
