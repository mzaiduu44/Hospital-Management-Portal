<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected array $defaults;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model');
        $this->defaults = config('gemini.defaults', []);
    }

    /**
     * Send a chat completion request to Gemini.
     *
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param array $options Additional options (model, max_tokens, temperature, etc.)
     * @return array
     * @throws \Exception
     */
    public function chat(array $messages, array $options = []): array
    {
        if (!$this->apiKey) {
            throw new \Exception('Gemini API key is not configured');
        }

        // Transform messages to Gemini format
        $contents = $this->transformMessages($messages);

        // Build request payload
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? $this->defaults['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? $this->defaults['temperature'] ?? 0.7,
            ],
        ];

        // Add system instruction if present in messages
        $systemMessage = collect($messages)->firstWhere('role', 'system');
        if ($systemMessage) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemMessage['content']]]
            ];
        }

        $model = $options['model'] ?? $this->model;
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying() // Fix for local SSL certificate issues
                ->timeout(60)
                ->post($apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('AI service request failed: ' . $response->status());
            }

            $data = $response->json();

            // Extract response text from Gemini format
            $responseText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return [
                'success' => true,
                'message' => $responseText,
                'model' => $model,
                'usage' => $data['usageMetadata'] ?? null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini Connection Error', ['error' => $e->getMessage()]);
            throw new \Exception('Unable to connect to AI service');
        }
    }

    /**
     * Quick method for single message chat.
     *
     * @param string $message The user message
     * @param string|null $systemPrompt Optional system prompt
     * @return string The AI response content
     */
    public function ask(string $message, ?string $systemPrompt = null): string
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $response = $this->chat($messages);

        return $response['message'];
    }

    /**
     * Generate news content based on title and category.
     *
     * @param string $title The news article title
     * @param string $category The news category
     * @param string|null $details Optional additional details
     * @return string Generated content (130-170 words)
     */
    public function generateNewsContent(string $title, string $category, ?string $details = null): string
    {
        $prompt = "Write a professional news article content for a healthcare/COVID-19 vaccination portal.\n\n";
        $prompt .= "Title: {$title}\n";
        $prompt .= "Category: {$category}\n";

        if ($details) {
            $prompt .= "Additional Details: {$details}\n";
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Write exactly 130-170 words\n";
        $prompt .= "- Be informative and professional\n";
        $prompt .= "- Include relevant health information\n";
        $prompt .= "- Make it engaging and easy to read\n";
        $prompt .= "- Do NOT include the title in the content\n";
        $prompt .= "- Do NOT use markdown formatting\n";
        $prompt .= "\nWrite the article content now:";

        $systemPrompt = "You are a professional health news writer for a COVID-19 vaccination portal. Write clear, accurate, and engaging content. Always maintain a professional tone and focus on public health information.";

        return $this->ask($prompt, $systemPrompt);
    }

    /**
     * Transform messages to Gemini format.
     * Gemini uses 'user' and 'model' roles, not 'user' and 'assistant'.
     *
     * @param array $messages
     * @return array
     */
    protected function transformMessages(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            // Skip system messages (handled separately)
            if ($message['role'] === 'system') {
                continue;
            }

            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]]
            ];
        }

        return $contents;
    }

    /**
     * Sanitize message content.
     *
     * @param string $content
     * @return string
     */
    protected function sanitizeContent(string $content): string
    {
        // Remove null bytes
        $content = str_replace("\0", '', $content);

        // Trim whitespace
        $content = trim($content);

        return $content;
    }
}
