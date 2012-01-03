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

class Gallic_ClassLoader_AbstractTest_Helper extends Gallic_ClassLoader_Abstract
{
	static function getComponents($class_name)
	{
		return parent::_getComponents($class_name);
	}

	public $result = true;

	protected function _load($class_name)
	{
		return $this->result;
	}
}

class Gallic_ClassLoader_AbstractTest_Class
{}

interface Gallic_ClassLoader_AbstractTest_Interface
{}

////////////////////////////////////////////////////////////////////////////////

final class Gallic_ClassLoader_AbstractTest extends GallicTest_Base
{
	function getComponentsProvider()
	{
		$tests = array(

			'Simple class name' =>
			array('MyClass', array('MyClass')),

			'Class name with underscores' =>
			array('My_Super_Class', array('My', 'Super', 'Class')),
		);

		if (PHP_VERSION_ID >= 50300)
		{
			$tests['Class name with namespaces'] = array(
				'Namespace1\\Namespace2\\My_Class',
				array('Namespace1', 'Namespace2', 'My', 'Class')
			);
		}

		return $tests;
	}

	/**
	 * @covers Gallic_ClassLoader_Abstract
	 *
	 * @dataProvider getComponentsProvider
	 *
	 * @param string   $input
	 * @param string[] $output
	 */
	function testGetComponents($input, array $output)
	{
		$this->assertSame(
			$output,
			Gallic_ClassLoader_AbstractTest_Helper::getComponents($input)
		);
	}

	//--------------------------------------

	/**
	 * @covers Gallic_ClassLoader_Abstract::load
	 */
	function testLoadChecksClassExistence()
	{
		$cl = new Gallic_ClassLoader_AbstractTest_Helper;

		// Loading failed.
		$cl->result = false;
		$this->assertFalse($cl->load('Gallic_ClassLoader_AbstractTest_Class'));
		$this->assertFalse($cl->load('Gallic_ClassLoader_AbstractTest_Interface'));

		// Loading succeeded.
		$cl->result = true;
		$this->assertTrue($cl->load('Gallic_ClassLoader_AbstractTest_Class'));
		$this->assertTrue($cl->load('Gallic_ClassLoader_AbstractTest_Interface'));

		// Loading has been reported as succeeded but it's a lie.
		$this->assertFalse($cl->load('InexitentClass'));
	}
}
