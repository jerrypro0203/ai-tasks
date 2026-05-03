<?php

use App\Jobs\AnalyzeTaskJob;
use App\Models\Task;
use Illuminate\Support\Facades\Bus;

it('can create a task', function () {
    Bus::fake();

    $response = $this->postJson('/api/tasks', [
        'title' => 'Test taak',
        'description' => 'Test beschrijving',
    ]);

    $response->assertStatus(201);
    Bus::assertDispatched(AnalyzeTaskJob::class);
});

it('can get all tasks', function () {
    $response = $this->getJson('/api/tasks');
    $response->assertStatus(200);
});

it('can delete a task', function () {
    $task = Task::factory()->create();
    
    $response = $this->deleteJson("/api/tasks/{$task->id}");
    $response->assertStatus(204);
});