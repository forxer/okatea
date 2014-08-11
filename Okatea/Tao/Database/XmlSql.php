<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Orignal file from Dotclear 2.
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 */
namespace Okatea\Tao\Database;

/**
 * Génération de requêtes SQL via un fichier XML
 */
class XmlSql
{
	protected $job;

	protected $xml;

	protected $db;

	protected $_action;

	protected $_current_tag_cdata;

	protected $_subtable;

	protected $checklist;

	protected $process = null;

	/**
	 * Constructor.
	 *
	 * @param mysql $db
	 * @param string $xml
	 * @param checkList $checklist
	 * @return void
	 */
	public function __construct($db, $xml, $checklist, $process = null)
	{
		$this->xml = $xml;
		$this->db = $db;
		$this->checklist = $checklist;
		$this->job = [];
		$this->_current_tag_cdata = '';

		if ($process !== null)
		{
			$this->process = $process;
		}

		$this->_subtable = array(
			'test' => array(
				'sql' => null,
				'eq' => 'eq',
				'value' => null,
				'label' => null,
				'string' => null,
				'type' => 'err'
			),
			'request' => array(
				'label' => null,
				'string' => null,
				'type' => null,
				'sql' => null
			//				'process' 	=> 'install'
			)
		);
	}

	/**
	 */
	public function replace($needle, $str)
	{
		$this->xml = str_replace($needle, $str, $this->xml);
	}

	/**
	 */
	public function execute()
	{
		$this->parse();

		$test = true;

		foreach ($this->job as $k => $v)
		{
			if ($test === null)
			{
				$test = true;
			}

			if (!empty($v['request']['process']) && $v['request']['process'] !== $this->process)
			{
				continue;
			}

			$ok = $err = '';
			$silent = false;

			# Si $test n'est pas faux et qu'on a un test SQL et une action non silencieuse
			if ($test !== false && $v['test']['sql'] !== null && $v['test']['value'] !== null && $v['request']['type'] != 'silent')
			{
				$req = $v['test']['sql'];
				$err = sprintf($v['test']['label'], $v['test']['string']);

				if (($rs = $this->db->select($req)) === false)
				{
					$test = false;
					$err = $this->db->error();
				}
				else
				{
					if ($v['test']['eq'] == 'neq')
					{
						$test = ($rs->f(0) != $v['test']['value']);
					}
					else
					{
						$test = ($rs->f(0) == $v['test']['value']);
					}

					if ($test == false && $v['test']['type'] == 'wrn')
					{
						$test = null;
					}
				}
			}

			# Si le test est passé, on tente la requête
			if ($test === true)
			{
				$ok = sprintf($v['request']['label'], $v['request']['string']);

				$req = $v['request']['sql'];

				if ($this->db->execute($req) === false)
				{
					$test = false;
					$err = sprintf($v['request']['label'], $v['request']['string']) . ' - ' . $this->db->error();
				}
				else
				{
					$test = true;
				}

				if ($v['request']['type'] == 'silent')
				{
					$silent = true;
					$test = true;
					$ok = $err = '';
				}
			}
			elseif ($err == '')
			{
				$err = sprintf($v['request']['label'], $v['request']['string']);
			}

			if (!$silent)
			{
				$this->checklist->addItem($k, $test, $ok, $err);
			}
		}

		return $test;
	}

	/**
	 */
	private function parse()
	{
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, '_openTag', '_closeTag');
		xml_set_character_data_handler($parser, '_cdata');
		xml_parse($parser, $this->xml);
		xml_parser_free($parser);
	}

	/**
	 */
	private function _openTag($p, $tag, $attr)
	{
		if ($tag == 'action' && !empty($attr['id']))
		{
			$id = $this->_action = $attr['id'];
			$this->job[$id] = $this->_subtable;

			if (!empty($attr['label']))
			{
				$this->job[$id]['request']['label'] = $attr['label'];
			}

			if (!empty($attr['string']))
			{
				$this->job[$id]['request']['string'] = $attr['string'];
			}

			if (!empty($attr['type']))
			{
				$this->job[$id]['request']['type'] = $attr['type'];
			}

			if (!empty($attr['process']))
			{
				$this->job[$id]['request']['process'] = $attr['process'];
			}
		}
		elseif ($tag == 'test')
		{
			$id = $this->_action;

			if (!empty($attr['eq']))
			{
				$this->job[$id]['test']['eq'] = $attr['eq'];
			}

			if (!empty($attr['value']))
			{
				$this->job[$id]['test']['value'] = $attr['value'];
			}

			if (!empty($attr['label']))
			{
				$this->job[$id]['test']['label'] = $attr['label'];
			}

			if (!empty($attr['string']))
			{
				$this->job[$id]['test']['string'] = $attr['string'];
			}

			if (!empty($attr['type']))
			{
				$this->job[$id]['test']['type'] = $attr['type'];
			}
		}
	}

	/**
	 */
	private function _closeTag($p, $tag)
	{
		if ($tag == 'action')
		{
			$this->job[$this->_action]['request']['sql'] = trim($this->_current_tag_cdata);
		}
		elseif ($tag == 'test')
		{
			$this->job[$this->_action]['test']['sql'] = trim($this->_current_tag_cdata);
		}

		$this->_current_tag_cdata = '';
	}

	/**
	 */
	private function _cdata($p, $cdata)
	{
		$this->_current_tag_cdata .= $cdata;
	}
}
