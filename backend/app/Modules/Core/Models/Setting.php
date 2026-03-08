<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Setting Model
 *
 * Represents application settings and configurations.
 *
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Setting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get the value attribute with proper type casting.
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueAttribute($value): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute with proper type handling.
     *
     * @param mixed $value
     * @return void
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = match ($this->type ?? 'string') {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json', 'array' => json_encode($value),
            default => $value,
        };
    }

    /**
     * Scope to filter by key pattern.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $pattern
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKeyPattern($query, string $pattern)
    {
        return $query->where('key', 'like', $pattern);
    }

    /**
     * Scope to filter by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
