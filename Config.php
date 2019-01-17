<?php
/*
Copyright 2014-2017 Ondrej Novy

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
		$ini = @parse_ini_file(self::fixHomeDirectory($filename), true);
		if ($ini === FALSE) {
			$phpError = error_get_last();
			throw new ConfigLoadException('Can\'t load config file: ' .
				$phpError['message']);
		}
		self::$config = $ini;

		$tz = @self::$config['core']['timezone'];
		if ($tz) {
			if (!@date_default_timezone_set($tz)) {
				echo 'Timezone ' . $tz . ' from config core.timezone ' .
					'is not correct. Please set one from ' .
					'http://php.net/manual/en/timezones.php ' .
					PHP_EOL;
				exit(-1);
			}
		} else {
			if (!ini_get('date.timezone')) {
				echo 'Timezone is not set. Please set one from ' .
					'http://php.net/manual/en/timezones.php ' .
					'in config core.timezone or in php.ini' .
					PHP_EOL;
				exit(-1);
			}
		}

		$dirs = array('todo_file', 'archive_file', 'backup_dir');
		foreach ($dirs as $dir) {
			self::$config['core'][$dir] = self::fixHomeDirectory(self::$config['core'][$dir]);
		}
	}

	private static function fixHomeDirectory($dir) {
		if ($dir[0] === '~') {
			if (isset($_SERVER['HOME'])) {
				$home = $_SERVER['HOME'];
			} else {
				$home = getenv('HOME');
			}
			return $home . substr($dir, 1);
		} else {
			return $dir;
		}
	}
}
