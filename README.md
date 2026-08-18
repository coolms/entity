# coolms/entity

[![CI](https://github.com/coolms/entity/actions/workflows/ci.yml/badge.svg)](https://github.com/coolms/entity/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/coolms/entity)](https://packagist.org/packages/coolms/entity)
[![PHP](https://img.shields.io/badge/php-%E2%89%A5%208.5-777bb4)](https://www.php.net/releases/8.5/en.php)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Entity contracts for the CoolMS platform, and the **extras** engine -- dynamic
per-entity fields stored in a JSON column and described by a schema resolved at
runtime.

## What extras is

A class opts in by implementing `ExtrasProviderInterface` (usually via
`ExtrasProviderTrait`). It then carries an open `$extras` bag whose permitted
keys, types, validation rules and per-instance applicability come from a schema
resolved by `EntitySchemaLookup` for the class's registered alias.

Extras is an **entity** capability, not a dynamic-types one: modules that never
define a type at runtime still declare extras on their own entities.

## The ports

The engine lives here, at the bottom of the dependency graph, and asks for what
it needs through interfaces other packages implement:

| Port | Answers | Typically implemented by |
|---|---|---|
| `Contract\FieldSchemaSourceInterface` | record-defined fields for an alias, in the neutral schema shape | the field-management module |
| `Contract\FieldMetadataSourceInterface` | resolved per-property metadata and override provenance | the same |
| `Contract\EntityTypeSchemaContributorInterface` | **optional** -- a type's parent chain and cached schema | a runtime-types module |
| `Contract\ExtrasNormalizationExclusionInterface` | "this type is normalized elsewhere" | any module with its own normalizer |

Only the first is required. With no type contributor installed there is no
schema inheritance -- which is correct for an install without runtime types,
not a silently disabled feature.

Ports return **neutral shapes** (plain arrays), never the implementing module's
classes. That is the point: a port typed against another package's entities
would be a wrapper, not a seam.

## Also here

`Registry\EntityAliasRegistry` (FQCN to alias map, filled by each module's
compiler pass), the entity resolver chain, virtual-field descriptors and
registry, and the shared provider traits (label, name, description,
translatable, orderable).

## Related packages

- `coolms/entity-module` -- platform composition: reflection field extraction,
  the extras-flattening normalizer, widget renderers
- `coolms/entity-doctrine` -- ORM/DBAL adapters: mapping driver, validation
  listener, generated virtual columns, per-platform schema and upsert managers
- `coolms/entity-bundle` -- Symfony integration

## Installation

```bash
composer require coolms/entity
```
