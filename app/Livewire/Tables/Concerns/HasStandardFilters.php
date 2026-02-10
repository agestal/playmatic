<?php

namespace App\Livewire\Tables\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use Throwable;

trait HasStandardFilters
{
    protected bool $useStandardFilters = true;

    protected ?array $resolvedStandardFilters = null;

    /**
     * Cached options for text fields rendered as select filters.
     *
     * @var array<string, array<int, array{value:string, label:string}>|null>
     */
    protected array $resolvedStandardSelectOptions = [];

    /**
     * Restrict auto-filters to this set of DB fields.
     *
     * @var array<int, string>
     */
    protected array $standardFilterOnly = [];

    /**
     * Exclude fields from auto-filters.
     *
     * @var array<int, string>
     */
    protected array $standardFilterExcept = [
        'id',
        'password',
        'remember_token',
    ];

    /**
     * Supported values: boolean|number|date|datetime|text|select|none.
     *
     * @var array<string, string>
     */
    protected array $standardFilterTypeOverrides = [];

    /**
     * Force select filters for these text fields.
     *
     * @var array<int, string>
     */
    protected array $standardTextSelectOnly = [];

    /**
     * Avoid select filters for these text fields.
     *
     * @var array<int, string>
     */
    protected array $standardTextSelectExcept = [];

    /**
     * Keyword heuristic used when $standardTextSelectOnly is empty.
     *
     * @var array<int, string>
     */
    protected array $standardTextSelectKeywords = [
        'status',
        'state',
        'type',
        'role',
        'category',
        'group',
        'level',
    ];

    protected int $standardTextSelectThreshold = 8;

    protected int $standardTextSelectMaxValueLength = 60;

    public function filters(): array
    {
        if (! $this->useStandardFilters) {
            return [];
        }

        if (is_array($this->resolvedStandardFilters)) {
            return $this->resolvedStandardFilters;
        }

        return $this->resolvedStandardFilters = $this->buildStandardFilters();
    }

    protected function buildStandardFilters(): array
    {
        $model = $this->resolveFilterModel();

        if (! $model instanceof Model) {
            return [];
        }

        $table = $model->getTable();
        $casts = $model->getCasts();

        return collect($this->standardFilterColumns())
            ->map(fn (string $field) => $this->makeStandardFilterForField($field, $table, $casts, $model))
            ->filter()
            ->values()
            ->all();
    }

