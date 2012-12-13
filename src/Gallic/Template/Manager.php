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
 * @experimental
 * @todo It is currently impossible to import a template twice (even if it
 *     changed).
 */
final class Gallic_Template_Manager
{
	function __construct($tpl_dir, $ttl = null, $cache_dir = null)
	{
		$this->_tplDir   = $tpl_dir;
		$this->_ttl      = ($ttl !== null)
			? $ttl
			: 300; // 5 minutes.
		$this->_cacheDir = ($cache_dir !== null)
			? $cache_dir
			: sys_get_temp_dir();
	}

	/**
	 * @param string $path
	 *
	 * @return Gallic_Template
	 */
	function build($path)
	{
		$tpl_path    = $this->_tplDir.'/'.$path;
		$cache_class = $this->_getCacheClass($path);
		$cache_path  = $this->_cacheDir.'/'.$cache_class.'.php';

		// If the class is already imported, use it.
		if (class_exists($cache_class, false))
		{
			return new $cache_class;
		}

		// If there is an up to date cache, import it.
		if (is_file($cache_path))
		{
			$mtime = filemtime($cache_path);

			$valid = (time() - $mtime) < $this->_ttl;
			if (!$valid)
			{
				if ($mtime > filemtime($tpl_path))
				{
					$valid = true;
					touch($cache_path);
				}
			}

			if ($valid)
			{
				require($cache_path);
				return new $cache_class;
			}
		}

		// We have to compile the template.
		if ($this->_parser === null)
		{
			$this->_parser   = new Gallic_Template_Parser;
			$this->_compiler = new Gallic_Template_Compiler;
		}


		$tpl   = file_get_contents($tpl_path);
		$tree  = $this->_parser->parse($tpl);
		if ($tree['extends'])
		{
			$extended_class = $this->_getCacheClass($tree['extends']);
			$this->build($tree['extends']);
		}
		else
		{
			$extended_class = null;
		}
		$cache = $this->_compiler->compile($tree, $cache_class, $extended_class);

		file_put_contents($cache_path, $cache);

		eval('?>'.$cache);
		return new $cache_class;
	}

	/**
	 * @var Gallic_Template_Parser
	 */
	private $_parser;

	/**
	 * @var Gallic_Template_Compiler
	 */
	private $_compiler;

	/**
	 *
	 */
	private function _getCacheClass($path)
	{
		return 'Gallic_Template_'.hash('crc32b', $this->_tplDir.'/'.$path);
	}
}
