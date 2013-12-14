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

namespace Tao\Forms\Statics;

/**
 * Option element creation helpers
 *
 */
class SelectOption
{
	public $name;
	public $value;
	public $class_name;
	public $html;

	protected $option = '<option value="%1$s"%3$s>%2$s</option>';

	/**
	 * Option constructor
	 *
	 * @param string	$name		Option name
	 * @param string	$value		Option value
	 * @param string	$class_name	Element class name
	 * @param string	$html		Extra HTML attributes
	 */
	public function __construct($name, $value, $class_name='', $html='')
	{
		$this->name = $name;
		$this->value = $value;
		$this->class_name = $class_name;
		$this->html = $html;
	}

	/**
	 * Option renderer
	 *
	 * Returns option HTML code
	 *
	 * @param boolean	$default	Option is selected
	 * @return string
	 */
	public function render($default)
	{
		$attr = $this->html;
		$attr .= $this->class_name ? ' class="'.$this->class_name.'"' : '';

		if ($this->value == $default) {
			$attr .= ' selected="selected"';
		}

		return sprintf($this->option, $this->value, $this->name,$attr).PHP_EOL;
	}
}
