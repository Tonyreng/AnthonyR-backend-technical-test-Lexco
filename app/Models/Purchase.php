<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     * @author OpenCode
     * @since 2026/05
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the purchase.
     *
     * @return BelongsTo
     * @author OpenCode
     * @since 2026/05
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items associated with the purchase.
     *
     * @return HasMany
     * @author OpenCode
     * @since 2026/05
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
