<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Misc;

use Swift_Validate;
use Okatea\Tao\Html\Escaper;

/**
 * Utilitaires divers et variés...
 *
 */
class Utilities
{
	/*
	 * Utilitaires sur les fichiers
	 *
	 */

	/**
	 * Indique si un répertoire contient des fichiers
	 *
	 * @param string $sDir
	 * @return boolean
	 */
	public static function dirHasFiles($sDir)
	{
		if (!is_dir($sDir)) {
			return false;
		}

		$bReturn = false;

		foreach (new \DirectoryIterator($sDir) as $oFileInfo)
		{
			if (!$oFileInfo->isDot())
			{
				$bReturn = true;
				break;
			}
		}

		return $bReturn;
	}

	/**
	 * Upload status
	 *
	 * Returns true if upload status is ok, throws an exception instead.
	 *
	 * @param array		$file		File array as found in $_FILES
	 * @throws Exception
	 * @return boolean
	 */
	public static function uploadStatus($aFile)
	{
		if (!isset($aFile['error'])) {
			throw new \Exception(__('c_c_upload_error_1'));
		}

		switch ($aFile['error'])
		{
			default:
			case UPLOAD_ERR_OK:
				return true;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new \Exception(__('c_c_upload_error_2'));

			case UPLOAD_ERR_PARTIAL:
				throw new \Exception(__('c_c_upload_error_3'));

			case UPLOAD_ERR_NO_FILE:
				throw new \Exception(__('c_c_upload_error_4'));

			case UPLOAD_ERR_NO_TMP_DIR:
				throw new \Exception(__('c_c_upload_error_5'));

			case UPLOAD_ERR_CANT_WRITE:
				throw new \Exception(__('c_c_upload_error_6'));
		}
	}

	/**
	 * Retourne le nom du sous-répertoire d'un chemin situé après un répertoire donnée.
	 *
	 * Exemple :
	 *
	 * $str = 'dir1/dir2/dir3/dir4/filename.ext';
	 * $dir = 'dir1/dir2';
	 *
	 * echo Utilities::getNextSubDir($str, $dir); // Outputs dir3
	 *
	 * @param string $sPath Le chemin complet
	 * @param string $sBasePath Le répertoire donné.
	 * @return string
	 */
	public static function getNextSubDir($sPath, $sBasePath)
	{
		if (is_file($sPath)) {
			$sPath = dirname($sPath);
		}

		$aPathComponents = array_filter(explode('/', str_replace('\\', '/', realpath($sPath))));
		$aBasePathComponents = array_filter(explode('/', str_replace('\\', '/', realpath($sBasePath))));

		foreach ($aPathComponents as $i=>$k)
		{
			if (!isset($aBasePathComponents[$i]) || $aBasePathComponents[$i] != $k) {
				return $k;
			}
		}
	}

	/*
	 * Utilitaires sur les chiffres
	 *
	 */

	/**
	 * Vérifie si $val est un entier.
	 * Contrairement à la fonction PHP cette fonction va retourner vrai pour '42'
	 * (autrement dit : elle ne tient pas compte du type)
	 *
	 * @param $val
	 * @return boolean
	 */
	public static function isInt($val)
	{
		return ($val !== true) && ((string)(int) $val) === ((string) $val);
	}

	/**
	 * Transforme un nombre formaté en un nombre manipulable par le système.
	 *
	 * @TODO: à revoir, moche...
	 * @param	string	number		Le nombre à formater
	 * @return string
	 */
	public static function sysNumber($number,$allow_negative=false)
	{
		$number = str_replace(__('c_c_number_thousands_separator'), '', $number);
		$number = str_replace(__('c_c_number_decimals_separator'), '.', $number);

		if (!is_numeric($number)) {
			return null;
		}

		if (!$allow_negative && $number < 0) {
			$number = -$number;
		}

		return $number;
	}

