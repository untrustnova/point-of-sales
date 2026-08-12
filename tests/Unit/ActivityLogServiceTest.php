<?php

use App\Models\Store;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('activity log service dapat mencatat audit trail aktivitas pengguna', function () {
    $store = Store::create(['name' => 'Store Log Test', 'code' => 'LOG01']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $logService = app(ActivityLogService::class);

    $log = $logService->log(
        action: 'product.created',
        description: 'Menambahkan produk baru Kopi Susu',
        storeId: $store->id,
        userId: $user->id,
        properties: ['sku' => 'PRD-NEW']
    );

    expect($log->action)->toBe('product.created')
        ->and($log->store_id)->toBe($store->id)
        ->and($log->user_id)->toBe($user->id)
        ->and($log->properties)->toBeArray();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'product.created',
        'store_id' => $store->id,
    ]);
});
