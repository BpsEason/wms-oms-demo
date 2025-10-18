<?php
namespace App\Jobs;
use App\Models\PickingTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPicking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $pickingTask;
    public function __construct(PickingTask $pickingTask) { $this->pickingTask = $pickingTask; }

    public function handle(): void
    {
        // 模擬實際揀貨員掃碼完成流程
        sleep(rand(1, 3)); 
        $this->pickingTask->status = 'Completed';
        $this->pickingTask->save();
        // 此處不發送事件，由 Listener 統一檢查訂單狀態
    }
}
