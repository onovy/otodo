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

class Config {
	public static $config;

	public static function loadFile($filename) {
		$ini = @parse_ini_file($filename, true);
		if ($ini === FALSE) {
			$phpError = error_get_last();
			throw new ConfigLoadException('Can\'t load config file: ' .
				$phpError['message']);
		}
		self::$config = $ini;
	}
}
