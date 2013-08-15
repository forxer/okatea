<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Pied de page "simple" des pages d'administration
 *
 * @addtogroup Okatea
 *
 */
?>

</div><!-- #page-simple -->

<script type="text/javascript" src="<?php echo $okt->config->app_path ?>oktMin/?g=js_admin"></script>
<?php echo $okt->page->js ?>

</body>
</html>

<?php

# Get buffer contents
$okt->page->content = ob_get_clean();


# -- CORE TRIGGER : adminBeforeSendContent
$okt->triggers->callTrigger('adminBeforeSendContent', $okt);

if (!$okt->config->admin_compress_output) {
	echo $okt->page->content;
}
else {
	$he = new HTTP_Encoder(array(
		'content' => $okt->page->content,
		'type' => 'text/html'
	));
	$he->encode();
	$he->sendAll();
}

exit;
