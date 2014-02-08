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

require_once __DIR__ . '/TextTag.php';

class HTMLTag
{
    private $attributes = array();
    private $children = array();
    private $tag;

    public function __construct($tag, $content = array())
    {
        if(!is_array($content)) $content = array($content);

        foreach($content as $key => $element) {
            if(is_string($element)) $element = new TextTag($element);
            $this->addChild($element);
        }

        if(!is_string($tag)) throw new Exception('$tag must be string, got: '.print_r($tag, true));
        $this->tag = $tag;
    }

    /**
     * @return HTMLTag
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Add attribute only if $condition is true.
     *
     * @return \Comsolit\HTML\Tags\HTMLTag
     */
    public function addAttributeIf($condition, $name, $value) {
        if($condition) $this->addAttribute($name, $value);
        return $this;
    }

    /**
     * @return HTMLTag
     */
    public function addChild(HTMLTag $child) {
        $this->children[] = $child;
        return $this;
    }

    public function build()
    {
        $html = '<'.$this->tag.' ';
        foreach($this->attributes as $key => $value) {
            $html .= $key.'="';
            $html .= $value.'" ';
        }
        $html .= '>'.$this->renderContent().'</'.$this->tag.'>';
        return $html;
    }

    public function renderContent() {
        return array_reduce($this->children, function($acc, $child){return $acc .= $child->build();}, '');
    }

    /**
     * @return HTMLTag
     */
    public function encapsulate($tag)
    {
        return new self($tag, $this);
    }

    /**
     * @return HTMLTag
     */
    public static function __callStatic($tag, $arguments) {
        return new self($tag, empty($arguments) ? array() : $arguments[0]);
    }
}
