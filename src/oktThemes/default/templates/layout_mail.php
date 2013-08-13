<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php _e('c_c_Email') ?> - <?php echo html::escapeHTML(util::getSiteTitle()) ?></title>
		<style type="text/css">
			body, div, p { margin: 0px; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; font-style: normal; color: #000000; }
			a, a:link, a:active, a:visited, a:focus, a:hover { font-weight: normal; color: #333333; }

			.page { width: 700px; display:block; margin: auto; }
			.contenu { padding: 15px; }
		</style>
	</head>
	<body>
		<div class="page">
			<img src="http://<?php echo html::escapeHTML($okt->config->domain).OKT_THEME ?>/images/mail/mail_header.jpg" />
		</div>
		<div class="page">
			<div class="contenu">
				<?php echo $body; ?>
			</div>
		</div>
		<div class="page">
			<img src="http://<?php echo html::escapeHTML($okt->config->domain).OKT_THEME ?>/images/mail/mail_footer.jpg" />
		</div>
	</body>
</html>