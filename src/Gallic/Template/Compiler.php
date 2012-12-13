<?php
/**
 * This file is a part of Gallic.
 *
 * Gallic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gallic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gallic. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Julien Fontanet <julien.fontanet@isonoe.net>
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 *
 * @package Gallic
 */

/**
 * @experimental
 *
 * @todo Add support for default filters.
 */
final class Gallic_Template_Compiler
{
	function __construct()
	{}

	function compile(array $template, $class_name, $extended_class = null)
	{
		$this->_extends = (bool) $extended_class;

		if ($this->_extends)
		{
			$code =
				'<?php if (!class_exists('.var_export($extended_class, true).
				', false)) require(dirname(__FILE__).DIRECTORY_SEPARATOR.'.
				var_export($extended_class, true).'.\'.php\'); class '.$class_name.
				' extends '.$extended_class;
		}
		else
		{
			$code = '<?php class '.$class_name.' extends Gallic_Template';
		}
		$code .= '{protected $_b = array();protected function _render(array $v, array $fi, array $fu){';
		if ($this->_extends)
		{
			$code .= 'ob_start()';
		}
		$code .= "?>\n".$this->_sequence($template['nodes']).'<?php ';
		if ($this->_extends)
		{
			$code .= 'ob_end_clean();parent::_render($v, $fi, $fu);';
		}
		$code .= '}}';

		return $code;
	}

	//--------------------------------------

	private $_extends = false;

	private function _visit(array $node)
	{
		$m = array($this, '_v_'.$node[0]);

		if (is_callable($m))
		{
			return $m($node);
		}

		throw new Gallic_Exception('unsupported node '.$node[0]);
	}

	private function _sequence(array $nodes)
	{
		$code = '';
		foreach ($nodes as $node)
		{
			$code .= $this->_visit($node);
		}
		return $code;
	}

	//--------------------------------------

	private function _block($name)
	{
		return '$this->_b['.var_export($name, true).']';
	}

	private function _filter($name)
	{
		return '$fi['.var_export($name, true).']';
	}

	private function _func($name)
	{
		return '$fu['.var_export($name, true).']';
	}

	private function _var(array $components)
	{
		$code = '$v['.var_export($components[0], true).']';
		$components[0] = '';

		return $code.implode('->', $components);
	}

	//--------------------------------------

	private function _v_text($text)
	{
		return str_replace('<?', '<<?php ?>?', $text['data']);
	}

	private function _v_var($var)
	{
		$code = '<?php echo ';
		foreach ($var['filters'] as $filter)
		{
			$code .= $this->_filter($filter['name']).'(';

			if ($filter['modifier'])
			{
				$code .= var_export($filter['modifier']['data'], true).',';
			}
		}
		return
			$code.
			$this->_var($var['name']).
			str_repeat(')', count($var['filters'])).
			" ?>\n";
	}

	private function _v_comment($comment)
	{
		return '';
	}

	private function _v_if($if)
	{
		$code =
			'<?php if(!empty('.$this->_var($if['condition']['data']).")){?>\n".
			$this->_sequence($if['then']).'<?php }';
		foreach ($if['elseif'] as $elseif)
		{
			$code .=
				'elseif(!empty('.$this->_var($elseif['condition']['data']).")){?>\n".
				$this->_sequence($if['then']).'<?php }';
		}
		if ($if['else'])
		{
			$code .= "else{?>\n".$this->_sequence($if['else']).'<?php }';
		}
		return $code."?>\n";
	}

	private function _v_foreach($foreach)
	{
		$code = '<?php foreach('.$this->_var($foreach['list']).' as ';
		if (isset($foreach['key']))
		{
			$code .= $this->_var($foreach['key']).' => ';
		}
		return
			$code.
			$this->_var($foreach['value'])."){?>\n".
			$this->_sequence($foreach['nodes'])."<?php }?>\n";
	}

	// @todo
	private function _v_block($block)
	{
		$code =
			'<?php if(isset('.$this->_block($block['name']).')){echo '.
			$this->_block($block['name']).';}else{';
		if ($this->_extends)
		{
			$code .= 'ob_start();';
		}
		$code .= "?>\n".$this->_sequence($block['nodes']).'<?php ';
		if ($this->_extends)
		{
			$code .= $this->_block($block['name']).'=ob_get_clean();';
		}
		$code .= "}?>\n";

		return $code;
	}

	private function _v_func($func)
	{
		$code = '<?php echo '.$this->_func($func['name']).'($this, array(';
		foreach ($func['parameters'] as $param)
		{
			$arg = $param['value'];
			$arg = ($arg[0] === 'varName')
				? $this->_var($arg['data'])
				: var_export($arg['data'], true)
				;

			$code .= var_export($param['name'], true).'=>'.$arg.',';
		}
		return $code.")) ?>\n";
	}
}
