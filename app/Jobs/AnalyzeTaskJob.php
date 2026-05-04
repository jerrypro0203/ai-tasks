<?php

namespace App\Jobs;

use App\Enums\Status;
use App\Models\Task;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeTaskJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private Task $task) {}

    // Backoff/retries in seconden: 1s, 2s, 4s
    public function backoff(): array
    {
        return [1, 2, 4];
    }

    public function handle(AiService $aiService): void
    {
        $this->task->update(['status' => Status::PROCESSING]);

        $result = $aiService->analyzeTask(
            $this->task->title,
            $this->task->description
        );

        $this->task->update([
            'status'            => Status::COMPLETED,
            'ai_description'    => $result['ai_description'] ?? null,
            'priority'          => $result['priority'] ?? 'middel',
            'estimated_minutes' => $result['estimated_minutes'] ?? null,
        ]);
    }

    public function failed(\Exception $e): void
    {
        $this->task->update(['status' => Status::FAILED]);

        Log::error('AnalyzeTaskJob failed', [
            'task_id' => $this->task->id,
            'error'   => $e->getMessage(),
        ]);
    }
}