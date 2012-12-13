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
 * Grammar:
 *
 *   tpl = node+ /$/
 *
 *   node = text | var | comment | if | foreach | block | func | extends
 *
 *   text    = /(?:[^{]|\{\{)+/
 *   var     = '{' varName ('|' filterName (':' literal))* '}'
 *   comment = /\{\*.*?\*}/
 *   if      = '{if' /\s+/ varName '}' node+
 *               ('{elseif' /\s+/ varName '}' node+)*
 *               ('{else}' node+)?
 *            '{/if}'
 *   foreach = '{foreach' /\s+/ varName /\s+/ 'as' /\s+/ (varName /\s+/ '=>' /\s+/)? varName '}'
 *              node+
 *            '{/foreach}'
 *   block   = '{block' /\s+/ blockName'}'
 *              node*
 *            '{/block' (/\s+/ blockName)? '}'
 *   func    = '{' /[a-z0-9_-]+/i (/\s+/ parameter)* '}'
 *   extends = '{extends' /\s+/ string '}'
 *
 *   filterName = /[a-z0-9_-]+/i
 *   blockName  = /[a-z0-9_-]+/i
 *
 *   parameter = /[a-z0-9_-]+/i '=' literal
 *
 *   literal  = boolean | number | string | varName
 *   boolean  = 'true' | 'false'
 *   number   = /[+-]?(?:[1-9][0-9]*|0)(?:\.[0-9]*[1-9])?/
 *   string   = /"([^"]*)"/
 *   varName  = /\$([a-z0-9_-]+(?:\.(?1))?)/i
 */
final class Gallic_Template_Parser
{
	function __construct()
	{}

	function parse($string, $start = null, $end = null)
	{
		$this->_s = $string;
		$this->_i = $start !== null ? $start : 0;
		$this->_n = $end !== null ? $end : strlen($this->_s);

		$this->_extendedTemplate = null;

		$this->_p_tpl(/* out */ $tpl);

		return $tpl;
	}

	//--------------------------------------

	private $_s;

	private $_i;

	private $_n;

	private $_extendedTemplate;

	private function _assert($string)
	{
		$this->_check($string)
			or $this->_fail('expected “'.$string.'”');
	}

	private function _check($string)
	{
		$length = strlen($string);

		if (($this->_i < $this->_n)
		    && (substr_compare($this->_s, $string, $this->_i, $length) === 0))
		{
			$this->_i += $length;
			return true;
		}

		return false;
	}

	private function _fail($reason)
	{
		throw new Gallic_Exception($reason.' at '.$this->_i);
	}

	private function _regex($re, &$match = null)
	{
		if (!preg_match($re.'As', $this->_s, $match, 0, $this->_i))
		{
			return false;
		}

		$this->_i += strlen($match[0]);
		return true;
	}

	private function _sequence($rule, $min = null, $max = null)
	{
		($min !== null)
			or $min = 0;
		($max !== null)
			or $max = -1;

		$sequence = array();
		while (($max-- !== 0) && $this->{'_p_'.$rule}(/* out */ $tmp))
		{
			$sequence[] = $tmp;
		}

		if (count($sequence) < $min)
		{
			$this->_fail($rule.' expected');
		}

		return $sequence;
	}

	//--------------------------------------

	function _p_tpl(&$tpl)
	{
		$tpl = array(
			'tpl',
			'nodes'   => $this->_sequence('node', 1),
			'extends' => $this->_extendedTemplate
		);

		($this->_i >= $this->_n)
			or $this->_fail('unexpected end');

		return true;
	}

	//--------------------------------------

	function _p_node(&$node)
	{
		return
			$this->_p_text(/* out */ $node)
			|| $this->_p_var(/* out */ $node)
			|| $this->_p_comment(/* out */ $node)
			|| $this->_p_if(/* out */ $node)
			|| $this->_p_foreach(/* out */ $node)
			|| $this->_p_block(/* out */ $node)
			|| $this->_p_func(/* out */ $node)
			|| $this->_p_extends(/* out */ $node)
			;
	}

	function _p_text(&$text)
	{
		if (!$this->_regex('/(?:[^{]|\{\{)+/', $match))
		{
			return false;
		}

		$text = array(
			'text',
			'data' => str_replace('{{', '{', $match[0])
		);

		return true;
	}

	function _p_var(&$var)
	{
		if (!$this->_check('{$'))
		{
			return false;
		}

		--$this->_i;
		$this->_p_varName(/* out */ $varName)
			or $this->_fail('variable expected');

		$var = array(
			'var',
			'name'    => $varName['data'],
			'filters' => array()
		);

		while ($this->_check('|'))
		{
			$this->_p_filterName(/* out */ $filterName)
				or $this->_fail('filter expected');
			if ($this->_check(':'))
			{
				$this->_p_literal(/* out */ $modifier)
					or $this->_fail('literal expected');
			}
			else
			{
				$modifier = null;
			}

			$var['filters'][] = array(
				'name'     => $filterName['data'],
				'modifier' => $modifier
			);
		}

		$this->_assert('}');

		return true;
	}

	function _p_comment(&$comment)
	{
		if (!$this->_regex('/\{\*.*?\*\}/', $match))
		{
			return false;
		}

		$comment = array(
			'comment',
			'data' => $match
		);
		return true;
	}

	function _p_if(&$if)
	{
		if (!$this->_regex('/\{if\s+/'))
		{
			return false;
		}
		$if = array(
			'if',
			'condition' => null,
			'then'      => array(),
			'elseif'    => array(),
			'else'      => array(),
		);
		$this->_p_varName(/* out */ $if['condition'])
			or $this->_fail('condition expected');
		$this->_assert('}');
		$if['then'] = $this->_sequence('node', 1);

		while ($this->_regex('/\{elseif\s+/'))
		{
			$elseif = array(
				'condition' => null,
				'then'      => array()
			);
			$this->_p_varName(/* out */ $elseif['condition'])
				or $this->_fail('condition expected');
			$elseif['then'] = $this->_sequence('node', 1);

			$if['elseif'][] = $elseif;
		}

		if ($this->_check('{else}'))
		{
			$if['else'] = $this->_sequence('node', 1);
		}

		$this->_assert('{/if}');

		return true;
	}

	function _p_foreach(&$foreach)
	{
		if (!$this->_regex('/\{foreach\s+/'))
		{
			return false;
		}

		$foreach = array(
			'foreach',
			'list'  => null,
			'key'   => null,
			'value' => null,
			'nodes' => array()
		);

		$this->_p_varName(/* out */ $varName)
			or $this->_fail('variable expected');
		$foreach['list'] = $varName['data'];
		$this->_regex('/\s+as\s+/')
			or $this->_fail('“as” expected');
		$this->_p_varName(/* out */ $varName)
			or $this->_fail('variable expected'); // @todo key.sub should not be allowed.
		if ($this->_regex('/\s+=>\s+/'))
		{
			$foreach['key'] = $varName['data'];
			$this->_p_varName(/* out */ $varName)
				or $this->_fail('variable expected'); // @todo value.sub should not be allowed.
		}
		$foreach['value'] = $varName['data'];
		$this->_assert('}');

		$foreach['nodes'] = $this->_sequence('node', 1);

		$this->_assert('{/foreach}');

		return true;
	}

	function _p_block(&$block)
	{
		if (!$this->_regex('/\{block\s+/'))
		{
			return false;
		}

		$block = array(
			'block',
			'name'  => null,
			'nodes' => array()
		);

		$this->_p_blockName(/* out */ $blockName)
			or $this->_fail('block name expected');
		$block['name'] = $blockName['data'];

		$this->_assert('}');

		$block['nodes'] = $this->_sequence('node');

		if ($this->_regex('#\{/block\s+#'))
		{
			$this->_p_blockName(/* out */ $blockName)
				or $this->_fail('block name expected');

			if ($blockName['data'] !== $block['name'])
			{
				$this->_fail('incorrect block name, '.$block['name'].' expected');
			}

			$this->_assert('}');
		}
		else
		{
			$this->_assert('{/block}');
		}

		return true;
	}

	private function _p_func(&$func)
	{
		if (!$this->_regex('/\{(?!else)(?!extends)([a-z0-9_-]+)/i', $match))
		{
			return false;
		}

		$func = array(
			'func',
			'name'       => $match[1],
			'parameters' => array(),
		);

		while ($this->_regex('/\s+/'))
		{
			$this->_p_parameter(/* out */ $func['parameters'][])
				or $this->_fail('parameter expected');
		}
		$this->_assert('}');

		return true;
	}

	private function _p_extends(&$extends)
	{
		if (!$this->_regex('/\{extends\s+/'))
		{
			return false;
		}

		($this->_extendedTemplate === null)
			or $this->_fail('only one extend allowed per template');

		$this->_p_string($string)
			or $this->_fail('string expected');

		$this->_assert('}');

		$this->_extendedTemplate = $string['data'];

		// Returns “nothing”.
		$extends = array('text', 'data' => '');
		return true;
	}

	//--------------------------------------

	private function _p_filterName(&$filterName)
	{
		if (!$this->_regex('/[a-z0-9_-]+/i', $match))
		{
			return false;
		}

		$filterName = array(
			'filterName',
			'data' => $match[0]
		);
		return true;
	}

	private function _p_blockName(&$blockName)
	{
		if (!$this->_regex('/[a-z0-9_-]+/i', $match))
		{
			return false;
		}

		$blockName = array(
			'blockName',
			'data' => $match[0]
		);
		return true;
	}

	//--------------------------------------

	private function _p_parameter(&$parameter)
	{
		if (!$this->_regex('/([a-z0-9_-]+)=/i', $match))
		{
			return false;
		}

		$parameter = array(
			'parameter',
			'name'  => $match[1],
			'value' => null
		);
		$this->_p_literal(/* out */ $parameter['value'])
			or $this->_fail('literal expected');

		return true;
	}

	//--------------------------------------

	private function _p_literal(&$literal)
	{
		return
			$this->_p_boolean(/* out */ $literal)
			|| $this->_p_number(/* out */ $literal)
			|| $this->_p_string(/* out */ $literal)
			|| $this->_p_varName(/* out */ $literal)
			;
	}

	private function _p_boolean(&$boolean)
	{
		if ($this->_check('true'))
		{
			$data = true;
		}
		elseif ($this->_check('false'))
		{
			$data = false;
		}
		else
		{
			return false;
		}

		$boolean = array(
			'boolean',
			'data' => $data
		);
		return true;
	}

	private function _p_number(&$number)
	{
		if (!$this->_regex('/[+-]?(?:[1-9][0-9]*|0)(\.[0-9]*[1-9])?/', $match))
		{
			return false;
		}

		$number = array(
			'number',
			'data' => isset($match[1]) ? (float) $match : (int) $match[0]
		);
		return true;
	}

	private function _p_string(&$string)
	{
		if (!$this->_regex('/"([^"]+)"/', $match))
		{
			return false;
		}

		$string = array(
			'string',
			'data' => $match[1]
		);
		return true;
	}

	private function _p_varName(&$varName)
	{
		if (!$this->_regex('/\$([a-z0-9_-]+(?:\.(?1))?)/i', $match))
		{
			return false;
		}

		$varName = array(
			'varName',
			'data' => explode('.', $match[1])
		);
		return true;
	}
}
