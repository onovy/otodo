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

require_once 'init.php';

class FuncTest extends PHPUnit_Framework_TestCase {
	public function testMbStrPad() {
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A', STR_PAD_RIGHT), 'heřloAA');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A', STR_PAD_LEFT), 'AAheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A', STR_PAD_BOTH), 'AheřloA');
	}
}
