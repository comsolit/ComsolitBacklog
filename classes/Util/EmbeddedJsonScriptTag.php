<?php namespace Comsolit\Backlog\Util;
// Copyright 2014 comsolit AG
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

require_once __DIR__ . '/HTMLTag.php';

class EmbeddedJsonScriptTag {

	public static function create($name, $data) {
		$json = json_encode($data);
		$tag = new HTMLTag('script', $json);
		$tag->addAttribute('data-name', $name)
		->addAttribute('type', 'application/json')
		->addAttribute('class', 'embedded-json-data');

		return $tag->build();
	}
}