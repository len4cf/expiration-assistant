<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductStatusRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
        ]);

        $products = $request->user()->products()
            ->where('status', $validated['status'] ?? ProductStatus::Active->value)
            ->orderBy('expiration_date')
            ->paginate(20);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $request->user()->products()->create($request->validated());

        return ProductResource::make($product->refresh())
            ->response()
            ->setStatusCode(201);
    }

    public function expiring(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'days' => ['sometimes', 'integer', 'min:0', 'max:365'],
        ]);

        $products = $request->user()->products()
            ->expiringWithin((int) ($validated['days'] ?? 3))
            ->get();

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        Gate::authorize('view', $product);

        return ProductResource::make($product);
    }

    public function updateStatus(UpdateProductStatusRequest $request, Product $product): ProductResource
    {
        Gate::authorize('update', $product);

        if ($product->status !== ProductStatus::Active) {
            throw ValidationException::withMessages([
                'status' => "This product is already {$product->status->value}.",
            ]);
        }

        $product->markAs($request->enum('status', ProductStatus::class));

        return ProductResource::make($product);
    }
}
