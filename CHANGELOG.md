# Changes in BibTex Parser

## [1.0.5] - 2018-08-30

### Fixed

- Issue [#49]: @Comment cause ParserException::unexpectedCharacter

[1.0.5]: https://github.com/renanbr/bibtex-parser/releases/tag/1.0.5
[#49]:https://github.com/renanbr/bibtex-parser/issues/49

## [1.0.4] - 2018-02-04

### Fixed

- Issue [#44]: Parsing CitationKey with : and / in them (DBPL and ACM)

[1.0.4]: https://github.com/renanbr/bibtex-parser/releases/tag/1.0.4
[#44]: https://github.com/renanbr/bibtex-parser/issues/44

## [1.0.3] - 2017-11-14

### Fixed

- Issue [#40]: invalid char before tag content isn't caught

[1.0.3]: https://github.com/renanbr/bibtex-parser/releases/tag/1.0.3
[#40]: https://github.com/renanbr/bibtex-parser/issues/40

## [1.0.2] - 2017-11-05

### Fixed

- Issue [#39] Parser::ORIGINAL_ENTRY isn't triggered

[1.0.2]: https://github.com/renanbr/bibtex-parser/releases/tag/1.0.2
[#39]: https://github.com/renanbr/bibtex-parser/issues/39

## [1.0.1] - 2017-10-29

### Fixed

- Issue [#33] Citation key is ignored

[1.0.1]: https://github.com/renanbr/bibtex-parser/releases/tag/1.0.1
[#33]: https://github.com/renanbr/bibtex-parser/issues/33

## [1.0.0] - 2017-10-11

### Removed

- Removed deprecated methods and classes

[1.0.0]: https://github.com/renanbr/bibtex-parser/releases/tag/1.0.0

## [0.6.0] - 2017-10-05

### Added

- Issues [#25] and [#26]: Ability to change covered tags  through `setTagCoverage()` for all processors;
- Names processor, `RenanBr\BibTexParser\Processor\NamesProcessor`;
- Issue [#29]: Support for PHP 7.2

### Changed

- Deprecate `RenanBr\BibTexParser\Processor\AuthorProcessor`.

[0.6.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.6.0
[#25]: https://github.com/renanbr/bibtex-parser/issues/25
[#26]: https://github.com/renanbr/bibtex-parser/issues/26
[#29]: https://github.com/renanbr/bibtex-parser/issues/29

## [0.5.0] - 2017-06-29

### Added

- Issue [#17]: Ability to add multiple processors to the `Listener` through `addTagValueProcessor()` ([@andrechalom]);
- Issue [#15]: Author name processor, `RenanBr\BibTexParser\Processor\AuthorProcessor` ([@andrechalom]);
- PR [#22]: Keywords processor, `RenanBr\BibTexParser\Processor\KeywordsProcessor`;
- Issue [#19]: LaTeX to Unicode processor, `RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor`.

[0.5.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.5.0

### Changed

- Issue [#17]: Deprecate `Listener::setTagValueProcessor()` ([@andrechalom]).

[#15]: https://github.com/renanbr/bibtex-parser/issues/15
[#17]: https://github.com/renanbr/bibtex-parser/issues/17
[#22]: https://github.com/renanbr/bibtex-parser/pull/22
[#19]: https://github.com/renanbr/bibtex-parser/issues/19

## [0.4.0] - 2017-05-23

### Fixed

- Issue [#6]: `%` character into delimited value causes `ParseException`.

[#6]: https://github.com/renanbr/bibtex-parser/issues/6

### Changed

- Issue [#7]: Comments are treated according to the BibTeX's specification.

[#7]: https://github.com/renanbr/bibtex-parser/issues/7

[0.4.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.4.0

## [0.3.0] - 2017-01-06

### Added

- Issue [#5]: Ability to process tag value through `Listener::setTagValueProcessor()`.

[#5]: https://github.com/renanbr/bibtex-parser/issues/5

[0.3.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.3.0

## [0.2.0] - 2016-12-17

### Fixed

- PR [#1]: Trailing comma causes `ParseException` ([@raphael-st]);
- Issue [#4]: `_` character into tag name causes `ParseException`.

[#1]: https://github.com/renanbr/bibtex-parser/commit/2ac8aec67d4f6aceb443cb03b855f8c2b2f456e3
[#4]: https://github.com/renanbr/bibtex-parser/issues/4

[0.2.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.2.0


### Added

- Issue [#2]: Original BibTeX entries are sent to the listeners with the status `Parser::ORIGINAL_ENTRY` just after each entry reading is done;
- `Listener::export()` produces entries with an additional key called `_original`, which contains the original BibTex entry;
- Ability to change the tag name case through `Listener::setTagNameCase()`.

[#2]: https://github.com/renanbr/bibtex-parser/issues/2

## [0.1.0] - 2016-11-29

- First release.

[0.1.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.1.0

[@andrechalom]: https://github.com/andrechalom
[@raphael-st]: https://github.com/raphael-st
