<?php

use App\Models\Task;
use App\Services\AiService;

it('can create a task', function () {
    // Without mocking, the AI returns a random enum value (laag/middel/hoog). So that is why we mock.
    $this->mock(AiService::class, function ($mock) {
        $mock->shouldReceive('analyzeTask')
            ->once()
            ->andReturn([
                'priority'          => 'hoog',
                'ai_description'    => 'AI omschrijving',
                'estimated_minutes' => 30,
            ]);
    });

    $response = $this->postJson('/api/tasks', [
        'title'       => 'Test taak',
        'description' => 'Test beschrijving',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('tasks', [
        'title'    => 'Test taak',
        'priority' => 'hoog',
    ]);
});

it('can get all tasks', function () {
    Task::factory()->count(3)->create();

    $response = $this->getJson('/api/tasks');
    $response->assertStatus(200);

    $response->assertJsonCount(3, 'data');
});

it('can get a single task', function () {
    $task = Task::factory()->create();

    $response = $this->getJson("/api/tasks/{$task->id}");
    $response->assertStatus(200);

    $this->assertDatabaseHas('tasks', [
        'title' => $task->title,
    ]);
});

it('can delete a task', function () {
    $task = Task::factory()->create();

    $response = $this->deleteJson("/api/tasks/{$task->id}");
    $response->assertStatus(204);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});