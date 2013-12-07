<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Pied de page des pages d'administration
 *
 * @addtogroup Okatea
 *
 */

use Tao\Utils as util;

?>
	</section><!-- #content -->
</div><!-- #main -->

<nav><?php echo $mainMenuHtml['html'] ?></nav>

<?php # init footer content
$aFooterContent = new ArrayObject;

$aFooterContent[10] = sprintf(__('c_c_proudly_propulsed_%s'), '<a href="http://okatea.org/">Okatea</a>');

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
	<img src="<?php echo OKT_PUBLIC_URL ?>/img/ajax-loader/big-circle-ball.gif" alt="" class="preload" />
	<?php echo implode('&nbsp;', $aFooterContent) ?></p>
</footer>
</div><!-- #page -->

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

echo $okt->page->content;

