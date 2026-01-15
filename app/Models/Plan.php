<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'features',
        'limits',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // Helper methods
    public function getLimit(string $key, $default = null)
    {
        return $this->limits[$key] ?? $default;
    }

    public function hasFeature(string $key): bool
    {
        return isset($this->features[$key]) && $this->features[$key];
    }
}
