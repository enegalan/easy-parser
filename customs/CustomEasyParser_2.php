<?php

require dirname(__DIR__).'/src/EasyParser.php';

use League\HTMLToMarkdown\Environment;


class CustomEasyParser_2 extends EasyParser {
    public function __construct(array $options = array(), array|Environment $environment) {
        parent::__construct($options, $environment);
    }
    public static function convert(string $html) {
        // Just HTML to markdown convertion
        $markdown = parent::$converter->convert($html);
        return $markdown;
    }
}
