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

class TodosEx extends Todos {
	protected $CLASS = 'TodoEx';

	protected function sortCmp($col, $asc, $a, $b) {
		switch ($col) {
			case 'due':
				if ($a->$col === null) {
					if ($b->$col === null) {
						return 0;
					}
					return 1;
				} elseif ($b->$col === null) {
					return -1;
				}
				$diff = $a->$col->diff($b->$col);
				if ($diff->days == 0) {
					return 0;
				}
				if ($diff->invert) {
					return $asc ? 1 : -1;
				} else {
					return $asc ? -1 : 1;
				}
			break;
			default:
				return parent::sortCmp($col, $asc, $a, $b);
			break;
		}
	}
}
