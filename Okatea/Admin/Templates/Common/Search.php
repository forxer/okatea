<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

# Autocomplétion du formulaire de recherche
if (!empty($sAutocompleteSrc))
{
	$okt->page->js->addReady('
		$("#search").autocomplete({
			source: "' . $sAutocompleteSrc . '",
			minLength: 2
		});
	');
}

# CSS
$okt->page->css->addCss('
.ui-autocomplete {
	max-height: 150px;
	overflow-y: auto;
	overflow-x: hidden;
}
.search_form p {
	margin: 0;
}
#search {
	background: transparent url(' . $okt['public_url'] . '/img/admin/preview.png) no-repeat center right;
}
');

if (!empty($sSearch))
{
	$okt->page->js->addFile($okt['public_url'] . '/plugins/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}

?>

<form action="<?php echo $sFormAction ?>" method="get" id="search_form"
	class="search_form">
	<p>
		<label for="search"><?php echo $sSearchLabel; ?></label>
	<?php echo form::text('search', 20, 255, (!empty($sSearch) ? $view->escape($sSearch) : '')); ?>

	<input type="submit" name="search_submit" id="search_submit"
			value="<?php _e('c_c_action_ok') ?>" />
	</p>
</form>
