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

require_once '../init.php';

class RecurrentTest extends PHPUnit_Framework_TestCase {
	public function testInOut() {
		$tests = array(
			'1d' => '1d',
			'+2d' => '+2d',
			'3w' => '3w',
			'+4w' => '+4w',
			'5m' => '5m',
			'+6m' => '+6m',
			'7y' => '7y',
			'+8y' => '+8y',
			'9' => '9d',
			'+9' => '+9d',

			'11d b' => '11db',
			'+12d b' => '+12db',
			'13w b' => '13wb',
			'+14w b' => '+14wb',
			'15m b' => '15mb',
			'+16m b' => '+16mb',
			'17y b' => '17yb',
			'+18y b' => '+18yb',
			'19 b' => '19db',
			'+19 b' => '+19db',

			'11db' => '11db',
			'+12db' => '+12db',
			'13wb' => '13wb',
			'+14wb' => '+14wb',
			'15mb' => '15mb',
			'+16mb' => '+16mb',
			'17yb' => '17yb',
			'+18yb' => '+18yb',
			'19b' => '19db',
			'+19b' => '+19db',
		);
		foreach ($tests as $in => $out) {
			$r = new Recurrent($in);
			$this->assertEquals($r->toString(), $out);
		}
	}

	public function testWrongInput() {
		$tests = array(
			'1r',
			'2q',
			'+3g',
		);
		foreach ($tests as $test) {
			try {
				$r = new Recurrent($test);
				$this->fail($test . ' should throw exception!');
			} catch (RecurrentParseException $rpe) {
				// This is OK
			}
		}
	}

	public function testRecurr() {
		$tests = array(
			// In, interval, out
			array('2014-01-01', '1d', '2014-01-02'),
			array('2014-01-01', '1w', '2014-01-08'),
			array('2014-01-01', '1m', '2014-02-01'),
			array('2014-01-01', '1y', '2015-01-01'),

			array('2014-01-01', '2d', '2014-01-03'),
			array('2014-01-01', '2w', '2014-01-15'),
			array('2014-01-01', '2m', '2014-03-01'),
			array('2014-01-01', '2y', '2016-01-01'),

			array('2014-01-31', '1w', '2014-02-07'),
			array('2014-01-31', '1m', '2014-03-03'),

			array('2014-01-01', '+1d', (new DateTime())->modify('+1day')->format('Y-m-d')),
			array('2014-01-01', '+1w', (new DateTime())->modify('+1week')->format('Y-m-d')),
			array('2014-01-01', '+1m', (new DateTime())->modify('+1month')->format('Y-m-d')),
			array('2014-01-01', '+1y', (new DateTime())->modify('+1year')->format('Y-m-d')),

			array('2014-01-03', '1b', '2014-01-06'),
			array('2014-01-03', '2b', '2014-01-06'),
			array('2014-01-03', '3b', '2014-01-06'),
			array('2014-01-03', '4b', '2014-01-07'),
			array('2014-01-04', '1wb', '2014-01-13'),
			array('2014-01-01', '1mb', '2014-02-03'),
			array('2014-01-03', '1yb', '2015-01-05'),
		);
		foreach ($tests as $test) {
			$in = $test[0];
			$interval = $test[1];
			$out = $test[2];

			$ts = new DateTime($in);
			$r = new Recurrent($interval);
			$this->assertEquals(
				$r->recurr($ts)->format('Y-m-d'),
				$out);
		}
	}
}
