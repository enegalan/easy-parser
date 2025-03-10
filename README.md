# EasyParser - HTML and Markdown parser for PHP

`EasyParser` is a PHP library designed to convert HTML content to markdown in a simple and efficient way. It uses [league/html-to-markdown](https://github.com/thephpleague/html-to-markdown) library for converting HTML to markdown and [erusev/parsedown](https://github.com/erusev/parsedown) for markdown to HTML, offering an ALL-IN-ONE library.

## Requirements

- PHP 7.4 or higher.
- Composer to manage dependencies.

## Installation

To use `EasyParser`, first install the dependencies via Composer:

```bash
composer require league/html-to-markdown
composer require erusev/parsedown
```

## How to use it
`EasyParser` provides `convert` and all methods provided by `Parsedown`.
```php
$parser = new EasyParser();
echo $parser->convert('<h1>Hello world</h1>'); // return "# Hello World"
```
```php
$parser = new EasyParser();
echo $parser->text('# Hello world'); // return "<h1>Hello world</h1>"
```
The included `customs` directory contains custom EasyParser extended class examples.

## Conversion options for `thephpleague/html-to-markdown`
> [!NOTE]
> Conversion is fully adapted to EasyMDE output markdown with default formatting options. You can create a custom class that extends `EasyParser` and implement methods to adapt the conversion to your needs.

To strip HTML tags that don't have a Markdown equivalent while preserving the content inside them, set `strip_tags` to true, like this:
```php
$options = array('strip_tags' => true);
$parser = new EasyParser($options);
echo $parser->convert('<span>Turnips!</span>'); // return "Turnips!"
```
Or more explicitly, like this:
```php
$parser = new EasyParser();
$parser->converter->getConfig()->setOption('strip_tags', true);
echo $parser->convert('<span>Turnips!</span>'); // return "Turnips!"
```
Note that only the tags themselves are stripped, not the content they hold.

To strip tags and their content, pass a space-separated list of tags in `remove_nodes`, like this:
```php
$options = array('remove_nodes' => 'span div');
$parser = new EasyParser($options);
echo $parser->convert('<span>Turnips!</span><div>Monkeys!</div>'); // return ""
```
By default, all comments are stripped from the content. To preserve them, use the `preserve_comments` option, like this:
```php
$options = array('preserve_comments' => true);
$parser = new EasyParser($options);
echo $parser->convert('<span>Turnips!</span><!-- Monkeys! -->'); // return "Turnips!<!-- Monkeys! -->"
```
To preserve only specific comments, set `preserve_comments` with an array of strings, like this:
```php
$options = array('preserve_comments' => array('Eggs!'));
$parser = new EasyParser($options);
echo $parser->convert('<span>Turnips!</span><!-- Monkeys! --><!-- Eggs! -->'); // return "Turnips!<!-- Eggs! -->"
```
By default, placeholder links are preserved. To strip the placeholder links, use the `strip_placeholder_links` option, like this:
```php
$options = array('strip_placeholder_links' => true);
$parser = new EasyParser($options);
echo $parser->convert('<a>Github</a>'); // return "Github"
```
### Style options
By default bold tags are converted using the asterisk syntax, and italic tags are converted using the underlined syntax. Change these by using the `bold_style` and `italic_style` options.
```php
$options = array(
    'italic_style' => '*',
    'bold_style' => '__',
);
$parser = new EasyParser($options);
echo $parser->convert('<em>Italic</em> and a <strong>bold</strong>'); // return "*Italic* and a __bold__"
```

### Line break options
By default, `br` tags are converted to two spaces followed by a newline character as per [traditional Markdown](https://daringfireball.net/projects/markdown/syntax#p). Set `hard_break` to `true` to omit the two spaces, as per GitHub Flavored Markdown (GFM).
```php
$parser = new EasyParser();
$html = '<p>test<br>line break</p>';

$parser->converter->getConfig()->setOption('hard_break', true);
echo $parser->convert($html); // return "test\nline break"

$parser->converter->getConfig()->setOption('hard_break', false);
echo $parser->convert($html); // return "test  \nline break"
```

### Autolinking options
By default, `a` tags are converted to the easiest possible link syntax, i.e. if no text or title is available, then the `<url>` syntax will be used rather than the full `[url](url)` syntax. Set `use_autolinks` to `false` to change this behavior to always use the full link syntax.
```php
$parser = new EasyParser();
$html = '<p><a href="https://thephpleague.com">https://thephpleague.com</a></p>';

$parser->converter->getConfig()->setOption('use_autolinks', true);
echo $parser->convert($html); // return "<https://thephpleague.com>"

$parser->converter->getConfig()->setOption('use_autolinks', false);
echo $parser->convert($html); // return "[https://thephpleague.com](https://thephpleague.com)"
```

### Passing custom Environment object
You can pass current Environment object to customize i.e. which converters should be used.
```php
$environment = new Environment(array(
    // your configuration here
));
$environment->addConverter(new HeaderConverter()); // optionally - add converter manually
$options = array();
$parser = new EasyParser($options, $environment);
$html = '<h3>Header</h3>
<img src="" />
';
echo $parser->convert($html); // return "### Header" and "<img src="" />"
```

## Limitations
Markdown Extra, MultiMarkdown and other variants aren't supported – just Markdown.

## Style notes
- Setext (underlined) headers are the default for H1 and H2. If you prefer the ATX style for H1 and H2 (# Header 1 and ## Header 2), set `header_style` to 'atx' in the options array when you instantiate the object:
```php
$converter = new EasyParser(array('header_style'=>'atx'));
```
Headers of H3 priority and lower always use atx style.
- Links and images are referenced inline. Footnote references (where image src and anchor href attributes are listed in the footnotes) are not used.
- Blockquotes aren't line wrapped – it makes the converted Markdown easier to edit.

## Dependencies
HTML To Markdown requires PHP's [xml](http://www.php.net/manual/en/xml.installation.php), [lib-xml](http://www.php.net/manual/en/libxml.installation.php), and [dom](http://www.php.net/manual/en/dom.installation.php) extensions, all of which are enabled by default on most distributions.

Errors such as "Fatal error: Class 'DOMDocument' not found" on distributions such as CentOS that disable PHP's xml extension can be resolved by installing php-xml.