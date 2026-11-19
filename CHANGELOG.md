# Changelog

## 11.0.1 — 2026-11-16

- Don't add deprecated log during comment management

## 11.0 — 2026-11-08

- Add deprecated messages
- Cope with new behavior coreContentFilterV2 instead of coreContentFilter
- Cope with RTL
- Use typed getter/setter Cursor method, DC 2.40+
- Enforces some verification before registering module

## 10.0 — 2026-08-03

- Avoid using magic _get(), _set() methods of MetaRecord (2.39+)
- Avoid using magic methods for user preferences and blog settings (2.39+)
- Use MetaRecord::strField(), MetaRecord::intField() and MetaRecord::boolField() whenever is possible
- Fav Icon
- Review icon colors (DC 2.39+)
- Harmonize usage of App::blogWorkspace()::NS_... and App::userWorkspace()::WS_... (and use short version of constant name for types)

## 9.3 — 2026-03-18

- Use getAttribute('id') method rather than using form.id to get form ID as if a field using "id" as name (or id) is present in list of form's fields, this field node will be returned instead of form ID.
- Add blogroll edit forms (link/category) to monitoring

## 9.2 — 2026-02-23

- DA -&gt; DC

## 9.1 — 2025-12-26

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.0 — 2025-12-15

- Forgot media option
- Forgot this one

## 8.4 — 2025-12-15

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.3 — 2025-11-19

- Report some commits from original repo

## 8.2 — 2025-10-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.1 — 2025-09-22

- Use backend services rather than static calls

## 8.0 — 2025-09-07

- DC 2.36 compliance

## 7.1 — 2025-09-02

- Be sure to use string pointer when calling SmartyPants::transform()

## 7.0 — 2025-05-20

- Switch to coreContentFilter behavior, DC 2.34+

## 6.1 — 2025-04-03

- Add an option for categories' description

## 6.0 — 2025-04-03

- Cope with the new 2.34 behavior coreContentFilter

## 5.2.1 — 2025-01-21

- Fix actions (pages/posts/comments)

## 5.2 — 2024-08-21

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.1 — 2023-10-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.0 — 2023-10-24

- Use instanced item constants rather than interface ones
- 2.28 compliance

## 4.0.1 — 2023-09-06

- Fix actions on entries

## 4.0 — 2023-08-26

- Dotclear 2.27 compliance, PHP 8.1+

## 3.4.1 — 2023-06-27

- Add local static $init variable

## 3.4 — 2023-06-20

- Use dcCore::app()-&gt;adminurl-&gt;redirect() rather than Http::redirect()

## 3.3 — 2023-06-19

- Use admin URLs

## 3.2.1 — 2023-06-12

- Don't do things in frontend if plugin disabled
- Use admin URLs rather than strings

## 3.2 — 2023-05-13

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 3.1 — 2023-05-10

- Use My::id() rather than string

## 3.0 — 2023-05-01

- Prepare 2.26
- Use new classes for actions
- Prepare 2.25

## 2.0 — 2022-11-30

- Dotclear 2.24 compliance
- Admin actions V2

## 1.12.1 — 2022-08-14

- Usual oups :-)

## 1.12 — 2022-08-10

- Update default branch in _define.php for alternative repository
- Add alternative repository

## 1.11 — 2021-09-06

- a11y fix form

## 1.10.1 — 2020-06-25

- Allow usage for 'simple' users

## 1.10 — 2020-03-20

- Doc update
- Cleanup afert switching from hg
- short notation for array (array() → [])
- minor updates in doc
- MkDocs sources

## 1.9 — 2016-02-14

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.8.1 — 2016-01-17

- PHP 7 compliance of SmartyPants library
- Using dcPage::cssLoad, dcPage::jsLoad and dcPage::getPF is a better way to load plugin resources

## 1.8 — 2015-01-02

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.7.2 — 2015-01-01

- Fix bug which prevents comments to be processed, update smartypants lib from https://michelf.ca/projects/php-smartypants/

## 1.7.1 — 2013-10-28

- Bugfix for 2.6 actions on comments, posts and pages

## 1.7 — 2013-10-04

- new _define.php
- Cope with new favorites system
- Cope with new breadcrumb
- No more $GLOBALS at top level, and various
- Factorisation du code
- Cope with changes in comment_actions
- Cope with changes in post_actions
- Add selected posts list on action page
- Autres correctifs et améliorations.

## 1.6 — 2012-12-03

- 1.6
- Admin action on pages
- More elegant code
- harmonize &lt;label&gt;&lt;/label&gt; in forms
- Now uses core message function (dcPage::message)
- Add missing files
- Revert to original name and desc in define.php of plugins. Localized versions are not required here.

## 1.5.1 — 2012-03-23

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.
