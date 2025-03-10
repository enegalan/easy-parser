<?php

require dirname(__DIR__).'/src/EasyParser.php';

use League\HTMLToMarkdown\Environment;

class CustomEasyParser_1 extends EasyParser {
    public function __construct(array $options = array(), array|Environment $environment) {
        parent::__construct($options, $environment);
    }
    public static function convert(string $html) {
        $html = self::preProcessHtml($html); // Additional pre-cleaning
        // Call to EasyParser class convertion method for HTML and markdown cleaning
        $markdown = parent::convert($html);
        $markdown = self::postProcessMarkdown($markdown); // Additional post-cleaning
        return $markdown;
    }
    private static function preProcessHtml(string $html) {
        // Custom logic to modify HTML before conversion
        return $html;
    }
    private static function postProcessMarkdown(string $markdown) {
        // Custom logic to modify markdown after conversion
        return $markdown;
    }
}
