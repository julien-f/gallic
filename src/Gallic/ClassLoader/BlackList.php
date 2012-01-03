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

final class Gallic_ClassLoader_BlackList implements Gallic_ClassLoader
{
	/**
	 * @param string[] $black_list
	 *
	 * @codeCoverageIgnore
	 */
	function __construct(Gallic_ClassLoader $cl, array $black_list)
	{
		$this->_cl = $cl;
		$this->_black_list = array_flip($black_list);
	}

	function load($class_name)
	{
		if (isset($this->_black_list[$class_name]))
		{
			return false;
		}

		return $this->_cl->load($class_name);
	}

	/**
	 * @var Gallic_ClassLoader
	 */
	private $_cl;

	/**
	 * @var mixed[string]
	 */
	private $_black_list;
}
