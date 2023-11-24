<?php
/*
Copyright 2014-2019 Ondrej Novy

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

require_once 'init.php';

class ConfigTest extends \PHPUnit\Framework\TestCase {
	public function testConfig() {
		Config::loadFile('../config.ini');

		$this->assertEquals(Config::$config['core']['todo_file'], 'todo.txt');
	}

	public function testConfigWrong() {
		$this->expectException(ConfigLoadException::class);

		Config::loadFile('not exists');
	}

	public function testFixHomeDirectory() {
		$ret = invokeStaticMethod('Config', 'fixHomeDirectory', array('dummy'));
		$this->assertEquals($ret, 'dummy');

		$ret = invokeStaticMethod('Config', 'fixHomeDirectory', array('~'));
		$this->assertEquals($ret, getenv('HOME'));

		$ret = invokeStaticMethod('Config', 'fixHomeDirectory', array('~/dummy'));
		$this->assertEquals($ret, getenv('HOME') . '/dummy');
	}
}
