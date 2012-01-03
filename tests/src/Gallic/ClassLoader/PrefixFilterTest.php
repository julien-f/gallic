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

final class Gallic_ClassLoader_PrefixFilterTest_MyClassLoader implements Gallic_ClassLoader
{
	function load($class_name)
	{
		return true;
	}
}

////////////////////////////////////////////////////////////////////////////////

final class Gallic_ClassLoader_PrefixFilterTest extends GallicTest_Base
{
	function loadProvider()
	{
		$cl = new Gallic_ClassLoader_PrefixFilter(
			new Gallic_ClassLoader_PrefixFilterTest_MyClassLoader(),
			array('First', 'Last')
		);

		return array(

			'First matches' =>
			array($cl, 'FirstClass', true),

			'Last matches' =>
			array($cl, 'LastClass', true),

			'No matches' =>
			array($cl, 'MyOwnClass', false),
		);
	}

	/**
	 * @covers Gallic_ClassLoader_PrefixFilter::load
	 *
	 * @dataProvider loadProvider
	 *
	 * @param string  $class_name
	 * @param boolean $result
	 */
	function testLoad(Gallic_ClassLoader_PrefixFilter $cl, $class_name, $result)
	{
		$this->assertSame($result, $cl->load($class_name));
	}
}
