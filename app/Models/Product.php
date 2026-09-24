<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'quantity', 'expiration_date', 'opened_at', 'location'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'opened_at' => 'date',
            'finished_at' => 'datetime',
            'status' => ProductStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Only products that are still in stock (not consumed or discarded).
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', ProductStatus::Active);
    }

    /**
     * Active products expiring within the given number of days, including already expired ones.
     */
    #[Scope]
    protected function expiringWithin(Builder $query, int $days): void
    {
        $query->active()
            ->whereDate('expiration_date', '<=', now()->addDays($days))
            ->orderBy('expiration_date');
    }

    /**
     * Mark the product as consumed or discarded.
     */
    public function markAs(ProductStatus $status): bool
    {
        $this->status = $status;
        $this->finished_at = $status === ProductStatus::Active ? null : now();

        return $this->save();
    }
}
