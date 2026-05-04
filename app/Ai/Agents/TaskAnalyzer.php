<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class TaskAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'Je bent een productiviteitsassistent.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'priority'          => $schema->string()->enum(['laag', 'middel', 'hoog'])->required(),
            'ai_description'    => $schema->string()->required(),
            'estimated_minutes' => $schema->integer()->required(),
        ];
    }
}
