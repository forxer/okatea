<?php
/**
 * Pied de page des pages d'administration
 *
 * @addtogroup Okatea
 *
 */
?>
	</section><!-- #content -->
</div><!-- #main -->

<nav><?php echo $mainMenuHtml['html'] ?></nav>

<?php # init footer content
$aFooterContent = new ArrayObject;

$aFooterContent[10] = 'okatea';

if (OKT_DEBUG) {
	$aFooterContent[20] = util::getVersion();
}

# -- CORE TRIGGER : adminFooterContent
$okt->triggers->callTrigger('adminFooterContent', $okt, $aFooterContent);


# sort items of footer content
$aFooterContent->ksort();

# remove empty values of footer content
$aFooterContent = array_filter((array)$aFooterContent);

?>
<footer>
	<p id="footer" class="clearb ui-widget ui-corner-all ui-state-default">
	<img src="<?php echo OKT_COMMON_URL ?>/img/ajax-loader/big-circle-ball.gif" alt="" class="preload" />
	<?php echo implode('&nbsp;', $aFooterContent) ?></p>
</footer>
</div><!-- #page -->

<script type="text/javascript" src="<?php echo $okt->config->app_path ?>oktMin/?g=js_admin"></script>
<?php echo $okt->page->js ?>

<?php # -- CORE TRIGGER : adminBeforeHtmlBodyEndTag
$okt->triggers->callTrigger('adminBeforeHtmlBodyEndTag', $okt); ?>

</body>
</html>

<?php

# Get buffer contents
$okt->page->content = ob_get_clean();

# -- CORE TRIGGER : adminBeforeSendContent
$okt->triggers->callTrigger('adminBeforeSendContent', $okt);

// feature disabled because non calling of shutdown
//if (!$okt->config->admin_compress_output) {
	echo $okt->page->content;
//}
//else {
//
//	$he = new HTTP_Encoder(array(
//		'content' => $okt->page->content,
//		'type' => 'text/html'
//	));
//	$he->encode();
//	$he->sendAll();
//}
