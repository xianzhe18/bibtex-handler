# Changes in BibTex Parser

## [0.5.0] - _not yet released_

### Added

- Issue [#17]: Ability to add multiple processors to the `Listener` through `addTagValueProcessor()`;
- Issue [#15]: Author name processor, `RenanBr\BibTexParser\Processor\AuthorProcessor`;

[0.5.0]: https://github.com/renanbr/bibtex-parser/releases/tag/0.5.0

### Changed

- Issue [#17]: Deprecate `Listener::setTagValueProcessor()`.

[#15]: https://github.com/renanbr/bibtex-parser/issues/15
[#17]: https://github.com/renanbr/bibtex-parser/issues/17

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

- PR [#1]: Trailing comma causes `ParseException`;
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
