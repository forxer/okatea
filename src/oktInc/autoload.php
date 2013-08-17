<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @file
 * @addtogroup Okatea
 * @brief L'autoload Okatea.
 *
 * Rudimentaire mais efficace : un tableau ayant pour index les noms des classes
 * et pour valeur le chemin du fichier à inclure.
 *
 *
 */

$oktAutoloadPaths = array();

# cache
$oktAutoloadPaths['AbstractCache']			= OKT_CLASSES_PATH.'/cache/AbstractCache.php';
$oktAutoloadPaths['ArrayCache']				= OKT_CLASSES_PATH.'/cache/ArrayCache.php';
$oktAutoloadPaths['SessionCache']           = OKT_CLASSES_PATH.'/cache/SessionCache.php';
$oktAutoloadPaths['Cache']					= OKT_CLASSES_PATH.'/cache/Cache.php';
$oktAutoloadPaths['FileCache']				= OKT_CLASSES_PATH.'/cache/FileCache.php';
$oktAutoloadPaths['SingleFileCache']		= OKT_CLASSES_PATH.'/cache/SingleFileCache.php';

# core
$oktAutoloadPaths['oktAuth']				= OKT_CLASSES_PATH.'/core/class.oktAuth.php';
$oktAutoloadPaths['oktConfig']				= OKT_CLASSES_PATH.'/core/class.oktConfig.php';
$oktAutoloadPaths['oktController']			= OKT_CLASSES_PATH.'/core/class.oktController.php';
$oktAutoloadPaths['oktCore']				= OKT_CLASSES_PATH.'/core/class.oktCore.php';
$oktAutoloadPaths['oktDb']					= OKT_CLASSES_PATH.'/core/class.oktDb.php';
$oktAutoloadPaths['oktDbUtil']				= OKT_CLASSES_PATH.'/core/class.oktDbUtil.php';
$oktAutoloadPaths['oktDebug']				= OKT_CLASSES_PATH.'/core/class.oktDebug.php';
$oktAutoloadPaths['oktErrors']				= OKT_CLASSES_PATH.'/core/class.oktErrors.php';
$oktAutoloadPaths['oktLanguages']			= OKT_CLASSES_PATH.'/core/class.oktLanguages.php';
$oktAutoloadPaths['oktLogAdmin']			= OKT_CLASSES_PATH.'/core/class.oktLogAdmin.php';
$oktAutoloadPaths['oktTemplating']			= OKT_CLASSES_PATH.'/core/class.oktTemplating.php';
$oktAutoloadPaths['oktTriggers']			= OKT_CLASSES_PATH.'/core/class.oktTriggers.php';
$oktAutoloadPaths['oktUpdate']				= OKT_CLASSES_PATH.'/core/class.oktUpdate.php';

# db
$oktAutoloadPaths['cursor']					= OKT_CLASSES_PATH.'/db/class.cursor.php';
$oktAutoloadPaths['mysql']					= OKT_CLASSES_PATH.'/db/class.mysql.php';
$oktAutoloadPaths['oktMysqli']				= OKT_CLASSES_PATH.'/db/class.oktMysqli.php';
$oktAutoloadPaths['recordset']				= OKT_CLASSES_PATH.'/db/class.recordset.php';
$oktAutoloadPaths['xmlsql']					= OKT_CLASSES_PATH.'/db/class.xmlsql.php';

# form
$oktAutoloadPaths['oktForm']				= OKT_CLASSES_PATH.'/form/class.oktForm.php';
$oktAutoloadPaths['oktFormElement']			= OKT_CLASSES_PATH.'/form/class.oktForm.element.php';
$oktAutoloadPaths['oktFormElementExtraHtml'] 		= OKT_CLASSES_PATH.'/form/elements/class.oktForm.element.extra.html.php';
$oktAutoloadPaths['oktFormElementInputHidden'] 		= OKT_CLASSES_PATH.'/form/elements/class.oktForm.element.input.hidden.php';
$oktAutoloadPaths['oktFormElementInputPassword'] 	= OKT_CLASSES_PATH.'/form/elements/class.oktForm.element.input.password.php';
$oktAutoloadPaths['oktFormElementInputText'] 		= OKT_CLASSES_PATH.'/form/elements/class.oktForm.element.input.text.php';
$oktAutoloadPaths['oktFormElementTextarea'] 		= OKT_CLASSES_PATH.'/form/elements/class.oktForm.element.textarea.php';

