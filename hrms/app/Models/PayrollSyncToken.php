<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PayrollSyncToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Generate and persist a new secured API token for external feeder clients.
     */
    public static function createToken(string $name): self
    {
        return self::create([
            'name' => $name,
            'token' => 'payflow_' . Str::random(40),
            'is_active' => true,
        ]);
    }
}
