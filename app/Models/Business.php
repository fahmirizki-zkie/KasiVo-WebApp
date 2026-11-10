<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'business_type',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function aiProcessingLogs(): HasMany
    {
        return $this->hasMany(AiProcessingLog::class);
    }

    public function restockInsights(): HasMany
    {
        return $this->hasMany(RestockInsight::class);
    }

    // Accessor cerdas: Kas Masuk, Kas Keluar, dan Kas Bersih Total
    public function getNetCashFlowAttribute(): float
    {
        $cashIn = (float) $this->transactions()->where('type', 'cash_in')->sum('amount');
        $cashOut = (float) $this->transactions()->where('type', 'cash_out')->sum('amount');

        return $cashIn - $cashOut;
    }
}

