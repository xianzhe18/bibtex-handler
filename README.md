# BibTeX Parser 1.x

[![Build Status](https://travis-ci.org/renanbr/bibtex-parser.svg?branch=1.x)](https://travis-ci.org/renanbr/bibtex-parser)

This is a [BibTeX](http://mirrors.ctan.org/biblio/bibtex/base/btxdoc.pdf) parser written in PHP.

You are browsing the documentation of **BibTeX Parser 1.x**.

[Documentation for version 2.x is available here](https://github.com/renanbr/bibtex-parser/blob/master/README.md).

## Table of contents

* [Installing](#installing)
* [Usage](#usage)
* [Configuring the Listener](#configuring-the-listener)
   * [Tag name case](#tag-name-case)
   * [Tag value processors](#tag-value-processors)
      * [Authors and Editors](#authors-and-editors)
      * [Keywords](#keywords)
      * [LaTeX to Unicode](#latex-to-unicode)
      * [Custom](#custom)
* [Advanced usage](#advanced-usage)

## Installing

```bash
composer require renanbr/bibtex-parser ^1
```

See the [changelog](CHANGELOG.md).

## Usage

```php
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Listener;

$parser = new Parser();                     // Create a Parser instance
$listener = new Listener();                 // Create a Listener instance
$parser->addListener($listener);            // Attach the Listener to the Parser
$parser->parseFile('/path/to/example.bib'); // Parse a file, or string $parser->parseString()
$entries = $listener->export();             // Get data from the Listener

$entries[0]['type'];         // article
$entries[0]['citation-key']; // Ovadia2011
$entries[0]['title'];        // Managing Citations With Cost-Free Tools
$entries[0]['journal'];      // Behavioral {\&} Social Sciences Librarian
```

Below we have the `example.bib` source file used in the sample above.

```bib
@article{Ovadia2011,
    author = {Ovadia, Steven},
    doi = {10.1080/01639269.2011.565408},
    issn = {0163-9269},
    journal = {Behavioral {\&} Social Sciences Librarian},
    month = {apr},
    number = {2},
    pages = {107--111},
    title = {Managing Citations With Cost-Free Tools},
    url = {http://www.tandfonline.com/doi/abs/10.1080/01639269.2011.565408},
    volume = {30},
    year = {2011}
}
```

## Configuring the Listener

The `RenanBr\BibTexParser\Listener` class provides, by default, these features:

- `export()` returns all entries found;
- [`citation-key` auto detection](http://www.bibtex.org/Format/);
- [Tag value concatenation](http://www.bibtex.org/Format/);
- [Abbreviation handling](http://www.bibtex.org/Format/);
- The type of publication is exposed in the `type` key;
- The original text of each entry is exposed in the `_original` key.

Besides that you can configure it in two ways:

- Tag name case; and
- Tag value processors.

If you need more than this, considering implementing your own listener (more info at the end of this document).

### Tag name case

You can change the character case of tags' names through `setTagNameCase()` before exporting the contents.

```php
$listener->setTagNameCase(CASE_UPPER); // or CASE_LOWER
$entries = $listener->export();
$entries[0]['TYPE'];
```

### Tag value processors

You can change tags' values by adding one or more processors through `addTagValueProcessor()` before exporting the contents.
This project is shipped with some useful processors out of the box.

#### Authors and Editors

BibTeX recognizes four parts of an author's name: First Von Last Jr.
If you would like to parse the author and editor names included in your entries, you can use the `RenanBr\BibTexParser\Processor\NamesProcessor` class.
Before exporting the contents, add this processor:

```php
use RenanBr\BibTexParser\Processor\NamesProcessor;

$listener->addTagValueProcessor(new NamesProcessor());
$entries = $listener->export();
```

The resulting `$entries[0]['author']` and `$entries[0]['editor']` will then be arrays with each name separated in the four parts above.

#### Keywords

The `keywords` tag contains a list of expressions represented as text, you might want to read them as an array instead.
You can achieve it adding `RenanBr\BibTexParser\Processor\KeywordsProcessor` before exporting the contents:

```php
use RenanBr\BibTexParser\Processor\KeywordsProcessor;

$listener->addTagValueProcessor(new KeywordsProcessor());
$entries = $listener->export();
```

The resulting `$entries[0]['keywords']` will then be an array.

#### LaTeX to Unicode

BibTeX files store LaTeX contents.
You might want to read them as unicode instead.
The `RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor` class solves this problem.
Before adding the processor to the listener you must:

- [install Pandoc](http://pandoc.org/installing.html) in your system; and
- add [ryakad/pandoc-php](https://github.com/ryakad/pandoc-php) as a dependency of your project.

```php
use RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor;

$listener->addTagValueProcessor(new LatexToUnicodeProcessor());
$entries = $listener->export();
```

Notes:

- Order matters, add this processor as the last;
- This processor may throw a `Pandoc\PandocException`.

#### Custom

The `addTagValueProcessor()` method expects a `callable` as argument.
In the example shown below, we append the text `with laser` to the `title` tags for all entries.

```php
$listener->addTagValueProcessor(function (&$value, $tag) {
    if ($tag == 'title') {
        $value .= ' with laser';
    }
});
```

## Advanced usage

This library has two main parts:

- Parser, represented by the `RenanBr\BibTexParser\Parser` class; and
- Listener, represented by the `Renan\BibTexParser\ListenerInterface` interface.

The parser class is able to detect BibTeX units, such as "type", "tag name", "tag value".
As the parser finds an unite, listeners are triggered.

You can code your own listener!
All you have to do is handle unites.

```php
interface RenanBr\BibTexParser\ListenerInterface
{
    /**
     * Called when an unit is found.
     *
     * @param string $text    The original content of the unit found.
     *                        Escaped characters will not be sent.
     * @param array  $context Contains details of the unit found.
     */
    public function bibTexUnitFound(string $text, array $context): void;
}
```

The `$context` variable explained:
- The `state` key contains the current parser's state.
  It may assume:
  - `Parser::TYPE`
  - `Parser::KEY` (tag name)
  - `Parser::RAW_VALUE` (tag value)
  - `Parser::BRACED_VALUE` (tag value)
  - `Parser::QUOTED_VALUE` (tag value)
  - `Parser::ORIGINAL_ENTRY`
- `offset` contains the text beginning position.
  It may be useful, for example, to [seek on a file pointer](https://php.net/fseek);
- `length` contains the original text length.
  It may differ from string length sent to the listener because may there are escaped characters.
