<?php namespace Comsolit\Backlog;

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