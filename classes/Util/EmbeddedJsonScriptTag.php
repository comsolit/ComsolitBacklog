<?php namespace Comsolit\Backlog\Util;

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