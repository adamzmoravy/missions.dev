<?php

namespace Karolina\Content;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class TiptapEntityMentionExtension extends Node
{

    public static $name = 'entityMention';


    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
            'renderLabel' => fn ($node) => $node->attrs->label ?? null,
        ];
    }

    public function parseHTML()
    {
        return [
            [
                'tag' => 'span[data-type="' . self::$name . '"]'
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'id' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-id') ?: null,
                'renderHTML' => fn ($attributes) => ['data-id' => $attributes->id ?? null],
            ],
        ];
    }

    public function renderText($node)
    {
        return $this->options['renderLabel']($node);
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return [
            'a',
            [
                'href' => '/'.$node->attrs->type.'/'.$node->attrs->id,
                'data-id' => $node->attrs->id,
                'data-type' => self::$name,
            ],
            0,
        ];
    }

}