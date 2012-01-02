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

final class Gallic_Reflection
{
	/**
	 * @param string $name
	 */
	static function getFunctionSignature($name)
	{
		static $cache = array();

		if (isset($cache[$name]))
		{
			return $cache[$name];
		}

		$items = explode('::', $name, 2);
		if (count($items) === 2)
		{
			$f = new ReflectionMethod($items[0], $items[1]);
		}
		else
		{
			$f = new ReflectionFunction($name);
		}


		$regexp = '/@param\s+([a-z~|_]+)\s+\$([a-z0-9_]+)/i';
		if (
			(($doc = $f->getDocComment()) !== null) &&
			(preg_match_all($regexp, $doc, $params) !== 0)
		)
		{
			$params = array_combine($params[2], $params[1]);
		}
		else
		{
			$params = array();
		}

		foreach ($f->getParameters() as $p)
		{
			if ($p->isArray())
			{
				$type = 'array';
			}
			elseif (($class = $p->getClass()) !== null)
			{
				$type = $class->name;
			}
			else
			{
				if (!isset($params[$p->name]))
				{
					$params[$p->name] = 'mixed';
				}
				continue;
			}

			if ($p->allowsNull())
			{
				$type .= '|null';
			}

			$params[$p->name] = $type;
		}

		return $params;
	}

	private function __construct()
	{}
}
