# API Platform Laravel — Bug Reproduction

Minimal reproduction for a bug in `api-platform/laravel` where
`ModelMetadata::getAttributes()` drops primary key columns that share a name
with `HasMany` foreign keys.

## Setup

```bash
composer install
php artisan migrate
```

## Run the tests

```bash
php artisan test tests/Feature/ApiPlatformBugTest.php
```

## The bug

When an Eloquent model uses a custom primary key (e.g. `device_id`) and has a
`HasMany` relation to another model with the same foreign key name
(`ports.device_id`), the primary key is incorrectly excluded from the attribute
list.

### Root cause

`ModelMetadata::getAttributes()` (line ~78-86) collects **all** foreign key
names from discovered relations and excludes matching columns:

```php
$relations = $this->getRelations($model);
$foreignKeys = array_flip(array_filter(array_column($relations, 'foreign_key')));

foreach ($columns as $column) {
    if (isset($foreignKeys[$column['name']])) {
        continue;   // drops primary key here
    }
}
```

`getForeignKeyName()` returns different columns depending on the relation type:

| Relation type | `getForeignKeyName()` returns | Column lives on |
|---|---|---|
| `BelongsTo` | local FK column (e.g. `ports.device_id`) | **current** model |
| `HasMany` / `HasOne` | remote FK column (e.g. `ports.device_id`) | **related** model |
| `BelongsToMany` | pivot FK column | pivot table |

Only `BelongsTo` foreign keys are local columns that should be excluded. The
code treats all FK names as local, which drops the primary key when it matches
a `HasMany` foreign key name.

### Impact

- Identifier discovery fails (no primary key in property collection)
- `Get` route is not registered (returns 404)
- IRI generation fails ("Unable to generate an IRI for the item of type ...")
- All single-item operations are broken

### Suggested fix

Only exclude foreign keys from `BelongsTo` relations:

```php
$localForeignKeys = [];
foreach ($relations as $relation) {
    if (
        $relation['foreign_key']
        && is_a($relation['type'], BelongsTo::class, true)
    ) {
        $localForeignKeys[$relation['foreign_key']] = true;
    }
}
```

## Models

- **Device** — `devices` table, primary key `device_id`, has `HasMany` ports
- **Port** — `ports` table, foreign key `device_id` referencing devices

Only Device is an `#[ApiResource]`.
