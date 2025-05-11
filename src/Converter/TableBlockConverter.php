<?php

declare(strict_types=1);

namespace EasyParser\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\Coerce;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
class TableBlockConverter extends TableConverter {
    /** @var array<int, string>|null */
    private $columnAlignments = [];

    /** @var string|null */
    private $caption = null;
    public function convert(ElementInterface $element): string {
        $value = $element->getValue();

        switch ($element->getTagName()) {
            case 'table':
                $this->columnAlignments = [];
                if ($this->caption) {
                    $side = $this->config->getOption('table_caption_side');
                    if ($side === 'top') {
                        $value = $this->caption . "\n" . $value;
                    } elseif ($side === 'bottom') {
                        $value .= $this->caption;
                    }

                    $this->caption = null;
                }

                return $value . "\n";
            case 'caption':
                $this->caption = \trim($value);

                return '';
            case 'tr':
                $value .= "|\n";
                if ($this->columnAlignments !== null) {
                    $value .= '| ' . \implode(' | ', $this->columnAlignments) . " |\n";

                    $this->columnAlignments = null;
                }

                return $value;
            case 'th':
            case 'td':
                if ($this->columnAlignments !== null) {
                    $align = $element->getAttribute('align');
                    $alignments = [
                        'left' => $this->config->getOption('table_align_left', ':--'),
                        'right' => $this->config->getOption('table_align_right', '--:'),
                        'center' => $this->config->getOption('table_align_center', ':-:'),
                    ];
                    $this->columnAlignments[] = $alignments[$align] ?? $this->config->getOption('table_align_default', '---');
                }

                $value = \str_replace("\n", ' ', $value);
                $value = \str_replace('|', Coerce::toString($this->config->getOption('table_pipe_escape') ?? '\|'), $value);
                $max_default_width = 10; // This maximum default is used just to fit the cell with the table
                $cell_width = -2; // The first and last space that are included always in the cell
                $cell_width += \strlen(\trim($value));
                if ($cell_width < $max_default_width) {
                    $fill_length = ($max_default_width - $cell_width) * 1;
                    $value = \str_pad($value, $fill_length);
                }
                return '| ' . $value . ' ';
            case 'thead':
            case 'tbody':
            case 'tfoot':
            case 'colgroup':
            case 'col':
                return $value;
            default:
                return '';
        }
    }
}
