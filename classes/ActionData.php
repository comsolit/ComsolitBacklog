<?php namespace Comsolit\Backlog;
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

class ActionData {

    private $data;

    public function __construct($rawPostData) {
        $this->data = json_decode($rawPostData, true);
        if(!is_array($this->data)) throw new \Exception('invalid post data: ' . $rawPostData);
        $this->ensureArrayKey('action', $postData);

        switch ($this->data['action']) {
            case 'move':
                $this->ensureArrayKey('dragId', $postData);
                $this->ensureArrayKey('dropId', $postData);
                break;
            case 'remove':
                $this->ensureArrayKey('id', $postData);
                break;
            default:
                throw new \Exception('invalid action: ' . $postData['action']);
        }
    }

    public function __get($name) {
        if(!array_key_exists($name, $this->data)) throw new \Exception('unknown ActionData property: ' . $name);
        return $this->data[$name];
    }

    function ensureArrayKey($key) {
        if(!array_key_exists($key, $this->data)) throw new \Exception('invalid post data. missing key ' . $key . ' in ' . print_r($this->data, true));
    }
}