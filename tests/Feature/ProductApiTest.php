<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-09-24 10:00:00');
    $this->user = User::factory()->create();
});

describe('store', function () {
    it('registers a product for the default user', function () {
        $this->postJson('/api/products', [
            'name' => 'Milk',
            'expiration_date' => '2026-09-26',
            'location' => 'fridge',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Milk')
            ->assertJsonPath('data.quantity', 1)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.opened_at', null)
            ->assertJsonPath('data.days_until_expiry', 2)
            ->assertJsonPath('data.is_expired', false);

        expect($this->user->products()->count())->toBe(1);
    });

    it('rejects invalid data', function (array $payload, string $field) {
        $this->postJson('/api/products', $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
    })->with([
        'missing name' => [['expiration_date' => '2026-10-01'], 'name'],
        'invalid date' => [['name' => 'Milk', 'expiration_date' => 'soon'], 'expiration_date'],
        'future opened_at' => [['name' => 'Milk', 'expiration_date' => '2026-10-01', 'opened_at' => '2026-09-30'], 'opened_at'],
        'zero quantity' => [['name' => 'Milk', 'expiration_date' => '2026-10-01', 'quantity' => 0], 'quantity'],
    ]);
});

describe('index', function () {
    it('lists active products by default, soonest expiry first', function () {
        $later = Product::factory()->for($this->user)->create(['expiration_date' => '2026-10-20']);
        $sooner = Product::factory()->for($this->user)->create(['expiration_date' => '2026-09-25']);
        Product::factory()->for($this->user)->consumed()->create();
        Product::factory()->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $sooner->id)
            ->assertJsonPath('data.1.id', $later->id);
    });

    it('filters by status', function () {
        $discarded = Product::factory()->for($this->user)->discarded()->create();
        Product::factory()->for($this->user)->create();

        $this->getJson('/api/products?status=discarded')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $discarded->id);
    });

    it('rejects an unknown status', function () {
        $this->getJson('/api/products?status=eaten')->assertUnprocessable()->assertJsonValidationErrors('status');
    });
});

describe('expiring', function () {
    it('lists active products expiring within the given days, including expired ones', function () {
        $expired = Product::factory()->for($this->user)->create(['expiration_date' => '2026-09-20']);
        $soon = Product::factory()->for($this->user)->create(['expiration_date' => '2026-09-29']);
        Product::factory()->for($this->user)->create(['expiration_date' => '2026-10-20']);
        Product::factory()->for($this->user)->consumed()->create(['expiration_date' => '2026-09-25']);

        $this->getJson('/api/products/expiring?days=5')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $expired->id)
            ->assertJsonPath('data.0.is_expired', true)
            ->assertJsonPath('data.1.id', $soon->id);
    });

    it('defaults to 3 days', function () {
        Product::factory()->for($this->user)->create(['expiration_date' => '2026-09-27']);
        Product::factory()->for($this->user)->create(['expiration_date' => '2026-09-28']);

        $this->getJson('/api/products/expiring')->assertOk()->assertJsonCount(1, 'data');
    });

    it('rejects a negative number of days', function () {
        $this->getJson('/api/products/expiring?days=-1')->assertUnprocessable()->assertJsonValidationErrors('days');
    });
});

describe('show', function () {
    it('shows the user\'s product', function () {
        $product = Product::factory()->for($this->user)->create();

        $this->getJson("/api/products/{$product->id}")->assertOk()->assertJsonPath('data.id', $product->id);
    });

    it('forbids viewing another user\'s product', function () {
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")->assertForbidden();
    });

    it('returns 404 for a missing product', function () {
        $this->getJson('/api/products/999')->assertNotFound();
    });
});

describe('update status', function () {
    it('marks a product as consumed or discarded', function (string $status) {
        $product = Product::factory()->for($this->user)->create();

        $this->patchJson("/api/products/{$product->id}/status", ['status' => $status])
            ->assertOk()
            ->assertJsonPath('data.status', $status)
            ->assertJsonPath('data.finished_at', now()->toIso8601String());

        expect($product->fresh()->status->value)->toBe($status);
    })->with(['consumed', 'discarded']);

    it('rejects setting the status back to active', function () {
        $product = Product::factory()->for($this->user)->create();

        $this->patchJson("/api/products/{$product->id}/status", ['status' => 'active'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    });

    it('rejects a product that is already finished', function () {
        $product = Product::factory()->for($this->user)->consumed()->create();

        $this->patchJson("/api/products/{$product->id}/status", ['status' => 'discarded'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    });

    it('forbids updating another user\'s product', function () {
        $product = Product::factory()->create();

        $this->patchJson("/api/products/{$product->id}/status", ['status' => 'consumed'])->assertForbidden();
    });
});
