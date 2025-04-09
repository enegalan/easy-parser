<?php

namespace EasyParser;

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

requireAutoloader();

class EasyParser {
    public static $converter;
    public static $parsedown;

    public function __construct(array $options = array(), array $environment = array()) {
        self::$converter = $this->buildConverter($environment);
        $opts = array_merge($this->getDefaultOptions(), $options);
        $this->setOptions($opts);
        self::$parsedown = new \Parsedown();
    }

    private function buildConverter(array $environment = array()) {
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
        $html = preg_replace('/(<table[^>]*>)/', "<br>$1", $html); // Add a <br> before the <table> tag
        $html = preg_replace('/(<\/table[^>]*>)/', "$1<br>", $html); // Add a <br> after the </table> tag
        // Remove the previous <br> tag before a list that contains (•) bullet points, this <br> can be on <div><font><span><br></span></font></div>
        $html = preg_replace('/(?:<div[^>]*>)?(?:<[^>]*>)*\s*<br\s*\/?>\s*(?:<\/[^>]*>)*(?:<\/div>)?\s*(?:<div[^>]*>\s*)*(?:<font[^>]*>\s*)*(?:<span[^>]*>\s*)*•/', "<br><br>•", $html);
        // If <li> parent is something different than <ul> or <ol>, replace parent with <ul>
        $html = preg_replace_callback('/<([^>]+)>\s*<li[^>]*>.*?<\/li>\s*<\/\1>/s', function($matches) {
            if (!in_array(strtolower($matches[1]), ['ul', 'ol'])) {
                // Replace tag with <ul> (by default markdown does that)
                return '<ul>' . preg_replace('/<([^>]+)>\s*<li/', '<li', $matches[0]) . '</ul>';
            }
            return $matches[0]; // Keep <ul> or <ol>
        }, $html);
        $html = preg_replace('/<li>\s*(?:&ZeroWidthSpace;|&nbsp;|\s)*<\/li>/', '', $html); // Remove "empty" <li> tags
        $html = preg_replace('/<li><\/li>/', '', $html); // Remove empty <li> tags
        $html = preg_replace('/<li><li>/', '<li>', $html); // Remove malformed <li> tags
        $html = preg_replace('/<li>\s*<li>/i', '<li>', $html);
        // Check if previous an <ul> or <ol> there is any newline, if not add a <br> before the <ul> or <ol>
        $html = preg_replace_callback('/(<(ul|ol)[^>]*>)/', function($matches) use ($html) {
            $pos = strpos($html, $matches[0]);
            // Find previous line start position (finds last <br> or <p> before the list)
            $beforeChunk = substr($html, 0, $pos);
            $lastLineBreak = max(strrpos($beforeChunk, '<br>'), strrpos($beforeChunk, '</p>'), strrpos($beforeChunk, '<p>'));
            if ($lastLineBreak === false) $lastLineBreak = 0;
            $prevLine = substr($beforeChunk, $lastLineBreak);
            $brCount = substr_count($prevLine, '<br>');
            if (preg_match('/<p>/', $prevLine) || $brCount >= 2) {
                // If there is already a <p> or two <br> in the previous line, do not add anything
                return $matches[0];
            } else {
                $breaks = '<br><br>';
                return $breaks . $matches[0];
            }
        }, $html);
        return $html;
    }

    private static function cleanMarkdown(string $markdown) {
        $search = [" \n ", "\n ", "|---", "\n```\n", "<del>", "</del>", "       ", "   ", "•"];
        $replace = [" ", "\n", "| -------- ", "```\n", "~~", "~~", "\t\t", "\t", self::$converter->getConfig()->getOption('list_item_style')];
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
        $markdown = preg_replace('/\\\(\*)/', '$1', $markdown); // Remove asterisks (*) escaping
        $markdown = preg_replace('/\\\(\-)/', '$1', $markdown); // Remove dash (-) escaping
        $markdown = preg_replace('/\n{3,}/', "\n\n\n", $markdown); // Replace more than 2 line breaks
        $markdown = preg_replace("/\n{2,}/", "\n\n", $markdown); // Normalize multiple newlines
        $markdown = preg_replace("/\n(?=\*)/", "\n", $markdown); // Remove any newline before list item
        $markdown = preg_replace('/\*\s+(?=\r?\n)/', "*\n", $markdown); // Remove space after asterisk and before newline
        return $markdown;
    }

    private function getDefaultOptions() {
        return array (
            'italic_style' => '*',
            'header_style' => 'atx', // #, ##, ###...
            'strip_tags' => true,
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

function requireAutoloader() {
    $autoloadPaths = array(
        // Local package usage
        __DIR__ . '/../vendor/autoload.php',
        // Package was included as a library
        __DIR__ . '/../../../autoload.php',
    );
    foreach ($autoloadPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}