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
 *
 * BNF:
 *
 *   list  = or {“,” or}
 *   or    = array {“|” array}
 *   array = term [“[” [regexp([0-9]+\.\.[0-9]*|\.\.[0-9]+|[0-9]+)] “]”]
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
 *     matches non-empty arrays of booleans (0 to ∞).
 *
 *   boolean,object
 *     matches arrays containing a boolean and an object.
 *
 *   (string|(boolean,object))[]
 *     matches non-empty  arrays containing either strings  or arrays containing
 *     a boolean and an object.
 *
 *   int[10]
 *     matches arrays of exactly 10 integers.
 *
 *   int[0..]
 *     matches arrays with 0 or more integers.
 *
 *   int[0..5]
 *     matches array with 5 or less integers.
 */
abstract class Gallic_TypeChecker
{
	/**
	 * @param mixed $data
	 *
	 * @return boolean
	 */
	abstract function evaluate($data);

	/**
	 * Compiles a string pattern into a Gallic_TypeChecker.
	 *
	 * For performance concerns, the result is cached.
	 *
	 * @param string $pattern
	 *
	 * @return Gallic_TypeChecker
	 *
	 * @throw Gallic_Exception If the compilation failed.
	 */
	static function compile($pattern)
	{
		if (isset(self::$_cache[$pattern]))
		{
			return self::$_cache[$pattern];
		}

		self::$_pattern = $pattern;
		self::$_i = 0;
		self::$_n = strlen($pattern);

		return (self::$_cache[$pattern] = self::_list());
	}

	private static
		$_cache = array(),
		$_pattern,
		$_i,
		$_n;

	private static function _check($allowed)
	{
		if (self::$_i >= self::$_n)
		{
			return false;
		}

		$length = strlen($allowed);
		$cmp = substr_compare(self::$_pattern, $allowed, self::$_i, $length);
		if ($cmp !== 0)
		{
			return false;
		}

		self::$_i += $length;
		return true;
	}

	private static function _expect($allowed)
	{
		if (!self::_check($allowed))
		{
			throw new Gallic_Exception('“'.$allowed.'” not found at character '.
			                           self::$_i);
		}
	}

	private static function _regexp($re)
	{
		if (preg_match($re.'A', self::$_pattern, $matches, 0, self::$_i) === 1)
		{
			self::$_i += strlen($matches[0]);

			return $matches;
		}

		return false;
	}

	private static function _list()
	{
		$result = self::_or();

		if (!self::_check(','))
		{
			return $result;
		}

		$result = array($result);
		do
		{
			$result[] = self::_or();
		}
		while (self::_check(','));

		return new Gallic_TypeChecker_List($result);
	}

	private static function _or()
	{
		$result = self::_array();

		if (!self::_check('|'))
		{
			return $result;
		}

		$result = array($result);
		do
		{
			$result[] = self::_array();
		}
		while (self::_check('|'));

		return new Gallic_TypeChecker_Or($result);
	}

	private static function _array()
	{
		$result = self::_term();

		if (self::_check('['))
		{
			$min = 1;
			$max = null;

			if (($tmp = self::_regexp('/(\d*)(\.\.)?(\d*)/')) &&
			    ($tmp[0] !== ''))
			{
				if ($tmp[3] !== '')
				{
					$max = (int) $tmp[3];
					if ($tmp[1] !== '')
					{
						$min = (int) $tmp[1];
					}
				}
				elseif ($tmp[1] !== '')
				{
					$min = (int) $tmp[1];
					if ($tmp[2] === '')
					{
						$max = $min;
					}
				}
				else
				{
					throw new Gallic_Exception('invalid range at character '.self::$_i);
				}
			}

			self::_expect(']');

			return new Gallic_TypeChecker_Array($result, $min, $max);
		}

		return $result;
	}

	private static function _term()
	{
		if (self::_check('('))
		{
			$result = self::_list();
			self::_expect(')');
			return $result;
		}

		return self::_type();
	}

	private static function _type()
	{
		if ($result = self::_regexp('/~?[a-z_][a-z0-9_]*/i'))
		{
			return new Gallic_TypeChecker_Type($result[0]);
		}

		throw new Gallic_Exception('unexpected character');
	}
}

class Gallic_TypeChecker_List extends Gallic_TypeChecker
{
	/**
	 * @param Gallic_TypeChecker[2..] $pattern
	 */
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
	/**
	 * @param Gallic_TypeChecker[2..] $pattern
	 */
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
	/**
	 * @param integer      $min
	 * @param integer|null $max
	 */
	function __construct(Gallic_TypeChecker $pattern, $min, $max)
	{
		$this->_pattern = $pattern;
		$this->_min = $min;
		$this->_max = $max;
	}

	function evaluate($data)
	{
		if (!is_array($data))
		{
			return false;
		}

		$n = count($data);

		if ($n < $this->_min)
		{
			return false;
		}

		if (($this->_max !== null) && ($n > $this->_max))
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

	private
		$_pattern,
		$_min,
		$_max;
}

class Gallic_TypeChecker_Type extends Gallic_TypeChecker
{
	/**
	 * @param string $type
	 */
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

		if (!is_object($data))
		{
			return false;
		}

		return Gallic_Type::looksLike($data, substr($this->_type, 1));
	}

	private $_type;
}
