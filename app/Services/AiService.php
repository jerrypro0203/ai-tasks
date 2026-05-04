<?php

namespace App\Services;

use App\Ai\Agents\TaskAnalyzer;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function analyzeTask(string $title, ?string $description): array
    {
        try {
            $response = (new TaskAnalyzer)->prompt("Analyseer: {$title}. {$description}")->structured;

            return [
                'priority'          => $response['priority'] ?? 'middel',
                'ai_description'    => $response['ai_description'] ?? null,
                'estimated_minutes' => $response['estimated_minutes'] ?? null,
            ];

        } catch (\Throwable $e) {
            Log::error('AI analyse mislukt: ' . $e->getMessage());

            return [
                'priority'          => 'middel',
                'ai_description'    => null,
                'estimated_minutes' => null,
            ];
        }
    }

    public function enrichTask(array $validated): array
    {
        $result = $this->analyzeTask(
            $validated['title'],
            $validated['description'] ?? null
        );

        return array_merge($validated, $result);
    }
}