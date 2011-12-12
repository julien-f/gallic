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
 * Base class for type checkers.
 */
abstract class Gallic_TypeChecker
{
	public static function check($expression, $value)
	{
		$tcc = new Gallic_TypeCheckerCompiler();

		return $tcc->compile($expression)->evaluate($value);
	}

	/**
	 * @param mixed $data
	 *
	 * @return boolean
	 */
	abstract function evaluate($data);
}

class Gallic_TypeChecker_List extends Gallic_TypeChecker
{
	function __construct(array $patterns)
	{
		$this->_patterns = $patterns;
	}

	function evaluate($data)
	{
		if (!is_array($data) || (count($data) !== count($this->_patterns)))
		{
			return false;
		}

		reset($data);
		reset($this->_patterns);

		while ($pattern = current($this->_patterns))
		{
			$entry = current($data);

			if (!$pattern->evaluate($entry))
			{
				return false;
			}

			next($this->_patterns);
			next($data);
		}

		return true;
	}

	private $_patterns;
}

class Gallic_TypeChecker_Or extends Gallic_TypeChecker
{
	function __construct(array $patterns)
	{
		$this->_patterns = $patterns;
	}

	function evaluate($data)
	{
		foreach ($this->_patterns as $pattern)
		{
			if ($pattern->evaluate($data))
			{
				return true;
			}
		}

		return false;
	}

	private $_patterns;
}

class Gallic_TypeChecker_Array extends Gallic_TypeChecker
{
	function __construct($pattern)
	{
		$this->_pattern = $pattern;
	}

	function evaluate($data)
	{
		if (!is_array($data))
		{
			return false;
		}

		foreach ($data as $entry)
		{
			if (!$this->_pattern->evaluate($entry))
			{
				return false;
			}
		}

		return true;
	}

	private $_pattern;
}

class Gallic_TypeChecker_Type extends Gallic_TypeChecker
{
	function __construct($type)
	{
		$this->_type = $type;
	}

	function evaluate($data)
	{
		if ($this->_type[0] !== '~')
		{
			return Gallic_Type::is($data, $this->_type);
		}

		return Gallic_Type::looksLike($data, substr($this->_type, 1));
	}

	private $_type;
}

/**
 * This class allows you to compile type checkers from advanced expressions.
 *
 * BNF:
 *
 *   list  = or {“,” or}
 *   or    = array {“|” array}
 *   array = term [“[]”]
 *   term  = “(” list “)” | type
 *   type  = [“~”] regexp([a-zA-Z_][a-zA-Z0-9_])
 *
 * Examples:
 *
 *   string
 *     matches only strings.
 *
 *   string|integer
 *     matches either strings or integers.
 *
 *   boolean[]
 *     matches arrays of booleans (0 to ∞).
 *
 *   boolean,object
 *     matches arrays containing a boolean and an object.
 *
 *   (string|(boolean,object))[]
 *     matches arrays  containing either strings or arrays  containing a boolean
 *     and an object.
 */
class Gallic_TypeCheckerCompiler
{
	/**
	 * Compiles a string pattern into a Gallic_TypeChecker.
	 *
	 * For performance concerns, the result is cached in this object.
	 *
	 * @param string $pattern
	 *
	 * @return Gallic_TypeChecker
	 *
	 * @throw Gallic_Exception If the compilation failed.
	 */
	function compile($pattern)
	{
		if (isset($this->_cache[$pattern]))
		{
			return $this->_cache[$pattern];
		}

		$this->_pattern = $pattern;
		$this->_i = 0;
		$this->_n = strlen($pattern);

		return ($this->_cache[$pattern] = $this->_list());
	}

	private
		$_cache = array(),
		$_pattern,
		$_i,
		$_n;

	private function _check($allowed)
	{
		if ($this->_i >= $this->_n)
		{
			return false;
		}

		$length = strlen($allowed);
		$cmp = substr_compare($this->_pattern, $allowed, $this->_i, $length);
		if ($cmp !== 0)
		{
			return false;
		}

		$this->_i += $length;
		return true;
	}

	private function _expect($allowed)
	{
		if (!$this->_check($allowed))
		{
			throw new Gallic_Exception('expected: '.$allowed);
		}
	}

	private function _regexp($re)
	{
		if (preg_match($re.'A', $this->_pattern, $match, 0, $this->_i) === 1)
		{
			$match = $match[0];

			$this->_i += strlen($match);

			return $match;
		}

		return false;
	}

	private function _list()
	{
		$result = array();

		do
		{
			$result[] = $this->_or();
		}
		while ($this->_check(','));

		if (count($result) === 1)
		{
			return reset($result);
		}

		return new Gallic_TypeChecker_List($result);
	}

	private function _or()
	{
		$result = array();

		do
		{
			$result[] = $this->_array();
		}
		while ($this->_check('|'));

		if (count($result) === 1)
		{
			return reset($result);
		}

		return new Gallic_TypeChecker_Or($result);
	}

	private function _array()
	{
		$result = $this->_term();

		if ($this->_check('['))
		{
			$this->_expect(']');
			return new Gallic_TypeChecker_Array($result);
		}

		return $result;
	}

	private function _term()
	{
		if ($this->_check('('))
		{
			$result = $this->_list();
			$this->_expect(')');
			return $result;
		}

		return $this->_type();
	}

	private function _type()
	{
		if ($result = $this->_regexp('/~?[a-z_][a-z0-9_]*/i'))
		{
			return new Gallic_TypeChecker_Type($result);
		}

		throw new Gallic_Exception('unexpected character');
	}
}
