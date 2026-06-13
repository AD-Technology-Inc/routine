<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;

/**
 * AIPlannerService is intentionally thin.
 * AI is called only for initial planning, weekly review, and chat coaching.
 * All scheduling and rescheduling uses the deterministic SchedulingService.
 */
class AIPlannerService
{
    /**
     * Generate an AI-suggested task roadmap for a goal.
     * Returns a structured array of task suggestions.
     *
     * @return array<int, array{title: string, estimated_minutes: int, type: string, priority: string}>
     */
    public function generateRoadmap(Goal $goal, User $user): array
    {
        // Stubbed — wire up OpenAI/Anthropic here when ready.
        // Replace this with: Http::withToken(config('services.openai.key'))->post(...)
        return [
            ['title' => 'Research and outline ' . $goal->title, 'estimated_minutes' => 60, 'type' => 'planning', 'priority' => 'high'],
            ['title' => 'Core implementation phase 1', 'estimated_minutes' => 120, 'type' => 'execution', 'priority' => 'high'],
            ['title' => 'Core implementation phase 2', 'estimated_minutes' => 120, 'type' => 'execution', 'priority' => 'high'],
            ['title' => 'Review and iterate', 'estimated_minutes' => 60, 'type' => 'review', 'priority' => 'medium'],
        ];
    }

    /**
     * Generate a weekly coaching review based on the user's performance.
     *
     * @return array{summary: string, suggestions: array<int, string>, encouragement: string}
     */
    public function generateWeeklyReview(User $user): array
    {
        // Stubbed — replace with real LLM call using analytics context.
        return [
            'summary' => 'You completed tasks consistently this week. Great momentum!',
            'suggestions' => [
                'Try to front-load high-energy tasks in the morning.',
                'Consider splitting any tasks over 90 minutes.',
            ],
            'encouragement' => 'Keep it up — consistency beats intensity.',
        ];
    }

    /**
     * Return a contextual coaching response for a user message.
     *
     * @param array<string, mixed> $context
     */
    public function chatResponse(User $user, string $message, array $context = []): string
    {
        // Stubbed — replace with real LLM call. Context includes goals + today's plan.
        return "I see you're working on: " . collect($context['goals'] ?? [])->pluck('title')->join(', ') . '. What specific challenge can I help you with?';
    }
}
