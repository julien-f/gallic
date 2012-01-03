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
 * This namespace contains the core data and functionalities of Gallic.
 */
final class Gallic
{
	/**
	 * Current version of the framework.
	 *
	 * @var string
	 */
	const VERSION = '0.2.0';

	/**
	 * Current version of the framework as integer.
	 *
	 * The value is equal to: (major*100 + minor)*100 + revision
	 *
	 * @var integer
	 */
	const VERSION_ID = 200;

	/**
	 * Initializes Gallic.
	 *
	 * This method is automatically called just at the end of this file and MUST
	 * not be called afterwards.
	 */
	static function _init()
	{
		/*
		 * This loader  will search  in the current  directory only  for classes
		 * beginning with “Gallic”.
		 */
		$loader = new Gallic_ClassLoader_PrefixFilter(
			new Gallic_ClassLoader_Standard(array(
				defined('__DIR__') ? __DIR__ : dirname(__FILE__),
			)),
			array('Gallic')
		);

		spl_autoload_register(array($loader, 'load'));
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {}
}

////////////////////////////////////////////////////////////////////////////////

final class Gallic_OS
{
	static function isWindows()
	{
		return (PHP_SHLIB_SUFFIX === 'dll');
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {}
}

////////////////////////////////////////////////////////////////////////////////

/**
 * This namespace provides paths related functions.
 */
final class Gallic_Path
{
	/**
	 * Checks if a path is absolute.
	 *
	 * @param string $path
	 *
	 * @return boolean
	 */
	static function isAbsolute($path)
	{
		if (Gallic_OS::isWindows())
		{
			// A absolute Windows path matches regexp(^[a-zA-Z]:).
			return ((strlen($path) >= 2) &&
			        ($path[1] === ':') &&
			        (65 <= ($c = ord($path[0]))) &&
			        ($c <= 122));
		}

		return ($path[0] === '/');
	}

	/**
	 * Joins multiple paths.
	 *
	 * Note:  This function  postulates that  all arguments  but the  parent are
	 * relative paths.
	 *
	 * @param string $parent
	 * @param string $path1
	 * @param string $...
	 *
	 * @return string
	 */
	static function join($parent, $path1)
	{
		// Prior  to PHP  5.3, func_get_args()  cannot  be used  directly as  an
		// argument.
		$args = func_get_args();

		return implode(DIRECTORY_SEPARATOR, $args);
	}


	/**
	 * Normalizes a path, which means, removes every '//', '.' and '..'.
	 *
	 * Note: This function does not handle correctly non Unix paths.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	static function normalize($path)
	{
		if ($path === '')
		{
			return '.';
		}

		$path = explode(DIRECTORY_SEPARATOR, $path);
		$absolute = ($path[0] === '');
		$out = array();
		foreach ($path as $component)
		{
			if (($component === '') || ($component === '.'))
			{
				// Current dir.
			}
			elseif (($component !== '..') ||
			        (($prev = end($out)) === '..') ||
			        ($prev === $absolute))
			{
				/*
				 * - Normal component
				 * - Parent directory and:
				 *   - the previous is already parent directory;
				 *   - there is no previous and the path is not absolute.
				 */
				array_push($out, $component);
			}
			else
			{
				// Parent directory (remove the parent).
				array_pop($out);
			}
		}

		if ($out === array())
		{
			if ($absolute)
			{
				return DIRECTORY_SEPARATOR;
			}
			return '.';
		}

		$path = implode(DIRECTORY_SEPARATOR, $out);
		return ($absolute ? DIRECTORY_SEPARATOR.$path : $path);
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {}
}

////////////////////////////////////////////////////////////////////////////////

/**
 * This namespace provides files' related operations.
 */
final class Gallic_File
{
	/**
	 * Finds a file which satisfies a predicate.
	 *
	 * If  the path  is relative,  the file  will be  searched in  the specified
	 * directories.
	 *
	 * @param string        $path      Absolute or relative file path.
	 * @param string[]      $dirs      The directories where to search the file.
	 * @param callback|null $predicate The  predicate the file  MUST  statisfies
	 *                                 (default is “is_readable”).
	 *
	 * @return string|false The path of the file if found, otherwise false.
	 */
	static function find($path, array $dirs, $predicate = null)
	{
		if ($predicate === null)
		{
			$predicate = 'is_readable';
		}

		if (Gallic_Path::isAbsolute($path))
		{
			return (call_user_func($predicate, $path) ? $path : false);
		}

		foreach ($dirs as $dir)
		{
			$full_path = $dir.DIRECTORY_SEPARATOR.$path;
			if (call_user_func($predicate, $full_path))
			{
				return $full_path;
			}
		}

		return false;
	}

