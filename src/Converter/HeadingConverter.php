<?php

declare(strict_types=1);

namespace EasyParser\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\Coerce;
use League\HTMLToMarkdown\Converter\HeaderConverter;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
class HeadingConverter extends HeaderConverter {
    
    public function convert(ElementInterface $element): string
    {
        $level = (int) \substr($element->getTagName(), 1, 1);
        $style = $this->config->getOption('header_style', self::STYLE_SETEXT);

        if (\strlen($element->getValue()) === 0) {
            return "\n";
        }

        if (($level === 1 || $level === 2) && ! $element->isDescendantOf('blockquote') && $style === self::STYLE_SETEXT) {
            return $this->createSetextHeader($level, $element->getValue());
        }

        return $this->createAtxHeader($level, $element->getValue());
    }
    private function createSetextHeader(int $level, string $content): string
    {
        $length    = \function_exists('mb_strlen') ? \mb_strlen($content, 'utf-8') : \strlen($content);
        $underline = $level === 1 ? '=' : '-';

        return $content . "\n" . \str_repeat($underline, $length) . "\n";
    }

    private function createAtxHeader(int $level, string $content): string
    {
        $prefix = \str_repeat('#', $level) . ' ';

        return $prefix . $content . "\n";
    }
}
