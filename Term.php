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

class Term {
	private static $terminalWidth = 0;
	private static $terminalHeight = 0;
	private static $terminalCache = 0;

	private static function reloadTerminalCache() {
		if (self::$terminalWidth &&
				self::$terminalHeight &&
				time() < self::$terminalCache + 1) {
			return;
		}

		self::$terminalCache = time();

		$n = exec('tput cols');
		if (is_numeric($n)) {
			self::$terminalWidth = (int) $n;
		} else {
			echo 'Can\'t detect terminal width!' . PHP_EOL;
			exit(-1);
		}

		$n = exec('tput lines');
		if (is_numeric($n)) {
			self::$terminalHeight = (int) $n;
		} else {
			echo 'Can\'t detect terminal height!' . PHP_EOL;
			exit(-1);
		}
	}

	public static function getTerminalWidth() {
		self::reloadTerminalCache();

		return self::$terminalWidth;
	}

	public static function getTerminalHeight() {
		self::reloadTerminalCache();

		return self::$terminalHeight;
	}
}
