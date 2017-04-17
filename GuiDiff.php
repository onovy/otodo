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

class GuiDiff {
	private $backups = array();
	private $data = array();
	private $top;
	private $one;
	private $two;

	function start() {
		$dir = opendir(Config::$config['core']['backup_dir']);
		while (false !== ($entry = readdir($dir))) {
			if ($entry !== 'todo.last.txt') {
				$this->backups[] = $entry;
			}
		}
		rsort($this->backups);

		$pos = 1;
		$this->top = 0;
		$this->diff($pos);
		system('stty -icanon -echo');
		// Hide cursor
		echo "\033[?25l";
		while ($cmd = fread(STDIN, 1)) {
			switch ($cmd) {
				case 'q':
					system('stty sane');
					// Show cursor
					echo "\033[?25h";
					return;
				break;
				case 'C': // Right
					if ($pos > 1) {
						$pos--;
						$this->top = 0;
						$this->diff($pos);
					}
				break;
				case 'D': // Left
					$pos++;
					$this->top = 0;
					$this->diff($pos);
				break;
				case 'A': // Up
					if ($this->top > 0) {
						$this->top--;
						$this->show();
					}
				break;
				case 'B': // Down
					if ($this->top + Term::getTerminalHeight() <= count($this->data)) {
						$this->top++;
						$this->show();
					}
				break;
				case '5': // PgUp
					$page = Term::getTerminalHeight() - 1;
					if ($this->top >= $page) {
						$this->top -= $page;
						$this->show();
					} else if ($this->top > 0) {
						$this->top = 0;
						$this->show();
					}
				break;
				case '6': // PgDown
					$page = Term::getTerminalHeight() - 1;
					if ($this->top + $page * 2 <= count($this->data)) {
						$this->top += $page;
						$this->show();
					} else if ($this->top + $page < count($this->data)) {
						$this->top = count($this->data) - $page;
						$this->show();
					}
				break;
			}
		}
	}

	function show() {
		$terminalWidth = Term::getTerminalWidth();
		$terminalHeight = Term::getTerminalHeight();

		$show = "\033[0;0f\033[m\033[K";

		$head = $this->one . ' -> ' . $this->two;
		if ($this->top) {
			$perc = ceil($this->top / (count($this->data) - $terminalHeight) * 100);
			if ($perc > 100) {
				$perc = 100;
			}
			$head .= ' [' . $perc . ' %]';
		}
		$show .= mb_substr($head, 0, $terminalWidth - 1);
		for ($i = 0 ; $i < $terminalHeight - 1 ; $i++) {
			$show .= PHP_EOL;
			$show .= "\033[9999D\033[m\033[K";
			if (count($this->data) > $i + $this->top) {
				$show .= mb_substr(trim($this->data[$i + $this->top]), 0, $terminalWidth - 1);
			}
		}

		echo $show;
	}

	function diff($pos) {
		$this->one = $this->backups[$pos];
		$this->two = $this->backups[$pos - 1];

		$oneD = Config::$config['core']['backup_dir'] .
			DIRECTORY_SEPARATOR . $this->one;
		$twoD = Config::$config['core']['backup_dir'] .
			DIRECTORY_SEPARATOR . $this->two;

		$this->data = array();
		$ret = 0;
		$cmd = Config::$config['gui']['diff'] . ' ' .
			escapeshellarg($oneD) . ' ' .
			escapeshellarg($twoD);
		exec($cmd, $this->data, $ret);
		if (!in_array($ret, Config::$config['gui']['diffRet'])) {
			array_unshift($this->data, 'Return code: ' . $ret);
			array_unshift($this->data, $cmd);
			array_unshift($this->data, 'Failed to execute diff cmd:');
		}

		$this->show();
	}
}