	/**
	 * Formatage d'un nombre selon les préférences locales
	 *
	 * Par exemple :
	 * 1 2058,38 en français
	 * 1,2058.38 en anglais
	 *
	 * @param	float	number		Le nombre à formater
	 * @param	integer	dec			Le nombre de décimaux à afficher
	 * @return	string
	 */
	public static function formatNumber($number, $dec=2)
	{
		return Escaper::html(number_format((float)$number, $dec, __('c_c_number_decimals_separator'), __('c_c_number_thousands_separator')));
	}

	/**
	 * Formatage d'un prix selon les préférences locales et le taux de conversion
	 *
	 * @param	float	price		Le prix à formater
	 * @param	float	taux		Le taux de conversion
	 * @param	integer	dec			Le nombre de décimaux
	 * @return	boolean
	 */
	public static function formatPrice($price, $taux=0, $dec=2)
	{
		if ($taux>0) {
			return self::formatNumber(self::ht2ttc($price,$taux), $dec);
		} else {
			return self::formatNumber($price, $dec);
		}
	}

	/**
	 * Calcule le prix TTC d'un prix HT selon un taux donné.
	 * Pric calculé selon la formule suivante :
	 *
	 * TTC = HT + (HT x TAUX)/100
	 *
	 * @param 	float ht		Le prix HT
	 * @param	float taux		Le taux de conversion
	 * @return float le prix TTC
	 */
	public static function ht2ttc($ht, $taux)
	{
		if ($taux == 0) {
			return $ht;
		}

		return ($ht+($ht*$taux)/100);
	}

	/**
	 * Calcule le prix TTC d'un prix HT selon un taux donné.
	 * Pric calculé selon la formule suivante :
	 *
	 * HT = TTC / (1 + TAUX/100)
	 *
	 * @param 	float ttc		Le prix TTC
	 * @param	float taux		Le taux de conversion
	 * @return float le prix HT
	 */
	public static function ttc2ht($ttc, $taux)
	{
		return ($ttc/(1+$taux/100));
	}

	/**
	 * Retourne le montant des mensualités d'un crédit à TAEG.
	 *
	 * @param float $k 		Capital/prix
	 * @param float $ti 	Taux d'interet
	 * @param float $ta 	Taux assurance
	 * @param integer $n 	Nombre de menusalités
	 * @return float
	 */
	public static function getMonthlyPaymentsOfTAEG($k, $ti, $ta, $n)
	{
		$t = (floatval($ti) + floatval($ta)) / 100;

		return (floatval($k) * $t/12) / (1 - pow(1 + $t/12, -intval($n)));
	}

	/**
	 * Retourne la notice de taille maximum d'upload.
	 *
	 * @return string
	 */
	public static function getMaxUploadSizeNotice()
	{
		return sprintf(__('c_c_maximum_file_size_%s'), self::l10nFileSize(self::getMaxUploadSize()));
	}

	/**
	 * Retourne la taille maximum d'upload.
	 *
	 * @return integer
	 */
	public static function getMaxUploadSize()
	{
		static $iMaxUploadSize = null;

		if ($iMaxUploadSize === null)
		{
			$iMaxUploadSize = \files::str2bytes(ini_get('upload_max_filesize'));
			$iMaxPostSize = \files::str2bytes(ini_get('post_max_size'));

			if ($iMaxPostSize < $iMaxUploadSize) {
				$iMaxUploadSize = $iMaxPostSize;
			}
		}

		return $iMaxUploadSize;
	}

	/**
	 * Human localized readable file size.
	 *
	 * @param integer	$size		Bytes
	 * @return array
	 */
	public static function l10nFileSize($size, $dec=2)
	{
		$aSize = self::getSize($size);

		return sprintf(__('c_c_x_bytes_size_in_'.$aSize['unit']), self::formatNumber($aSize['size'], $dec));
	}

