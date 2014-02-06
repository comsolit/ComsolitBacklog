<?php namespace Comsolit\Backlog;

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
