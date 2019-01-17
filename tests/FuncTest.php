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

class FuncTest extends \PHPUnit\Framework\TestCase {
	public function testMbStrPad() {
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A', STR_PAD_RIGHT), 'heřloAA');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A'), 'heřloAA');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A', STR_PAD_LEFT), 'AAheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'A', STR_PAD_BOTH), 'AheřloA');

		$this->assertEquals(mb_str_pad('heřlo', 7, 'AB', STR_PAD_RIGHT), 'heřloAB');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'AB', STR_PAD_LEFT), 'ABheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'AB', STR_PAD_BOTH), 'AheřloA');

		$this->assertEquals(mb_str_pad('heřlo', 8, 'AB', STR_PAD_RIGHT), 'heřloABA');
		$this->assertEquals(mb_str_pad('heřlo', 8, 'AB', STR_PAD_LEFT), 'ABAheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 8, 'AB', STR_PAD_BOTH), 'AheřloAB');

		$this->assertEquals(mb_str_pad('heřlo', 7, 'č', STR_PAD_LEFT), 'ččheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'č', STR_PAD_RIGHT), 'heřločč');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'č', STR_PAD_BOTH), 'čheřloč');

		$this->assertEquals(mb_str_pad('heřlo', 7, 'čď', STR_PAD_LEFT), 'čďheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'čď', STR_PAD_RIGHT), 'heřločď');
		$this->assertEquals(mb_str_pad('heřlo', 7, 'čď', STR_PAD_BOTH), 'čheřloč');

		$this->assertEquals(mb_str_pad('heřlo', 8, 'čď', STR_PAD_LEFT), 'čďčheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 8, 'čď', STR_PAD_RIGHT), 'heřločďč');
		$this->assertEquals(mb_str_pad('heřlo', 8, 'čď', STR_PAD_BOTH), 'čheřločď');

		$this->assertEquals(mb_str_pad('heřlo', 6, 'čď', STR_PAD_LEFT), 'čheřlo');
		$this->assertEquals(mb_str_pad('heřlo', 6, 'čď', STR_PAD_RIGHT), 'heřloč');
		$this->assertEquals(mb_str_pad('heřlo', 6, 'čď', STR_PAD_BOTH), 'heřloč');

		$this->assertEquals(mb_str_pad('heřlo', 5, 'č', STR_PAD_LEFT), 'heřlo');
		$this->assertEquals(mb_str_pad('heřlo', 4, 'č', STR_PAD_LEFT), 'heřlo');
		$this->assertEquals(mb_str_pad('heřlo', 0, 'č', STR_PAD_LEFT), 'heřlo');
		$this->assertEquals(mb_str_pad('heřlo', -1, 'č', STR_PAD_LEFT), 'heřlo');
	}
}