	/**
	 * Human readable file size.
	 *
	 * Return an array like this
	 * array(
	 *     'size' => integer,
	 *     'unit' => string ('bytes', 'KB', 'MB'...)
	 * );
	 *
	 * @param integer	$size		Bytes
	 * @return array;
	 */
	public static function getSize($size)
	{
		static $kb = 1024;
		static $mb = 1048576;
		static $gb = 1073741824;
		static $tb = 1099511627776;

		if ($size < $kb) {
			return array('size' => $size, 'unit' => 'bytes');
		}
		elseif ($size < $mb) {
			return array('size' => ($size/$kb), 'unit' => 'KB');
		}
		elseif ($size < $gb) {
			return array('size' => ($size/$mb), 'unit' => 'MB');
		}
		elseif ($size < $tb) {
			return array('size' => ($size/$gb), 'unit' => 'GB');
		}
		else {
			return array('size' => ($size/$tb), 'unit' => 'TB');
		}
	}

	/**
	 * Calcul le nombre de d'heures, de minutes et de secondes
	 * à partir d'un nombre de seconde.
	 *
	 * @param integer $iSeconds
	 * @return array
	 */
	public static function secondsToTime($iSeconds)
	{
		# extract hours
		$iHours = floor($iSeconds / 3600);

		# extract minutes
		$iDivisorForMinutes = $iSeconds % 3600;
		$iMinutes = floor($iDivisorForMinutes / 60);

		# extract the remaining seconds
		$iDivisorForSeconds = $iDivisorForMinutes % 60;
		$iSeconds = ceil($iDivisorForSeconds);

		# return the final array
		return array(
			'h' => (integer)$iHours,
			'm' => (integer)$iMinutes,
			's' => (integer)$iSeconds
		);
	}

	/**
	 * Retourne le nombre de d'heures, de minutes et de secondes
	 * à partir d'un nombre de seconde pour l'afficher.
	 *
	 * @param integer $iSeconds
	 * @return string
	 */
	public static function displayableSecondsToTime($iSeconds)
	{
		if ($iSeconds < 1) {
			return '&lt; 1 '.__('c_c_second');
		}

		$a = self::secondsToTime($iSeconds);

		$s = '';

		if ($a['h'] > 0) {
			$s .= $a['h'].' '.($a['h']>1 ? __('c_c_hours') : __('c_c_hour')).', ';
		}

		if ($a['m'] > 0 || $a['h'] > 0) {
			$s .= $a['m'].' '.($a['m']>1 ? __('c_c_minutes') : __('c_c_minute')).' et ';
		}

		if ($a['s'] > 0 || $a['m'] > 0 || $a['h'] > 0) {
			$s .= $a['s'].' '.__('c_c_seconds');
		}

		return $s;
	}

	/*
	 * Utilitaires sur les textes
	 *
	 */

	/**
	 * Check email address
	 *
	 * Returns true if $email is a valid email address.
	 *
	 * @param string	$sEmail	Email string
	 * @return boolean
	 */
	public static function isEmail($sEmail)
	{
		return Swift_Validate::email($sEmail);
	}

	/**
	 * Retourne une chaine de caractère incrémentée
	 * en fonction d'une liste donnée
	 *
	 * @param array $list
	 * @param string $url
	 * @return string
	 */
	public static function getIncrementedString($list, $str, $prefix='')
	{
		foreach ($list as $k=>$v) {
			if (!preg_match('/^('.preg_quote($str,'/').')('.preg_quote($prefix,'/').'?)([0-9]*)$/',$v)) {
				unset($list[$k]);
			}
		}
		natsort($list);
		$t_url = end($list);

		if (preg_match('/^('.preg_quote($str,'/').')('.preg_quote($prefix,'/').'+)([0-9]+)$/',$t_url,$m)) {
			$i = (integer) $m[3];
		} else {
			$i = 1;
		}

		return $str.$prefix.($i+1);
	}

