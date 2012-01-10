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
 *
 */
final class Gallic_FileWrapper
{
	/**
	 * Opens the file for reading.
	 */
	const READ   = 1;

	/**
	 * Opens the file for writing.
	 *
	 * Note: This mode cannot be used alone.
	 */
	const WRITE  = 2;

	/**
	 * Sets the pointer at the end of the file.
	 *
	 * Depends:   WRITE
	 * Conflicts: TRUNC
	 * Implies:   ACREAT
	 */
	const APPEND = 20; // 4 + 16

	/**
	 * Truncates the file (clears its content).
	 *
	 * Depends:   WRITE
	 * Conflicts: APPEND
	 * Implies:   ACREAT
	 */
	const TRUNC  = 24; // 8 + 16

	/**
	 * Automatically creates the file if it does not exist.
	 *
	 * Depends:   WRITE
	 * Conflicts: CREATE
	 */
	const ACREAT = 16;

	/**
	 * Creates the file, fails if it already exists.
	 *
	 * Depends:   WRITE
	 * Conflicts: ACREAT
	 */
	const CREATE = 32;

	/**
	 * Opens a file.
	 *
	 * @param string              $path
	 * @param string|integer|null $mode
	 */
	static function open($path, $mode = null)
	{
		if (!isset($mode))
		{
			$mode = 'a+'; // READ + WRITE + APPEND.
		}
		elseif (is_integer($mode))
		{
			$mode = self::_getMode($mode);
		}
		else
		{
			// Ensures binary mode.
			$mode .= 'b';
		}

		$handle = fopen($path, $mode);
		if (!$handle)
		{
			throw new Exception('opening failed: '.$path);
		}

		return new self($handle, $path);
	}

	/**
	 * @param string|null $path
	 * @param resource    $handle
	 */
	function __construct($handle, $path = null)
	{
		$this->_handle = $handle;
		$this->_path   = $path;
	}

	/**
	 *
	 */
	function __destruct()
	{
		fclose($this->_handle);
	}

	/**
	 * @param string $name
	 */
	function __call($name, array $args)
	{
		$func = strtolower($name);

		if ($try = (strncmp($func, 'try', 3) === 0))
		{
			$func = substr($func, 3);
		}

		array_unshift($args, $this->_handle);
		$result = call_user_func_array('f'.$func, $args);

		if (!$try && ($result === false))
		{
			throw new Exception('file operation failed: '.$name);
		}

		return $result;
	}

	/**
	 * Reads  a string  until the  given character  or the  end of  the  file is
	 * encountered.
	 *
	 * Note: The delimiter is not included in the returned string.
	 *
	 * @param string $char
	 *
	 * @return string
	 */
	public function readUntil($char)
	{
		$data = '';

		while ((($c = $this->tryGetC()) !== false) && ($c !== $char))
		{
			$data .= $c;
		}

		return $data;
	}

	/**
	 * @var string|null
	 */
	private $_path;

	/**
	 * @var resource
	 */
	private $_handle;

	private static function _getMode($mode)
	{
		static $modes = null;

		if (!isset($modes))
		{
			$modes = array(
				self::READ                               => 'r',
				self::READ   | self::WRITE               => 'r+',

				// ACREAT implied.
				self::APPEND | self::WRITE               => 'a',
				self::APPEND | self::WRITE  | self::READ => 'a+',

				// ACREAT implied.
				self::TRUNC  | self::WRITE               => 'w',
				self::TRUNC  | self::WRITE  | self::READ => 'w+',

				self::CREATE | self::WRITE               => 'x',
				self::CREATE | self::WRITE  | self::READ => 'x+',
			);

			if (PHP_VERSION_ID >= 50206)
			{
				$modes += array(
					self::ACREAT | self::WRITE               => 'c',
					self::ACREAT | self::WRITE  | self::READ => 'c+',
				);
			}
		}

		if (!isset($modes[$mode]))
		{
			throw new Exception('invalid mode: '.$mode);
		}

		return $modes[$mode];
	}
}
