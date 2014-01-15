<?php
/**
 * upload.php
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */


	// HTTP headers for no cache etc
	header('Content-type: text/plain; charset=UTF-8');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// Settings
	$cleanupTargetDir = false; // Remove old files
	$maxFileAge = 60 * 60; // Temp file age in seconds

	// 5 minutes execution time
	@set_time_limit(5 * 60);
	// usleep(5000);

	# chargement d'Okatea
	require_once __DIR__.'/../../oktInc/public/prepend.php';


	// Get parameters
	$chunk = isset($_REQUEST['chunk']) ? $_REQUEST['chunk'] : 0;
	$chunks = isset($_REQUEST['chunks']) ? $_REQUEST['chunks'] : 0;
	$inputFileName = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';

	$gallery_id = isset($_REQUEST['gallery_id']) ? $_REQUEST['gallery_id'] : 0;

	$aItemLocalesData = array();

	foreach ($okt->languages->list as $aLanguage)
	{
		$aItemLocalesData[$aLanguage['code']] = array();
		$aItemLocalesData[$aLanguage['code']]['title'] = '';
	}

	if (empty($gallery_id)) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Empty gallery ID"}, "id" : "id"}');
	}

	$rsGalleryLocales = $okt->galleries->tree->getGalleryI18n($gallery_id);

	foreach ($okt->languages->list as $aLanguage)
	{
		while ($rsGalleryLocales->fetch())
		{
			if ($rsGalleryLocales->language == $aLanguage['code']) {
				$aItemLocalesData[$aLanguage['code']]['title'] = $rsGalleryLocales->title;
			}
		}

		if (!empty($_REQUEST['p_title'][$aLanguage['code']])) {
			$aItemLocalesData[$aLanguage['code']]['title'] = $_REQUEST['p_title'][$aLanguage['code']];
		}
	}

	$iNewId = $okt->galleries->items->addItem($okt->galleries->items->openItemCursor(array(
		'gallery_id' => $gallery_id,
		'active' => 1
	)), $aItemLocalesData);

	/*
	$item_data = array(
		'gallery_id' => $gallery_id,
		'visibility' => 1,
		'title' => $title,
		'legend' => '',
		'created_at' => '',
		'updated_at' => '',
		'title_tag' => '',
		'slug' => '',
		'meta_description' => '',
		'meta_keywords' => ''
	);

	$cursor = $okt->galleries->openCursor($item_data);

	# ajout dans la DB
	$date = date('Y-m-d H:i:s');
	$cursor->created_at = $date;
	$cursor->updated_at = $date;

	$cursor->legend = $okt->HTMLfilter($cursor->legend);

	$cursor->meta_description = html::clean($cursor->meta_description);

	$cursor->meta_keywords = html::clean($cursor->meta_keywords);

	if (!$cursor->insert()) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "unable to create item"}, "id" : "id"}');
	}

	# récupération de l'ID
	$iNewId = $okt->db->getLastID();

	# création du slug
	$okt->galleries->setItemSlug($iNewId);
	*/

	# define the target directory
	$targetDir = $okt->galleries->upload_dir.'img/items/'.$iNewId;

	if (!file_exists($targetDir)) {
		files::makeDir($targetDir,true);
	}

	$fileName = '1.'.pathinfo($inputFileName,PATHINFO_EXTENSION);

	# Remove old temp files
	if (is_dir($targetDir) && ($dir = opendir($targetDir)))
	{
		while (($file = readdir($dir)) !== false)
		{
			$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

			# Remove temp files if they are older than the max age
			if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) {
				@unlink($filePath);
			}
		}

		closedir($dir);
	}
	else {
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	}

	# Look for the content type header
	if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	}

	if (isset($_SERVER["CONTENT_TYPE"])) {
		$contentType = $_SERVER["CONTENT_TYPE"];
	}

	if (strpos($contentType, "multipart") !== false)
	{
		if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
		{
			# Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");

			if ($out)
			{
				# Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				}
				else {
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				}

				fclose($out);
				unlink($_FILES['file']['tmp_name']);
			}
			else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		}
		else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		}
	}
	else
	{
		# Open temp file
		$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
		if ($out)
		{
			# Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");

			if ($in)
			{
				while ($buff = fread($in, 4096)) {
					fwrite($out, $buff);
				}
			}
			else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}

			fclose($out);
		}
		else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
	}

	$aNewImagesInfos = $okt->galleries->items->getImageUploadInstance()->buildImagesInfos($iNewId, array(1 => $fileName));


	if (isset($aNewImagesInfos[1]))
	{
		$aNewItemImages = $aNewImagesInfos[1];
		$aNewItemImages['original_name'] = utf8_encode(basename($inputFileName));
	}
	else {
		$aNewItemImages = array();
	}

	$okt->galleries->items->updImages($iNewId, $aNewItemImages);

	// Return JSON-RPC response
	die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