	public static function base64EncodeImage($filename, $filetype)
	{
		$handle = fopen($filename, 'rb');
		$imgbinary = fread($handle, filesize($filename));
		fclose($handle);

		return 'data:image/'.$filetype.';base64,'.base64_encode($imgbinary);
	}

	/**
	 * Force le téléchargement d'un fichier $fileName
	 *
	 * @param string $fileName
	 */
	public static function forceDownload($fileName=null)
	{
		# désactive le temps max d'exécution
		set_time_limit(0);

		# on a bien une demande de téléchargement de fichier
		if (empty($fileName)) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		$name = basename($fileName);

		# vérifie l'existence et l'accès en lecture au fichier
		if (!is_file($fileName) || !is_readable($fileName)) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		# calcul la taille total du fichier
		$size = filesize($fileName);

		# désactivation compression GZip
		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		# fermeture de la session
		session_write_close();

		# désactive la mise en cache
		header('Cache-Control: no-cache, must-revalidate');
		header('Cache-Control: post-check=0,pre-check=0');
		header('Cache-Control: max-age=0');
		header('Pragma: no-cache');
		header('Expires: 0');

		# force le téléchargement du fichier avec un beau nom
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename="'.$name.'"');

		# on indique au client la prise en charge de l'envoi de données par portion.
		header("Accept-Ranges: bytes");

		# par défaut, on commence au début du fichier
		$start = 0;

		# par défaut, on termine à la fin du fichier (envoi complet)
		$end = $size - 1;
		if (isset($_SERVER['HTTP_RANGE']))
		{
			# l'entête doit être dans un format valide
			if (!preg_match('#bytes=([0-9]+)?-([0-9]+)?(/[0-9]+)?#i', $_SERVER['HTTP_RANGE'], $m)) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				exit;
			}

			# modification de $start et $end et on vérifie leur validité
			$start = !empty($m[1]) ? (integer)$m[1] : null;
			$end = !empty($m[2]) ? (integer)$m[2] : $end;
			if (!$start && !$end || $end !== null && $end >= $size || $end && $start && $end < $start) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				exit;
			}

			# si $start n'est pas spécifié, on commence à $size - $end
			if ($start === null) {
				$start = $size - $end;
				$end -= 1;
			}

			# indique l'envoi d'un contenu partiel
			header('HTTP/1.1 206 Partial Content');