# html
$oktAutoloadPaths['htmlBlockList']			= OKT_CLASSES_PATH.'/html/class.html.block.list.php';
$oktAutoloadPaths['breadcrumb']				= OKT_CLASSES_PATH.'/html/class.html.breadcrumb.php';
$oktAutoloadPaths['checkList']				= OKT_CLASSES_PATH.'/html/class.html.checklist.php';
$oktAutoloadPaths['htmlCss']				= OKT_CLASSES_PATH.'/html/class.html.css.php';
$oktAutoloadPaths['htmlJs']					= OKT_CLASSES_PATH.'/html/class.html.js.php';
$oktAutoloadPaths['htmlPage']				= OKT_CLASSES_PATH.'/html/class.html.page.php';
$oktAutoloadPaths['htmlStack']				= OKT_CLASSES_PATH.'/html/class.html.stack.php';

# images
$oktAutoloadPaths['oktImageUploadConfig']	= OKT_CLASSES_PATH.'/images/class.image.upload.config.php';
$oktAutoloadPaths['oktImageUpload']			= OKT_CLASSES_PATH.'/images/class.image.upload.php';

# libs
$oktAutoloadPaths['webFileManager']			= OKT_CLASSES_PATH.'/libs/lib.filemanager.php';
$oktAutoloadPaths['form']					= OKT_CLASSES_PATH.'/libs/lib.form.php';
$oktAutoloadPaths['formSelectOption']		= OKT_CLASSES_PATH.'/libs/lib.form.php';
$oktAutoloadPaths['password']				= OKT_CLASSES_PATH.'/libs/lib.password.php';
$oktAutoloadPaths['templateReplacement']	= OKT_CLASSES_PATH.'/libs/lib.template.replacement.php';
$oktAutoloadPaths['util']					= OKT_CLASSES_PATH.'/libs/lib.util.php';

# modules
$oktAutoloadPaths['oktModuleInstall']		= OKT_CLASSES_PATH.'/modules/class.module.install.php';
$oktAutoloadPaths['oktModule']				= OKT_CLASSES_PATH.'/modules/class.module.php';
$oktAutoloadPaths['oktModules']				= OKT_CLASSES_PATH.'/modules/class.modules.php';

# router
$oktAutoloadPaths['oktRoute']				= OKT_CLASSES_PATH.'/router/class.oktRoute.php';
$oktAutoloadPaths['oktRouter']				= OKT_CLASSES_PATH.'/router/class.oktRouter.php';

# themes
$oktAutoloadPaths['oktDefinitionsLessEditor'] = OKT_CLASSES_PATH.'/themes/class.oktDefinitionsLessEditor.php';
$oktAutoloadPaths['oktTemplatesSet']		= OKT_CLASSES_PATH.'/themes/class.oktTemplatesSet.php';
$oktAutoloadPaths['oktThemeBase']			= OKT_CLASSES_PATH.'/themes/class.oktThemeBase.php';
$oktAutoloadPaths['oktThemeEditor']			= OKT_CLASSES_PATH.'/themes/class.oktThemeEditor.php';
$oktAutoloadPaths['oktThemes']				= OKT_CLASSES_PATH.'/themes/class.oktThemes.php';

# tools
$oktAutoloadPaths['filemanager_old']		= OKT_CLASSES_PATH.'/tools/class.filemanager.php';
$oktAutoloadPaths['fileUpload']				= OKT_CLASSES_PATH.'/tools/class.files.upload.php';
$oktAutoloadPaths['filters']				= OKT_CLASSES_PATH.'/tools/class.filters.php';
$oktAutoloadPaths['imageTools']				= OKT_CLASSES_PATH.'/tools/class.image.tools.php';
$oktAutoloadPaths['iniFile']				= OKT_CLASSES_PATH.'/tools/class.ini.file.php';
$oktAutoloadPaths['nestedTree']				= OKT_CLASSES_PATH.'/tools/class.nested.tree.php';
$oktAutoloadPaths['nestedTreei18n']			= OKT_CLASSES_PATH.'/tools/class.nested.tree.i18n.php';
$oktAutoloadPaths['oktMail']				= OKT_CLASSES_PATH.'/tools/class.oktMail.php';
$oktAutoloadPaths['oktPublicAdminBar']		= OKT_CLASSES_PATH.'/tools/class.oktPublicAdminBar.php';
$oktAutoloadPaths['oktSimpleLogs']			= OKT_CLASSES_PATH.'/tools/class.oktSimpleLog.php';
$oktAutoloadPaths['oktMonthlyCalendar']		= OKT_CLASSES_PATH.'/tools/class.monthly.calendar.php';
$oktAutoloadPaths['pager']					= OKT_CLASSES_PATH.'/tools/class.pager.php';
$oktAutoloadPaths['parameterHolder']		= OKT_CLASSES_PATH.'/tools/class.parameter.holder.php';

