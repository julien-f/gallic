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
 * This namespace contains the core data and functionnalities of Gallic.
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
	 * This is, more or less, the equivalent of PHP's “include_path”.
	 *
	 * @var array
	 */
	public static $paths = array();

	/**
	 * Initializes Gallic.
	 *
	 * This method is automatically called just after this class' definition and
	 * MUST not be called afterwards.
	 */
	public static function _init()
	{
		self::$paths[] = defined('__DIR__') ? __DIR__ : dirname(__FILE__);

		spl_autoload_register(array(__CLASS__, '_autoload'));
	}

	private static function _autoload($classname)
	{
		return (Gallic_Loader::loadClass($classname) &&
		        (class_exists($classname, false) ||
		         interface_exists($classname, false)));
	}

	private function __construct() {}

	private function __clone() {}
}
Gallic::_init();

////////////////////////////////////////////////////////////////////////////////

/**
 * This namespace provides paths related functions.
 */
final class Gallic_Path
{
	/**
	 * Checks if a path is absolute.
	 *
	 * Note: This function only works with POSIX paths.
	 *
	 * @param string $path
	 *
	 * @return boolean
	 */
	public static function is_absolute($path)
	{
		return ($path[0] === '/');
	}

	/**
	 * Joins multiple paths.
	 *
	 * Note:  This function  postulates that  all  arguments but  the parent  are
	 * relative paths.
	 *
	 * @param string $parent
	 * @param string $path1
	 * @param string $...
	 *
	 * @return string
	 */
	public static function join($parent, $path1)
	{
		// Prior  to PHP  5.3, func_get_args()  cannot  be used  directly as  an
		// argument.
		$args = func_get_args();

		return implode(DIRECTORY_SEPARATOR, $args);
	}


	/**
	 * Normalizes a path, which means, removes every '//', '.' and '..'.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function normalize($path)
	{
		if ($path === '')
		{
			return '.';
		}

		$path = explode(DIRECTORY_SEPARATOR, $path);

		$out = array($path[0]);
		array_shift($path);

		foreach ($path as $component)
		{
			if (($component === '') || ($component === '.'))
			{
				continue;
			}

			if (($component === '..') && (($prev = end($out)) !== '..'))
			{
				if ($prev !== '')
				{
					array_pop($out);
				}
				continue;
			}

			array_push($out, $component);
		}

		$n = count($out);
		if ($n === 0)
		{
			return '.';
		}

		if ($n === 1)
		{
			if ($out[0] === '')
			{
				return '/';
			}
			return $out[0];
		}

		return implode(DIRECTORY_SEPARATOR, $out);
	}

	private function __construct() {}

	private function __clone() {}
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

	 * @param callback|null $predicate The  predicate the file  MUST  statisfies
	 *                                 (default is “is_readable”).
	 * @param string[]|null $dirs      The directories where to  search the file
	 *                                 (default is “Gallic::$paths”).
	 *
	 * @return string|false The path of the file if found, otherwise false.
	 */
	public static function find($path, $predicate = null, $dirs = null)
	{
		if (is_null($predicate))
		{
			$predicate = 'is_readable';
		}

		$dirs = $dirs !== null ? (array) $dirs : Gallic::$paths;

		if (Gallic_Path::is_absolute($path))
		{
			return (call_user_func($predicate, $path) ? $path : false);
		}

		foreach ($dirs as $dir)
		{
			$full_path = Gallic_Path::join($dir, $path);
			if (call_user_func($predicate, $full_path))
			{
				return $full_path;
			}
		}

		return false;
	}

	private function __construct() {}

	private function __clone() {}
}

////////////////////////////////////////////////////////////////////////////////

/**
 * This namespace provides loading facilities.
 */
final class Gallic_Loader
{
	/**
	 * Tries to loads a class.
	 *
	 * Note: Excepted from the  namespace handling, this implementation complies
	 * to PSR-0.
	 *
	 * @param string        $classname
	 * @param string[]|null $dirs
	 *
	 * @return boolean
	 */
	public static function loadClass($classname, $dirs = null)
	{
		$path = str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';
		return self::loadFile($path, $dirs);
	}

	/**
	 * Tries to loads a file.
	 *
	 * Note: If the path is relative, the file will be searched in the specified
	 * directories (or “Gallic::$paths” if null).
	 *
	 * @param string        $classname
	 * @param string[]|null $dirs
	 *
	 * @return boolean
	 */
	public static function loadFile($path, $dirs = null)
	{
		$path = Gallic_File::find($path, 'is_readable', $dirs);
		if ($path === false)
		{
			return false;
		}

		include $path;

		return true;
	}

	private function __construct() {}

	private function __clone() {}
}
