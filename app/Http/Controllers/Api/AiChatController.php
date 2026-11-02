<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    protected $aiService;

    public function __construct(GeminiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle chat request from frontend.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:2000',
                'context' => 'nullable|string|max:1000', // Optional context (e.g., patient history summary)
            ]);

            // Construct the system prompt based on user role and context
            $systemPrompt = $this->buildSystemPrompt($request->user(), $validated['context'] ?? null);

            // Get response from AI
            $response = $this->aiService->ask($validated['message'], $systemPrompt);

            return apiSuccess('AI response retrieved', [
                'reply' => $response
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            return apiError('Failed to process AI request. Please try again later.', null, 500);
        }
    }

    /**
     * Build a context-aware system prompt.
     */
    protected function buildSystemPrompt($user, ?string $context): string
    {
        $role = $user ? class_basename($user) : 'User';

        $prompt = "You are a helpful AI assistant for a Healthcare Portal. ";
        $prompt .= "You are communicating with a {$role}. ";

        if ($role === 'Patient') {
            $prompt .= "Your goal is to help patients understand their medical appointments, vaccination schedules, and covid test results. ";
            $prompt .= "Be empathetic, clear, and professional. Do not provide medical diagnosis. Always disable advice to consult a doctor for serious concerns. ";
        } elseif ($role === 'Hospital') {
            $prompt .= "Assist hospital staff with managing appointments and updating patient records. ";
        } elseif ($role === 'Admin') {
            $prompt .= "Assist the administrator with system analytics and data management. ";
        }

        if ($context) {
            $prompt .= "\n\nCurrent Context: {$context}";
        }

        $prompt .= "\n\nKeep your responses concise and relevant to the healthcare portal context.";

        return $prompt;
    }
}
