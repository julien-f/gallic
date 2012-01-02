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
 * This class allows you to temporary suppress PHP error reporting.
 *
 * This is  useful when  using failable PHP  functions which  unecessary trigger
 * errors (such as IO functions).
 *
 * Example:
 *
 *  $es = new Gallic_ErrorSuppressor();
 *  $handle = fopen('my_file', 'x');
 *  unset($es);
 */
final class Gallic_ErrorSuppressor
{
	public
		$numero  = 0,
		$message = '',
		$file    = '',
		$line    = 0,
		$context = array();

	/**
	 * @param integer $error_types
	 */
	function __construct($error_types = null)
	{
		if ($error_types === null)
		{
			$error_types =
				E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING;
		}
		set_error_handler(array($this, '_errorHandler'), $error_types);
	}

	function __destruct()
	{
		restore_error_handler();
	}

	function _errorHandler($numero, $message, $file, $line, array $context)
	{
		$this->numero  = $errno;
		$this->message = $errstr;
		$this->file    = $errfile;
		$this->line    = $errline;
		$this->context = $errcontext;
	}
}
