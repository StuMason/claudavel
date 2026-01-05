<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Services\SqidService;
use Illuminate\Database\Eloquent\Builder;

trait HasUid
{
    /**
     * Get the uid attribute (computed from ID).
     */
    public function getUidAttribute(): string
    {
        if ($this->id) {
            return app(SqidService::class)->encode($this->id);
        }

        return '';
    }

    /**
     * Get the route key name for Laravel route binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    /**
     * Decode the uid to use in route model binding.
     */
    public function resolveRouteBindingQuery(
        $query,
        $value,
        $field = null
    ): \Illuminate\Contracts\Database\Eloquent\Builder {
        $field ??= 'id';

        if ($field === $this->getUidKeyName()) {
            $field = 'id';
        }

        return parent::resolveRouteBindingQuery(
            query: $query,
            value: $field === 'id' ? app(SqidService::class)->decode($value)[0] ?? null : $value,
            field: $field
        );
    }

    /**
     * Get the UID key name.
     */
    private function getUidKeyName(): string
    {
        return 'uid';
    }

    /**
     * Find a model by its UID.
     */
    public static function findByUid(string $uid): ?static
    {
        return static::where(
            'id',
            app(SqidService::class)->decode($uid)[0] ?? null
        )->firstOrFail();
    }

    /**
     * Scope a query to a specific UID.
     */
    public static function scopeWhereUid(Builder $query, string $uid): ?Builder
    {
        return $query->where(
            'id',
            app(SqidService::class)->decode($uid)[0] ?? null
        );
    }
}
