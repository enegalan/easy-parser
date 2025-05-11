<?php

namespace EasyParser\Converter;

use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\Configuration;


class HorizontalRuleConverter extends \League\HTMLToMarkdown\Converter\HorizontalRuleConverter implements ConfigurationAwareInterface {
    /** @var Configuration */
    protected $config;
    public function setConfig(Configuration $config): void {
        $this->config = $config;
    }
    public function convert(ElementInterface $element): string {
        $horizontalRule = $this->config->getOption('horizontal_rule', "\n\n---\n\n");
        return "\n" . $horizontalRule;
    }
}