			# décrit quelle plage de données est envoyée
			header('Content-Range: '.$start.'-'.$end.'/'.$size);
		}

		# on indique bien la taille des données envoyées
		header('Content-Length: '.($end-$start+1));

		# ouverture du fichier en lecture et en mode binaire
		$f = fopen($fileName, 'rb');

		# on se positionne au bon endroit ($start)
		fseek($f, $start);

		# cette variable sert à connaître le nombre d'octet envoyé.
		$remainingSize = $end-$start+1;

		# calcul la taille des lots de données je choisi 4ko ou $remainingSize si plus petit que 4ko
		$length = $remainingSize < 4096 ? $remainingSize : 4096;

		while (($datas = fread($f, $length)) !== false)
		{
			# envoie des données vers le client
			echo $datas;

			# on a envoyé $length octets, on le soustrait alors du nombre d'octets restant
			$remainingSize -= $length;

			# si tout est envoyé, on quitte la boucle
			if ($remainingSize <= 0) {
				break;
			}

			# si reste moins de $length octets à envoyer, on le rédefinit en conséquence
			if ($remainingSize < $length) {
				$length = $remainingSize;
			}
		}

		fclose($f);
	}

	/**
	 * Retourne le type de media en fonction du type mime
	 *
	 * @param string $mime_type
	 * @return string
	 */
	public static function getMediaType($mime_type)
	{
			$type_prefix = explode('/',$mime_type);
			$type_prefix = $type_prefix[0];

			$media_type = null;

			switch ($type_prefix)
			{
				case 'image':
					$media_type = 'image';
					break;

				case 'audio':
					$media_type = 'audio';
					break;

				case 'text':
					$media_type = 'text';
					break;

				case 'video':
					$media_type = 'video';
					break;

				default:
					$media_type = 'blank';
			}

			switch ($mime_type)
			{
				case 'application/msword':
				case 'application/vnd.oasis.opendocument.text':
				case 'application/vnd.sun.xml.writer':
				case 'application/postscript':
					$media_type = 'document';
					break;

				case 'application/pdf':
					$media_type = 'pdf';
					break;

				case 'application/msexcel':
				case 'application/vnd.oasis.opendocument.spreadsheet':
				case 'application/vnd.sun.xml.calc':
					$media_type = 'spreadsheet';
					break;

				case 'application/mspowerpoint':
				case 'application/vnd.oasis.opendocument.presentation':
				case 'application/vnd.sun.xml.impress':
					$media_type = 'presentation';
					break;

				case 'application/x-debian-package':
				case 'application/x-gzip':
				case 'application/x-java-archive':
				case 'application/rar':
				case 'application/x-redhat-package-manager':
				case 'application/x-tar':
				case 'application/x-gtar':
				case 'application/zip':
					$media_type = 'package';
					break;

				case 'application/octet-stream':
					$media_type = 'executable';
					break;
				case 'application/x-shockwave-flash':
					$media_type = 'video';
					break;

				case 'application/ogg':
					$media_type = 'audio';
					break;

				case 'text/html':
					$media_type = 'html';
					break;
			}

			return $media_type;
	}

	public static function setDefaultModuleTpl($sModuleId, $sSection, $sTemplate)
	{
		global $okt;

		if (!$okt->modules->moduleExists($sModuleId) || !isset($okt->{$sModuleId}->config) || !isset($okt->{$sModuleId}->config->templates)) {
			return false;
		}

		$aTemplates = $okt->{$sModuleId}->config->templates;
		$aTemplates[$sSection]['default'] = $sTemplate;

		$okt->{$sModuleId}->config->templates = $aTemplates;

		$okt->{$sModuleId}->config->writeCurrent();

		return true;
	}

	/**
	 * Retourne la configuration des tailles des miniatures des images.
	 *
	 * @param string $sModuleId
	 * @param string $sWidth_min
	 * @param string $sheight_min
	 * @param string $iWidth
	 * @param string $iHeight
	 *
	 * @return string
	 */
	public static function setDefaultModuleImageSize($sModuleId, $aImages)
	{
		global $okt;

		if (!$okt->modules->moduleExists($sModuleId) || !isset($okt->{$sModuleId}->config) || !isset($okt->{$sModuleId}->config->images)) {
			return false;
		}

		$okt->{$sModuleId}->config->images = array_merge($okt->{$sModuleId}->config->images, $aImages);

		$okt->{$sModuleId}->config->writeCurrent();

		return true;
	}

	/**
	 * Retourne le temps d'execution du script
	 *
	 * @return float
	 */
	public static function getExecutionTime()
	{
		$time = explode(' ', microtime());
		$exec_time = sprintf('%.3f', ((float)$time[0] + (float)$time[1]) - OKT_START_TIME);

		return $exec_time;
	}

	/**
	 * Retourne la liste des fichiers cache d'Okatea
	 *
	 * @return array
	 */
	public static function getOktCacheFiles($bForce=false)
	{
		global $okt;

		static $aCacheFiles=null;

		if (is_array($aCacheFiles) && !$bForce) {
			return $aCacheFiles;
		}

		$aCacheFiles = array();
		foreach (new \DirectoryIterator($okt->options->get('cache_dir')) as $oFileInfo)
		{
			if ($oFileInfo->isDot() || in_array($oFileInfo->getFilename(),array('.svn','.htaccess','.gitkeep'))) {
				continue;
			}

			if ($oFileInfo->isDir())
			{
				foreach (new \DirectoryIterator($oFileInfo->getPathname()) as $oFileInfoInDir)
				{
					if ($oFileInfoInDir->isDot() || in_array($oFileInfoInDir->getFilename(),array('.svn','.htaccess','.gitkeep'))) {
						continue;
					}

					$aCacheFiles[] = $oFileInfo->getFilename().'/'.$oFileInfoInDir->getFilename();
				}
			}
			else {
				$aCacheFiles[] = $oFileInfo->getFilename();
			}

		}
		natsort($aCacheFiles);

		return $aCacheFiles;
	}

	/**
	 * Supprime les fichiers cache d'Okatea
	 *
	 * @return void
	 */
	public static function deleteOktCacheFiles()
	{
		global $okt;

		$aCacheFiles = self::getOktCacheFiles();

		foreach ($aCacheFiles as $file)
		{
			if (is_dir($okt->options->get('cache_dir').'/'.$file)) {
				\files::deltree($okt->options->get('cache_dir').'/'.$file);
			}
			else {
				unlink($okt->options->get('cache_dir').'/'.$file);
			}
		}
	}

	/**
	 * Retourne la liste des fichiers cache public d'Okatea
	 *
	 * @return array
	 */
	public static function getOktPublicCacheFiles($bForce=false)
	{
		global $okt;

		static $aCacheFiles=null;

		if (is_array($aCacheFiles) && !$bForce) {
			return $aCacheFiles;
		}

		$aCacheFiles = array();
		foreach (new \DirectoryIterator($okt->options->public_dir.'/cache') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || in_array($oFileInfo->getFilename(),array('.svn','.htaccess','index.html'))) {
				continue;
			}

			$aCacheFiles[] = $oFileInfo->getFilename();
		}
		natsort($aCacheFiles);

		return $aCacheFiles;
	}

	/**
	 * Supprime les fichiers cache public d'Okatea
	 *
	 * @return void
	 */
	public static function deleteOktPublicCacheFiles($bForce=false)
	{
		global $okt;

		$aCacheFiles = self::getOktPublicCacheFiles($bForce);

		foreach ($aCacheFiles as $file)
		{
			if (is_dir($okt->options->public_dir.'/cache/'.$file)) {
				\files::deltree($okt->options->public_dir.'/cache/'.$file);
			}
			else {
				unlink($okt->options->public_dir.'/cache/'.$file);
			}
		}
	}

	/**
	 * Generate a random key of length $len
	 *
	 * @param $len
	 * @param $readable
	 * @param $hash
	 * @return string
	 */
	public static function random_key($len, $readable = false, $hash = false)
	{
		$key = '';

		if ($hash) {
			$key = substr(sha1(uniqid(rand(), true)), 0, $len);
		}
		elseif ($readable)
		{
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

			for ($i = 0; $i < $len; ++$i) {
				$key .= substr($chars, (mt_rand() % strlen($chars)), 1);
			}
		}
		else {
			for ($i = 0; $i < $len; ++$i) {
				$key .= chr(mt_rand(33, 126));
			}
		}

		return $key;
	}

	/**
	 * Format un chemin d'application en supprimant et/ou laissant les slash de début et de fin.
	 *
	 * @param string $sPath
	 * @param boolean $bStartingSlash (true)
	 * @param boolean $bTrailingSlash (true)
	 * @return string
	 */
	public static function formatAppPath($sPath, $bStartingSlash=true, $bTrailingSlash=true)
	{
		$sPath = preg_replace('|/+$|', '', $sPath);
		$sPath = preg_replace('|^/+|', '', $sPath);

		if ($bStartingSlash) {
			$sPath = '/'.$sPath;
		}

		if ($bTrailingSlash) {
			$sPath = $sPath.'/';
		}

		$sPath = preg_replace('|/+|', '/', $sPath);

		return $sPath;
	}
}
