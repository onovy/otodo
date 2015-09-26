<?php
/*
Copyright 2014 Ondrej Novy

This file is part of otodo.

otodo is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

otodo is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with otodo.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Call protected/private method of a class.
 *
 * @param object &$object    Instantiated object that we will run method on.
 * @param string $methodName Method name to call
 * @param array  $parameters Array of parameters to pass into method.
 *
 * @return mixed Method return.
 */
function invokeMethod(&$object, $methodName, array $parameters = array()) {
	$reflection = new \ReflectionClass(get_class($object));
	$method = $reflection->getMethod($methodName);
	$method->setAccessible(true);

	return $method->invokeArgs($object, $parameters);
}

/**
 * Get value of protected/private properties of a class.
 *
 * @param object &$object    Instantiated object that we will run method on.
 * @param string $methodName Property name
 *
 * @return mixed Property value
 */
function getProperty(&$object, $propertyName) {
	$reflection = new \ReflectionClass(get_class($object));
	$property = $reflection->getProperty($propertyName);
	$property->setAccessible(true);

	return $property->getValue($object);
}

require_once dirname(__FILE__) . '/../init.php';
