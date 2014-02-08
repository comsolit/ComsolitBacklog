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

class Configuration {

    const GLOBAL_PREFIX = 'g_comsolitBacklog_';

    private static $configNames = array(
        'prioritizedStatus',
        'unprioritizedStatus',
        'categories'
    );

    private $config = array();

    public function __construct(array $config) {
        foreach($config as $name => $value) {
            self::ensureValidName($name);
            $this->config[$name] = $value;
        }
    }

    public function get($name) {
        self::ensureValidName($name);
        if(!array_key_exists($name, $this->config)) return null;
        return $this->config[$name];
    }

    public function getRequired($name) {
        $value = $this->get($name);
        if(is_null($value)) throw new \Exception('missing configuration: ' . $name . ' set global variable $' . self::globalVarName($name));
        return $value;
    }

    public static function fromGlobalVariables() {
        $config = array();
        foreach(self::$configNames as $name) {
            if(self::isSetGlobal($name)) $config[$name] = $GLOBALS[self::globalVarName($name)];
        }
        return new self($config);
    }

    private static function ensureValidName($name) {
        if(!in_array($name, self::$configNames)) throw new \Exception('unknown config name: ' .  $name);
    }

    private static function globalVarName($name) {
        return self::GLOBAL_PREFIX . $name;
    }

    private static function isSetGlobal($name) {
        return array_key_exists(self::globalVarName($name), $GLOBALS);
    }
}
