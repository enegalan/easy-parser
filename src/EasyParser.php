<?php

namespace enegalan\EasyParser;

use League\HTMLToMarkdown\Environment;
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

require 'vendor/autoload.php';

class EasyParser {
    public static $converter;
    public static $parsedown;

    public function __construct(array $options = array(), array|Environment $environment) {
        self::$converter = $this->buildConverter($environment);
        $opts = array_merge($this->getDefaultOptions(), $options);
        $this->setOptions($opts);
        self::$parsedown = new Parsedown();
    }

    private function buildConverter(array|Environment $environment = array()) {
        $converter = new HtmlConverter($environment);
        $converter->getEnvironment()->addConverter(new TableConverter()); // Add tables support
        return $converter;
    }

    public static function convert(string $html) {
        $html = self::cleanHtml($html);
        $markdown = self::$converter->convert($html);
        $markdown = self::cleanMarkdown($markdown);
        return $markdown;
    }

    private static function cleanHtml(string $html) {
        $html = preg_replace('/(<table[^>]*>)/', "<br>$1", $html);
        $html = preg_replace('/(<\/table[^>]*>)/', "$1<br>", $html);
        return $html;
    }

    private static function cleanMarkdown(string $markdown) {
        $search = [" \n ", "\n ", "|---", "\n```\n", "<del>", "</del>", "       ", "   "];
        $replace = [" ", "\n", "| -------- ", "```\n", "~~", "~~", "\t\t", "\t"];
        $markdown = str_replace($search, $replace, $markdown);
        $markdown = preg_replace_callback('/```(\w+)?\s*(.*?)\s*```/s', function ($matches) {
            $language = $matches[1] ?? '';
            $codeContent = preg_replace('/\n\s*\n/', "\n", trim($matches[2]));
            return $language ? "```$language\n$codeContent\n```" : "```\n$codeContent\n```";
        }, $markdown); // Remove newLines between codeBlock content
        $markdown = preg_replace('/^(#{1,6} .+?)\n+/m', "$1\n", $markdown); // Remove the line break that comes just below the titles
        $markdown = preg_replace('/(?<!\| )(?<!\|)(?<=\n)\n*---+\n*(?!\|)/', "\n\n-----\n\n\n", $markdown); // horizontalRule line breaks
        $markdown = preg_replace('/```\n+\n```/s', "```\n```", $markdown); // Remove empty lines between consecutive code blocks
        $markdown = preg_replace('/```\n+/', "```\n", $markdown); // Remove any newlines after code blocks
        $markdown = preg_replace('/\\\(\*)/', '$1', $markdown); // Remove asterisks escaping
        return $markdown;
    }

    private function getDefaultOptions() {
        return array (
            'italic_style' => '*',
            'header_style' => 'atx', // #, ##, ###...
            'strip_tags' => false,
            'list_item_style' => '*',
            'use_autolinks' => false, // Links []()
            'hard_break' => true, // "something\nline break"
        );
    }

    private function setOptions(array $options) {
        foreach ($options as $opt_key => $opt_value) {
            self::$converter->getConfig()->setOption($opt_key, $opt_value);
        }
    }
    // Implement Parsedown functions
    public static function text(string $text) {
        return self::$parsedown->text($text);
    }

    public static function line(string $text, array $nonNestables = array()) {
        return self::$parsedown->line($text, $nonNestables);
    }

    public static function setBreaksEnabled(bool $breaksEnabled) {
        return self::$parsedown->setBreaksEnabled($breaksEnabled);
    }

    public static function setBreaksEnablesd(bool $markupEscaped) {
        return self::$parsedown->setMarkupEscaped($markupEscaped);
    }

    public static function setSafeMode(bool $safeMode) {
        return self::$parsedown->setSafeMode($safeMode);
    }

    public static function setUrlsLinked(bool $urlsLinked) {
        return self::$parsedown->setUrlsLinked($urlsLinked);
    }

    public static function parse(string $text) {
        return self::$parsedown->parse($text);
    }
}
