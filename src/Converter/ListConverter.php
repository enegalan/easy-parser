<?php

namespace EasyParser\Converter;

use League\HTMLToMarkdown\Converter\ListItemConverter;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\Coerce;

class ListConverter extends ListItemConverter {
    public function convert(ElementInterface $element): string {
        // If parent is an ol, use numbers, otherwise, use dashes
        $listType = ($parent = $element->getParent()) ? $parent->getTagName() : 'ul';

        // Add spaces to start for nested list items
        $level = $element->getListItemLevel();

        $value = \trim(\implode("\n" . "\t", \explode("\n", \trim($element->getValue()))));
        // Remove double line breaks
        $value = preg_replace("/\n\s*\n(?=\s*(\d+\.)?)/", "\n", $value);

        // If list item is the first in a nested list, add a newline before it
        $prefix = '';
        if ($level > 0 && $element->getSiblingPosition() === 1) {
            $prefix = "\n";
        }

        if ($listType === 'ul') {
            $listItemStyle          = Coerce::toString($this->config->getOption('list_item_style', '-'));
            $listItemStyleAlternate = Coerce::toString($this->config->getOption('list_item_style_alternate', ''));
            if (! isset($this->listItemStyle)) {
                $this->listItemStyle = $listItemStyleAlternate ?: $listItemStyle;
            }

            if ($listItemStyleAlternate && $level === 0 && $element->getSiblingPosition() === 1) {
                $this->listItemStyle = $this->listItemStyle === $listItemStyle ? $listItemStyleAlternate : $listItemStyle;
            }

            return $prefix . $this->listItemStyle . ' ' . $value . "\n";
        }

        if ($listType === 'ol' && ($parent = $element->getParent()) && ($start = \intval($parent->getAttribute('start')))) {
            $number = $start + $element->getSiblingPosition() - 1;
        } else {
            $number = $element->getSiblingPosition();
        }

        return $prefix . $number . '. ' . $value . "\n";
    }
}
