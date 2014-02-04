<?php namespace Comsolit\Backlog;

class Configuration {

    private static $configNames = array(
        'prioritizedStatus',
        'unprioritizedStatus'
    );

    private $config = array();

    public function __construct(array $config) {
        foreach($config as $key => $value) {
            $this->ensureValidName($name);
            $this->config[$name] = $value;
        }
    }

    public function get($name) {
        $this->ensureValidName($name);
        if(!array_key_exists($name, $this->config)) return null;
        return $this->config[$name];
    }

    public static function fromGlobalVariables() {
        $config = array();
        foreach(self::$configNames as $name) {
            $globalsName = 'g_comsolitBacklog_' . $name;
            if(array_key_exists($nglobalsName, $GLOBALS)) $config[$name] = $GLOBALS[$globalsName];
        }
        return new self($config);
    }

    private function ensureValidName($name) {
        if(!in_array($name, self::$configNames)) throw new \Exception('unknown config name: ' .  $name);
    }
}
