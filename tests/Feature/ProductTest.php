<?php

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('defaults to active and unopened when registered', function () {
    $user = User::factory()->create();

    $product = $user->products()->create([
        'name' => 'Milk',
        'expiration_date' => '2026-10-01',
    ])->refresh();

    expect($product->status)->toBe(ProductStatus::Active)
        ->and($product->quantity)->toBe(1)
        ->and($product->opened_at)->toBeNull()
        ->and($product->finished_at)->toBeNull();
});

it('lists active products expiring within the given days, soonest first', function () {
    $this->travelTo('2026-09-24');

    $user = User::factory()->create();
    $expired = Product::factory()->for($user)->create(['expiration_date' => '2026-09-20']);
    $soon = Product::factory()->for($user)->create(['expiration_date' => '2026-09-27']);
    Product::factory()->for($user)->create(['expiration_date' => '2026-10-10']);
    Product::factory()->for($user)->consumed()->create(['expiration_date' => '2026-09-25']);

    $ids = $user->products()->expiringWithin(3)->pluck('id')->all();

    expect($ids)->toBe([$expired->id, $soon->id]);
});

it('marks a product as consumed or discarded', function (ProductStatus $status) {
    $product = Product::factory()->create();

    $product->markAs($status);

    expect($product->fresh()->status)->toBe($status)
        ->and($product->fresh()->finished_at)->not->toBeNull()
        ->and(Product::active()->count())->toBe(0);
})->with([ProductStatus::Consumed, ProductStatus::Discarded]);

it('scopes products to their owner', function () {
    [$owner, $other] = User::factory()->count(2)->create();
    Product::factory()->for($owner)->count(2)->create();
    Product::factory()->for($other)->create();

    expect($owner->products)->toHaveCount(2)
        ->and($other->products)->toHaveCount(1);
});
