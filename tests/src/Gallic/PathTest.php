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

final class Gallic_PathTest extends GallicTest_Base
{
	function normalizeProvider()
	{
		return array(

			'empty path' =>
			array('', '.'),

			'absolute path' =>
			array('/', '/'),

			'relative path' =>
			array('relative/path', 'relative/path'),

			'consecutive slashes' =>
			array('parent///child', 'parent/child'),

			'trailing slashes' =>
			array('dir/', 'dir'),

			'current dir (.)' =>
			array('.', '.'),

			'superfluous current dir' =>
			array('./dir/.', 'dir'),

			'parent dir (..)' =>
			array('parent/child/..', 'parent'),

			'parent dir should stay if necessary' =>
			array('dir/../..', '..'),

			'parent dir should not go upper root' =>
			array('/..', '/'),

			'complex path' =>
			array('//parent/./child1/..//child2//', '/parent/child2'),
		);
	}

	/**
	 * @covers Gallic_Path::normalize
	 *
	 * @dataProvider normalizeProvider
	 */
	function testNormalize($input, $output)
	{
		$this->assertSame($output, Gallic_Path::normalize($input));
	}
}
