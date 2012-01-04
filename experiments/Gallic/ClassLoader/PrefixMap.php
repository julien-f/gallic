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
final class Gallic_ClassLoader_PrefixMap extends Gallic_ClassLoader_Abstract
{
	function __construct(array $map)
	{
		$this->_map = $map;
	}

	protected function _load($class_name)
	{
		$components = self::_getComponents($class_name);

		$paths = $this->_map;
		$component = reset($components);
		while (is_array($paths) && isset($paths[$component]))
		{
			$paths = $paths[$component];

			$component = next($components);
		}

		$components = array_slice($components, key($components));
		$path = implode(DIRECTORY_SEPARATOR, $components);

		if (!is_array($paths))
		{
			$paths = array($paths);
		}

		return Gallic_File::load($path, $paths);
	}
}