$oktAutoloadPaths['Diff']					= OKT_CLASSES_PATH.'/tools/DifferenceEngine.php';
$oktAutoloadPaths['UnifiedDiffFormatter']	= OKT_CLASSES_PATH.'/tools/DifferenceEngine.php';
$oktAutoloadPaths['TableDiffFormatter']		= OKT_CLASSES_PATH.'/tools/DifferenceEngine.php';

# vendors
$oktAutoloadPaths['crypt']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.crypt.php';
$oktAutoloadPaths['dt']						= OKT_VENDOR_PATH.'/clearbricks/common/lib.date.php';
$oktAutoloadPaths['files']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.files.php';
$oktAutoloadPaths['path']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.files.php';
$oktAutoloadPaths['html']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.html.php';
$oktAutoloadPaths['http']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.http.php';
$oktAutoloadPaths['l10n']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.l10n.php';
$oktAutoloadPaths['text']					= OKT_VENDOR_PATH.'/clearbricks/common/lib.text.php';

$oktAutoloadPaths['filemanager']			= OKT_VENDOR_PATH.'/clearbricks/filemanager/class.filemanager.php';
$oktAutoloadPaths['imageMeta']				= OKT_VENDOR_PATH.'/clearbricks/image/class.image.meta.php';
$oktAutoloadPaths['netSocket']				= OKT_VENDOR_PATH.'/clearbricks/net/class.net.socket.php';
$oktAutoloadPaths['netHttp']				= OKT_VENDOR_PATH.'/clearbricks/net.http/class.net.http.php';
$oktAutoloadPaths['restServer']				= OKT_VENDOR_PATH.'/clearbricks/net.http/class.net.http.php';
$oktAutoloadPaths['restServer']				= OKT_VENDOR_PATH.'/clearbricks/rest/class.rest.php';
$oktAutoloadPaths['xmlTag']					= OKT_VENDOR_PATH.'/clearbricks/rest/class.rest.php';
$oktAutoloadPaths['fileZip']				= OKT_VENDOR_PATH.'/clearbricks/zip/class.zip.php';
$oktAutoloadPaths['fileUnzip']				= OKT_VENDOR_PATH.'/clearbricks/zip/class.unzip.php';

$oktAutoloadPaths['Mobile_Detect']			= OKT_VENDOR_PATH.'/Mobile-Detect/Mobile_Detect.php';

$oktAutoloadPaths['Ftp']					= OKT_VENDOR_PATH.'/ftp/ftp.class.php';

$oktAutoloadPaths['Rmail']					= OKT_VENDOR_PATH.'/Rmail/Rmail.php';
$oktAutoloadPaths['sfYaml']					= OKT_VENDOR_PATH.'/sfYaml/sfYaml.php';

$oktAutoloadPaths['PhpThumbFactory']		= OKT_VENDOR_PATH.'/phpthumb/ThumbLib.inc.php';

$oktAutoloadPaths['KLogger']			 	= OKT_VENDOR_PATH.'/KLogger/KLogger.php';
$oktAutoloadPaths['log']			 		= OKT_VENDOR_PATH.'/phplogclass/class.log.php';

$oktAutoloadPaths['lessc']			 		= OKT_VENDOR_PATH.'/lessphp/lessc.inc.php';

# minify tools
$oktAutoloadPaths['HTTP_ConditionalGet']	= OKT_ROOT_PATH.'/oktMin/lib/HTTP/ConditionalGet.php';
$oktAutoloadPaths['HTTP_Encoder']			= OKT_ROOT_PATH.'/oktMin/lib/HTTP/Encoder.php';


# intern autoload
function okt_autoload($name)
{
	global $oktAutoloadPaths;

	if (isset($oktAutoloadPaths[$name])) {
		require $oktAutoloadPaths[$name];
	}
}

spl_autoload_register('okt_autoload');

