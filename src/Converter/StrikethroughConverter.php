<?php

declare(strict_types=1);

namespace EasyParser\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
class StrikethroughConverter implements ConverterInterface, ConfigurationAwareInterface {
    /** @var Configuration */
    protected $config;
    public function setConfig(Configuration $config): void
    {
        $this->config = $config;
    }
    public function convert(ElementInterface $element): string
    {

        $markdown = '';
        $strikethrough = \html_entity_decode($element->getChildrenAsString());
        $strikethrough = \preg_replace('/<del\b[^>]*>/', '', $strikethrough);
        \assert($strikethrough !== null);
        $code = \str_replace('</del>', '', $strikethrough);
        $strikethrough_style = $this->config->getOption('strikethrough_style', '~~');
        $markdown .= $strikethrough_style . $code . $strikethrough_style;
        return $markdown;
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['del'];
    }
}
