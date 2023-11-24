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

class Recurrent {
	public $fromNow = false;
	public $count;
	public $unit;
	public $businessDay = false;

	public function __construct($str) {
		$this->fromNow = false;
		$str = strval($str);

		if ($str[0] == '+') {
			$this->fromNow = true;
			$str = substr($str, 1);
		}
		if (!preg_match('/^(\d+)([dwmy]?) ?(b)?$/', $str, $matches)) {
			throw new RecurrentParseException($str);
		}
		$this->count = $matches[1];
		if (isset($matches[2]) && $matches[2] != '') {
			$this->unit = $matches[2];
		} else {
			$this->unit = 'd';
		}
		if (isset($matches[3]) && $matches[3] == 'b') {
			$this->businessDay = true;
		}
	}

	public function toString() {
		$out = '';
		if ($this->fromNow) {
			$out .= '+';
		}
		$out .= $this->count . $this->unit;
		if ($this->businessDay) {
			$out .= 'b';
		}

		return $out;
	}

	public function recurr($oldTs) {
		assert($oldTs instanceof DateTime || $oldTs === null);
		if ($this->fromNow || $oldTs === null) {
			$ts = new DateTime('today');
		} else {
			$ts = clone $oldTs;
		}
		switch ($this->unit) {
			case 'd':
				$m = 'day';
			break;
			case 'm':
				$m = 'month';
			break;
			case 'w':
				$m = 'week';
			break;
			case 'y':
				$m = 'year';
			break;
			default:
				throw new RecurrentParseException('Unknown unit: ' . $this->unit);
			break;
		}
		$ts->modify('+' . $this->count . ' ' . $m);
		if ($this->businessDay) {
			while (in_array($ts->format('N'), array(6, 7))) {
				$ts->modify('+1day');
			}
		}

		return $ts;
	}
}
