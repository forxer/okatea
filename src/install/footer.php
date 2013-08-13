<?php
/**
 * Le pied de page
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */
?>

</div><!-- #content -->


<footer>
	<p id="footer" class="clearb ui-widget ui-corner-all ui-state-default">
	Okatea
	<?php if ($oktVersion) { echo ' version <strong>'.$oktVersion.'</strong> '; } ?>
	<?php if ($oktRevision) { echo ' revision <em>'.$oktRevision.'</em> '; } ?>
	</p><!-- #footer -->
</footer>
</div><!-- #main-right -->
</div><!-- #page -->

<?php echo $oHtmlPage->js ?>

</body>
</html>