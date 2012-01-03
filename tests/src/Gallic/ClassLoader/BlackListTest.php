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

final class Gallic_ClassLoader_BlackListTest_MyClassLoader implements Gallic_ClassLoader
{
	static public $request = null;

	function load($class_name)
	{
		self::$request = $class_name;
		return true;
	}
}

////////////////////////////////////////////////////////////////////////////////

final class Gallic_ClassLoader_BlackListTest extends GallicTest_Base
{
	function loadSuccessProvider()
	{
		$cl = new Gallic_ClassLoader_BlackList(
			new Gallic_ClassLoader_BlackListTest_MyClassLoader(),
			array('MyClass')
		);

		return array(
			array($cl, 'MyInterface'),
		);
	}

	/**
	 * @covers Gallic_ClassLoader_BlackList::load
	 *
	 * @dataProvider loadSuccessProvider
	 *
	 * @param string $class_name
	 */
	function testLoadSuccess(Gallic_ClassLoader_BlackList $cl, $class_name)
	{
		Gallic_ClassLoader_BlackListTest_MyClassLoader::$request = null;

		$this->assertTrue($cl->load($class_name));

		$this->assertSame(
			$class_name,
			Gallic_ClassLoader_BlackListTest_MyClassLoader::$request
		);
	}

	//--------------------------------------

	function loadFailureProvider()
	{
		$cl = new Gallic_ClassLoader_BlackList(
			new Gallic_ClassLoader_BlackListTest_MyClassLoader(),
			array('MyClass')
		);

		return array(
			array($cl, 'MyClass'),
		);
	}

	/**
	 * @covers Gallic_ClassLoader_BlackList::load
	 *
	 * @dataProvider loadFailureProvider
	 *
	 * @param string $class_name
	 */
	function testLoadFailure(Gallic_ClassLoader_BlackList $cl, $class_name)
	{
		Gallic_ClassLoader_BlackListTest_MyClassLoader::$request = null;

		$this->assertFalse($cl->load($class_name));

		$this->assertNull(
			Gallic_ClassLoader_BlackListTest_MyClassLoader::$request
		);
	}
}