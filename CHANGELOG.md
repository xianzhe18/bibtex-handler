# Changes in renanbr/bibtex-parser

## [0.2.0](https://github.com/renanbr/bibtex-parser/releases/tag/0.2.0) <small>17/12/2016</small>

### Fixed

- Trailing comma support;
- Allow underscore in tag name.

### Added

- Original BibTeX entries are sent to the listeners with the status `RenanBr\Parser::ORIGINAL_ENTRY` just after each entry reading is done;
- `RenanBr\Listener::export()` produces entries with an additional key called `_original`, which contains the original BibTex entry;
- Created `RenanBr\Listener::setTagNameCase()`.

## [0.1.0](https://github.com/renanbr/bibtex-parser/releases/tag/0.1.0) <small>29/11/2016</small>

- First release.