	/**
	 * Tries to loads a file.
	 *
	 * Note: If the path is relative, the file will be searched in the specified
	 * directories.
	 *
	 * @param string        $classname
	 * @param string[]|null $dirs
	 *
	 * @return boolean
	 */
	static function load($path, $dirs = null)
	{
		if ($dirs === null)
		{
			$dirs = array('.');
		}

		$path = self::find($path, $dirs);
		if ($path === false)
		{
			return false;
		}

		include $path;

		return true;
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {}
}

////////////////////////////////////////////////////////////////////////////////

/**
 *
 */
interface Gallic_ClassLoader
{
	/**
	 * Loads a class (or interface).
	 *
	 * The behavior is undefined if the class (or interface) is already defined.
	 *
	 * @param string $class
	 *
	 * @return boolean
	 */
	function load($class_name);
}

abstract class Gallic_ClassLoader_Abstract implements Gallic_ClassLoader
{
	/**
	 * The inherited classes MUST define this method instead of “load()” because
	 * this class does some additional work.
	 *
	 * For instance, it  checks the existence of the  class (or interface) after
	 * the loading to provide accurate report through the returned value.
	 *
	 * @param string $class_name
	 *
	 * @return boolean
	 */
	abstract protected function _load($class_name);

	final function load($class_name)
	{
		return ($this->_load($class_name) &&
		        (class_exists($class_name, false) ||
		         interface_exists($class_name, false)));
	}

	/**
	 * Splits the class name into basic components.
	 *
	 * Components are namespaces and the class name splited at underscores.
	 *
	 * @param string $class_name
	 *
	 * @return string[]
	 */
	protected static function _getComponents($class_name)
	{
		if (PHP_VERSION_ID >= 50300)
		{
			$components = explode('\\', $class_name);
			$class_name = array_pop($components);
		}
		else
		{
			$components = array();
		}

		foreach (explode('_', $class_name) as $component)
		{
			if ($component !== '')
			{
				$components[] = $component;
			}
		}

		return $components;
	}
}

class Gallic_ClassLoader_PrefixFilter implements Gallic_ClassLoader
{
	/**
	 * @codeCoverageIgnore
	 */
	function __construct(Gallic_ClassLoader $cl, array $prefixes)
	{
		$this->_cl = $cl;
		$this->_prefixes = $prefixes;
	}

	function load($class_name)
	{
		foreach ($this->_prefixes as $prefix)
		{
			if (strncmp($class_name, $prefix, strlen($prefix)) === 0)
			{
				return $this->_cl->load($class_name);
			}
		}

		return false;
	}

	/**
	 * @var Gallic_ClassLoader
	 */
	private $_cl;

	/**
	 * @var string[]
	 */
	private $_prefix;
}

final class Gallic_ClassLoader_Standard extends Gallic_ClassLoader_Abstract
{
	/**
	 * @param string[] $paths
	 */
	function __construct(array $paths)
	{
		$this->_paths = $paths;
	}

	protected function _load($class_name)
	{
		$path = implode(DIRECTORY_SEPARATOR, self::_getComponents($class_name)).'.php';

		return (
			($path = Gallic_File::find($path, $this->_paths, 'is_readable')) &&
			Gallic_File::load($path)
		);
	}

	/**
	 * @var string[]
	 */
	private $_paths;
}

final class Gallic_ClassLoader_ClassMap extends Gallic_ClassLoader_Abstract
{
	function __construct(array $map)
	{
		$this->_map = $map;
	}

	protected function _load($class_name)
	{
		if (isset($this->_map[$class_name]))
		{
			return Gallic_File::load($this->_map[$class_name]);
		}

		return false;
	}

	/**
	 * @var mixed[]
	 */
	private $_map;
}

////////////////////////////////////////////////////////////////////////////////

Gallic::_init();