    protected function makeStandardFilterForField(string $field, string $table, array $casts, Model $model): mixed
    {
        $type = $this->resolveStandardFilterType($field, $table, $casts);

        if ($type === 'select') {
            $options = $this->resolveStandardSelectOptions($field, $model);

            if (is_array($options)) {
                return Filter::select($field)
                    ->dataSource($options)
                    ->optionValue('value')
                    ->optionLabel('label');
            }

            return Filter::inputText($field)->operators(['contains', 'is', 'starts_with']);
        }

        if ($type === 'text' && $this->shouldUseSelectForTextField($field)) {
            $options = $this->resolveStandardSelectOptions($field, $model);

            if (is_array($options)) {
                return Filter::select($field)
                    ->dataSource($options)
                    ->optionValue('value')
                    ->optionLabel('label');
            }
        }

        return match ($type) {
            'boolean' => Filter::boolean($field)->label(__('Yes'), __('No')),
            'number' => Filter::number($field),
            'date' => Filter::datepicker($field),
            'datetime' => Filter::datetimepicker($field),
            'text' => Filter::inputText($field)->operators(['contains', 'is', 'starts_with']),
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    protected function standardFilterColumns(): array
    {
        $columns = collect($this->columns())
            ->filter(
                fn (mixed $column): bool => $column instanceof Column
                    && ! $column->isAction
                    && ! $column->hidden
            )
            ->map(fn (Column $column): string => $column->dataField ?: $column->field)
            ->filter(fn (string $field): bool => filled($field) && ! str_contains($field, '.'))
            ->unique();

        if ($this->standardFilterOnly !== []) {
            $allowed = collect($this->standardFilterOnly);

            $columns = $columns->filter(fn (string $field): bool => $allowed->contains($field));
        }

        if ($this->standardFilterExcept !== []) {
            $blocked = collect($this->standardFilterExcept);

            $columns = $columns->reject(fn (string $field): bool => $blocked->contains($field));
        }

        return $columns
            ->values()
            ->all();
    }

    protected function resolveStandardFilterType(string $field, string $table, array $casts): ?string
    {
        $override = data_get($this->standardFilterTypeOverrides, $field);

        if (is_string($override)) {
            $override = Str::lower($override);

            if (in_array($override, ['boolean', 'number', 'date', 'datetime', 'text', 'select'], true)) {
                return $override;
            }

            if (in_array($override, ['none', 'skip'], true)) {
                return null;
            }
        }

        $castType = Str::before(strval(data_get($casts, $field, '')), ':');

        if (filled($castType)) {
            return $this->mapCastTypeToFilterType($castType);
        }

        try {
            if (! Schema::hasColumn($table, $field)) {
                return null;
            }

            return $this->mapColumnTypeToFilterType(Schema::getColumnType($table, $field));
        } catch (Throwable) {
            return null;
        }
    }

    protected function mapCastTypeToFilterType(string $castType): ?string
    {
        $cast = Str::lower($castType);

        return match (true) {
            in_array($cast, ['bool', 'boolean'], true) => 'boolean',
            in_array($cast, ['int', 'integer', 'real', 'float', 'double', 'decimal'], true) => 'number',
            in_array($cast, ['date', 'immutable_date'], true) => 'date',
            in_array($cast, ['datetime', 'immutable_datetime', 'custom_datetime', 'timestamp'], true) => 'datetime',
            in_array($cast, ['string'], true) => 'text',
            default => null,
        };
    }

    protected function mapColumnTypeToFilterType(string $columnType): ?string
    {
        $type = Str::lower($columnType);

        return match (true) {
            in_array($type, ['boolean', 'bool', 'tinyint', 'bit'], true) => 'boolean',
            in_array($type, ['integer', 'int', 'smallint', 'mediumint', 'bigint', 'decimal', 'numeric', 'float', 'double', 'real'], true) => 'number',
            in_array($type, ['date'], true) => 'date',
            in_array($type, ['datetime', 'datetimetz', 'timestamp', 'timestamptz'], true) => 'datetime',
            in_array($type, ['char', 'string', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'enum', 'set', 'uuid'], true) => 'text',
            default => null,
        };
    }

    protected function resolveFilterModel(): ?Model
    {
        $datasource = $this->datasource();

        if ($datasource instanceof EloquentBuilder) {
            return $datasource->getModel();
        }

        return null;
    }

    protected function shouldUseSelectForTextField(string $field): bool
    {
        if ($this->standardTextSelectOnly !== []) {
            return in_array($field, $this->standardTextSelectOnly, true);
        }

        if (in_array($field, $this->standardTextSelectExcept, true)) {
            return false;
        }

        $normalizedField = Str::lower($field);

        return collect($this->standardTextSelectKeywords)
            ->contains(fn (string $keyword): bool => Str::contains($normalizedField, Str::lower($keyword)));
    }

    /**
     * @return array<int, array{value:string, label:string}>|null
     */
    protected function resolveStandardSelectOptions(string $field, Model $model): ?array
    {
        if (array_key_exists($field, $this->resolvedStandardSelectOptions)) {
            return $this->resolvedStandardSelectOptions[$field];
        }

        try {
            $values = $model->newQuery()
                ->select($field)
                ->whereNotNull($field)
                ->distinct()
                ->orderBy($field)
                ->limit($this->standardTextSelectThreshold + 1)
                ->pluck($field)
                ->map(fn (mixed $value): string => is_scalar($value) ? trim((string) $value) : '')
                ->filter(
                    fn (string $value): bool => $value !== ''
                        && Str::length($value) <= $this->standardTextSelectMaxValueLength
                )
                ->unique()
                ->values();
        } catch (Throwable) {
            return $this->resolvedStandardSelectOptions[$field] = null;
        }

        if ($values->isEmpty() || $values->count() > $this->standardTextSelectThreshold) {
            return $this->resolvedStandardSelectOptions[$field] = null;
        }

        return $this->resolvedStandardSelectOptions[$field] = $values
            ->map(function (string $value): array {
                $label = Str::contains($value, ['_', '-'])
                    ? Str::of($value)->replace(['_', '-'], ' ')->title()->toString()
                    : $value;

                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })
            ->all();
    }
}
