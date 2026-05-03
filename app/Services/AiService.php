<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai;
use Mockery;
use function Laravel\Ai\agent;

class AiService
{
    public function analyzeTask(string $title, ?string $description): array
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                $response = agent(
                    instructions: $this->instructions(),
                )->prompt($this->buildPrompt($title, $description));

                // checken of dit de juiste propertie is
                return $this->parse($response->text);

            } catch (\Throwable $e) {
                $attempts++;

                Log::error("AI poging {$attempts} mislukt: " . $e->getMessage());

                sleep(pow(2, $attempts - 1));

                if ($attempts >= $maxAttempts) {
                    throw new \RuntimeException(
                        "AI call mislukt na {$maxAttempts} pogingen."
                    );
                }
            }
        }

        return [];
    }

    private function instructions(): string
    {
        return 'Je bent een productiviteitsassistent. Geef altijd JSON terug met: {"priority": "laag|middel|hoog", "ai_description": "...", "estimated_minutes": 30}';
    }

    private function buildPrompt(string $title, ?string $description): string
    {
        return "Analyseer deze taak: {$title}. " . ($description ?? '');
    }

    private function parse(string $json): array
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('AI returned invalid JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    public function enrichTask(array $validated): array
    {
        $result = $this->analyzeTask(
            $validated['title'],
            $validated['description'] ?? null
        );

        return array_merge($validated, [
            'ai_description'    => $result['ai_description'] ?? null,
            'priority'          => $result['priority'] ?? 'middel',
            'estimated_minutes' => $result['estimated_minutes'] ?? null,
        ]);
    }
}