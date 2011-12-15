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
 * Various string operations.
 */
final class Gallic_String
{
	/**
	 * Checks whether the string has a certain prefix.
	 *
	 * @param string $string
	 * @param string $prefix
	 *
	 * @return boolean
	 */
	public static function has_prefix($string, $prefix)
	{
		$prefix_l = strlen($prefix);

		if ($prefix_l > strlen($string))
		{
			return false;
		}

		return (strncmp($string, $prefix, $prefix_l) === 0);
	}

	/**
	 * Checks whether the string has a certain suffix.
	 *
	 * @param string $string
	 * @param string $prefix
	 *
	 * @return boolean
	 */
	public static function has_suffix($string, $suffix)
	{
		$suffix_l = strlen($suffix);

		if ($suffix_l > strlen($string))
		{
			return false;
		}

		return (substr_compare($string, $suffix, -$suffix_l) === 0);
	}

	private function __construct()
	{}
}
