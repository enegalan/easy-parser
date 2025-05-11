<?php

require dirname(__DIR__).'/src/EasyParser.php';

use EasyParser\EasyParser;

class CustomEasyParser_2 extends EasyParser {
    public function __construct(array $options = array()) {
        parent::__construct($options);
    }
    public static function convert(string $html) {
        // Just HTML to markdown convertion
        $markdown = parent::$converter->convert($html);
        return $markdown;
    }
}
