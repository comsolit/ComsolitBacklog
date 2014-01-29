<?php namespace Comsolit\Backlog\Util;

require_once __DIR__ . '/HTMLTag.php';

class TextTag extends HTMLTag {

    private $text;

    public function __construct($text) {
        $this->text = $text;
    }

    public function build() {
        return $this->text;
    }
}
