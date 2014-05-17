<?php

# okatea
require_once __DIR__ . '/oktInc/public/prepend.php';

use Okatea\Tao\Forms\Simple\Form;

$form = new Form(array(
	'action' => 'test_form.php'
));

$form->html('<fieldset>')
	->html('<legend>Bla bla bla</legend>')
	->text(array(
	'label' => 'Un premier champ texte'
), array(
	'id' => 'text1',
	'name' => 'text1',
	'size' => 60,
	'maxlength' => 100
))
	->text(array(
	'label' => 'Un deuxième champ texte'
), array(
	'id' => 'text2',
	'name' => 'text2',
	'size' => 40,
	'maxlength' => 50
))
	->password(array(
	'label' => 'Mot de passe'
), array(
	'id' => 'password',
	'name' => 'password',
	'size' => 60,
	'maxlength' => 50
))
	->html('</fieldset>')
	->html('<fieldset>')
	->html('<legend>Bli bli bli</legend>')
	->textarea(array(
	'label' => 'Un  textarea'
), array(
	'id' => 'textarea',
	'name' => 'textarea',
	'cols' => 60,
	'rows' => 6
))
	->html('</fieldset>');

echo $form->render();