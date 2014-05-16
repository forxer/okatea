<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Builder/Admin/Templates/Builder');

$okt->page->css->addCss('
#editor_wrapper {
	width: 80%;
	float: left;
}
	.CodeMirror {
		border: 1px solid #eee;
		width: 100%;
		height: 400px;
		font-size: 1.2em;
	}
	.CodeMirror-focused .cm-matchhighlight {
		background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAFklEQVQI12NgYGBgkKzc8x9CMDAwAAAmhwSbidEoSQAAAABJRU5ErkJggg==);
		background-position: bottom;
		background-repeat: repeat-x;
	}
	.CodeMirror-activeline-background {
		background: #e8f2ff !important;
	}
	.CodeMirror-fullscreen {
		display: block;
		position: absolute;
		top: 0; left: 0;
		width: 100%;
		z-index: 9999;
	}
#treecontrol a {
	text-decoration: none;
	padding: 0 1em
}
#files_list {
	width: 17%;
	float: right;
	padding-left: 1em;
	word-wrap: break-word;
}
#editor_actions {
	list-style: none;
}
	#editor_actions li {
		display: inline-block;
		margin-right: 1em;
	}
');

# CSS
$okt->page->css->addFile($okt->options->public_url . '/components/codemirror/lib/codemirror.css');
$okt->page->css->addFile($okt->options->public_url . '/components/codemirror/addon/dialog/dialog.css');

# JS
$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/lib/codemirror.js');
$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/mode/yaml/yaml.js');
$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/mode/clike/clike.js');
$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/mode/php/php.js');

$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/addon/search/search.js');
$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/addon/search/searchcursor.js');

$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/addon/search/match-highlighter.js');

$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/addon/dialog/dialog.js');

$okt->page->js->addFile($okt->options->public_url . '/components/codemirror/addon/selection/active-line.js');

$okt->page->js->addScript('

		function isFullScreen(cm) {
			return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
		}
		function winHeight() {
			return window.innerHeight || (document.documentElement || document.body).clientHeight;
		}
		function setFullScreen(cm, full) {
			var wrap = cm.getWrapperElement();
			if (full) {
				wrap.className += " CodeMirror-fullscreen";
				wrap.style.height = winHeight() + "px";
				document.documentElement.style.overflow = "hidden";
			} else {
				wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
				wrap.style.height = "";
				document.documentElement.style.overflow = "";
			}
			cm.refresh();
		}
		CodeMirror.on(window, "resize", function() {
			var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
			if (!showing) return;
			showing.CodeMirror.getWrapperElement().style.height = winHeight() + "px";
		});

		var config_editor = CodeMirror.fromTextArea(document.getElementById("config_editor"), {
			mode:  "text/x-yaml",
			indentUnit: 4,
			indentWithTabs: true,
			styleActiveLine: true,
			lineNumbers: true,
			lineWrapping: true,
			highlightSelectionMatches: true,
			extraKeys: {
				"F11": function(cm) {
					setFullScreen(cm, !isFullScreen(cm));
				},
				"Esc": function(cm) {
					if (isFullScreen(cm)) setFullScreen(cm, false);
				}
			}
		});

		var options_editor = CodeMirror.fromTextArea(document.getElementById("options_editor"), {
			mode:  "text/x-php",
			indentUnit: 4,
			indentWithTabs: true,
			styleActiveLine: true,
			lineNumbers: true,
			lineWrapping: true,
			highlightSelectionMatches: true,
			extraKeys: {
				"F11": function(cm) {
					setFullScreen(cm, !isFullScreen(cm));
				},
				"Esc": function(cm) {
					if (isFullScreen(cm)) setFullScreen(cm, false);
				}
			}
		});

	');

$okt->page->toggleWithLegend('options_title', 'options', array(
	'cookie' => 'oktBuilderOptions'
));

?>

<form
	action="<?php echo $view->generateUrl('Builder_index', array('step' => $stepper->getCurrentStep())) ?>"
	method="post">

	<p><?php _e('m_builder_step_config_1') ?></p>

	<h3><?php _e('m_builder_step_config_2') ?></h3>

	<p><?php _e('m_builder_step_config_3') ?></p>

	<ul>
		<li><?php printf(__('m_builder_step_config_4'), '<code>app_path</code>', '<code>/</code>')?></li>
		<li><?php printf(__('m_builder_step_config_4'), '<code>domain</code>', '<code>\'\'</code>')?></li>
		<li><?php printf(__('m_builder_step_config_5'), '<code>maintenance public / maintenance admin</code>', '<code>false</code>')?></li>
	</ul>

	<textarea id="config_editor" name="config_editor" rows="35" cols="97"><?php echo $sConfig ?></textarea>

	<h3 id="options_title"><?php _e('m_builder_step_config_6') ?></h3>

	<div id="options">
		<textarea id="options_editor" name="options_editor" rows="35"
			cols="97"><?php echo $sOptions ?></textarea>
	</div>

	<p><?php echo form::hidden('form_sent', 1)?>
	<input type="submit" value="<?php _e('c_c_next') ?>" />
	</p>
</form>

