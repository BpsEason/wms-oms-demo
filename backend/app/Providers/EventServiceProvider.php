<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// 引入 WMS 相關事件和監聽器
use App\Events\OrderCreated;
use App\Listeners\CreatePickingTask;
use App\Events\PickingCompleted;
use App\Listeners\CreateSortingTask;
use App\Events\SortingCompleted;
use App\Listeners\CreatePackingTask;
use App\Events\PackingCompleted;
use App\Listeners\UpdateInventory;
use App\Events\ShippingStarted; // 新增
use App\Listeners\CreateShipment; // 新增
use App\Events\SortingStarted; // 假設 SortingStarted 已經存在

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // WMS 核心流程事件註冊
        OrderCreated::class => [
            CreatePickingTask::class,
        ],
        PickingCompleted::class => [
            // 此處應避免重複創建 SortingTask，假設 PickingController 已經觸發 SortingStarted
        ],
        SortingStarted::class => [ // 這是 Picking 完成後觸發的
            CreateSortingTask::class,
        ],
        SortingCompleted::class => [
            CreatePackingTask::class, // 分揀完成 -> 創建包裝任務
        ],
        PackingCompleted::class => [
            UpdateInventory::class, // 包裝完成 -> 扣減庫存
        ],
        ShippingStarted::class => [ // 新增：包裝完成後觸發
            CreateShipment::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
