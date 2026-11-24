<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'purpose',
        'code_hash',
        'expires_at',
        'attempts',
        'consumed_at',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /**
     * @param  Builder<PhoneVerification>  $query
     */
    public function scopeForPurpose(Builder $query, string $purpose): void
    {
        $query->where('purpose', $purpose);
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->consumed_at === null && ! $this->isExpired();
    }
}
